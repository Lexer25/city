<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
* @package    ParkResident/Parking
 * @category   Base
 * @author     Artonit
 * @copyright  (c) 2025 Artonit Team
 * @license    http://artonit/ru 
 
 */

class Model_devices extends Model {
	
	
	 public $ID_SERVER;
     public $NAME;
     public $IP;
     public $PORT;
     public $ACTIVE;
     public $ID_TYPE;
     public $NAMETYPE;
		
	public function get_list()// получить список всех зарегистрированных Транспортных серверов
	{
		$res=array();
				
		$sql='select s.id_server, s.name, s.ip, s.port, s."ACTIVE", stl.id_type,  st.name as nameType from server s
			join servertypelist stl on stl.id_server=s.id_server
			join servertype st on st.id=stl.id_type';
		
		
		$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
			
		return $query;	
	}
	
	
	public function get_list_type()// получить список типов Транспортных серверов
	{
		$res=array();
				
		$sql='select * from servertype';
		
		
		$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
			
		return $query;	
	}
	
	
	
	
	
	//==============================================
	/**
	 * получить список всех парковочных площадок с названиями.
	 * Метод сделан для удобной организации select
	 * @param   void
	 * @return  array id_parking, name, parent
	 */
	public function get_list_for_select()// получить список всех парковочных площадок
	{
		$res=array();
				
		$sql='select hlr.id, hlr.name, hlr.parent from hl_parking hlr';
		$sql='select hlr.id, hlr.name from hl_parking hlr';
		
		
		$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
		$res=array();
		if($query)
		{
			foreach($query as $key=>$value)
			{
				$res[Arr::get($value, 'ID')] = iconv('windows-1251','UTF-8', Arr::get($value, 'NAME'));
				
			}
		}
			
		return $res;	
	}
	
	
	
	
	public function get_list_for_parent($parent)// получить список парковочных площадок для указанного родителя
	{
		$res=array();
				
		$sql='select hlr.id from hl_parking hlr
		where hlr.parent='.$parent;
		
		
		$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
	//echo Debug::vars('11',$sql, $query); exit;
		
		return $query;	
	}
	
	public function getCount($parent)// получить количество машиномест для указанной парковки
	{
		$res=array();
				
		$sql='select hlr.id from hl_parking hlr
		where hlr.parent='.$parent;
		
		
		$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
	//echo Debug::vars('11',$sql, $query); exit;
		
		return $query;	
	}
	
	
	
}
