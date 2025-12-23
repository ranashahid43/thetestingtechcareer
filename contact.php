<?php
// contact.php - Secure Contact Form Handler

// Start session for CSRF
session_start();

// Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Content-Type: application/json; charset=UTF-8');

// Generate or reuse CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Helper Functions
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Response Array
$response = ['success' => false, 'message' => '', 'errors' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $response['message'] = 'Invalid security token. Please refresh the page.';
        echo json_encode($response);
        exit;
    }

    // Get and Sanitize Data
    $first_name = sanitize($_POST['first_name'] ?? '');
    $last_name  = sanitize($_POST['last_name'] ?? '');
    $email      = $_POST['email'] ?? '';
    $phone      = sanitize($_POST['phone'] ?? '');
    $message    = sanitize($_POST['message'] ?? '');

    // Validation
    if (empty($first_name) || strlen($first_name) < 2) {
        $response['errors'][] = 'First name must be at least 2 characters.';
    }
    if (empty($last_name) || strlen($last_name) < 2) {
        $response['errors'][] = 'Last name must be at least 2 characters.';
    }
    if (!validateEmail($email)) {
        $response['errors'][] = 'Please enter a valid email address.';
    }
    if (!empty($phone) && !preg_match('/^[+0-9\s\-\(\)]{8,20}$/', $phone)) {
        $response['errors'][] = 'Invalid phone number format.';
    }
    if (empty($message) || strlen($message) < 20) {
        $response['errors'][] = 'Message must be at least 20 characters.';
    }

    // If no errors, process the form
    if (empty($response['errors'])) {
        // HERE YOU CAN SEND EMAIL OR SAVE TO DATABASE
        // Example: Send email (uncomment and configure)
        /*
        $to      = "info@thetestingtech.com";
        $subject = "New Contact Form Submission";
        $body    = "Name: $first_name $last_name\nEmail: $email\nPhone: $phone\n\nMessage:\n$message";
        $headers = "From: $email\r\nReply-To: $email\r\n";
        mail($to, $subject, $body, $headers);
        */

        $response['success'] = true;
        $response['message'] = 'Thank you! Your message has been sent successfully.';
    } else {
        $response['message'] = 'Please correct the errors below.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

// Send JSON response
echo json_encode($response);
exit;