<?php
session_start();
require_once 'db_connect.php';

$logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$user_name = $logged_in ? $_SESSION['user_name'] : '';

$category_key = strtolower(trim((string)($_GET['category'] ?? 'indoor')));

$category_map = [
    'indoor' => ['label' => 'Indoor Plants', 'query' => "SELECT * FROM products WHERE category = 'indoor' ORDER BY id DESC"],
    'outdoor' => ['label' => 'Outdoor Plants', 'query' => "SELECT * FROM products WHERE category = 'outdoor' ORDER BY id DESC"],
    'hanging' => ['label' => 'Hanging Plants', 'query' => "SELECT * FROM products WHERE category = 'hanging' ORDER BY id DESC"],
    'fruit' => ['label' => 'Fruit-Bearing Plants', 'query' => "SELECT * FROM products WHERE category IN ('fruit', 'fruits') ORDER BY id DESC"],
    'new' => ['label' => 'New Arrivals', 'query' => "SELECT * FROM products ORDER BY id DESC LIMIT 24"],
    'popular' => ['label' => 'Most Bought Plants', 'query' => "SELECT p.*,
            COALESCE(SUM(oi.quantity), 0) AS sold_count
        FROM products p
        LEFT JOIN order_items oi ON oi.product_name = p.name
        GROUP BY p.id
        ORDER BY sold_count DESC, p.id DESC
        LIMIT 24"]
];

if (!isset($category_map[$category_key])) {
    $category_key = 'indoor';
}

$page_label = $category_map[$category_key]['label'];
$products = [];
$result = $conn->query($category_map[$category_key]['query']);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$product_count = count($products);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_label); ?> - BotaniQ</title>
    <link rel="stylesheet" href="../css/category.css">
    <style>
        .logout-btn {
            text-decoration: none;
            margin-left: 15px;
            color: white;
            border: 1px solid black;
            padding: 7px;
        }

        .logout-btn:hover {
            color: black;
        }

        .category-switcher {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 20px 0 24px;
            justify-content: center;
            align-items: center;
        }

        .category-chip {
            display: inline-block;
            padding: 8px 14px;
            border: 1px solid #b6c4b6;
            border-radius: 999px;
            text-decoration: none;
            color: #163020;
            background: #fff;
            font-weight: 600;
        }

        .category-chip:hover {
            background: #eef0e5;
            border-color: #20674b;
        }

        .category-chip.active {
            background: #20674b;
            border-color: #20674b;
            color: #fff;
        }
    </style>
</head>
<body>
    <header>
        <div class="top-header">
            <span>Welcome to BotaniQ!</span>
            <?php if ($logged_in): ?>
                <div class="user-account">
                    <span>Hi, <?php echo htmlspecialchars($user_name); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            <?php else: ?>
                <a href="../pages/auth/login.html" class="login-signup">
                    <img src="../icon/user.png" alt="User"> Login / Sign Up
                </a>
            <?php endif; ?>
        </div>

        <div class="contact-bar">
            <div class="contact-item">
                <div class="contact-icon">
                    <img src="../icon/call.png" alt="Call">
                </div>
                <div class="contact-info">
                    <span class="contact-label">CALL US</span>
                    <span class="contact-text">+977 9812345670</span>
                </div>
            </div>

            <div class="contact-item">
                <div class="contact-icon">
                    <img src="../icon/location.png" alt="Location">
                </div>
                <div class="contact-info">
                    <span class="contact-label">LOCATION</span>
                    <span class="contact-text">Kathmandu, Nepal</span>
                </div>
            </div>
        </div>

        <div class="main-nav-container">
            <div class="logo-container">
                <div class="logo">
                    <img src="../image/BotaniQ.svg" alt="BotaniQ" id="LOGO">
                </div>
            </div>

            <nav class="main-nav">
                <ul class="nav-links left-menu">
                    <li class="list"><a href="../e-commerce.php">HOME</a></li>
                    <li class="list"><a href="../e-commerce.php#services">SERVICES</a></li>
                    <li class="list"><a href="../e-commerce.php#categories">PRODUCTS</a></li>
                </ul>

                <ul class="nav-links right-menu">
                    <li class="list"><a href="../e-commerce.php#updates">BLOG</a></li>
                    <li class="list"><a href="../e-commerce.php#about">ABOUT US</a></li>
                    <li class="list cart-icon">
                        <a href="#">
                            <img src="../icon/shopping-cart.png" alt="Cart">
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <section class="banner">
            <h1>Home > <?php echo htmlspecialchars($page_label); ?></h1>
        </section>

        <nav class="category-switcher" aria-label="Shop by category">
            <a class="category-chip <?php echo $category_key === 'indoor' ? 'active' : ''; ?>" href="category.php?category=indoor">Indoor</a>
            <a class="category-chip <?php echo $category_key === 'outdoor' ? 'active' : ''; ?>" href="category.php?category=outdoor">Outdoor</a>
            <a class="category-chip <?php echo $category_key === 'hanging' ? 'active' : ''; ?>" href="category.php?category=hanging">Hanging</a>
            <a class="category-chip <?php echo $category_key === 'fruit' ? 'active' : ''; ?>" href="category.php?category=fruit">Fruit-Bearing</a>
            <a class="category-chip <?php echo $category_key === 'popular' ? 'active' : ''; ?>" href="category.php?category=popular">Most Bought</a>
            <a class="category-chip <?php echo $category_key === 'new' ? 'active' : ''; ?>" href="category.php?category=new">New Arrivals</a>
        </nav>

        <div class="product-page-content">
            <div class="products-main-content">
                <div class="product-sorting">
                    <div class="product-count">
                        <p>Showing <?php echo $product_count; ?> products</p>
                    </div>
                    <div class="sort-options">
                        <select>
                            <option value="popularity">Sort by Popularity</option>
                            <option value="price-low-high">Price: Low to High</option>
                            <option value="price-high-low">Price: High to Low</option>
                            <option value="newest">Newest First</option>
                        </select>
                    </div>
                </div>

                <div class="product-grid">
                    <?php if ($product_count === 0): ?>
                        <p>No products found in this category yet.</p>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="../image/<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                </div>
                                <div class="product-info">
                                    <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                                    <div class="product-price">Rs. <?php echo number_format((float)$product['price'], 2); ?></div>
                                    <button class="add-to-cart-btn">Add to Cart</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <section class="contact">
            <div class="contact_contain">
                <h2>Stay Updated with BotaniQ</h2>
                <p>Subscribe to our newsletter for plant care tips, new arrivals, and exclusive offers.</p>
                <form class="subject">
                    <input type="email" placeholder="Your Email Address">
                    <button type="submit">Subscribe</button>
                </form>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="fcontain">
            <div>
                <h3>About BotaniQ</h3>
                <p>BotaniQ is Nepal's premier plant shop offering a wide selection of indoor and outdoor plants, gardening supplies, and expert advice for plant enthusiasts.</p>
            </div>

            <div class="flist">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="../e-commerce.php">Home</a></li>
                    <li><a href="category.php?category=indoor">Shop</a></li>
                    <li><a href="category.php?category=popular">Plant Care</a></li>
                    <li><a href="../e-commerce.php#about">About Us</a></li>
                    <li><a href="../e-commerce.php#updates">Contact</a></li>
                </ul>
            </div>

            <div class="Fform">
                <h3>Contact Us</h3>
                <p><strong>Address:</strong> Kathmandu, Nepal</p>
                <p><strong>Phone:</strong> +977 9812345670</p>
                <p><strong>Email:</strong> info@botaniq.com.np</p>
            </div>

            <div class="social">
                <h3>Follow Us</h3>
                <div class="social-image">
                    <a href="#">FB</a>
                    <a href="#">IG</a>
                    <a href="#">TW</a>
                    <a href="#">YT</a>
                </div>
            </div>
        </div>

        <div style="max-width: 1200px; margin: 40px auto 0; padding: 20px; border-top: 1px solid #444; text-align: center; color: #ccc;">
            <p>&copy; 2025 BotaniQ. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="../javascript/category.js"></script>
</body>
</html>
