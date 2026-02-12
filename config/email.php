<?php
/**
 * Email Configuration
 * Set your Gmail App Password and email settings here
 */

return [
    // SMTP Server Settings
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_encryption' => 'tls', // 'tls' or 'ssl'
    
    // Gmail Credentials
    'smtp_username' => 'adolfoadonis12@gmail.com',  // 👈 CHANGE THIS to your Gmail address
    'smtp_password' => 'dzwu yvqm bdjy wuko', // 👈 CHANGE THIS to your 16-character Gmail App Password
    
    // Sender Information
    'from_email' => 'noreply@holyfamily.edu.ph',
    'from_name' => 'Holy Family Gate Security',
    
    // Enable/Disable Email Notifications
    'enabled' => true, // Set to false to disable all email notifications
];

/*
 * HOW TO GET GMAIL APP PASSWORD:
 * 
 * 1. Go to: https://myaccount.google.com/security
 * 2. Enable 2-Step Verification (if not already enabled)
 * 3. Search "App passwords"
 * 4. Put a app name
 * 5. Click "Generate"
 * 6. Copy the 16-character password (remove spaces)
 * 7. Paste it in 'smtp_password' above
 * 
 * Example:
 * 'smtp_username' => 'john.doe@gmail.com',
 * 'smtp_password' => 'abcdefghijklmnop',
 */
