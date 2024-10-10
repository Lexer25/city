<?php
//echo Debug::vars('2', $serverList);//exit;
/* echo Form::open('dashboard/getIpFromTs2');
	echo Form::button('getIpFromTs2','Скопировать IP адреса из ТС2 в БД СКУД.');

echo Form::close(); */
if(!is_null($serverList))
{
	//echo Debug::vars('4', $serverList);
	/* $curl= Kohana::$config->load('artonitcity_config')->curl_place;//'C:\xampp\curl.exe -L';
	echo __('serverList').'<br>';


	echo '<br>';
	echo 'rem сбор статистическихх данных по контроллерам (версия, состояние связи, количество карт по каналу 0, количество карт по каналау 1. Данные записываются в таблицу ST_DATA.'.'<br>';
	echo HTML::anchor('/task/delete_stat_data', $curl.' http://'.Arr::get($_SERVER, 'HTTP_HOST', '127.0.0.1').'/city/task/delete_stat_data').'<br>';
	foreach($serverList as $key=>$value)
	{
		echo HTML::anchor(
				'task/stat_device/'.Arr::get($value, 'ID_SERVER'),
				$curl.' http://'.Arr::get($_SERVER, 'HTTP_HOST', '127.0.0.1').'/city/task/stat_device/'.Arr::get($value, 'ID_SERVER'))
				.'<br>';
	}
	
	echo '<br>';
	echo 'rem Выявление режима ТЕСТ.'.'<br>';
	foreach($serverList as $key=>$value)
	{
		echo HTML::anchor('task/stat_device/1', $curl.' http://'.Arr::get($_SERVER, 'HTTP_HOST', '127.0.0.1').'/city/task/detectTestModeAllDevice/'.Arr::get($value, 'ID_SERVER')).'<br>';
	}
	
	echo '<br>';
	echo 'rem Запись количества карт по точкам прохода в момент опроса. Данные берутся из базы данных и записываются в таблицу ST_DATA.'.'<br>';
	echo HTML::anchor('task/stat_device/1', $curl.' http://'.Arr::get($_SERVER, 'HTTP_HOST', '127.0.0.1').'/city/task/fixKeyOnDBCount').'<br>';

	echo '<br>';
	echo 'rem Вычитка карт из указанной точки прохода. В файл записываются номера карт из базы данных и номера карты, вычитанные из контроллера. Вместо '.__('ID_DEV').' необходимо указать ID точки прохода (двери).<br>';
	echo HTML::anchor(
		'task/stat_device/1',
		$curl.' http://'.Arr::get($_SERVER, 'HTTP_HOST', '127.0.0.1').'/city/task/readkey_once/['.__('ID_DEV').']'
		).'<br>';
		
	echo '<br>';
	echo 'rem Запуск задачи minion.'.'<br>';
	echo HTML::anchor('task/stat_device/1', $curl.' http://'.Arr::get($_SERVER, 'HTTP_HOST', '127.0.0.1').'/city/task/test').'<br>'; */
	
	
}

if(true){

	// $t1=microtime(true);
	// $ip_address='10.25.16.70';
	// $ip_address='10.25.16.76';// это  прошивка 2014
	 $ip_address='192.168.0.33';
	//echo Debug::vars('47',$ip_address);
	$deviceHard = new artonitHTTP($ip_address);
	$deviceHard->getDeviceInfo();
	$deviceHard->getScudMode();
	$deviceHard->disconnect();
	
	echo Debug::vars('61', $deviceHard); exit;
	// $deviceHard = new artonitHTTP($ip_address);
						
						
						// $deviceHard->getDeviceInfo();// заполняю данные экземпляра из полученных ответов
						
						
		// $deviceHard->disconnect();
		// echo Debug::vars('60',$deviceHard); exit;
					
					
					
	//$deviceHard->getDoorMode();
	//$deviceHard->getInputPortState();
	//$deviceHard->getSoftVersion();
	//$deviceHard->getDeviceInfo();
	//echo Debug::vars('70', $deviceHard); exit;
	//echo Debug::vars('50 time execute', (microtime(true) - $t1));
	//exit;
							
	// $t1=microtime(true);

	// $artonit=new phpArtonitUDP($ip_address, 8192);
	// $artonit->command='GetVersion';
	// $artonit->execute();
	// echo Debug::vars('63', $artonit->command, $artonit->result,$artonit->edesc, $artonit->answer); 

	// $artonit->command='GetMAC';
	// $artonit->execute();
	// echo Debug::vars('67', $artonit->command,$artonit->result,$artonit->edesc, $artonit->answer);

	// $artonit->command='GetJmp';
	// $artonit->execute();
	// echo Debug::vars('71', $artonit->command,$artonit->result,$artonit->edesc, $artonit->answer);

 
	// $artonit->command='GetIO';
	// $artonit->execute();
	// echo Debug::vars('76', $artonit->command,$artonit->result, $artonit->edesc, $artonit->answer);

	// $artonit->command='GetAP0';
	// $artonit->execute();
	// echo Debug::vars('80', $artonit->command,$artonit->result, $artonit->edesc, $artonit->answer);

	// $artonit->command='GetKeyCount';
	// $artonit->execute();
	// echo Debug::vars('84', $artonit->command,$artonit->result, $artonit->edesc, $artonit->answer);



	//echo Debug::vars('86', $deviceHard);
	// echo Debug::vars('87 time execute', (microtime(true) - $t1));

		//echo Debug::vars('96', $key, $value);
	//	$artonit=new phpArtonitUDP($value, 8192);
		/* $artonit->command='GetVersion';
		$artonit->execute();
		//echo Debug::vars('84', $artonit->command,$artonit->result, $artonit->edesc, $artonit->answer);
		$ver=$artonit->answer;
		$artonit->command='GetAP0';
		$artonit->execute();
		$var0=$artonit->answer;
		$artonit->command='GetAP1';
		$artonit->execute();
		$var1=$artonit->answer;
		 */
		// $artonit->command='GetKeyCount';
		// $artonit->execute();
		// $keyCount=$artonit->answer;
		

		$dev=new phpArtonitTS2();
		$dev->connect();
		
		echo Debug::vars('126', $dev->sendcommand('getversion'));
		echo Debug::vars('126', $dev->sendcommand('getdevicetime'));
		$t1=microtime(true);
		$res=array();
		for($i=0; $i<32; $i++){
			$res[]=trim($dev->sendcommand('readkey door=0, cell='.$i));
		
		}
		$dev->close();
	//	echo Debug::vars('135',  (microtime(true)-$t1));exit;
		
		echo Debug::vars('141', Model::factory('dbskud')->checkRfidKeyFormat());
}

?>



