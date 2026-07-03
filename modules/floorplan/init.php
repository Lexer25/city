<?php defined('SYSPATH') OR die('No direct script access.');

defined('FLOORPLAN_VERSION') OR define('FLOORPLAN_VERSION', '1.0.0');

// Добавляем в админ-меню (если используется)
if (Kohana::$config->load('adm')) {
    Kohana::$config->load('adm')
        ->set('floorplan', array(
            'title' => 'Планы объекта',
            'url' => 'floorplan',
            'icon' => 'fa-map',
            'order' => 95,
        ));
}

// Основные маршруты - ВАЖНО: порядок имеет значение!
Route::set('floorplan_savePositions', 'floorplan/savePositions')
    ->defaults(array(
        'controller' => 'Floorplan',
        'action' => 'savePositions',
    ));

Route::set('floorplan_addPointAjax', 'floorplan/addPointAjax')
    ->defaults(array(
        'controller' => 'Floorplan',
        'action' => 'addPointAjax',
    ));

Route::set('floorplan_deletePointAjax', 'floorplan/deletePointAjax')
    ->defaults(array(
        'controller' => 'Floorplan',
        'action' => 'deletePointAjax',
    ));

Route::set('floorplan_view', 'floorplan/view/<id>', array('id' => '\d+'))
    ->defaults(array(
        'controller' => 'Floorplan',
        'action' => 'view',
    ));

Route::set('floorplan_edit', 'floorplan/edit/<id>', array('id' => '\d+'))
    ->defaults(array(
        'controller' => 'Floorplan',
        'action' => 'edit',
    ));

Route::set('floorplan_delete', 'floorplan/delete/<id>', array('id' => '\d+'))
    ->defaults(array(
        'controller' => 'Floorplan',
        'action' => 'delete',
    ));

Route::set('floorplan_add', 'floorplan/add')
    ->defaults(array(
        'controller' => 'Floorplan',
        'action' => 'add',
    ));

// Главный маршрут - ДОЛЖЕН БЫТЬ ПОСЛЕДНИМ!
Route::set('floorplan', 'floorplan(/<action>(/<id>))', array('id' => '\d+'))
    ->defaults(array(
        'controller' => 'Floorplan',
        'action' => 'index',
    ));