<?php
session_start();
require_once 'db_connect.php';

// Check admin login
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../html/login.html");
    exit();
}

// Get all customers
$customers = [];
$result = $conn->query("SELECT * FROM users");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $customers[] = $row;
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
        .main-content {
        flex-grow: 1;
        padding: 20px;
        background-color: #EEF0E5;
    }
    
    .customer-management {
        background-color: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        overflow-x: auto;
    }
    
    .customer-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .customer-table th {
        background-color: #163020;
        color: white;
        padding: 12px 15px;
        text-align: left;
    }
    
    .customer-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #ddd;
    }
    
    .customer-table tr:hover {
        background-color: #f5f5f5;
    }
    
    .actions {
        display: flex;
        gap: 8px;
    }
    
    .view-btn, .edit-btn {
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }
    
    .view-btn {
        background-color: #20674b;
        color: white;
    }
    
    .edit-btn {
        background-color: #163020;
        color: white;
    }
    
    .table-container {
        overflow-x: auto;
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
                <li><a href="admin_dashboard.php">
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
            <h1>Customer Management</h1>
            <div class="content-section">
                <div class="customer-management">
                    <div class="table-container">
                        <table class="customer-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Gender</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?= htmlspecialchars($customer['id']) ?></td>
                            <td><?= htmlspecialchars($customer['full_name']) ?></td>
                            <td><?= htmlspecialchars($customer['email']) ?></td>
                            <td><?= htmlspecialchars($customer['phone_number']) ?></td>
                            <td><?= ucfirst(htmlspecialchars($customer['gender'])) ?></td>
                            <td><?= date('M j, Y', strtotime($customer['created_at'])) ?></td>
                            <td class="actions">
                                <button class="view-btn">View</button>
                                <button class="edit-btn">Edit</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
<div id="viewCustomerModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
    <div class="modal-content" style="background:white; width:60%; max-width:600px; margin:5% auto; padding:20px; border-radius:8px;">
        <span class="close-modal" style="float:right; cursor:pointer; font-size:24px;">&times;</span>
        <h2>Customer Details</h2>
        <div id="customerDetails"></div>
    </div>
</div>
<div id="editCustomerModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
    <div class="modal-content" style="background:white; width:60%; max-width:600px; margin:5% auto; padding:20px; border-radius:8px;">
        <span class="close-modal" style="float:right; cursor:pointer; font-size:24px;">&times;</span>
        <h2>Edit Customer</h2>
        <form id="editCustomerForm">
            <input type="hidden" id="editCustomerId">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" id="editCustomerName" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="editCustomerEmail" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="tel" id="editCustomerPhone" required>
            </div>
            <div class="form-group">
                <label>Gender</label>
                <select id="editCustomerGender" required>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <button type="submit" class="submit-btn">Save Changes</button>
        </form>
    </div>
</div>
<script>
document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const row = this.closest('tr');
        const customerData = {
            id: row.cells[0].textContent,
            name: row.cells[1].textContent,
            email: row.cells[2].textContent,
            phone: row.cells[3].textContent,
            gender: row.cells[4].textContent,
            registered: row.cells[5].textContent
        };
        
        document.getElementById('customerDetails').innerHTML = `
            <p><strong>ID:</strong> ${customerData.id}</p>
            <p><strong>Name:</strong> ${customerData.name}</p>
            <p><strong>Email:</strong> ${customerData.email}</p>
            <p><strong>Phone:</strong> ${customerData.phone}</p>
            <p><strong>Gender:</strong> ${customerData.gender}</p>
            <p><strong>Registered:</strong> ${customerData.registered}</p>
            <!-- Add more fields if available -->
        `;
        
        document.getElementById('viewCustomerModal').style.display = 'block';
    });
});


document.querySelector('.close-modal').addEventListener('click', function() {
    document.getElementById('viewCustomerModal').style.display = 'none';
});

document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const row = this.closest('tr');
        document.getElementById('editCustomerId').value = row.cells[0].textContent;
        document.getElementById('editCustomerName').value = row.cells[1].textContent;
        document.getElementById('editCustomerEmail').value = row.cells[2].textContent;
        document.getElementById('editCustomerPhone').value = row.cells[3].textContent;
        document.getElementById('editCustomerGender').value = row.cells[4].textContent.toLowerCase();
        
        document.getElementById('editCustomerModal').style.display = 'block';
    });
});

document.getElementById('editCustomerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const customerData = {
        id: document.getElementById('editCustomerId').value,
        name: document.getElementById('editCustomerName').value,
        email: document.getElementById('editCustomerEmail').value,
        phone: document.getElementById('editCustomerPhone').value,
        gender: document.getElementById('editCustomerGender').value
    };

    fetch('update_customer.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(customerData)
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('Customer updated successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
});

document.querySelectorAll('.close-modal').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('editCustomerModal').style.display = 'none';
    });
});
</script>
</body>
</head>
</html>