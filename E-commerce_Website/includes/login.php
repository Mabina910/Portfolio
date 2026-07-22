<?php
session_start();
require_once 'db_connect.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = (string)($_POST['email'] ?? '');
$password = (string)($_POST['password'] ?? '');
$userType = (string)($_POST['userType'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../pages/auth/login.html?error=invalid_email");
    exit();
}

if ($userType === 'admin') {
    $sql = "SELECT admin_id, name, email, password FROM admins WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing admin query: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        die("Error executing admin query: " . $stmt->error);
    }

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        
        if ($password === $admin['password']) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['name'];
            
            header("Location: admin_dashboard.php");
            exit();
        } else {
            header("Location: ../pages/auth/login.html?error=invalid_credentials");
            exit();
        }
    }
} elseif ($userType === 'user') {
    $sql = "SELECT id, full_name, email, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing user query: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        die("Error executing user query: " . $stmt->error);
    }

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Set all user session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['logged_in'] = true;
            $_SESSION['is_admin'] = false;
            
            // Check if there's a redirect parameter
            if (isset($_GET['redirect']) && $_GET['redirect'] === 'checkout') {
                header("Location: checkout.php");
            } else {
                header("Location: ../e-commerce.php");
            }
            exit();
        } else {
            header("Location: ../pages/auth/login.html?error=invalid_credentials");
            exit();
        }
    }
}

header("Location: ../pages/auth/login.html?error=invalid_credentials");
exit();

$stmt->close();
$conn->close();
?>
