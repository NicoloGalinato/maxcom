<?php
// process-contact.php
require_once 'config/database.php';
require_once 'includes/functions.php';

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $service = sanitize($_POST['service']);
    $message = sanitize($_POST['message']);
    
    // Save to database
    $stmt = $conn->prepare("INSERT INTO contact_submissions (name, email, phone, service_interested, message) VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$name, $email, $phone, $service, $message])) {
        // Redirect with success message
        header('Location: contact.php?success=1');
        exit();
    } else {
        header('Location: contact.php?error=1');
        exit();
    }
} else {
    header('Location: contact.php');
    exit();
}