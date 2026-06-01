<?php defined('SYSPATH') or die('No direct script access.');


Kohana::$config->load('menu')
    ->set('holiday', array(
        'title' => 'holiday',
        'url' => 'holiday',
        'icon' => 'fa-cog',
        'order' => 101,
       
    ));
	
	
Kohana::$config->load('menu')
    ->set('holiday', array(
        'title' => 'Праздники',
        'url' => 'holiday',
        'icon' => 'fa-calendar',
        'order' => 110,
    ));