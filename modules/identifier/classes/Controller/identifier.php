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
	
	public function action_control()
	{
		//echo Debug::vars('39', $_POST);exit;
		$post=Validation::factory($_POST);
		$post->rule('identifier', 'not_empty')
				 ->rule('todo', 'not_empty')
				->rule('todo', 'in_array', array(':value', array('unactive', 'delete')));
		if($post->check())
		{
			switch(Arr::get($post, 'todo')){
				case 'unactive'://вызов метода сделать карту неактивной.
					//делю массив на блоки по 1024 записи - более в параметры SQL передавать нельзя.
					$chunks = array_chunk(Arr::get($post, 'identifier'), 1024);

					foreach ($chunks as $chunk) {
						//вызываю метод unactive
						$model=Model::factory('identifier');
						if($model->setUnactive($chunk))
						{
							$result[]='OK';
							
						} else {
							
							$result[]='err '. $model->mess;
						};
						
						
					}
				
				break;
				case 'delete'://вызов метода удаления карт
				
				
				break;
				
			}
			
			
		}
		$this->redirect('identifier');
	}
	
	
}
