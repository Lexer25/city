<?php defined('SYSPATH') or die('No direct script access.');
 
return array(
    'dir_log' => 'C:\Program Files (x86)\Cardsoft\DuoSE\Access\Log',
    'dir_compare' => 'C:\xampp\htdocs\city',
    'stat_day_befor' => 2,
    'name_device_fro_test' => 'л251 к45 калитка в л254',
	'city_name' => 'ПАО "Калибр"', //'Балчуг Вьюпоинт',
	'ver'=>'1.3.6b',//исправлены C:\xampp\htdocs\city\application\classes\Model\People.php->getPeopleDoor теперь с учетом карты, удален раздел Кол-во карт с превышением, удалена кнопка "Прекратить запись".
	'ver'=>'1.3.6с',//очередь загрузки - имя контроллера - выводит список ошибок; IP адрес теперь в отдельной колонке;Исправлен вывод информации по контроллеру если не указан id сервера.
	'ver'=>'2.0.1',// Новая версия в расчете на ТС4. 
	'ver'=>'2.0.2',// Удален раздел выбора базы данных. 
	'timeUpdate'=>'2026-01-15',//дата обновления. параметр может отсутствовать.
	'developer'=>'www.artonit.ru',
		'main_windows'=>array(
				'windows1'=>true, // true окно №1  Информация по жильцам и картам
				'windows2'=>false, // true окно №2 Оборудование
				'windows3'=>false, // true окно №3 Очередь загрузок
				'windows4'=>false, // falseокно №4 События
				'windows5'=>false, // false окно №5 Изменения системы
				'windows6'=>false, // true окно №6 Статистика событий
				
				
		),
	'count_day_befor_end_time' =>30,
	'analit_ok'=>array(507, 509, 650, 651, 652, 653, 654, 655, 656, 480), //Список кодов аналитики, которые следует рассматривать как правильную работу системы 
	'analit_err'=>array(500, 501, 502, 503, 504, 505, 506, 508, 657, 658, 46, 481), // Список кодов аналитики, которые следует рассматривать как нарушение правильной работы системы.
	'analit_transit'=>array(5001, 5011, 5021, 5031, 5041, 5051, 5061), // 14.03.2020 Список кодов аналитики, которые следует рассматривать как переходные процессы: карта уже поставлена на удаление, но еще не удалена из контроллера.
	//30.01.2020 Определение условий доступа к пунктам верхнего меню. Значение false - без авторизации не показывать, значение true - показывать меню всегда
	'view_without_auth'=>array(
		'load'=>false,
		'load_order'=>false,
		'device_control'=>false,
		'events'=>true,
		'people'=>false,
		'door'=>false,
		'log'=>true,
		'check'=>false,
		),
	'curl_place'=>'C:\xampp\curl.exe -L',
	'baseFormatRfid'=>'0', //0 - HEX 8 byte 00124CD8, 1 -  001A 10 byte 262F8F001A
	
);