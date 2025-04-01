document.addEventListener("DOMContentLoaded", function () {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    const cartCount = document.getElementById("cart-count");
    const cartLink = document.getElementById("cart-link");

    // Update cart count
    function updateCartCount() {
        let totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        cartCount.textContent = totalItems;
    }

    // Add to Cart Function
    function addToCart(bookTitle, bookPrice, bookImage) {
        let existingItem = cart.find(item => item.title === bookTitle);
        
        if (existingItem) {
            existingItem.quantity++;
        } else {
            cart.push({ title: bookTitle, price: bookPrice, image: bookImage, quantity: 1 });
        }

        localStorage.setItem("cart", JSON.stringify(cart));
        updateCartCount();
    }

    // Event Listener for Add to Cart buttons
    document.querySelectorAll(".pointer-button").forEach((button, index) => {
        button.addEventListener("click", function () {
            let bookItem = this.parentElement;
            let bookTitle = bookItem.querySelector("img").alt;
            let bookPrice = parseInt(bookItem.querySelector(".price").textContent.replace(/\D/g, ''));
            let bookImage = bookItem.querySelector("img").src;

            addToCart(bookTitle, bookPrice, bookImage);
        });
    });

    updateCartCount();
});
