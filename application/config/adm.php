<?php defined('SYSPATH') or die('No direct script access.');

return array(
    // Базовые пункты, которые всегда есть
    'config' => array(
        'title' => 'Артонит Сити Конфигуратор',
        'url' => '/',
        'icon' => 'fa-home',
        'order' => 0,
		'disabled' => false, 
		

    ),
	'baselist' => array(
        'title' => 'Справочники',
        'url' => 'dashboard/log',
        'icon' => 'fa-home',
        'order' => 50,
		'disabled' => false, 
		 'children' => array(
            'tasks' => array(
                'title' => 'Тип ТС',
                'url' => 'bas'
            ),
            'setting' => array(
                'title' => 'Тип идентификаторов',
                'url' => 'bas'
            ),
			'config' => array(
                'title' => 'Типы устройств',
                'url' => 'bas/search'
            )
			
        )
		
    ),
	'devgroup' => array(
        'title' => 'Группы устройств',
        'url' => '/',
        'icon' => 'fa-home',
        'order' => 0,
		'disabled' => true, 
		

    ),
	
	
);