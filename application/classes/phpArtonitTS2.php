<?php



/** 28.08.2024 Драйвер для работы с контроллерам Артонит через ТС2 сервер

*/

class phpArtonitTS2
{
    protected $socket;            /* holds the socket	*/
    public $address;            /* broker address */
    public $port;                /* broker port */
    public $connection = false;  /* состояние коннекта к выбранному устройству */
    public $result;            /* результат выполнения команды */
    public $answer;            /* ответ на команду (если он должен быть) */
    public $command;            /* команда для выполнения */
    public $commandParam;            /* параметры команды */
    public $coordinate;            /* координаты вывода строки на табло */
    public $binCommand;            /* команда для выполнения */
    public $codeCommand;            /* код команды контроллера */
    public $dev_name = 'VP3 K3\1';            // название контроллера
    public $ts2client;            // клиент для связи с тс2
    public $errDesc;            // описание причины ошибки
	
	
	
    public $_lenCommand=3;            /*Длина команды вместе с параметрами*/
   public $t1;//start
  
    public function __construct($address=null, $port=null)
    {
        
    }


   /** Подготовка подключения к контроллеру через ТС2.
   *28.08.2024
   
   */
    public function connect($ip = null, $port=null)
    {
     
	$this->ts2client=new TS2client();
		
	
		$this->ts2client->startServer();
		
		
		if($this->ts2client->connReady){
			
			$aaa=$this->sendCommand('getversion');
			$sser='www.artonit.ru';
		
				if(strpos($aaa, $sser))
			{
				$this->connection=true;
				
			} else {
				$this->connection=false;
				$this->errDesc='Устройство не найдено либо отключено';
				
			};
		} else {
			
			
			$this->connection=false;// если нет соединение с ТС2, то false
			$this->errDesc='Транспортный сервер не ответил на запрос на подключение.';
			
		}

		
    }

   

    /**
     * Sends a proper disconnect, then closes the socket
     */
    public function close()// при завершении работы возможно придется что-то куда-то выводить, поэтому перед вызовом disconnect можно сделать еще что-нибудь
    {
        $this->disconnect();
        
    }

	
	
	public function disconnect()
    {
       $this->ts2client->stopClient();
    }
	

	
	
	/*
	10.09.2023
		вспомогательная программа для организации цикла обмена с целью полуить именно ответ, отфильтровать от событий
		$dev_name - имя устройства
		$command - подготовленная команда
		$connect - подготовленное соединение с устройством
		$attempy - количество попыток чтения до получения нужного ответа
		
	*/
	public function sendCommand ($command, $attempt=10){
		
		$pid_send='t45';
		$message=$pid_send.' exec device="'.$this->dev_name.'", command="'.$command.'"';
		//Log::instance()->add(Log::NOTICE, $message);
		
		$this->ts2client->sendMessage($message);
		for ($i=0; $i<$attempt; $i++)
		{
			$ttt=$this->ts2client->readMessage();
			
			$pos_pid=strpos($ttt, ' ');
			$pId = substr($ttt,0, $pos_pid);//pid ответа
			//Log::instance()->add(Log::NOTICE, '286 XXX pid='.$pId.', attempt='.$i);
			if($pId==$pid_send)
			{
				$pos_result=strpos($ttt, ' ', $pos_pid);//результат выполенния команды
				$result = substr($ttt,$pos_pid+1, $pos_result);//ответ на команду для драйвера
				
				$devAnswer = substr($ttt, $pos_pid + $pos_result+1);
				//Log::instance()->add(Log::NOTICE, '293 XXX return='.$ttt.', attempt='.$i);
				return $ttt;//если pid отправленного и полученного сообщения совпали, то передаю его наружу. Иначе - вычитываю ответы еще раз
			}
			
		}
		
		
	}
	

   
 


   
   
}
