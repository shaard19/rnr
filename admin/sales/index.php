
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
|
| IMPORTANT:
| Products table uses:
| Active / Inactive
|
| It does NOT use:
| Available / Out of Stock
|
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
    LIMIT 20
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


    <!-- POS CSS -->

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

                <option
                    value="<?= (int)$row['id']; ?>"
                >

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


            <!-- CATEGORIES -->

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


            <!-- PRODUCTS -->

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


                            <!-- PRODUCT IMAGE -->

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


                            <!-- PRODUCT CODE -->

                            <small>

                                <?= htmlspecialchars(
                                    $product['product_code']
                                ); ?>

                            </small>


                            <!-- PRODUCT NAME -->

                            <h4>

                                <?= htmlspecialchars(
                                    $product['product_name']
                                ); ?>

                            </h4>


                            <!-- PRICE -->

                            <p>

                                KSh
                                <?= number_format(
                                    (float)$product['selling_price'],
                                    2
                                ); ?>

                            </p>


                            <!-- STOCK -->

                            <p>

                                Stock:
                                <?= (int)$product['quantity']; ?>

                                <?php if (!empty($product['unit'])): ?>

                                    <?= htmlspecialchars(
                                        $product['unit']
                                    ); ?>

                                <?php endif; ?>

                            </p>


                            <!-- ADD TO CART -->

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

                        <th>
                            Product
                        </th>

                        <th>
                            Qty
                        </th>

                        <th>
                            Price
                        </th>

                        <th>
                            Total
                        </th>

                        <th>
                        </th>

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


                <label>

                    <input
                        type="radio"
                        name="payment"
                        value="Cash"
                        checked
                    >

                    Cash

                </label>


                <label>

                    <input
                        type="radio"
                        name="payment"
                        value="Mpesa"
                    >

                    M-Pesa

                </label>


                <label>

                    <input
                        type="radio"
                        name="payment"
                        value="Credit"
                    >

                    Credit

                </label>


                <br>
                <br>


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


<!-- SALES JAVASCRIPT -->

<script src="../../assets/js/sales.js?v=3"></script>


</body>

</html>
```
