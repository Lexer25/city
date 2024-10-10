    <?php defined('SYSPATH') or die('No direct script access.');
/**
*12.06.2024
* Проверка состояние точек прохода:
* - рабочее состояния open closed
* - открыта навсегда fired
* - закрыта навсегда Blocked
c:\xampp\php\php.exe c:\xampp\htdocs\city\modules\minion\minion --task=getdoorstatus
*/
 
    class Task_getdoorstatus extends Minion_Task {
		
		    protected $_options = array(
        // param name => default value
     
       // 'id_door'   => '2',
       // 'ip_address'   => '10.25.16.104',
		);
	
        
        protected function _execute(array $params)
        {
         //начало работы
		 
		 $devList=Model::factory('Device2')->getdeviceList(2);
		// Log::instance()->add(Log::DEBUG, '26 '.Debug::vars($devList)); //exit;
		// $devList=array('164'=>'164');// массив для отладки
		 
		 //Log::instance()->add(Log::DEBUG, '29 '.Debug::vars($devList)); //exit;
		 foreach($devList as $key=>$value)
				{
					
						$devtime= 'n/a';		
						$statusDoor0= 'n/a';		
						$statusDoor1= 'n/a';
						
						$dev=new Device($key);
						$ts2client=new TS2client();
						$ts2client->startServer();
						$t1=microtime(true);
							
							$message='t56 DeviceInfo name="'.$dev->name.'"';
							$ts2client->sendMessage($message);
							$answer=$ts2client->readMessage();
							//Log::instance()->add(Log::NOTICE, '36 '.Debug::vars( $ts2client, $dev, $message, $answer));exit;
							$deviceInfo=$this->deviceInfoPars($answer);
							$ip_address =Arr::get($deviceInfo, 'ConnectionString');
							//Log::instance()->add(Log::NOTICE, '50 id_dev='.$key.', ip='.$ip_address);//exit;
							
							if(strlen($ip_address)>0)
							{							
								 $request = Request::factory('http://'.$ip_address.'/8.xml');
				
				
								try{
								$response=$request->execute();

								$devtime= $this->minipars(trim($response->body()), 'T');		
								$statusDoor0= $this->minipars(trim($response->body()), 'A');		
								$statusDoor1= $this->minipars(trim($response->body()), 'B');		
														
								} catch (Kohana_Request_Exception $e){
									

								$devtime= 'n/a';		
								$statusDoor0= 'n/a';		
								$statusDoor1= 'n/a';		
									
								}
								Log::instance()->add(Log::NOTICE, '50 time=:devtime, id_dev=:id_dev, ip=:ip, door0=:door0, door1=:door1', array(':id_dev'=> $key, ':ip'=>$ip_address, ':door0'=>$statusDoor0, ':door1'=>$statusDoor1, ':devtime'=>$devtime));	
							}	
										
				}	
				
			
		 
		 		
		
			
		}
		
		/** минипарсер строки.
		/* Извлекает из строки содержимое между указанными параметрами.
		*/
		public function minipars($strdata, $label)
		{
			$_startPosition=strpos($strdata, '<'.$label.'>');
			$_endPosition=strpos($strdata, '</'.$label.'>');
			return trim(substr($strdata, $_startPosition+3, $_endPosition-$_startPosition-3));
		}
		
		
		/** парсинг строки ифнормации о контроллере из БД ТС.
		/* Извлекает из строки содержимое между указанными параметрами.
		*/
		public function deviceInfoPars($strdata)
		{
			$res=array();
			if(is_string($strdata)){

				$_strdata=(explode(",", trim(str_replace('"','', str_replace('t56 OK', '', trim($strdata))))));
				if(is_array($_strdata)){
				
					foreach ($_strdata as $key=>$value)
					{
						$data2=explode(",", $value);
						if(is_array($data2)){
						
							foreach($data2 as $key2=>$value2)
							{
								$data3=explode("=", $value2);
								
								$res[trim($data3[0])]=trim($data3[1]);
							}
						// 
						} else {
							
							Log::instance()->add(Log::DEBUG, '110 Ожидается массив, а получено неизвестно что.');	
							return $res;
						}
					}
				} else {
					
					Log::instance()->add(Log::DEBUG, '111 Ожидается массив, а получено неизвестно что.');	
					return $res;
				}
			} else {
				Log::instance()->add(Log::DEBUG, '121 Ответ не является строкой.');	
				return $res;
			
			}
			
			
			
			return $res;
		}
		
		
		
		
    }
	