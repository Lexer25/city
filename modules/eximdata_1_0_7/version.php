<?php defined('SYSPATH') or die('No direct script access.');

return array(
    'name' => 'My Custom Module',
    'version' => '1.0.7',
    'version_code' => 10203,
    'author' => 'Artonit',
    'description' => 'Экспорт импорт контактов и организаций',
    'website' => 'http://artonit.ru',
    'license' => 'MIT',
    'dependencies' => array(
        'database' => '>=3.3.0'
    ),
	'abbr'=>'[+]
	Доработки при добавлении Tree:
	Контроль повторного запуска импорта из одного и того же файла. Повторная вставка не допускается.
	Вывод результатов вставки на экран.',
	
);