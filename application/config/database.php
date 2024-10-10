<?php defined('SYSPATH') OR die('No direct access allowed.');

return array
(
	//'fb' => Arr::get(Arr::get(Arr::get(Kohana::$config->load('skud'),'skud_list'), Session::instance()->get('skud_number')), 'fb_connection'),
	// в Калибре выбор базы данных фиксирован.
	'fb2' => array(
		'type'			=> 'pdo',
		'connection'	=> array(
			'dsn'		=> 'firebird:dbname=localhost:C:\\Program Files (x86)\\Cardsoft\\DuoSE\\Access\\SHIELDPRO_REST.GDB',
			'username'	=> 'SYSDBA',
			'password'	=> 'temp',
			'charset'   => 'windows-1251',
			)
		),
		'fb' => array(
				'type'			=> 'pdo',
				'connection'	=> array(
					//'dsn'		=> 'odbc:SDUO',
					'dsn'		=> 'odbc:VNII_local',
					'charset'   => 'windows-1251',
					)
				),
	
);

