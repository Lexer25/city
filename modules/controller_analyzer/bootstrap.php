<?php
// modules/controller_analyzer/bootstrap.php
defined('SYSPATH') or die('No direct script access.');

// Регистрация роутов для модуля
Route::set('controller_analyzer', 'controller-analyzer(/<action>(/<controller>))')
    ->defaults(array(
        'directory'  => 'controller_analyzer',
        'controller' => 'analyzer',
        'action'     => 'index',
    ));

Route::set('controller_map', 'controller-map(/<format>)')
    ->defaults(array(
        'directory'  => 'controller_analyzer',
        'controller' => 'map',
        'action'     => 'index',
        'format'     => 'html'
    ));