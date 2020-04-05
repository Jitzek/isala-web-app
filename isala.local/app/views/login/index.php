<?php
?>

<!DOCTYPE html>
<html>

<head>
    <title>
        <?= htmlentities($data['title']) ?>
    </title>
    <link rel="stylesheet" href="/public/css/login.css">

</head>

<body>
    <div id="container">
        <div id="image">
        </div>
        <div id="mobile">
            <?php if ($data['2fa'] === true) : ?>
                <form method="post" action="">
                    <img src="/public/imgs/isala-logo.png">
                    <input type="text" name="2fa_code" placeholder="2FA" />
                    <br>
                    <p style="color: crimson; max-width: 350px;"><?= htmlentities($data['err_msg']); ?></p>
                    <button type="submit" name="2fa_submit" value="2fa_submit">
                        2FA
                    </button>
                </form>
            <?php else : ?>
                <form method="post" action="">
                    <img src="/public/imgs/isala-logo.png">
                    <input type="text" name="uid" placeholder="UID" />
                    <br>
                    <input type="password" name="passwd" placeholder="Password" />
                    <br>
                    <p style="color: crimson; max-width: 350px;"><?= htmlentities($data['err_msg']); ?></p>
                    <button type="submit" name="login" value="login">
                        Log In
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <script src="/public/js/login.js" type="text/javascript"> </script>
</body>

</html>