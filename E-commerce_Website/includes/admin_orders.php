<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../pages/auth/login.html");
    exit();
}

$recent_orders = [];
$orders_result = $conn->query("SELECT o.id, o.total_amount, o.status, o.payment_method, o.shipping_city, o.shipping_country, u.full_name
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.id DESC");

if ($orders_result) {
    while ($row = $orders_result->fetch_assoc()) {
        $recent_orders[] = $row;
    }
}

$admin_name = $_SESSION['admin_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BotaniQ - Orders</title>
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
            overflow-x: auto;
        }
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }
        .orders-table th,
        .orders-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e6e6e6;
            text-align: left;
            white-space: nowrap;
        }
        .orders-table th {
            background-color: #163020;
            color: #fff;
        }
        .status-pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }
        .status-completed {
            background: #d8f3dc;
            color: #1b4332;
        }
        .status-pending {
            background: #fff3cd;
            color: #7a5a00;
        }
        .status-failed {
            background: #f8d7da;
            color: #842029;
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
                <li><a href="admin_orders.php" class="active"><span><img src="../icon/order.png"></span>Orders</a></li>
                <li><a href="admin_customers.php"><span><img src="../icon/customer.png"></span>Customers</a></li>
                <li><a href="admin_settings.php"><span><img src="../icon/settings.png"></span>Settings</a></li>
                <li>
                    <form action="logout.php" method="post">
                        <button type="submit" class="logout-btn">Logout</button>
                    </form>
                </li>
            </ul>
        </aside>
        <main class="main-content">
            <h1>Orders</h1>
            <div class="content-panel">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent_orders) === 0): ?>
                            <tr><td colspan="6">No orders found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recent_orders as $order): ?>
                                <?php
                                    $status = strtolower((string)$order['status']);
                                    $status_class = 'status-pending';
                                    if ($status === 'completed') {
                                        $status_class = 'status-completed';
                                    } elseif ($status === 'failed') {
                                        $status_class = 'status-failed';
                                    }
                                ?>
                                <tr>
                                    <td>#<?php echo (int)$order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['full_name'] ?? 'Unknown'); ?></td>
                                    <td>NPR <?php echo number_format((float)$order['total_amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst((string)$order['payment_method'])); ?></td>
                                    <td><span class="status-pill <?php echo $status_class; ?>"><?php echo htmlspecialchars($order['status']); ?></span></td>
                                    <td><?php echo htmlspecialchars(trim(($order['shipping_city'] ?? '') . ', ' . ($order['shipping_country'] ?? ''), ', ')); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
