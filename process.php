<?php
// Set max execution time to 5 seconds
ini_set('max_execution_time', 5);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input data
    $firstName = htmlspecialchars(trim($_POST["FirstName"]));
    $lastName = htmlspecialchars(trim($_POST["LastName"]));
    $email = htmlspecialchars(trim($_POST["email"]));
    $message = htmlspecialchars(trim($_POST["message"]));
    $country = htmlspecialchars(trim($_POST["country"]));

    // Array to hold error messages
    $errors = [];

    // Validation checks

    // Check if first name is empty
    if (empty($firstName)) {
        $errors[] = "First name is required.";
    }

    // Check if last name is empty
    if (empty($lastName)) {
        $errors[] = "Last name is required.";
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Check if message is at least 10 characters long
    if (strlen($message) < 10) {
        $errors[] = "Message must be at least 10 characters long.";
    }

    // Check if country is selected
    if (empty($country)) {
        $errors[] = "Please select a country.";
    }

    // If there are errors, return the errors as a JSON response
    if (count($errors) > 0) {
        echo json_encode(["status" => "error", "message" => implode(" ", $errors)]);
        exit;
    }
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->SMTPDebug = 0; // Hide debug output
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = "alwayslateno1@gmail.com";
        $mail->Password = "iawj ttul mumb eavc"; // Use App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom("alwayslateno1@gmail.com", "Mabina Thapa");
        $mail->addAddress("alwayslateno1@gmail.com", "$firstName $lastName");

        $mail->Subject = "New Contact Form Submission";
        $mail->Body = "Name: $firstName $lastName\nEmail: $email\nCountry: $country\n\nMessage:\n$message";
        $mail->send();
        
        // Only send success response AFTER email is sent
        echo json_encode(["status" => "success", "message" => "Your message has been sent successfully!"]);
        
    } catch (Exception $e) {
        error_log($mail->ErrorInfo);
        echo json_encode(["status" => "error", "message" => "Message could not be sent. Mailer Error"]);
    }
}
?>
