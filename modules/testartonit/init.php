<?php defined('SYSPATH') OR die('No direct access allowed.');

defined('TESTARTONIT_VERSION') OR define('TESTARTONIT_VERSION', '1.0.1');

Kohana::$config->load('adm')
    ->set('testartonit', array(
        'title' => 'TESTARTONIT',
        'url' => 'TestArtonit',
        'icon' => 'fa-sitemap',
        'order' => 50,
    ));

// Регистрируем маршруты для модуля
Route::set('testartonit', 'testartonit(/<action>)')
    ->defaults(array(
        'controller' => 'TestArtonit',
        'action'     => 'index',
    ));

// Подключаем необходимые классы
// require_once MODPATH . 'testartonit/classes/TS2client.php';
// require_once MODPATH . 'testartonit/classes/phpArtonitTS2.php';
// require_once MODPATH . 'testartonit/classes/phpArtonitUDP.php';

// Kohana::modules(array(
    // 'testartonit' => MODPATH . 'testartonit',
// ));