<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="/public/css/navbar.css" />
</head>

<body>
    <section>
        <nav class="navbar navbar-dark bg-dark justify-content-between">
            <a class="navbar-brand">
                <img class="isala-logo" src="../public/imgs/isala-logo.png" />
            </a>
            <div class="form-inline">
                <div class="dropdown show">
                    <a class="dropdown-toggle btn-user" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?= htmlentities($data['name']); ?>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                        <a class="dropdown-item" href="#">Profiel</a>
                        <a class="dropdown-item" href="#">Instellingen</a>
                    </div>
                </div>
                <form method="post" action="/public/logout">
                    <button class="btn btn-logout" type="submit" name="logout" value="logout">
                        <img class="logout-icon" src="../public/imgs/logout_white.png" />
                    </button>
                </form>
            </div>
        </nav>
    </section>
</body>

</html>