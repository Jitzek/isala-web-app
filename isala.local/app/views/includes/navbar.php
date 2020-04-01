<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../public/css/navbar.css" />
</head>

<body>
    <section>
        <nav class="navbar navbar-dark bg-dark justify-content-between">
            <a class="navbar-brand" href="#">
                <button type="button" id="sidebarCollapse" class="btn-hamburger">
                    <i class="hamburger-menu icon-white"></i>
                </button>
                <img class="isala-logo" src="../public/imgs/isala-logo.png" />
            </a>
            <div class="nav-right-side">
                <div class="dropdown show">
                    <a class="dropdown-toggle btn-user" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?= htmlentities($data['name']); ?>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                        <a class="dropdown-item" href="#">Profiel</a>
                        <a class="dropdown-item" href="#">Instellingen</a>
                    </div>
                </div>
                <div>
                    <form method="post" action="/public/logout" class="form-logout">
                        <button class="btn btn-logout" type="submit" name="logout" value="logout">
                            <img class="logout-icon" src="../public/imgs/logout_white.png" />
                        </button>
                    </form>
                </div>
            </div>
        </nav>
    </section>
        <!-- Sidebar -->
    <div>
        <nav id="sidebar">
            <ul class="list-unstyled components sidebar-ul">
                <div style="margin-bottom: 50px;"></div>
                <li class="active">
                    <a class="sidebar-item" href="#"><img src="../public/imgs/home_white.png" width="50" height="50"><br>Home</a>
                </li>
                <li>
                    <a class="sidebar-item" href="#"><img src="../public/imgs/documents_white.png" width="50" height="50"><br>Documenten</a>
                </li>
                <li>
                    <a class="sidebar-item" href="#"><img src="../public/imgs/chart_white.png" width="50" height="50"><br>Voortgang</a>
                </li>
                <li>
                    <a class="sidebar-item" href="#"><img src="../public/imgs/calendar_white.png" width="50" height="50"><br>Agenda</a>
                </li>
            </ul>
        </nav>
    </div>
    <script src="../public/js/navbar.js" type="text/javascript"> </script>
</body>

</html>