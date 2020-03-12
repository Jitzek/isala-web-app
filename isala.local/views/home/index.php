<?php
$__ROOT__ = "/var/www/isala.local";
require_once($__ROOT__ . '/controllers/HomeController.php');
require_once($__ROOT__ . '/models/HomeModel.php');
require_once($__ROOT__ . '/database/connection.php');
require_once($__ROOT__ . '/ldap/connection.php');

$model = new HomeModel();
$controller = new HomeController($model);
$dbconn = $model->getDB()->getConnection();
$ldapconn = $model->getLDAP()->getConnection();

// Check connection
if ($dbconn->connect_error) {
	die("<p style=\"color: #FC240F\">Database Connection Failed</p>");
}

if ($ldapconn) {
	# Example Login
	$ldapbind = $model->getLDAP()->bind(NULL, NULL); //NULL, NULL = anonymous bind

	$ldap_dn_users = "ou=developers,dc=isala,dc=local"; // Location of the user in LDAP Directory
	$given_uid = "elzenknopje";
	$given_pass= "idebian";

	// Check if User Exists
	if (!$model->getLDAP()->uidExists($ldap_dn_users, $given_uid, "inetOrgPerson")) die("<p style=\"color: #FC240F\">UserID not Found</p>");
	
	// Get User's DN
	$ldap_user_dn = $model->getLDAP()->getDnByUid($ldap_dn_users, $given_uid);

	// Check if User is in Group
	$ldap_group_dn = "cn=developers,ou=developers,dc=isala,dc=local"; // Location of the group in LDAP Directory
	if (!$model->getLDAP()->userInGroup($ldap_group_dn, $ldap_user_dn, "groupOfNames", "member")) die("<p style=\"color: #FC240F\">User not Found in Group</p>");

	// Bind to LDAP with this user
	$ldapbind = $model->getLDAP()->bind($ldap_user_dn, $given_pass); //NULL, NULL = anonymous bind
	if (!$ldapbind) {
		die("<p style=\"color: #FC240F\">LDAP Bind Failed</p>");
	}

	$_SERVER["AUTHENTICATE_UID"] = $given_uid;
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