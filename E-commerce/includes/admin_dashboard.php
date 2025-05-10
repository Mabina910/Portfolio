<?php
session_start();
require_once 'db_connect.php'; // Make sure this path is correct

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../html/login.html");
    exit();
}

// Get counts for dashboard
$total_customers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];

$admin_name = $_SESSION['admin_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BotaniQ - Admin Dashboard</title>
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
            position: relative;
        }
        .logo{
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
        .sidebar-menu a:hover {
            background-color: #20674b;
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
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .dashboard-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .sidebar-menu img {
        width: 20px;
        height: 20px;
        object-fit: contain;
    }
    
    .card-header img {
        width: 24px;
        height: 24px;
        object-fit: contain;
    }
  
    </style>
</head>
<body>
    <div class="admin-dashboard">
        <aside class="sidebar">
                <div class="logo">
                    <img src="../image/BotaniQ.svg" alt="BotaniQ" id="LOGO" >
                </div>
            <div class="admin-greeting">
                Hi, <?php echo htmlspecialchars($admin_name); ?>
            </div>
            <ul class="sidebar-menu">
                <li><a href="#dashboard">
                    <span><img src="../icon/dashboard.png"></span> 
                    Dashboard
                </a></li>
                <li><a href="admin_product.php">
                    <span><img src="../icon/product.png"></span> 
                    Products
                </a></li>
                <li><a href="#orders">
                    <span><img src="../icon/order.png"></span> 
                    Orders
                </a></li>
                <li><a href="admin_customers.php">
                    <span><img src="../icon/customer.png"></span> 
                    Customers
                </a></li>
                <li><a href="#settings">
                    <span><img src="../icon/settingssssss.png"></span> 
                    Settings
                </a></li>
                <li>
                    <form action="logout.php" method="post">
                        <button type="submit" class="logout-btn">Logout</button>
                    </form>
                </li>
            </ul>
        </aside>
        <main class="main-content">
            <h1>Admin Dashboard</h1>
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Total Sales</h3>
                        <span><img src="../icon/sales.png"></span>
                    </div>
                    <p style="font-size: 24px; font-weight: bold; color: #20674b;">NPR 75,000</p>
                    <p style="color: #666;">+12% from last month</p>
                </div>
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Total Orders</h3>
                        <span><img src="../icon/order.png"></span>
                    </div>
                    <p style="font-size: 24px; font-weight: bold; color: #20674b;">245</p>
                    <p style="color: #666;">+5 from last week</p>
                </div>
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>New Customers</h3>
                        <span><img src="../icon/new-costumer.png"></span>
                    </div>
                    <p style="font-size: 24px; font-weight: bold; color: #20674b;">37</p>
                    <p style="color: #666;">+8 from last week</p>
                </div>
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Inventory</h3>
                        <span><img src="../icon/inventory.png"></span>
                    </div>
                    <p style="font-size: 24px; font-weight: bold; color: #20674b;">128</p>
                    <p style="color: #666;">Products in stock</p>
                </div>
                <div class="dashboard-card">
        <div class="card-header">
            <h3>Total Customers</h3>
            <span><img src="../icon/customer.png"></span>
        </div>
        <p style="font-size: 24px; font-weight: bold; color: #20674b;"><?php echo $total_customers; ?></p>
        <p style="color: #666;">Registered users</p>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3>Total Products</h3>
            <span><img src="../icon/product.png"></span>
        </div>
        <p style="font-size: 24px; font-weight: bold; color: #20674b;"><?php echo $total_products; ?></p>
        <p style="color: #666;">Available products</p>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3>Total Orders</h3>
            <span><img src="../icon/order.png"></span>
        </div>
        <p style="font-size: 24px; font-weight: bold; color: #20674b;"><?php echo $total_orders; ?></p>
        <p style="color: #666;">Completed orders</p>
    </div>
            </div>
        </main>
    </div>
</body>
</html>