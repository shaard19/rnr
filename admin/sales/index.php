<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";


if (!hasPermission('make_sales')) {
    die("Access Denied");
}


/*
 Load Categories
*/

$categories = mysqli_query(
    $conn,
    "
    SELECT id, category_name
    FROM categories
    WHERE status='Active'
    ORDER BY category_name ASC
    "
);


/*
 Load Customers
*/

$customers = mysqli_query(
    $conn,
    "
    SELECT id, customer_name
    FROM customers
    ORDER BY customer_name ASC
    "
);


/*
 Default products
*/

$products = mysqli_query(
    $conn,
    "
    SELECT 
    id,
    product_name,
    selling_price,
    quantity,
    image

    FROM products

    WHERE status='Available'

    ORDER BY product_name ASC

    LIMIT 20
    "
);


?>


<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>POS - R&R Collection</title>

<link rel="stylesheet" href="../../assets/css/style.css">
<link rel="stylesheet" href="../../assets/css/sales.css">
</head>


<body>


<div class="pos-container">


<h1>
R&R Collection POS
</h1>



<!-- Search -->

<div class="search-box">

<input 
type="text"
id="productSearch"
placeholder="Search product or scan barcode..."
>


</div>




<!-- Customer -->

<div class="customer-box">


<label>
Customer
</label>


<select id="customer">

<option value="">
Walk In Customer
</option>


<?php while($row=mysqli_fetch_assoc($customers)){ ?>

<option value="<?= $row['id']; ?>">

<?= $row['customer_name']; ?>

</option>


<?php } ?>


</select>


</div>





<div class="pos-body">



<!-- LEFT SIDE -->

<div class="products-section">


<h3>
Categories
</h3>


<div class="categories">


<?php while($cat=mysqli_fetch_assoc($categories)){ ?>


<button 
class="category-btn"
data-id="<?= $cat['id']; ?>"
>

<?= $cat['category_name']; ?>

</button>


<?php } ?>


</div>




<h3>
Products
</h3>


<div class="products-grid" id="products">
    


<?php while($product=mysqli_fetch_assoc($products)){ ?>


<div class="product-card">


<?php if($product['image']){ ?>

<img src="../../uploads/products/<?= $product['image']; ?>">

<?php } ?>


<h4>
<?= $product['product_name']; ?>
</h4>


<p>
KSh <?= number_format($product['selling_price'],2); ?>
</p>


<p>
Stock:
<?= $product['quantity']; ?>
</p>


<button
class="add-cart"
data-id="<?= $product['id']; ?>"
data-name="<?= $product['product_name']; ?>"
data-price="<?= $product['selling_price']; ?>"
>

Add

</button>


</div>



<?php } ?>


</div>



</div>






<!-- RIGHT SIDE CART -->


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



<div class="totals">


<p>
Subtotal:
<span id="subtotal">
0
</span>
</p>


<p>
Discount:
<span>
0
</span>
</p>



<h2>

TOTAL:

KSh <span id="total">
0

</span>

</h2>


</div>




<div class="payment">


<h3>
Payment
</h3>



<label>

<input type="radio" name="payment" value="Cash" checked>

Cash

</label>


<label>

<input type="radio" name="payment" value="Mpesa">

M-Pesa

</label>


<label>

<input type="radio" name="payment" value="Credit">

Credit

</label>



<br><br>



<input 
type="number"
id="amount"
placeholder="Amount Paid"
>


<br>


<p>

Change:

<span id="change">
0

</span>

</p>



<button id="complete-sale">

COMPLETE SALE

</button>


</div>




</div>


</div>



</div>


<script src="../../assets/js/sales.js"></script>


</body>

</html>