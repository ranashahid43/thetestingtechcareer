<?php
// careers.php - Secure Careers Spontaneous Application Handler

session_start();

// Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Content-Type: application/json; charset=UTF-8');

// CSRF Token
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

// Response
$response = ['success' => false, 'message' => '', 'errors' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $response['message'] = 'Invalid security token. Please refresh the page.';
        echo json_encode($response);
        exit;
    }

    // Sanitize Text Inputs
    $first_name    = sanitize($_POST['first_name'] ?? '');
    $last_name     = sanitize($_POST['last_name'] ?? '');
    $email         = $_POST['email'] ?? '';
    $phone         = sanitize($_POST['phone'] ?? '');
    $position      = sanitize($_POST['position'] ?? '');
    $cover_letter  = sanitize($_POST['cover_letter'] ?? '');

    // Text Validation
    if (empty($first_name) || strlen($first_name) < 2) {
        $response['errors'][] = 'First name must be at least 2 characters.';
    }
    if (empty($last_name) || strlen($last_name) < 2) {
        $response['errors'][] = 'Last name must be at least 2 characters.';
    }
    if (!validateEmail($email)) {
        $response['errors'][] = 'Please enter a valid email address.';
    }
    if (empty($phone) || !preg_match('/^[+0-9\s\-\(\)]{8,20}$/', $phone)) {
        $response['errors'][] = 'Valid phone number is required.';
    }
    if (empty($position) || strlen($position) < 5) {
        $response['errors'][] = 'Position interested in must be specified.';
    }
    if (empty($cover_letter) || strlen($cover_letter) < 50) {
        $response['errors'][] = 'Cover letter must be at least 50 characters.';
    }

    // File Validation (CV is required)
    if (!isset($_FILES['cv']) || $_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
        $response['errors'][] = 'CV (PDF) is required.';
    } else {
        $cv = $_FILES['cv'];
        $allowed_types = ['application/pdf'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($cv['type'], $allowed_types)) {
            $response['errors'][] = 'CV must be a PDF file.';
        }
        if ($cv['size'] > $max_size) {
            $response['errors'][] = 'CV file size must not exceed 5MB.';
        }
    }

    // Optional Files (Portfolio & Video Intro)
    // You can add similar validation if needed

    // If no errors, process the application
    if (empty($response['errors'])) {
        // HERE: Save files or send email
        // Example: Move CV to uploads folder (create 'uploads/' folder first)
        /*
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $cvPath = $uploadDir . time() . '_' . basename($cv['name']);
        move_uploaded_file($cv['tmp_name'], $cvPath);
        */

        // Example: Send email notification
        /*
        $to = "careers@thetestingtech.com";
        $subject = "New Spontaneous Application: " . $position;
        $body = "Name: $first_name $last_name\nEmail: $email\nPhone: $phone\nPosition: $position\n\nCover Letter:\n$cover_letter";
        mail($to, $subject, $body);
        */

        $response['success'] = true;
        $response['message'] = 'Thank you! Your application has been submitted successfully. We will contact you soon.';
    } else {
        $response['message'] = 'Please correct the errors below.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
exit;