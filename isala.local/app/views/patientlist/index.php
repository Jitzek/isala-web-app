<?php
?>

<!DOCTYPE html>
<html>

<head>
    <title>
        <?= htmlentities($data['title']) ?>
    </title>
    <link rel="stylesheet" href="/public/css/patientlist">
</head>

<body>
    <div class="container">
    <table class="table">
        <thead>
            <tr>
                <th>Naam</th>
                <th>Leeftijd</th>
                <th>Geboorte Datum</th>
                <th>Geslacht</th>
                <th>Telefoon nummer</th>
                <th>Adres</th>
                <th>Handige verwijziging</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($data['patientlist'] as $patient): ?>
                <tr>
                    <td><?= htmlentities($patient->getFullName()); ?></td>
                    <td><?= htmlentities($patient->getLeeftijd()); ?></td>
                    <td><?= htmlentities($patient->getGeboorteDatum()); ?></td>
                    <td><?= htmlentities($patient->getGeslacht()); ?></td>
                    <td><?= htmlentities($patient->getTelefoonnummer()); ?></td>
                    <td><?= htmlentities($patient->getAdres()); ?></td>
                    <td><a href="/public/profile/<?= htmlentities($patient->getUid());?>"><img src="/public/imgs/contact_info.png"/></a> <a href="/public/fileupload/<?= htmlentities($patient->getUid());?>"><img src="/public/imgs/document.png"/></a> </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</body>
<footer>
</footer>

</html>