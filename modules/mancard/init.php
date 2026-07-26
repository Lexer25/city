<?php defined('SYSPATH') or die('No direct script access.');

defined('MANCARD_VERSION') OR define('MANCARD_VERSION', '1.0.0');

Kohana::$config->load('menu')
    ->set('mancard', array(
        'title' => 'Организации и сотрудники',
        'url' => 'mancard/index',
        'icon' => 'fa-sitemap',
        'order' => 25,
        'children' => array(
            array(
                'title' => 'Управление',
                'url' => 'mancard/index',
                'icon' => 'fa-home',
            ),
            array(
                'title' => 'Массовое перемещение',
                'url' => 'mancard/move',
                'icon' => 'fa-exchange',
            ),
        )
    ));
	
	
	
	Route::set('mancard_entity_access', 'mancard/get_entity_access/<type>/<id>')
    ->defaults(array(
        'controller' => 'mancard',
        'action'     => 'get_entity_access',
    ));