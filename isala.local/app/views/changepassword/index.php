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
    <h1>Change Password Page</h1>
    <form method="post" action="/public/changepassword">
        <input type="password" name="prev_password" />
        <input type="password" name="new_password" />
        <input type="password" name="new_password2" />
        <button type="submit" name="change_password" value="change_password">
            Submit
        </button>
    </form>
</body>

</html>