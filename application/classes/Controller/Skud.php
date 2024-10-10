<?php defined('SYSPATH') or die('No direct script access.');

//class Controller_Skud extends Controller_Template {
class Controller_Skud extends Controller {

   //public $template = 'template';
	
	public function before()
	{
			
			parent::before();
			
			$session = Session::instance();
			
			
			//echo Debug::vars('12', $_GET, $_POST,'skud_number from session befor change', Session::instance()->get('skud_number') ); //exit;
			
			//include Kohana::find_file('classes/controller','check_db_connect');
			
			$post=Validation::factory($_POST);
			$post->rule('skud_number', 'not_empty')
				->rule('skud_number', 'digit')
				->rule('select_skud_button', 'not_empty');
			
			if($post->check())
			{
				
				Session::instance()->set('skud_number', Arr::get($post,'skud_number'));
				
			} else {
				
				
				
			}
			
	}
	
	
	public function action_index()
	{	
		$t1=microtime(1);		
		$skud_list=Model::Factory('skud')->getSkudList();
		$content = View::factory('skud_list', array(
			'skud_list'=>$skud_list,
			'time_exec_start'=>$t1,
			'title'=>'Сводная таблица',
			
			));
		
		//$this->template->content = $content;
		$this->response->body($content);
		//echo View::factory('profiler/stats');
		
	}



	
}