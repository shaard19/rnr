<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";


/*
|--------------------------------------------------------------------------
| Permission
|--------------------------------------------------------------------------
*/

if (!hasPermission('manage_settings')) {
    die("Access Denied");
}


/*
|--------------------------------------------------------------------------
| Load Current Settings
|--------------------------------------------------------------------------
*/

$settingsQuery = mysqli_query(
    $conn,
    "SELECT *
     FROM settings
     ORDER BY id ASC
     LIMIT 1"
);

$settings = mysqli_fetch_assoc($settingsQuery);


/*
|--------------------------------------------------------------------------
| Default Values
|--------------------------------------------------------------------------
*/

$business_name = $settings['business_name'] ?? '';
$phone         = $settings['phone'] ?? '';
$email         = $settings['email'] ?? '';
$address       = $settings['address'] ?? '';
$currency      = $settings['currency'] ?? 'KES';
$tax_rate      = $settings['tax_rate'] ?? '16.00';
$logo          = $settings['logo'] ?? '';

$message = '';
$message_type = '';


/*
|--------------------------------------------------------------------------
| Save Settings
|--------------------------------------------------------------------------
*/

if (isset($_POST['save_settings'])) {

    $business_name = trim($_POST['business_name']);
    $phone         = trim($_POST['phone']);
    $email         = trim($_POST['email']);
    $address       = trim($_POST['address']);
    $currency      = trim($_POST['currency']);
    $tax_rate      = trim($_POST['tax_rate']);


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($business_name === '') {

        $message = "Business name is required.";
        $message_type = "error";

    } elseif ($currency === '') {

        $message = "Currency is required.";
        $message_type = "error";

    } elseif (!is_numeric($tax_rate) || $tax_rate < 0 || $tax_rate > 100) {

        $message = "Tax rate must be between 0 and 100.";
        $message_type = "error";

    } else {


        /*
        |--------------------------------------------------------------------------
        | Update Existing Settings
        |--------------------------------------------------------------------------
        */

        if ($settings) {

            $stmt = mysqli_prepare(
                $conn,
                "
                UPDATE settings
                SET
                    business_name = ?,
                    phone = ?,
                    email = ?,
                    address = ?,
                    currency = ?,
                    tax_rate = ?
                WHERE id = ?
                "
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ssssssi",
                $business_name,
                $phone,
                $email,
                $address,
                $currency,
                $tax_rate,
                $settings['id']
            );


        /*
        |--------------------------------------------------------------------------
        | Create Settings Record
        |--------------------------------------------------------------------------
        */

        } else {

            $stmt = mysqli_prepare(
                $conn,
                "
                INSERT INTO settings
                (
                    business_name,
                    phone,
                    email,
                    address,
                    currency,
                    tax_rate
                )
                VALUES (?, ?, ?, ?, ?, ?)
                "
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ssssss",
                $business_name,
                $phone,
                $email,
                $address,
                $currency,
                $tax_rate
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Execute
        |--------------------------------------------------------------------------
        */

        if (mysqli_stmt_execute($stmt)) {

            $message = "Settings saved successfully.";
            $message_type = "success";

            /*
            | Refresh settings data
            */

            $settingsQuery = mysqli_query(
                $conn,
                "SELECT *
                 FROM settings
                 ORDER BY id ASC
                 LIMIT 1"
            );

            $settings = mysqli_fetch_assoc($settingsQuery);

        } else {

            $message =
                "Unable to save settings: " .
                mysqli_error($conn);

            $message_type = "error";
        }
    }
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

<title>
Settings - R&R Collection
</title>


<link
    rel="stylesheet"
    href="../../assets/css/style.css"
>


<link
    rel="stylesheet"
    href="../../assets/css/users.css"
>


</head>


<body>


<div class="users-container">


<!-- =====================================================
     HEADER
====================================================== -->

<div class="users-header">

    <div>

        <h1>
            System Settings
        </h1>

        <p>
            Manage your business and POS settings
        </p>

    </div>

</div>



<!-- =====================================================
     SETTINGS CARD
====================================================== -->

<div class="user-form-card">


<?php if ($message !== ''): ?>

<div class="form-message <?= $message_type; ?>">

    <?= htmlspecialchars($message); ?>

</div>

<?php endif; ?>



<form method="POST">


<!-- =====================================================
     BUSINESS INFORMATION
====================================================== -->

<h3 class="settings-section-title">
    Business Information
</h3>


<div class="form-grid">


<div class="form-group">

<label>
Business Name
</label>

<input
    type="text"
    name="business_name"
    value="<?= htmlspecialchars($business_name); ?>"
    placeholder="Enter business name"
    required
>

</div>



<div class="form-group">

<label>
Phone
</label>

<input
    type="text"
    name="phone"
    value="<?= htmlspecialchars($phone); ?>"
    placeholder="Enter business phone"
>

</div>



<div class="form-group">

<label>
Email
</label>

<input
    type="email"
    name="email"
    value="<?= htmlspecialchars($email); ?>"
    placeholder="Enter business email"
>

</div>



<div class="form-group">

<label>
Currency
</label>

<select name="currency" required>

<option
    value="KES"
    <?= $currency === 'KES' ? 'selected' : ''; ?>
>
    KES - Kenyan Shilling
</option>

<option
    value="USD"
    <?= $currency === 'USD' ? 'selected' : ''; ?>
>
    USD - US Dollar
</option>

<option
    value="EUR"
    <?= $currency === 'EUR' ? 'selected' : ''; ?>
>
    EUR - Euro
</option>

<option
    value="GBP"
    <?= $currency === 'GBP' ? 'selected' : ''; ?>
>
    GBP - British Pound
</option>

</select>

</div>


</div>



<!-- ADDRESS -->

<div class="form-group">

<label>
Business Address
</label>

<textarea
    name="address"
    rows="4"
    placeholder="Enter business address"
><?= htmlspecialchars($address); ?></textarea>

</div>



<!-- =====================================================
     TAX SETTINGS
====================================================== -->

<h3 class="settings-section-title">
    Financial Settings
</h3>


<div class="form-grid">


<div class="form-group">

<label>
Tax Rate (%)
</label>

<input
    type="number"
    name="tax_rate"
    value="<?= htmlspecialchars($tax_rate); ?>"
    min="0"
    max="100"
    step="0.01"
    placeholder="16.00"
>

<small class="settings-help">
    Enter tax rate as a percentage.
    Example: 16 means 16%.
</small>

</div>


</div>



<!-- =====================================================
     ACTIONS
====================================================== -->

<div class="form-actions">

<button
    type="submit"
    name="save_settings"
    class="save-btn"
>
    Save Settings
</button>

</div>


</form>


</div>


</div>


</body>

</html>