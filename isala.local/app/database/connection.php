<?php

require_once('../app/database/queries.php');

class DBConnection
{
    private $conn;

    private $queries;

    public function __construct() {
        $username = apache_getenv("DB_USER");
        $password = apache_getenv("DB_PASSWD");
        $servername = apache_getenv("DB_SERVER");
        $database = apache_getenv("DB_NAME");
        $this->conn = new mysqli($servername, $username, $password, $database);
        $this->queries = new DBQueries($this->getConnection());
    }

    public function getConnection() {
        return $this->conn;
    }

    /**
     * $query: String - name of method
     * $args:  Array  - parmeters
     */
    public function query($query, $args = []) {
        return call_user_func_array([$this->queries, $query], $args);
    }
}