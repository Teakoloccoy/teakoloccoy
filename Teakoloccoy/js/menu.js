    function filterSelection(c) {
    const items = document.getElementsByClassName("column");
    if (c === "all") c = "";
    Array.from(items).forEach(item => {
        item.style.display = (c === "" || item.classList.contains(c)) ? "block" : "none";
    });
}

function w3AddClass(element, name) {
    const arr1 = element.className.split(" ");
    const arr2 = name.split(" ");
    arr2.forEach(cls => {
        if (!arr1.includes(cls)) element.className += " " + cls;
    });
}

function w3RemoveClass(element, name) {
    let arr1 = element.className.split(" ");
    const arr2 = name.split(" ");
    arr2.forEach(cls => {
        arr1 = arr1.filter(item => item !== cls);
    });
    element.className = arr1.join(" ");
}

const btnContainer = document.getElementById("myBtnContainer");
if (btnContainer) {
    const btns = btnContainer.getElementsByClassName("btn");
    for (let i = 0; i < btns.length; i++) {
        btns[i].addEventListener("click", function () {
            const current = document.getElementsByClassName("active");
            if (current.length > 0) {
                current[0].className = current[0].className.replace(" active", "");
            }
            this.className += " active";
        });
    }
}

let cart = JSON.parse(localStorage.getItem("cart")) || [];

function updateCartCount() {
    const cartCountElem = document.getElementById("cartCount");
    if (cartCountElem) {
        cartCountElem.innerText = cart.length;
    }
}

function addToCart(itemName, itemPrice, itemImage) {
    const item = { 
        name: itemName, 
        price: parseFloat(itemPrice), // Ensure price is a number
        image: itemImage 
    };
    cart.push(item);
    localStorage.setItem("cart", JSON.stringify(cart));
    updateCartCount();
    displayCartItems();
}

function toggleCart() {
    const cart = document.getElementById("cart");
    if (cart) {
        cart.style.display = cart.style.display === "none" || cart.style.display === "" ? "block" : "none";
        displayCartItems();
    }
}

function displayCartItems() {
    const cartItemsDiv = document.getElementById("cartItems");
    const cartTotalSpan = document.getElementById("cartTotal");
    if (cartItemsDiv && cartTotalSpan) {
        cartItemsDiv.innerHTML = ""; // Clear previous items
        let total = 0;

        if (cart.length === 0) {
            cartItemsDiv.innerHTML = "<p>Your cart is empty</p>";
            cartTotalSpan.innerText = "0.00";
        } else {
            cart.forEach((item, index) => {
                const itemDiv = document.createElement("div");
                itemDiv.innerHTML = `
                    <div style="display: flex; align-items: center; margin-bottom: 15px; padding: 10px; border-bottom: 1px solid #eee;">
                        <img src="${item.image}" alt="${item.name}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; margin-right: 15px;">
                        <div style="flex-grow: 1;">
                            <p style="margin: 0 0 5px 0; font-size: 16px;">${item.name}</p>
                            <p style="margin: 0; color: #666;">₱${item.price.toFixed(2)}</p>
                        </div>
                        <button onclick="removeFromCart(${index})" 
                            style="background-color: #ff4444; color: white; border: none; padding: 6px 10px; 
                            border-radius: 4px; cursor: pointer; font-size: 10px; transition: background-color 0.3s;">
                            Remove
                        </button>
                    </div>
                `;
                cartItemsDiv.appendChild(itemDiv);
                total += item.price;
            });
            cartTotalSpan.innerText = total.toFixed(2);
        }
    }
}

function removeFromCart(index) {
    cart.splice(index, 1);
    localStorage.setItem("cart", JSON.stringify(cart));
    updateCartCount();
    displayCartItems(); 
}

function clearCart() {
    cart = [];
    localStorage.setItem("cart", JSON.stringify(cart));
    updateCartCount();
    displayCartItems();
}

function checkout() {
    if (cart.length > 0) {
        openCheckoutModal();
    } else {
        alert("Your cart is empty!");
    }
}

document.getElementById('checkoutForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const name = document.getElementById('name').value.trim();
    const address = document.getElementById('address').value.trim();
    const phone = document.getElementById('phone').value.trim();

    if (name && address && phone) {
        const orderData = {
            username: sessionData.username,
            items: cart,
            total: parseFloat(document.getElementById("cartTotal").innerText),
            deliveryName: name,
            deliveryAddress: address,
            deliveryPhone: phone,
            timestamp: new Date().toISOString()
        };

        fetch('../php/menu.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(orderData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Order placed successfully!");
                clearCart();
                closeCheckoutModal();
                window.location.reload();
            } else {
                alert("Error: " + (data.error || "Failed to place order"));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An error occurred while placing your order. Please try again.");
        });
    } else {
        alert("Please fill in all delivery details.");
    }
});

document.addEventListener("DOMContentLoaded", function() {
    filterSelection('all');
    updateCartCount();

    const addToCartButtons = document.querySelectorAll(".addToCartBtn");
    addToCartButtons.forEach(button => {
        button.addEventListener("click", function() {
            const itemName = this.getAttribute("data-name");
            const itemPrice = parseFloat(this.getAttribute("data-price"));
            const contentDiv = this.closest('.content');
            const imgElement = contentDiv.querySelector('img');
            const itemImage = imgElement ? imgElement.src : '';
            
            addToCart(itemName, itemPrice, itemImage);
        });
    });
});