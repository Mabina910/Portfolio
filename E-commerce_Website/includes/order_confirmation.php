<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/auth/login.html");
    exit();
}

$order_id = $_GET['order_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - BotaniQ</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .confirmation-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        .confirmation-success {
            color: #28a745;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .confirmation-message {
            margin-bottom: 30px;
            color: #6c757d;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #163020;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
            margin: 5px;
        }
        .btn:hover {
            background-color: #0f2217;
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="confirmation-success">
            <h2>Order Confirmed!</h2>
        </div>
        <div class="confirmation-message">
            <p>Thank you for your order. Your order ID is: <strong><?php echo htmlspecialchars($order_id); ?></strong></p>
            <p>You will receive an email confirmation shortly.</p>
        </div>
        <div>
            <a href="e-commerce.php" class="btn">Continue Shopping</a>
            <a href="order_history.php" class="btn">View Order History</a>
        </div>
    </div>
</body>
</html>
