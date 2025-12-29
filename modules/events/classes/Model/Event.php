<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_Event extends Model
{

	public function getAnalytListDoor($analyt_code)//1.2.7 получение списка точек прохода с указаными кодами аналитики
	{
		$sql='select d2.id_dev, 
			d2.name as name_dev, 
			d.id_dev as id_door, 
			d.name as name_door, 
			d2.param as param_dev, 
			d.param as param_door, 
			count (*) from events e
			join device d on d.id_dev=e.id_dev
			join device d2 on d2.id_ctrl=d.id_ctrl and d2.id_reader is null
			where e.analit='.$analyt_code.
			'and e.datetime>current_date-1
			group by d2.id_dev, 
			d2.name,d.id_dev,
			d.name,
			d2.param, 
			d.param
			order by d.id_dev';
			//echo Debug::vars('25', $sql); exit;
			$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'));
			
				foreach ($query as $key=>$value)
				{
				$res[$key]['ID_DEV']=$value['ID_DEV'];
				$res[$key]['NAME_DEV']=iconv('windows-1251','UTF-8',$value['NAME_DEV']);
				$res[$key]['ID_DOOR']=$value['ID_DOOR'];
				$res[$key]['NAME_DOOR']=iconv('windows-1251','UTF-8',$value['NAME_DOOR']);
				$res[$key]['COUNT']=$value['COUNT'];
				$res[$key]['PARAM_DEV']=iconv('windows-1251','UTF-8',$value['PARAM_DEV']);
				$res[$key]['PARAM_DOOR']=iconv('windows-1251','UTF-8',$value['PARAM_DOOR']);
			}
			if(isset($res))
			{
				return $res;
			} else {
				
				HTTP::redirect('errorpage?err=Answer error 44 '.Request::current()->controller().'/'.Request::current()->action());
				
			}
			
		
	}



	public function getAnalytCodeList($analyt_code=FALSE)// 1.2.5 получение данных по кодам аналитки. Добавлено 2.03.2020
	{
		$res=array();
		$sql='select  
			d.id_reader,
			srv.name as srv_name,
			cdx.devidx,
            cdx.load_time,
            cdx.load_result,
			e.id_eventtype, 
			et.name as EVENT_NAME, 
			d.id_dev,
			d.name, 
			d2.id_dev as deviceid, 
			d2.flag, 
			d2.name as deviceName, 
			std.facts,
			std1.facts as deviceversion, 
			std2.facts as test_mode, 
			e.note, 
			e.ess1, 
			e.ess2,
			e.analit, 
			e.id_card,
			c."ACTIVE" as IS_ACTIVE, 
			count(*) from events e
			
				left join device d on d.id_dev=e.id_dev
				left join cardidx cdx on cdx.id_card=e.id_card and cdx.id_dev=e.id_dev
				left join device d2 on d2.id_ctrl=d.id_ctrl and d2.id_reader is null
				left join server srv on srv.id_server=d2.id_server
				left join eventtype et on et.id_eventtype=e.id_eventtype
				left join cardindev cd on cd.id_card=e.id_card
				left join card c on c.id_card=e.id_card
				left join st_data std on std.id_dev=d2.id_dev and std.id_param=10
				left join st_data std1 on std1.id_dev=d2.id_dev and std1.id_param=1
				left join st_data std2 on std2.id_dev=d.id_dev and std2.id_param=9
				where e.datetime>current_timestamp-1
				and e.analit in ('.$analyt_code.')
				group by 
					d.id_reader,
					srv.name,
					cdx.devidx,
					cdx.load_time,
					cdx.load_result,
					e.id_eventtype, 
					et.name, d.id_dev, d.name, d2.id_dev,d2.flag,  d2.name ,std.facts,std1.facts, std2.facts, e.note, e.ess1, e.ess2,e.analit, e.id_card, c."ACTIVE"';
		//echo Debug::vars('91', $sql);	exit;			
		$query = DB::query(Database::SELECT, $sql)
		->execute(Database::instance('fb'));
		//echo Debug::vars('91', isset($query));	exit;		
		if(isset($query)){
			//echo Debug::vars('95', $query); exit;	
			foreach ($query as $key => $value)
			{
				
			//установка умолчательных значений
			$readCommonList = 100;//режим работы "Единый список". 0- единый список выключен, 1 - единый список включен.
			$device_testMode=0;// проверка режима рыботы контроллера. 0 - тестовые режим выключен, 1 - тестовый режим включен
			
			if(!is_null($value['FACTS'])) $readCommonList=substr (Model::Factory('Stat')->parser_2($value['FACTS']), 7, 1);
			$readCommonList=$readCommonList & 1;// выляю последний бит из слова конфигурации, прочитанного из контроллера. 0 - нет Единого списка, 1 - есть единый список.
			
				//4C9900 00 4C9900 00 конфигурация начинается с 7-го знака (считая с 1).
			if(!is_null($value['TEST_MODE']) and ($value['TEST_MODE'] == 'TEST_ON')) $device_testMode=1;
			$device_version=Arr::get($value, 'DEVICEVERSION', 'unknow device version');
			$device_version=Model::Factory('Stat')->parser_2(Arr::get($value, 'DEVICEVERSION', 'unknow device version'));
				
			
			
			$DBCommonList=$value['FLAG'] & 1 ; // выляю последний бит из слова конфигурации, прочитанного из базы данных. 0 - нет Единого списка, 1 - есть единый список.
;			//echo Debug::vars('29', ($readCommonList << 1) + $DBCommonList);
			$res[$key]['ID_READER']=$value['ID_READER'];
			$res[$key]['ID_EVENTTYPE']=$value['ID_EVENTTYPE'];
			$res[$key]['ID_DEV']=$value['ID_DEV'];
			$res[$key]['NAME']=iconv('windows-1251','UTF-8',$value['NAME']);
			$res[$key]['DEVICEID']=$value['DEVICEID'];
			$res[$key]['DEVICEFLAG']=$value['FLAG'];
			$res[$key]['DEVICENAME']=iconv('windows-1251','UTF-8',$value['DEVICENAME']);
			//$res[$key]['DATETIME']=$value['DATETIME'];
			$res[$key]['NOTE']=iconv('windows-1251','UTF-8',$value['NOTE']);
			$res[$key]['ESS1']=$value['ESS1'];
			$res[$key]['ESS2']=$value['ESS2'];
			$res[$key]['ANALIT']=$value['ANALIT'];
			$res[$key]['ID_CARD']=$value['ID_CARD'];
			$res[$key]['IS_ACTIVE']=$value['IS_ACTIVE'];
			$res[$key]['EVENT_NAME']=iconv('windows-1251','UTF-8',$value['EVENT_NAME']);
			$res[$key]['COUNT']=$value['COUNT'];
			$res[$key]['checkConfig']=($readCommonList << 1) + $DBCommonList + ($device_testMode << 2);
			$res[$key]['TESTMODE']= $device_testMode;
			$res[$key]['singleListDevice']= $DBCommonList;
			$res[$key]['singleListDB']= $readCommonList;
			$res[$key]['DEVICEVERSION']= $device_version;
			$res[$key]['DEVIDX']=$value['DEVIDX'];
			$res[$key]['LOAD_TIME']=date("d.m.Y H:i:s", strtotime(Arr::get($value, 'LOAD_TIME')));
			$res[$key]['LOAD_RESULT']=Arr::get($value, 'LOAD_RESULT');
			$res[$key]['SRV_NAME']=iconv('windows-1251','UTF-8',Arr::get($value,'SRV_NAME','err'));
			
			}
		} else {
			//$this->redirect('errorpage?err='.Request::current()->controller().'/'.Request::current()->action());
			HTTP::redirect('errorpage?err=Answer error 154 '.Request::current()->controller().'/'.Request::current()->action());
		}
			
				
		return $res;		
	}
	
	public function stat()//вывод статистики за последние 24 часа
	{
		//Выбор статистики по событиям
		$sql='select distinct e.id_eventtype, et.name, count(*) from events e
		join eventtype et on et.id_eventtype=e.id_eventtype
		where e.datetime>\''.date("d.m.Y H:i:s",strtotime("-1 days")).'\'
		group by e.id_eventtype, et.name';
		$query = DB::query(Database::SELECT, $sql)
		->execute(Database::instance('fb'));
		$res=array();
		foreach ($query as $key => $value)
		{
			$res[$value['ID_EVENTTYPE']]['ID_EVENTTYPE']=$value['ID_EVENTTYPE'];
			$res[$value['ID_EVENTTYPE']]['NAME_EVENT']=iconv('windows-1251','UTF-8',$value['NAME']);
			$res[$value['ID_EVENTTYPE']]['COUNT_EVENT']=$value['COUNT'];
		}
		
		//выбор статистики по аналитике. Выбираются все случаи, кроме правильных проходов и правильных отказов. Список "правильных" проходов и отказов указан в файле конфигурации.
		$analit_ok = Kohana::$config->load('artonitcity_config')->analit_ok;
				
		$sql='select count(*) from events e 
		where e.datetime>\''.date("d.m.Y H:i:s",strtotime("-1 days")).'\'
		and e.datetime<\'Now\'
		and e.analit not in ('. implode(",", $analit_ok) .')
		and e.analit>400';
		
		$res_analit_count = DB::query(Database::SELECT, $sql)
		->execute(Database::instance('fb'))
		->get('COUNT');
		
		return array($res, $res_analit_count);
	}
	
	public function event_unknowcard()// подготовка данных для события 46 неизвестная карта
	{
		$sql='select distinct e.id_card, e.id_dev, d.name, et.name as name_event, count(e.id_event) from events e
			join device d on d.id_dev=e.id_dev
			join eventtype et on et.id_eventtype=e.id_eventtype
			where e.datetime>\''.date("d.m.Y H:i:s",strtotime("-1 days")).'\'
			and e.id_eventtype=46
			group by e.id_card, e.id_dev, d.name , et.name';
		$query = DB::query(Database::SELECT, $sql)
		->execute(Database::instance('fb'));
		//далее необходимо сформировать из этих событий массив для отображения
		$i=0;
		foreach ($query as $key=>$value)
		{
			$res[$value['ID_CARD']]['ID_CARD']=$value['ID_CARD'];
			$res[$value['ID_CARD']]['NAME_EVENT']=iconv('windows-1251','UTF-8',$value['NAME_EVENT']);
			$res[$value['ID_CARD']]['ID_DEV'][$i]['NAME']=iconv('windows-1251','UTF-8',$value['NAME']);
			$res[$value['ID_CARD']]['ID_DEV'][$i]['COUNT']=$value['COUNT'];
			$i++;
		}
		
		return $res;
	}
	
	
	public function event_unknowcard_80()// подготовка данных для события 46 неизвестная карта
	{
		$sql='select distinct e.id_card, e.id_dev, d.name, et.name as name_event, count(e.id_event) from events e
			join device d on d.id_dev=e.id_dev
			join eventtype et on et.id_eventtype=e.id_eventtype
			where e.datetime>\''.date("d.m.Y H:i:s",strtotime("-1 days")).'\'
			and e.id_eventtype=80
			group by e.id_card, e.id_dev, d.name , et.name';
		$query = DB::query(Database::SELECT, $sql)
		->execute(Database::instance('fb'));
		//далее необходимо сформировать из этих событий массив для отображения
		$i=0;
		foreach ($query as $key=>$value)
		{
			$res[$value['ID_CARD']]['ID_CARD']=$value['ID_CARD'];
			$res[$value['ID_CARD']]['NAME_EVENT']=iconv('windows-1251','UTF-8',$value['NAME_EVENT']);
			$res[$value['ID_CARD']]['ID_DEV'][$i]['NAME']=iconv('windows-1251','UTF-8',$value['NAME']);
			$res[$value['ID_CARD']]['ID_DEV'][$i]['COUNT']=$value['COUNT'];
			$i++;
		}
		
		return $res;
	}
	
	
	public function getEventStat()
	{
		$a=$this->event_invalid();
		$a=$this->stat();
		echo Debug::vars('51', $a);
		foreach ($a as $key=>$value)
		{
			//echo Debug::vars('54', $value);
			foreach($value as $aa=>$bb)
			{
				$res=1;
				
			}
			
			
			
		}
		return '25';
		
	}
	
	public function getPhoto($id)
	{
		$sql='select p.photo from people p where p.id_pep='.$id;
		$query = DB::query(Database::SELECT, $sql)
		->execute(Database::instance('fb'))
		->get('PHOTO');
		return $query;
	}
	public function event_invalid($is_right_only = FALSE)// подготовка данных для события 65 недействительная карта
	{
		
		// получаю список контроллеров в режиме ТЕСТ
		
		$query_test_on=Model::factory('stat')->getDeviceInTestMode();// Получил список ID_DEV, работающих в режиме TEST
		
		$sql='select distinct e.ess1, p.surname, p.name as p_name, p.patronymic, e.id_card, e.id_dev, d.name, d."ACTIVE" as dev_active, et.name as name_event, e.analit from events e
            join device d on d.id_dev=e.id_dev
            join eventtype et on et.id_eventtype=e.id_eventtype
            join people p on e.ess1=p.id_pep
			where e.datetime>\''.date("d.m.Y H:i:s",strtotime("-1 days")).'\'
			and e.id_eventtype=65
			and e.analit>0
            group by e.ess1, p.surname, p.name, p.patronymic, e.id_card, e.id_dev, d.name , d."ACTIVE" , et.name, e.analit';
		
		//echo Debug::vars('120', $sql);
		$query = DB::query(Database::SELECT, $sql)
		->execute(Database::instance('fb'));
		$i=0;
		$res=array();
		foreach ($query as $key=>$value)
		{
			if($is_right_only)
			{
				if($this->checkEvents($value['ESS1'], $value['ID_DEV']) == 1){
					$res[$value['ID_DEV']]['DOOR_NAME']=iconv('windows-1251','UTF-8',$value['NAME']);;
			$res[$value['ID_DEV']]['NAME_EVENT']=iconv('windows-1251','UTF-8',$value['NAME_EVENT']);;
			$res[$value['ID_DEV']]['ID_DEV']=$value['ID_DEV'];
			$res[$value['ID_DEV']]['DEV_ACTIVE']=$value['DEV_ACTIVE'];
			$res[$value['ID_DEV']]['MODE_TEST']=0;
			if(array_search(array('ID_DEV'=>$value['ID_DEV']), $query_test_on)) $res[$value['ID_DEV']]['MODE_TEST']=1;
			//echo Debug::vars('131', $query_test_on, $value, $res, $value['ID_DEV'], array_search(array('ID_DEV'=>$value['ID_DEV']), $query_test_on) ); exit;
			$res[$value['ID_DEV']]['USER'][$i]['P_SURNAME']=iconv('windows-1251','UTF-8',$value['SURNAME']);;
			$res[$value['ID_DEV']]['USER'][$i]['P_NAME']=iconv('windows-1251','UTF-8',$value['P_NAME']);;
			$res[$value['ID_DEV']]['USER'][$i]['P_PATRONYMIC']=iconv('windows-1251','UTF-8',$value['PATRONYMIC']);;
			$res[$value['ID_DEV']]['USER'][$i]['P_PATRONYMIC']=iconv('windows-1251','UTF-8',$value['PATRONYMIC']);;
			$res[$value['ID_DEV']]['USER'][$i]['ESS1']=$value['ESS1'];
			
			$res[$value['ID_DEV']]['USER'][$i]['PHOTO']='';//$value['PHOTO'];
			$res[$value['ID_DEV']]['USER'][$i]['ID_CARD']=$value['ID_CARD'];
			$res[$value['ID_DEV']]['USER'][$i]['IS_RIGHT']=$this->checkEvents($value['ESS1'], $value['ID_DEV']);
			//$res[$value['ID_DEV']]['USER'][$i]['IS_RIGHT']=$value['ANALIT'];
				
				}
			
			} else {
			
			$res[$value['ID_DEV']]['DOOR_NAME']=iconv('windows-1251','UTF-8',$value['NAME']);;
			$res[$value['ID_DEV']]['NAME_EVENT']=iconv('windows-1251','UTF-8',$value['NAME_EVENT']);;
			$res[$value['ID_DEV']]['ID_DEV']=$value['ID_DEV'];
			$res[$value['ID_DEV']]['DEV_ACTIVE']=$value['DEV_ACTIVE'];
			$res[$value['ID_DEV']]['MODE_TEST']=0;
			if(array_search(array('ID_DEV'=>$value['ID_DEV']), $query_test_on)) $res[$value['ID_DEV']]['MODE_TEST']=1;
			$res[$value['ID_DEV']]['USER'][$i]['P_SURNAME']=iconv('windows-1251','UTF-8',$value['SURNAME']);;
			$res[$value['ID_DEV']]['USER'][$i]['P_NAME']=iconv('windows-1251','UTF-8',$value['P_NAME']);;
			$res[$value['ID_DEV']]['USER'][$i]['P_PATRONYMIC']=iconv('windows-1251','UTF-8',$value['PATRONYMIC']);;
			$res[$value['ID_DEV']]['USER'][$i]['P_PATRONYMIC']=iconv('windows-1251','UTF-8',$value['PATRONYMIC']);;
			$res[$value['ID_DEV']]['USER'][$i]['ESS1']=$value['ESS1'];
			$res[$value['ID_DEV']]['USER'][$i]['PHOTO']='';//$value['PHOTO'];
			$res[$value['ID_DEV']]['USER'][$i]['ID_CARD']=$value['ID_CARD'];
			$res[$value['ID_DEV']]['USER'][$i]['IS_RIGHT']=$this->checkEvents($value['ESS1'], $value['ID_DEV']);
			}
			$i++;
		}
		return $res;
	}
	
	public function event_invalid_list($id_dev=FALSE)// подготовка данных для события 65 недействительная карта
	{
		
		$sql='select e.id_event, e.datetime, cd.load_time, cd.load_result, cd.devidx,  e.ess1, e.note,   e.id_card, e.id_dev, d.name, et.name as name_event from events e
            join device d on d.id_dev=e.id_dev
            join eventtype et on et.id_eventtype=e.id_eventtype
            left join cardidx cd on cd.id_card=e.id_card and cd.id_dev=e.id_dev
			where e.datetime>\''.date("d.m.Y H:i:s",strtotime("-1 days")).'\'
			and e.id_eventtype=65';
		if($id_dev > 0) $sql = $sql. 'and e.id_dev='. $id_dev;
		$sql = $sql.' order by e.id_eventtype';
		//echo Debug::vars('119', $sql); exit;
		$query = DB::query(Database::SELECT, $sql)
		->execute(Database::instance('fb'));
		$i=0;
		$res=array();
		foreach ($query as $key=>$value)
		{
			
			$res[$key]['ID_EVENT']=$value['ID_EVENT'];
			$res[$key]['DATETIME']=$value['DATETIME'];
			$res[$key]['LOAD_TIME']=$value['LOAD_TIME'];
			$res[$key]['LOAD_RESULT']=iconv('windows-1251','UTF-8',$value['LOAD_RESULT']);
			$res[$key]['DEVIDX']=$value['DEVIDX'];
			$res[$key]['DOOR_NAME']=iconv('windows-1251','UTF-8',$value['NAME']);;
			$res[$key]['NAME_EVENT']=iconv('windows-1251','UTF-8',$value['NAME_EVENT']);;
			$res[$key]['ID_DEV']=$value['ID_DEV'];
			$res[$key]['PEP_NAME']=iconv('windows-1251','UTF-8',$value['NOTE']);;
			$res[$key]['ESS1']=$value['ESS1'];
			//$res[$key]['PHOTO']=$this->getPhoto($value['ESS1']);
			$res[$key]['ID_CARD']=$value['ID_CARD'];
			$analis=$this->checkEvents($value['ESS1'], $value['ID_DEV']);
			$res[$key]['IS_RIGHT']=$analis;
			$res[$key]['TR_COLOR'] ='warning';
			if($analis==0) $res[$key]['TR_COLOR'] ='success';
			$res[$key]['HINT']=$this->hint($analis, $value['DATETIME'], iconv('windows-1251','UTF-8',$value['LOAD_RESULT']), $value['LOAD_TIME']);
			
			$i++;
		}
		//echo Debug::vars('143', $res); exit;
		return $res;
	}
	
	public function hint ($analis, $event_datestamp, $load_result, $load_datestamp)// 24.10.2017 подготовка подсказки при анализе проходов или отказе
	{
		//class="active"-серый, "success" - зеленый, "info" - голубой, "warning" - желтый, "danger" - красный
		$tr_color=array('active'=>'active', 'success'=>'success', 'info'=>'info', 'warning'=>'warning', 'danger'=>'danger');
		$res['id']=-1;
		$res['tr_color']=$tr_color['active'];
		$res['hint']=__('hint_-1');
		if($analis == 0) {
			$res['id']=0;//И не пустило (событие то 65), и не должно было... Все правильно.
			$res['tr_color']=$tr_color['active'];
			$res['hint']=__('hint0');
		}
		if($analis == 1) {
			$res['id']=1;// Должно было пустить... Но далее надо рассмотреть варинаты загрузки карты.
			$res['tr_color']=$tr_color['active'];
			$res['hint']=__('hint1');
		}
		if(strtotime($load_datestamp)> strtotime($event_datestamp)) {
			$res['id']=2;// Не пустило ДО загрузки карты. Это не нарушение.
			$res['tr_color']=$tr_color['warning'];
			$res['hint']=__('hint2');
		}
		if(strtotime($load_datestamp)< strtotime($event_datestamp)) {
			$res['id']=3;// Не пустило ПОСЛЕ загрукзи карты. Это авария!
			$res['tr_color']=$tr_color['danger'];
			$res['hint']=__('hint3');
		}
		if($load_datestamp == NULL and $analis == 0) {
			$res['id']=4;// Отказ правильный. И ходить нельзя, и карта не загружена.
			$res['tr_color']=$tr_color['success'];
			$res['hint']=__('hint4');
		}
		
		if($load_datestamp == NULL and $analis == 1) {
			$res['id']=5;// Ошибка! ХОдить должен, но даже не загружен!!!
			$res['tr_color']=$tr_color['danger'];
			$res['hint']=__('hint5');
		}
		
		
		return $res;
	}
	
	
	public function checkEvents($id_pep, $id_dev)// проверка разрешения пройти указанному id_pep в точке прохода id_dev 0 - нельзя ходить, 1 - можно ходить
	{
		$res=0;
		$sql='select * from ss_accessuser ssu
		join access a on a.id_accessname=ssu.id_accessname
		join card c on c.id_pep=ssu.id_pep
		where ssu.id_pep='.$id_pep.' and a.id_dev='.$id_dev.'
		and c."ACTIVE">0';
		$query = DB::query(Database::SELECT, $sql)
		->execute(Database::instance('fb'))
		->count();
		if ($query>0) $res=1;
		return $res;
	}
	
	
	public function event_select($eventtype)//подготовка событий для указанного события за текущие сутки
	{
		$sql='select e.id_event, e.id_eventtype, e.id_dev, e.datetime, e.id_card, e.note, e.id_pep, e.ess1, e.ess2, e.idsource, e.idserverts, et.name, d.name from events e
join eventtype et on et.id_eventtype=e.id_eventtype
join device d on d.id_dev=e.id_dev
where e.datetime>\''.date("d.m.Y",strtotime("-1 days")).'\'
and e.id_eventtype='.$eventtype;
		
		
		$query = DB::query(Database::SELECT, $sql)
		->execute(Database::instance('fb'));
		
		foreach ($query as $key => $value)
		{
			$res[$value['ID_EVENT']]['ID_EVENT']=$value['ID_EVENT'];
			$res[$value['ID_EVENT']]['ID_EVENTTYPE']=$value['ID_EVENTTYPE'];
			$res[$value['ID_EVENT']]['ID_DEV']=$value['ID_DEV'];
			$res[$value['ID_EVENT']]['DATETIME']=$value['DATETIME'];
			$res[$value['ID_EVENT']]['ID_CARD']=$value['ID_CARD'];
			$res[$value['ID_EVENT']]['ID_CARD']=$value['ID_CARD'];
			$res[$value['ID_EVENT']]['NOTE']=iconv('windows-1251','UTF-8',$value['NOTE']);
			$res[$value['ID_EVENT']]['ID_PEP']=$value['ID_PEP'];
			$res[$value['ID_EVENT']]['ESS1']=$value['ESS1'];
			$res[$value['ID_EVENT']]['ESS2']=$value['ESS2'];
			$res[$value['ID_EVENT']]['IDSOURCE']=$value['IDSOURCE'];
			$res[$value['ID_EVENT']]['IDSERVERTS']=$value['IDSERVERTS'];
		}
		return $res;
		
		
	}
	public function event_people($id_pep, $id_card)//подготовка событий для указанного пользователя
	{
		
		$sql='select e.id_event, e.id_eventtype, e.id_dev, e.datetime, e.id_card, e.note, e.id_pep, e.ess1, e.ess2, e.idsource, e.idserverts, et.name as EVENT_NAME, d.name as DOOR_NAME, e.analit from events e
		join eventtype et on et.id_eventtype=e.id_eventtype
		join device d on d.id_dev=e.id_dev
		where e.datetime>\''.Arr::get($_SESSION, 'peopleEventsTimeFrom', date("d.m.Y",strtotime("-2 days"))).'\'
		and e.datetime<\''.Arr::get($_SESSION, 'peopleEventsTimeTo', date("d.m.Y H:m:s")).'\'
		and e.ess1='.$id_pep.'
		and e.id_card=\''.$id_card.'\'';
		//echo Debug::vars('361', $sql, $_SESSION); exit;
		
		//4/08/2021
		$sql='select e.id_event, e.id_eventtype, e.id_dev, e.datetime, e.id_card, e.note, e.id_pep, e.ess1, e.ess2, e.idsource, e.idserverts, et.name as EVENT_NAME, d.name as DOOR_NAME, e.analit from events e
		join eventtype et on et.id_eventtype=e.id_eventtype
		join device d on d.id_dev=e.id_dev
		where e.datetime>\''.Arr::get($_SESSION, 'peopleEventsTimeFrom', date("d.m.Y",strtotime("-2 days"))).'\'
		and e.datetime<\''.Arr::get($_SESSION, 'peopleEventsTimeTo', date("d.m.Y H:m:s")).'\'
		and e.ess1='.$id_pep;
		
		
		
		$query = DB::query(Database::SELECT, $sql)
		->execute(Database::instance('fb'));
		$res=array();
		foreach ($query as $key => $value)
		{
			$res[$key]['ID_EVENT']=$value['ID_EVENT'];
			$res[$key]['ID_EVENTTYPE']=$value['ID_EVENTTYPE'];
			$res[$key]['ID_DEV']=$value['ID_DEV'];
			$res[$key]['DATETIME']=$value['DATETIME'];
			$res[$key]['ID_CARD']=$value['ID_CARD'];
			$res[$key]['NOTE']=iconv('windows-1251','UTF-8',$value['NOTE']);
			$res[$key]['EVENT_NAME']=iconv('windows-1251','UTF-8',$value['EVENT_NAME']);
			$res[$key]['DOOR_NAME']=iconv('windows-1251','UTF-8',$value['DOOR_NAME']);
			$res[$key]['ID_PEP']=$value['ID_PEP'];
			$res[$key]['ESS1']=$value['ESS1'];
			$res[$key]['ESS2']=$value['ESS2'];
			$res[$key]['IDSOURCE']=$value['IDSOURCE'];
			$res[$key]['IDSERVERTS']=$value['IDSERVERTS'];
			$res[$key]['PASS_ENABLE']=$this->checkEvents($id_pep, $value['ID_DEV']) ;
			$pass_enable = $this->checkEvents($id_pep, $value['ID_DEV']);
			$res[$key]['EVENT_ANALIT'] = 1;// изначально считаем, что есть нарушение прохода.
			//IF($value['ID_EVENTTYPE'] == 65 and $pass_enable == 1) $res[$key]['EVENT_ANALIT'] = 1;// Ошибка! Пользователь может тут ходить.
			//IF($value['ID_EVENTTYPE'] == 50 and $pass_enable ==1) $res[$key]['EVENT_ANALIT'] = 0;// Ошибки нет, проходе действительно запрещен
			if(in_array($value['ANALIT'] , Kohana::$config->load('artonitcity_config')->analit_ok)) $res[$key]['EVENT_ANALIT']=0; // событие относится к разряду штатных, надо подкрасить зеленым цветом
			
			$res[$key]['ANALIT_CODE']=$value['ANALIT'];
			
			$res[$key]['PASS_ENABLE']=$pass_enable ;
		}
		
		return $res;
	}
	
	public function event_door($id_door)//подготовка событий для указанной двери
	{
		
		$sql='select e.datetime, e.id_card, e.id_eventtype,  e.note, et.name, e.id_dev, d.name as dev_name, e.ess1 from events e
			join eventtype et on et.id_eventtype=e.id_eventtype
			join device d on d.id_dev=e.id_dev
			where
			e.datetime > \''.Arr::get($_SESSION, 'doorEventsTimeFrom', date("d.m.Y",strtotime("-1 days"))).'\'
			and e.datetime<\''.Arr::get($_SESSION, 'doorEventsTimeTo', date("d.m.Y")).'\'
			and e.id_dev ='.$id_door;
		//	echo Debug::vars('540', $sql); exit;
		$query = DB::query(Database::SELECT, $sql)
		->execute(Database::instance('fb'));
		$res=array();
		foreach ($query as $key => $value)
		{
			$res[$key]['DATETIME']=$value['DATETIME'];
			$res[$key]['ID_CARD']=$value['ID_CARD'];
			$res[$key]['ID_EVENTTYPE']=$value['ID_EVENTTYPE'];
			$res[$key]['NOTE']=iconv('windows-1251','UTF-8',$value['NOTE']);
			$res[$key]['NAME']=iconv('windows-1251','UTF-8',$value['NAME']);
			$res[$key]['ID_DEV']=$value['ID_DEV'];
			$res[$key]['ID_PEP']=$value['ESS1'];
			$res[$key]['DEV_NAME']=iconv('windows-1251','UTF-8',$value['DEV_NAME']);
			
			
		}
		
		return $res;
	}
	 public function errtz()// подготовка списка событий Сотрудник не пропущен по времени (код события 47)
	 {
		$sql='select e.id_dev, e.datetime, e.id_card, e.note, e.ess1, e.ess2, d.name as door_name, p.surname, p.name, p.patronymic,
		p.note as people_note, 
		o.name as org_name, 
		o2.name as parent2, o3.name as parent3, o4.name as parent4
		from events e
			left join people p on e.ess1=p.id_pep
			join device d on e.id_dev=d.id_dev
			left join organization o on o.id_org=p.id_org
			left join organization o2 on o.id_parent=o2.id_org
				left join organization o3 on o2.id_parent=o3.id_org
				left join organization o4 on o3.id_parent=o4.id_org
			where e.id_eventtype=47
			and e.datetime>\''.date("d.m.Y H:i:s",strtotime("-1 days")).'\'';
		$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'));
			$res=array();
		foreach ($query as $key => $value)
		{
			$res[$key]['ID_DEV']=$value['ID_DEV'];
			$res[$key]['DATETIME']=$value['DATETIME'];
			$res[$key]['ID_CARD']=$value['ID_CARD'];
			$res[$key]['NOTE']=iconv('windows-1251','UTF-8',$value['NOTE']);
			$res[$key]['ESS1']=iconv('windows-1251','UTF-8',$value['ESS1']);
			$res[$key]['ESS2']=$value['ESS2'];
			$res[$key]['DOOR_NAME']=iconv('windows-1251','UTF-8',$value['DOOR_NAME']);
			$res[$key]['NAME']=iconv('windows-1251','UTF-8',$value['NAME']);
			$res[$key]['SURNAME']=iconv('windows-1251','UTF-8',$value['SURNAME']);
			$res[$key]['PATRONYMIC']=iconv('windows-1251','UTF-8',$value['PATRONYMIC']);
			$res[$key]['PEOPLE_NOTE']=iconv('windows-1251','UTF-8',$value['PEOPLE_NOTE']);
			$res[$key]['ORG_NAME']=iconv('windows-1251','UTF-8',$value['ORG_NAME']);
			$res[$key]['ORG_PARENT']= '..\\'
					.iconv('windows-1251','UTF-8', Arr::get($value, 'PARENT4', '..')).'\\'
							.iconv('windows-1251','UTF-8', Arr::get($value, 'PARENT3', '..')).'\\'
									.iconv('windows-1251','UTF-8', Arr::get($value, 'PARENT2', '..')).'\\'
											.iconv('windows-1251','UTF-8', Arr::get($value, 'ORG_NAME', '..'));
		} 
		return $res;
	 }
	 
	 public function test_mode($id_dev=FALSE)// подготовка данных для события 65 недействительная карта
	{
		
		
		
		$sql='select distinct e.id_dev, count(e.id_event),  d.name, d2.name as device_name, et.name as name_event from events e
            join device d on d.id_dev=e.id_dev
            join device d2 on d.id_ctrl=d2.id_ctrl and d2.id_reader is null
			 join eventtype et on et.id_eventtype=e.id_eventtype
            where e.datetime>\''.date("d.m.Y H:i:s",strtotime("-1 days")).'\'
            and e.id_eventtype=145
            group by e.id_dev, d.name, d2.name , et.name';
			
		//echo Debug::vars('119', $sql); exit;
		$res = DB::query(Database::SELECT, $sql)
		->execute(Database::instance('fb'))
		->as_array();
		
		
		//echo Debug::vars('143', $res); exit;
		return $res;
	}
	
	public function analit_list()
	{
		$analit_ok = Kohana::$config->load('artonitcity_config')->analit_ok;
				
		$sql='select e.id_event, e.id_dev, e.datetime, e.id_card, e.note, e.ess1, e.ess2, e.analit, d.name as door_name, et.name as event_name, ci.load_time, ci.load_result from events e
		join device d on d.id_dev=e.id_dev
		join eventtype et on et.id_eventtype=e.id_eventtype
		left join cardidx ci on ci.id_dev=e.id_dev and ci.id_card=e.id_card
		where e.datetime>\''.date("d.m.Y H:i:s",strtotime("-1 days")).'\'
		and e.datetime<\'Now\'
		and e.analit not in ('. implode(",", $analit_ok) .')
		and e.analit>400';
		
		$res = DB::query(Database::SELECT, $sql)
		->execute(Database::instance('fb'))
		->as_array();
		
		
		foreach ($res as $key => $value)
		{
			$res[$key]['NOTE']=iconv('windows-1251','UTF-8',$value['NOTE']);
			$res[$key]['DOOR_NAME']=iconv('windows-1251','UTF-8',$value['DOOR_NAME']);
			$res[$key]['EVENT_NAME']=iconv('windows-1251','UTF-8',$value['EVENT_NAME']);
			
		} 
		//		echo Debug::vars('465', $res); exit;
		return $res;
	
	}
	
	 
}


