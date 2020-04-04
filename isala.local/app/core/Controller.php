<?php

class Controller
{
    protected function model($model, $data = NULL)
    {
        if (file_exists('../app/models/' . $model . '.php')) {
            require_once('../app/models/' . $model . '.php');
            return isset($data) ? new $model(...$data) : new $model;
        }
        return null;
    }

    protected function view($view, $data = [])
    {
        if (file_exists('../app/views/' . $view . '.php')) {
            require_once '../app/views/' . $view . '.php';
        }
    }
}
