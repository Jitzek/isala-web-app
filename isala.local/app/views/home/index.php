<?php
?>

<!DOCTYPE html>
<html>

<head>
    <title>
        <?= htmlentities($data['title']) ?>
    </title>
    <?php include('../public/includes/head.php') ?>
</head>

<body>
    <?php include('../public/includes/navbar.php') ?>
    <div class="container">
        <h1>Home Page</h1>
        <p>Welcome <?= htmlentities($data['name']) ?></p>
        <p>Group <?= htmlentities($data['group']) ?></p>
    </div>
</body>
<footer>
    <?php include_once('../public/includes/footer.php') ?>
</footer>

</html>