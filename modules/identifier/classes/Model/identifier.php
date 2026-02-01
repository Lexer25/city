<?php defined('SYSPATH') OR die('No direct access allowed.');

/**31.01.2026 Модель для отображения состояние идентификаторов.
*/
class Model_identifier extends Model
{
	public $mess;//сообщения разные
	
	public function getLastEvent()
	{
		
	$listIdentifier=array();//начальные значения пустой массив	
		//получаю массив: идентификатор - дата последнего прохода
		$sql='select e.id_card, max(e.datetime) from events e
		where e.id_eventtype in (46, 50, 65, 70, 71)
		group by e.id_card';
		
		$listWhoGo = array_column(DB::query(Database::SELECT, iconv('UTF-8', 'CP1251',$sql))
					->execute(Database::instance('fb'))
					->as_array(), null, 'ID_CARD');
		//echo Debug::vars('17 кто ходил', $listWhoGo);//exit;
	//теперь получаю список всех карт
	$sql='select
    c.id_card
    ,c.timestart
    ,c.timeend
    ,c."ACTIVE"
    ,c.id_cardtype
    ,ct.smallname  as idtype
    ,c.createdat
    ,p.id_pep
    ,p.surname||\' \'||p.name||\' \'||p.patronymic as fio
    ,o.id_org
    ,o.name as orgname
    ,o.id_parent
    ,o2.name as orgparentname
     from card c
     join people p on c.id_pep=p.id_pep
     join organization o on p.id_org=o.id_org
     join organization o2 on o2.id_org=o.id_parent
     join cardtype ct on c.id_cardtype=ct.id';
	$listIdentifier = array_column(DB::query(Database::SELECT, iconv('UTF-8', 'CP1251',$sql))
					->execute(Database::instance('fb'))
					->as_array(), null, 'ID_CARD');

		
	//теперь для каждого элемента добавляю время прохода
	foreach ($listIdentifier as &$key)
	{
		//echo Debug::vars('45', $key);//exit;
		$key['lastevent']=Arr::get(Arr::get($listWhoGo,Arr::get($key,'ID_CARD')), 'MAX');
		//echo Debug::vars('50', $key);exit;
		
	}
		unset($key);
		
			
		//	echo Debug::vars('55', $listIdentifier);exit;
	return $listIdentifier;		
			
			
		//теперь из этих массивов мне надо выбрать ключи, у которых нет отметы о выходе
		
	}
	
	
	/**1.02.2026 функция устанавилвает ACTIVE=0 для указанного массива карт.
		*/	
	public function setUnactive($cards)
	{
		$sql=__('update card c 
			set c."ACTIVE"=0 
			where c.id_card in (:card_array)
			', array(
			':card_array'=>implode(",", $cards)));
			
		//echo Debug::vars('77', $sql);exit;	
		try
			{
			$query = DB::query(Database::UPDATE, $sql)
			->execute(Database::instance('fb'));
			return true;
			} catch (Exception $e) {
				Log::instance()->add(Log::DEBUG, $e->getMessage());
				$this->mess=$e->getMessage();
				return 	false;
			}
			
		
	}
}
	

