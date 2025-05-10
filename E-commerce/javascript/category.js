document.addEventListener('DOMContentLoaded', function() 
{
    let cartItems = [];

    function createCartElements() 
    {
        const cartOverlay = document.createElement('div');
        cartOverlay.className = 'cart-overlay';
        document.body.appendChild(cartOverlay);

        const cartSidebar = document.createElement('div');
        cartSidebar.className = 'cart-sidebar';
        cartSidebar.innerHTML = `
            <div class="cart-header">
                <h2>Your Cart</h2>
                <button class="close-cart-btn">✕</button>
            </div>
            <div class="cart-content"></div>
            <div class="cart-footer">
                <div class="cart-total">
                    <span>Total:</span>
                    <span>Rs. 0</span>
                </div>
                <button class="checkout-btn">Checkout</button>
            </div>`;
        document.body.appendChild(cartSidebar);

        const cartIcon = document.querySelector('.cart-icon');
        if (cartIcon) {
            const cartCounter = document.createElement('span');
            cartCounter.className = 'cart-counter';
            cartCounter.textContent = '0';
            cartIcon.appendChild(cartCounter);
        }
    }

    createCartElements();

    const cartIcon = document.querySelector('.cart-icon a');
    const cartOverlay = document.querySelector('.cart-overlay');
    const cartSidebar = document.querySelector('.cart-sidebar');
    const closeCartBtn = document.querySelector('.close-cart-btn');
    const cartContent = document.querySelector('.cart-content');
    const cartCounter = document.querySelector('.cart-counter');

    function openCart() {
        cartOverlay.style.display = 'block';

        setTimeout(() => {
            cartSidebar.classList.add('open');
        }, 10);
        document.body.style.overflow = 'hidden';
    }

    function closeCart() {
        cartSidebar.classList.remove('open');

        setTimeout(() => {
            cartOverlay.style.display = 'none';
            document.body.style.overflow = 'auto';
        }, 300);
    }

    function addToCart(name, price, image) {

        const existingItem = cartItems.find(item => item.name === name);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cartItems.push({
                name: name,
                price: price,
                image: image,
                quantity: 1
            });
        }
        
        updateCart();
        showToast(`${name} added to cart!`);
    }
    
    function removeFromCart(index) {
        cartItems.splice(index, 1);
        updateCart();
    }
    function updateQuantity(index, newQuantity) {
        if (newQuantity < 1) {
            removeFromCart(index);
        } else {
            cartItems[index].quantity = newQuantity;
            updateCart();
        }
    }
    function updateCart() {
        const totalItems = cartItems.reduce((total, item) => total + item.quantity, 0);
        cartCounter.textContent = totalItems;

        cartContent.innerHTML = '';
        
        if (cartItems.length === 0) {
            cartContent.innerHTML = '<p class="empty-cart-message">Your cart is empty</p>';
        } else {
            cartItems.forEach((item, index) => {

                const priceNumber = parseFloat(item.price.replace('Rs. ', ''));
                const itemTotal = priceNumber * item.quantity;
                
                const cartItem = document.createElement('div');
                cartItem.className = 'cart-item';
                cartItem.innerHTML = `
                    <div class="cart-item-image">
                        <img src="${item.image}" alt="${item.name}">
                    </div>
                    <div class="cart-item-details">
                        <h4>${item.name}</h4>
                        <p>${item.price}</p>
                        <div class="quantity-control">
                            <button class="quantity-btn minus" data-index="${index}">-</button>
                            <span>${item.quantity}</span>
                            <button class="quantity-btn plus" data-index="${index}">+</button>
                        </div>
                    </div>
                    <div class="cart-item-actions">
                        <button class="remove-item-btn" data-index="${index}">×</button>
                        <p>Rs. ${itemTotal.toFixed(2)}</p>
                    </div>
                `;
                
                cartContent.appendChild(cartItem);
            });
        }
        const totalPrice = cartItems.reduce((total, item) => {
            const price = parseFloat(item.price.replace('Rs. ', ''));
            return total + (price * item.quantity);
        }, 0);
        
        document.querySelector('.cart-total').innerHTML = `
            <span>Total:</span>
            <span>Rs. ${totalPrice.toFixed(2)}</span>
        `;

        addCartItemEventListeners();
    }
    
    function addCartItemEventListeners() {

        document.querySelectorAll('.quantity-btn.minus').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                updateQuantity(index, cartItems[index].quantity - 1);
            });
        });

        document.querySelectorAll('.quantity-btn.plus').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                updateQuantity(index, cartItems[index].quantity + 1);
            });
        });
        document.querySelectorAll('.remove-item-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                removeFromCart(index);
            });
        });
    }

    function showToast(message) {
        const existingToast = document.querySelector('.toast-message');
        if (existingToast) {
            existingToast.remove();
        }
        const toast = document.createElement('div');
        toast.className = 'toast-message';
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }
    
    if (cartIcon) {
        cartIcon.addEventListener('click', function(e) {
            e.preventDefault();
            openCart();
        });
    }
    
    closeCartBtn.addEventListener('click', closeCart);
    cartOverlay.addEventListener('click', closeCart);
    
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productCard = this.closest('.product-card');
            if (productCard) {
                const name = productCard.querySelector('.product-name').textContent;
                const price = productCard.querySelector('.product-price').textContent;
                const image = productCard.querySelector('.product-image img').src;
                
                addToCart(name, price, image);
            }
        });
    });
    
    updateCart();
});