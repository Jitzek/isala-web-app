<?php

class Connection
{
    private $servername = "isala.local";
    private $username = "elzenknopje";
    private $password = "mydebian";
    private $database = "isalaDB";
    private $conn;

    public function __construct() {
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->database);
    }

    public function getConnection() {
        return $this->conn;
    }
}