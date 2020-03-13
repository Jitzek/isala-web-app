<?php

require_once('../app/ldap/queries.php');

class LDAPConnection
{
    private $conn;
    private $hostname = "isala.local"; // "localhost" also accepted

    private $queries;

    public function __construct()
    {
        $this->conn = ldap_connect($this->hostname);
        $this->queries = new LDAPQueries($this->getConnection());
    }

    public function getConnection()
    {
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