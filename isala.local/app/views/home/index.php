<?php
?>

<!DOCTYPE html>
<html>
    <head>
        <title>
            <?=htmlentities($data['title'])?>
        </title>
    </head>
    <body>
        <h1>Home Page</h1>
        <p>Welcome <?=htmlentities($data['name'])?></p>
        <p>Group <?=htmlentities($data['group'])?></p>
    </body>
</html>