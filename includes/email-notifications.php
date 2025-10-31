<?php
require_once 'config/database.php';

class EmailNotifications {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Send contact form notification
     */
    public function sendContactNotification($submission_id) {
        $stmt = $this->conn->prepare("SELECT * FROM contact_submissions WHERE id = ?");
        $stmt->execute([$submission_id]);
        $submission = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$submission) return false;
        
        $to = "admin@elitesportsmanagement.com"; // Change this to your email
        $subject = "New Contact Form Submission - Sports Management";
        
        $message = "
        <html>
        <head>
            <title>New Contact Form Submission</title>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9fafb; }
                .field { margin-bottom: 10px; }
                .label { font-weight: bold; color: #374151; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>New Contact Form Submission</h1>
                </div>
                <div class='content'>
                    <div class='field'>
                        <span class='label'>Name:</span> {$submission['name']}
                    </div>
                    <div class='field'>
                        <span class='label'>Email:</span> {$submission['email']}
                    </div>
                    <div class='field'>
                        <span class='label'>Phone:</span> {$submission['phone']}
                    </div>
                    <div class='field'>
                        <span class='label'>Service Interested:</span> {$submission['service_interested']}
                    </div>
                    <div class='field'>
                        <span class='label'>Message:</span><br>
                        {$submission['message']}
                    </div>
                    <div class='field'>
                        <span class='label'>Submitted:</span> {$submission['submitted_at']}
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Sports Management <noreply@elitesportsmanagement.com>" . "\r\n";
        
        return mail($to, $subject, $message, $headers);
    }
    
    /**
     * Send blog post notification to subscribers
     */
    public function sendNewPostNotification($post_id, $subscribers = []) {
        $stmt = $this->conn->prepare("SELECT * FROM blog_posts WHERE id = ?");
        $stmt->execute([$post_id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$post) return false;
        
        $subject = "New Blog Post: {$post['title']}";
        
        $message = "
        <html>
        <head>
            <title>New Blog Post</title>
        </head>
        <body>
            <h2>{$post['title']}</h2>
            <p>{$post['excerpt']}</p>
            <p><a href='https://yourdomain.com/blog-post.php?slug={$post['slug']}'>Read More</a></p>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Sports Management Blog <blog@elitesportsmanagement.com>" . "\r\n";
        
        $sent_count = 0;
        foreach ($subscribers as $email) {
            if (mail($email, $subject, $message, $headers)) {
                $sent_count++;
            }
        }
        
        return $sent_count;
    }
    
    /**
     * Send password reset email
     */
    public function sendPasswordReset($user_email, $reset_token) {
        $reset_link = "https://yourdomain.com/admin/reset-password.php?token=$reset_token";
        
        $subject = "Password Reset Request - Sports Management";
        
        $message = "
        <html>
        <head>
            <title>Password Reset</title>
        </head>
        <body>
            <h2>Password Reset Request</h2>
            <p>You requested a password reset for your Sports Management CMS account.</p>
            <p><a href='$reset_link'>Click here to reset your password</a></p>
            <p>This link will expire in 1 hour.</p>
            <p>If you didn't request this, please ignore this email.</p>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Sports Management <noreply@elitesportsmanagement.com>" . "\r\n";
        
        return mail($user_email, $subject, $message, $headers);
    }
}
?>