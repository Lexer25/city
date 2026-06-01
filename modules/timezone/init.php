<?php defined('SYSPATH') or die('No direct script access.');

Kohana::$config->load('menu')
    ->set('timezone', array(
        'title' => 'Временные зоны',
        'url' => 'timezone',
        'icon' => 'fa-clock-o',
        'order' => 105,
    ));