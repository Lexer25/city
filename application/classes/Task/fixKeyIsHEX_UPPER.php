    <?php defined('SYSPATH') or die('No direct script access.');
     
    /**
     * 31.08.2024 проверка card в таблице card. Номера должны строго содержать только цифры и большие буквы HEX
		
     c:\xampp\php\php.exe c:\xampp\htdocs\city\modules\minion\minion --task=collectStatFromDevice --countDevice=10
	 */
	 
     
    class Task_fixKeyIsHEX_UPPER extends Minion_Task {
		
		
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