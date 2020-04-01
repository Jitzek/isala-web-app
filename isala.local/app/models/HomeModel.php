<?php

class HomeModel
{
    private $title;
    private $db;
    public function __construct()
    {
        $this->title = 'Home';
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