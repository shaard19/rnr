<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";


/*
|--------------------------------------------------------------------------
| PERMISSION CHECK
|--------------------------------------------------------------------------
*/

if (!hasPermission('make_sale')) {
    die("Access Denied");
}


/*
|--------------------------------------------------------------------------
| LOAD CATEGORIES
|--------------------------------------------------------------------------
*/

$categories = mysqli_query(
    $conn,
    "
    SELECT
        id,
        category_name
    FROM categories
    WHERE status = 'Active'
    ORDER BY category_name ASC
    "
);

if (!$categories) {
    die("Categories Query Error: " . mysqli_error($conn));
}


/*
|--------------------------------------------------------------------------
| LOAD CUSTOMERS
|--------------------------------------------------------------------------
*/

$customers = mysqli_query(
    $conn,
    "
    SELECT
        id,
        customer_name
    FROM customers
    ORDER BY customer_name ASC
    "
);

if (!$customers) {
    die("Customers Query Error: " . mysqli_error($conn));
}


/*
|--------------------------------------------------------------------------
| LOAD PRODUCTS
|--------------------------------------------------------------------------
*/

$products = mysqli_query(
    $conn,
    "
    SELECT
        id,
        product_code,
        product_name,
        category_id,
        selling_price,
        quantity,
        unit,
        image
    FROM products
    WHERE status = 'Active'
    ORDER BY product_name ASC
    "
);

if (!$products) {
    die("Products Query Error: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>POS - R&R Collection</title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

    <link
        rel="stylesheet"
        href="../../assets/css/sales.css"
    >

</head>


<body>


<div class="pos-container">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <h1>
        R&R Collection POS
    </h1>


    <!-- =====================================================
         PRODUCT SEARCH
    ====================================================== -->

    <div class="search-box">

        <input
            type="text"
            id="productSearch"
            placeholder="Search product or product code..."
            autocomplete="off"
        >

    </div>


    <!-- =====================================================
         CUSTOMER
    ====================================================== -->

    <div class="customer-box">

        <label for="customer">
            Customer
        </label>

        <select id="customer">

            <option value="">
                Walk In Customer
            </option>

            <?php while ($row = mysqli_fetch_assoc($customers)): ?>

                <option value="<?= (int)$row['id']; ?>">

                    <?= htmlspecialchars(
                        $row['customer_name']
                    ); ?>

                </option>

            <?php endwhile; ?>

        </select>

    </div>


    <!-- =====================================================
         POS BODY
    ====================================================== -->

    <div class="pos-body">


        <!-- =================================================
             PRODUCTS SECTION
        ================================================== -->

        <div class="products-section">

            <h3>
                Categories
            </h3>

            <div class="categories">

                <?php while ($cat = mysqli_fetch_assoc($categories)): ?>

                    <button
                        type="button"
                        class="category-btn"
                        data-id="<?= (int)$cat['id']; ?>"
                    >

                        <?= htmlspecialchars(
                            $cat['category_name']
                        ); ?>

                    </button>

                <?php endwhile; ?>

            </div>


            <h3>
                Products
            </h3>


            <div
                class="products-grid"
                id="products"
            >

                <?php if (mysqli_num_rows($products) > 0): ?>

                    <?php while ($product = mysqli_fetch_assoc($products)): ?>

                        <div
                            class="product-card"
                            data-id="<?= (int)$product['id']; ?>"
                            data-category="<?= (int)$product['category_id']; ?>"
                            data-name="<?= htmlspecialchars(
                                strtolower($product['product_name']),
                                ENT_QUOTES
                            ); ?>"
                            data-code="<?= htmlspecialchars(
                                strtolower($product['product_code']),
                                ENT_QUOTES
                            ); ?>"
                        >

                            <?php if (!empty($product['image'])): ?>

                                <img
                                    src="../../uploads/products/<?= htmlspecialchars(
                                        $product['image']
                                    ); ?>"
                                    alt="<?= htmlspecialchars(
                                        $product['product_name']
                                    ); ?>"
                                >

                            <?php endif; ?>


                            <small>

                                <?= htmlspecialchars(
                                    $product['product_code']
                                ); ?>

                            </small>


                            <h4>

                                <?= htmlspecialchars(
                                    $product['product_name']
                                ); ?>

                            </h4>


                            <p>

                                KSh
                                <?= number_format(
                                    (float)$product['selling_price'],
                                    2
                                ); ?>

                            </p>


                            <p>

                                Stock:
                                <?= (int)$product['quantity']; ?>

                                <?php if (!empty($product['unit'])): ?>

                                    <?= htmlspecialchars(
                                        $product['unit']
                                    ); ?>

                                <?php endif; ?>

                            </p>


                            <button
                                type="button"
                                class="add-cart"
                                data-id="<?= (int)$product['id']; ?>"
                                data-name="<?= htmlspecialchars(
                                    $product['product_name'],
                                    ENT_QUOTES
                                ); ?>"
                                data-price="<?= (float)$product['selling_price']; ?>"
                            >

                                Add

                            </button>

                        </div>

                    <?php endwhile; ?>

                <?php else: ?>

                    <p>
                        No active products found.
                    </p>

                <?php endif; ?>

            </div>

        </div>


        <!-- =================================================
             CART SECTION
        ================================================== -->

        <div class="cart-section">

            <h3>
                Cart
            </h3>


            <table>

                <thead>

                    <tr>

                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th></th>

                    </tr>

                </thead>


                <tbody id="cart">

                </tbody>

            </table>


            <!-- =================================================
                 TOTALS
            ================================================== -->

            <div class="totals">

                <p>

                    Subtotal:

                    <span id="subtotal">
                        0
                    </span>

                </p>


                <p>

                    Discount:

                    <span id="discount">
                        0
                    </span>

                </p>


                <h2>

                    TOTAL:

                    KSh

                    <span id="total">
                        0
                    </span>

                </h2>

            </div>


            <!-- =================================================
                 PAYMENT
            ================================================== -->

            <div class="payment">

                <h3>
                    Payment
                </h3>


                <!-- CASH -->

                <label>

                    <input
                        type="radio"
                        name="payment"
                        value="Cash"
                        checked
                    >

                    Cash

                </label>


                <!-- MPESA -->

                <label>

                    <input
                        type="radio"
                        name="payment"
                        value="Lipa na M-Pesa"
                    >

                    Lipa na M-Pesa

                </label>


                <!-- CREDIT -->

                <label>

                    <input
                        type="radio"
                        name="payment"
                        value="Credit"
                    >

                    Credit

                </label>


                <!-- =================================================
                     MPESA SECTION
                ================================================== -->

                <div
                    id="mpesa-section"
                    style="display:none; margin-top:15px;"
                >

                    <h4>
                        M-Pesa Payment
                    </h4>


                    <!-- STK PUSH -->

                    <label>

                        <input
                            type="radio"
                            name="mpesa_mode"
                            value="STK_PUSH"
                            checked
                        >

                        Send STK Push

                    </label>


                    <!-- DIRECT TILL -->

                    <label>

                        <input
                            type="radio"
                            name="mpesa_mode"
                            value="DIRECT_TILL"
                        >

                        Customer Paid Directly to Till

                    </label>


                    <!-- =================================================
                         STK PUSH DETAILS
                    ================================================== -->

                    <div
                        id="stk-section"
                        style="margin-top:10px;"
                    >

                        <label for="mpesa-phone">

                            Customer M-Pesa Number

                        </label>

                        <input
                            type="tel"
                            id="mpesa-phone"
                            placeholder="0712345678"
                            autocomplete="off"
                        >

                        <small>

                            The M-Pesa prompt will be sent
                            to this number.

                        </small>

                    </div>


                    <!-- =================================================
                         DIRECT TILL DETAILS
                    ================================================== -->

                    <div
                        id="direct-till-section"
                        style="display:none; margin-top:10px;"
                    >

                        <label for="mpesa-transaction-code">

                            M-Pesa Transaction Code

                        </label>

                        <input
                            type="text"
                            id="mpesa-transaction-code"
                            placeholder="e.g. DFG74754GH"
                            maxlength="50"
                            autocomplete="off"
                        >

                        <small>

                            Enter the transaction code
                            shown on the customer's M-Pesa
                            confirmation.

                        </small>

                    </div>

                </div>


                <!-- =================================================
                     AMOUNT PAID
                ================================================== -->

                <input
                    type="number"
                    id="amount"
                    placeholder="Amount Paid"
                    min="0"
                    step="0.01"
                >


                <br>


                <p>

                    Change:

                    <span id="change">
                        0
                    </span>

                </p>


                <button
                    type="button"
                    id="complete-sale"
                >

                    COMPLETE SALE

                </button>

            </div>

        </div>

    </div>

</div>


<script src="../../assets/js/sales.js?v=4"></script>

</body>

</html>