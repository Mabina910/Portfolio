<?php
session_start();
require_once 'db_connect.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->error);
}

$email = $_POST['email'];
$password = $_POST['password'];
$userType = $_POST['userType'];

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../html/login.html?error=invalid_email");
    exit();
}

if ($userType === 'admin') {
    // Admin login process
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
            // Set session variables
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['name'];
            
            // Redirect to admin dashboard
            header("Location:admin_dashboard.php");
            exit();
        } else {
            // Invalid password
            $_SESSION['login_error'] = "Invalid email or password";
            header("Location: ../html/login.html");
            exit();
        }
    }
} elseif ($userType === 'user') {
    // User login process
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
            $_SESSION['id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['logged_in'] = true;
            $_SESSION['is_admin'] = false;
            
            header("Location: e-commerce.php");
            exit();
        }
    }
}

// If we get here, login failed
header("Location: ../html/login.html?error=invalid_credentials");
exit();

$stmt->close();
$conn->close();
?>