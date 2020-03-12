<?php
$__ROOT__ = "/var/www/isala.local";
require_once($__ROOT__ . '/controllers/DashboardController.php');
require_once($__ROOT__ . '/models/DashboardModel.php');
require_once($__ROOT__ . '/database/connection.php');

$model = new DashboardModel();
$controller = new DashboardController($model);
$conn = (new DBConnection())->getConnection();

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
	<h1>Dashboard</h1>
	<?php
	echo "Welcome " . $model->getUser();
	?>
</body>

</html>