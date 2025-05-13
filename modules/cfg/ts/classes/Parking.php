<?php

/**
* @package    ParkResident/Parking
 * @category   Base
 * @author     Artonit
 * @copyright  (c) 2025 Artonit Team
 * @license    http://artonit/ru 
 
 */

/* Parking 
Класс описывает парковочную площадь - территорию, где находятся парковочные места.


*/

class Parking
{
	public $name;
	public $id;
	public $is_active;
	public $created;
	public $parent;// родительская резиденция.
	public $modify;//дата последнего изменения
	public $mess;//сообщения всякие
	public $count;//количество машиномест

	
	
	
	public function __construct($id=null)
    {
       if(!is_null($id))//если указан id, то создаю экземпляр класса с данными из БД.
	   {
	   $this->id = $id;
		$sql='select hlr.id, hlr.name, hlr.enabled, hlr.created, hlr.modify, hlr.parent, hlr.maxcount from hl_parking hlr where hlr.id ='.$this->id;
		//echo Debug::vars('30', $sql);exit;
		try
		{
			$query = Arr::flatten(DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array());
			$this->name=Arr::get($query, 'NAME');
			$this->is_active=Arr::get($query, 'ENABLED');
			$this->created=Arr::get($query, 'CREATED');
			$this->modify=Arr::get($query, 'MODIFY');
			$this->parent=Arr::get($query, 'PARENT');
			$this->count=Arr::get($query, 'MAXCOUNT');
			
			
			//echo Debug::vars('23', $sql, $query, $this); exit;
			
		} catch (Exception $e) {
			////echo Debug::vars('30', $sql, $e->getMessage()); exit;
			Log::instance()->add(Log::DEBUG, 'Line 40 '. $e->getMessage());
		
		}
	   } else { // если не указан id, то создаю пустой экземпляр класса
			
	   }
	}
	
	/*
	26.08.2023
	Сохранение данных
	*/
	
	public function add()
	{
		//echo Debug::vars('61', $this->name);exit;
		$sql='INSERT INTO HL_parking (NAME, PARENT, ENABLED, MAXCOUNT)
			values (\''.$this->name.'\', '.$this->parent.', '.$this->is_active.', '.$this->count.')';
			
			
		//echo Debug::vars('783', $sql);exit;
		try
				{
				$query = DB::query(Database::INSERT, iconv('UTF-8','windows-1251',$sql))
				->execute(Database::instance('fb'));
				return true;
				} catch (Exception $e) {
					Log::instance()->add(Log::NOTICE, '794 '. $e->getMessage());
					return false;
				}
		return;
	}
	
	/*
	26.08.2023
	Изменение данных для указанного id
	*/
	public function update()
	{
		//echo Debug::vars('36', $this->name, $this->standalone);
		
		$sql='UPDATE HL_PARKING
				SET NAME = \''.$this->name.'\',
				ENABLED = '.$this->is_active.',
				PARENT = '.$this->parent.',
				MAXCOUNT = '.$this->count.'
			WHERE (ID = '.$this->id.')';
		Log::instance()->add(Log::DEBUG, 'Line 101 '. $sql);
		//echo Debug::vars('65', $sql); exit;
		try
			{
			$query = DB::query(Database::UPDATE, iconv('UTF-8', 'CP1251',$sql))
			->execute(Database::instance('fb'));
			return true;
			} catch (Exception $e) {
				Log::instance()->add(Log::DEBUG, 'Line 112 '. $e->getMessage());
				
				$this->mess=$e->getMessage();
				return 	false;
			}
	}
	
	
	/*
	26.08.2023
	Удаление указанного ЖК по его id
	*/
	public function del()
	{
		//echo Debug::vars('36', $this->name, $this->standalone);
		
		$sql='delete from hl_parking hlr
			where hlr.id='.$this->id;
		Log::instance()->add(Log::DEBUG, 'Line 72 '. $sql);
		//echo Debug::vars('65', $sql); exit;
		try
			{
			$query = DB::query(Database::DELETE, iconv('UTF-8', 'CP1251',$sql))
			->execute(Database::instance('fb'));
			
			return true;
			
			} catch (Exception $e) {
				Log::instance()->add(Log::DEBUG, 'Line 139 '. $e->getMessage());
				$this->mess=$e->getMessage();	
				return false;				
			}
	}
	
	
   
}
