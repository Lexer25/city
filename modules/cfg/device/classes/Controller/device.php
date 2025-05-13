<?php defined('SYSPATH') or die('No direct script access.');
/**
* @package    device
 * @category   Base
 * @author     Artonit
 * @copyright  (c) 2025 Artonit Team
 * @license    http://artonit/ru 
 
 */

class Controller_cfg_device extends Controller_Template { 

	
	
	public $template = 'template';
	
	
	public function before()
	{
			
			parent::before();
			//echo Debug::vars('22', Kohana::$environment);exit;
			$session = Session::instance();
			if (!empty($_POST)) {
             	$username = Arr::get($_POST, 'username');
                $password = Arr::get($_POST, 'password');
			
                if (Auth::instance()->login($username, $password)) {
                $user = Auth::instance()->get_user();
				}
			}
			I18n::load('rubic');
			
	}
	
	
	public function action_index()// главная страница при входе. Показывает все парковочные площадки
	{
		$id = $this->request->param('id');
			
		$query=Validation::factory($this->request->query());
					$query->rule('id_resident', 'not_empty')
							->rule('id_resident', 'digit')
							;
					//echo Debug::vars('37', $query, Arr::get($query, 'id_resident'));exit;
					$id_parent=Arr::get($query, 'id_resident');
					if($query->check())
					{
						
						
									
					} else 
					{
						
						
					}
		//получили список информации по транспортным серверам
		$ts=Model::factory('tss');
		$listTS=$ts->get_list();//получил список ТС
		$listTsType=$ts->get_list_type();//получил список типов ТС
		$content = View::factory('ts/list', array(
			
			'listTS'=>$listTS,
			'listTsType'=>$listTsType,
			
		
		));
        $this->template->content = $content;
	}
	
	public function action_control()
	{
		echo Debug::vars('56', $_POST);exit;
		$post=Validation::factory($this->request->post());
					$post->rule('todo', 'not_empty')
							
							;
					if($post->check())
					{
						$todo = Arr::get($post, 'todo');
						
					} else 
					{
						$todo='no';
						
					}
		switch ($todo){
			
			
			
			case 'add'://добавление парковочной площадки
			echo Debug::vars('92', $_POST);//exit;
				$_data=Validation::factory($this->request->post());
				$_data->rule('name', 'not_empty')
						->rule('parent', 'not_empty')
						->rule('parent', 'digit')
							;
					if($_data->check())
					{
						//echo Debug::vars('113', Arr::get($_data, 'name'));exit;
						$_entity = new Parking();
						$_entity->name=Arr::get($_data, 'name');
						$_entity->is_active=1;
						$_entity->parent=Arr::get($_data, 'parent');
						$_entity->count=0;
						
					/* 	if(filter_var(Arr::get($_data, 'id_parent'), FILTER_VALIDATE_BOOLEAN)) 
					{
						//$_entity->is_active=1;
						$_entity->parent=Arr::get($_data, 'id_parent');
					} else{
						$_entity->parent=0;
					}
					 */
					
						
						if ($_entity->add())
						{
							Session::instance()->set('ok_mess', array('ok_mess' => __(Arr::get($_data, 'name').' добавлено успешно')));
							
						} else {
							Session::instance()->set('e_mess', array('ok_mess' => __(Arr::get($_data, 'name').' ошибка при добавлении')));
							
						}
						
						
					} else 
					{
						Session::instance()->set('e_mess', $_data->errors('Valid_mess'));
						
					}
				$this->redirect('ts');
			break;
			
			case 'del'://удаление Жилого комплекса из списка
				//echo Debug::vars('301', $_GET, $_POST); exit;
				$_data=Validation::factory($this->request->post());
				$_data->rule('id', 'not_empty')
							->rule('id', 'digit')
							;
					if($_data->check())
					{
						$_entity = new Parking(Arr::get($_data, 'id'));
						
						//echo Debug::vars('151',Arr::get($_data, 'id'),  $_entity );exit;
						if($_entity->del())
						{
							Session::instance()->set('ok_mess', array('ok_mess' => __('Парковочная площадка :placeName удалена успешно', array(':placeName'=>iconv('windows-1251','UTF-8', $_entity->name)))));
							
						} else {
							
							Session::instance()->set('e_mess', array('ok_mess' => __('Ошибка при удалении парковочной площадки :placeName. Парковочные места должны быть удалены заранее.', array(':placeName'=>iconv('windows-1251','UTF-8', $_entity->name)))));
							
						}
						
						
						
					} else {
						Session::instance()->set('e_mess', $_data->errors('Valid_mess'));
						
					}
					$this->redirect('ts');
			break;
			
			case 'edit'://просмотр и редакция парковки. Переход на форму редактирования
			//echo Debug::vars('235', $_GET, $_POST); exit;
				$_data=Validation::factory($this->request->post());
				$_data->rule('id', 'not_empty')
						->rule('id', 'digit')
						
						;
				
				
				if($_data->check())
				{
					//echo Debug::vars('167', $_data, Arr::get($_data, 'id'));//exit;
					$_entity = new Parking(Arr::get($_data, 'id'));
					//echo Debug::vars('169', $_entity);exit;
					$content = View::factory('parking/edit', array(
							'parking'=>$_entity,
							));
					$this->template->content = $content;
				} else 
				{
					//echo Debug::vars('175');exit;
					Session::instance()->set('e_mess', $_data->errors('Valid_mess'));
					$this->redirect('ts');
				}
			break;
			
			case 'update'://обновление данных о парковочной площадке. Примем данных и обновление данных о родителе.
			//echo Debug::vars('187', $_GET, $_POST); exit;
				$_data=Validation::factory($this->request->post());
				$_data->rule('id', 'not_empty')
						->rule('id', 'digit')
						->rule('name', 'not_empty')
						
						;
				if($_data->check())
				{
					$_entity = new Parking(Arr::get($_data, 'id'));
					echo Debug::vars('170', $_entity);//exit;
					$_entity->name=Arr::get($_data, 'name');
					
					$_entity->parent=Arr::get($_data, 'parent');
					$_entity->count=Arr::get($_data, 'count');
					if(is_null(Arr::get($_data, 'is_active'))) $_entity->is_active=0;
					
					if(filter_var(Arr::get($_data, 'is_active'), FILTER_VALIDATE_BOOLEAN)) 
					{
						$_entity->is_active=1;
					} else{
						$_entity->is_active=0;
					}
					//echo Debug::vars('178', $_entity);exit;
					if($_entity->update())
						{
							Session::instance()->set('ok_mess', array('ok_mess' => __(Arr::get($_data, 'name').' обновлен успешно')));
							
						} else {
							Session::instance()->set('e_mess', array('ok_mess' => __(Arr::get($_data, 'name').' ошибка при обновлении')));
							
						}
						
					
				} else 
				{
					Session::instance()->set('e_mess', $_data->errors('Valid_mess'));
					
				}
				$this->redirect('ts');
		
			break;
			
			
			
			default:
				//echo Debug::vars('755', $_GET, $_POST); exit;
				$this->redirect('/');
			break;
		}
		
		
	}

		
	
} 
