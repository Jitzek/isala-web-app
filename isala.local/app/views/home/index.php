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
    <div class="container">
        <h1>Home Page</h1>
        <p>Welcome <?= htmlentities($data['name']) ?></p>
        <p>Group <?= htmlentities($data['group']) ?></p>
    </div>
</body>
<footer>
</footer>

</html>