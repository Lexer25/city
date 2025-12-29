<?php defined('SYSPATH') or die('No direct script access.');

//class Controller_Errorpage extends Controller_Template {
class Controller_Errorpage extends Controller{
	
	public function action_index()
	{
		echo Debug::vars('8');exit;
		$err=Arr::get($_GET, 'err');
		echo Debug::vars('10', $err);exit;
		$content = View::factory('errorpage', array('err'=>$err));
	$this->response->body($content);
		
	}
	
	
	
}

