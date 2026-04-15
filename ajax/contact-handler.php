<?php
header('Content-Type: application/json');

function get_input_data(): array {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw ?: '', true);
        return is_array($decoded) ? $decoded : [];
    }
    return $_POST;
}

function respond_json(int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond_json(405, ['ok' => false, 'message' => 'Method not allowed.']);
}

$input = get_input_data();

$name = trim((string)($input['name'] ?? ''));
$email = trim((string)($input['email'] ?? ''));
$subject = trim((string)($input['subject'] ?? ''));
$phone = trim((string)($input['phone'] ?? ''));
$message = trim((string)($input['details'] ?? ''));
$website = trim((string)($input['website'] ?? ''));
$startedAt = (int)($input['form_started_at'] ?? 0);
$mathNum1 = (int)($input['math_num_1'] ?? 0);
$mathNum2 = (int)($input['math_num_2'] ?? 0);
$mathAnswer = (int)($input['math_answer'] ?? PHP_INT_MIN);

$errors = [];

if ($website !== '') {
    respond_json(400, ['ok' => false, 'message' => 'Bot validation failed.']);
}

if ($startedAt > 0) {
    $elapsedMs = (int)(round(microtime(true) * 1000) - $startedAt);
    if ($elapsedMs < 3000) {
        respond_json(400, ['ok' => false, 'message' => 'Please wait a few seconds before submitting.']);
    }
}

if ($mathAnswer !== ($mathNum1 + $mathNum2)) {
    $errors[] = 'Verification answer is incorrect.';
}
if ($name === '') {
    $errors[] = 'Name is required.';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email address is required.';
}
if ($subject === '') {
    $errors[] = 'Subject is required.';
}
if ($message === '') {
    $errors[] = 'Message is required.';
}

if (!empty($errors)) {
    respond_json(422, ['ok' => false, 'message' => implode(' ', $errors)]);
}

$to = "jim.jacob@acesoft.ca";
$safeSubject = preg_replace('/[\r\n]+/', ' ', $subject);
$safeName = preg_replace('/[\r\n]+/', ' ', $name);
$safeEmail = preg_replace('/[\r\n]+/', '', $email);

$body = "Name: {$name}\n"
    . "Email: {$email}\n"
    . "Phone: {$phone}\n"
    . "Subject: {$subject}\n"
    . "Message: {$message}\n";
$headers = "From: {$safeName} <{$safeEmail}>\r\n"
    . "Reply-To: {$safeEmail}\r\n";

if (!mail($to, $safeSubject, $body, $headers)) {
    respond_json(500, ['ok' => false, 'message' => 'Failed to send email. Please try again later.']);
}

respond_json(200, ['ok' => true, 'message' => 'Submitted successfully.']);
?>