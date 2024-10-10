    <?php defined('SYSPATH') or die('No direct script access.');
/*
1.03.2023
Проверка очереди идентификаторов на загрузку
при наличии связи число попыток ставится равным 0, после чего идентификаторы автоматически загружаются в контроллеры.

c:\xampp\php\php.exe c:\xampp\htdocs\city\modules\minion\minion --task=CheckConnect --id_ts=2
*/
 
    class Task_CheckConnect extends Minion_Task {
		
		    protected $_options = array(
        // param name => default value
     
        'id_ts'   => '2',
		);
	
        
        protected function _execute(array $params)
        {
         //начало работы
		Log::instance()->add(log::INFO, 'Start checkConnect id_ts='.Arr::get($params, 'id_ts', -1)); 
		$count_connect_err=0;
		$count_connect_ok=0;
		
		//выборка id_dev контроллеров
		$checkDeviceNameList=$this->getDeviceNameList(Arr::get($params, 'id_ts', 0));// массив имен id_dev, с которыми надо проверить состояние связи ВЫборка делается для указанного Траснпортнного сервераю
		
		
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
					Log::instance()->add(log::INFO, __('Device_connect_OK '.Arr::get($value, 'ID_DEV').' '.iconv('windows-1251','UTF-8', Arr::get($value, 'NAME'))));
					//получаю список дочерних id_dev (т.е. дверей)
					$dev->getChild();
					
					//для каждой двери...
					foreach ($dev->child as $key)
					{
						//обновление счетчики попыток
						$sql='update cardindev cd
								set cd.attempts=0
								where cd.attempts<220
								and cd.id_dev ='.$key;
						try {
							$query = DB::query(Database::UPDATE, $sql)
							->execute(Database::instance('fb'));
							Log::instance()->add(log::INFO, $sql);
							
							} catch (Exception $e) {
							//если ошибка - фиксация в лог-файле.				
								Log::instance()->add(log::INFO, $e->getMessage());
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
			Log::instance()->add(log::INFO, __('no_device_for_load_card'));
		}
		
		//завершение работы.
		Log::instance()->add(log::INFO, 'Stop checkConnect id_ts='.Arr::get($params, 'id_ts', -1).'. Restore connect '.$count_connect_ok.', connect error '.$count_connect_err); 
		
        }
		
		public function getDeviceNameList($id_ts)// получить список точек контроллеров, в которые имеется очередь.
		{
			
			$result=array();
			// выбираю карты, для которых количество попыток меньше 100. 100 - это пороговое число. Все, что выше 100 обрабатывать не надо.
			$sql='select distinct d.id_dev, d.name from device d
			join device d2 on d2.id_ctrl=d.id_ctrl and d2.id_reader in (0,1)
			join cardindev cd on cd.id_dev=d2.id_dev
			join server s on d.id_server=s.id_server
			where cd.attempts>0
			and cd.attempts<100 
			and d2."ACTIVE">0
			and d."ACTIVE">0
			and d.id_reader is null
			and s.id_server='.$id_ts;
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