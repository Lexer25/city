<?php defined('SYSPATH') OR die('No direct access allowed.');

/**31.12.2025 Модель для отображения состояние контроллеров.
*/
class Model_Dev extends Model
{
	
	public function getDevData()
	{
		
		$sql='select d.id_dev, d.id_reader, d.name as doorName, d.netaddr, d."ACTIVE", d2.name as devName,  s.id_server, s.name as serverName, std.facts, std.time_insert from device d
			join device d2 on d2.id_ctrl=d.id_ctrl and d2.id_reader is null
			join server s on d2.id_server=s.id_server
			left join st_data std on d.id_dev=std.id_dev
			where d.id_reader is not null';
		$query = DB::query(Database::SELECT, iconv('UTF-8', 'CP1251',$sql))
					->execute(Database::instance('fb'))
					->as_array();
	
		return $query;
	}
	
	/**3.01.2026 сборка данных для вывода на экран построчно
	*/
	
	public function getDevDataDetail()
	{
	//массив точек прохода 
	$sql='select d.id_dev, d.id_reader, d.name, d.netaddr, d."ACTIVE", d2.id_dev as parentId, d2.name as parentName,  s.id_server, s.name as serverName from device d
		join device d2 on d2.id_ctrl=d.id_ctrl and d2.id_reader is null
		left join server s on d2.id_server=s.id_server
		where d.id_reader is not null
		order by d.id_dev';
	
	$sql='select d.id_dev, d.id_reader, d.name, d.netaddr, d."ACTIVE", d2.id_dev as parentId, d2.name as parentName,  s.id_server, s.name as serverName, std.facts as dbCount from device d
        join device d2 on d2.id_ctrl=d.id_ctrl and d2.id_reader is null
        left join server s on d2.id_server=s.id_server
        left join st_data std on d.id_dev=std.id_dev and std.id_param in (8)
        where d.id_reader is not null
        order by d.id_dev';
	
	
	
	$query = DB::query(Database::SELECT, iconv('UTF-8', 'CP1251',$sql))
					->execute(Database::instance('fb'))
					->as_array();
					
	$result=array_column($query, null, 'ID_DEV');

	//массив данных для контроллеров 
	$sql='select std.id_dev, std.facts, std.time_insert from st_data std
	join device d on d.id_dev=std.id_dev
	and d.id_reader is null
	and std.id_param in (113)';
	$queryDev = DB::query(Database::SELECT, iconv('UTF-8', 'CP1251',$sql))
				->execute(Database::instance('fb'))
				->as_array();
						
	$temp=array_column($queryDev, null, 'ID_DEV');
	//echo Debug::vars('50', $temp);exit;
	
	foreach($result as $key=>$value)
	{
		
		/* if(Arr::get($value, 'PARENTID')==743){
			
			echo Debug::vars('54', $value);//exit;
			echo Debug::vars('55',Arr::get(Arr::get($temp, Arr::get($value, 'PARENTID')), 'FACTS'));exit;
		} */
		$result[$key]['facts2']=Arr::get(Arr::get($temp, Arr::get($value, 'PARENTID')), 'FACTS');
		
	}
	
	
	//сведение данных в один массив

	return $result;
	
	}
	
}
	

