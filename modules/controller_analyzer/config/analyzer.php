<?php
// modules/controller_analyzer/config/analyzer.php
return array(
    'cache' => array(
        'enabled'    => FALSE, // По умолчанию выключим кэш
        'lifetime'   => 3600,
        'driver'     => 'file', // или 'apc', 'memcache', 'sqlite' в зависимости от доступности
        'prefix'     => 'controller_analyzer_'
    ),
    'scan' => array(
        'paths' => array(
            APPPATH.'classes/Controller',
            MODPATH
        ),
        'exclude' => array(
            'Controller_Template',
            'Controller_REST',
            'Controller_Analyzer',
            'Controller_Camap',
            'Controller_CA'
        ),
        'extensions' => array('php')
    ),
    'output' => array(
        'default_format' => 'html',
        'supported_formats' => array('html', 'json', 'xml')
    )
);