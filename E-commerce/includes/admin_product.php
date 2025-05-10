<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../html/login.html");
    exit();
}

// Database connection
require_once 'db_connect.php';

// Handle product addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = $conn->real_escape_string($_POST['price']);
    $category = $conn->real_escape_string($_POST['category']);
    
    // Handle image upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../image/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = basename($_FILES["image"]["name"]);
        }
    }
    
    $sql = "INSERT INTO products (name, description, price, category, image_path) 
            VALUES ('$name', '$description', '$price', '$category', '$image_path')";
    $conn->query($sql);
}

// Handle product deletion
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    // First get image path to delete file
    $result = $conn->query("SELECT image_path FROM products WHERE id = $delete_id");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (!empty($row['image_path'])) {
            $image_path = "../image/" . $row['image_path'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
    }
    // Then delete from database
    $conn->query("DELETE FROM products WHERE id = $delete_id");
    header("Location: admin_product.php");
    exit();
}

// Handle product update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    $id = (int)$_POST['id'];
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = $conn->real_escape_string($_POST['price']);
    $category = $conn->real_escape_string($_POST['category']);
    
    // Handle image update if new image is uploaded
    $image_update = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // First delete old image
        $result = $conn->query("SELECT image_path FROM products WHERE id = $id");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (!empty($row['image_path'])) {
                $old_image_path = "../image/" . $row['image_path'];
                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }
        }
        
        // Upload new image
        $target_dir = "../image/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = basename($_FILES["image"]["name"]);
            $image_update = ", image_path = '$image_path'";
        }
    }
    
    $sql = "UPDATE products SET 
            name = '$name', 
            description = '$description', 
            price = '$price', 
            category = '$category'
            $image_update
            WHERE id = $id";
    $conn->query($sql);
    header("Location: admin_product.php");
    exit();
}

// Get all products
$products = [];
$result = $conn->query("SELECT * FROM products");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

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

        .product-management {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .product-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, 
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group textarea {
            height: 100px;
        }
        .full-width {
            grid-column: span 2;
        }
        .submit-btn {
            background-color: #163020;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
        .products-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .product-item {
            background-color: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .product-item img {
            max-width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 4px;
        }
        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .edit-btn, .delete-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .edit-btn {
            background-color: #20674b;
            color: white;
        }
        .delete-btn {
            background-color: #d32f2f;
            color: white;
        }
        .content-section {
            display: flex;
            flex-direction: column;
            gap: 20px;
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
            
            <div class="content-section">
            <div class="product-management">
                <h2>Add New Product</h2>
                <form method="POST" enctype="multipart/form-data" class="product-form">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="price">Price (NPR)</label>
                        <input type="number" id="price" name="price" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="indoor">Indoor Plants</option>
                            <option value="outdoor">Outdoor Plants</option>
                            <option value="hanging">Hanging Plants</option>
                            <option value="fruit">Fruit-Bearing Plants</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="image">Product Image</label>
                        <input type="file" id="image" name="image" accept="image/*" required>
                    </div>
                    <div class="form-group full-width">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" required></textarea>
                    </div>
                    <div class="form-group full-width">
                        <button type="submit" name="add_product" class="submit-btn">Add Product</button>
                    </div>
                </form>
            </div>
            <div class="product-management">
                <h2>Current Products (<?php echo count($products); ?>)</h2>
                <div class="products-list">
                    <?php foreach ($products as $product): ?>
                        <div class="product-item">
                            <img src="../image/<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p>NPR <?php echo htmlspecialchars($product['price']); ?></p>
                            <p><?php echo htmlspecialchars($product['category']); ?></p>
                            <p><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="product-actions">
                                <button class="edit-btn" onclick="editProduct(<?php echo $product['id']; ?>)">Edit</button>
                                <button class="delete-btn" onclick="deleteProduct(<?php echo $product['id']; ?>)">Delete</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <div id="editModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div class="modal-content" style="background-color: white; margin: 5% auto; padding: 20px; width: 60%; max-width: 600px; border-radius: 8px;">
            <span class="close-btn" style="float: right; cursor: pointer; font-size: 24px;">&times;</span>
            <h2>Edit Product</h2>
            <form id="editForm" method="POST" enctype="multipart/form-data" class="product-form">
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="update_product" value="1">
                <div class="form-group">
                    <label for="edit_name">Product Name</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="edit_price">Price (NPR)</label>
                    <input type="number" id="edit_price" name="price" required>
                </div>
                <div class="form-group">
                    <label for="edit_category">Category</label>
                    <select id="edit_category" name="category" required>
                    <option value="indoor">Indoor Plants</option>
                    <option value="outdoor">Outdoor Plants</option>
                    <option value="hanging">Hanging Plants</option>
                    <option value="fruit">Fruit-Bearing Plants</option></select>
                 </div>
                 <div class="form-group">
                    <label for="edit_image">Product Image (Leave empty to keep current)</label>
                    <input type="file" id="edit_image" name="image" accept="image/*">
                </div>
                <div class="form-group full-width">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description" required></textarea>
                </div>
                <div class="form-group full-width">
                    <button type="submit" class="submit-btn">Update Product</button>
                </div>
            </form>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const editModal = document.getElementById('editModal');
        const closeBtn = document.querySelector('.close-btn');
        window.editProduct = function(productId) {
        // Find the product in the PHP array
        const product = <?php echo json_encode($products); ?>.find(p => p.id == productId);
        
        if (product) {
            document.getElementById('edit_id').value = product.id;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_category').value = product.category;
            document.getElementById('edit_description').value = product.description;
            
            editModal.style.display = 'block';
        }};
        closeBtn.addEventListener('click', function() {
        editModal.style.display = 'none';});
        window.addEventListener('click', function(event) {
        if (event.target == editModal) {
            editModal.style.display = 'none';
        }});
        window.deleteProduct = function(productId) {
        if (confirm('Are you sure you want to delete this product?')) {
            window.location.href = 'admin_product.php?delete_id=' + productId;
        } };
    });
    </script>
    </body>
    </html>