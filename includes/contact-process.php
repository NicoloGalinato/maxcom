<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if ($_POST) {
    $database = new Database();
    $conn = $database->getConnection();
    
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $service = sanitize($_POST['service']);
    $message = sanitize($_POST['message']);
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $stmt = $conn->prepare("INSERT INTO contact_submissions (name, email, phone, service_interested, message, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$name, $email, $phone, $service, $message, $ip_address])) {
        // Send email notification
        $to = "your-email@domain.com";
        $subject = "New Contact Form Submission";
        $body = "Name: $name\nEmail: $email\nPhone: $phone\nService: $service\nMessage: $message";
        mail($to, $subject, $body);
        
        echo "success";
    } else {
        echo "error";
    }
}
?>