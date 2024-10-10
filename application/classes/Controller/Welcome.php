<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Welcome extends Controller {
public $template = 'template';
	public function action_index()
	{
		$this->response->body('Error database connect');
		
	}

} // End Welcome
