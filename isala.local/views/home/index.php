<?php
$__ROOT__ = "/var/www/isala.local";
require_once($__ROOT__ . '/controllers/HomeController.php');
require_once($__ROOT__ . '/models/HomeModel.php');
require_once($__ROOT__ . '/database/connection.php');

$model = new HomeModel();
$controller = new HomeController($model);
$conn = (new Connection())->getConnection();

// Check connection
if ($conn->connect_error) {
	die("<p style=\"color: #FC240F\">Database Connection Failed</p>");
}
?>

<!DOCTYPE html>
<html>

<head>
	<title><?php echo $model->getTitle(); ?></title>
</head>

<body>
	<h1><?php echo "Home Page" ?></h1>
</body>

</html>