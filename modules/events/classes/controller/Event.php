<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Event extends Controller_Template { 

	public function before()
	{
			
			parent::before();
			$session = Session::instance();
			
	}
	
	
	public function action_index()
	{
		$_SESSION['menu_active']='events';
		
		
		$a=Model::Factory('Event')->stat();
		$content = View::factory('event', array(
			'list' => $a[0],
			'analit_count' => $a[1],
			));
        $this->template->content = $content;
	}
	
	public function action_event_analyt($analyt_code=FALSE)// выборка данных по кодам аналитики вер. 1.2.5 добавлено 2.03.2020.
	{
		
		
		$analyt_code = $this->request->param('id');
		switch ($analyt_code){
			
			case 480:// не пропущен повторно правильно
				$analyt_result=Model::factory('event')->getAnalytCodeList($analyt_code);
				$content=View::factory('Event/event_analyt', array(
					'analyt_result' => $analyt_result,
					'analyt_code' => $analyt_code,
					));
			break;
			case 481:// не пропущен повторно НЕ правильно
				$analyt_result=Model::factory('event')->getAnalytCodeList($analyt_code);
				$content=View::factory('Event/event_analyt', array(
					'analyt_result' => $analyt_result,
					'analyt_code' => $analyt_code,
					));
			break;
			case 80:
				$this->redirect('event/unknowcard/80');
			break;
			case 145:
				$this->redirect('event/test_mode/145');
			break;
			case 503:
			case 506:
				$analyt_result=Model::factory('event')->getAnalytCodeList($analyt_code);
				$analyt_result_door=Model::factory('event')->getAnalytListDoor($analyt_code);// список точек прохода, где наблюдались заданные коды аналитики
				$content=View::factory('Event/event_analyt', array(
					'analyt_result' => $analyt_result,
					'analyt_result_door' => $analyt_result_door,
					'analyt_code' => $analyt_code,
					));
				break;
			case 508:
				$analyt_result=Model::factory('event')->getAnalytCodeList($analyt_code);
				$content=View::factory('Event/event_analyt', array(
					'analyt_result' => $analyt_result,
					'analyt_code' => $analyt_code,
					));
				break;
				
			case 652:
			case 657:
				$analyt_result=Model::factory('event')->getAnalytCodeList($analyt_code);//списко ФИО, у которых есть нарушения 657
				$analyt_result_door=Model::factory('event')->getAnalytListDoor($analyt_code);
				
				$content=View::factory('Event/event_analyt', array(
					'analyt_result' => $analyt_result,
					'analyt_result_door' => $analyt_result_door,
					'analyt_code' => $analyt_code,
					));
				break;
		default:
			$content=View::factory('Event/no_event_analyt', array(
				'analyt_code' => $analyt_code,
				));
		}
		
		$this->template->content = $content;	
	}
	
	public function action_device65 ()// вывод данных по событию 65 для указанного устройства
	{
		$id = $this->request->param('id');
		$a=Model::Factory('event')->event_invalid_list($id);
		$content2 = View::factory('Event/invalid_list', array(
			'list' => $a,
			));		
		$this->template->content = $content2;
	
	}
	
	public function action_unknowcard ($eventtype=FALSE)
	{
		$id = $this->request->param('id');
		if($id=46)
		{
		$a=Model::Factory('event')->event_unknowcard();
		$content = View::factory('Event/event_unknowcard', array(
			'list' => $a,
			));		
		}
		
		if($id=80)
		{
		$a=Model::Factory('event')->event_unknowcard_80();
		$content = View::factory('Event/event_unknowcard', array(
			'list' => $a,
			));		
		}
		
		
		
		
		
		$this->template->content = $content;
	}
	

	
	public function action_invalid ($eventtype=FALSE)
	{
		$id = $this->request->param('id');
		if($id=65)
		{
		//$a=Model::Factory('event')->event_invalid();
		//$content1 = View::factory('Event/invalid', array(
		//	'list' => $a,
		//	));		
		
		$a=Model::Factory('event')->event_invalid_list();
		$content2 = View::factory('Event/invalid_list', array(
			'list' => $a,
			));		
		}
		$this->template->content = $content2;
	}
	
	public function action_errtz ($eventtype=FALSE)// сотрудние не пропущен по времени, событие 47
	{
		$a=Model::factory('event')->errtz();
		$content = View::factory('Event/event_errtz', array(
			'list' => $a,
			));
		$this->template->content = $content;	
	}
	
	public function action_test_mode ($eventtype=FALSE)// проход в режиме Тест
	{
		$a=Model::factory('event')->test_mode();
		$content = View::factory('Event/test_mode', array(
			'list' => $a,
			));
		$this->template->content = $content;	
	}
	
	public function action_analit_list () // вывод страницы со списком отклонений за последние сутки.
	{
		$analit_list=Model::factory('event') -> analit_list();
		$content=View::factory('Event/analit_list', array(
			'list' => $analit_list,
			));
		$this->template->content = $content;
	}

}