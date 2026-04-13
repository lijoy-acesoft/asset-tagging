<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    exit;
}

/**
 * Accept both JSON (current AJAX form) and form-urlencoded payloads.
 */
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$isJson = stripos($contentType, 'application/json') !== false;

$payload = [];
if ($isJson) {
    $rawBody = file_get_contents('php://input');
    $decoded = json_decode($rawBody ?: '', true);
    if (is_array($decoded)) {
        $payload = $decoded;
    }
} else {
    $payload = $_POST;
}

$name = trim((string)($payload['name'] ?? ''));
$email = trim((string)($payload['email'] ?? ''));
$subject = trim((string)($payload['subject'] ?? ''));
$phone = trim((string)($payload['phone'] ?? ''));
$details = trim((string)($payload['details'] ?? ($payload['message'] ?? '')));

$errors = [];
if ($name === '') {
    $errors[] = 'Name is required';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required';
}
if ($subject === '') {
    $errors[] = 'Subject is required';
}
if ($phone === '') {
    $errors[] = 'Phone is required';
}
if ($details === '') {
    $errors[] = 'Details are required';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'message' => implode(' | ', $errors),
    ]);
    exit;
}

$to = 'lijoy.oommen@acesoft.ca';
$mailSubject = "Website Inquiry: {$subject}";
$body = "Name: {$name}\n"
    . "Email: {$email}\n"
    . "Phone: {$phone}\n"
    . "Subject: {$subject}\n\n"
    . "Details:\n{$details}\n";

$safeReplyTo = str_replace(["\r", "\n"], '', $email);
$headers = "From: no-reply@acesoft.ca\r\n"
    . "Reply-To: {$safeReplyTo}\r\n"
    . "X-Mailer: PHP/" . phpversion();

$sent = mail($to, $mailSubject, $body, $headers);

if (!$sent) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'Failed to send email. Please try again later.',
    ]);
    exit;
}

echo json_encode([
    'ok' => true,
    'message' => 'Submitted successfully. We will contact you shortly.',
]);
