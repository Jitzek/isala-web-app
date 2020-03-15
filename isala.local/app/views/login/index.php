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
    <form method="post" action="/public/login">
        <input type="radio" name="group" value="developers" />
        <label>Developer</label><br>
        <input type="radio" name="group" value="patienten" />
        <label>Patiënt</label><br>
        <input type="radio" name="group" value="dokters" />
        <label>Dokter</label><br>
        <input type="radio" name="group" value="anders" />
        <label>Anders</label><br>
        <br>
        <input type="text" name="uid" />
        <input type="password" name="passwd" />
        <button type="submit" name="login" value="login">
            Submit
        </button>
    </form>
</body>

</html>