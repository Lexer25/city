    <?php defined('SYSPATH') or die('No direct script access.');
     
    /**
     * 20.09.2023 Заполенние статистики: количество карт для каждой точки прохода, данные берутся из БД СКУД.
		
     
	 */
	 
     
    class Task_fixKeyOnDBCount extends Minion_Task {
		
		
		 protected $_options = array(
        // param name => default value
        //'id_ts'   => '2',
       
		);
        
        protected function _execute(array $params)
        {
			//Model::Factory('Stat')->fixKeyOnDBCount();
			Model::Factory('Stat')->fixKeyOnCardidx();
			Model::Factory('Stat')->fixOverTimeKeyOnDBCount();
		}
    }