<?php defined('SYSPATH') or die('No direct script access.');


class Controller_sse extends Controller {

   //public $template = 'template';
	
	public function before()
	{
			
			parent::before();
			
			$session = Session::instance();
						
	}
	
	
	public function action_index()
	{	
		
		Log::instance()->add(Log::NOTICE, '21_test_21') ;
		
		$param=$this->request->post('EventITV');
		
		$post=Validation::factory($this->request->post());
		$post->rule('EventITV', 'not_empty');
		if($post->check())
		{
			Log::instance()->add(Log::NOTICE, '36 '. Debug::vars(json_decode (Arr::get($post, 'EventITV'), true))) ;
			Model::factory('mqtt')->send_message('test_ITV');// отправить сообщение
			
		} else {
			
			Log::instance()->add(Log::NOTICE, '40 Валидация не пройдена') ;
		}
		
		
		
	}
	
	public function action_log()
	{

				Log::instance()->add(Log::NOTICE, '44 получил '.Debug::vars($_POST)) ;
	}



	
}