document.addEventListener("DOMContentLoaded", function () {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    const cartContainer = document.getElementById("cart-container");
    const cartCount = document.getElementById("cart-count");

    function updateCartDisplay() {
        cartContainer.innerHTML = "";
        if (cart.length === 0) {
            cartContainer.innerHTML = "<p>Your cart is empty</p>";
            cartCount.textContent = "0";
            return;
        }

        cart.forEach((item, index) => {
            let cartItem = document.createElement("div");
            cartItem.classList.add("cart-item");
            cartItem.innerHTML = `
                <img src="${item.image}" alt="${item.title}" width="50">
                <p>${item.title}</p>
                <p>Price: ${item.price}</p>
                <p>Quantity: ${item.quantity}</p>
                <button class="remove-item" data-index="${index}">Remove</button>
            `;
            cartContainer.appendChild(cartItem);
        });

        let totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        cartCount.textContent = totalItems;
    }

    // Remove item from cart
    cartContainer.addEventListener("click", function (event) {
        if (event.target.classList.contains("remove-item")) {
            let index = event.target.getAttribute("data-index");
            cart.splice(index, 1);
            localStorage.setItem("cart", JSON.stringify(cart));
            updateCartDisplay();
        }
    });

    // Clear Cart
    document.getElementById("clear-cart").addEventListener("click", function () {
        localStorage.removeItem("cart");
        cart = [];
        updateCartDisplay();
    });

    updateCartDisplay();
});
