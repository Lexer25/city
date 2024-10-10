<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
*16.06.2024 
*Класс artonitHTTP - работа с конроллером СКУД Артонит СКУД по протоколу HTTP
*Класс сделан для получения необходимых данных непосредственно с контроллера путем чтения экранных форм (и выделения необходимых данных) и выполнение настроек (по HTTP протоколу).
Для работы необходимо указать IP адрес
*/
class artonitHTTP
{
	
	public $softVersion;// версия прошивки
	public $httpPort=80;
	public $isOnline=false;// проверка наличия контроллера на связи
	public $isWp=false;// чтение перемычки WP. TRUE - перемычка установлена, False - перемычка снята
	public $isTest=false;// чтение перемычки TEST. TRUE - перемычка установлена, False - перемычка снята
	public $doorMode=array('no', 'no');// режим работы точек прохода
	public $doorState=array('no', 'no');// состояние входов (маска входов)
	public $ip_address='no';// IP адрес
	public $mac_address='00-00-00-00-00-00';// mac адрес
	public $portStateInput=array();// массив состояния портов
	public $keyCount=array();// количество карт по каналам
	public $scud='d0';// режим работы d2 - две независимые двери, d1 - одна дверь на два считывателя
	
	private $leftMenuPage='1.htm';//страница левого меню веб-панели контроллера
	private $netParamPage='B.htm';//страница сетевых параметров на веб-панели контроллера
	private $doorStatePage='8.xml';//состояние точек прохода. Тут сразу же определяется и режим работы (см. ниже), и состояние точки прохода, если дверь находится в рабочем режиме
	private $softVersionPage='A.htm';//состояние точек прохода. Тут сразу же определяется и режим работы (см. ниже), и состояние точки прохода, если дверь находится в рабочем режиме
	private $scudModePage='F.htm';//режим работы контроллера две двери или одна дверь на два считывателя
	/*
	* Проверка состояние точек прохода:
* - рабочее состояния open closed
* - открыта навсегда fired
* - закрыта навсегда Blocked
Lock open, wait for door opening - замок открыт, ждем открывания двери
*/
	private $portStatePage='E.htm';//страница состояния портов контроллера
	
	private $ch;// ресурс CURL
	
	
	private $doorStateVar=array(
		'Closed',
		'Closed',
		'Closed',
		'Lock open, wait for door opening',
		);
	 
	 
	 
	 public function __construct($ip)
    {
       //Log::instance()->add(Log::DEBUG, 'Line 47 Старт сбора информации для ip='. $ip);
		$this->connect();
	   $this->isOnline=false;
	   
	  
	   
	   $this->ip_address=$ip;
	   if($this->checkOnline()) $this->isOnline=true;
	   
	  
	}
	
	/** 19.08.2024 Получить полную информацию об устройстве.
	* процесс сводится к запуску нужнух методов и заполнению свойств
	*/
	public function getDeviceInfo()
    {
	   if($this->isOnline) {	//собираю данные по прибору
			$this->getDoorMode(); //режимы работы точек прохода (рабочий, закрыто навсегда, открыто навсегда)
			$this->getSoftVersion();//версия прошивки. Тут же, в версии указана емкость контроллера
			$this->getDeviceMode();//режимы работы WP и Test
			$this->getInputPortState(); // чтение состояния входных портов
			$this->getScudMode(); // чтение состояния входных портов
	   
			$artonit=new phpArtonitUDP($this->ip_address, 8192);
						$artonit->command='GetKeyCount';
						$artonit->execute();
			
				$this->keyCount=$artonit->answer;
			

	   }
	}
	
	
	
	
	
	public function getInputPortState()// получить битовыую маску состояния входных портов
	{
		
		$stringBeforPortState='<tr><td>IN';
		$textShift=34;
		
		$stringAfterPortState='</td>';
		$state=array();
		
		
		$curl = $this->makeRequest($this->portStatePage);
		
			try{
				$response=$this->execute($curl );
				for($i=1; $i<9;$i++){
					$state[$i-1]=$this->parsFromStrToStr($response, $stringBeforPortState.$i, $textShift, $stringAfterPortState);
					//Log::instance()->add(Log::DEBUG, 'Line 70-70 '. Debug::vars('71',$state));

				}
				
				$this->portStateInput=$state;
				
				//Log::instance()->add(Log::DEBUG, 'Line 71-71 '. Debug::vars($this));
				
				
				return true;	
			} catch (Kohana_Request_Exception $e){
				
			
			}
	}
	
	public function getDoorMode()// получить режим работы точек прохода. Ответ - массив
	{
		
		$curl = $this->makeRequest($this->doorStatePage);
		
			try{
				$response=$this->execute($curl );
				//echo Debug::vars('54', $response); exit;
				//$this->doorState[0]= $this->minipars(trim($response->body()), 'T');		
				$this->doorMode[0]= $this->minipars(trim($response), 'A');		
				$this->doorMode[1]= $this->minipars(trim($response), 'B');		
				
				return true;	
			} catch (Kohana_Request_Exception $e){
				
				//$this->doorState[0]= -1;	
				$this->doorMode[0]= -1;		
				$this->doorMode[1]= -1;			
			}
		
	}
	
	public function getDeviceMode()// получить режим работы контроллера (состояния WP, TEST, mac). Ответ - массив
	{
		
		$stringWP='checked><b> Jumper "WP"';
		$stringTest='checked><b> Jumper "TEST"';
		
		$stringBeforMAc='00-20-A6';
		$stringAfterMac='</b>';
		
		
		$curl = $this->makeRequest($this->leftMenuPage);
			try{
				$response=$this->execute($curl );
		
	
		if($this->parsFindString($response, $stringWP)) $this->isWp = True;
				if($this->parsFindString($response, $stringTest)) $this->isTest = True;
				$mac=$this->parsFromStrToStr($response, $stringBeforMAc, 0, $stringAfterMac);
				if(strlen($mac)>1)$this->mac_address = $mac;
				
				
				return true;	
			} catch (Kohana_Request_Exception $e){
				
			
			}
		
	}
	
	
	public function getScudMode()// получить режим работы контроллера (одна дверь на два считывателя или две двери).
	{
		
		$d1='name="S0f7F1" value="Y" checked';//одна дверь на два считывателя
		$d2='name="S0f7F1" value="N" checked';//две двери на два считывателя
		
		
		$curl = $this->makeRequest($this->scudModePage);
			try{
				$response=$this->execute($curl );
		
		//echo Debug::vars('187', $response); exit;
		if($this->parsFindString($response, $d1)) $this->scud = 'd1';
		if($this->parsFindString($response, $d2)) $this->scud = 'd2';
				
				
				return true;	
			} catch (Kohana_Request_Exception $e){
				
			
			}
		
	}
	
	
	
	
	
	public function getSoftVersion()// получить версию прошивки контроллера
	{
	
		$stringBefor='www.artonit.ru</a> ';//начало позиции, которую ищу
		$stringAfterMac='</TD></TR>';
	$this->softVersion="n/a";
		$curl = $this->makeRequest($this->softVersionPage);
	
			try{
				$response=$this->execute($curl );
			      //  Log::instance()->add(Log::DEBUG, 'Line 185 response'. Debug::vars('185',$curl, $response));	
					
				$softVersion=$this->parsFromStrToStr($response, $stringBefor, strlen($stringBefor), $stringAfterMac);
				
				
				 Log::instance()->add(Log::DEBUG, 'Line 217 для id_dev='.$this->ip_address.' from pos= '.strpos( trim($softVersion), iconv('UTF-8','windows-1251', 'Артонит')));	
				 Log::instance()->add(Log::DEBUG, 'Line 189 для id_dev='.$this->ip_address.' распознал версию '. trim($softVersion). ' mb_detect_encoding is '. mb_detect_encoding(trim($softVersion)));	
				
				if(mb_detect_encoding($softVersion) == 'ASCII') {
					
					if(strlen($softVersion)>1)$this->softVersion = trim(str_replace ('|', '', $softVersion));
				} else {
					
					if(strpos( trim($softVersion), iconv('UTF-8','windows-1251', 'Артонит-М'))>0) $this->softVersion='Артонит-М 28К 2014';
					if(strpos( trim($softVersion), iconv('UTF-8','windows-1251', 'Артонит-СЕ'))>0) $this->softVersion='Артонит-СЕ 4К 2014';
						
						
					}
				
				
				$softVersion = '';
				
				
							
				//echo Debug::vars('193', $this);exit;
				//Log::instance()->add(Log::DEBUG, 'Line 194 распознал версию '. Debug::vars('194',$this));	
				return true;	
			} catch (Kohana_Request_Exception $e){
				
			
			}
		
	}
	
	
	
	
	public function makeRequest($page)
	{
		
		
		return "http://".$this->ip_address.'/'.$page;
		
	
	}
	
	public function checkOnline()
	{
	$curl = $this->makeRequest($this->leftMenuPage);
	   
		$result=$this->execute($curl );
		//echo Debug::vars('226', $curl, $result);exit;
		//Log::instance()->add(Log::NOTICE, '228 curl'. Debug::vars('228', $result ));
		if($result){
				Log::instance()->add(Log::NOTICE, '226 curl есть связь  IP '.$this->ip_address);

				return true;
			
		} else {
				Log::instance()->add(Log::NOTICE, '229 curl нет связи '.$this->ip_address);
				return false;
		}
			
	}
	
	
	/** минипарсер строки.
		/* Извлекает из строки содержимое между указанными параметрами. Строка имеет формат а-ля xml
		*/
		public function minipars($strdata, $label)
		{
			$_startPosition=strpos($strdata, '<'.$label.'>');
			$_endPosition=strpos($strdata, '</'.$label.'>');
			return trim(substr($strdata, $_startPosition+3, $_endPosition-$_startPosition-3));
		}
	
	/** определение состояния WP и TEST
	* состояние определяется путем поиска фиксированных строк
	* 
	*/
	
	public function parsFindString($strdata, $label)
		{
			
			if(strpos($strdata, $label)) return true;
			return false;
		}
	
	/** 4.08.2024
	*парсер делает выборку данных с указанного набора символов и до указанного набора символов
	*$strdata - строка, в которой происходит поиск нужных параметров
	*$strFrom - строка начала поиска
	*$startShift - сдвиг относительно первого вхождения
	*$strTo - строка, перед которыми завершается поис
	*/
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
	
	
	public function connect()
	{
			$this->ch = curl_init();
					
	}
	
	public function disconnect()
	{
		curl_close($this->ch);
		
	}
	
	
	public function execute($url)
	{
//echo Debug::vars('325',$url);exit;
		curl_setopt($this->ch, CURLOPT_URL, $url);
		curl_setopt($this->ch, CURLOPT_HEADER, 0);
		curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($this->ch, CURLOPT_TIMEOUT, 60);// Максимально позволенное количество секунд для выполнения cURL-функций. 
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		
			
		return curl_exec($this->ch);
		
	}
	
	
	
}
