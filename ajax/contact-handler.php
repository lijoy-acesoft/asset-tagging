<?php
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $phone = $_POST['phone'];
    $message = $_POST['details'];
    
    // Validate input
    $errors = array();
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email address is required";
    }
    if (empty($subject)) {
        $errors[] = "Subject is required";
    }
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    // If no errors, send email
    if (empty($errors)) {
        $to = "jim.jacob@acesoft.ca"; // Change this to your email address
        $body = "Name: $name\nEmail: $email\nSubject: $subject\nPhone: $phone\nMessage: $message";
        $headers = "From: $name <$email>";

        // Send email
        if (mail($to, $subject, $body, $headers)) {
         //   echo "Email sent successfully! Redirecting back to Acesoft portal. We will be contacting you very soon.";
            header("Location: https://assettracking.ca/index.html");
			 exit; // Stop further execution
        } else {
            echo "Failed to send email. Please try again later.";
        }
    } else {
        // Output errors
        foreach ($errors as $error) {
            echo $error . "<br>";
        }
    }
}
?>