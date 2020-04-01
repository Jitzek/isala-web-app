<?php

require_once('../app/database/queries.php');

class DBConnection
{
    private $servername = "isala.local";
    private $username = "server";
    private $password = "mydebian";
    private $database = "isalaDB";
    private $conn;

    private $queries;


    public function __construct() {
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->database);
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