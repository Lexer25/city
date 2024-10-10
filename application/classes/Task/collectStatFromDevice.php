    <?php defined('SYSPATH') or die('No direct script access.');
	/**
 * 
 * 
 * It can accept the following options:
 *  - countDevice: set how many device should be executed.
 *  
 *
 * @package    Kohana
 * @category   Helpers
 * @author     Artonit
 * @copyright  (c) 2024 Artonit Team
 * @license    http://kohanaframework.org/license
 */
	
/*


c:\xampp\php\php.exe c:\xampp\htdocs\city\modules\minion\minion --task=collectStatFromDevice --countDevice=10 --id_ts=2

--countDevice=10 - будет обработане указанное количество первых записей в массиве. Этот параметр введн для удобства отладки. Если параметр не указан, то будет обработан весь массив.

--id_ts=2 указывание на id_server, с которым надо работатать. Если не указан, то будут выбраные все сервера типа 1 (Работающие с Артонит).

*/
 
    class Task_collectStatFromDevice extends Minion_Task {
		
		    protected $_options = array(
        // param name => default value
     
       // 'id_ts'   => 3,
        'countDevice'   => 500, //количество контроллеров для опроса. 
		);
	
        
        protected function _execute(array $params)
        {
		$t1=microtime(true);
		$list=array();

		if(is_null(Arr::get($params, 'id_ts'))){// если TS_ID не указан, то извлекаю номера из базы данных
			
			$sql='select stl.id_server from servertypelist stl
			where stl.id_type in (1)';// тут 1 - тип сервера для работы с контроллерами Артонит
			
			$query = DB::query(Database::SELECT, $sql)
							->execute(Database::instance('fb'))
							->as_array();
			foreach ($query as $key=>$value){
				
				$list_0=Model::factory('Device')->getdeviceList(Arr::get($value, 'ID_SERVER'));
				
				if(is_array($list_0)) $list = array_merge($list, $list_0);
				
				
			}
			
		} else {
		
			$list=Model::factory('Device')->getdeviceList(Arr::get($params, 'id_ts'));//получил список контроллеров СКУД
		}
		
		//	$list=array(905=>905);// отладка. Будет собрана информация для указанного id_dev
		$list=array_flip($list);//обмен местами ключей и значений
		Log::instance()->add(Log::DEBUG, '66 Collect stat for device START. Total '.count($list).' devices');	
		
		$count_=Arr::get($params, 'countDevice');
		
		
		
			foreach($list as $key=>$value){
				//echo Debug::vars('39', $count_);//exit;
				$t2=microtime(true);
				$count_ --;
				if(($count_) <0) {
					Log::instance()->add(Log::DEBUG, '27 Stop Collect for countDevice reason '.Arr::get($params, 'countDevice'));	
					break;
				}
				
				Log::instance()->add(Log::DEBUG, '43 count_ is  '.$count_);							
				if(is_numeric($key)){
								
					$device= new Device($key);
										
					$ip_address=$device->connectionString;
					
			
			if($ip_address){//если указана строка подключения (а пока под этим подразумевается IP адрес), то иду выбирать данные для этого контроллера.
				$tt1=microtime(true);
							$deviceHard = new artonitHTTP($ip_address);
							if($deviceHard->isOnline) {
							$deviceHard->getDeviceInfo();// заполняю данные экземпляра из полученных ответов
							$deviceHard->disconnect();
							
						
						//готовлю массив для записи в базу данных				
							$deviceState=array(
								'id'=>$key,
								'ip'=>$deviceHard->ip_address,
								'mac'=>$deviceHard->mac_address,
								'Onl'=>$deviceHard->isOnline,
								'isWp'=>$deviceHard->isWp,
								'isTst'=>$deviceHard->isTest,
								'dMode_0'=>Arr::get($deviceHard->doorMode, 0),
								'dMode_1'=>Arr::get($deviceHard->doorMode, 1),
								'InputPortState'=>$deviceHard->portStateInput,
								'sver'=>$deviceHard->softVersion,
								'kc'=>$deviceHard->keyCount,
								'scud'=>$deviceHard->scud,
								'te'=>number_format((microtime(true)-$tt1), 3, '.',''), //time execute
								'timef'=>time(), //время получения статистики
								'isNew'=>true, //признак, что данные собрали при работающем контроллере.
								'lastTimef'=>time(), //время последнего успешного подключения
								);
						
							
							
						} else {
						
									// если прибор не на связи, то надо переписать данные в таблице st_data
						$deviceInfo=new DeviceInfo($key, trim(Model::Factory('Stat')->getDeviceStatData($key)));// взять информацию из таблицы st_data для указанного контроллера
						$deviceState=array(
								'id'=>$key,
								'ip'=>$deviceInfo->ip,
								'mac'=>$deviceInfo->mac,
								'Onl'=>false,
								'isWp'=>$deviceHard->isWp,
								'isTst'=>$deviceHard->isTest,
								'dMode_0'=>Arr::get($deviceHard->doorMode, 0),
								'dMode_1'=>Arr::get($deviceHard->doorMode, 1),
								'InputPortState'=>$deviceHard->portStateInput,
								'sver'=>$deviceHard->softVersion,
								'kc'=>$deviceHard->keyCount,
								'scud'=>$deviceHard->scud,
								'te'=>number_format((microtime(true)-$tt1), 3, '.',''), //time execute
								'timef'=>time(), //время получения статистики
								'isNew'=>false, //признак, что данные собрали при работающем контроллере.
								'lastTimef'=>$deviceInfo->timeGetData, //беру из предыдущего значения
								);				
						}
						
							//записывают массив в базу данных в формате json
							$id_param=113;//название параметра - данные в виде json. Этот же параметр должен быть заявлен в БД в таблице ST_PARAM
							$id_agent=1;
							$order=445;//$ser;
							
							if(Model::factory('device')->stat_insert($order, $key, $id_agent, $id_param, json_encode($deviceState)) == 0) {
									Log::instance()->add(Log::DEBUG, 'Данные для id_dev='.$key.' ip='.$ip_address.'записаны успешно');
								
							} else {
								
								Log::instance()->add(Log::DEBUG, Log::instance()->add(Log::DEBUG, 'ERR Данные для id_dev='.$key.' ip='.$ip_address.'записаны c ошибкой.'.Debug::vars($deviceState)));
							};	
							
							//echo Debug::vars('49');exit;
							$url = 'http://localhost/city/index.php/sse/log';

							$request = Request::factory($url)
								->method('POST')
								->post('key', 'collectStatFromDevice:'.json_encode($deviceState));
								

							$response = $request->execute();
							Log::instance()->add(Log::DEBUG, '32-32  get device info id_dev='.$key.' time excute='.(microtime(true) - $t2));
							
					} else {
						Log::instance()->add(Log::DEBUG, '162 Неправильно указан IP адрес устройства id_dev='.$key);		
						
				
								

					}
				}
			}
				
		
       
		Log::instance()->add(Log::DEBUG, 'Collect stat for device END. Time executer='.(microtime(true)-$t1));	
		}
	}
		
	