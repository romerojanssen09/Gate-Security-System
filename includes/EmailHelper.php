<?php
/**
 * Email Helper Class
 * Handles sending email notifications using PHPMailer
 */

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../storage/database.php';

class EmailHelper {
    private $mailer;
    private $config;
    private $enabled;
    
    public function __construct() {
        $this->loadConfig();
        $this->enabled = (bool)($this->config['enabled'] ?? false);
        
        if ($this->enabled) {
            $this->mailer = new PHPMailer(true);
            $this->configureMailer();
        }
    }
    
    private function loadConfig() {
        // Load email configuration from config file
        $configFile = __DIR__ . '/../config/email.php';
        if (file_exists($configFile)) {
            $this->config = require $configFile;
        } else {
            // Fallback to default config
            $this->config = [
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'smtp_encryption' => 'tls',
                'smtp_username' => '',
                'smtp_password' => '',
                'from_email' => 'noreply@holyfamily.edu.ph',
                'from_name' => 'Holy Family Gate Security',
                'enabled' => false
            ];
        }
    }
    
    private function configureMailer() {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['smtp_host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['smtp_username'];
            $this->mailer->Password = $this->config['smtp_password'];
            $this->mailer->SMTPSecure = $this->config['smtp_encryption'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = (int)$this->config['smtp_port'];
            
            // Sender
            $this->mailer->setFrom(
                $this->config['from_email'],
                $this->config['from_name']
            );
            
            // Encoding
            $this->mailer->CharSet = 'UTF-8';
        } catch (Exception $e) {
            error_log("Email configuration error: " . $e->getMessage());
        }
    }
    
    /**
     * Send card ready notification to user
     */
    public function sendCardReadyNotification($email, $fullName, $rfidId) {
        if (!$this->enabled) {
            return false;
        }
        
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $fullName);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Your RFID Card is Ready for Pickup';
            
            $this->mailer->Body = $this->getCardReadyTemplate($fullName, $rfidId);
            $this->mailer->AltBody = strip_tags($this->getCardReadyTemplate($fullName, $rfidId));
            
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send card rejection notification to user
     */
    public function sendCardRejectionNotification($email, $fullName, $reason) {
        if (!$this->enabled) {
            return false;
        }
        
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $fullName);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'RFID Card Registration Update';
            
            $this->mailer->Body = $this->getCardRejectionTemplate($fullName, $reason);
            $this->mailer->AltBody = strip_tags($this->getCardRejectionTemplate($fullName, $reason));
            
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending error: " . $e->getMessage());
            return false;
        }
    }
    
    private function getCardReadyTemplate($fullName, $rfidId) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #001F4D 0%, #003366 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .card-info { background: white; padding: 20px; border-left: 4px solid #28a745; margin: 20px 0; border-radius: 5px; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
                .btn { display: inline-block; padding: 12px 30px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🎉 Your RFID Card is Ready!</h1>
                </div>
                <div class='content'>
                    <p>Dear <strong>{$fullName}</strong>,</p>
                    
                    <p>Great news! Your RFID card registration has been approved and your card is now ready for pickup.</p>
                    
                    <div class='card-info'>
                        <h3>Card Details:</h3>
                        <p><strong>RFID ID:</strong> {$rfidId}</p>
                        <p><strong>Status:</strong> <span style='color: #28a745;'>Active</span></p>
                    </div>
                    
                    <p><strong>Next Steps:</strong></p>
                    <ol>
                        <li>Visit the security office during office hours</li>
                        <li>Bring a valid ID for verification</li>
                        <li>Collect your RFID card</li>
                    </ol>
                    
                    <p>Once you have your card, you can use it to access the gate. Simply scan your card at the gate entrance and exit.</p>
                    
                    <p>If you have any questions, please contact the security office.</p>
                    
                    <p>Best regards,<br><strong>Holy Family High School<br>Gate Security Team</strong></p>
                </div>
                <div class='footer'>
                    <p>This is an automated message. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getCardRejectionTemplate($fullName, $reason) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .reason-box { background: white; padding: 20px; border-left: 4px solid #dc3545; margin: 20px 0; border-radius: 5px; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
                .btn { display: inline-block; padding: 12px 30px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>RFID Card Registration Update</h1>
                </div>
                <div class='content'>
                    <p>Dear <strong>{$fullName}</strong>,</p>
                    
                    <p>Thank you for your interest in obtaining an RFID card for gate access. After reviewing your application, we regret to inform you that your registration request has not been approved at this time.</p>
                    
                    <div class='reason-box'>
                        <h3>Reason:</h3>
                        <p>{$reason}</p>
                    </div>
                    
                    <p>If you believe this decision was made in error or if you would like to discuss this further, please contact the security office during office hours.</p>
                    
                    <p>You may also submit a new registration request after addressing the concerns mentioned above.</p>
                    
                    <p>Best regards,<br><strong>Holy Family High School<br>Gate Security Team</strong></p>
                </div>
                <div class='footer'>
                    <p>This is an automated message. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
