<?php
?>

<!DOCTYPE html>
<html>
    <head>
        <title>
            <?=$data['title']?>
        </title>
    </head>
    <body>
        <h1>Login Page</h1>
        <form method="post" action="/public/login">
            <input type="text" name="uid" />
            <input type="password" name="passwd" />
            <button type="submit">
                Submit
            </button>
        </form>
    </body>
</html>