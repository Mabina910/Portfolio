document.addEventListener("DOMContentLoaded", function () {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    const cartCount = document.getElementById("cart-count");
    const message = document.getElementById("message");
    const cartPanel = document.getElementById("cart-panel");
    const cartContainer = document.getElementById("cart-container");
    const closeCartBtn = document.getElementById("close-cart");
    const clearCartBtn = document.getElementById("clear-cart");
    const cartLink = document.getElementById("cart-link");

    // Update Cart UI
    function updateCartDisplay() {
        cartContainer.innerHTML = "";
        if (cart.length === 0) {
            cartContainer.innerHTML = "<p>Your cart is empty</p>";
        } else {
            cart.forEach((item, index) => {
                const div = document.createElement("div");
                div.classList.add("cart-item");
                div.innerHTML = `
                    <img src="${item.image}" alt="${item.title}" class="cart-img">
                    <div>
                        <p><strong>${item.title}</strong></p>
                        <p>Price: Rs. ${item.price}</p>
                        <p>Quantity: ${item.quantity}</p>
                        <button class="remove-item" data-index="${index}">Remove</button>
                    </div>
                `;
                cartContainer.appendChild(div);
            });
        }

        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        cartCount.textContent = totalItems;
    }

    // Add to Cart function globally accessible
    window.addTocart = function (title, price, image) {
        const existing = cart.find(item => item.title === title);
        if (existing) {
            existing.quantity++;
        } else {
            cart.push({ title, price, image, quantity: 1 });
        }
        localStorage.setItem("cart", JSON.stringify(cart));
        showMessage("Item added to cart!");
        updateCartDisplay();
    };

    // Show Add Message
    function showMessage(text) {
        message.textContent = text;
        message.style.display = "block";
        setTimeout(() => {
            message.style.display = "none";
        }, 2000);
    }

    // Remove Item
    cartContainer.addEventListener("click", function (e) {
        if (e.target.classList.contains("remove-item")) {
            const index = e.target.dataset.index;
            cart.splice(index, 1);
            localStorage.setItem("cart", JSON.stringify(cart));
            updateCartDisplay();
        }
    });

    // Clear Cart
    clearCartBtn.addEventListener("click", () => {
        cart = [];
        localStorage.removeItem("cart");
        updateCartDisplay();
    });

    // Show/Hide Cart Panel
    cartLink.addEventListener("click", (e) => {
        e.preventDefault();
        cartPanel.classList.add("open");
    });

    closeCartBtn.addEventListener("click", () => {
        cartPanel.classList.remove("open");
    
        // Optional: hide after animation ends
        setTimeout(() => {
            cartPanel.style.display = "none";
        }, 300); // match transition duration (0.3s = 300ms)
    });
    
    cartLink.addEventListener("click", (e) => {
        e.preventDefault();
        cartPanel.style.display = "block"; // show again when opened
        setTimeout(() => {
            cartPanel.classList.add("open");
        }, 10); // small delay for smooth transition
    });
    

    updateCartDisplay();
    cartCount.style.display = totalItems > 0 ? "inline-block" : "none";

});
