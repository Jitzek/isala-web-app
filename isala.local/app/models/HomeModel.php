<?php

class HomeModel
{
    private $title;
    public function __construct()
    {
        $this->title = 'Home';
    }

    public function getTitle()
    {
        return $this->title;
    }
}