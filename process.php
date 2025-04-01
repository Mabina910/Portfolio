<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ensure PHPMailer is installed via Composer

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
file_put_contents("mail_error_log.txt", "Processing form submission...\n", FILE_APPEND);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input data
    $firstName = htmlspecialchars(trim($_POST["FirstName"]));
    $lastName = htmlspecialchars(trim($_POST["LastName"]));
    $email = htmlspecialchars(trim($_POST["email"]));
    $message = htmlspecialchars(trim($_POST["message"]));
    $country = htmlspecialchars(trim($_POST["country"]));

    // Basic validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($message) || empty($country)) {
        echo "error: Missing fields";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "error: Invalid email format";
        exit;
    }

    // Setup PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->SMTPDebug = 4; // Change to 4 for full debugging
        $mail->Debugoutput = 'error_log';
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = "alwayslateno1@gmail.com"; // Change to your email
        $mail->Password = " iawj ttul mumb eavc"; // Use an App Password, not your actual password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Sender & Recipient
        $mail->setFrom("alwayslateno1@gmail.com", "Mabina Thapa");
        $mail->addAddress("alwayslateno1@gmail.com", "Mabina Thapa"); // Change to your email to receive messages

        // Email Content
        $mail->Subject = "New Contact Form Submission";
        $mail->Body = "Name: $firstName $lastName\nEmail: $email\nCountry: $country\n\nMessage:\n$message";

        // Send email
        if ($mail->send()) {
            echo "success";
        } else {
            echo "error: Failed to send email";
        }
    } catch (Exception $e) {
        // Log error details for debugging
        file_put_contents("mail_error_log.txt", $mail->ErrorInfo, FILE_APPEND);
        echo "error: " . $mail->ErrorInfo;
    }
}
?>
