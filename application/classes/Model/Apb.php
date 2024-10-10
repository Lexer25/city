<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_Apb extends Model {
	
	
	
	
	public function update_delay_pass($id_apb, $id_dev)// изменение параметров периметра АПБ
	{
		//echo Debug::vars('10',$id_apb, $id_dev ); exit;
		foreach ($id_dev as $key=>$value)
		{
			if(is_numeric($value))
			{
				$sql='update perimeter_gate pg
						set pg.delay = '.(int)$value.'
						where pg.id_dev='.(int)$key.'
						and pg.id_perimeter='.(int)$id_apb.'
						AND (ID_DB = 1)';
						$query = DB::query(Database::UPDATE, $sql)
						->execute(Database::instance('fb'));
			}
		}
		
	}
	
	
	public function change_config($id_apb, $name, $duration, $is_active)// изменение параметров периметра АПБ
	{
		$sql='UPDATE PERIMETER
			SET NAME = \''.iconv('UTF-8','windows-1251',$name).'\',
			GUEST_DURATION = '.$duration. '
			,ENABLED = '.$is_active. '
			WHERE (ID = '.$id_apb.') AND (ID_DB = 1)';
		
		$query = DB::query(Database::UPDATE, $sql)
		->execute(Database::instance('fb'));
		
	}
	
	
	public function get_people_list_inside($id_apb)// получить список людей внутри указанного apb
	{
		$sql='select pi.id_PERIMETER, pi.id_db, pi.id_pep, pi.enter_time, pi.exit_time, p.surname, p.name, p.patronymic from PERIMETER_inside pi
			join people p on p.id_pep=pi.id_pep
			where pi.id_PERIMETER='.$id_apb;
			
		$sql='select pi.id_PERIMETER, pi.id_db, pi.id_pep, pi2.event_time as enter_time, pi3.event_time as exit_time, p.surname, p.name, p.patronymic, pi.is_enter  from PERIMETER_inside pi
    left join PERIMETER_inside pi2 on pi2.id_pep=pi.id_pep and pi2.id_dev=pi.id_dev and pi2.id_PERIMETER=pi.id_PERIMETER and pi2.is_enter=1
    left join PERIMETER_inside pi3 on pi3.id_pep=pi.id_pep and pi3.id_dev=pi.id_dev and pi3.id_PERIMETER=pi.id_PERIMETER and pi3.is_enter=0
    join people p on p.id_pep=pi.id_pep
    where pi.id_PERIMETER='.$id_apb;
		
		
		$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
		//echo Debug::vars('11', $sql); exit;
		$res=array();
		foreach ($query as $key => $value)
		{
			$res[$key]['ID_PERIMETER']=$value['ID_PERIMETER'];
			$res[$key]['ID_PEP']=$value['ID_PEP'];
			$res[$key]['ENTER_TIME']=$value['ENTER_TIME'];
			$res[$key]['EXIT_TIME']=$value['EXIT_TIME'];
			$res[$key]['SURNAME']=iconv('windows-1251','UTF-8',$value['SURNAME']);
			$res[$key]['NAME']=iconv('windows-1251','UTF-8',$value['NAME']);
			$res[$key]['PATRONYMIC']=iconv('windows-1251','UTF-8',$value['PATRONYMIC']);
			
		}
		return $res;	
	}
	
	
	public function get_info_apb($id_apb)// получить список настроек указанного apb
	{
		$sql='select p.id, p.id_db, p.name, p.guest_duration as DURATION, p.enabled from PERIMETER p where p.id='.$id_apb;
		
		
		$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
		//echo Debug::vars('11', $query); exit;
		foreach ($query as $key => $value)
		{
			$res['ID']=$value['ID'];
			$res['NAME']=iconv('windows-1251','UTF-8',$value['NAME']);
			$res['DURATION']=$value['DURATION'];
			$res['ENABLED']=$value['ENABLED'];
		}
		return $res;	
	}
	
	public function get_list()// получить список настроек apb
	{
		$sql='select p.id, p.id_db, p.name, p.guest_duration as DURATION, p.enabled from PERIMETER p';
		
		
		$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
		//echo Debug::vars('11', $query); exit;
		foreach ($query as $key => $value)
		{
			$res[$key]['ID']=$value['ID'];
			$res[$key]['NAME']=iconv('windows-1251','UTF-8',$value['NAME']);
			$res[$key]['DURATION']=$value['DURATION'];
			$res[$key]['ENABLED']=$value['ENABLED'];
			
			
		}
		return $res;	
	}
	
	
	public function get_list_dev($id_apb)// получить список точек прохода для указанного периметра
	{
		$res=array();
		$sql='select pg.id_PERIMETER, pg.id_dev, pg.id_db, pg.is_enter, p.name, d.id_dev, d.name, pg.delay from PERIMETER_gate pg
			join PERIMETER p on p.id=pg.id_PERIMETER
			join device d on d.id_dev=pg.id_dev
			where pg.id_PERIMETER='.$id_apb;
		
		
		$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
		//echo Debug::vars('39', $query); exit;
		foreach ($query as $key => $value)
		{
			$res[$key]['ID_PERIMETER']=$value['ID_PERIMETER'];
			$res[$key]['ID_DEV']=$value['ID_DEV'];
			$res[$key]['IS_ENTER']=$value['IS_ENTER'];
			$res[$key]['NAME']=iconv('windows-1251','UTF-8',$value['NAME']);
			$res[$key]['DELAY']=$value['DELAY'];
						
		}
		return $res;	
	}
	
	public function get_dev_info($id_dev,$id_apb)// получить информацию по указанной точке прохода apb
	{
		$res=array();
		$sql='select pg.id_PERIMETER, pg.id_dev, pg.id_db, pg.is_enter, p.name from PERIMETER_gate pg
            join PERIMETER p on p.id=pg.id_PERIMETER
            where pg.id_PERIMETER='.$id_apb.' 
            and pg.id_dev='.$id_dev;
				
		$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
		//echo Debug::vars('39', $query); exit;
		foreach ($query as $key => $value)
		{
			$res['ID_PERIMETER']=$value['ID_PERIMETER'];
			$res['ID_DEV']=$value['ID_DEV'];
			$res['IS_ENTER']=$value['IS_ENTER'];
			$res['NAME']=iconv('windows-1251','UTF-8',$value['NAME']);
						
		}
		return $res;	
	}
	
	
	
	public function get_door_list_not_apb($id_apb)// получить список точек прохода для добавления в периметры
	{
		$res=array();
		
		/* $sql='select d.id_dev, d.name from device d
			left join PERIMETER_gate pg on pg.id_dev=d.id_dev and pg.id_PERIMETER='.$id_apb.
			'where pg.id_dev is null
			and d.id_reader is not null
			order by d.name'; */
		
		$sql='select d.id_dev, d.name from device d
			left join PERIMETER_gate pg on pg.id_dev=d.id_dev
			where pg.id_dev is null
			and d.id_reader is not null
			order by d.name';
		
		
		$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
		//
		foreach ($query as $key => $value)
		{
			$res[$value['ID_DEV']]=iconv('windows-1251','UTF-8',$value['NAME']);
						
		}
		//echo Debug::vars('39', $res); exit;
		return $res;	
	}
	
	
	
	
	public function add_apb($add_apb)
	{
		$sql='insert into PERIMETER (id_db, name, guest_duration, enabled) values (1, \''.iconv('UTF-8','windows-1251',$add_apb).'\', 5, 1)';
		//echo Debug::vars('180', $sql); exit;
		try
				{
				$query = DB::query(Database::INSERT, $sql)
				->execute(Database::instance('fb'));
				} catch (Exception $e) {
				}
	}
	
	public function add_door_apd($id_dev, $isEnter, $id_apb)// управление точками прохода в таблице апб
	{
		
		$sql='INSERT INTO PERIMETER_GATE (ID_PERIMETER,ID_DEV,ID_DB,IS_ENTER) VALUES ('.$id_apb.','.$id_dev.',1,'.$isEnter.');';
		//echo Debug::vars('180', $sql); exit;
		try
				{
				$query = DB::query(Database::INSERT, $sql)
				->execute(Database::instance('fb'));
				} catch (Exception $e) {
				}
	}
	
	public function del_door_apd($id_dev, $id_apb)// управление точками прохода в таблице апб
	{
		$sql='delete from PERIMETER_gate pg
			where pg.id_PERIMETER='.$id_apb.
			'and pg.id_dev='.$id_dev;
		$query = DB::query(Database::DELETE, $sql)
		->execute(Database::instance('fb'));	
			
		
	}
	
	
	public function del_apb($del_apb)
	{
		/*
		$sql='delete from PERIMETER where id='.$del_apb;
		//echo Debug::vars('46', $sql); exit;
		$query = DB::query(Database::DELETE, $sql)
		->execute(Database::instance('fb'));
		*/
		$sql='UPDATE PERIMETER
			SET ENABLED = 0
			WHERE (ID = '.$del_apb.') AND (ID_DB = 1)';
		
		$query = DB::query(Database::UPDATE, $sql)
		->execute(Database::instance('fb'));
		
		
	}
	
	public function clear_perimeter_inside($add_apb)
	{
		$sql='delete from PERIMETER_inside pi
		where pi.id_PERIMETER='.$add_apb;
		//echo Debug::vars('46', $sql); exit;
		$query = DB::query(Database::DELETE, $sql)
		->execute(Database::instance('fb'));
	}
	
	
	
	
}
