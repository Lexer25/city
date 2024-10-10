<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Dashboard extends Controller_Template {

   public $template = 'template';
	
	public function before()
	{
			//Log::instance()->add(Log::NOTICE, 'Получил запрос в dashboard');
			parent::before();
			$session = Session::instance();
			
			if(!Session::instance()->get('skud_number')) 
			{
				if(count($skud_list=Model::Factory('skud')->getSkudList()) == 1)
				{
					Session::instance()->set('skud_number', Arr::get(array_keys($skud_list=Model::Factory('skud')->getSkudList()),0)) ;
					
				} else {
				
					$this->redirect('errorpage?err=no SKUD select.');
				}
			}		
			//Log::instance()->add(Log::NOTICE, 'База данных вот какая: '.Session::instance()->get('skud_number'));
			include Kohana::find_file('classes/controller','check_db_connect');
			
			Session::instance()->set('peopleEventsTimeFrom', date("d.m.Y H:m:s",strtotime("-1 days")));
			Session::instance()->set('peopleEventsTimeTo', date("d.m.Y H:m:s",strtotime("now")));
			
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
		$a=array();
		$event_stat=array();
		$system_events=array();
		if(Arr::get($config_windows, 'windows1', FALSE)) $a=Model::Factory('Stat')->stat();
		if(Arr::get($config_windows, 'windows5', FALSE)) $system_events=Model::Factory('Stat')->detect_change_device_count();
		if(Arr::get($config_windows, 'windows4', FALSE)) $event_stat=Model::Factory('Event')->stat();// подготовка статистических данных для раздела События. Готовится массив данных $list['card'], $list['device'], $list['order']
		$analyt_result = Model::Factory('Stat')->analyt_result();// 26.02.2020 подсчет аналитики
		$timeExecute=microtime(1)-$t1;
		$countErrKeyFormatRfid=count(Model::factory('dbskud')->checkRfidKeyFormat());
		//echo Debug::vars('57',$analyt_result, $a ); exit;
		$content = View::factory('dashboard', array(
			'list' => $a,
			'event_stat' => $event_stat,
			'event_stat_enable' => Arr::get($config_windows, 'windows4'),
			'system_events' => $system_events,
			'system_events_enable' => Arr::get($config_windows, 'windows5'),
			'analyt_result' => $analyt_result,
			'timeExecute' => $timeExecute,	
			'countErrKeyFormatRfid' => $countErrKeyFormatRfid,	
			));
		
		$this->template->content = $content;
		//echo View::factory('profiler/stats');
		
	}
	
	
	/** 14.09.2024 Просмотр списка карт с неправильным форматом
	*/
	public function action_ErrKeyFormatRfid()
	{
		//echo Debug::vars('95', Model::factory('dbskud')->checkRfidKeyFormat()); exit;
		//
		$res= Model::factory('dbskud')->checkRfidKeyFormat();
		if(is_array($res)){
			foreach($res as $key=>$value)
			{
				$var[]='"'.Arr::get($value,'ID_CARD').'"';
				
			}
			
		}
		
	
		$mess=__('Ошибка формата карт :cardlist. Номер карты должен содержать строку цифры и буквы ABCDEF. Удалите карту и зарегистрируйте ее еще раз.', array(':cardlist'=>implode(",", $var)));
		
		throw new Exception ('Неправильный формат карт '. $mess);
		
		$content = View::factory('result', array(
			'content' => $mess,
		));
		$this->template->content = $content;
		
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
	
	public function action_load() //таблица загрузки контроллеров
	{
        $_SESSION['menu_active']='load';

		
		if(array_key_exists('browser',$_POST)) $_SESSION['brows']=Arr::get($_POST, 'browser');

		//$list=Model::Factory('Stat')->load_table();
		
		$list=Model::Factory('Device')->getDoorList();//список точек прохода (дверей)
		$countDataBase=Model::Factory('Stat')->getAnyDataFromStdata(8); // выборка данных из st_data для указанного параметра. 8 - это количество карт по базе данных
		//echo Debug::vars('121', $countDataBase);exit;
		$date_stat=Model::Factory('Stat')->date_stat();//получение даты и времени выбора статистики

		$devtypeList=Model::Factory('Device')->getDevtypeList();//получить типы устройств

		//echo Debug::vars('115', $date_stat, $list); exit;
		//$content = View::factory('load_table', array(
		$content = View::factory('load_table_new', array(
			'list' => $list,
			'countDataBase' => $countDataBase,
			'date_stat' =>$date_stat,
			'devtypeList' =>$devtypeList,
		));

        $this->template->content = $content;
        //echo View::factory('profiler/stats');
	}
	
	
	public function action_load_old() //копия action_load 22.08.2024
    {
		$_SESSION['menu_active']='load';

		
		if(array_key_exists('browser',$_POST)) $_SESSION['brows']=Arr::get($_POST, 'browser');

		$list=Model::Factory('Stat')->load_table();

		$date_stat=Model::Factory('Stat')->date_stat();//получение даты и времени выбора статистики

		$devtypeList=Model::Factory('Device')->getDevtypeList();//получить типы устройств

		//echo Debug::vars('115', $date_stat, $list); exit;
		$content = View::factory('load_table', array(
			'list' => $list,

			'date_stat' =>$date_stat,
			'devtypeList' =>$devtypeList,
		));

        $this->template->content = $content;
        //echo View::factory('profiler/stats');
	}
	
	
	
	
	public function action_load_order()
	{
		
		$_SESSION['menu_active']='load_order';
		
		if(!empty($_POST['stop_load'])) Model::Factory('Stat')->stop_load($_POST['stop_load']);
		if(Arr::get($_POST, 'reload', 0)) Model::Factory('Stat')->repeat_load(Arr::get($_POST, 'reload'));
		if(Arr::get($_POST, 'del_queue', 0)) Model::Factory('Stat')->del_queue(Arr::get($_POST, 'reload'));
		
		$b=Model::Factory('Stat')->load_order(); // вывод очереди карт на загрузку
		$c=Model::Factory('Stat')->load_order_overcount(); // вывод очереди карт на загрузку с превышенным количеством попыток
		
		$content = View::factory('order_table', array(
			'list' => $b,
			'overcount'=>$c,
		));
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
	
	public function action_device_control()// обработка кнопок рыботы с контролерами
	{
		$_SESSION['menu_active']='device_control';
		
		//echo Debug::vars('144', $_POST); exit;
		$res='';
		if(array_key_exists('checkStateDoor',$_POST)){ // опрос состояния контроллеров
				
				//echo Debug::vars('177 опрос указанных контроллеров', $_POST);exit;
				if(is_null(Arr::get($_POST, 'id_dev'))) $this->redirect('errorpage?err='.__('no device id for check door state'));
				

					
					$sql='select distinct d2.id_dev from device d
							join device d2 on d2.id_ctrl=d.id_ctrl  and d2.id_reader is null
							where d.id_dev in ('.implode(",", Arr::get($_POST, 'id_dev')).')';
					
					$query = DB::query(Database::SELECT, $sql)
							->execute(Database::instance('fb'))
							->as_array();
				
				foreach ($query as $key=>$value){
					Model::factory('Device')->getStatForOneController(Arr::get($value, 'ID_DEV'));//надо указать id контроллера
					Log::instance()->add(Log::DEBUG, '183 сбор информации для контроллера id_dev='.Arr::get($value, 'ID_DEV'));
				}
				$res='183 сбор информации для контроллеров'.implode(",", Arr::get($_POST, 'id_dev'));
		}

		if(array_key_exists('all',$_POST)) 
			{
				$id_dev=Model::Factory('Device')->getdeviceList();
			} else {
			
				$id_dev=Arr::get($_POST, 'id_dev'); 
			}

		
		
		if (Arr::get($_POST, 'synctime'))
		{
				if(is_null(Arr::get($_POST, 'id_dev'))) $this->redirect('errorpage?err='.__('no device id for synctime'));
				Log::instance()->add(Log::NOTICE, 'Synctime for device :user', array(
					'user' => implode(",",$id_dev),
				));
				
				
				$res=$res.Model::Factory('Device')->synctime($id_dev);
				
		}
		
		if (Arr::get($_POST, 'checkStatus'))// запись состояния контроллера в БД: версия контроллера, контроль линии связи, кол-во карт в указаанной канале (только в одном!!!), кол-во карт двери по базе данных.
		{
				
				//echo Debug::vars('173', $_POST, $id_dev); //exit;
				$sql='select distinct d2.id_dev, d2.id_devtype, d2.netaddr from device d
					join device d2 on d2.id_ctrl=d.id_ctrl and d2.id_reader is null
					where d.id_dev in ('.implode(",", $id_dev).')';
					
				$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();	
				//	echo Debug::vars('270', $query);exit;
				foreach($query as $key)
				{
						
						switch(Arr::get($key, 'ID_DEVTYPE')){
							case 1: //контроллеры типа Артонит
							case 2: //контроллеры типа Артонит
							$ip_address=Arr::get($key, 'NETADDR');
								if($ip_address){//если указана строка подключения (а пока под этим подразумевается IP адрес), то иду выбирать данные для этого контроллера.
								$tt1=microtime(true);
										$deviceHard = new artonitHTTP($ip_address);
										$deviceHard->getDeviceInfo();// заполняю данные экземпляра из полученных ответов
										$deviceHard->disconnect();
										
									} else {
										echo Debug::vars('1179 Неправильно указан IP адрес устройства id_dev='.$key);exit;					
								}
								//echo Debug::vars('336', $deviceHard); exit;
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
										);
								
								$id_param=113;//название параметра - данные в виде json. Этот же параметр должен быть заявлен в БД в таблице ST_PARAM
								$id_agent=1;
								$order=445;//$ser;
								
								if(Model::factory('device')->stat_insert($order, Arr::get($key, 'ID_DEV'), $id_agent, $id_param, json_encode($deviceState)) == 0) {
									
										Log::instance()->add(Log::DEBUG, 'Данные для id_dev='.Arr::get($key, 'ID_DEV').' ip='.$ip_address.'записаны успешно');
									
								} else {
									
									Log::instance()->add(Log::DEBUG, Log::instance()->add(Log::DEBUG, 'ERR Данные для id_dev='.$key.' ip='.$ip_address.'записаны c ошибкой.'.Debug::vars($deviceState)));
								};
						
							
							break;
							default:
							
								throw new Exception('Function checkStatus for devtype id_dev='.Arr::get($key, 'ID_DEV').' connection '.$ip_address.'Not implemented, please install iconv');
							
							break;
							
						}
				
					
				}
			
			
			
				$res= 'insertStatusIdDev_arr for id_dev '. implode(",",$id_dev);
				//Model::Factory('Stat')->fixKeyOnDBCountForDoors($id_dev);//вставка количества карт для точки прохода по базе данных
				Model::Factory('Stat')->fixKeyOnCardidx($id_dev);//вставка количества карт для точки прохода по базе данных
		}
		
		if (Arr::get($_POST, 'checkStatusOnLine'))// проверка статуса он-лайн. Делается вычитка количества карт по базе данных и из контроллера и заносится в базу данных.
		{
				//echo Debug::vars('178', 'checkStatus'); exit;
				$res=Model::Factory('Device')->checkStatusOnLine($id_dev);
				$b=Model::Factory('Stat')->load_table($id_dev, $res);
				
		}
		
		if (Arr::get($_POST, 'load_card'))// загрузить карты в контроллер 
		{

				if(is_null(Arr::get($_POST, 'id_dev'))) $this->redirect('errorpage?err='.__('no device id for load'));
				$res=Model::Factory('Device')->load_card_arr($id_dev);
		}
		
		
		if (Arr::get($_POST, 'cardidx_refresh'))// загрузить карты в контроллер 
		{

				if(is_null(Arr::get($_POST, 'id_dev'))) $this->redirect('errorpage?err='.__('cardidx_refresh'));
				$res=Model::Factory('Device')->cardidx_refresh($id_dev);
		}
		
		
		if (Arr::get($_POST, 'clear_device'))
		{
				if(is_null(Arr::get($_POST, 'id_dev'))) $this->redirect('errorpage?err='.__('no device id for clear'));
				$res=Model::Factory('Device')->clear_device_arr($id_dev);
		}
		
		if (Arr::get($_POST, 'control_door'))//выполнение команд для точек прохода. Сама команда содержится в Arr::get($_POST, 'control_door') (открыть, закрыть и т.п.)
		{
				//echo Debug::vars('257', $_POST, Arr::get($_POST, 'control_door'));exit;
				if(is_null(Arr::get($_POST, 'id_dev'))) $this->redirect('errorpage?err='.__('no device id for clear'));// если нет перечня точек прохода, то выходим...
				$res=Model::Factory('Device')->unlock_door_arr($id_dev, Arr::get($_POST, 'control_door'));// реализация команды управления точкой прохода
				sleep(2);//пауза, чтобы контроллер успел поменять свое состояние
				foreach($id_dev as $key=>$value)// тут получаю список точек прохода (не контроллеров!!!)
				{

					 $id_dev_hard=Arr::get(Model::Factory('Device')->get_device_info($key), 'device_id');//16.07.2024 доработка для опроса нового состояния точек прохода
					 //Log::instance()->add(Log::DEBUG, '245 надо записать состояние точек прохода для контроллера '.$id_dev_hard);
					
					 Model::Factory('Device')->getStatForOneController($id_dev_hard);// чтение нового состояния контроллера (закрыт, открыт и т.п.)
					
				} 
		}
		
		if (Arr::get($_POST, 'settz'))
		{
				if(is_null(Arr::get($_POST, 'id_dev'))) $this->redirect('errorpage?err='.__('no device id for settz'));
				$res=Model::Factory('Device')->settz_arr($id_dev);
		}
		
		
		/*28.08.2024 Сверка карт на основе нового алгоритма
		* делается вычитка карт из контроллера в массив,
		*делается вычитка карт из БД в массив,
		* делается сверка массивов.
		* результат записывается в базу данных, в таблицу cardindev
		*/
		if (Arr::get($_POST, 'readkey'))//вычитать данные из контроллеров, сравнить с базой данных, найти "лишние" карты и выставить их в очередь на удалдение.
		{
			$errKeyFormat=0;	
			$errKeyFormat=Model::factory('dbskud')->checkRfidKeyFormat();
	//echo Debug::vars('421', $errKeyFormat);exit;
			if(count($errKeyFormat)>0) throw new Exception ('Ошибка в номерах идентификаторов RFID. Проверьте RFID '.implode(",", $errKeyFormat));
				$post=Validation::factory($_POST);
				$post->rule('id_dev', 'not_empty')
					->rule('readkey', 'digit');
					
					$t1=microtime(true);
			if($post->check())
			{
				$t1=microtime(true);
					
			
			//получаю ip адреса контроллеров, тип контроллеров для точек прохода, с которыми надо работать.
					$sql='select distinct d.id_dev, d2.id_devtype, d2.netaddr, d2.name, d.id_reader from device d
					join device d2 on d2.id_ctrl=d.id_ctrl and d2.id_reader is null
					where d.id_dev in ('.implode(",", $id_dev).')';
					
			$query = DB::query(Database::SELECT, $sql)
				->execute(Database::instance('fb'))
				->as_array();	
				
				//echo Debug::vars('420', $query);exit;
				//для каждой точки прохода (а не контроллера!) организую цикл вычитки номеров ключей 
				$result='Старт процесса сверки для контроллеров '. implode(",", $id_dev);
				$res=array();//массив, в который будут записыватьяс карты, вычитанные из контроллера.
				Log::instance()->add(Log::NOTICE, '475 '.$result);
								
				foreach($query as $key)
				{
					
						switch(Arr::get($key, 'ID_DEVTYPE')){
							case 1: //контроллеры типа Артонит
							case 2: //контроллеры типа Артонит
							$ip_address=Arr::get($key, 'NETADDR');
								
								//созданю экземпляр класса работы через ТС2
								$dev=new phpArtonitTS2();
			
							
								$dev->dev_name=Arr::get($key, 'NAME');

		
								$dev->connect();
								
							//echo Debug::vars('463', $dev);exit;
						
							if($dev->connection) {
								
							//		$t1=microtime(true);
								//получаю данные о версии контроллера и режиме работы.
								
									$deviceHard = new artonitHTTP($ip_address);
									$deviceHard->getSoftVersion();
									$deviceHard->getScudMode();
									$deviceHard->disconnect();
								//echo Debug::vars('503', $deviceHard->scud, Arr::get($key, 'ID_READER'), (!($deviceHard->scud == 'd1') AND (Arr::get($key, 'ID_READER') ==1))); exit;	
								if(!(($deviceHard->scud == 'd1') AND (Arr::get($key, 'ID_READER') ==1))){ // если режим 1 дверь, и канала 1, то сверку не проводить!!!
									
									//echo Debug::vars('507', $deviceHard->scud, Arr::get($key, 'ID_READER')); exit;	
									
									$strFrom='Key=';
									$strTo='"", Access';
									$startShift=6;
								//вычитываю карты из контроллера в цикле
								for($i=0; $i<4000; $i++){

									//$res[]=trim($dev->sendcommand('readkey door=0, cell='.$i));
									$command='readkey door='.Arr::get($key, 'ID_READER').', cell='.$i;
									//echo Debug::vars('447', $command);//exit;
									$strdata=trim($dev->sendcommand($command));
									// string(66) "t45 OK Answer="OK Cell=0, Key=""0022AE0D"", Access=Yes, TZ=0x0001""
									
									//Log::instance()->add(Log::NOTICE, '451 Комнада '.$command.'Ответ'.$strdata);
					
									
									$var1=explode(",", $strdata);
									
									$card_=explode("=", trim($var1[1]));
									$access_=explode("=", trim($var1[2]));
									$tz_=explode("=", trim($var1[3]));

									switch(Kohana::$config->load('artonitcity_config')->baseFormatRfid){
										case 0:
											$keyRfidLenght=8;
										break;
										case 1:
											$keyRfidLenght=10;
										break;
											
									}
									
									$card=str_pad(trim(Arr::get($card_,1), '"'), $keyRfidLenght, 0, STR_PAD_LEFT);// формирую номер карты как строка. Количество символов зависит от формата базы данных, добавлена нулями слева
								
									$access=trim(Arr::get($access_,1));
									$tz=trim(Arr::get($tz_, 1));
								
									if((preg_match('/^00000000/', $card) ==0) and ($access == 'Yes') and ($tz<>'TZ=0x0000') )$res[$card]=$card;
								
								}
								$dev->close();
								
								Log::instance()->add(Log::NOTICE, '482 из контроллера id_dev='.Arr::get($key, 'ID_DEV').' выбрал '.count($res).' карты за время '.(microtime(true) - $t1));
									// сохраняю массив в файл (для последующего анализа)
								$file_name="compare_readkey_from_door_id_door=".Arr::get($key, 'ID_DEV');
								$this->saveFile($file_name,$res);
							
								
								
								//echo Debug::vars('519', $res, count($res), $dev->connection);exit;
								// массив карт может быть пустой (и при этом контроллер на связи. Например, его заменили. В этом случае надо продолжить работу как обычно...
								//но если массив пустой, т.к. нет связи, то работу по сверке необходимо прекратить. 
								//т.о., продолжать сверку надо только при наличии связи с контроллером: пустой массив не является признаком отсутсвия связи.
													
								// теперь готовлю список карт из таблицы crdidx для выбранной точки прохода
										$sql='select cd.id_card from cardidx cd where cd.id_dev='.Arr::get($key, 'ID_DEV');
											
										$query_db = DB::query(Database::SELECT, $sql)
									->execute(Database::instance('fb'))
									->as_array();	
									$card_db=array();
									foreach($query_db as $key3=>$value3)
									{
																	
										$card_db[]=strtoupper(str_pad(trim(Arr::get($value3, 'ID_CARD')), $keyRfidLenght, 0, STR_PAD_LEFT));
									}
						//удаляю повторяющиеся значения				
									$card_db=array_unique($card_db);
									
						// сохраняю массив в файл (для последующего анализа)
									$file_name="compare_readkey_from_db_id_door=".Arr::get($key, 'ID_DEV');
									$this->saveFile($file_name,$card_db);

									
									Log::instance()->add(Log::NOTICE, '497 выбрал из базы данных '.count($card_db).' карты id_dev='.Arr::get($key, 'ID_DEV'));			

						// ищу карты для удаления из контроллера СКУД	
										$cardForDeleteArray=array_diff($res, $card_db);
										
						//Организую запись в очередь на удаление карт
										if(count($cardForDeleteArray)>0){
											
											
						// сохраняю массив в файл (для последующего анализа)
												$this->saveFile('compare_cardForDeleteArray_for_id_dev_'.Arr::get($key, 'ID_DEV'),$cardForDeleteArray);
												Log::instance()->add(Log::NOTICE, 'Карт для удаления для точки прохода id_dev='.Arr::get($key, 'ID_DEV').' найдено '.count($cardForDeleteArray).' штук.');
											
											foreach($cardForDeleteArray as $key4=>$value4)
											{
												
												Model::factory('Device')-> delKeyFromIdDev($value4, Arr::get($key, 'ID_DEV'), 508);// постановка карты в очередь на удаление
											}
											
										} else {
											Log::instance()->add(Log::NOTICE, 'Карт для удаления для точки прохода id_dev='.Arr::get($key, 'ID_DEV').' не найдено.');
										}
										
						//Ищу карты для записи в контроллер			
										$cardForWriteArray=array_diff($card_db ,$res);
										
						//Организую запись в очередь на запись карт
										if(count($cardForWriteArray)>0){
											
											foreach($cardForWriteArray as $key4=>$value4)
											{
																	// сохраняю массив в файл (для последующего анализа)
												$this->saveFile('compare_cardForWriteArray_for_id_dev_'.Arr::get($key, 'ID_DEV'),$cardForWriteArray);
												
												
												Model::factory('Device')->writeKeyToDevice($value4, Arr::get($key, 'ID_DEV'));// постановка карты в очередь на запись 2024
											}
											Log::instance()->add(Log::NOTICE, 'Карт для записи в точку прохода id_dev='.Arr::get($key, 'ID_DEV').' найдено '.count($cardForWriteArray).' штук.');
											
										} else {
											Log::instance()->add(Log::NOTICE, 'Карт для записи в точку прохода id_dev='.Arr::get($key, 'ID_DEV').' не найдено .');
											
										}
										
								$result=$result.'<br> работу по сверке карт с точкой прохода '.Arr::get($key, 'ID_DEV').' завершил. Поставлено в очередь на удаление '.count($cardForDeleteArray).' карт, на запись '.count($cardForWriteArray).' карт. Время выполнения '.(microtime(true) - $t1);			
								
								
								}else {
									
									$result='Для точки прохода id_dev='.Arr::get($key, 'ID_DEV').' "'. iconv('windows-1251','UTF-8',Arr::get($key, 'NAME')).'" сверка не производится, т.к. контроллера имеет настройку Одна дверь на два считывателя, и канал 1 повторяет содержимое канала 1.';
									Log::instance()->add(Log::NOTICE, $result);
									
								}
								
								} else { // если нет подключения
								
									$result=$result.'<br>Подключение точке прохода id_dev= '.Arr::get($key, 'ID_DEV').' контроллер '.iconv('windows-1251','UTF-8', Arr::get($key, 'NAME')).' произошло неудачно. Причина: '.$dev->errDesc;				
				
								
								
								}
								
							break;
							default:
							
								throw new Exception('Function checkStatus for devtype id_dev='.Arr::get($key, 'ID_DEV').' connection '.$ip_address.'Not implemented, please install iconv');
							
							break;
							
						}
			
								
				}
			
			
			} else {
				
				$res=$result.'<br>Валидация списка точек прохода для сверки прошла неудачно.';	
			}
				
			$res=$result.'<br> сверка карт для точек прохода '.implode(",", $id_dev).' заверешн.';	
		}
		
		
	
		
		
		if (Arr::get($_POST, 'checkkey'))//8.07.2020 вычитать данные из контроллера по списку из БД, найти карты, которых нет в контроллере, и выставить их на запись в контроллеры
		{
				echo Debug::vars('205', $_POST ); exit;
				//$res=Model::Factory('Check')->checkKey($id_dev, NULL);
				$res=Model::Factory('Device')->readkey_arr(Arr::get($post, 'id_dev'));
		}
		
		$resultMessages=array();
		//echo Debug::vars('629',  Session::instance()->get('res') );exit;
		$resultMessages=Session::instance()->get('res');
		$resultMessages[]=$res;
		Session::instance()->set('res',$resultMessages);
		
		$content = View::factory('result', array(
			'content' => $res,
		));
		
        $this->template->content = $content;
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
