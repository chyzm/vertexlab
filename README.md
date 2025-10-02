# Vertex Labs Contact Form - Production Ready

A professional contact form solution for the Vertex Labs website with PHP email functionality using environment variables for secure configuration.

## Files Included

- **`index.html`** - Main website with contact form
- **`send_email_production.php`** - Production-ready PHP email handler
- **`email_config.php`** - Configuration loader (reads from .env)
- **`env_loader.php`** - Environment variable parser
- **`.env`** - Your email configuration (keep secure!)
- **`.env.example`** - Example configuration file
- **`.gitignore`** - Excludes sensitive files from version control
- **`img/`** - Website images folder

## Quick Setup for Live Server

### 1. Configure Your Email Settings

Edit your `.env` file with your actual email settings:
```env

```

### 2. Upload to Your Web Server

Upload all files to your web hosting:
- Via FTP, cPanel File Manager, or hosting control panel
- Ensure PHP is enabled (most hosting providers have this by default)
- Set proper file permissions (644 for files, 755 for directories)

### 3. Test the Contact Form

1. Open your website in a browser
2. Navigate to the contact section
3. Fill out and submit the form
4. Check your email (suavedef@gmail.com) for the message

## Features

✅ **Production-Ready** - Multiple email methods with automatic fallback  
✅ **Security Enhanced** - Input validation, spam protection, rate limiting  
✅ **Professional Design** - Beautiful HTML email templates  
✅ **Mobile Responsive** - Works perfectly on all devices  
✅ **Error Handling** - Comprehensive logging and debugging  
✅ **AJAX Submission** - Form submits without page reload  
✅ **Environment Variables** - Secure credential management  

## How It Works

The contact form uses a sophisticated multi-method approach:

1. **Primary Method**: Gmail SMTP (if configured in .env)
2. **Fallback 1**: Enhanced PHP mail() with optimized headers
3. **Fallback 2**: Basic PHP mail() for maximum compatibility

This ensures your contact form works on virtually any web hosting provider.

## Email Methods Supported

- **Gmail SMTP** - Direct SMTP authentication with Gmail
- **Enhanced mail()** - PHP mail() with delivery optimization
- **Basic mail()** - Standard PHP mail() as final fallback

## Server Compatibility

✅ **Shared Hosting** - Works with basic PHP hosting (GoDaddy, Bluehost, etc.)  
✅ **VPS/Cloud** - Full featured on private servers (AWS, DigitalOcean)  
✅ **cPanel Hosting** - Compatible with standard hosting panels  
✅ **WordPress Hosting** - Works alongside WordPress installations  

## Security Features

- **Input Validation** - All form fields validated and sanitized
- **Spam Protection** - Keyword filtering and content analysis
- **Rate Limiting** - Prevents abuse (1 submission per minute per IP)
- **XSS Protection** - All output properly escaped
- **Environment Variables** - Sensitive data stored securely

## Email Features

- **Professional Templates** - Branded, mobile-responsive HTML emails
- **Complete Metadata** - IP address, timestamp, user agent tracking
- **Delivery Optimization** - Headers optimized for inbox delivery
- **Auto-Reply Ready** - Easy to extend with confirmation emails

## Troubleshooting

### Form Not Sending Emails

1. **Check PHP Configuration**
   - Verify PHP mail() function is enabled
   - Check server error logs for PHP errors

2. **Verify Email Settings**
   - Confirm RECIPIENT_EMAIL is correct in .env
   - Test SMTP credentials if using Gmail

3. **Check Spam Folders**
   - Emails might be filtered as spam initially
   - Add your domain to email whitelist

4. **Contact Hosting Support**
   - Ask them to verify mail() function is working
   - Request any specific SMTP requirements

### Common Issues

- **500 Error**: Usually a PHP syntax error, check error logs
- **Form Submits but No Email**: mail() function disabled or misconfigured
- **Gmail SMTP Fails**: Check app password, not regular password

## Production Deployment Checklist

- [ ] Update .env with production email settings
- [ ] Upload all files to web server
- [ ] Test contact form submission
- [ ] Verify email delivery
- [ ] Check server error logs
- [ ] Test from different devices/browsers
- [ ] Monitor initial submissions

## Support

For technical issues:
1. Check your hosting provider's PHP/mail documentation
2. Review server error logs
3. Test with a simple PHP mail script first
4. Contact hosting support for mail server configuration

The script includes comprehensive error logging to help diagnose any issues.