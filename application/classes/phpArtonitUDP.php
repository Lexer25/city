<?php



/** 15.08.2024 Драйвер UDP для работы с контроллерам Артонит по UDP

*/

class phpArtonitUDP
{
    protected $socket;            /* holds the socket	*/
    public $address;            /* broker address */
    public $port;                /* broker port */
    private $connection;            /* stores connection */
    public $result;            /* результат выполнения команды */
    public $answer;            /* ответ на команду (если он должен быть) */
    public $command;            /* команда для выполнения */
    public $commandParam;            /* параметры команды */
    public $coordinate;            /* координаты вывода строки на табло */
    public $binCommand;            /* команда для выполнения */
    public $codeCommand;            /* код команды контроллера */
    public $udp_delay = 500000;            /* задержка при получении ответа UDP */
	
	
	
    public $_lenCommand=3;            /*Длина команды вместе с параметрами*/
   public $t1;//start
   public $timelist=array();//массив с временными метками разных событий

    

 
    public function __construct($address, $port)
    {
        $this->t1=microtime(true);
        $this->timelist['start']=microtime(true)-$this->t1;
		$this->address = $address;
        $this->port = $port;
		$this->udp_delay=50000;
    }


   
    public function connect()
    {
     //открываю сокет UDP 
	
	 $this->timelist['connect_start']=microtime(true)-$this->t1;
	  $this->socket= @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
	  socket_set_option($this->socket,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>1, "usec"=>0));
		if (false === $this->socket) { 
			Log::instance()->add(Log::NOTICE, "4 Socket error!!!");
			exit;
			} else {
				
				//Log::instance()->add(Log::NOTICE, "48 Socket создан успешно"); 	
			};
		
	
		// создаем соединение 
		$this->connection = @socket_connect($this->socket, $this->address, $this->port);
		
		if ($this->connection === false)      
		{
			Log::instance()->add(Log::NOTICE, "55 Cannot connect to server".$this->address.":". $this->port);
			exit;
		} else {
			//Log::instance()->add(Log::NOTICE, "60 OK connect to server".$this->address.":". $this->port);
			
		}
   
$this->timelist['connect_end']=microtime(true)-$this->t1;
        return true;
    }

   

    /**
     * Sends a proper disconnect, then closes the socket
     */
    public function close()// при завершении работы возможно придется что-то куда-то выводить, поэтому перед вызовом disconnect можно сделать еще что-нибудь
    {
        $this->disconnect();
        
    }

	/**
     * отправка пакета данных в контроллера
    
     */
	
	
    public function sendCommand($command)
    {
        //echo Debug::vars('69',unpack('C*',$this->binCommand),  unpack('C*', $command));
		//$this->timelist['sendCommand_start']=microtime(true)-$this->t1;
		$replay='';
		if($this->connection === true)
		{
			try
			{
				$byte_send=socket_write($this->socket, $command, strlen($command));
				//Log::instance()->add(Log::NOTICE, '82-phpMPT-UDP-sendCommand '.$this->address.':'.$this->port.' timestamp '.microtime(true).' отправлен UDP пакет '.implode (",", unpack("C*",$command)).', ответ '.$byte_send);
				// ждать 500 миллисекунды
				$delay=0;
				while($delay<5){
					
					usleep($this->udp_delay);
					$reply = socket_read($this->socket,4096);
					 //$this->timelist['replay'][]=$replay;
					if($replay===false or is_null($replay)) {
						++$delay;
						echo Debug::vars('112', $replay);
					} //если ответа нет, то увеличаю знаение условия
					//if($replay=='') $delay=1000001;// пустая строка - значит, принимать нечего. надо отключаться.
						elseif(strlen($replay)>1) {
							//$delay=100; //данные получены, можно выходить.
							echo Debug::vars('117', $replay);
						}
						
					$delay=100;	
				}
				//Log::instance()->add(Log::NOTICE, '86-phpMPT-UDP-recievCommand '.$this->address.':'.$this->port.' timestamp '.microtime(true).' получен ответ  '.implode (",", unpack("C*",$reply)));
			
				} catch  (Exception $e) {
					//Log::instance()->add(Log::NOTICE, '91-MPT-checkAnswer '.Debug::vars('180', $e).' '.Debug::vars('92', iconv('windows-1251','UTF-8', $e->getMessage())));
					//Log::instance()->add(Log::NOTICE, '91-MPT-checkAnswer-exception timestamp '.microtime(true).Debug::vars('92', iconv('windows-1251','UTF-8', $e->getMessage())));
					$this->result='Err';
					$this->edesc=$e->getMessage();
					//echo Debug::vars('129', '91-MPT-checkAnswer-exception timestamp '.microtime(true).Debug::vars('92', iconv('windows-1251','UTF-8', $e->getMessage())));exit;
					return;	
				}
			
		} else {
				$reply ='No connection';
				
			}
			//echo Debug::vars('137');exit;
			return $reply;
    }
	
	public function disconnect()
    {
        socket_close($this->socket);
		//Log::instance()->add(Log::NOTICE, "121 Socket закрыт");
    }
	
	/*
	расчет контрольной суммы по строке/массиву
	
	
	*/
	
	
	public function bcc($data, $from, $to)
	{
		
		$bcc=pack('C', 0);
		//echo Debug::vars('98 start bcc',  unpack("C*",$data), $from, $to, unpack("C*",$bcc));
		for($i=$from; $i<$to-1; $i++)
		{
			//echo Debug::vars('97', $i, unpack("C",$bcc), unpack("C",$data[$i]));
			$bcc=$bcc^$data[$i];
		}
		//echo Debug::vars('105', unpack("C",$bcc));
		return $bcc;
	}

	/*
			функция из текстовых команд формирует бинарный набор (включая в себя длину команды и BCC)
		
		$command - текст команды	
		$this->commandParam - дополнительные параметры команды 
		$this->coordinate - дополнительные параметры команды	
		
		запрос чтение джамперов
		02030001453b тут
		02 длина
		03 чтение
		00 группа
		01 подгруппа
		453b CRC
		
		ответ
		05
		83
		00 код ошибки 0 - выполнено успешно
		0001
		01 битовая маска джамперов
		00 режим работы
		0836 crc
		
		
		запрос версии
		02030000cc2a тут
		02 длина
		03 чтение
		00 группа
		00 подгруппа
		cc2a crc
		ответ
		2b830000007777772e6172746f6e69742e72750000000000004d61792032372032303234000000000000000000dbf4 тут
		2b
		83
		00 код ошибки 00 - выполнено успешно
		00
		00
		7777772e6172746f6e69742e72750000000000004d61792032372032303234000000000000000000
		dbf4
		
	
	*/
	public function make_binary_command($command)
	{
		
		$known_commands = array(
        'GetVersion'=>"\x03\x00\x00", 
        'GetJmp'=>"\x03\x00\x01", 
        'GetMAC'=>"\x03\x01\x00", 
        'GetIO'=>"\x21", 
        'GetKeyCount'=>"\x1b", 
        'GetAP0'=>"\x1f\x00\xff\x01\x02\x03\x04", 
        'GetAP1'=>"\x1f\x01\xff\x01\x02\x03\x04", 
		
		);
		//$lenCommad=pack('h', strlen(Arr::get($known_commands, $command)) - 1);
		
		$this->_lenCommand=pack('h', strlen(Arr::get($known_commands, $command)) -1) ; //сохраняю длину команды для последующего правильного извлечения данных
		//echo Debug::vars('208', $command, $lenCommad, $this->_lenCommand); exit;
		$this->codeCommand=Arr::get(unpack('c*', (Arr::get($known_commands, $command))), 1);//запоминаю команду для последующего сравнения
		
		$ttr=Arr::get($known_commands, $this->command);// сборка команды с параметрами, без длины и BCC
		//$lenCommad=pack('c', strlen($ttr)-1);// длина команды в формате binary
		//$crc=$this->crc16($lenCommad.$ttr, 0, strlen($ttr)+2); // получение bcc по всей команде
		$crc=$this->crc16($this->_lenCommand.$ttr, 0, strlen($ttr)+2); // получение crc по всей команде
	
		//return $lenCommad.$ttr.pack('S', $crc);
		return $this->_lenCommand.$ttr.pack('S', $crc);

	}
	
	/*
	Отправка подготовленного UDP пакета
	*/
	
	public function execute()// выполнение команды $this->command  
	{
		$this->answer='';
		$this->connect();
		$_command=$this->make_binary_command($this->command);
		$_answer=$this->sendCommand($_command);
		//Log::instance()->add(Log::NOTICE, '161-phpMPT-execute timestamp '.microtime(true).' отправлен UDP пакет '.implode (",", unpack("C*",$_command)));
		//Log::instance()->add(Log::NOTICE, '163-phpMPT-execute '.microtime(true).' получен ответ  '.implode (",", unpack("C*",$_answer)));
		$this->checkAnswer($_answer);//заполняют свойства result и answer
		$this->close();
		
		return;
		
	}
	
	/**18.08.2024 Разбор ответа
	*@ $data - строка ответа, полученная от контроллера
	*/
	
	public function checkAnswer($data)
	{
		//echo Debug::vars('173-MPT ответ на команду', unpack("C*",$data)); 
		$this->timelist['checkAnswer_start']=microtime(true)-$this->t1;
		$_lenData=strlen($data);//длина полученного ответа
		//$len_duty=strlen(Arr::get($known_commands, $artonit->command));// длина набора с командой
		$len_duty=4;// длина набора с командой
		$_lenDEC=Arr::get(unpack('c*', $data), 1);//длина ответа в заголовке
		$_commandRepeatDEC=Arr::get(unpack('c*', $data), 3);
		//$_resultDEC=Arr::get(unpack('c*', $data), strlen($this->command) );//результат выполнения смотрю байт 2 слева (Начиная с нуля)
		$_resultDEC=Arr::get(unpack('c*', $data), 3 );//результат выполнения смотрю байт 2 слева (Начиная с нуля)

		//Log::instance()->add(Log::NOTICE, '180-MPT-checkAnswer '.Debug::vars('180',unpack("C*",$data), implode (",", unpack("C*",$data)), '_lenData='.$_lenData, '_lenDEC='.$_lenDEC, '_commandRepeatDEC='.$_commandRepeatDEC, '_resultDEC='.$_resultDEC));
		
		
		if($_lenData==0)// нет ответа из сокета
		{
			$this->result='Err_254';
			$this->edesc='Ответ не получен или длина пакета 0';
		}
		elseif ($_lenDEC<3) // ответ не может быть меньше 3 байт.
			{
				$this->result='Err_259';
				$this->edesc='Длина пакета меньше 3 байт.';
				
			}
		
		elseif($_lenData>255)// проверка длины пакета
		{
			$this->result='Err_266';
			$this->edesc='Packet UDP more then 255 byte';
		} elseif ($_lenDEC<>$_lenData-$len_duty) // проверка совпадения заявленной и реальной длины пакета.
			{
				$this->result='Err_270';
				$this->edesc='Длина пакета '.$_lenData.' не совпадает с указанной длиной '.$_lenDEC;
				echo Debug::vars('274', $_lenDEC, $_lenData); exit;
				
			} /* elseif (Arr::get(unpack('C',$this->bcc($data, 0, $_lenDEC)), 1) <>$_bccDEC)// проверка контрольной суммы пакета.
				{
					$this->result='Err_275';
					$this->edesc='Неправильная контрольная сумма ответа. В ответе BCC='.$_bccDEC.', рассчитанная BCC='.Arr::get(unpack('C',$this->bcc($data, 0, $_lenDEC)), 1);
					
				}  */
					elseif ($this->command=="\x03" and $_resultDEC<>0)// проверка успешно ли выполнена команда
					{
						
						$err_mess=array 
						('1'=>'Ошибка контрольной суммы, команда не выполнена',
						'2'=>'Ошибка длины, команда не выполнена',
						'3'=>'Ошибка, неизвестная команда',
						'4'=>'Ошибка подключенного оборудования'
						);

						
						$this->result='Err_289';
						$this->edesc='Команда выполнена с ошибкой '.$_resultDEC.' ('.Arr::get($err_mess, $_resultDEC).')';
						
						
					} 
					elseif (!$_commandRepeatDEC<>$this->codeCommand and false)
						{
							$this->result='Err_296';
							$this->edesc='Получен ответ '.$_commandRepeatDEC.' на команду'. $this->codeCommand;
							
							
						} else 
						{
							$this->result='OK';
							$this->edesc='OK';
							
							// тут начинаю подготовку ответов в зависимости от типа запроса
							//удаляю из ответа заголовок и CRC
						
							$_start = $this->_lenCommand + 2;//с этого места начинается тело отчета
							$_len = strlen($data)- $this->_lenCommand -4;//длина ответа
							$_data=substr($data, $_start, $_len);
							
							switch($this->command){

								case ('GetVersion'):
									$this->answer=$_data;
								
								break;
								
								case ('GetMAC'):
									$_res='';
									$_res=unpack("H*", $_data);
									
									$var2=str_split(Arr::get($_res, 1), 2);
									$this->answer=strtoupper(implode("-", $var2));
								
								break;
								
								case ('GetJmp'):
									$_res=unpack("H*", $_data);
									$var2=str_split(Arr::get($_res, 1), 2);
									$jmp=array(
										 'isWp' => (Arr::get($var2, 0) & 1)? true : false,
										 'isTest' => (Arr::get($var2, 0) & 2)? true : false,
										
									);
									//echo Debug::vars('335', $var2, $jmp);
								
								 $this->answer=$jmp;	
								
								break;
								case ('GetIO'):
									$_res=unpack("H*", $_data);
									//echo Debug::vars('351', $_res);//exit;
									$var2=(Arr::get($_res, 1));
									echo Debug::vars('353',$var2);//exit;
									$result=array(
										 'input' => substr($var2, 0, 18),
										 'output' => substr($var2, 20),
										
									);
									//echo Debug::vars('335', $var2, $jmp);
								
								 $this->answer=$result;	
								
								break;
								
								case ('GetAP0'):// получить состояние точки прохода 0
									$_res=unpack("H*", $_data);
									
								 $this->answer=Arr::get($_res, 1);	
								
								break;
								
								case ('GetAP1'):// получить состояние точки прохода 0
									$_res=unpack("H*", $_data);
									
								 $this->answer=Arr::get($_res, 1);	
								
								break;
								
								case ('GetKeyCount'):
									$_res=unpack("H*", $_data);
									
									$var2=(Arr::get($_res, 1));
									$var_1=str_split ($var2,2);
									$var_2=implode(array_reverse($var_1));
									//echo Debug::vars('382',$_res, $var_1, implode(array_reverse($var_1)));exit;
									$result=array(
										'0' => hexdec(substr($var_2, 4)),
										'1' => hexdec(substr($var_2, 0, 4)),
									);
									//echo Debug::vars('335', $result);
								
								 $this->answer=$result;	
								
								break;
								
								
								
								
							}
						}	
		$this->timelist['checkAnswer_end']=microtime(true)-$this->t1;
		return;
		
	}
	
	public function sendtext($mess, $param)//вывод сообщения на табло
	{
		$this->command='opendoor door=0';
		$this->execute();
		return;
	}
   
   //18.08.2024 https://stackoverflow.com/questions/53812834/computing-crc16-with-reflected-bit-reversed-input-in-c

	public function crc16($data) {

	$Poly = 0x1021;
    $Init = 0xFFFF;
    $Xor = 0xFFFF;
    $Refin = True;
    $Refout = True;
	
        $crc = $Init;
        $len = strlen($data);
        $i = 0;
        while ($len--) {
            $crc ^= $this->reversebyte(ord($data[$i++])) << 8;
            $crc &= 0xffff;
            for ($j = 0; $j < 8; $j++){
                $crc = ($crc & 0x8000) ? ($crc << 1) ^ $Poly : $crc << 1;
                $crc &= 0xffff;
            }
        }
        $crc ^= $Xor;
        $crc = $this->reversebits($crc);
        return $crc;
    }

    public function reversebyte($byte) {
        $ob = 0;
        $b = (1 << 7);
        for ($i = 0; $i <= 7; $i++) {
            if (($byte & $b) !== 0) {
                $ob |= (1 << $i);
            }
            $b >>= 1;
        }
        return $ob;
    }

    public function reversebits($cc) {
        $ob = 0;
        $b = (1 << 15);
        for ($i = 0; $i <= 15; $i++) {
            if (($cc & $b) !== 0) {
                $ob |= (1 << $i);
            }
            $b >>= 1;
        }
        return $ob;
    }



   
   
}
