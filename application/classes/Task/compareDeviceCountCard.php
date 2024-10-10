    <?php defined('SYSPATH') or die('No direct script access.');
     
    /**
     * 
     * c:\xampp\php\php.exe c:\xampp\htdocs\city\modules\minion\minion --task=compareDeviceCountCard --id_door=2
     * @author 
     */
     
    class Task_compareDeviceCountCard extends Minion_Task {
		
		
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
				Model::Factory('Device')->check_and_delete_card_from_device($id_door);// 18.05.2021 исправлено для правильной записи результата в лог			
			
			Log::instance()->add(Log::NOTICE, 'Проверка в контроллере '.$id_door.' завершена. за время '.(microtime(true)-$ttt1));	

		
		}
    }