<?php
// Include configuration
require_once 'email_config.php';

// Set proper headers for AJAX requests
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
if ($isAjax) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
}

/**
 * Send email using enhanced mail() function (works on most live servers)
 */
function sendEmailEnhanced($to, $subject, $body, $fromEmail, $fromName) {
    // Clean and prepare headers
    $from_address = "Vertex Labs <noreply@" . FROM_DOMAIN . ">";
    
    $headers = array();
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: text/html; charset=UTF-8";
    $headers[] = "From: " . $from_address;
    $headers[] = "Reply-To: " . $fromEmail;
    $headers[] = "Return-Path: noreply@" . FROM_DOMAIN;
    $headers[] = "X-Mailer: Vertex Labs Contact Form v1.0";
    $headers[] = "X-Priority: 3";
    $headers[] = "Message-ID: <" . time() . "." . md5($fromEmail . $to) . "@" . FROM_DOMAIN . ">";
    
    $header_string = implode("\r\n", $headers);
    
    // Use -f parameter to set envelope sender (important for deliverability)
    $additional_parameters = "-f noreply@" . FROM_DOMAIN;
    
    $success = mail($to, $subject, $body, $header_string, $additional_parameters);
    
    if ($success) {
        return ['success' => true, 'method' => 'Enhanced PHP mail()'];
    } else {
        return ['success' => false, 'error' => 'Enhanced mail() function failed'];
    }
}

/**
 * Fallback: Basic PHP mail() function
 */
function sendEmailBasic($to, $subject, $body, $fromEmail, $fromName) {
    $headers = "From: Vertex Labs <noreply@" . FROM_DOMAIN . ">\r\n";
    $headers .= "Reply-To: $fromEmail\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

    $success = mail($to, $subject, $body, $headers);
    
    if ($success) {
        return ['success' => true, 'method' => 'Basic PHP mail()'];
    } else {
        return ['success' => false, 'error' => 'Basic mail() function failed'];
    }
}

/**
 * SMTP method using your Gmail credentials (for servers that support it)
 */
function sendEmailGmailSMTP($to, $subject, $body, $fromEmail, $fromName) {
    if (!USE_SMTP || empty(SMTP_USERNAME) || empty(SMTP_PASSWORD)) {
        return ['success' => false, 'error' => 'SMTP not configured'];
    }
    
    // Use mail() with Gmail SMTP headers
    $headers = array();
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: text/html; charset=UTF-8";
    $headers[] = "From: Vertex Labs <" . SMTP_USERNAME . ">";
    $headers[] = "Reply-To: " . $fromEmail;
    $headers[] = "X-Mailer: Vertex Labs SMTP";
    
    $header_string = implode("\r\n", $headers);
    
    // Try to configure PHP to use Gmail SMTP
    ini_set('SMTP', SMTP_HOST);
    ini_set('smtp_port', SMTP_PORT);
    ini_set('sendmail_from', SMTP_USERNAME);
    
    $success = mail($to, $subject, $body, $header_string);
    
    if ($success) {
        return ['success' => true, 'method' => 'Gmail SMTP via mail()'];
    } else {
        return ['success' => false, 'error' => 'Gmail SMTP failed'];
    }
}

// Security: Only process POST requests
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    if ($isAjax) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    } else {
        header('Location: index.html');
    }
    exit;
}

// Get and sanitize form data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Enhanced validation
$errors = [];

// Name validation
if (empty($name)) {
    $errors[] = "Name is required";
} elseif (strlen($name) < 2) {
    $errors[] = "Name must be at least 2 characters";
} elseif (strlen($name) > 100) {
    $errors[] = "Name must be less than 100 characters";
} elseif (!preg_match('/^[a-zA-Z\s\-\.\']+$/', $name)) {
    $errors[] = "Name contains invalid characters";
}

// Email validation
if (empty($email)) {
    $errors[] = "Email is required";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format";
} elseif (strlen($email) > 254) {
    $errors[] = "Email address too long";
}

// Subject validation
if (empty($subject)) {
    $errors[] = "Subject is required";
} elseif (strlen($subject) < 3) {
    $errors[] = "Subject must be at least 3 characters";
} elseif (strlen($subject) > 200) {
    $errors[] = "Subject must be less than 200 characters";
}

// Message validation
if (empty($message)) {
    $errors[] = "Message is required";
} elseif (strlen($message) < 10) {
    $errors[] = "Message must be at least 10 characters";
} elseif (strlen($message) > 5000) {
    $errors[] = "Message must be less than 5000 characters";
}

// Basic spam protection
$spam_keywords = ['viagra', 'casino', 'lottery', 'bitcoin', 'crypto', 'investment', 'loan', 'winner', 'congratulations'];
$combined_text = strtolower($name . ' ' . $email . ' ' . $subject . ' ' . $message);
foreach ($spam_keywords as $keyword) {
    if (strpos($combined_text, $keyword) !== false) {
        $errors[] = "Message flagged as potential spam";
        break;
    }
}

// Rate limiting (simple IP-based)
$rate_limit_file = sys_get_temp_dir() . '/vertex_contact_' . md5($_SERVER['REMOTE_ADDR']);
if (file_exists($rate_limit_file)) {
    $last_submission = filemtime($rate_limit_file);
    if (time() - $last_submission < 60) { // 1 minute between submissions
        $errors[] = "Please wait 1 minute between submissions";
    }
}

// Process form if no errors
if (empty($errors)) {
    // Update rate limit
    touch($rate_limit_file);
    
    $to = RECIPIENT_EMAIL;
    $email_subject = "Vertex Labs Contact: " . htmlspecialchars($subject);
    
    // Create professional, mobile-friendly email template
    $email_body = "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Contact Form Submission</title>
        <style>
            body { 
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; 
                margin: 0; padding: 0; background-color: #f5f5f5; line-height: 1.6; 
            }
            .container { 
                max-width: 600px; margin: 20px auto; background-color: white; 
                border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
            }
            .header { 
                background: linear-gradient(135deg, #2563eb, #1d4ed8); 
                color: white; padding: 30px 20px; text-align: center; 
            }
            .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
            .header p { margin: 10px 0 0 0; opacity: 0.9; font-size: 14px; }
            .content { padding: 30px 20px; }
            .field { 
                margin-bottom: 20px; padding-bottom: 15px; 
                border-bottom: 1px solid #e5e7eb; 
            }
            .field:last-of-type { border-bottom: none; margin-bottom: 0; }
            .label { 
                font-weight: 600; color: #374151; font-size: 14px; 
                display: block; margin-bottom: 8px; text-transform: uppercase; 
                letter-spacing: 0.5px; 
            }
            .value { color: #1f2937; font-size: 16px; }
            .message-box { 
                background-color: #f8fafc; border-left: 4px solid #2563eb; 
                padding: 20px; margin: 15px 0; border-radius: 0 6px 6px 0;
                font-style: italic; line-height: 1.7;
            }
            .footer { 
                background-color: #f9fafb; padding: 20px; text-align: center; 
                font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb;
            }
            .footer p { margin: 4px 0; }
            .footer .timestamp { font-weight: 600; color: #374151; }
            @media (max-width: 600px) {
                .container { margin: 10px; border-radius: 4px; }
                .header { padding: 20px 15px; }
                .content { padding: 20px 15px; }
                .footer { padding: 15px; }
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>New Contact Submission</h1>
                <p>Vertex Labs Website Contact Form</p>
            </div>
            <div class='content'>
                <div class='field'>
                    <span class='label'>From</span>
                    <div class='value'>" . htmlspecialchars($name) . " &lt;" . htmlspecialchars($email) . "&gt;</div>
                </div>
                <div class='field'>
                    <span class='label'>Subject</span>
                    <div class='value'>" . htmlspecialchars($subject) . "</div>
                </div>
                <div class='field'>
                    <span class='label'>Message</span>
                    <div class='message-box'>
                        " . nl2br(htmlspecialchars($message)) . "
                    </div>
                </div>
            </div>
            <div class='footer'>
                <p class='timestamp'>Submitted on " . date('F j, Y \a\t g:i A T') . "</p>
                <p>IP Address: " . htmlspecialchars($_SERVER['REMOTE_ADDR']) . "</p>
                <p>User Agent: " . htmlspecialchars(substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 50)) . "...</p>
                <p>Referrer: " . htmlspecialchars($_SERVER['HTTP_REFERER'] ?? 'Direct') . "</p>
                <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 15px 0;'>
                <p><strong>Vertex Labs</strong> - Digital Innovation &amp; Technology Solutions</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Try multiple email methods for maximum compatibility on live servers
    $result = null;
    $methods_tried = [];
    
    // Method 1: Try Gmail SMTP if configured
    if (USE_SMTP && !empty(SMTP_USERNAME) && !empty(SMTP_PASSWORD)) {
        $result = sendEmailGmailSMTP($to, $email_subject, $email_body, $email, $name);
        $methods_tried[] = 'Gmail SMTP';
        
        // If Gmail SMTP fails, try enhanced mail
        if (!$result['success']) {
            $result = sendEmailEnhanced($to, $email_subject, $email_body, $email, $name);
            $methods_tried[] = 'Enhanced mail()';
        }
    } else {
        // Method 2: Enhanced PHP mail (recommended for most live servers)
        $result = sendEmailEnhanced($to, $email_subject, $email_body, $email, $name);
        $methods_tried[] = 'Enhanced mail()';
    }
    
    // Method 3: Basic fallback if enhanced fails
    if (!$result['success']) {
        $result = sendEmailBasic($to, $email_subject, $email_body, $email, $name);
        $methods_tried[] = 'Basic mail()';
    }
    
    // Prepare response
    if ($result['success']) {
        $response = [
            'status' => 'success',
            'message' => 'Thank you ' . htmlspecialchars($name) . '! Your message has been sent successfully. We will get back to you within 24 hours.',
            'method' => $result['method'] ?? 'Unknown',
            'timestamp' => date('c')
        ];
        
        // Log successful submission for monitoring
        error_log("VERTEX CONTACT SUCCESS - Method: " . ($result['method'] ?? 'Unknown') . " - From: $email - Subject: $subject - IP: " . $_SERVER['REMOTE_ADDR']);
        
    } else {
        $response = [
            'status' => 'error',
            'message' => 'We apologize, but there was a technical issue sending your message. Please try again in a few minutes or contact us directly at ' . RECIPIENT_EMAIL . '.',
            'debug' => 'Methods tried: ' . implode(', ', $methods_tried) . '. Last error: ' . ($result['error'] ?? 'Unknown'),
            'timestamp' => date('c')
        ];
        
        // Log error for server monitoring
        error_log("VERTEX CONTACT ERROR - Methods: " . implode(', ', $methods_tried) . " - Error: " . ($result['error'] ?? 'Unknown') . " - From: $email - IP: " . $_SERVER['REMOTE_ADDR']);
    }
    
} else {
    $response = [
        'status' => 'error',
        'message' => 'Please correct the following: ' . implode(', ', $errors),
        'errors' => $errors,
        'timestamp' => date('c')
    ];
}

// Return response based on request type
if ($isAjax) {
    echo json_encode($response);
} else {
    // For regular form submission without AJAX
    session_start();
    $_SESSION['form_response'] = $response;
    $redirect_url = ($response['status'] === 'success') ? SUCCESS_REDIRECT : ERROR_REDIRECT;
    header("Location: $redirect_url");
}

exit;
?>