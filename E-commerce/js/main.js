document.addEventListener("DOMContentLoaded", () => {
    const bookList = document.getElementById("all-books");
  
    const books = [
      {
        title: "The Book Thief",
        price: 12.99,
        image: "assets/images/book1.jpg",
      },
      {
        title: "1984 by George Orwell",
        price: 10.49,
        image: "assets/images/book2.jpg",
      },
      {
        title: "To Kill a Mockingbird",
        price: 11.99,
        image: "assets/images/book3.jpg",
      },
    ];
  
    if (bookList) {
      books.forEach((book) => {
        const card = document.createElement("div");
        card.className = "book-card";
        card.innerHTML = `
          <img src="${book.image}" alt="${book.title}" />
          <h3 class="book-title">${book.title}</h3>
          <p class="book-price">${book.price}</p>
          <button class="add-to-cart">Add to Cart</button>
        `;
        bookList.appendChild(card);
      });
    }
  
    // Add to Cart functionality
    document.addEventListener("click", (e) => {
      if (e.target.classList.contains("add-to-cart")) {
        const bookCard = e.target.closest(".book-card");
        const title = bookCard.querySelector(".book-title").textContent;
        const price = parseFloat(bookCard.querySelector(".book-price").textContent);
        const image = bookCard.querySelector("img").getAttribute("src");
  
        let cartItems = JSON.parse(localStorage.getItem("cart")) || [];
  
        cartItems.push({
          title,
          price,
          image,
          quantity: 1,
        });
  
        localStorage.setItem("cart", JSON.stringify(cartItems));
        alert("Book added to cart!");
      }
    });
  });
  