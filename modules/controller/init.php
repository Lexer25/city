<?php defined('SYSPATH') or die('No direct script access.');

// Регистрация путей модуля
if ( ! isset(Kohana::$_modules['controller']))
{
    Kohana::$_modules['controller'] = array(
        'path' => __DIR__,
        'name' => 'controller'
    );
}