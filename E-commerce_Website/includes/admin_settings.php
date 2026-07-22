<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../pages/auth/login.html");
    exit();
}

$admin_settings = [
    'name' => $_SESSION['admin_name'] ?? 'Admin',
    'email' => 'Not available',
    'id' => $_SESSION['admin_id'] ?? 'N/A'
];

if (!empty($_SESSION['admin_id'])) {
    $settings_stmt = $conn->prepare("SELECT admin_id, name, email FROM admins WHERE admin_id = ? LIMIT 1");
    if ($settings_stmt) {
        $settings_stmt->bind_param("i", $_SESSION['admin_id']);
        $settings_stmt->execute();
        $settings_result = $settings_stmt->get_result();
        if ($settings_result && $settings_result->num_rows > 0) {
            $row = $settings_result->fetch_assoc();
            $admin_settings['name'] = $row['name'];
            $admin_settings['email'] = $row['email'];
            $admin_settings['id'] = $row['admin_id'];
        }
        $settings_stmt->close();
    }
}

$admin_name = $_SESSION['admin_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BotaniQ - Settings</title>
    <link rel="stylesheet" href="../css/design.css">
    <style>
        .admin-dashboard {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: #163020;
            color: white;
            padding: 20px;
        }
        .logo {
            background-color: #163020;
            align-items: center;
        }
        .admin-greeting {
            text-align: center;
            color: white;
            margin-bottom: 20px;
            font-size: 18px;
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
        }
        .sidebar-menu li {
            margin-bottom: 15px;
        }
        .sidebar-menu a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: #20674b;
        }
        .sidebar-menu img {
            width: 20px;
            height: 20px;
            object-fit: contain;
        }
        .logout-btn {
            width: 100%;
            background-color: #B6C4B6;
            color: #163020;
            border: none;
            padding: 10px;
            margin-top: 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .main-content {
            flex-grow: 1;
            padding: 20px;
            background-color: #EEF0E5;
        }
        .content-panel {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
        }
        .setting-item {
            background: #f7faf7;
            border: 1px solid #d6e2d9;
            border-radius: 8px;
            padding: 14px;
        }
        .setting-label {
            font-size: 12px;
            color: #4f4f4f;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .setting-value {
            font-size: 16px;
            color: #163020;
            font-weight: 600;
            word-break: break-word;
        }
    </style>
</head>
<body>
    <div class="admin-dashboard">
        <aside class="sidebar">
            <div class="logo">
                <img src="../image/BotaniQ.svg" alt="BotaniQ" id="LOGO">
            </div>
            <div class="admin-greeting">Hi, <?php echo htmlspecialchars($admin_name); ?></div>
            <ul class="sidebar-menu">
                <li><a href="admin_dashboard.php"><span><img src="../icon/dashboard.png"></span>Dashboard</a></li>
                <li><a href="admin_product.php"><span><img src="../icon/product.png"></span>Products</a></li>
                <li><a href="admin_orders.php"><span><img src="../icon/order.png"></span>Orders</a></li>
                <li><a href="admin_customers.php"><span><img src="../icon/customer.png"></span>Customers</a></li>
                <li><a href="admin_settings.php" class="active"><span><img src="../icon/settings.png"></span>Settings</a></li>
                <li>
                    <form action="logout.php" method="post">
                        <button type="submit" class="logout-btn">Logout</button>
                    </form>
                </li>
            </ul>
        </aside>
        <main class="main-content">
            <h1>Settings</h1>
            <div class="content-panel">
                <div class="settings-grid">
                    <div class="setting-item">
                        <div class="setting-label">Admin ID</div>
                        <div class="setting-value"><?php echo htmlspecialchars((string)$admin_settings['id']); ?></div>
                    </div>
                    <div class="setting-item">
                        <div class="setting-label">Display Name</div>
                        <div class="setting-value"><?php echo htmlspecialchars($admin_settings['name']); ?></div>
                    </div>
                    <div class="setting-item">
                        <div class="setting-label">Email</div>
                        <div class="setting-value"><?php echo htmlspecialchars($admin_settings['email']); ?></div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
