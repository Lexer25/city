<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_People extends Model
{
	
	public function people_unactive($id_pep)// Изменение активности карты
	{
		if(count($id_pep)>0) {
		$count_in=50;
		if (count($id_pep)>$count_in)
		{
			$id_for_del=array_chunk($id_pep, $count_in);
			$id_pep=$id_for_del[0];
		}
		$sql='update card c set c."ACTIVE"=0 where c.id_pep in ('. implode(",", $id_pep).')';
		Kohana::$log->add(Log::INFO, 'Активность карты установлена в 0. :id_pep', array(':id_pep' => $sql));
		$query = DB::query(Database::UPDATE, $sql)
		->execute(Database::instance('fb'));
		} else {
			Kohana::$log->add(Log::INFO, 'Нет данных для выполнения операции по unactive карты.');
		}
	}
	
	public function setAuthMetod($id_pep, $authMode)// Изменение метода авторизации
	{
		
		$sql='update people p set p.AUTHMODE='.$authMode.' where p.id_pep ='. $id_pep;
		$query = DB::query(Database::UPDATE, $sql)
		->execute(Database::instance('fb'));
		return;
	}
	
	
	public function People_delete ($id_pep)// удаление пользователей. При удалении проверяется количество индексов в удаляемом массиве, чтобы их число не превысило 1500 в одном запросе.
	{
		if(count($id_pep)>0) {
		$t1=microtime(1);
		$count_in=1500;
		if (count($id_pep)>$count_in)
		{
			$id_for_del=array_chunk($id_pep, $count_in);
			$id_pep=$id_for_del[0];
		}
		$sql='delete from people p where p.id_pep in ('. implode(",", $id_pep).')  and p.id_pep <> 1';
		Kohana::$log->add(Log::INFO, 'Удаляются пользователи. :id_pep', array(':id_pep' => $sql));
		//echo Debug::vars('14', $sql); exit;
		$query = DB::query(Database::DELETE, $sql)
		->execute(Database::instance('fb'));
		$t2=microtime(1)-$t1;
		Kohana::$log->add(Log::INFO, 'Время выполнения='.$t2);
		} else {
			Kohana::$log->add(Log::INFO, 'Нет данных для выполнения операции по удалению карты.');
		}
	}
	
	public function Card_delete ($id_pep)// удаление карт. При удалении проверяется количество индексов в удаляемом массиве, чтобы их число не превысило 1500 в одном запросе.
	{
		if(count($id_pep)>0) {
		$t1=microtime(1);
		$count_in=50;
		if (count($id_pep)>$count_in)
		{
			$id_for_del=array_chunk($id_pep, $count_in);
			$id_pep=$id_for_del[0];
		}
		$sql='delete from card c where c.id_pep in ('. implode(",", $id_pep).')';
		Kohana::$log->add(Log::INFO, 'Удаляются картя у пользователей. :id_pep', array(':id_pep' => $sql));
		//echo Debug::vars('14', $sql); exit;
		$query = DB::query(Database::DELETE, $sql)
		->execute(Database::instance('fb'));
		$t2=microtime(1)-$t1;
		Kohana::$log->add(Log::INFO, 'Время выполнения='.$t2);
		} else {
			Kohana::$log->add(Log::INFO, 'Нет данных для выполнения операции по удалению карты.');
		}
	}
	
	public function People_long ($id_pep, $time_long)
	{
		$t1=microtime(1);
		$isactive=0;
		if(count($id_pep)>0) {
		
		$date=date("Y-m-d H:i:s", strtotime($time_long));
		$count_in=50;
		if (count($id_pep)>$count_in)
		{
			$id_for_long=array_chunk($id_pep, $count_in);
			$id_pep=$id_for_long[0];
		}
		if(strtotime($time_long)>strtotime("now")) {
			$isactive=1;
		}
		$sql='update card c set c."ACTIVE"='.$isactive.', c.timeend = \''.$date.'\' where c.id_pep in ('.implode(",",$id_pep).')';
		$query = DB::query(Database::UPDATE, $sql)
		->execute(Database::instance('fb'));
		$t2=microtime(1)-$t1;
		Kohana::$log->add(Log::INFO, 'Продлен срок действия карты. :id_pep', array(':id_pep' => $sql));
		Kohana::$log->add(Log::INFO, 'Время выполнения='.$t2);
		
		} else {
			Kohana::$log->add(Log::INFO, 'Нет данных для выполнения операции по продлению срока действия карты для :id_pep до даты :date', array(':id_pep' => $id_pep, ':date'=>$date));
		}
	
	}
	
	
	
	
	public function findIdPep ($search)// поиск пользователя по введенным буквам 
	{

	if ($search == NULL) return NULL;
	if(strlen($search)<4) return NULL;// т.к. кодировка UTF, о на каждую букву отводится 2 байта. 3 буквы - это 6 байт.

	$sql='select distinct p.id_pep from people p
			join card c on c.id_pep=p.id_pep
			where (p.name containing \''.$search.'\' or
			p.surname containing \''.$search.'\' or
			p.patronymic containing \''.$search.'\')';

	$query = DB::query(Database::SELECT, iconv('UTF-8','windows-1251',$sql))
			->execute(Database::instance('fb'))
			->as_array();
	//echo Debug::vars('28', $sql, $query);exit;
	$res=array();
	
		foreach ($query as $key=>$value)
		{
				$res[]=Arr::get($value, 'ID_PEP');
		}
	return $temp=$this->findIdPepInfo($res);;
	
	}
	
	
	 public function getIdPepFromCard ($key)// поиск пользователя по номеру карты 
	{

		
	$sql='select c.id_pep from card c
		where c.id_card containing \''.$key.'\'';// containing дает даже неполное совпадение
		
		
	$sql_0='select c.id_pep from card c
		where c.id_card = \''.$key.'\'';// а тут - полное строгое совпадение
		
		
		

	$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
		//	->get('ID_PEP')
			->as_array()
			;
	//echo Debug::vars('28', $sql, $query);exit;
	

	return $query;
	} 
	
	
	
	
	public function findIdPepInfo($id_pep)// подготовка данных для выбора по массиву id_pep
	{
		//echo Debug::vars('154', $id_pep, empty($id_pep)); exit;
		$res=array();
		if(!empty($id_pep))
		{
		
			$sql='select distinct p.id_pep, p.name, p.surname, p.patronymic, p.note,  o.name as org_name, c.id_card,
			max (e.datetime), C.ID_CARDTYPE, ct.name as CARDTYPENAME, p.authmode from people p
			join card c on c.id_pep=p.id_pep
			join cardtype ct on ct.id=c.id_cardtype
			join organization o on o.id_org=p.id_org
			left join events e on e.ess1=p.id_pep and e.id_card=c.id_card and e.id_eventtype in (50, 65)
			where p.id_pep in ('.implode(",", $id_pep).')
			group by p.id_pep, p.name, p.surname, p.patronymic, p.note,  o.name, c.id_card, C.ID_CARDTYPE, ct.name, p.authmode';
			//echo Debug::vars('162', $sql); exit;
			$query = DB::query(Database::SELECT, iconv('UTF-8','windows-1251',$sql))
			//$query = DB::query(Database::SELECT, mb_convert_encoding($sql, 'UTF-8','windows-1251'))
			//$query = DB::query(Database::SELECT, $sql)
					->execute(Database::instance('fb'))
					->as_array();
			//echo Debug::vars('28', $sql, $query);exit;
		
			foreach ($query as $key=>$value)
			{
				$res[$key]=$value;
				$res[$key]['NAME']=iconv('windows-1251','UTF-8',$value['NAME']);
				$res[$key]['PATRONYMIC']=iconv('windows-1251','UTF-8',$value['PATRONYMIC']);
				$res[$key]['SURNAME']=iconv('windows-1251','UTF-8',$value['SURNAME']);
				$res[$key]['ORG_NAME']=iconv('windows-1251','UTF-8',$value['ORG_NAME']);
				$res[$key]['NOTE']=iconv('windows-1251','UTF-8',$value['NOTE']);
				$res[$key]['MAX']=Arr::get($value, 'MAX');
				$res[$key]['CARDTYPE']=Arr::get($value, 'ID_CARDTYPE');
				$res[$key]['CARDTYPENAME']=iconv('windows-1251','UTF-8',Arr::get($value, 'CARDTYPENAME'));
				$res[$key]['AUTHMODE']=Arr::get($value, 'AUTHMODE');
			}
		}
	return $res;
		
	}
	
	
	public function getPeople($id_pep, $id_card=false)//полученние данных для указанного ID сотрудника
	{
		$sql='select p.id_pep,
			p.id_org,
			p.surname,
			p.name,
			p.patronymic,
			p.datebirth,
			p.placelife,
			p.placereg,
			p.phonehome,
			p.phonecellular,
			p.phonework,
			p.numdoc,
			p.datedoc,
			p.photo,
			p.workstart,
			p.workend,
			p."ACTIVE",
			p.flag,
			p.login,
			p.pswd,
			p.id_devgroup,
			p.id_orgctrl,
			p.peptype,
			p.post,
			p.placebirth,
			p.present,
			p.note,
			p.id_area,
			p.sysnote,
			p.tabnum,
			p.authmode,
			c.id_card,
			c.TIMESTART,
			c.TIMEEND,
			c."ACTIVE" as card_is_active,
			c.id_cardtype,
			ct.name as CARDTYPE

			 from people p
			 left join card c on c.id_pep=p.id_pep
			 join cardtype ct on ct.id=c.id_cardtype
			 where p.id_pep='.$id_pep;
			 if(isset($id_card)) $sql=$sql.'and c.id_card=\''.$id_card.'\'';

		$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
		$res=array();
		foreach ($query as $key=>$value)
		{
			foreach ($value as $key1=>$data){
				$res[$key1] = $data;
				if($key1 == 'NAME') $res[$key1]=iconv('windows-1251','UTF-8',$data);
				if($key1 == 'PATRONYMIC') $res[$key1]=iconv('windows-1251','UTF-8',$data);
				if($key1 == 'SURNAME') $res[$key1]=iconv('windows-1251','UTF-8',$data);
				if($key1 == 'CARDTYPE') $res[$key1]=iconv('windows-1251','UTF-8',$data);
				$res['tree']=iconv('windows-1251','UTF-8',$this->getOrgTree($value['ID_ORG']));	// получение дерева организаций
			}
		
		
		}
		
		return $res;
	}

		public function getPeopleDoor($id_pep, $id_card=null)// список точек прохода, куда может ходить пользователь
		{
		
		$sql='select distinct dt.standalone,  d.name, d.id_dev, d.id_reader, d2.name as controller_name, cd.devidx, cd.load_result, cd.load_time, cdd.id_pep,  cdd.operation, cdd.attempts, cdd.time_stamp, s.name as SERVER_NAME   from ss_accessuser assu
			join access ac on assu.id_accessname=ac.id_accessname
			join device d on ac.id_dev=d.id_dev
			join device d2 on d.id_ctrl=d2.id_ctrl and d2.id_reader is null
			join devtype dt on d.id_devtype=dt.id_devtype
			join card c on c.id_pep=assu.id_pep
			left join cardindev cdd on assu.id_pep=cdd.id_pep and cdd.id_dev=d.id_dev and cdd.operation=1
			left join cardidx cd on cd.id_card=c.id_card and cd.id_dev=d.id_dev
			join server s on d2.id_server=s.id_server
			where assu.id_pep=' . $id_pep.'	
			and c.id_card=\''.$id_card.'\'
			order by d.id_dev';
				
		//4.08.2021
		$sql='select distinct dt.standalone,  d.name, d.id_dev, d.id_reader, d2.name as controller_name, cd.devidx, cd.load_result, cd.load_time, cdd.id_pep,  cdd.operation, cdd.attempts, cdd.time_stamp, s.name as SERVER_NAME   from ss_accessuser assu
			join access ac on assu.id_accessname=ac.id_accessname
			join device d on ac.id_dev=d.id_dev
			join device d2 on d.id_ctrl=d2.id_ctrl and d2.id_reader is null
			join devtype dt on d.id_devtype=dt.id_devtype
			join card c on c.id_pep=assu.id_pep
			left join cardindev cdd on assu.id_pep=cdd.id_pep and cdd.id_dev=d.id_dev and cdd.operation=1
			left join cardidx cd on cd.id_card=c.id_card and cd.id_dev=d.id_dev
			join server s on d2.id_server=s.id_server
			where assu.id_pep=' . $id_pep;
		

		
		//echo Debug::vars('215', $sql); exit;
		$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
			$res=array();
			foreach ($query as $key=>$value)
			{
				$key=$value['ID_DEV'];
				$res[$key]['NAME'] =  iconv('windows-1251','UTF-8',$value['NAME']);
				$res[$key]['ID_DEV'] = $value['ID_DEV'];
				$res[$key]['STANDALONE'] = $value['STANDALONE'];
				$res[$key]['LOAD_RESULT'] = iconv('windows-1251','UTF-8', $value['LOAD_RESULT']);
				$res[$key]['LOAD_TIME'] = $value['LOAD_TIME'];
				$res[$key]['LOAD_INSERT'] = Arr::get($value, 'OPERATION', 'no');
				$res[$key]['TIME_INSERT'] = Arr::get($value, 'TIME_STAMP', 'no');
				$res[$key]['DEVIDX'] = Arr::get($value, 'DEVIDX', 'no');
				$res[$key]['ID_READER'] = Arr::get($value, 'ID_READER', 'no');
				$res[$key]['CONTROLLER_NAME'] = iconv('windows-1251','UTF-8', Arr::get($value, 'CONTROLLER_NAME', 'no'));
				$res[$key]['SERVER_NAME'] = iconv('windows-1251','UTF-8', Arr::get($value, 'SERVER_NAME', 'no'));
			
			}
		
		return $res;
		}
		
		public function getPeople_without_card()// список пользователей без карты
		{
			$sql='select p.id_pep, p.surname, p.name, p.patronymic, p.note, p."ACTIVE" as isactive, o.name as org_name from people p
				join organization o on o.id_org=p.id_org
				left join card c on c.id_pep=p.id_pep
				where c.id_card is null';
			$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
			$res=array();
			foreach ($query as $key=>$value)
			{
				$res[$key]['ID_PEP'] = $value['ID_PEP'];
				$res[$key]['SURNAME']=iconv('windows-1251','UTF-8',$value['SURNAME']);
				$res[$key]['NAME']=iconv('windows-1251','UTF-8',$value['NAME']);
				$res[$key]['PATRONYMIC']=iconv('windows-1251','UTF-8',$value['PATRONYMIC']);
				$res[$key]['NOTE']=iconv('windows-1251','UTF-8',$value['NOTE']);
				$res[$key]['ISACTIVE'] = $value['ISACTIVE'];
				$res[$key]['ORG_NAME']=iconv('windows-1251','UTF-8',$value['ORG_NAME']);
			}
			
			return $res;
			
		}
		
		public function getPeople_without_events()// (17.10.2017)список пользователей, у которых вообще нет событий
		{
			$sql='select c.id_card, c.timestart, c.timeend, p.id_pep, p.surname, p.name, p.patronymic, p."ACTIVE" as isactive, p.note, o.name as org_name from card c
left join events e on e.id_card=c.id_card
join people p on p.id_pep=c.id_pep
join organization o on o.id_org=p.id_org
where e.id_card is null';
			
			$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
			$res=array();
			foreach ($query as $key=>$value)
			{
				$res[$key]['ID_CARD'] = $value['ID_CARD'];
				$res[$key]['TIMESTART'] = $value['TIMESTART'];
				$res[$key]['TIMEEND'] = $value['TIMEEND'];
				$res[$key]['ID_PEP'] = $value['ID_PEP'];
				$res[$key]['SURNAME']=iconv('windows-1251','UTF-8',$value['SURNAME']);
				$res[$key]['NAME']=iconv('windows-1251','UTF-8',$value['NAME']);
				$res[$key]['PATRONYMIC']=iconv('windows-1251','UTF-8',$value['PATRONYMIC']);
				$res[$key]['NOTE']=iconv('windows-1251','UTF-8',$value['NOTE']);
				$res[$key]['ISACTIVE'] = $value['ISACTIVE'];
				$res[$key]['ORG_NAME']=iconv('windows-1251','UTF-8',$value['ORG_NAME']);
			}
			
			return $res;
			
		}
		public function getOrgTree($id_org)// получить дерево организаций
		{
			$sql='SELECT ID_ORG, NAME, ID_PARENT, FLAG  FROM ORGANIZATION_GETPARENT(1,'. $id_org.')';
			
			$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
			$orgTreeName=array();
			foreach ($query as $key=>$value)
			{
				$orgTreeName[]= $value['NAME'];
			
			}
			//echo Debug::vars('296', $query, $orgTreeName); exit;
			$res=array();
			
			$res='Tree222';
			return implode("/", array_reverse($orgTreeName));
		
		}
		
}


