<?php
?>

<!DOCTYPE html>
<html>

<head>
    <title>
        <?= htmlentities($data['title']) ?>
    </title>
</head>

<body>
    <h1>Change Password Page</h1>
    <form method="post" action="/public/changepassword">
        <p>type previous password:</p>
        <input type="password" name="prev_password" />
        <p>type new password:</p>
        <input type="password" name="new_password" />
        <p>Type new password again:</p>
        <input type="password" name="new_password2" />
        <button type="submit" name="change_password" value="change_password">
            Submit
        </button>
    </form>
    <p style="color: #FC240F"><?= htmlentities(isset($data['err_msg']) ? $data['err_msg'] : ''); ?></p>
</body>

</html>