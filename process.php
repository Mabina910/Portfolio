<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = htmlspecialchars(trim($_POST["FirstName"]));
    $lastName = htmlspecialchars(trim($_POST["LastName"]));
    $email = htmlspecialchars(trim($_POST["email"]));
    $message = htmlspecialchars(trim($_POST["message"]));
    $country = htmlspecialchars(trim($_POST["country"]));

    $errors = [];

    if (empty($firstName)) {
        $errors[] = "First name is required.";
    }

    if (empty($lastName)) {
        $errors[] = "Last name is required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (strlen($message) < 10) {
        $errors[] = "Message must be at least 10 characters long.";
    }

    if (empty($country)) {
        $errors[] = "Please select a country.";
    }

    if (count($errors) > 0) {
        echo json_encode(["status" => "error", "message" => implode(" ", $errors)]);
        exit;
    }
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->SMTPDebug = 0; 
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = "alwayslateno1@gmail.com";
        $mail->Password = "iawj ttul mumb eavc"; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom("alwayslateno1@gmail.com", "Mabina Thapa");
        $mail->addAddress("alwayslateno1@gmail.com", "$firstName $lastName");

        $mail->Subject = "New Contact Form Submission";
        $mail->Body = "Name: $firstName $lastName\nEmail: $email\nCountry: $country\n\nMessage:\n$message";
        $mail->send();
        echo json_encode(["status" => "success", "message" => "Your message has been sent successfully!"]);
    } catch (Exception $e) {
        error_log($mail->ErrorInfo);
        echo json_encode(["status" => "error", "message" => "Message could not be sent. Mailer Error"]);
    }
}
?>
