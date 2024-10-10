    <?php defined('SYSPATH') or die('No direct script access.');
/*
Отладка сбора информации о состоянии точек прохода
c:\xampp\php\php.exe c:\xampp\htdocs\city\modules\minion\minion --task=test --id_dev=469
*/
 
    class Task_test extends Minion_Task {
		
		    protected $_options = array(
        // param name => default value
     
        'id_dev'   => '107',
		);
	
        
        protected function _execute(array $params)
        {
			Model::factory('Device')->getStatForOneController(Arr::get($params, 'id_dev'));
			Log::instance()->add(Log::DEBUG, 'Сбор данных для контроллера id_dev= '.Arr::get($params, 'id_dev').' нет связи.');
        }
		
    }
	