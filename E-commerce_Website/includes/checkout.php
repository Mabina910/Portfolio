<?php
// checkout.php
session_start();

// include DB connection (make sure db.php exists in includes folder and defines $conn)
require_once 'db_connect.php';

// ==================== USER INFO ====================
$user_name = "";
$user_email = "";
if (isset($_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT full_name, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $user_name  = $row['full_name'];
        $user_email = $row['email'];
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - BotaniQ</title>
    <style>
        /* Base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        
        body {
            background-color: #EEF0E5;
            color: #163020;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header styles matching your theme */
        .checkout-header {
            text-align: center;
            padding: 20px 0;
            margin-bottom: 30px;
            border-bottom: 2px solid #B6C4B6;
        }
        
        .checkout-header h1 {
            color: #163020;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .checkout-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        
        .step {
            display: flex;
            align-items: center;
            margin: 0 15px;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #B6C4B6;
            color: #163020;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: bold;
        }
        
        .step.active .step-number {
            background-color: #20674b;
            color: white;
        }
        
        .step-text {
            font-weight: 500;
        }
        
        /* Checkout content layout */
        .checkout-content {
            display: grid;
            grid-template-columns: 60% 35%;
            gap: 5%;
        }
        
        .checkout-section {
            background-color: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section-title {
            color: #163020;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #B6C4B6;
        }
        
        /* Form styles */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #163020;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #B6C4B6;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #20674b;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        /* Order summary */
        .order-summary {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .summary-total {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 1.2rem;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #B6C4B6;
        }
        
        /* Cart items in checkout */
        .cart-item-checkout {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .cart-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
        }
        
        .cart-item-details {
            flex-grow: 1;
        }
        
        .cart-item-name {
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .cart-item-price {
            color: #20674b;
            font-weight: bold;
        }
        
        .cart-item-quantity {
            color: #666;
        }
        
        /* Payment methods */
        .payment-method {
            margin-bottom: 15px;
            padding: 15px;
            border: 1px solid #B6C4B6;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .payment-method:hover {
            border-color: #20674b;
            background-color: #f5f5f5;
        }
        
        .payment-method.selected {
            border-color: #20674b;
            background-color: #EEF0E5;
        }
        
        .payment-method input {
            margin-right: 10px;
        }
        
        /* Button styles */
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: #20674b;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
            text-align: center;
            text-decoration: none;
        }
        
        .btn:hover {
            background-color: #163020;
        }
        
        .btn-block {
            display: block;
            width: 100%;
        }
        
        .continue-shopping {
            display: inline-block;
            margin-top: 15px;
            color: #20674b;
            text-decoration: none;
        }
        
        .continue-shopping:hover {
            text-decoration: underline;
        }
        
        /* Responsive styles */
        @media (max-width: 992px) {
            .checkout-content {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        /* User info display */
        .user-info {
            background-color: #EEF0E5;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="checkout-header">
            <h1>Checkout</h1>
            <div class="user-info">
                <p>Logged in as: <strong><?php echo htmlspecialchars($user_name); ?></strong> (<?php echo htmlspecialchars($user_email); ?>)</p>
            </div>
            <div class="checkout-steps">
                <div class="step active">
                    <div class="step-number">1</div>
                    <div class="step-text">Shipping</div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-text">Payment</div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-text">Confirmation</div>
                </div>
            </div>
        </div>
        
        <div class="checkout-content">
            <div class="checkout-left">
                <div class="checkout-section">
                    <h2 class="section-title">Shipping Information</h2>
                    <form id="shipping-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">First Name</label>
                                <input type="text" id="firstName" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name</label>
                                <input type="text" id="lastName" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user_email); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Shipping Address</label>
                            <input type="text" id="address" class="form-control" placeholder="Street address" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="zipCode">ZIP Code</label>
                                <input type="text" id="zipCode" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="country">Country</label>
                            <select id="country" class="form-control" required>
                                <option value="">Select Country</option>
                                <option value="nepal" selected>Nepal</option>
                                <option value="india">India</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="shippingNotes">Delivery Notes (Optional)</label>
                            <textarea id="shippingNotes" class="form-control" rows="3" placeholder="Any special delivery instructions"></textarea>
                        </div>
                    </form>
                </div>
                
                <div class="checkout-section">
                    <h2 class="section-title">Payment Method</h2>
                    
                    <div class="payment-method">
                        <input type="radio" id="cod" name="paymentMethod" value="cod" checked>
                        <label for="cod">Cash on Delivery</label>
                    </div>
                    
                    <div class="payment-method">
                        <input type="radio" id="esewa" name="paymentMethod" value="esewa">
                        <label for="esewa">eSewa ePay</label>
                    </div>
                    
                    <div class="payment-method">
                        <input type="radio" id="card" name="paymentMethod" value="card">
                        <label for="card">Credit/Debit Card</label>
                    </div>
                </div>
            </div>
            
            <div class="checkout-right">
                <div class="checkout-section order-summary">
                    <h2 class="section-title">Order Summary</h2>
                    
                    <div id="checkout-items">
                        <!-- Cart items will be dynamically inserted here -->
                    </div>
                    
                    <div class="summary-item">
                        <span>Subtotal</span>
                        <span id="subtotal">Rs. 0</span>
                    </div>
                    
                    <div class="summary-item">
                        <span>Shipping</span>
                        <span>Rs. 100</span>
                    </div>
                    
                    <div class="summary-total">
                        <span>Total</span>
                        <span id="total">Rs. 0</span>
                    </div>
                    
                    <button id="place-order-btn" class="btn btn-block">Place Order</button>
                    <a href="../e-commerce.php" class="continue-shopping">Continue Shopping</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get cart items from sessionStorage or initialize empty array
            let cartItems = JSON.parse(sessionStorage.getItem('cartItems')) || [];

            function parsePrice(value) {
                if (typeof value === 'number') {
                    return value;
                }
                if (typeof value === 'string') {
                    let normalized = value.replace(/,/g, '');
                    normalized = normalized.replace(/npr\.?|rs\.?/gi, '');
                    normalized = normalized.replace(/\s+/g, '');
                    normalized = normalized.replace(/[^0-9.]/g, '');

                    const firstDot = normalized.indexOf('.');
                    if (firstDot !== -1) {
                        normalized = normalized.slice(0, firstDot + 1) + normalized.slice(firstDot + 1).replace(/\./g, '');
                    }

                    const parsed = parseFloat(normalized || '0');
                    return Number.isFinite(parsed) ? parsed : 0;
                }
                return 0;
            }
            
            // Display cart items in checkout
            function displayCartItems() {
                const checkoutItemsContainer = document.getElementById('checkout-items');
                checkoutItemsContainer.innerHTML = '';
                
                if (cartItems.length === 0) {
                    checkoutItemsContainer.innerHTML = '<p>Your cart is empty</p>';
                    document.getElementById('subtotal').textContent = 'Rs. 0';
                    document.getElementById('total').textContent = 'Rs. 0';
                    return;
                }
                
                let subtotal = 0;
                
                cartItems.forEach((item, index) => {
                    const price = parsePrice(item.price);
                    const quantity = parseInt(item.quantity, 10) || 0;
                    const itemTotal = price * quantity;
                    subtotal += itemTotal;
                    
                    const cartItemElement = document.createElement('div');
                    cartItemElement.className = 'cart-item-checkout';
                    cartItemElement.innerHTML = `
                        <img src="${item.image}" alt="${item.name}" class="cart-item-image">
                        <div class="cart-item-details">
                            <div class="cart-item-name">${item.name}</div>
                            <div class="cart-item-price">${item.price}</div>
                            <div class="cart-item-quantity">Quantity: ${quantity}</div>
                        </div>
                        <div class="cart-item-total">Rs. ${itemTotal.toFixed(2)}</div>
                    `;
                    
                    checkoutItemsContainer.appendChild(cartItemElement);
                });
                
                const shipping = 100;
                const total = subtotal + shipping;
                
                document.getElementById('subtotal').textContent = `Rs. ${subtotal.toFixed(2)}`;
                document.getElementById('total').textContent = `Rs. ${total.toFixed(2)}`;
            }
            
            // Initialize the page with cart items
            displayCartItems();
            
            // Handle place order button click
            document.getElementById('place-order-btn').addEventListener('click', function() {
                const shippingForm = document.getElementById('shipping-form');
                const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
                
                // Validate form
                if (!shippingForm.checkValidity()) {
                    alert('Please fill out all required shipping information correctly.');
                    shippingForm.reportValidity();
                    return;
                }
                
                if (cartItems.length === 0) {
                    alert('Your cart is empty. Please add items to your cart before placing an order.');
                    return;
                }
                
                // Get form values
                const formData = {
                    firstName: document.getElementById('firstName').value,
                    lastName: document.getElementById('lastName').value,
                    email: document.getElementById('email').value,
                    phone: document.getElementById('phone').value,
                    address: document.getElementById('address').value,
                    city: document.getElementById('city').value,
                    zipCode: document.getElementById('zipCode').value,
                    country: document.getElementById('country').value,
                    notes: document.getElementById('shippingNotes').value,
                    paymentMethod: paymentMethod
                };
                
                // Prepare order data
                const orderData = {
                    customerInfo: formData,
                    items: cartItems.map(item => ({
                        ...item,
                        quantity: parseInt(item.quantity, 10) || 0,
                        price: parsePrice(item.price)
                    })),
                    subtotal: parseFloat(document.getElementById('subtotal').textContent.replace('Rs. ', '')),
                    shipping: 100,
                    total: parseFloat(document.getElementById('total').textContent.replace('Rs. ', '')),
                    orderDate: new Date().toISOString()
                };

                if (paymentMethod === 'esewa') {
                    processOrderForEsewa(orderData);
                    return;
                }
                
                processOrder(orderData);
            });
            
            // Process order with normal payment methods
            function processOrder(orderData) {
                // Show loading state
                const placeOrderBtn = document.getElementById('place-order-btn');
                placeOrderBtn.disabled = true;
                placeOrderBtn.textContent = 'Processing...';
                
                fetch('process_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(orderData),
                    credentials: 'include'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Clear the cart
                        sessionStorage.removeItem('cartItems');
                        cartItems = [];
                        
                        // Redirect to confirmation page
                        alert('Order placed successfully! Your order ID is: ' + data.orderId);
                        window.location.href = 'order_confirmation.php?order_id=' + data.orderId;
                    } else {
                        alert('Error: ' + data.message);
                        console.error('Server error:', data);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while processing your order. Please try again.');
                })
                .finally(() => {
                    // Reset button state
                    placeOrderBtn.disabled = false;
                    placeOrderBtn.textContent = 'Place Order';
                });
            }

            function processOrderForEsewa(orderData) {
                const placeOrderBtn = document.getElementById('place-order-btn');
                placeOrderBtn.disabled = true;
                placeOrderBtn.textContent = 'Redirecting to eSewa...';

                fetch('process_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(orderData),
                    credentials: 'include'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.error || 'Failed to create order');
                    }

                    const redirectAmount = Number.isFinite(orderData.total) ? orderData.total.toFixed(2) : '0.00';
                    if (parseFloat(redirectAmount) <= 0) {
                        throw new Error('Invalid order amount for eSewa payment.');
                    }

                    const params = new URLSearchParams({
                        amount: redirectAmount,
                        transaction_uuid: data.transactionId,
                    });

                    window.location.href = `create_payment.php?${params.toString()}`;
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Could not start eSewa payment. Please try again.');
                    placeOrderBtn.disabled = false;
                    placeOrderBtn.textContent = 'Place Order';
                });
            }
            
            // Style payment method selection
            const paymentMethods = document.querySelectorAll('.payment-method');
            paymentMethods.forEach(method => {
                method.addEventListener('click', function() {
                    const radio = this.querySelector('input[type="radio"]');
                    radio.checked = true;
                    
                    // Update styles
                    paymentMethods.forEach(m => m.classList.remove('selected'));
                    this.classList.add('selected');
                });
            });
            
            // Initialize payment method selection style
            document.querySelector('.payment-method input[type="radio"]:checked')
                .closest('.payment-method')
                .classList.add('selected');
        });
    </script>
</body>
</html>