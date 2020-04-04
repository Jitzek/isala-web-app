<?php
?>

<!DOCTYPE html>
<html>

<head>
    <title>
        <?= htmlentities($data['title']) ?>
    </title>
    <link rel="stylesheet" href="../../public/css/createuser.css">
</head>

<body>
    <div id="accountinput">
        <h3>Maak nieuw account aan</h3>
        <form method="post" action="/public/createuser">
            <label>Voornaam:</label><br>
            <input type="text" name="cn" maxlength="128" value="<?= htmlentities(isset($data['prev_values']['cn']) ? $data['prev_values']['cn'] : ''); ?>"/>
            <br><br>
            <label>Achternaam:</label><br>
            <input type="text" name="sn" maxlength="128" value="<?= htmlentities(isset($data['prev_values']['sn']) ? $data['prev_values']['sn'] : ''); ?>"/>
            <br><br>
            <label>Adres:</label><br>
            <input type="text" name="adres" maxlength="256" value="<?= htmlentities(isset($data['prev_values']['adres']) ? $data['prev_values']['adres'] : ''); ?>"/>
            <br><br>
            <label>Geboortedatum:</plabel><br>
            <input type="date" name="geboortedatum" maxlength="10" value="<?=  htmlentities(isset($data['prev_values']['geboortedatum']) ? $data['prev_values']['geboortedatum'] : ''); ?>"/>
            <br><br>
            <label>Geslacht:</label><br>
            <input type="text" name="geslacht" maxlength="32" value="<?= htmlentities(isset($data['prev_values']['geslacht']) ? $data['prev_values']['geslacht'] : ''); ?>"/>
            <br><br>
            <label>Telefoonnummer:</label><br>
            <input type="text" name="telefoonnummer" maxlength="32" value="<?= htmlentities(isset($data['prev_values']['telefoonnummer']) ? $data['prev_values']['telefoonnummer'] : ''); ?>"/>
            <br><br>
            <label>BSN:</label><br>
            <input type="text" name="uid" maxlength="9" value="<?=  htmlentities(isset($data['prev_values']['uid']) ? $data['prev_values']['uid'] : ''); ?>"/>
            <br><br>
            <label>Wachtwoord:</label><br>
            <input type="password" name="passwd" />
            <br><br>
            <button type="submit" name="create_user" value="create_user">
                Submit
            </button>
            <br><br>
            <p style="color: #FC240F">
                <?= htmlentities($data['err_msg']) ?>
            </p>
            <p style="color: #29e314">
                <?= htmlentities($data['succ_msg']) ?>
            </p>
        </form>
    </div>

</body>
<footer>
</footer>

</html>