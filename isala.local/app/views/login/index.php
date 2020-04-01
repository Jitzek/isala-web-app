<?php
?>

<!DOCTYPE html>
<html>

<head>
    <title>
        <?= $data['title'] ?>
    </title>
</head>

<body>
    <h1>Login Page</h1>
    <?php if ($data['2fa'] === true) : ?>
        <form method="post" action="">
            <input type="text" name="2fa_code" />
            <button type="submit" name="2fa_submit" value="2fa_submit">
                2FA
            </button>
        </form>
    <?php else : ?>
        <form method="post" action="">
            <input type="text" name="uid" />
            <input type="password" name="passwd" />
            <button type="submit" name="login" value="login">
                Log In
            </button>
        </form>
    <?php endif; ?>
</body>

</html>