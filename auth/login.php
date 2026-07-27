<?php
session_start();

if (isset($_SESSION['user_id'])) {

    if ($_SESSION['role'] == "Admin") {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../cashier/dashboard.php");
    }

    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login | R&R Collection POS</title>

<link rel="stylesheet" href="../assets/css/login.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<div class="login-container">

<div class="login-card">

<div class="logo">

<i class="fa-solid fa-store"></i>

</div>

<h1>R&R Collection</h1>

<p>POS & Inventory Management System</p>

<?php if(isset($_GET['error'])) { ?>

<div class="error">

Invalid username or password.

</div>

<?php } ?>

<form action="authenticate.php" method="POST">

<div class="input-group">

<i class="fa-solid fa-user"></i>

<input
type="text"
name="username"
placeholder="Username"
required>

</div>

<div class="input-group">

<i class="fa-solid fa-lock"></i>

<input
type="password"
name="password"
placeholder="Password"
required
id="password">

<span class="toggle-password">

<i class="fa-solid fa-eye"></i>

</span>

</div>

<button type="submit">

<i class="fa-solid fa-right-to-bracket"></i>

Login

</button>

</form>

<div class="footer">
© <?php echo date("Y"); ?> Developed By Shadrack K Otido, BSIT<br>
 <?php ?> ID_LABs<br>
</div>

</div>

</div>

<script>

const eye=document.querySelector(".toggle-password");

const password=document.querySelector("#password");

eye.onclick=function(){

if(password.type==="password"){

password.type="text";

eye.innerHTML='<i class="fa-solid fa-eye-slash"></i>';

}else{

password.type="password";

eye.innerHTML='<i class="fa-solid fa-eye"></i>';

}

}

</script>

</body>

</html>