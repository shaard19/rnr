
<div class="topbar">

    <h2>R&R Collection POS</h2>

    <div class="user-info">

        <div>

            <strong>
                <?php echo htmlspecialchars($_SESSION['fullname']); ?>
            </strong>
            <br>

            <small>
                <?php echo htmlspecialchars($_SESSION['role']); ?>
            </small>

        </div>

        <div class="avatar">

            <?php echo strtoupper(substr($_SESSION['fullname'], 0, 1)); ?>

        </div>

    </div>

</div>