    <?php defined('SYSPATH') or die('No direct script access.');
     
    /**
     * 20.09.2023 Заполенние статистики по контроллерам:
		- версия, 
		- состояние связи (есть-нет),
		- количество карт по каналам
     
	 */
	 
     
    class Task_StatDevice extends Minion_Task {
		
		
		 protected $_options = array(
        // param name => default value
        'id_ts'   => '2',
       
		);
        
        protected function _execute(array $params)
        {
		
			Model::Factory('Device')->checkStatus(Arr::get($params, 'id_ts'));
		
		
		
		}
    }