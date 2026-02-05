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
   
   
  public $options1 = array(
				'cardNoEvent' => 'Список идентификаторов, не имеющих отметки о событиях.',
				'cardNoEventDate' => 'Список идентификаторов, не имеющих отметки о событиях до указанной даты.',
				'allCards' => 'Список всех идентификаторов.',
				'expiredCards' => 'Список всех идентификаторов срок действия закончился',
				'inactiveCards' => 'Список всех неактивных идентификаторов',
				'invalidFormat' => 'Список всех идентификаторов неправильного формата'
			);
   
  public $options = array(
				'cardNoEvent' => 'Список идентификаторов, не имеющих отметки о событиях.',
				'allCards' => 'Список всех идентификаторов.',
				//'cardNoEventDate' => 'Список идентификаторов, не имеющих отметки о событиях до указанной даты.',
				
			);
   
  	
	public function before()
	{
			
			parent::before();
			$session = Session::instance();
			$this->template = View::factory($this->template_width);//во всю ширину экрана
		
	}
	
	public function action_index()
	{
		
	
		
		$content = View::factory('identifier/welcome', array(//начальная страница для работы с идентификаторами.
		'options'=>$this->options,
			
		));
        $this->template->content = $content;
		
	}
	
	public function action_cardNoEventDate()
	{
		
		$identifier=Model::factory('identifier');
		return $identifier->getLastEvent();//выбор всех карт с указанием последней даты прохода
		// $content = View::factory('identifier/index', array(//начальная страница для работы с идентификаторами.
			// 'list'=>$list,
		// ));
        // $this->template->content = $content;
		
	}
	
	
	public function action_action()
	{
		//echo Debug::vars('39', $_POST);//exit;
				

			// Создаем валидацию
			$post = Validation::factory($_POST)
				->rule('todo', 'not_empty', array(':value'))
				->rule('todo', 'in_array', array(':value', array_keys($this->options1)));

			// Если форма отправлена и нажата кнопка карт без событий до даты
			if (isset($_POST['cardNoEventDate'])) {
				// Проверяем дату
				$post->rule('event_date', 'not_empty')
					 ->rule('event_date', 'date');
			}

			// Проверяем данные
			if ($_POST && $post->check()) {
				// Данные валидны
				$todo = $post['todo'];
			
				switch ($todo) {
					case 'cardNoEvent':
						// Обработка для cardNoEvent
						$data=Model::factory('identifier')->cardNoEvent();
						//echo Debug::vars('93', count($data));exit;
						$view='cardNoEvent';
						break;
					case 'cardNoEventDate':
						// Обработка для cardNoEventDate (нужна дата)
						//echo Debug::vars('98');exit;
						$event_date = $post['event_date'];
						$data=Model::factory('identifier')->cardNoEventDate($event_date);
						$view='cardNoEvent';
						break;
					case 'allCards':
						// Обработка для allCards
						$data=Model::factory('identifier')->allCards();
						$view='allCards';
						break;
					case 'expiredCards':
						// Обработка для allCards
						$data=Model::factory('identifier')->expiredCards();
						$view='expiredCards';
						break;
					case 'inactiveCards':
						// Обработка для allCards
						$data=Model::factory('identifier')->inactiveCards();
						$view='inactiveCards';
						break;
					case 'invalidFormat':
						// Обработка для allCards
						$data=Model::factory('identifier')->invalidFormat();
						$view='invalidFormat';
						break;
											
					
				}
			//echo Debug::vars('126');exit;
				$content = View::factory(__('identifier/:view', array(':view'=>$view)), array(//начальная страница для работы с идентификаторами.
					'list'=>$data,
					'type'=>$todo,
				));
			/* 	$content = View::factory(__('identifier/:view', array(':view'=>$view)))
					->bind('list', $data);
				; */
				
				
				//echo Debug::vars('128');exit;
				$this->template->content = $content;
		
		
		
			} else {
				// Выводим ошибкиecho Debug::vars('142');exit;
				$errors = $post->errors('validation');
			}
			
			
		
	}
	
	public function action_control()
	{
		//echo Debug::vars('148', $_POST);exit;
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
	
	/** 1.02.2026 сохранение в cvs массива подготовленной выборки
	*@input название модели, передается в POST
	*/
	public function action_save_csv() {
		echo Debug::vars('191', $_POST);exit;
		$post = Validation::factory($_POST)
				->rule('todo', 'not_empty', array(':value'))
				->rule('todo', 'in_array', array(':value', array_keys($this->options)));

			// Если форма отправлена и нажата кнопка карт без событий до даты
			if (isset($_POST['cardNoEvent'])) {
				// Проверяем дату
				$post->rule('event_date', 'not_empty')
					 ->rule('event_date', 'date');
			}

			// Проверяем данные
			if ($_POST && $post->check()) {
				if ($this->request->method() === 'POST') {
						
						// Важно: полностью отключаем все шаблоны
						$this->auto_render = FALSE;
						
						// Не должно быть никакого вывода ДО заголовков!
						$method=Arr::get($post, 'todo');
												
						$model = Model::factory('identifier');
						if (method_exists($model, $method)) {
							$big_array = $model->$method();
						} else {
							// Обработка ошибки
							throw new Kohana_Exception('Method :method not found', 
								[':method' => $method]);
						}

						$filename = 'export_' . Arr::get($post, 'todo') .'_'.date('Y-m-d_H-i-s') . '.csv';
						
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
						
						fputcsv($output, array_keys(reset($big_array)), ';', '"');
						foreach ($big_array as $row) {
							fputcsv($output, $row, ';', '"');
						}
						
						fclose($output);
						
						// 5. Завершаем скрипт
						
				}
			}
			exit;
	}
	
}
