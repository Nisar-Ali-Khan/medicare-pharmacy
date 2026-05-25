<?php
require_once 'config.php';
header('Content-Type: application/json');

$conn    = getConnection();
$name    = sanitize($conn, $_POST['name'] ?? '');
$email   = sanitize($conn, $_POST['email'] ?? '');
$message = sanitize($conn, $_POST['message'] ?? '');

if (!$name || !$email || !$message) {
    jsonResponse(['error' => 'All fields are required'], 400);
}

$conn->query("INSERT INTO contact_messages (name, email, message) VALUES ('$name', '$email', '$message')");
jsonResponse(['success' => true, 'message' => 'Message received']);
?>
