<?php
session_start();
include("../config/db.php");

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$category = trim($_POST['category'] ?? 'General');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $message === '') {
    $_SESSION['feedback_error'] = 'Please complete all required fields.';
    header("Location: ../pages/contact.php");
    exit;
}

$conn->query("CREATE TABLE IF NOT EXISTS feedbacks (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(60) DEFAULT 'General',
    name VARCHAR(120) NOT NULL,
    email VARCHAR(120) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new','reviewed','resolved') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$stmt = $conn->prepare("INSERT INTO feedbacks (category, name, email, message) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $category, $name, $email, $message);

if ($stmt->execute()) {
    $_SESSION['feedback_success'] = 'Thanks! Your feedback has been submitted.';
} else {
    $_SESSION['feedback_error'] = 'Unable to submit feedback right now. Please try again.';
}

header("Location: ../pages/contact.php");
exit;
