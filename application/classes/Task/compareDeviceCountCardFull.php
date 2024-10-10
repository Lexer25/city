    <?php defined('SYSPATH') or die('No direct script access.');
     
    /**
     * Новый вариант сравнения карт.
	 * сначала читаю все карты из контроллера в массив
	 * затем читаю список карт в контроллер из базы данных
	 * затем формирую два массива: есть в БД, но нет в контроллере и есть в контроллере, но нет в БД
     * c:\xampp\php\php.exe c:\xampp\htdocs\city\modules\minion\minion --task=compareDeviceCountCardFull --id_door=469
     * @author 
     */
     
    class Task_compareDeviceCountCardFull extends Minion_Task {
		
		
		  protected $_options = array(
        // param name => default value
     
        'id_door'   => '2', // id точки прохода
		);
		
        
        protected function _execute(array $params)
        {
			$id_door=Arr::get($params, 'id_door', -1);
			$ttt1=microtime(true);
		
				
				Log::instance()->add(Log::NOTICE, 'Проверка в контроллере id_door='.$id_door.' начата.');	
			

				Model::Factory('Device')->insertStatusIdDev_arr(array($id_door));
				Model::Factory('Device')->readkey_once($id_door);// 			
			
			Log::instance()->add(Log::NOTICE, 'Проверка в контроллере '.$id_door.' завершена. за время '.(microtime(true)-$ttt1));	

		
		}
    }