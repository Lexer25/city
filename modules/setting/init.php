<?php defined('SYSPATH') OR die('No direct access allowed.');

// Определяем константы модуля
define('SETTING_VERSION', '1.0.0');

// Подключаем маршруты - используем префикс 'setting'
Route::set('setting', 'setting(/<action>(/<id>))')
    ->defaults(array(
        'controller' => 'setting',
        'action'     => 'index',
    ));

// Проверяем, включен ли модуль
if (Kohana::$config->load('setting.enable_module') === false) {
    // Модуль отключен
    return;
}