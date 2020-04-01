<?php

class HomeModel
{
    private $title;
    private $db;
    public function __construct()
    {
        $this->title = 'Home';
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