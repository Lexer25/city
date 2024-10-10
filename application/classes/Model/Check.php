<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_Check extends Model
{
	public function getServerList ()
		{
		$sql='select * from server';
			$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
		return $query;
		}
		
	public function getDeviceListFromServer ($id_server)
	{
		$sql='select ip, port from server  where id_server='.$id_server;
			$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
		foreach ($query as $key=>$value){
			$ip=Arr::get($value, 'IP', 'no');
			$port=Arr::get($value, 'PORT', 'no');
		}
		$device_list=$this->sendCommand(Model::Factory('Stat')->IntToIP($ip), $port, NULL, 'enumdevices');// получил список устройств из транспортного сервера
		
		$list=Model::Factory('Stat')->parser_1($device_list);
		$res=explode (",", substr($list, 1, strlen($list)-2));
		
		
		//echo Debug::vars('37',$res);
		$_SESSION['device_list']=$res;
		return $res;
	}
	
	
	public function sendCommand ($server, $port, $id_dev, $command, $test=FALSE) // процедура передачи команды в транспортный сервер.
	{
		$smes = 'r55 login name="3", password="3"';
		$smes1 = 'r55 enumdevices';
		if(isset($id_dev)) 
		{
			$smes_command = 'r55 exec device="'.trim($_SESSION['device_list'][$id_dev]).'", command="'.$command.'"';
			
		} else {
			$smes_command = 'r55 '.$command;
		}
		
		//echo Debug::vars('54', $smes_command );
			
		//создаем сокет для подключения ТСП
		if (false == ($socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP))) {
			die("Couldn't create socket, error code is: " . socket_last_error() .
			",error message is: " . socket_strerror(socket_last_error()));
			}
		
		// создаем соединение с сервером
		$connection = @socket_connect($socket, $server, $port);
		if ($connection === false)      die("Cannot connect to server {$server}:{$port}");
		
		$reply = socket_read($socket,4096);
		
		//авторизация
		socket_write($socket, $smes."\r\n", strlen($smes."\r\n"));
		//получаем ответ
		$reply = socket_read($socket,4096);
				
		//send command
		socket_write($socket, $smes_command."\r\n", strlen($smes_command."\r\n"));
		//получаем ответ
		$reply = socket_read($socket,4096);
		
		//закрыть сокет
		socket_close($socket);
		return $reply;
	}
	
	
	
	public function checkKey($id_dev_arr)// подготовка файлов с результатом сверки карт в контроллере и в базе данных СКУД.
	{
		
		foreach ($id_dev_arr as $id_dev=>$value)
		{
			$id_server=$this->GetIdServer($id_dev);
			$this->getStatusIdDev($id_dev);
		 }
		return;
	
	}
	
	public function GetIdServer($id_dev)//получить id транспортного сервера по id_dev
	{
		$sql='select d2.id_server from device d
			join device d2 on d.id_ctrl=d2.id_ctrl and d2.id_reader is null
			where d.id_dev='.$id_dev;
			$query = DB::query(Database::SELECT, $sql)
						->execute(Database::instance('fb'))
			->get('ID_SERVER');
		return $query;
	
	}
	
	public function GetNameController($id_dev)//получить имени контроллера
	{
		$sql='select d2.name from device d
			join device d2 on d.id_ctrl=d2.id_ctrl and d2.id_reader is null
			where d.id_dev='.$id_dev;
			$query = DB::query(Database::SELECT, $sql)
						->execute(Database::instance('fb'))
			->get('NAME');
		return $query;
	
	}
	
	public function getStatusIdDev ($id_dev) // чтение ключей из устройства, сверка с базой, удаление лишних ключей; Вызов из основного меню
	{
		$id_server=$this->GetIdServer($id_dev);
		Kohana::$log->add(Log::INFO, 'Start getStatusIdDev '.$id_dev.':'.$id_server);
		$time_start=microtime(true);
		$res='';
		$res_out=__('readkey_title').'<br>'.__('command_time_start').date('d.m.Y H:i:s'); //Строка для записи в журнал
		
		
		//получаем IP адрес и порт транспортного сервера, где работает нужный контроллер.
		$sql='select ip, port from server  where id_server='.$id_server;
			$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
		foreach ($query as $key=>$value){
			$ip=Model::Factory('Stat')->IntToIP(Arr::get($value, 'IP', 'no'));
			$port=Arr::get($value, 'PORT', 'no');
		}

		//готовлю файл для записи вычитанных данных.		
		$file_name="check_readkey_".$id_dev.'_'.date('Y-m-d_H_i_s').".csv";
		$file_name_delete="delete_key_".$id_dev.'_'.date('Y-m-d_H_i_s').".csv";
		
		$fp = fopen($file_name, "w"); // Открываем файл в режиме записи	
		$fp_delete = fopen($file_name_delete, "w"); // Открываем файл для удаления
		
		$mytext ="cell;door;key_from_device;TZ;status; read_count\r\n"; // строка заголовок
		$test = fwrite($fp, $mytext); // Запись в файл
		set_time_limit(12000);//установка общее время выполения
		
		//получаею диапазон чтения ячеек. Если он не указан, то читаю первые 10 ячеек.
		$i_start=0;
		
		
		$sql='select count(distinct c.id_card) from ss_accessuser ssu
        join card c on ssu.id_pep=c.id_pep
        join access ac on ssu.id_accessname=ac.id_accessname
        where
        c."ACTIVE">0
        and (c.timeend>\'NOW\' or c.timeend is null)
		 and c.id_cardtype in (1,2)
		 and ac.id_dev='.$id_dev.'
        group by ac.id_dev';
		$i_end = DB::query(Database::SELECT, $sql)
						->execute(Database::instance('fb'))
			->get('COUNT');
		
		//$i_end= Arr::get($_SESSION, 'cellto', 3000);
		
		$i=$i_start;
		$i_max=$i_end+500;
		//$i_max=30000;
		
		//определение номера канала
		//$sql='select d.id_reader from device d where d.id_dev='.$id_dev;
		//$id_reader = DB::query(Database::SELECT, $sql)
		//				->execute(Database::instance('fb'))
		//	->get('ID_READER');
			
			
		//определение номера канала
		$sql='select d.id_reader from device d where d.id_dev='.$id_dev;
		$id_reader = DB::query(Database::SELECT, $sql)
						->execute(Database::instance('fb'))
			->get('ID_READER');
			
			
			
		$time_start_local=microtime(true);
	
		$device_name=$this->GetNameController($id_dev);

		// открываю соединение с транспортным сервером
		$conn = stream_socket_client("tcp://".$ip.':'.$port.'"', $errno, $errstr);
		Kohana::$log->add(Log::INFO, 'Open socket '.$ip.':'.$port); 
		if (!$conn) {
		 echo "$errstr ($errno)<br />\n";
		} else {
				//Авторизацияю Строка выдается для любого типа ТС
				$auth_string='r55 login name="3", password="3"';
				fwrite($conn, $auth_string."\r\n");
				$res1=fread($conn, 4096);
				Kohana::$log->add(Log::INFO, 'TS answer authorisation '.$res1);
				//Начало цикла опроса . Цикл выполняется по количеству карт в точке прохода	
				$count_delete=0;
				while ($i <= $i_max) 
				{
					$command='readkey door='.$id_reader.', cell='.$i;
					$smes_command = 'r77 exec device="'.$device_name.'", command="'.$command.'"';
					$res_command='ERR';
					$i_count=0;
					fwrite($conn, $smes_command."\r\n");
					while ($res_command == 'ERR' and $i_count<2)// до 10 попыток чтения данных из ячейки или получения сообщения об ошибке в ответ на команду
					{
						$temp=fread($conn, 4096);//прочитал ответ
						//Kohana::$log->add(Log::INFO, 'Get answer '.$temp);
						$etalon='ERR';
						$pos=stripos(strtoupper($temp), $etalon);
						if(stripos(strtoupper($temp), $etalon)) HTTP::redirect('errorpage?err=Amswer error '.str_replace("\r\n","", $temp));
						//ищу положительный ответ на команду
						
						$etalon='r77 OK';
						$pos=stripos($temp, $etalon);
						if($pos !== false) $res_command='OK';
						$i_count++;
					}
					
					//Просмотр ответа от контроллера
					//echo Debug::vars('Device answer', $temp); exit;
					
					
					//Формирование ответа в виде массива
					
					$temp=str_replace("R77 OK ANSWER=\"OK ","", strtoupper($temp));
					$temp=str_replace(" ","", $temp);
					$temp=str_replace("\r\n","", $temp);
					$temp=str_replace('"','', $temp);
					$key2=explode(",",$temp);
					
					//echo Debug::vars('Device answer', $key2); exit;
					
					$res=array();
					//преобразование в массив вида
					//для Артонита:
					//	"CELL" => string(1) "0"
					//	"KEY" => string(10) "000380001A"
					//	"ACCESS" => string(3) "YES"
					//	"TZ" => string(6) "0X0001"	
					// для Адеманта:
					// 	"KEY" => string(10) "0037E1001A"
					//	"TZ" => string(6) "0X0001"
					//	"STATUS" => string(4) "0X00"
					
					foreach($key2 as $key=>$value)
					{
						$aaa=explode("=", $value);
						$res[$aaa[0]]=$aaa[1];
												
					}
					//echo Debug::vars('Device answer', $key2, $res, array_key_exists('ACCESS', $res)); exit;
					
					$key_prop=$this->getKeyProperty(Arr::get($res, 'KEY', 'no'), $id_dev);
					
		
					//заполнение файла данными из массива $key2
					$mytext =$i.';'
						.$id_reader.';'
						.Arr::get($res, 'KEY', 'no1').';'
						.Arr::get($res, 'TZ', 'no2').';'
						.Arr::get($res, 'ACCESS', 'no_access').';'
						.Arr::get($key_prop, 'ID_CARD', 'no_id_card').';'
						.Arr::get($key_prop, 'LOAD_RESULT', 'no_load_result').';'
						.Arr::get($key_prop, 'LOAD_TIME', 'no_load_time').';'
						."\r\n"; // строка данных
					
					
					
					// Удаляем лишние карты после проверки, что карта не стоит в очереди на загрузку
					
					if( (Arr::get($res, 'KEY') != '0000000000')  and  (Arr::get($key_prop, 'LOAD_RESULT') === NULL)  and (Arr::get($key_prop, 'ID_CARDINDEV') === NULL) ) {
					
					if(array_key_exists('ACCESS', $res))// параметр access есть в ответе только у Артонита
					{
						//если это Артонит
						$command='deletekey door='.$id_reader.', key=""'.Arr::get($res, 'KEY').'""';
					} else {
						// если это Адемант
						$command='writekey door='.$id_reader.', key=""0000000000"", cell='.$i;
						}
					
					$smes_command = 'r44 exec device="'.$device_name.'", command="'.$command.'"';
					//подготовка данных для записи в файл удаления
					$list_for_delete =$i.';'
						.$id_reader.';'
						.Arr::get($res, 'KEY', 'no1').';'
						.Arr::get($res, 'TZ', 'no2').';'
						.Arr::get($res, 'ACCESS', 'no_access').';'
						.Arr::get($key_prop, 'ID_CARD', 'no_id_card').';'
						.Arr::get($key_prop, 'LOAD_RESULT', 'no_load_result').';'
						.Arr::get($key_prop, 'LOAD_TIME', 'no_load_time').';'
						.Arr::get($key_prop, 'ID_CARDINDEV', 'no_id_cardindev').';'
						.$smes_command.';'
						."\r\n"; // строка данных
				//echo Debug::vars('266', $smes_command, $list_for_delete, $res); exit;
				if( (Arr::get($res, 'ACCESS') == 'YES')){// особенность Артонитов. Карта в канале записана, но проход запрещен. В этом случае удалять карту бессмысленно.
						
					fwrite($fp_delete, $list_for_delete); // Запись в файл	
						//Команды на удаление лишних карт
						
						Kohana::$log->add(Log::INFO, 'Команда на удаление карты='.$smes_command);
						Kohana::$log->add(Log::INFO, 'Count delete='.$count_delete);
						fwrite($conn, $smes_command."\r\n");
						$ans12=fread($conn, 4096);	
						Kohana::$log->add(Log::INFO, 'Ответ на команду на удаление карты='.$ans12);	
						$count_delete++;
						}
					}
					
					$test = fwrite($fp, $mytext); // Запись в файл
					
				$i++;
				}
			}
	//закрываю соединение с траснпортным сервером
	fclose($conn);
			fclose($fp); //Закрытие файла
			fclose($fp_delete); //Закрытие файла

			$res_out=$res_out.'<br>'.__('check_key_result', array(':device_name' => iconv('windows-1251','UTF-8',$device_name), 
			':cellfrom' => $i_start,
			':cellto' => $i_max,
			':file_name'=>iconv('windows-1251','UTF-8',$file_name),  
			':during'=> round(microtime(true) - $time_start_local, 2),
			':count_del'=>$count_delete,
			));
				
		
		$res_out=$res_out.'<br>'.__('command_time_end').date('d.m.Y H:i:s');
			
		Model::Factory('Device')->last_command($res_out);
		$time_execute=microtime(true) - $time_start;
		Kohana::$log->add(Log::INFO, 'Stop getStatusIdDev '.$id_dev.':'.$id_server.', time execute='.$time_execute.'.');
		
		
		//записываю новое состояние контроллера в статистику
		Kohana::$log->add(Log::INFO, 'get stat and insert to DB for device='.$id_dev);
		//Model::Factory('Device')->insertStatusIdDev($id_dev);
		return $res;
		
		
	}
	
	public function readKeyFromDevice ($id_dev, $id_server) // чтение ключей из устройства
	{
		echo Debug::vars('114',  $id_dev, $id_server, $_SESSION); exit;
		$time_start=microtime(true);
		$res='';
		$res_out=__('readkey_title').'<br>'.__('command_time_start').date('d.m.Y H:i:s');
		
		
		//получаем IP адрес и порт транспортного сервера, где работает нужный контроллер.
		$sql='select ip, port from server  where id_server='.$id_server;
			$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
		foreach ($query as $key=>$value){
			$ip=Model::Factory('Stat')->IntToIP(Arr::get($value, 'IP', 'no'));
			$port=Arr::get($value, 'PORT', 'no');
		}

		//готовлю файл для записи вычитанных данных.		
		$file_name="check_readkey_".$_SESSION['device_list'][$id_dev].'_'.date('Y-m-d_H_i_s').".csv";
		$fp = fopen($file_name, "w"); // Открываем файл в режиме записи	
		$mytext ="cell;door;key_from_device;TZ;status; read_count\r\n"; // строка данных
		$test = fwrite($fp, $mytext); // Запись в файл
		set_time_limit(12000);
		
		//получаею диспазон чтения ячеек. Если он не указан, то читаю первые 10 ячеек.
		$i_start= (Arr::get($_SESSION, 'cellfrom') == '')? 0 : Arr::get($_SESSION, 'cellfrom') ;
		$i_end= Arr::get($_SESSION, 'cellto', 10);
		
		$i=$i_start;
		$i_max=$i_end;
		$id_reader=Arr::get($_SESSION, 'door', 0);
		$time_start_local=microtime(true);
		// открываю соединение с транспортным сервером
		
				while ($i <= $i_max) 
				{
					$command='readkey door='.$id_reader.', cell='.$i;
					$res_command='ERR';
					$i_count=0;
					while ($res_command == 'ERR' and $i_count<10)// до 10 попыток чтения данных из ячейки
					{
						$res1=trim($this->sendCommand($ip, $port, $id_dev, $command));
						$temp=Model::Factory('Stat')->parser_1($res1);// ответ на команду
						
						// проверка результата ответ
						$res_command=substr($temp, 0, stripos($temp, " "));// выборка результат выполнения команды: OK или ERR
						$i_count++;
					}
					$temp=str_replace("OK ","", $temp);
					$temp=str_replace(" ","", $temp);
					$key2=explode(",",$temp);
					$res=array();
					foreach($key2 as $key=>$value)
					{
						$aaa=explode("=", $value);
						$res[$aaa[0]]=$aaa[1];
						
					
					}
					//echo De
					$key_prop=$this->getKeyProperty(Arr::get($res, 'Key', 'no'), 308);//Это для работы с Адемантами
		
										
					//заполнение файла данными из массива $key2
					$mytext =$i.';'
						.$id_reader.';'
						.Arr::get($res, 'Key', 'no').';'//это тольки при работе с Адемантами
						.Arr::get($res, 'TZ', 'no').';'
						.Arr::get($key_prop, 'LOAD_RESULT', 'no').';'
						.Arr::get($key_prop, 'LOAD_TIME', 'no').';'
						."\r\n"; // строка данных
					$test = fwrite($fp, $mytext); // Запись в файл
				$i++;
				}
	//закрываю соединение с траснпортным сервером
			fclose($fp); //Закрытие файла
			$res_out=$res_out.'<br>'.__('check_key_result', array(':device_name' => $_SESSION['device_list'][$id_dev], ':cellfrom' => $i_start,':cellto' => $i_end,':file_name'=>iconv('windows-1251','UTF-8',$file_name),  ':during'=> round(microtime(true) - $time_start_local, 2)));
				
		
		$res_out=$res_out.'<br>'.__('command_time_end').date('d.m.Y H:i:s');
			
		Model::Factory('Device')->last_command($res_out);
		
		return $res;
		
		
	}
	
	public function getKeyProperty($key, $id_dev)
	{
		$sql='select c.id_card,cd.id_dev, cd.load_time, cd.load_result, cd.id_cardindev from card c
		left join  cardidx cd on cd.id_card=c.id_card and cd.id_dev='.$id_dev.'
		where c.id_card=\''.$key.'\' ';
		//echo Debug::vars('154', $sql); //exit;
		$res=array();
		$query = DB::query(Database::SELECT, $sql)
		->execute(Database::instance('fb'))
		->as_array();
		if($query) $res=$query[0];
		//echo Debug::vars('154', $sql, $query, $res); exit;
		return $res;
	
	
	}
	
	public function writeKeyToDevice ($id_dev, $id_server)
	{
		//$device_name=Kohana::$config->load('artonitcity_config')->name_device_fro_test;
		$time_start=microtime(true);
		$res='';
		$res_out=__('writekey_title').'<br>'.__('command_time_start').date('d.m.Y H:i:s');
		
		$sql='select ip, port from server  where id_server='.$id_server;
			$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
		foreach ($query as $key=>$value){
			$ip=Model::Factory('Stat')->IntToIP(Arr::get($value, 'IP', 'no'));
			$port=Arr::get($value, 'PORT', 'no');
		}
				
		$file_name="writekey_".$_SESSION['device_list'][$id_dev].'_'.date('Y-m-d_H_i_s').".csv";
		$fp = fopen($file_name, "w"); // Открываем файл в режиме записи	
		$mytext ="cell;door;key;result;read_count\r\n"; // строка данных
		$test = fwrite($fp, $mytext); // Запись в файл
		set_time_limit(12000);
		
		$i_start= (Arr::get($_SESSION, 'cellfrom_write') == '')? 0 : Arr::get($_SESSION, 'cellfrom') ;
		$i_end= Arr::get($_SESSION, 'cellto_write', 10);
		
		$i=$i_start;
		$i_max=$i_end;
		$id_reader=Arr::get($_SESSION, 'door', 0);
		//echo Debug::vars('172', $i, $i_max); exit;
		$time_start_local=microtime(true);
				while ($i <= $i_max) 
				{
					$key=$this->getKeyCode($i);
					$command='writekey door='.$id_reader.', cell='.$i.', key=""'.$key.'"", tz=0x1, status=0';
					$res_command='ERR';
					$i_count=0;
					while ($res_command == 'ERR' and $i_count<10)// до 10 попыток чтения данных из ячейки
					{
						$res1=trim($this->sendCommand($ip, $port, $id_dev, $command, 1));
						$temp=Model::Factory('Stat')->parser_1($res1);// ответ на команду
						//-echo Debug::vars('181', $res1, $temp);
						// проверка результата ответ
						$res_command=substr($temp, 0, stripos($temp, " "));// выборка результат выполнения команды: OK или ERR
						$i_count++;
					}
					//$key=str_replace(" ",'',Model::Factory('Stat')->parser_2($res1));
					$mytext =$i.';'.$id_reader.';'.$key.';'.$temp.';'.$i_count."\r\n"; // строка данных
					
					$test = fwrite($fp, $mytext); // Запись в файл
				$i++;
				}

			fclose($fp); //Закрытие файла
			$res_out=$res_out.'<br>'.__('write_key_result', array(':device_name' => $_SESSION['device_list'][$id_dev], ':cellfrom' => $i_start,':cellto' => $i_end,':file_name'=>iconv('windows-1251','UTF-8',$file_name),  ':during'=> round(microtime(true) - $time_start_local, 2)));
				
		
		$res_out=$res_out.'<br>'.__('command_time_end').date('d.m.Y H:i:s');
			
		Model::Factory('Device')->last_command($res_out);
		
		return $res;
	}
	
	public function getKeyCode($count)
	{
		if(is_numeric($count) and $count>=0)
		{
			if($count < 10) $res='00000'.$count.'001A';
			if($count >= 10 and $count< 100) $res='0000'.$count.'001A';
			if($count >= 100 and $count< 1000) $res='000'.$count.'001A';
			if($count >= 1000 and $count< 10000) $res='00'.$count.'001A';
			if($count >= 10000 and $count< 100000) $res='0'.$count.'001A';
			if($count >= 10000) $res=$count.'A5A5A5001A';
		
		} else {
			$res='112233001A';
		}
	return $res;
	}
}
