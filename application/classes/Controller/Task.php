<?
class Controller_Task extends Controller {


	public function action_index()// сказать, что тут что-то есть.
	{
		
		
		 //$sql='select * from card c';
		/*  $sql='select distinct c.id_card from cardidx c';
		
		$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
		foreach ($query as $key=>$value)
		{
			if(strlen(Arr::get($value, 'ID_CARD')) <>8) echo Debug::vars($value);
			if( preg_match('/^[A-F0-9]+$/',Arr::get($value, 'ID_CARD')) ==1 ){
			//echo(Arr::get($value, 'ID_CARD'). " OK<br>");
				}else{
			echo(Arr::get($value, 'ID_CARD'). " Err<br>");
}

		}
		
		exit;  */
		$id_door=468;
		$door=Model::Factory('Device2');
		
		$keyFromDevice=array();
		$keyFromDatabase=array();
		
		//выбираю массив ключей из контроллера
	 	if($door->readkey_onceInArray($id_door) == 0)
		{
			$keyFromDevice = $door->result;
			
			//выбираю массив ключей из базы данных	
			if($door->getCardListIDX($id_door) == 0)
				{
					$keyFromDatabase = $door->result;
					
					
				} else {
					echo Debug::vars('27', $door->errors);
				}
				
		
		} else {
			echo Debug::vars('17', $door->errors);
		} 
		//echo Debug::vars('34 keyFromDevice', $keyFromDevice);
		//echo Debug::vars('35 keyFromDatabase', $keyFromDatabase);
		
		$key_for_write=$diffArr=array_diff($keyFromDatabase, $keyFromDevice);
		$key_for_del=$diffArr=array_diff( $keyFromDevice, $keyFromDatabase);
		
		//echo Debug::vars('17 diff need to write', $key_for_write	); //exit;
		//echo Debug::vars('27 diff need to delete', 	$key_for_del); //exit;
		foreach($key_for_write as $key=>$value)
		{
			
			$door->writeKeyToDevice($value, $id_door);
		}
		
		foreach($key_for_del as $key=>$value)
		{
			
			$door->delKeyFromIdDev($value, $id_door, $id_door);
		}
		
		//echo Debug::vars('18 action index'); exit;
		
	}
	
	
	public function action_delete_stat_data()// очистка таблицы st_data
	{
		Model::Factory('Stat')->delete_stat_data();
		
	}
	
	public function action_fixKeyOnDBCount()// Запись в базу данных в таблицу st_data текущего значения количества карт по базе данных для каждой точки прохода.
	{
		Model::Factory('Stat')->fixKeyOnDBCount();
	}
	
	public function action_fixOverTimeKeyOnDBCount()// Запись в базу данных в таблицу st_data просроченных карт.
	{
		Model::Factory('Stat')->fixOverTimeKeyOnDBCount();
	}
	
	
	
	
	public function action_readkey_once()// вычитка карт из контроллера и запись их в файл. 19.04.2020
	{
		//C:\xampp\curl.exe -L http://192.168.230.4:8080/city/task/readkey_once/226
		$id_dev = $this->request->param('id');
		//echo Debug::vars('7', $id_dev); exit;
		Model::Factory('Device')->readkey_once($id_dev);
		
	}

	
	public function action_stat_device()// чтение состояний контроллеров для указанного ТС.
	{
		$id_server = $this->request->param('id');
		//echo Debug::vars('7', $id_server); exit;
		Model::Factory('Device')->checkStatus($id_server);
		
	}

	public function action_getStatusIdDev()// поиск и удаление лишних карт. После выполнения команды выполняется запись в таблицу статистики
	{
		$id_dev = $this->request->param('id');
		//echo Debug::vars('7', $id_server); exit;
		Model::Factory('Check')->getStatusIdDev($id_dev);
		Model::Factory('Device')->insertStatusIdDev($id_dev);// сразу после удаления сделать вычитку карты из контроллера, кол-ва карт по БД в момент опроса  и зафиксировать результат в базе данныхю
		return;
	}
	
	public function action_insertStatusIdDev() //сбор и запись в базу данных статистики по указанному контроллерую
	{
		//C:\xampp\curl.exe -L http://192.168.222.1:8080/city/task/insertStatusIdDev/635
		$id_dev = $this->request->param('id');
		Model::Factory('Device')->insertStatusIdDev($id_dev);
		
		return;
	}
	
	public function action_detectTestModeAllDevice()// 29.03.2020 попытка выявить тестовый режим для контроллера указанного транспортного сервера.
	{
		$id_server = $this->request->param('id');
		$doorList=Model::Factory('Device')->getDoorList($id_server);// выборка id точек прохода
		//echo Debug::vars('33',$id_server,  $doorList); exit;
		$device_mode='mode_n/a';
		foreach ($doorList as $key=>$value){
				//echo Debug::vars('36',$value); exit;
				$device_version=Model::Factory('Stat')->getVersion($value);	
				//echo Debug::vars('38',$device_version, $value); exit;
			if($device_version == 'ademant')$device_mode=Model::Factory('Stat')->getAnalitic_for_Test_mode_ademant($value);
			if($device_version == 'artonit') $device_mode=Model::Factory('Stat')->getAnalitic_for_Test_mode_artonit($value);
			Model::Factory('Device')->stat_insert(0, $value, 8, 9, $device_mode);
			
		}
		
	}
	
	
	public function action_detectTestMode($id_dev)//попытка выявить тестовый режим указанного контроллера путем анализа событий
	{
		//C:\xampp\curl.exe -L http://192.168.222.1:8080/city/task/detectTestMode/2
		//C:\xampp\curl.exe -L http://192.168.222.1:8080/city/task/detectTestMode/658
		// получить версию контроллера. Данные взять из таблицы ST_DATA
		$id_dev = $this->request->param('id');
		$device_version=Model::Factory('Stat')->getVersion($id_dev);
		$device_mode='no_info';
		if($device_version == 'no_data')$device_mode='no_data';
		//провести анализ журнала событий с целью выявить тестовый режим
		if($device_version == 'ademant')$device_mode=Model::Factory('Stat')->getAnalitic_for_Test_mode_ademant($id_dev);
		if($device_version == 'artonit') $device_mode=Model::Factory('Stat')->getAnalitic_for_Test_mode_artonit($id_dev);
		Kohana::$log->add(Log::INFO, 'detectTestMode id='.$id_dev.', device_mode='.$device_mode);
		//echo Debug::vars('32', $device_version, $device_mode); exit; 		
		// для адеманта убедиться, что все события с картой 65 - это может быть только в режиме Тест.
		// для Артонит убедиться, что все событий 145 - проход в режиме тест.
		// результат занести в таблицу ST_DATA
		Model::Factory('Device')->stat_insert(0, $id_dev, 8, 9, $device_mode);
	}

}
