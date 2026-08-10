<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Конфигурационный файл Artonit City
 * Сгенерировано: 2026-04-01 22:06:50
 */

return array(
    'dir_log' => 'C:\\Program Files (x86)\\Cardsoft\\DuoSE\\Access\\Log',
    'dir_compare' => 'C:\\xampp\\htdocs\\city',
    'stat_day_befor' => 2,
    'city_name' => 'ЖК Хедлайнер',
 
    'developer' => 'www.artonit.ru',
    'main_windows' => array(
        'windows1' => true,
        'windows2' => true,
        'windows3' => true,
        'windows4' => true,
        'windows5' => true,
    ),
    'count_day_befor_end_time' => 30,
    'analit_ok' => array(
        507,
        509,
        650,
        651,
        652,
        653,
        654,
        655,
        656,
        480,
    ),
    'analit_err' => array(
        500,
        501,
        502,
        503,
        504,
        505,
        506,
        508,
        657,
        658,
        46,
        481,
    ),
    'analit_transit' => array(
        5001,
        5011,
        5021,
        5031,
        5041,
        5051,
        5061,
    ),
    'view_without_auth' => array(
        'load' => false,
        'load_order' => true,
        'device_control' => false,
        'events' => true,
        'people' => false,
        'door' => false,
        'log' => true,
    ),
    'curl_place' => 'C:\\xampp\\curl.exe -L',
    'baseFormatRfid' => 0,
);
