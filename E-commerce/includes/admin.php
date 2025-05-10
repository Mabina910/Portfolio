<?php
session_start();

// Database connection (replace with your actual database credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "botaniq";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Query to check admin credentials
    $sql = "SELECT admin_id, name, email, password FROM admins WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        
        // Verify password (use password_verify for hashed passwords in a real-world scenario)
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
    } else {
        // No admin found
        $_SESSION['login_error'] = "Invalid email or password";
        header("Location: ../html/login.html");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>