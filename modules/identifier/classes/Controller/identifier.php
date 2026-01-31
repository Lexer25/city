<?php defined('SYSPATH') or die('No direct script access.');

/**31.01.2025 класс identifier задуман как набор методов, позволяющих работать с идентификаторами.
* задача минимуму - поиск "мертвых" душ.
//  https://localhost/city/index.php/identifier
*/

class Controller_identifier extends Controller_Template {
   public $template = 'template';
   //Широки шаблон
   //для использьвания необходимо указать 
   //$this->template = View::factory($this->template_width);
   public $template_width = 'template_width';
   
  	
	public function before()
	{
			
			parent::before();
			$session = Session::instance();
		
	}
	
	public function action_index()
	{
		
		$identifier=Model::factory('identifier');
		$list=$identifier->getLastEvent();//выбор всех карт с указанием последней даты прохода
		
		$content = View::factory('identifier/index', array(//начальная страница для работы с идентификаторами.
			'list'=>$list,
		));
        $this->template->content = $content;
		
	}
	
	
	
}
