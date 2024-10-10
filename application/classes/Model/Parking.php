<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_Parking extends Model
{
	
	public function event_people ($search) // Сообщения об ошибках паркинга
	{
	if ($search == NULL) return NULL;
	//echo Debug::vars('9', strlen($search)); //exit;
	
	$sql='select pi.*, p.name from parking_inside pi
			join parking p on pi.id_parking=p.id
		where pi.id_pep='.$search;
		$query = DB::query(Database::SELECT, iconv('UTF-8','windows-1251',$sql))
			->execute(Database::instance('fb'))
			->as_array();
	//echo Debug::vars('28', $query); exit;

	
	$res=array();
		foreach ($query as $key=>$value)
		{
			foreach ($value as $name=>$data)
				{
					
					if($name=='NAME' or $name=='DEVICE_NAME' or $name=='SERVER_NAME')
						{ $res[$key][$name]=iconv('windows-1251','UTF-8',$data);
						
						} else {
						
						$res[$key][$name]=$data;
						}
				}

		}
		//$res='About parking';
		
	return $res;
	}
	
	
	
	public function parking_error($id_pep)//полученние информации о нарушениях правил парковки
	{
		$res=__('no_parking_errors');
		$sql='select p.sysnote from people p where p.id_pep='.$id_pep;
 
		$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->get('SYSNOTE', '--');
	
		//echo Debug::vars('52', $query); //exit;
		if (!empty($query))
		{
			
			$res=iconv('windows-1251','UTF-8',$query);
			$order   = array("\r\n", "\n", "\r");
			$replace = '<br />';
			$res= str_replace($order, $replace, $res);
		}
		return $res;
	}

	
}
	

