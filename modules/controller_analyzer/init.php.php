<?php
// modules/controller_analyzer/init.php
defined('SYSPATH') or die('No direct script access.');

class ControllerAnalyzer_Core {
    
    public static function init()
    {
        // Автозагрузка классов модуля
        Kohana::modules(array_merge(Kohana::modules(), array(
            'controller_analyzer' => MODPATH.'controller_analyzer'
        )));
    }
}