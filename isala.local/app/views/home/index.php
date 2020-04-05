<?php
?>

<!DOCTYPE html>
<html>

<head>
    <title>
        <?= htmlentities($data['title']) ?>
    </title>
    <link rel="stylesheet" href="/public/css/home.css">
</head>

<body>
    <div id="knopkes">
        <?php if ($_SESSION['role'] == 'patienten') : ?>
            <a href="/public/fileupload">
                <button id="knopke"><img src="/public/imgs/documents_white.png">Documenten</img></button>
            </a>
        <?php else : ?>
            <a href="/public/patientlist">
                <button id="knopke"><img src="/public/imgs/documents_white.png">Patiënten</img></button>
            </a>
        <?php endif; ?>
        <a>
            <button id="knopke"><img src="/public/imgs/chart_white.png">Voortgang</img></button>
        </a>
        <a>
            <button id="knopke"><img src="/public/imgs/calendar_white.png">Agenda</img></button>
        </a>
        <?php if ($data['auth'] === true) : ?>
            <a href="/public/linkuser">
                <button id="knopke"><img src="/public/imgs/user_white.png">Link gecontracteerden</img></button>
            </a>
        <?php endif; ?>
        <?php if ($_SESSION['role'] == 'dokters') : ?>
            <a href="/public/createuser">
                <button id="knopke"><img src="/public/imgs/user_white.png">Maak Patiënt aan</img></button>
            </a>
        <?php endif; ?>
    </div>
</body>
<footer>
</footer>

</html>