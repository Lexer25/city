<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Dashboard extends Controller_Template {

   public $template = 'template';
   //Широки шаблон
   //для использьвания необходимо указать 
   //$this->template = View::factory($this->template_width);
   public $template_width = 'template_width';
   
  	
	public function before()
	{
			
			parent::before();
			$session = Session::instance();
			include Kohana::find_file('classes/controller','check_db_connect');
			
			Session::instance()->set('peopleEventsTimeFrom', date("d.m.Y H:i:s",strtotime("-1 days")));
			Session::instance()->set('peopleEventsTimeTo', date("d.m.Y H:i:s",strtotime("now")));
			
			
	}
	

	
	public function action_services()
	{
		
		$serverList=Model::factory('Check')->getServerList();// получили список транспортных серверов
		$content = View::factory('services', array(
			'serverList'=>$serverList,
			));
		$this->template->content = $content;
		
	}

	
	
	public function action_index()
	{	
				$t1=microtime(1);
				
		//Проверка авторизации
		if (!empty($_POST)) {
             	$username = Arr::get($_POST, 'username');
                $password = Arr::get($_POST, 'password');
			
                if (Auth::instance()->login($username, $password)) {
                $user = Auth::instance()->get_user();
				}
			}
			$config_windows=Kohana::$config->load('artonitcity_config')->main_windows;
			
			
	// подготовка и вывод информации для панелей №№ 1, 2, 3.
		
		$_SESSION['menu_active']='index';
		$list=array();
		$event_stat=array();
		$system_events=array();
		$list_windows1=array();
		$list_windows2=array();
		$list_windows3=array();
		$t1=microtime(true);
		//if(Arr::get($config_windows, 'windows1', FALSE)) $list=Model::Factory('Stat')->stat();
		
		//if(Arr::get($config_windows, 'windows1', FALSE)) $list_windows1=Model::Factory('Stat')->getStatPeopleAndCard();//статистика для окна 1
		
		if(Arr::get($config_windows, 'windows1', FALSE)) 
		{
			$list_windows1=$this->getWin1();
			$list_windowsGuest=$this->getWin1Guest();
			
		}
		
		if(Arr::get($config_windows, 'windows2', FALSE)) $list_windows2=Model::Factory('Stat')->getEquipment();//оборудование
		
		if(Arr::get($config_windows, 'windows3', FALSE)) $list_windows3=Model::Factory('Stat')->getLoadOrder();//очередь загрузок
		
		
		$analyt_result = Model::Factory('Stat')->analyt_result();// 26.02.2020 подсчет аналитики
		$timeExecute=microtime(1)-$t1;
		$countErrKeyFormatRfid=count(Model::factory('dbskud')->checkRfidKeyFormat());
		//echo Debug::vars('57',$analyt_result, $list ); exit;
		$_connectName='fb';
		$about=Model::factory('Parkdb')->aboutDB($_connectName);
		$content = View::factory('dashboard', array(
			'list_windows1' => $list_windows1,
			'list_windows2' => $list_windows2,
			'list_windows3' => $list_windows3,
			'analyt_result' => $analyt_result,
			'countErrKeyFormatRfid' => $countErrKeyFormatRfid,	
			'about' => $about,	
			'config_windows' => $config_windows,//информация о разрешенных окнах
			
			));
		
		$this->template->content = $content;
		//echo View::factory('profiler/stats');
		
	}
	
	/**31.03.2026 Сбор информации для окна №1
					
	*/
			public function getWin1()
			{
				$config = Kohana::$config->load('artonitcity_config');
				$days = (int) $config->count_day_befor_end_time;
				$dateExpired=date('d.m.Y', strtotime("+{$days} days"));//дата для расчета
				$people_model = Model::factory('people');
				$card_model = Model::factory('identifier');
				
				$result=array();
				$result['people_count']=$people_model->getPeopleCount();//количество пользователей
				$result['key_people_delete']=$people_model->getDeletedPeopleCount();//количество удаленных пользователей
				$result['getPeopleWithoutCard']=$people_model->getPeopleWithoutCard();//количество сотрудников без карты
				
                                $result['timeExpired']=$dateExpired;//дата для расчета
				$result['count_card_late_next_week']=$card_model->getCountCardLateNextTime($result['timeExpired']);//количество карт, срок которых истечет до указанной даты
				$result['getcardexpired']=$card_model->getcardexpired();//количество карт, у которых истек срок действия
				$result['getCardNotActive']=$card_model->getCardNotActive();//количество неактивных идентификаторов
				
				
				
				return $result;
			}
			
			
			
		/**2.04.2026 Сбор информации для окна №1 по бюро пропусков
					
		*/
			public function getWin1Guest()
			{
				$config = Kohana::$config->load('artonitcity_config');
				$days = (int) $config->count_day_befor_end_time;
				$dateExpired=date('d.m.Y', strtotime("+{$days} days"));//дата для расчета
				$people_model = Model::factory('people');
				$card_model = Model::factory('identifier');
				
				$result=array();
				$result['people_count']=$people_model->getPeopleCount();//количество пользователей
				$result['key_people_delete']=$people_model->getDeletedPeopleCount();//количество удаленных пользователей
				$result['getPeopleWithoutCard']=$people_model->getPeopleWithoutCard();//количество сотрудников без карты
				
                                $result['timeExpired']=$dateExpired;//дата для расчета
				$result['count_card_late_next_week']=$card_model->getCountCardLateNextTime($result['timeExpired']);//количество карт, срок которых истечет до указанной даты
				$result['getcardexpired']=$card_model->getcardexpired();//количество карт, у которых истек срок действия
				$result['getCardNotActive']=$card_model->getCardNotActive();//количество неактивных идентификаторов
				
				
				
				return $result;
			}
			
			
			
			
			
			
			
			
	/** 14.09.2024 Просмотр списка карт с неправильным форматом
	*/
	public function action_ErrKeyFormatRfid()
	{
		//echo Debug::vars('95', Model::factory('dbskud')->checkRfidKeyFormat()); exit;
		//
		$res= Model::factory('dbskud')->checkRfidKeyFormat();
		$var=array();
		if(is_array($res)){
			foreach($res as $key=>$value)
			{
				$var[]='"'.Arr::get($value,'ID_CARD').'"';
				
			}
			
		}
		
		if(count($var)){
		$mess=__('Ошибка формата карт :cardlist. Номер карты должен содержать строку цифры и буквы ABCDEF. Удалите карту и зарегистрируйте ее еще раз.', array(':cardlist'=>implode(",", $var)));
		
		throw new Exception ('Неправильный формат карт '. $mess);
		
		$content = View::factory('result', array(
			'content' => $mess,
		));
		$this->template->content = $content;
		} else {
			
			$this->redirect('/');
		}
		
	}

	public function action_log()// просмотр лог-файлы
	{
		$_SESSION['menu_active']='log';
		$res1=Model::Factory('Log')->getList();
		$res2=Model::Factory('Log')->getListCompare();
		
		$content=View::factory('Log', array(
			'list1'=> $res1,
			'list2'=> $res2,
			));
		$this->template->content = $content;
	}
	
	public function action_sendFile ()//передача данных пользователю
	{
		$file=Arr::get($_GET, 'name');	
		$content = Model::Factory('Log')->send_file($file);
		$this->template->content = $content;
	}
	
	
	
	
	
	
	
	
	
	
    
	public function ErrMess ($err=false)
	{
		$content = View::factory('errorpage');
		$this->template->content = $content;
	}
	
	public function action_opendoor()// обработка команды открывания дверей
	{
		Log::instance()->add(Log::NOTICE, 'Получил запрос opendoor');
		$res=Model::Factory('Device')->sendCommand('127.0.0.1', 1967, '333', 'opendoor door=0');
		$content = View::factory('result', array(
			'content' => $res,
		));
	    $this->template->content = $content;
	}
	
	
	/**31.08.2024  функция записи массива данных в файл
	*/
	public function saveFile($fileName, $data)
	{
				$fileName=$fileName.".csv";
				$fp = fopen($fileName, 'w');
				foreach ($data as $key=>$value)
				{
//если $value массив, то сохраняю через fputcsv
					if(is_array($value)){
						fputcsv ($fp, $value,';');
					} else {
						
						fwrite ($fp, $value.PHP_EOL);
					}
				}
					
				
			
		fclose($fp); //Закрытие файла
		
	}
	
	


	/** 2.09.2024 экспорт состояния СКУД в файл
	
	*/
	public function action_saveStateSkud()
	{
		// заголовок отчета
		$reportTitle=array();
		//$reportTitle[]=array('Отчет о состоянии СКУД','Отчет о состоянии СКУД',);
		//$reportTitle[]=array('','','','',date('Y-m-d H:i:s'));
		$reportTitle[]=array('id', 
				// iconv('UTF-8','windows-1251','Название'), 
				// iconv('UTF-8','windows-1251','Тип'),
				// iconv('UTF-8','windows-1251','Активность'),
				// iconv('UTF-8','windows-1251','Строка подключения'),
				'name',
				'type',
				'is_active',
				'connectionString',
				'mac',
						'onLine',
						'isWP',
						'isTest',
						'door_0',
						'doore_1',
						'inputPortState',
						'softVersion',
						'keyCount',
						'timestamp',
				
				);
		
		//список контроллеров и их состояние
		$deviceList=Model::factory('Device')->getdeviceList();
		
		
		//echo Debug::vars('635', $deviceList);exit;
		
		foreach($deviceList as $key=>$value)
		{
			$device=new Device ($value);
			$deviceInfo=new DeviceInfo($value, trim(Model::Factory('Stat')->getDeviceStatData($value)));
			//echo Debug::vars('641', $key, $value, $device, $deviceInfo);exit;
			//if($key>107) {echo Debug::vars('653', $device, $deviceInfo);exit;}
			
			
			$reportTitle[]=array($device->id, 
					$device->name,
					$device->type,
					$device->is_active? 'Yes':'No',
					$device->connectionString,
						$deviceInfo->mac,
						$deviceInfo->onLine? 'Yes':'No',
						$deviceInfo->isWP? 'Yes':'No',
						$deviceInfo->isTest? 'Yes':'No',
						$deviceInfo->doorMode_0,
						$deviceInfo->doorMode_1 ,
						is_array($deviceInfo->inputPortState)?  implode("", $deviceInfo->inputPortState) : '',
						$deviceInfo->softVersion,
						is_array($deviceInfo->keyCount)? implode(",", $deviceInfo->keyCount) : '',
						date("H:i:s d.m.Y", $deviceInfo->timeGetData) ,
						);
			
			
		}
		
		//список "неправильных" карт
		$objectName= isset(Kohana::$config->load('artonitcity_config')->city_name)? Kohana::$config->load('artonitcity_config')->city_name : '';
		$file_name="scud_report_".$objectName.'_'.date('Y_m_d_H-i-s').".csv";
		
			$fp = fopen($file_name, 'w');
			
						foreach ($reportTitle as $fields) {
							fputcsv($fp, $fields, ';');
						}

						fclose($fp);
					$file=$file_name;
						if (file_exists($file)) {
				// сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
				// если этого не сделать файл будет читаться в память полностью!
				if (ob_get_level()) {
				  ob_end_clean();
				}
				// заставляем браузер показать окно сохранения файла
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename=' . basename($file));
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Length: ' . filesize($file));
				// читаем файл и отправляем его пользователю
				readfile($file);
				exit;
			  }
			  
  
		$this->redirect('/Dashboard');
		
	}



	/** 3.09.2024 выборка IP адресов из ТС2 и вставка их в БД СКУД.
	*/
	public function action_getIpFromTs2()
	{
		//получаю список транспортных серверов
		
		//получаю список контроллеров
		$deviceList=Model::factory('Device')->getdeviceList();
		//далее надо работать только с типом 1 и 2 (они обслуживаются в ТС2
		
		foreach($deviceList as $key=>$value)
		{
			$dev=new Device($key);
			//echo Debug::vars('735', $dev);exit;
			switch($dev->type){
							case 1: //контроллеры типа Артонит
							case 2: //контроллеры типа Артонит
							
								//созданю экземпляр класса работы через ТС2
								$TS2client=new TS2client();
								$TS2client->startServer();
								
								$command ='h56 deviceinfo name="'.$dev->name.'"';
								$TS2client->sendMessage($command);
								$answer=$TS2client->readMessage();
								$TS2client->stopClient();
								echo Debug::vars('746', $command, $answer);exit;
								
								$dev->connect();
								
							if($dev->connection) {
								
						//		$t1=microtime(true);
								$command='readkey door='.Arr::get($key, 'ID_READER').', cell='.$i;
									//echo Debug::vars('447', $command);//exit;
									$strdata=trim($dev->sendcommand($command));
								$device=new Device($key);
								echo Debug::vars('730', $key, $value, $device);exit;
							}
							break;
							default;
							break;
				}
		}
	}
	
	public function parsFromStrToStr($strdata, $strFrom, $startShift, $strTo)
	{
		
		if(!$strdata) {
			Log::instance()->add(Log::DEBUG, 'Line 269. Входящая строка для анализа пустая. Работа парсера прекращается.');	
				
			return '';
		}
		
		$_startPosition=strpos($strdata, $strFrom)+$startShift;
		$_stopPosition=strpos($strdata, $strTo, $_startPosition);
//echo Debug::vars('169', $_startPosition, $_stopPosition , substr($strdata, $_startPosition, $_stopPosition-$_startPosition)); exit;
		if($_stopPosition-$_startPosition >0) {
			return substr($strdata, $_startPosition, $_stopPosition-$_startPosition);
		} else return '';
		
		
	}
	
	
}
