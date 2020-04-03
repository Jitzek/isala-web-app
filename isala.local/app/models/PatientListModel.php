<?php

require_once('../app/database/connection.php');

class PatientListModel
{
    private $title;
    private $db;
    public function __construct()
    {
        $this->title = 'Patienten lijst';
        $this->db = new DBConnection();
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDB()
    {
        return $this->db;
    }
}