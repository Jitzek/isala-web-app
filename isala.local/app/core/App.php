<?php

/**
 * Main App
 * Creates Requested Controllers
 */
class App 
{
    // Default Routing args
    protected $controller = 'home';
    protected $method = 'index';
    protected $params = [];

    function __construct() 
    {
        //TODO: make controller factory

        $url = $this->parseUrl();

        // Check if requested controller exists
        if (file_exists('../app/controllers/' . $url[0] . '.php')) {
            $this->controller = $url[0];
            unset($url[0]);
        }

        // Define controller
        require_once '../app/controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        // Check if requested method exists
        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            }
        }

        // Check if parameters are given, else keep array empty
        // FIXME: How to handle too many arguments?
        $this->params = $url ? array_values($url) : [];

        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    /**
     * Gets arguments from url e.g. localhost/argument1/argument2
     * Return Array with url arguments
     */
    protected function parseUrl() 
    {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
    }
}