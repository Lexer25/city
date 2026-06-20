<?php defined('SYSPATH') or die('No direct script access.');

defined('HOLIDAY_VERSION') OR define('HOLIDAY_VERSION', '1.0.1');

	
Kohana::$config->load('menu')
    ->set('holiday', array(
        'title' => 'Праздники',
        'url' => 'holiday',
        'icon' => 'fa-calendar',
        'order' => 110,
    ));