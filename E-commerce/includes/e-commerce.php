<?php
// Start session
session_start();

$logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$user_name = $logged_in ? $_SESSION['user_name'] : ''; // Changed to match login.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BotaniQ</title>
    <link rel="stylesheet" href="../css/design.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <a href="../html/login.html" class="login-signup">
                    <img src="../icon/user.png"> Login / Sign Up
                </a>
            <?php endif; ?>
            <style>
                .logout-btn{
                    text-decoration:none;
                    margin-left:15px;
                    color:white;
                    border: 1px solid black;
                    padding:7px;
                    
                }
                .logout-btn:hover{
                    color:black;
                }
            </style>
        </div>
        <div class="contact-bar">
            <div class="contact-item">
                <div class="contact-icon">
                    <img src="../icon/call.png">
                </div>
                <div class="contact-info">
                    <span class="contact-label">CALL US</span>
                    <span class="contact-text">+977 9812345670</span>
                </div>
            </div>
            
            <div class="contact-item">
                <div class="contact-icon">
                    <img src="../icon/location.png">
                </div>
                <div class="contact-info">
                    <span class="contact-label">LOCATION</span>
                    <span class="contact-text">Kathamndu,Nepal</span>
                </div>
            </div>
        </div>

        <div class="main-nav-container">
            <div class="logo-container">
                <div class="logo">
                    <img src="../image/BotaniQ.svg" alt="BotaniQ" id="LOGO" >
                </div>
            </div>
            
            <nav class="main-nav">
                <ul class="nav-links left-menu">
                    <li class="list"><a href="#">HOME</a></li>
                    <li class="list"> <a href="#">SERVICES</a></li>
                    <li class="list"><a href="#">PRODUCTS</a></li>
                </ul>
                
                <ul class="nav-links right-menu">
                    <li class="list"><a href="#">BLOG</a></li>
                    <li class="list"><a href="#">ABOUT US</a></li>

                </ul>
            </nav>
        </div>
    </header>
    
    <main class="container">
        <section class="home">
            <h1> We Bring The Nature To Your Home</h1>
        </section>
        <section class="service">
            <div class="service-container">
                <div class="service-item">
                    <div class="service-icon">
                        <img src="../icon/delivery.png" alt="Delivery">
                    </div>
                    <div class="service-content">
                        <h3>Delivery Service</h3>
                        <p>Get plants delivered to your doorstep without hassle!</p>
                    </div>
                </div>
                
                <div class="service-item">
                    <div class="service-icon">
                        <img src="../icon/money.png" alt="Secure Payment">
                    </div>
                    <div class="service-content">
                        <h3>100% Payment Secure</h3>
                        <p>Your payment are safe with us</p>
                    </div>
                </div>
                
                <div class="service-item">
                    <div class="service-icon">
                        <img src="../icon/support.png" alt="Support">
                    </div>
                    <div class="service-content">
                        <h3>Support 10 Am - 6 Pm</h3>
                        <p>We are available all week from 10 Am to 6 Pm</p>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="category">
            <h2 class="category-title">Shop By Category</h2>
            <div class="category-container">
                <div class="category-column">
                    <div class="types">
                        <h3 class="row-title">Indoor Plants</h3>
                        <a href="./indoor.php" class="Shop">Shop now <img src="../icon/right-arrow.png"class="arrow" ></a>
                        
                    </div>
                    <img src="../icon/indoor.png" >
                </div>
                <div class="category-column">
                    <div class="types">
                        <h3 class="row-title">Outdoor Plants</h3>
                        <a href="./outdoor.html" class="Shop">Shop now <img src="../icon/right-arrow.png"class="arrow"></a>
                        
                    </div>
                    <img src="../icon/outdoor.png" >
                </div>
                <div class="category-column">
                    <div class="types">
                        <h3 class="row-title">Hanging Plants</h3>
                        <a href="./hanging.html" class="Shop">Shop now <img src="../icon/right-arrow.png"class="arrow"></a>
                        
                    </div>
                    <img src="../icon/hang.png">
                </div>
            <div class="category-column">
                <div class="types">
                    <h3 class="row-title">Fruit-Bearing Plants</h3>
                    <a href="./Fruits.html" class="Shop">Shop now <img src="../icon/right-arrow.png"class="arrow"></a>
              
                </div>
                <img src="../icon/fruits.png">
            </div>
            
            <div class="category-column">
                <div class="types">
                    <h3 class="row-title">Most Bought Plants</h3>
                    <a href="./popular.htm" class="Shop">Shop now <img src="../icon/right-arrow.png"class="arrow"></a>            
                </div>
                <img src="../icon/favored.png">
            </div>

            <div class="category-column">
                <div class="types">
                    <h3 class="row-title">New Arrivals</h3>
                    <a href="./new.html" class="Shop">Shop now <img src="../icon/right-arrow.png" class="arrow"></a>
                    
                </div>
                <img src="../icon/new-product.png">
            </div>
        </div>
        </section>
        
    </main>
    <script src="../javascript/index.js"></script>
    <section class="contact">
        <div class="contact_contain"  >
            <h2>Stay Updated with BotaniQ</h2>
            <p >Subscribe to our newsletter for plant care tips, new arrivals, and exclusive offers.</p>
            <form class="subject">
                <input type="email" placeholder="Your Email Address" >
                <button type="submit" >Subscribe</button>
            </form>
        </div>
    </section>
</main>

<footer class="footer">
    <div class="fcontain" >
        <div>
            <h3 >About BotaniQ</h3>
            <p>BotaniQ is Nepal's premier plant shop offering a wide selection of indoor and outdoor plants, gardening supplies, and expert advice for plant enthusiasts.</p>
        </div>
        
        <div class="flist">
            <h3 >Quick Links</h3>
            <ul >
                <li><a href="#" >Home</a></li>
                <li><a href="#" >Shop</a></li>
                <li><a href="#" >Plant Care</a></li>
                <li><a href="#">About Us</a></li>
                <li><a href="#" >Contact</a></li>
            </ul>
        </div>
        
        <div class="Fform">
            <h3>Contact Us</h3>
            <p><strong>Address:</strong> Kathmandu, Nepal</p>
            <p><strong>Phone:</strong> +977 9812345670</p>
            <p><strong>Email:</strong> info@botaniq.com.np</p>
        </div>
        
        <div class="social">
            <h3 >Follow Us</h3>
            <div class="social-image" >
                <a href="#" >FB</a>
                <a href="#" >IG</a>
                <a href="#" >TW</a>
                <a href="#" >YT</a>
            </div>
        </div>
    </div>
    
    <div style="max-width: 1200px; margin: 40px auto 0; padding: 20px; border-top: 1px solid #444; text-align: center; color: #ccc;">
        <p>&copy; 2025 BotaniQ. All Rights Reserved.</p>
    </div>
</footer>
</body>
</html>