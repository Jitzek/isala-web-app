<?php


class Logger
{
    protected static $log_file;

    protected static $file;

    protected static $options = [
        'dateFormat' => 'd-M-Y',
        'logFormat' => 'H:i:s d-M-Y'
    ];

    private static $instance;
}