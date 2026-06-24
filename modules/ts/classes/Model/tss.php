<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
* @package    ParkResident/Parking
 * @category   Base
 * @author     Artonit
 * @copyright  (c) 2025 Artonit Team
 * @license    http://artonit/ru 
 
 */

class Model_tss extends Model {
	
	
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
    
			// Преобразуем только строковые поля
			foreach ($query as &$row) {
				$row['NAME'] = iconv('Windows-1251', 'UTF-8//IGNORE', $row['NAME']);
				$row['NAMETYPE'] = iconv('Windows-1251', 'UTF-8//IGNORE', $row['NAMETYPE']);
			}
			return $query;
	}
	
	
	public function get_list_type()// получить список типов Транспортных серверов
	{
		$res=array();
				
		$sql='select * from servertype';
		
		
		$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
		foreach ($query as &$row) {
				$row['NAME'] = iconv('Windows-1251', 'UTF-8//IGNORE', $row['NAME']);
				$row['DESCRIPTION'] = iconv('Windows-1251', 'UTF-8//IGNORE', $row['DESCRIPTION']);
			}
			return $query;
			
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
	
	
	public function get_server_by_id($id)
	{
		$sql = 'select s.id_server, s.name, s.ip, s.port, s."ACTIVE", stl.id_type, st.name as nameType 
				from server s
				join servertypelist stl on stl.id_server = s.id_server
				join servertype st on st.id = stl.id_type
				where s.id_server = '.$id;
		
		$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
		
		if (!empty($query)) {
			$row = $query[0];
			// Преобразуем кодировку
			$row['NAME'] = iconv('Windows-1251', 'UTF-8//IGNORE', $row['NAME']);
			$row['NAMETYPE'] = iconv('Windows-1251', 'UTF-8//IGNORE', $row['NAMETYPE']);
			return $row;
		}
		
		return null;
	}
	
	
	
	
	public function update_server_by_id($data)
	{
		echo Debug::vars('161', Arr::get($data, 'name'));exit;
		$sql = 'select s.id_server, s.name, s.ip, s.port, s."ACTIVE", stl.id_type, st.name as nameType 
				from server s
				join servertypelist stl on stl.id_server = s.id_server
				join servertype st on st.id = stl.id_type
				where s.id_server = '.$id;
		
		$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
		
		if (!empty($query)) {
			$row = $query[0];
			// Преобразуем кодировку
			$row['NAME'] = iconv('Windows-1251', 'UTF-8//IGNORE', $row['NAME']);
			$row['NAMETYPE'] = iconv('Windows-1251', 'UTF-8//IGNORE', $row['NAMETYPE']);
			return $row;
		}
		
		return null;
	}
	
	
	
	public function add_server($data)
	{
		echo Debug::vars('187', $data);//exit;
		
		
		$id_server = DB::query(Database::SELECT, 'select id_server from  SERVER_GETID(1)')
                ->execute(Database::instance('fb'))
				->get('ID_SERVER');
		
		$sql = 'INSERT INTO SERVER (ID_SERVER, ID_DB, NAME, IP, PORT, "ACTIVE") VALUES (' .
            (int)$id_server . ', ' .
            (int)Arr::get($data, 'ID_DB') . ', ' .
            '\'' . addslashes (Arr::get($data,'name')) . '\', ' .
            (int) Arr::get($data,'ip') . ', ' .
            (int) Arr::get($data,'port') . ', ' .
            (int) Arr::get($data,'ACTIVE', 1) . ')';
			
			//echo Debug::vars('195', $sql);exit;
			
          try {
            DB::query(Database::UPDATE, iconv('UTF-8', 'Windows-1251', $sql))
                ->execute(Database::instance('fb'));
            
            return true;
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error updating table server: ' . $e->getMessage());
            return false;
        }
		
		return null;
	}
	
	
	
	
	
	
	
}
