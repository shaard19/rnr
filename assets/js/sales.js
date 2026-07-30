console.log("RNR SALES JS - CREDIT FIX");

document.addEventListener("DOMContentLoaded", function () {

    console.log("POS JAVASCRIPT READY");


    /*
    |--------------------------------------------------------------------------
    | ELEMENTS
    |--------------------------------------------------------------------------
    */

    const searchInput =
        document.getElementById("productSearch");

    const productsContainer =
        document.getElementById("products");

    const cartContainer =
        document.getElementById("cart");

    const customerSelect =
        document.getElementById("customer");

    const subtotalElement =
        document.getElementById("subtotal");

    const discountElement =
        document.getElementById("discount");

    const totalElement =
        document.getElementById("total");

    const amountInput =
        document.getElementById("amount");

    const changeElement =
        document.getElementById("change");

    const completeSaleButton =
        document.getElementById("complete-sale");


    /*
    |--------------------------------------------------------------------------
    | CART
    |--------------------------------------------------------------------------
    */

    let cart = [];


    /*
    |--------------------------------------------------------------------------
    | FORMAT MONEY
    |--------------------------------------------------------------------------
    */

    function formatMoney(value) {

        return Number(value).toLocaleString("en-US", {

            minimumFractionDigits: 2,

            maximumFractionDigits: 2

        });

    }


    /*
    |--------------------------------------------------------------------------
    | ESCAPE HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {

        const div =
            document.createElement("div");

        div.textContent = value;

        return div.innerHTML;

    }


    /*
    |--------------------------------------------------------------------------
    | GET CART TOTAL
    |--------------------------------------------------------------------------
    */

    function getCartTotal() {

        let total = 0;

        cart.forEach(function (item) {

            total +=
                Number(item.price) *
                Number(item.quantity);

        });

        return total;

    }


    /*
    |--------------------------------------------------------------------------
    | PRODUCT SEARCH
    |--------------------------------------------------------------------------
    */

    if (searchInput) {

        searchInput.addEventListener(
            "input",
            function () {

                const search =
                    this.value
                        .trim()
                        .toLowerCase();


                document
                    .querySelectorAll(".product-card")
                    .forEach(function (card) {

                        const name =
                            card.dataset.name || "";

                        const code =
                            card.dataset.code || "";


                        if (
                            name.includes(search) ||
                            code.includes(search)
                        ) {

                            card.style.display = "";

                        } else {

                            card.style.display = "none";

                        }

                    });

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | CATEGORY FILTER
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(".category-btn")
        .forEach(function (button) {

            button.addEventListener(
                "click",
                function () {

                    const categoryId =
                        this.dataset.id;


                    if (searchInput) {

                        searchInput.value = "";

                    }


                    document
                        .querySelectorAll(".product-card")
                        .forEach(function (card) {

                            if (
                                card.dataset.category ===
                                categoryId
                            ) {

                                card.style.display = "";

                            } else {

                                card.style.display = "none";

                            }

                        });

                }
            );

        });


    /*
    |--------------------------------------------------------------------------
    | ADD PRODUCT TO CART
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        "click",
        function (event) {

            const button =
                event.target.closest(".add-cart");


            if (!button) {

                return;

            }


            const productId =
                button.dataset.id;

            const productName =
                button.dataset.name;

            const productPrice =
                parseFloat(
                    button.dataset.price
                );


            if (
                !productId ||
                !productName ||
                isNaN(productPrice)
            ) {

                alert(
                    "Invalid product information."
                );

                return;

            }


            const existingProduct =
                cart.find(function (item) {

                    return item.id === productId;

                });


            if (existingProduct) {

                existingProduct.quantity++;

            } else {

                cart.push({

                    id: productId,

                    name: productName,

                    price: productPrice,

                    quantity: 1

                });

            }


            renderCart();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | RENDER CART
    |--------------------------------------------------------------------------
    */

    function renderCart() {

        if (!cartContainer) {

            return;

        }


        cartContainer.innerHTML = "";


        if (cart.length === 0) {

            cartContainer.innerHTML = `

                <tr>

                    <td
                        colspan="5"
                        style="text-align:center;"
                    >

                        Cart is empty

                    </td>

                </tr>

            `;


            updateTotals();

            return;

        }


        cart.forEach(function (item, index) {

            const row =
                document.createElement("tr");


            const itemTotal =
                Number(item.price) *
                Number(item.quantity);


            row.innerHTML = `

                <td>
                    ${escapeHtml(item.name)}
                </td>

                <td>

                    <button
                        type="button"
                        class="qty-minus"
                        data-index="${index}"
                    >
                        −
                    </button>

                    <strong style="margin:0 8px;">
                        ${item.quantity}
                    </strong>

                    <button
                        type="button"
                        class="qty-plus"
                        data-index="${index}"
                    >
                        +
                    </button>

                </td>

                <td>
                    KSh ${formatMoney(item.price)}
                </td>

                <td>
                    KSh ${formatMoney(itemTotal)}
                </td>

                <td>

                    <button
                        type="button"
                        class="remove-cart"
                        data-index="${index}"
                    >
                        Remove
                    </button>

                </td>

            `;


            cartContainer.appendChild(row);

        });


        updateTotals();

    }


    /*
    |--------------------------------------------------------------------------
    | CART BUTTONS
    |--------------------------------------------------------------------------
    */

    if (cartContainer) {

        cartContainer.addEventListener(
            "click",
            function (event) {

                const plusButton =
                    event.target.closest(".qty-plus");


                if (plusButton) {

                    const index =
                        parseInt(
                            plusButton.dataset.index
                        );


                    if (cart[index]) {

                        cart[index].quantity++;

                        renderCart();

                    }


                    return;

                }


                const minusButton =
                    event.target.closest(".qty-minus");


                if (minusButton) {

                    const index =
                        parseInt(
                            minusButton.dataset.index
                        );


                    if (cart[index]) {

                        cart[index].quantity--;


                        if (
                            cart[index].quantity <= 0
                        ) {

                            cart.splice(index, 1);

                        }


                        renderCart();

                    }


                    return;

                }


                const removeButton =
                    event.target.closest(".remove-cart");


                if (removeButton) {

                    const index =
                        parseInt(
                            removeButton.dataset.index
                        );


                    if (!isNaN(index)) {

                        cart.splice(index, 1);

                        renderCart();

                    }

                }

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE TOTALS
    |--------------------------------------------------------------------------
    */

    function updateTotals() {

        const subtotal =
            getCartTotal();


        const discount = 0;


        const total =
            subtotal - discount;


        if (subtotalElement) {

            subtotalElement.textContent =
                formatMoney(subtotal);

        }


        if (discountElement) {

            discountElement.textContent =
                formatMoney(discount);

        }


        if (totalElement) {

            totalElement.textContent =
                formatMoney(total);

        }


        updateChange();

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE PAYMENT DISPLAY
    |--------------------------------------------------------------------------
    */

    function updateChange() {

        if (!changeElement) {

            return;

        }


        const total =
            getCartTotal();


        const amountPaid =
            parseFloat(
                amountInput?.value
            ) || 0;


        if (
            amountPaid <= 0 ||
            total <= 0
        ) {

            changeElement.textContent =
                "0.00";

            return;

        }


        const change =
            amountPaid - total;


        if (change >= 0) {

            changeElement.textContent =
                formatMoney(change);

        } else {

            /*
            |--------------------------------------------------------------------------
            | Partial payment / credit
            |--------------------------------------------------------------------------
            |
            | Do not display a negative change.
            |
            */

            changeElement.textContent =
                "0.00";

        }

    }


    /*
    |--------------------------------------------------------------------------
    | AMOUNT INPUT
    |--------------------------------------------------------------------------
    */

    if (amountInput) {

        amountInput.addEventListener(
            "input",
            function () {

                updateChange();

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | PAYMENT METHOD CHANGE
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(
            'input[name="payment"]'
        )
        .forEach(function (radio) {

            radio.addEventListener(
                "change",
                function () {

                    /*
                    |--------------------------------------------------------------------------
                    | IMPORTANT
                    |--------------------------------------------------------------------------
                    | DO NOT CLEAR AMOUNT FOR CREDIT.
                    |
                    | Credit can now be:
                    |
                    | 0 paid
                    | 100 paid
                    | 150 paid
                    | etc.
                    |
                    */

                    updateChange();

                }
            );

        });


    /*
    |--------------------------------------------------------------------------
    | COMPLETE SALE
    |--------------------------------------------------------------------------
    */

    if (completeSaleButton) {

        completeSaleButton.addEventListener(
            "click",
            async function () {

                console.log(
                    "COMPLETE SALE CLICKED"
                );


                /*
                |--------------------------------------------------------------------------
                | CART CHECK
                |--------------------------------------------------------------------------
                */

                if (cart.length === 0) {

                    alert(
                        "Please add a product to the cart first."
                    );

                    return;

                }


                /*
                |--------------------------------------------------------------------------
                | PAYMENT METHOD
                |--------------------------------------------------------------------------
                */

                const paymentInput =
                    document.querySelector(
                        'input[name="payment"]:checked'
                    );


                if (!paymentInput) {

                    alert(
                        "Please select a payment method."
                    );

                    return;

                }


                const paymentMethod =
                    paymentInput.value;


                /*
                |--------------------------------------------------------------------------
                | TOTAL
                |--------------------------------------------------------------------------
                */

                const total =
                    getCartTotal();


                /*
                |--------------------------------------------------------------------------
                | AMOUNT PAID
                |--------------------------------------------------------------------------
                */

                const amountPaid =
                    parseFloat(
                        amountInput?.value
                    ) || 0;


                /*
                |--------------------------------------------------------------------------
                | CUSTOMER
                |--------------------------------------------------------------------------
                */

                const customerId =
                    customerSelect &&
                    customerSelect.value !== ""
                        ? parseInt(
                            customerSelect.value
                        )
                        : null;


                /*
                |--------------------------------------------------------------------------
                | PAYMENT TYPE
                |--------------------------------------------------------------------------
                */

                const isCredit =
                    paymentMethod === "Credit";


                /*
                |--------------------------------------------------------------------------
                | CREDIT VALIDATION
                |--------------------------------------------------------------------------
                */

                if (isCredit) {

                    /*
                    | Credit requires customer.
                    */

                    if (!customerId) {

                        alert(
                            "Please select a registered customer for a credit sale."
                        );

                        return;

                    }


                    /*
                    | Partial credit is allowed.
                    |
                    | Example:
                    |
                    | Total = 160
                    | Paid  = 100
                    | Credit = 60
                    */

                    if (amountPaid > total) {

                        alert(
                            "Amount paid cannot exceed the sale total."
                        );

                        return;

                    }

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | CASH / MPESA
                    |--------------------------------------------------------------------------
                    */

                    if (amountPaid < total) {

                        alert(
                            "Amount paid is less than the total."
                        );

                        return;

                    }

                }


                /*
                |--------------------------------------------------------------------------
                | SALE DATA
                |--------------------------------------------------------------------------
                |
                | CRITICAL FIX:
                |
                | We now ALWAYS send the actual amountPaid.
                |
                | Previously:
                |
                | amount_paid: isCredit ? 0 : amountPaid
                |
                | That was forcing every credit sale to KSh 0.
                |
                */

                const saleData = {

                    customer_id:
                        customerId,

                    payment_method:
                        paymentMethod,

                    payment_status:
                        isCredit
                            ? "Credit"
                            : null,

                    amount_paid:
                        amountPaid,

                    cart:
                        cart.map(function (item) {

                            return {

                                id:
                                    parseInt(item.id),

                                quantity:
                                    parseInt(
                                        item.quantity
                                    )

                            };

                        })

                };


                console.log(
                    "SALE DATA:",
                    saleData
                );


                /*
                |--------------------------------------------------------------------------
                | PROCESSING
                |--------------------------------------------------------------------------
                */

                completeSaleButton.disabled =
                    true;


                completeSaleButton.textContent =
                    "PROCESSING...";


                try {

                    const response =
                        await fetch(
                            "process_sale.php",
                            {

                                method: "POST",

                                headers: {

                                    "Content-Type":
                                        "application/json",

                                    "Accept":
                                        "application/json"

                                },

                                body:
                                    JSON.stringify(
                                        saleData
                                    )

                            }
                        );


                    const responseText =
                        await response.text();


                    console.log(
                        "PHP RESPONSE:",
                        responseText
                    );


                    let data;


                    try {

                        data =
                            JSON.parse(
                                responseText
                            );

                    } catch (jsonError) {

                        console.error(
                            "JSON ERROR:",
                            jsonError
                        );


                        alert(
                            "Server returned an invalid response:\n\n" +
                            responseText
                        );

                        return;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | FAILED
                    |--------------------------------------------------------------------------
                    */

                    if (!data.success) {

                        alert(
                            data.message ||
                            "Sale could not be completed."
                        );

                        return;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | SUCCESS - OPEN RECEIPT
                    |--------------------------------------------------------------------------
                    */

                    if (data.sale_id) {

                        window.open(
                            "receipt.php?id=" +
                            encodeURIComponent(
                                data.sale_id
                            ),
                            "_blank",
                            "width=450,height=700"
                        );

                    } else {

                        alert(
                            "Sale completed successfully, " +
                            "but the receipt could not be opened."
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | CLEAR CART
                    |--------------------------------------------------------------------------
                    */

                    cart = [];

                    renderCart();


                    /*
                    |--------------------------------------------------------------------------
                    | CLEAR AMOUNT
                    |--------------------------------------------------------------------------
                    */

                    if (amountInput) {

                        amountInput.value = "";

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | RESET CUSTOMER
                    |--------------------------------------------------------------------------
                    */

                    if (customerSelect) {

                        customerSelect.value = "";

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | RESET CHANGE
                    |--------------------------------------------------------------------------
                    */

                    if (changeElement) {

                        changeElement.textContent =
                            "0.00";

                    }


                } catch (error) {

                    console.error(
                        "SALE PROCESSING ERROR:",
                        error
                    );


                    alert(

                        "Unable to connect to the sale processor.\n\n" +
                        error.message

                    );

                } finally {

                    completeSaleButton.disabled =
                        false;


                    completeSaleButton.textContent =
                        "COMPLETE SALE";

                }

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | INITIALIZE
    |--------------------------------------------------------------------------
    */

    renderCart();

    updateChange();


    console.log(
        "RNR POS READY"
    );

});