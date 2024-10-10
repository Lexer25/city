<?php defined('SYSPATH') or die('No direct script access.');
class Controller_People extends Controller_Template { 

	public function before()
	{
			
			parent::before();
			$session = Session::instance();
			
	}
	
	
	
	public function action_index()
	{
		$_SESSION['menu_active']='people';
		
		$content = View::factory('people/search');
        $this->template->content = $content;
	}
	 
	public function action_setAuthmetod()//Установка метода авториза
	{
		//echo Debug::vars('24', $this->request->post());exit;
		$post=Validation::factory($this->request->post());
		$post->rule('Authmode', 'not_empty')
						->rule('Authmode', 'digit')
						->rule('id_pep', 'not_empty')
						->rule('id_pep', 'digit')
						->rule('id_card', 'not_empty')
						;
		if($post->check())
				{
					Model::Factory('People')->setAuthMetod(Arr::get($post, 'id_pep'), Arr::get($post, 'Authmode'));
					$this->redirect('people/peopleInfo/'.Arr::get($post, 'id_pep').'/'.Arr::get($post, 'id_card'));
				}
				else {
					echo Debug::vars('37'); exit;
				}
		
	}
	 
	 
	 
	 public function action_people_delete()
	 {
	 	//echo Debug::vars('23', $_POST); exit;
	 	if (Arr::get($_POST, 'people_delete')) Model::Factory('People')->People_delete(Arr::get($_POST, 'id_pep'));
	 	if (Arr::get($_POST, 'people_long')) Model::Factory('People')->People_long(Arr::get($_POST, 'id_pep'), Arr::get($_POST, 'timeTo'));
	 	if (Arr::get($_POST, 'card_late_save_to_file')) $this->action_card_late_save_to_file();
	 	if (Arr::get($_POST, 'people_unactive')) Model::Factory('People')->people_unactive(Arr::get($_POST, 'id_pep'));
	 	if (Arr::get($_POST, 'card_delete')) Model::Factory('People')->card_delete(Arr::get($_POST, 'id_pep'));
	 	
	 	$this->redirect('people/find_card_late');
	 	
	 }
	
	 
	 public function action_find()
	 {
	 	//echo Debug::vars('61', $_GET); exit;
		$search=Arr::get($_GET, 'peopleInfo');
	 	$_SESSION['peopleEventsTimeFrom']=Arr::get($_GET, 'timeFrom', Date::formatted_time('-2 days', "d.m.Y H:i:s"));
	 	$_SESSION['peopleEventsTimeTo']=Arr::get($_GET, 'timeTo',Date::formatted_time('now', "d.m.Y H:i:s"));
		$result=Model::Factory('People')->findIdPep($search);// поиск жильцов, совпадающих с введенным именем
		if(count($result)>0)
		 {
			$content=View::Factory('people/select', array(
			'list' => $result,
			));
		 $this->template->content = $content;
		 
		 } else {
		 $content=View::Factory('people/search');
		 $this->template->content = $content;
		 }
	 }
	 
	 
	 public function action_findID()//13.02.2022 поиск пользователя по ID_PEP
	 {
	 	
		$post=Validation::factory($this->request->post());
				//echo Debug::vars('82', $_POST, $post); exit;
		$post->rule('idPepInfo', 'not_empty')
						->rule('idPepInfo', 'digit')
						;
		if($post->check())
		{
			//echo Debug::vars('95');exit;
			$id_pep=Arr::get($post, 'idPepInfo');
			$_SESSION['peopleEventsTimeFrom']=Arr::get($_GET, 'timeFrom', Date::formatted_time('-2 days', "d.m.Y H:i:s"));
			$_SESSION['peopleEventsTimeTo']=Arr::get($_GET, 'timeTo',Date::formatted_time('now', "d.m.Y H:i:s"));
			$result=Model::Factory('People')->findIdPepInfo(array($id_pep));// поиск ID жильцов по указанном ID_PEP
		
			if(count($result)>0)
			 {
				$content=View::Factory('people/select', array(
				'list' => $result,
				));
			 $this->template->content = $content;
			 
			 } else {
			 $content=View::Factory('people/search');
			 $this->template->content = $content;
			 }
		 } else {
			 //echo Debug::vars('108', $param->errors());exit;
		 }
	 }
	 
	 
	public function action_findAnyCard()//13.02.2022 поиск пользователя по номеру карты
	 {
	 	//echo Debug::vars('115', $this->request->post()); //exit;
		//echo Debug::vars('116',$_POST);exit;
		//echo Debug::vars('139 baseFormatRfid ', Kohana::$config->load('artonitcity_config')->baseFormatRfid);//exit;
		
		$key='';
		$post=Validation::factory($this->request->post());
		//echo Debug::vars('121',$post,Arr::get($post, 'keyFormat'),Arr::get($post, 'getCardInfo'));exit;
		
		switch(Arr::get($post, 'keyFormat')){
			case 'dec': // номер для поиска - десятичное число. Валидирую и преобразую к формату хранения в бд.
				$post->rule('getCardInfo', 'not_empty')
								->rule('getCardInfo', 'digit')
								;
				if($post->check()){// номер передан в формате целого десятичного числа
				
					//echo Debug::vars('126 число десятичное!');
					//преобразую целое длинное число к формат 001А
					switch(Kohana::$config->load('artonitcity_config')->baseFormatRfid){
						case 0: //baseFormatRfid hex8
						//преобразование длинного десятичного числа к HEX
							//$key=Model::Factory('Stat')->decDigitTo001A(Arr::get($post, 'getCardInfo'));
							$key= STR_PAD(strtoupper(dechex(Arr::get($post, 'getCardInfo'))), 8, '0', STR_PAD_LEFT);
						break;
						
						case 1:// baseFormatRfid 001A
						//преобразование длинного десятичного числа к 001A
							$key=Model::Factory('Stat')->decDigitTo001A(Arr::get($post, 'getCardInfo'));
						break;
					
					}
				}
				
			break;
			case 'hex':	// номер для поиска передан в формате hex		
				
					 //echo Debug::vars('144 число HEX');
						//Номер передан в формате HEX	
							 $post=Validation::factory($this->request->post());
							 $post->rule('getCardInfo', 'not_empty')
									->rule('getCardInfo', 'regex', array(':value', '/^[a-fA-F0-9]+$/'));
											;
							if($post->check())// номер передан в формате hex
							{
								//echo Debug::vars('147 001A');exit;
								$key=Arr::get($post, 'getCardInfo');
								switch(Kohana::$config->load('artonitcity_config')->baseFormatRfid){
									case 0: //baseFormatRfid hex8
									//преобразование HEX к HEX. Добавляю слева нули, чтобы длина была 8 символов.
										
										$key = STR_PAD(Arr::get($post, 'getCardInfo'), 8, '0', STR_PAD_LEFT);
									break;
									
									case 1:// baseFormatRfid 001A
									//преобразование HEX к 001A
										$key=Model::Factory('Stat')->decDigitTo001A(hexdec(Arr::get($post, 'getCardInfo')));// пребразую HEX в DEC, а затем использую готовую функцию.
									break;
								}
							
							}

			break;
			default:
				$this->redirect('people/find');
			break;
						
		}		
		//echo Debug::vars('160 STOP', $key);exit;
		
		if($key!='')
		{
			//Получаю ID_PEP по номеру карты
			$id_pep_array=Model::factory('People')->getIdPepFromCard($key);
			//echo Debug::vars('171', $id_pep, $key);exit;
			
			$_SESSION['peopleEventsTimeFrom']=Arr::get($_GET, 'timeFrom', Date::formatted_time('-2 days', "d.m.Y H:i:s"));
			$_SESSION['peopleEventsTimeTo']=Arr::get($_GET, 'timeTo',Date::formatted_time('now', "d.m.Y H:i:s"));
			$id_pep=null;
			if(is_array($id_pep_array))
			{
				foreach($id_pep_array as $key=>$value)
				{
					$id_pep[Arr::get($value, 'ID_PEP')]=Arr::get($value, 'ID_PEP');
				}
		
			}
			//echo Debug::vars('200', $result);exit;
			$result=Model::Factory('People')->findIdPepInfo($id_pep);// поиск ID жильцов по указанном ID_PEP
			if(count($result)>0)
			 {
				$content=View::Factory('people/select', array(
				'list' => $result,
				));
			 $this->template->content = $content;
			 
			 } else {
			 $content=View::Factory('people/search');
			 $this->template->content = $content;
			 }
		 } else {
			 //echo Debug::vars('108', $param->errors());exit;
			  $content=View::Factory('people/search');
			 $this->template->content = $content;
		 }
			
	}
		
	 
	 
	
	
	public function action_card_late_save_to_file()
	{
		Model::Factory('stat')->card_late_save_to_file();
		$content =Model::Factory('Log')->send_file(Kohana::find_file('downloads','Late_card_befor', 'csv'));		
		$this->template->content = $content;
	}
	
	public function action_card_late_next_week_save_to_file()
	{
		Model::Factory('stat')->card_late_next_week_save_to_file();
		$content =Model::Factory('Log')->send_file(Kohana::find_file('downloads','Late_card_next_week', 'csv'));		
		$this->template->content = $content;
	}
	
	
	public function action_find_card_late()
	{
		$t1=microtime(1);
		$result=Model::Factory('stat')->Get_people_late();
		$t2=microtime(1);
		$content=View::Factory('people/card_late', array(
			'list' => $result,
			'delay'=>$t2-$t1,
			'title'=>'card_late_info'
			
		));
		$this->template->content = $content;
		//echo View::factory('profiler/stats');
	}
	
		public function action_find_unActiveCard()
	{
		$t1=microtime(1);
		$result=Model::Factory('stat')->Get_unActiveCard();
		$t2=microtime(1);
		$content=View::Factory('people/card_late', array(
			'list' => $result,
			'delay'=>$t2-$t1,
			'title'=>'unActiveCard'
		));
		$this->template->content = $content;
		//echo View::factory('profiler/stats');
	}
	
	
	public function action_find_card_late_next_week()
	{
		$result=Model::Factory('stat')->Get_people_late_next_week();
	
			$content=View::Factory('people/card_late_next_week', array(
			'list' => $result,
			));
		 $this->template->content = $content;
		 
		 
	}
	
	
	public function action_peopleInfo($id_pep=false)//подготовка информации по выбранному пользователю
	{
			$id_pep = $this->request->param('id');
			$id_card = $this->request->param('card');
			$_SESSION['menu_active']='people';
			//echo Debug::vars('44 peopleInfo', 'POST:', $_POST, 'GET:', $_GET,'id_pep:',  $id_pep, 'SESSION:', $_SESSION); exit;
			if ($id_pep == NULL) $this->redirect('people/find');
			$people_data=Model::Factory('People')->getPeople($id_pep, $id_card);//персональные данные
			$people_door=Model::Factory('People')->getPeopleDoor($id_pep, $id_card);//Точки прохода, куда может ходить пользователь
			$people_event=Model::Factory('Event') -> event_people($id_pep, $id_card);//события по пользователю за последние 24 часа.
			$people_parking=Model::Factory('Parking') -> event_people($id_pep);//Информация о нахождении на парковке
			$people_parking_errors=Model::Factory('Parking') -> parking_error($id_pep);//Информация о нарушениях парковки
			//echo Debug::vars('125', $people_data, $id_pep); exit;
		$content=View::Factory('people/view', array(
			'contact'	=> $people_data,
			'doors'	=> $people_door,
			'events'	=> $people_event,
			'parking'	=> $people_parking,
			'people_parking_errors'	=> $people_parking_errors,
			
			));
			
		$this->template->content = $content;
	}
	
	public function action_people_without_card()//список пользователей без карты 
	{
		$people_without_card=Model::Factory('People')->getPeople_without_card();
		$content=View::factory('people/people_without_card', array(
				'list'=>$people_without_card,
		));
		$this->template->content = $content;
	}
	
	
	public function action_people_without_card_delete()//Удаление указанных пользователй без карты
	{
		$people_for_del=Arr::get($_POST, 'id_pep');
		Log::instance()->add(Log::NOTICE, 'Удадлены пользователи :user', array(
				'user' => implode(",",$people_for_del),
				));
		Model::Factory('People')->People_delete($people_for_del);//удаление указанних пользователей
		//echo Debug::vars('131', $_SESSION, $_POST, $people_for_del); exit;	
		$this->redirect('/');
	}
	
	
	public function action_people_without_events()//список пользователей без событий в журнале событий
	{
		$people_without_card=Model::Factory('People')->getPeople_without_events();
		$content=View::factory('people/people_without_events', array(
				'list'=>$people_without_card,
		));
		$this->template->content = $content;
	}
	
	
}
