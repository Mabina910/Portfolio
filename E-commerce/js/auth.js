// auth.js

document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("loginForm");
    const signupForm = document.getElementById("signupForm");
  
    if (loginForm) {
      loginForm.addEventListener("submit", (e) => {
        e.preventDefault();
        const email = document.getElementById("loginEmail").value;
        const password = document.getElementById("loginPassword").value;
        const storedUser = JSON.parse(localStorage.getItem("user"));
  
        if (storedUser && storedUser.email === email && storedUser.password === password) {
          alert("Login successful!");
          window.location.href = "index.html";
        } else {
          alert("Invalid credentials!");
        }
      });
    }
  
    if (signupForm) {
      signupForm.addEventListener("submit", (e) => {
        e.preventDefault();
        const name = document.getElementById("signupName").value;
        const email = document.getElementById("signupEmail").value;
        const password = document.getElementById("signupPassword").value;
  
        const user = { name, email, password };
        localStorage.setItem("user", JSON.stringify(user));
        alert("Signup successful! You can now login.");
        window.location.href = "login.html";
      });
    }
  });
  