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
	
	
	public function action_save_csv() {
		if ($this->request->method() === 'POST') {
			
			// Важно: полностью отключаем все шаблоны
			$this->auto_render = FALSE;
			
			// Не должно быть никакого вывода ДО заголовков!
			
			$big_array = Model::factory('identifier')->getLastEvent();
			$filename = 'export_' . date('Y-m-d_H-i-s') . '.csv';
			
			// 1. Очищаем все буферы вывода
			while (ob_get_level()) {
				ob_end_clean();
			}
			
			// 2. Устанавливаем заголовки
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' . $filename . '"');
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			
			// 3. Выводим BOM
			//echo "\xEF\xBB\xBF";
			
			// 4. Выводим CSV данные
			$output = fopen('php://output', 'w');
			
			foreach ($big_array as $row) {
				fputcsv($output, $row, ';', '"');
			}
			
			fclose($output);
			
			// 5. Завершаем скрипт
			exit;
		}
	}
	
}
