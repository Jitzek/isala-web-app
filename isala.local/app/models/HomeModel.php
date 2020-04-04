<?php

class HomeModel
{
    private $title;
    private $ldap;

    public function __construct()
    {
        $this->title = 'Home';
    }

    public function getTitle()
    {
        return $this->title;
    }
}