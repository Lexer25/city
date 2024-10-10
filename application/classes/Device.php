<?php defined('SYSPATH') OR die('No direct access allowed.');
/*
25.08.2023 
Класс Device - контроллеры СКУД
*/
class Device
{
	
	private $result_ok='OK';
	private $result_err='Err';
	private $errcode=array(
		'id_is_null' =>'ID is null',
	);
	public $result;//результат выполнение метода OK - выполнен правильно, Err - выполнен с ошибкой
	public $rdesc;// результат выполнения метода: набор данных или ошибок
	
	
	public $id;//id_dev контроллера
	public $name;//название контроллера
	public $ctrl;//контрол как признак объединения с точками прохода.
	public $type;//тип контроллера
	public $id_ts;//принадлежность к Транспортному серверу
	public $is_active;//признак активности
	public $is_present;//признак наличия двери для указанного id. Если контроллера нет, то значение = NULL, если дверь есть, то значение True
	public $connectionString;//строка подключения
	public $child;//дочерние id (id_dev дверей, 2 шт)
	public $childDoorState;//дочерние id (id_dev дверей, 2 шт)
	
	 
	 public function __construct($id = null)
    {
        if(isset($id)){
			$this->id = $id;
			$sql='select d.id_dev, d.name, d.id_devtype, d.id_server, d.id_ctrl, d.netaddr, d."ACTIVE" as is_active from device d
					where d.id_reader is null
					and d.id_dev='.$this->id;
			try
			{
				$query = Arr::flatten(DB::query(Database::SELECT, $sql)
				->execute(Database::instance('fb'))
				->as_array()
				);
				$this->name=Arr::get($query, 'NAME');
				$this->ctrl=Arr::get($query, 'ID_CTRL');
				$this->type=Arr::get($query, 'ID_DEVTYPE');
				$this->id_ts=Arr::get($query, 'ID_SERVER');
				$this->is_active=Arr::get($query, 'IS_ACTIVE');
				$this->connectionString=Arr::get($query, 'NETADDR');
				$this->is_present = (Arr::get($query, 'ID_DEV'))? TRUE : FALSE;
			
			} catch (Exception $e) {

				Log::instance()->add(Log::DEBUG, 'Line 40 '. $e->getMessage());
			}
		}
		
	}
	
	public function getChild()
	{
	$sql='select d2.id_dev from device d
			join device d2 on d2.id_ctrl=d.id_ctrl and d2.id_reader is not null
                where d.id_reader is null
                and d.id_dev='.$this->id;
		$query = DB::query(Database::SELECT, DB::expr($sql))
			->execute(Database::instance('fb'))
			->as_array();
				
		foreach($query as $key=>$value)
		{
			
			$res[]=Arr::get($value, 'ID_DEV');
		}
		//echo Debug::vars('63', $sql, $res);
		$this->child=$res;
	
	}
	/*
	вычисление id_ctrl для добавления контролера в таблицу device 
	*/
	public function getCtrl()
	{
		$ctrl=-2;
		$sql='select distinct d.id_ctrl from device d
			order by d.id_ctrl';
		$query = DB::query(Database::SELECT, DB::expr($sql))
			->execute(Database::instance('fb'))
			->as_array();
		
		$sql='select max(d.id_ctrl) from device d';
		$ctrl_max = DB::query(Database::SELECT, DB::expr($sql))
			->execute(Database::instance('fb'))
			->get('MAX');
		
		
		foreach ($query as $key=>$value)
		{
			$res[Arr::get($value, 'ID_CTRL')]=1;//
		}
		
		for($i=1;  $i<$ctrl_max+1; $i++)
		{
			
			if (!array_key_exists($i, $res))
			{
				$ctrl=$i;
				break ;
			} else {
				$ctrl=-1;
			}
			
		}
		
		return $ctrl;
	}
	
	
	/*
	26.08.2023
	Сохранение данных Об устройстве в базу данных
	*/
	
	public function save()
	{

			$id_dev = DB::query(Database::SELECT,
			'SELECT gen_id(gen_dev_id, 1) FROM rdb$database')
			->execute(Database::instance('fb'))
			->get('GEN_ID');
			//получил id вставляемого контроллера
			$ctrl=$this->getCtrl();
			$sql=__('INSERT INTO DEVICE (ID_DEV, ID_DB, ID_SERVER, ID_DEVTYPE, ID_CTRL,  NETADDR, NAME, "ACTIVE")
		VALUES (:ID_DEV, :ID_DB, :ID_SERVER, :ID_DEVTYPE, :ID_CTRL, \':NETADDR\', \':NAME\', :"ACTIVE")', 
		array(
			':ID_DEV'=>$id_dev,
			':ID_DB'=>1,
			':ID_SERVER'=>$this->id_ts,
			':ID_DEVTYPE'=>$this->type,
			':ID_CTRL'=>$ctrl,
			':ID_READER'=>NULL,
			':NETADDR'=>$this->id,
			':NAME'=>$this->name,
			':"VERSION"'=>NULL,
			':INTERVAL'=>NULL,
			':DSS1'=>NULL,
			':DSS2'=>NULL,
			':FLAG'=>NULL,
			':ID_PLAN'=>NULL,
			':POS_X'=>NULL,
			':POS_Y'=>NULL,
			':PSW'=>NULL,
			':"ACTIVE"'=>$this->is_active,
			':CONFIG'=>NULL,
			':PARAM'=>NULL,
			':TAGNAME'=>NULL,
			':ID_GUIDE'=>NULL,
			':ID_OBJECT'=>NULL,
			':ID_PARENT'=>NULL
			));
		//echo Debug::vars('42', $ctrl, $id_dev, $sql); exit;	
		//echo Debug::vars('45', $sql); exit;
		try
			{
				$query = DB::query(Database::INSERT, iconv('UTF-8', 'CP1251',$sql))
				->execute(Database::instance('fb'));
				$this->result=$this->result_ok;
				$this->rdesc=$ctrl;
			} catch (Exception $e) {
				Log::instance()->add(Log::DEBUG, 'Line 83 '. $e->getMessage());
				$this->result=$this->result_err;
				$this->rdesc=$e->getMessage();				
			}
	}
	
	/*
	26.08.2023
	Изменение данных для указанного id
	*/
	public function update()
	{
		//echo Debug::vars('36', $this);exit;
		
		$sql='UPDATE DEVICE
			SET ID_SERVER = '.$this->id_ts.',
			ID_DEVTYPE ='.$this->type.',
			NETADDR = \''.$this->connectionString.'\',
			NAME = \''.$this->name.'\',
			"ACTIVE" = '.$this->is_active.'
			WHERE (ID_DEV = '.$this->id.') AND (ID_DB = 1)';
			
		
			
		


		Log::instance()->add(Log::DEBUG, 'Line 101 '. $sql);
		//echo Debug::vars('65', $sql); exit;
		try
			{
			//$query = DB::query(Database::UPDATE, iconv('UTF-8', 'CP1251',$sql))
			$query = DB::query(Database::UPDATE, $sql)
			->execute(Database::instance('fb'));
			
			$this->result=$this->result_ok;
			$this->rdesc=$this->id;
			return 0;
			
			} catch (Exception $e) {
				Log::instance()->add(Log::DEBUG, 'Line 112 '. $e->getMessage());
				Log::instance()->add(Log::DEBUG, 'Line 214 '. Debug::vars('65', $this));
				$this->result=$this->result_err;
				$this->rdesc=$e->getMessage();	
				return 3;				
			}
			
	}
	
	
	/*
	26.08.2023
	Удаление данных для указанного id
	*/
	public function delete()
	{
		//echo Debug::vars('36', $this->name, $this->standalone);
		
		$sql='delete from ...';
		Log::instance()->add(Log::DEBUG, 'Line 72 '. $sql);
		//echo Debug::vars('65', $sql); exit;
		try
			{
			$query = DB::query(Database::DELETE, iconv('UTF-8', 'CP1251',$sql))
			->execute(Database::instance('fb'));
			
			$this->result=$this->result_ok;
			$this->rdesc=$this->id;
			
			} catch (Exception $e) {
				Log::instance()->add(Log::DEBUG, 'Line 139 '. $e->getMessage());
				$this->result=$this->result_err;
				$this->rdesc=$e->getMessage();		
				
			}
	}
	
	
	/*
	9.09.2023
	проверка состояния связи. Для проверки выполняется чтение версии устройства.
	В ходе проверки устанавливаетя самостоятельное соединение с ТС для  работы с указаным устройством.
	*/
	public function checkConnect(){
		$ts2client=new TS2client();
		
		if($ts2client->init($this->id_ts)==0)
		{
			
			$ts2client->startServer();
			
			//$message='t56 exec device="'.$this->name.'", command="getversion"';
			//$ts2client->sendMessage($message);
			$aaa=$this->XXX($this->name, 'getversion', $ts2client);
			$sser='www.artonit.ru';
			//echo Debug::vars('245', $aaa, strpos( $aaa, $sser ));exit;
				if(strpos($aaa, $sser))
			{
				//echo Debug::vars('252', $aaa);exit;
				$ts2client->stopClient();
				return true;
			} else {
				//echo Debug::vars('256', $aaa);exit;
				$ts2client->stopClient();
				return false;
			};
		} else {
			return false;
		}
		
		
	}
	
	/*
	10.09.2023
		вспомогательная программа для организации цикла обмена с целью полуить именно ответ, отфильтровать от событий
		$dev_name - имя устройства
		$command - подготовленная команда
		$connect - подготовленное соединение с устройством
		$attempy - количество попыток чтения до получения нужного ответа
		
	*/
	public function XXX ($dev_name, $command, $connect, $attempt=10){
		
		$pid_send='t45';
		$message=$pid_send.' exec device="'.$dev_name.'", command="'.$command.'"';
		//Log::instance()->add(Log::NOTICE, 'XXX '.$message);
		$connect->sendMessage($message);
		for ($i=0; $i<$attempt; $i++)
		{
			$ttt=$connect->readMessage();
			
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
