<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_Skud extends Model
{
	
	public function getSkudList()//получение списка объектов СКУД
	{
		$acl=array(1,2,3,4,5,6,7);
		$config_skud=Arr::get(Kohana::$config->load('skud'),'skud_list');
		$alarm_event=Arr::get(Kohana::$config->load('artonitcity_config'),'analit_err');
		//echo Debug::vars('9', $config_skud); //exit;
		
		foreach ($config_skud as $key => $value)
		//foreach ($acl as $key)
		{
			
// HY000 - ошибка Неизвестная колонка в таблице
			if(Arr::get($value, 'is_active', 1))

			{
				$t1=microtime(1);
				//echo Debug::vars('19', 'time:'.(microtime(1)-$t1), $value);
				try {
					$dbconfig=Arr::get($value, 'fb_connection');
					$query = DB::query(Database::SELECT, 'SELECT COUNT (*) FROM card')
					->execute(Database::instance('fbi', $dbconfig))
					->get('COUNT');
					//echo Debug::vars('25', $query);exit;
					if ($query)
					{
						$config_skud[$key]['count_card'] = $query; 
						$config_skud[$key]['db_connect'] = __('ok'); 
						//================
						$query = DB::query(Database::SELECT, 'select count(*) from events e
							where e.analit in ('.implode(",",$alarm_event).')
							and e.datetime > current_date-1 ')
						->execute(Database::instance('fbi', $dbconfig))
						->get('COUNT');
						$config_skud[$key]['err_657'] = $query;
						
						//===================
						$query = DB::query(Database::SELECT, 'select * from setting s where s.name=\'skud_name\'')
						->execute(Database::instance('fbi', $dbconfig))
						->get('VALUE_STR');
						$config_skud[$key]['nameSkud'] = iconv('windows-1251','UTF-8',$query);
						$config_skud[$key]['time_exec'] = (microtime(1)-$t1); 
					}
											
				} catch (Exception $e)  {
					$config_skud[$key]['db_connect'] = $e->getMessage();
					//$config_skud[$key]['count_card'] = $e->getMessage();
					$config_skud[$key]['time_exec'] = (microtime(1)-$t1); 
				}
				Database::instance('fbi', $dbconfig)->disconnect();
			} else {
					$config_skud[$key]['count_card'] = __('not_active');
					$config_skud[$key]['err_657']= __('not_active');
					$config_skud[$key]['nameSkud'] = __('not_active');
					$config_skud[$key]['time_exec'] = (microtime(1)-$t1);
					$config_skud[$key]['db_connect'] = __('not_active');
					
				
			}
		}
		return $config_skud;
		
	}
	
	
	
	
	
}
