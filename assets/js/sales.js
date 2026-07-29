console.log("SALES JS LOADED");

let cart = [];


// ======================================================
// INITIALIZE POS
// ======================================================

document.addEventListener("DOMContentLoaded", function () {

    console.log("POS JavaScript initialized");

    activateProducts();
    setupSearch();
    setupCategories();
    setupPayment();
    setupCompleteSale();

});


// ======================================================
// ADD PRODUCT TO CART
// ======================================================

function addProductToCart(button) {

    const id = String(button.dataset.id);
    const name = button.dataset.name;
    const price = parseFloat(button.dataset.price) || 0;

    let existing = cart.find(item => item.id === id);

    if (existing) {

        existing.qty++;

    } else {

        cart.push({
            id: id,
            name: name,
            price: price,
            qty: 1
        });

    }

    displayCart();
}


// ======================================================
// ACTIVATE ADD BUTTONS
// ======================================================

function activateProducts() {

    const buttons = document.querySelectorAll(".add-cart");

    buttons.forEach(button => {

        button.onclick = function () {

            addProductToCart(this);

        };

    });

}


// ======================================================
// DISPLAY CART
// ======================================================

function displayCart() {

    const cartTable = document.getElementById("cart");

    if (!cartTable) {
        console.error("Cart element #cart not found");
        return;
    }

    cartTable.innerHTML = "";

    let subtotal = 0;


    cart.forEach((item, index) => {

        const itemTotal = item.qty * item.price;

        subtotal += itemTotal;


        cartTable.innerHTML += `

            <tr>

                <td>
                    ${escapeHtml(item.name)}
                </td>

                <td>

                    <button
                        type="button"
                        class="qty-btn"
                        onclick="decreaseQty(${index})">
                        −
                    </button>

                    <span class="cart-qty">
                        ${item.qty}
                    </span>

                    <button
                        type="button"
                        class="qty-btn"
                        onclick="increaseQty(${index})">
                        +
                    </button>

                </td>

                <td>
                    ${item.price.toFixed(2)}
                </td>

                <td>
                    ${itemTotal.toFixed(2)}
                </td>

                <td>

                    <button
                        type="button"
                        class="remove-item"
                        onclick="removeItem(${index})">
                        ✕
                    </button>

                </td>

            </tr>

        `;

    });


    const subtotalElement =
        document.getElementById("subtotal");

    const totalElement =
        document.getElementById("total");


    if (subtotalElement) {

        subtotalElement.textContent =
            subtotal.toFixed(2);

    }


    if (totalElement) {

        totalElement.textContent =
            subtotal.toFixed(2);

    }


    calculateChange();

}


// ======================================================
// INCREASE QUANTITY
// ======================================================

function increaseQty(index) {

    if (!cart[index]) {
        return;
    }

    cart[index].qty++;

    displayCart();

}


// ======================================================
// DECREASE QUANTITY
// ======================================================

function decreaseQty(index) {

    if (!cart[index]) {
        return;
    }

    if (cart[index].qty > 1) {

        cart[index].qty--;

    } else {

        cart.splice(index, 1);

    }

    displayCart();

}


// ======================================================
// REMOVE ITEM
// ======================================================

function removeItem(index) {

    if (!cart[index]) {
        return;
    }

    cart.splice(index, 1);

    displayCart();

}


// ======================================================
// PAYMENT / CHANGE
// ======================================================

function setupPayment() {

    const amount =
        document.getElementById("amount");

    if (!amount) {
        return;
    }

    amount.addEventListener(
        "input",
        calculateChange
    );

}


function calculateChange() {

    const amountElement =
        document.getElementById("amount");

    const totalElement =
        document.getElementById("total");

    const changeElement =
        document.getElementById("change");


    if (
        !amountElement ||
        !totalElement ||
        !changeElement
    ) {
        return;
    }


    const amount =
        parseFloat(amountElement.value) || 0;

    const total =
        parseFloat(totalElement.textContent) || 0;


    let change =
        amount - total;


    if (change < 0) {
        change = 0;
    }


    changeElement.textContent =
        change.toFixed(2);

}


// ======================================================
// LIVE PRODUCT SEARCH
// ======================================================

function setupSearch() {

    const searchBox =
        document.getElementById("productSearch");

    const productsContainer =
        document.getElementById("products");


    if (!searchBox) {

        console.error(
            "Search box #productSearch not found"
        );

        return;
    }


    if (!productsContainer) {

        console.error(
            "Products container #products not found"
        );

        return;
    }


    searchBox.addEventListener(
        "input",
        function () {

            const search =
                this.value.trim();


            console.log(
                "Searching for:",
                search
            );


            // Clear products when search is empty

            if (search.length === 0) {

                productsContainer.innerHTML = "";

                return;

            }


            // Wait until at least 2 characters

            if (search.length < 2) {

                productsContainer.innerHTML = "";

                return;

            }


            productsContainer.innerHTML = `
                <div class="search-loading">
                    Searching...
                </div>
            `;


            /*
             * IMPORTANT:
             *
             * index.php and search_product.php
             * are in the SAME folder:
             *
             * admin/sales/
             *
             * Therefore this is the correct path.
             */

            const url =
                "search_product.php?search="
                + encodeURIComponent(search);


            console.log(
                "Search URL:",
                url
            );


            fetch(url, {
                method: "GET",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            })


            .then(response => {

                if (!response.ok) {

                    throw new Error(
                        "HTTP Error " +
                        response.status
                    );

                }

                return response.text();

            })


            .then(data => {

                console.log(
                    "Search response received"
                );


                productsContainer.innerHTML =
                    data;


                // Reconnect Add buttons

                activateProducts();

            })


            .catch(error => {

                console.error(
                    "Search error:",
                    error
                );


                productsContainer.innerHTML = `
                    <div class="search-error">
                        Unable to search products.
                    </div>
                `;

            });

        }
    );

}


// ======================================================
// CATEGORY FILTER
// ======================================================

function setupCategories() {

    const categoryButtons =
        document.querySelectorAll(".category-btn");


    categoryButtons.forEach(button => {

        button.addEventListener(
            "click",
            function () {

                const categoryId =
                    this.dataset.id;


                const productsContainer =
                    document.getElementById("products");


                if (!productsContainer) {
                    return;
                }


                productsContainer.innerHTML = `
                    <div class="search-loading">
                        Loading products...
                    </div>
                `;


                const url =
                    "search_product.php?category_id="
                    + encodeURIComponent(categoryId);


                fetch(url, {
                    method: "GET",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                })


                .then(response => {

                    if (!response.ok) {

                        throw new Error(
                            "HTTP Error " +
                            response.status
                        );

                    }

                    return response.text();

                })


                .then(data => {

                    productsContainer.innerHTML =
                        data;


                    activateProducts();

                })


                .catch(error => {

                    console.error(
                        "Category error:",
                        error
                    );


                    productsContainer.innerHTML = `
                        <div class="search-error">
                            Unable to load products.
                        </div>
                    `;

                });

            }
        );

    });

}


// ======================================================
// HTML SAFETY
// ======================================================

function escapeHtml(value) {

    return String(value)

        .replace(/&/g, "&amp;")

        .replace(/</g, "&lt;")

        .replace(/>/g, "&gt;")

        .replace(/"/g, "&quot;")

        .replace(/'/g, "&#039;");

}
// ======================================================
// COMPLETE SALE
// ======================================================

function setupCompleteSale() {

    const saleButton =
        document.getElementById("complete-sale");

    console.log("SALE BUTTON:", saleButton);

    if (!saleButton) {

        console.error(
            "Complete Sale button not found"
        );

        return;
    }

    saleButton.addEventListener(
        "click",
        completeSale
    );

}


// ======================================================
// PROCESS SALE
// ======================================================

async function completeSale() {

    console.log("COMPLETE SALE CLICKED");

    if (cart.length === 0) {

        alert(
            "Please add at least one product to the cart."
        );

        return;
    }


    const paymentElement =
        document.querySelector(
            'input[name="payment"]:checked'
        );


    const paymentMethod =
        paymentElement
            ? paymentElement.value
            : "Cash";


    const amountElement =
        document.getElementById("amount");


    const amountPaid =
        parseFloat(
            amountElement.value
        ) || 0;


    const customerElement =
        document.getElementById("customer");


    const customerId =
        customerElement.value || null;


    const totalElement =
        document.getElementById("total");


    const total =
        parseFloat(
            totalElement.textContent
        ) || 0;


    // Cash / M-Pesa

    if (
        (paymentMethod === "Cash" ||
         paymentMethod === "Mpesa") &&
        amountPaid < total
    ) {

        alert(
            "Amount paid is less than the sale total."
        );

        return;
    }


    // Credit requires customer

    if (
        (paymentMethod === "Credit" ||
         paymentMethod === "Installment") &&
        !customerId
    ) {

        alert(
            "Please select a customer for credit or installment sales."
        );

        return;
    }


    const saleButton =
        document.getElementById(
            "complete-sale"
        );


    saleButton.disabled = true;

    saleButton.textContent =
        "PROCESSING...";


    const saleData = {

        cart: cart.map(item => ({

            id: item.id,

            qty: item.qty

        })),

        payment_method:
            paymentMethod,

        amount_paid:
            amountPaid,

        customer_id:
            customerId,

        discount:
            0

    };


    console.log(
        "Sending sale:",
        saleData
    );


    try {

        const response =
            await fetch(
                "process_sale.php",
                {

                    method: "POST",

                    headers: {

                        "Content-Type":
                            "application/json",

                        "X-Requested-With":
                            "XMLHttpRequest"

                    },

                    body:
                        JSON.stringify(
                            saleData
                        )

                }
            );


        console.log(
            "HTTP STATUS:",
            response.status
        );


        const result =
            await response.json();


        console.log(
            "SALE RESPONSE:",
            result
        );


        if (!result.success) {

            alert(
                result.message ||
                "Sale failed."
            );

            saleButton.disabled = false;

            saleButton.textContent =
                "COMPLETE SALE";

            return;
        }


        alert(
            "SALE COMPLETED!\n\n" +

            "Invoice: " +
            result.invoice_no +

            "\nTotal: KSh " +
            result.total +

            "\nPaid: KSh " +
            result.amount_paid +
            "\nChange: KSh " +
            result.change +
            "\nBalance: KSh " +
            result.balance        );


        /*
        Clear cart
        */

        cart = [];


        /*
        Clear amount
        */

        amountElement.value = "";


        /*
        Reset customer
        */

        customerElement.value = "";


        /*
        Refresh cart
        */

        displayCart();


        /*
        Reload POS
        */

        window.location.reload();


    } catch (error) {

        console.error(
            "SALE ERROR:",
            error
        );


        alert(
            "Could not connect to the sale processor."
        );


        saleButton.disabled = false;

        saleButton.textContent =
            "COMPLETE SALE";

    }

}