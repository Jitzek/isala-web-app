<?php
require_once('../app/database/connection.php');

class DownloadModel
{
    private $title;
    private $db;
    public function __construct()
    {
        $this->title = 'Download';
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
