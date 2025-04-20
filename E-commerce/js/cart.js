// cart.js

function loadCart() {
    const cart = JSON.parse(localStorage.getItem("cart")) || [];
    const cartItemsDiv = document.getElementById("cartItems");
    const totalPriceEl = document.getElementById("totalPrice");
  
    cartItemsDiv.innerHTML = "";
    let total = 0;
  
    if (cart.length === 0) {
      cartItemsDiv.innerHTML = "<p>Your cart is empty.</p>";
      totalPriceEl.textContent = "";
      return;
    }
  
    cart.forEach((item, index) => {
      const card = document.createElement("div");
      card.className = "book-card";
      card.innerHTML = `
        <img src="${item.image}" alt="${item.title}" />
        <h3>${item.title}</h3>
        <p>$${item.price.toFixed(2)}</p>
        <button onclick="removeFromCart(${index})">Remove</button>
      `;
      cartItemsDiv.appendChild(card);
      total += item.price;
    });
  
    totalPriceEl.textContent = `Total: $${total.toFixed(2)}`;
  }
  
  function removeFromCart(index) {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    cart.splice(index, 1);
    localStorage.setItem("cart", JSON.stringify(cart));
    loadCart();
  }
  
  window.onload = loadCart;
  