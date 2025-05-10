<?php
require_once 'db_connect.php';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "botaniq";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$full_name = $_POST['full_name'];
$phone_number = $_POST['phone_number'];
$email = $_POST['email'];
$gender = $_POST['gender'];
$dob = $_POST['year'] . '-' . $_POST['month'] . '-' . $_POST['day'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); 

$check_email = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($check_email);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {

    header("Location: signup.html?error=email_exists");
    exit();
} else {

    $sql = "INSERT INTO users (full_name, phone_number, email, gender, date_of_birth, password) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $full_name, $phone_number, $email, $gender, $dob, $password);
    
    if ($stmt->execute()) {
        header("Location: ../html/login.html?signup=success");
        exit();
    } else {
        header("Location: ../html/signup.html?error=registration_failed");
        exit();
    }
}

$stmt->close();
$conn->close();
?>

