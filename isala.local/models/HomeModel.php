<?php
$__ROOT__ = "/var/www/isala.local";
require_once($__ROOT__ . '/database/connection.php');
require_once($__ROOT__ . '/ldap/connection.php');
class HomeModel
{
    private $title;
    private $DB;
    private $LDAP;

    public function __construct()
    {
        $this->title = "Home";
        $this->DB = new DBConnection();
        $this->LDAP = new LDAPConnection();
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDB() {
        return $this->DB;
    }

    public function getLDAP() {
        return $this->LDAP;
    }
}
