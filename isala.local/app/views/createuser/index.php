<?php
?>

<!DOCTYPE html>
<html>
<head>
    <title>
        <?= htmlentities($data['title']) ?>
    </title>
    <link rel="stylesheet" href="../../public/css/home.css">
</head>
<body>
<div id="accountinput">
    <h3>Maak nieuw account aan</h3>
    <form method="post" action="/public/create_user">
    <p>Voornaam:</p>
    <input type="text" name="voornaam" />
    <p>Achternaam:</p>
    <input type="text" name="sn" />
    <p>BSN:</p>
    <input type="text" name="uid" />
    <p>Wachtwoord:</p>
    <input type="password" name="passwd" />
        <br>
    <button type="submit" name="create_user" value="create_user">
        Submit
    </button>
    </form>
</div>

</body>
<footer>
</footer>
</html>