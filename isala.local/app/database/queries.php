<?php

class DBQueries
{
    private $conn;
    public function __construct($connection)
    {
        $this->conn = $connection;
    }

    private $result;

    public function userIsLocked($uid, $table)
    {
        // Make sure $table can not be edited by user
        $table = $this->conn->real_escape_string($table);
        $query = $this->conn->prepare("SELECT isLocked FROM {$table} WHERE BSN = ?");
        $query->bind_param("s", $uid);
        $query->execute();
        $query->bind_result($this->result);
        $query->fetch();
        $query->close;
        return $this->result;
    }
}
