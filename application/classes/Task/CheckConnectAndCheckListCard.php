    <?php defined('SYSPATH') or die('No direct script access.');
/*
16.04.2023
Сверка списков карт в точке прохода и в базе данных.
при наличии связи из контроллера выбираются все карты, и затем происходит сравнение с таблицей cardidx. Выяляются расхождения 
* в массиве и формируется список на запись и удаление.
c:\xampp\php\php.exe c:\xampp\htdocs\city\modules\minion\minion --task=CheckConnectAndCheckListCard --id_door=469
*/
 
    class Task_CheckConnectAndCheckListCard extends Minion_Task {
		
		    protected $_options = array(
        // param name => default value
     
        'id_door'   => '2',
		);
	
        
        protected function _execute(array $params)
        {
         //начало работы
		$count_connect_err=0;
		$count_connect_ok=0;
		$testMode=false;// в режиме $testMode=true формируются файлы содержимого БД, контроллера, их разница, но команды на удаление - запись не выдаются. Т.о., в результат работы в режиме $testMode=true состояние контроллеров не меняется.
		
		if($testMode)
		{
			Log::instance()->add(log::INFO, 'Start CheckConnectAndCheckListCard id_door='.Arr::get($params, 'id_door', -1) . ' в режиме testMode = true'); 
			
		} else {
			Log::instance()->add(log::INFO, 'Start CheckConnectAndCheckListCard id_door='.Arr::get($params, 'id_door', -1) . ' в режиме testMode = false'); 
			
			
		}
		
		
		$id_door=Arr::get($params, 'id_door', -1);
		
		//выборка id_dev контроллера для организации связи с ним
		$checkDeviceNameList=$this->getDeviceNameList(Arr::get($params, 'id_door', 0));// массив имен id_dev, с которыми надо проверить состояние связи ВЫборка делается для указанного Траснпортнного сервераю
		
			
		if(count($checkDeviceNameList)>0)
		{
			// если такие есть, то в цикле организую проверку связи.
			foreach($checkDeviceNameList as $key=>$value)
			{
				//создаю экземпляр контроллера
				$dev= new Device(Arr::get($value, 'ID_DEV'));
				
				//проверка связи
				if($dev->checkConnect())
				{
					//если связь есть
					Log::instance()->add(log::INFO, __('46 Device_connect_OK '.Arr::get($value, 'ID_DEV').' '.iconv('windows-1251','UTF-8', Arr::get($value, 'NAME'))));
					//получаю список дочерних id_dev (т.е. дверей)
					$dev->getChild();
					
					//для каждой двери...
					foreach ($dev->child as $key)
					{
						// сверка списков
						
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
							//echo Debug::vars('34 keyFromDevice', $keyFromDevice); exit;
							//echo Debug::vars('35 keyFromDatabase', $keyFromDatabase); exit;
							
							$key_for_write=$diffArr=array_diff($keyFromDatabase, $keyFromDevice);
							$key_for_del=$diffArr=array_diff( $keyFromDevice, $keyFromDatabase);
							
							//echo Debug::vars('17 diff need to write '.count($key_for_write).' cards', $key_for_write	); //exit;
							//echo Debug::vars('27 diff need to delete'.count($key_for_del).' cards', 	$key_for_del); exit;
							
							//echo Debug::vars('88 diff need to write '.count($key_for_write).' cards'); //exit;
							//echo Debug::vars('89 diff need to delete '.count($key_for_del).' cards'); exit;
							
							
							
							//Log::instance()->add(Log::DEBUG, '84 diff need to write for device='.iconv('windows-1251','UTF-8', Arr::get($value, 'NAME')).' id_dev='.$id_door. Debug::vars($key_for_write));
							//Log::instance()->add(Log::DEBUG, '84 diff need to delete for device='.iconv('windows-1251','UTF-8', Arr::get($value, 'NAME')).' id_dev='.$id_door.  Debug::vars($key_for_del));
						
							Log::instance()->add(Log::DEBUG, '105 diff need to write for device="'.iconv('windows-1251','UTF-8', Arr::get($value, 'NAME')).'" id_dev='.$id_door.' '. count($key_for_write).' cards');
							Log::instance()->add(Log::DEBUG, '106 diff need to delete for device="'.iconv('windows-1251','UTF-8', Arr::get($value, 'NAME')).'" id_dev='.$id_door.' '.  count($key_for_del).' cards');
						

						if(!$testMode){
																
								foreach($key_for_del as $key=>$value)
								{
									
									$door->delKeyFromIdDev($value, $id_door, $id_door);
								}
								
								foreach($key_for_write as $key=>$value)
								{
									
									$door->writeKeyToDevice($value, $id_door);
								}
							}						
										
					}
					$count_connect_ok++;
					
				} else {
					 //если связи нет
					 Log::instance()->add(log::INFO, __('Device_connect_ERR '.Arr::get($value, 'ID_DEV').' '.iconv('windows-1251','UTF-8', Arr::get($value, 'NAME'))));
					 $count_connect_err++;
				 }
			}
			
			
		} else {
			//контроллеров с превышением попыток записи нет.
			Log::instance()->add(log::INFO, __('no_device_for_check_count'));
		}
		
		//завершение работы.
		Log::instance()->add(log::INFO, 'Stop CheckConnectAndCheckListCard id_ts='.Arr::get($params, 'id_door', -1).'. Restore connect '.$count_connect_ok.', connect error '.$count_connect_err); 
		
        }
		
		public function getDeviceNameList($id_door)// получить список точек контроллеров, в которых надо проверить списки
		{
			
			$result=array();
			
			$sql='select distinct d.id_dev, d.name from device d
				join device d2 on d2.id_ctrl=d.id_ctrl and d.id_reader is null
				where d2.id_dev='.$id_door;
			//	Log::instance()->add(log::INFO, $sql);
			try {
				$query = DB::query(Database::SELECT, $sql)
				->execute(Database::instance('fb'))
				->as_array();	
				$result= $query;
				
			} catch (Exception $e) {
							
				Log::instance()->add(log::INFO, $e->getMessage());
			}
			
			return $result;
		}
		
    }
	