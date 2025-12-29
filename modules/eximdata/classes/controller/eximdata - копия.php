<?php defined('SYSPATH') or die('No direct script access.');

/**13.10.2024 Пакет eximdata предназначен для экспорти и импорта данных.

*/
class Controller_eximdata extends Controller_Template{
	
	public $template = 'template';
	
	public function before()
	{
			parent::before();
			$session = Session::instance();
			if(!Session::instance()->get('skud_number')) $this->redirect('errorpage?err=no SKUD select.');
	}
	
	
	public function action_index()
	{
		
		 // Получаем текущий запрос
        $request = $this->request;
        
        // Проверяем метод запроса
        if ($request->method() === HTTP_Request::GET) {
			
			   // Обработка GET запроса
           
			$id=$this->request->param('id');
		$orgList=Model::factory('eximdata')->getChild($id);
		$countChild=Model::factory('eximdata')->countChild($id);
		$countPeopleInOrg=Model::factory('eximdata')->countPeopleInOrg();

	

			$content = View::factory('eximpdata', array(
					'orgList'=>$orgList,
					'countChild'=>$countChild,
					'countPeopleInOrg'=>$countPeopleInOrg,
					));
					$this->template->content = $content;
         
        } elseif ($request->method() === HTTP_Request::POST) {
            // Обработка POST запроса
          return $this->action_upload();

        } elseif ($request->method() === HTTP_Request::PUT) {
            // Обработка PUT запроса
            echo "Это PUT запрос";
        } elseif ($request->method() === HTTP_Request::DELETE) {
            // Обработка DELETE запроса
            echo "Это DELETE запрос";
        }
		
		
		

	}
	

	
	public function action_editOrg()//просмотр свойст организации и их редактирование 23.08.2022
	{
		$id=$this->request->param('id');
		$id_org=Validation::factory(array('id_org'=>$id));
		$id_org->rule('id_org', 'digit')
				->rule('id_org', 'not_empty')
				->rule('id_org', 'Model_eximdata::unique_org');
		if($id_org->check())
			{
				$nameOrg=Model::factory('eximdata')->getFileNameFromIdOrg($id);// получил название организации
				$list=Model::factory('eximdata')->export(Arr::get($id_org, 'id_org'));// получил список данных о сотрудниках для сохранения.
				}
			else {
			Session::instance()->set('e_mess', $id_org->errors('eximdata'));
			
			$this->redirect('/eximdata');
				
			}	
		
		$content = View::factory('org/view', array(
					'nameOrg'=>$nameOrg,
					'list'=>$list,
					'id_org'=>Arr::get($id_org, 'id_org')
					));
		$this->template->content = $content;
		
		
		
		
	}
	
	public function action_executor()// фукнция для обработки GET и POST запросов 23.08.2022
	{
		$post=Validation::factory($_POST);
		$post->rule('timeStart', 'regex', array(':value', '/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'))
				->rule('timeEnd', 'regex', array(':value', '/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'))
				->rule('updateWorkTime', 'not_empty')
				->rule('id_org', 'not_empty')
				->rule('id_org', 'digit')
				;
				if($post->check())
		{
				
				$res=Model::Factory('eximdata')->setWorkTime(Arr::get($post, 'timeStart'), Arr::get($post, 'timeEnd'),Arr::get($post, 'id_org') );
				Session::instance()->set('ok_mess', array('Обновление времен начала и завершения рабочего дня выполнено успешно.'));
				$this->redirect('/eximdata/editOrg/'.Arr::get($post, 'id_org'));
				
				
		} else {
				
			
			Session::instance()->set('e_mess', $post->errors('eximdata'));
			$this->redirect('/eximdata');
		}
		
		
		
	}
	
	
	
	public function action_export()
	{
		$id=$this->request->param('id');
			
		$file='file2.csv';	
		$id_org=Validation::factory(array('id_org'=>$id));
		$id_org->rule('id_org', 'digit')
				->rule('id_org', 'not_empty')
				->rule('id_org', 'Model_eximdata::unique_org');
		
		if($id_org->check())
			{
				$nameOrg=Model::factory('eximdata')->getFileNameFromIdOrg($id);// получил название организации как имя файла
				$file = preg_replace("([[:punct:] ])", '_', $nameOrg).'.csv';
				$list=Model::factory('eximdata')->export(Arr::get($id_org, 'id_org'));// получил список данных о сотрудниках для сохранения.
				//сохранение промежуточного файла
				$fp = fopen($file, 'w');
				
				foreach ($list as $fields) {
					fputcsv($fp, $fields, ';', '"');
				}
				if(!fclose($fp))
				{
					Session::instance()->set('err_mess', array('ok_mess'=>'Не могу сохранить файл '.$file));
				} 					

		
		$content = Model::Factory('eximdata')->send_file($file);
			} else {
				Session::instance()->set('e_mess', $id_org->errors('eximdata'));
				$this->redirect('/eximdata');
				
			}	
		
		exit;
	}
	
	/** 12.10.2024 экспорт дерева организаций
	*в этом процессе главное соблюст порядом добавления организаций, чтобы все выполнялось последовательно
	*
	*/
	public function action_exportTree()
	{
		$id=$this->request->param('id');//id головной организации
		$var1=time();
		$eximdata=Model::factory('eximdata');
		$about=array(
			'timestamp'=>$var1,
			'datestamp'=>date('d.m.Y H:i:s', $var1),
			'uid'=>$eximdata->getGUID_8(),//уникальный идентификатор формируемого массива данных.
			);
		
		$tree=$eximdata->getOrgListForOnce($id);// получил упорядоченный массив дерева организаций.
		$people=$eximdata->exportPeopleFromParentOrg($id);// получил список жильцов из указанной и дочерних организаций.
		$card=$eximdata->exportCardFromParentOrg($id);// получил список карт из указанной и дочерних организаций.
		
		$exportData=array(
			'about'=>$about,
			'org'=>$tree,
			'people'=>$people,
			'card'=>$card,
		);
				
				$nameOrg=Model::factory('eximdata')->getFileNameFromIdOrg($id);// получил название организации
				$filename = preg_replace("([[:punct:] ])", '_', $nameOrg).'.json';
				
				if(!file_put_contents($filename, serialize($exportData)))
				{
					Session::instance()->set('err_mess', array('ok_mess'=>'Не могу сохранить файл '.$filename));
				} 					

		
		
			$content = Model::Factory('eximdata')->send_file($filename);
		
				
		
	}
	/** 12.10.2024 Экспорт данных со всем вложенными папками для последующей вставки в базу данных.
	*
	*
	*/
	
	public function action_importTree()
	{
		
		
		
		//https://www.programmerall.com/article/372759554/
			//Внимание: 'dataimport' - это название массива с параметрами файла:
			// "dataimport" => array(5) (
			// "name" => string(22) "Корпус_06(3).txt"
			// "type" => string(10) "text/plain"
			// "tmp_name" => string(23) "C:\xampp\tmp\phpED2.tmp"
			// "error" => integer 0
			// "size" => integer 96658
			// )
		//именно этот dataimport используется в параметрах валидации
			
			$id_org=Arr::get($_POST, 'id_org2');
			$sourceFile = Arr::get($_FILES['dataimport'], 'name');
			$validation = Validation::factory($_FILES);
			$validation->rule('dataimport', 'Upload::not_empty');
			$validation->rule('dataimport', 'Upload::valid');
			$validation->rule('dataimport', 'Upload::size', array(':value', '3M'));
			$validation->rule('dataimport', 'Upload::type', array(':value', array('json')));
				
			if ($validation->check())
			{
				$cache_dir = APPPATH . 'cache/';
				$filename='dataimport.tmp';
				Upload::save($validation['dataimport'], $filename, $cache_dir);//сохраняю файл в указанную папку
				$current = unserialize(file_get_contents($cache_dir.'\\'.$filename));
				
			} else {
				// set user errors
				Session::instance()->set('e_mess', $validation->errors('eximdata'));
				$this->redirect('/eximdata');// если не удалось сохранить файл, то будет выведено сообщение об ошибке
				
			}

					
			//запись дерева организаций в место вставки
				
			$eximdata=Model::factory('eximdata');
			
			$uid=Arr::get(Arr::get($current, 'about'), 'uid');//уникальный идентификатор пакета для вставки данных
			//проверка, что это не повторный запуск. Признак повторного запуска - наличие UID в guid организаций.
			
			if($eximdata->detectRepeatRun($uid)->result != 0)
			{
					
					//уже была попытка вставить эти данные. Надо вывести сообщение о прекращении работы.
					Session::instance()->set('e_mess', array('e_mess'=>__('Повторная вставка данных из файла ":filename" не допускается. Работа прекращается.', 
						array(
							':filename'=> $sourceFile,
							)
					))
					);
					
					//выход из импорта, вывод причины на экран
				$this->redirect('/eximdata');
				
			} else {
				//это первая вставка, продолжаю работу.
				
			}
			
			$var1=$eximdata->insertTree(Arr::get($current, 'org'), $id_org, $uid);
			
			if($var1->result==0) //если организации добавлены успешно, то продолжаю
			{
				$result['orgCount']=$var1->countResult;//количество успешно вставленных организаций.
			//если вставка прошла успешно, то начинаю добавлять контакты
				//добавляю контакты. Важно: в качестве id_org контакта необходимо искать организацию, у которой divcode=$uid<старый номер id_org>
				$var2=$eximdata->insertContactInTree(Arr::get($current, 'people'), $uid);
				if($var2->result == 0) //если контакты добавлены успешно
				{					
					$result['contactCount']=$var2->countResult;//количество успешно вставленных организаций.
					//добавление карт для сотрудников. При добавлении может быть коллизия: карта уже выдана. В этом случае формируется список ошибочных карт, который затем выводится в файл.
					$errCard=$eximdata->insertCardInTree(Arr::get($current, 'card'), $uid);
						$result['contactKeyForInsert']=count(Arr::get($current, 'card'));//количество карт для вставки.	
						$result['contactKeyInsertErr']=count($errCard);//количество карт для вставки.
						if($errCard){
							//готовлю файл с ошибками
										
							Session::instance()->set('e_mess', array('ok_mess'=>'При вставке данных произошли ошибки. Ошибки записаны в лог-файл приложения.'));
							
						} else {
							Session::instance()->set('ok_mess', array(__('Вставка данных из файла ":filename" прошла успешно.', array(':filename'=> $sourceFile,))));
					
						}
						
			
				}
			} else {
				
				//при добавлении организаций возникли ошибки.
				//удаляю все организации, у которых divcode содержи uid
				$eximdata->deleteOrgImportErr($uid);
				
			}
			
			$list_error[]= __('Добавлено организаций :orgCount<br>
								Добавлено контактов :contactCount<br>
								Добавлено идентификаторов без ошибки :contactKeyInsert<br>
								Не добавлено идентификаторов contactKeyInsertErr<br>
								',
						array(
							'orgCount'=>$result['orgCount'],
							'contactCount'=>$result['contactCount'],
							'contactKeyInsertErr'=>$result['contactKeyInsertErr'],
							'contactKeyInsert'=>$result['contactKeyForInsert']-$result['contactKeyInsertErr'],
							));
							
			
			Session::instance()->set('result_mess', $list_error);
			
			$this->redirect('/eximdata');
	}
	
	public function action_exportFull()
	{
		$id=$this->request->param('id');
			
		$file='file2_full.csv';	
		$id_org=Validation::factory(array('id_org'=>$id));
		$id_org->rule('id_org', 'digit')
				->rule('id_org', 'not_empty')
				->rule('id_org', 'Model_eximdata::unique_org');
		
		if($id_org->check())
			{
				$nameOrg=Model::factory('eximdata')->getFileNameFromIdOrg($id);// получил название организации как имя файла
				$file = preg_replace("([[:punct:] ])", '_', $nameOrg).'.csv';
				$list=Model::factory('eximdata')->export(Arr::get($id_org, 'id_org'));// получил список данных о сотрудниках для сохранения.
				//сохранение промежуточного файла
				$fp = fopen($file, 'w');
				
				foreach ($list as $fields) {
					fputcsv($fp, $fields, ';', '"');
				}
				if(!fclose($fp))
				{
					Session::instance()->set('err_mess', array('ok_mess'=>'Не могу сохранить файл '.$file));
				} 					

		
		
		//$content = Model::Factory('Log')->send_file($file);
		$content = Model::Factory('eximdata')->send_file($file);
		
		$this->redirect('/eximdata');
				
			} else {
				Session::instance()->set('e_mess', $id_org->errors('eximdata'));
				//$this->template->content = $content;
				$this->redirect('/eximdata');
				
			}	
		
		exit;
	}
	
	public function action_upload()
	{
			$id_org=Arr::get($_POST, 'id_org1');
			
			// create validation object
			$validation = Validation::factory($_FILES)
				->rules('csv', array(
					array('Upload::not_empty'),
				));

			if ($validation->check())
			{
				$cache_dir = APPPATH . 'cache/';
				
				// Создаем директорию если её нет
				if (!is_dir($cache_dir)) {
					mkdir($cache_dir, 0755, true);
				}

				Upload::save($validation['csv'], 'file.csv', $cache_dir);//сохраняю файл в указанную папку
				
			} else {
				// set user errors
				Session::instance()->set('e_mess', $validation->errors('eximdata'));
				$this->redirect('/eximdata');// если не удалось сохранить файл, то будет выведено сообщение об ошибке
				
			}

			
			//чтение данных из файла и преобразование их в массив			
						
				$cache_file = $cache_dir . 'file.csv';

				
				if (($fp = fopen($cache_file, "r")) !== FALSE) {
					while (($data = fgetcsv($fp, 0, ";")) !== FALSE) {
						$list[] = $data;
					}
					fclose($fp);
				}
			
			//валидация полученных данных
			foreach($list as $key=>$value)
			{
				$data=Validation::factory($list);
				
				$data=Validation::factory($value);
				$data->rule(0, 'digit')
					->rule(0, 'not_empty')
					->rule(1, 'max_length', array(':value', 50))
					->rule(2, 'max_length', array(':value', 50))
					->rule(3, 'max_length', array(':value', 50))
					//->rule(5, 'regex', array(':value', '/^[A-F\d]{10}+$/')) // https://regex101.com/
					->rule(5, 'Model_eximdata::unique_card') //проверка идентификатора на уникальность.
					->rule(6, 'regex', array(':value', '/^[1-5]{1}+$/')) //^[1-5]{1}+$ https://regex101.com/
					;
				$keyTypeList=$this->_getCardtype();//получил список типов карт.
				
				if($data->check())//если карты нет в БД, то добавляем ее в базу данных
				{
					if(Model::factory('eximdata')->insertPeople($value, $id_org))
					{
					
						$fioo = Model::factory('eximdata')->getInforForCard(Arr::get($data, 5));

					$list_error[]= __('377 ok Карта :card пользователя :f :i :o тип :cardType зарегистрирована успешно в организацию :orgName.',
						array(
							':ffrom'=>iconv('windows-1251','UTF-8',Arr::get($fioo, 'SURNAME')),
							':ifrom'=>iconv('windows-1251','UTF-8',Arr::get($fioo, 'NAME')),
							':ofrom'=>iconv('windows-1251','UTF-8',Arr::get($fioo, 'PATRONYMIC')),
							':orgName'=>iconv('windows-1251','UTF-8',Arr::get($fioo, 'ORGNAME')),
							':f'=>iconv('windows-1251','UTF-8',Arr::get($value, 1)),
							':i'=>iconv('windows-1251','UTF-8',Arr::get($value, 2)),
							':o'=>iconv('windows-1251','UTF-8',Arr::get($value, 3)),
							':card'=>Arr::get($value, 5),
							':cardType'=>Arr::get($keyTypeList, Arr::get($value, 6)),
							));
					} else {
						
						$list_error[]= __('391 err Карта :card пользователя :f :i :o тип :cardType  ошибка валидации. Проверьте номер идентификатора.',
						array(
							':ffrom'=>iconv('windows-1251','UTF-8',Arr::get($fioo, 'SURNAME')),
							':ifrom'=>iconv('windows-1251','UTF-8',Arr::get($fioo, 'NAME')),
							':ofrom'=>iconv('windows-1251','UTF-8',Arr::get($fioo, 'PATRONYMIC')),
							':orgName'=>iconv('windows-1251','UTF-8',Arr::get($fioo, 'ORGNAME')),
							':f'=>iconv('windows-1251','UTF-8',Arr::get($value, 1)),
							':i'=>iconv('windows-1251','UTF-8',Arr::get($value, 2)),
							':o'=>iconv('windows-1251','UTF-8',Arr::get($value, 3)),
							':card'=>Arr::get($value, 5),
							':cardType'=>Arr::get($keyTypeList, Arr::get($value, 6)),
							));
					}
				} else {
					 
					$fioo = Model::factory('eximdata')->getInforForCard(Arr::get($data, 5));

					$list_error[]= __('380 err Карта :card пользователя :f :i :o тип :cardType  уже выдана пользователю :ffrom :ifrom :ofrom из организации :orgName.',
						array(
							':ffrom'=>iconv('windows-1251','UTF-8',Arr::get($fioo, 'SURNAME')),
							':ifrom'=>iconv('windows-1251','UTF-8',Arr::get($fioo, 'NAME')),
							':ofrom'=>iconv('windows-1251','UTF-8',Arr::get($fioo, 'PATRONYMIC')),
							':orgName'=>iconv('windows-1251','UTF-8',Arr::get($fioo, 'ORGNAME')),
							':f'=>iconv('windows-1251','UTF-8',Arr::get($value, 1)),
							':i'=>iconv('windows-1251','UTF-8',Arr::get($value, 2)),
							':o'=>iconv('windows-1251','UTF-8',Arr::get($value, 3)),
							':card'=>Arr::get($value, 5),
							':cardType'=>Arr::get($keyTypeList, Arr::get($value, 6)),
							));
					//Session::instance()->set('e_mess', $list_error);
					//$this->redirect('/eximdata');// если не удалось сохранить файл, то,  будет выведено сообщение об ошибке
				}
				
				
			}

			Session::instance()->set('result_mess', $list_error);
			
			//Model::factory('eximdata')->insertPeople($list);
			
		
		

		// redirect to home page
		$this->redirect('/eximdata');
	}
	
		public function _getCardtype()
		{
			
			$sql='select c.id, c.name from cardtype c';
			
			$query=DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array()
			;
			
			foreach($query as $key=>$value)
			{
				$list[Arr::get($value, 'ID')]=iconv('windows-1251','UTF-8' ,Arr::get($value, 'NAME'));
				
			}
			return $list;
		}
	
	
	
}

