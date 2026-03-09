<?php defined('SYSPATH') OR die('No direct access allowed.');

return array(
    // Включить/выключить модуль
    'enable_module' => true,
    
    // Требуемая роль для доступа
    'required_role' => 'admin',
    
    // Максимальное количество бэкапов
    'max_backups' => 50,
    
    // Папка для бэкапов (null = рядом с конфигом)
    'backup_dir' => null,
    
    // Формат даты для бэкапов
    'backup_date_format' => 'Y-m-d_H-i-s',
    
    // Настройки интерфейса
    'ui' => array(
        'items_per_page' => 50,
        'theme' => 'default'
    ),
    
    // Защищенные параметры (только для чтения)
    'protected_keys' => [
        // Нельзя изменять эти ключи
    ],
    
    // Кастомные типы полей
    'field_types' => [
		'baseFormatRfid' => 'radio',
        'dir_log' => 'path',
        'dir_compare' => 'path',
        'curl_place' => 'path',
        'ver' => 'version',
        'timeUpdate' => 'date',
        'lightVerDay' => 'integer',
        'count_day_befor_end_time' => 'integer',
        'stat_day_befor' => 'integer',
        'analit_ok' => 'array',
        'analit_err' => 'array',
        'analit_transit' => 'array',
        'view_without_auth' => 'boolean_array',
        'main_windows' => 'boolean_array',
    ],
);