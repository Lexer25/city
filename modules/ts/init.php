<?php
// MODPATH/about/init.php
defined('TS_VERSION') OR define('TS_VERSION', '2.0.0');

Route::set('default', '(<controller>(/<action>(/<id>)))')
	->defaults(array(
		'controller' => 'dashboard',
		'action'     => 'index',
	));
	
	
Kohana::$config->load('adm')
    ->set('ts', array(
        'title' => 'ТС',
        'url' => '/ts',
        'icon' => 'fa-cog',
        'order' => 200,
		'disabled' => false, 
        'children' => array(
            'tasks' => array(
                'title' => 'Типы ТС',
                'url' => '/ts/types'
            ),
            'setting' => array(
                'title' => 'Транспортные сервера',
                'url' => 'ts/servers'
            ),
			'config' => array(
                'title' => 'Настройка ТС',
                'url' => 'ts/links',
            )
			
        )
    ));
	
	// Основной маршрут
Route::set('ts', 'ts(/<action>(/<id>))')
    ->defaults(array(
        'controller' => 'ts',
        'action'     => 'index',
    ));

Kohana::$config->load('adm')
    ->set('ts', array(
        'title' => 'ТС',
        'url' => '/ts',
        'icon' => 'fa-truck',
        'order' => 200,
        'disabled' => false, 
        'children' => array(
            'types' => array(
                'title' => 'Типы ТС',
                'url' => 'ts/types'
            ),
            'servers' => array(
                'title' => 'Сервера ТС',
                'url' => 'ts/servers'
            ),
            'links' => array(
                'title' => 'Привязка ТС',
                'url' => 'ts/links'
            )
        )
    ));