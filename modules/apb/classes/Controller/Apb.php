<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Apb extends Controller_Template {
	
	
	public $template = 'template';
	public function before()
	{
			
			parent::before();
			$session = Session::instance();
			//echo Debug::vars('9', $_POST, $_GET);
			I18n::load('apb');
			
	}

	public function action_index()
	{
		$_SESSION['menu_active']='apb';
		//echo Debug::vars('20', $_SESSION);
		$apb_list=Model::Factory('apb')->get_list();
		$content = View::factory('apb/apb', array(
			'apb_list'=>$apb_list,
		
		));
        $this->template->content = $content;
		
	}
	
	public function action_add_apb()//Добавить новую зону антипассбека
	{
		$_SESSION['menu_active']='kp_park_menu';
		$apb_list=Model::Factory('apb')->get_list();
		$content = View::factory('apb/apb', array(
			'apb_list'=>$apb_list,
		
		));
        $this->template->content = $content;
		
	}
	
	public function action_edit_apb()//редактировать и просматривать  апб
	{
		$_SESSION['menu_active']='kp_park_menu';
		//echo Debug::vars('43', $_GET, $_POST, $this->request->param('id')); exit;
		$id_apb = $this->request->param('id');
		$apb_getinfo=Model::Factory('apb')->get_info_apb($id_apb); //получить лист точек прохода, уже входящих в периметр
		$apb_device_list=Model::Factory('apb')->get_list_dev($id_apb); //получить лист точек прохода, уже входящих в периметр
		$door_list=Model::Factory('apb')->get_door_list_not_apb($id_apb); //получить лист точек прохода, не входящих в периметр
		$people_list_inside=Model::Factory('apb')->get_people_list_inside($id_apb); //получить лист точек прохода, не входящих в периметр
		//echo Debug::vars('45', $people_list_inside); exit;
		
		$content = View::factory('apb/edit_apb', array(
			'apb_device_list'=>$apb_device_list,
			'door_list'=>$door_list,
			'id_apb'=>$id_apb,
			'apb_getinfo'=>$apb_getinfo,
			'people_list_inside' => $people_list_inside
		));
        $this->template->content = $content;
		
	}
	
	

	public function action_apb_control()
	{
		//echo Debug::vars('30', $_GET, $_POST); exit;
		//echo Debug::vars('68', $_SESSION);
		
		$todo = $this->request->post('todo');
		switch ($todo){
			case 'add_apb'://добавление нового периметра
				$add_apb = $this->request->post('add_apb');// далее добавляем новый периметр.
				Model::factory('apb')->add_apb($add_apb);
				$this->redirect('apb');
			break;
			
			case 'del_apb'://добавление нового периметра
				$del_apb = $this->request->post('id_apb');// далее добавляем новый периметр.
				Model::factory('apb')->del_apb($del_apb);
				$this->redirect('apb');
			break;
			
			case 'edit_apb'://просмотр и редакция периметра
				$post=Validation::factory($this->request->post());
				$post->rule('id_apb', 'not_empty')
						->rule('id_apb', 'digit')
						;
				//echo Debug::vars('73', $_GET, $_POST, $todo, $this->request->post('id_apb')); exit;
				if($post->check())
				{
					$this->redirect('apb/edit_apb/'.Arr::get($post, 'id_apb'));
				} else 
				{
					Session::instance()->set('e_mess', $post->errors('apb'));
					$this->redirect('apb');
				}
		
			break;
			
			case 'change_door'://поменять вход и выход точки прохода
				//echo Debug::vars('83', $_GET, $_POST, $todo, $this->request->post('change_door')); exit;
				$id_dev=$this->request->post('id_dev_for_change');
				$id_apb=$this->request->post('id_apb');
				$device_info_apb =Model::factory('apb')->get_dev_info($id_dev, $id_apb);// получить информацию по заданной точке прохода в заданном apb
				if(Arr::get($device_info_apb, 'IS_ENTER') == 0)
				{
					Model::factory('apb')->del_door_apd($id_dev, $id_apb);// удаляю точку прохода
					Model::factory('apb')->add_door_apd($id_dev, 1, $id_apb);// добавляю точку с признаком Enter
				}
				
				if(Arr::get($device_info_apb, 'IS_ENTER') == 1)
				{
					Model::factory('apb')->del_door_apd($id_dev, $id_apb);// удаляю точку прохода
					Model::factory('apb')->add_door_apd($id_dev, 0, $id_apb);// добавляю точку с признаком Enter
				}
				
				$this->redirect('apb/edit_apb/'.$id_apb);
		
			break;
			
			case 'delete_door'://удалить точку прохода
				//echo Debug::vars('91', $_GET, $_POST, $todo, $this->request->post('change_door')); exit;
				$id_dev=$this->request->post('id_dev_for_change');
				$id_apb=$this->request->post('id_apb');
				Model::factory('apb')->del_door_apd($id_dev, $id_apb);// удаляю точку прохода
				$this->redirect('apb/edit_apb/'.$id_apb);
		
			break;
			
			case 'add_door_enter'://добавить точку прохода в enter
				//echo Debug::vars('92', $_GET, $_POST, $todo, $this->request->post('id_dev')); exit;
				$id_dev=$this->request->post('id_dev');
				$id_apb=$this->request->post('id_apb');
				Model::factory('apb')->add_door_apd($id_dev, 1, $id_apb);// добавляю точку с признаком Enter
				$this->redirect('apb/edit_apb/'.$id_apb);
		
			break;
			
			case 'add_door_exit'://добавить точку прохода в exit
				//echo Debug::vars('99', $_GET, $_POST, $todo, $this->request->post('id_dev')); exit;
				$id_dev=$this->request->post('id_dev');
				$id_apb=$this->request->post('id_apb');
				Model::factory('apb')->add_door_apd($id_dev, 0, $id_apb);// добавляю точку с признаком Enter
				$this->redirect('apb/edit_apb/'.$id_apb);
		
			break;
			case 'change_config'://изменение конфигурации apb
				//echo Debug::vars('135', $_GET, $_POST, $todo); exit;
				$post=Validation::factory($this->request->post());
				$post->rule('name', 'not_empty')
						//->rule('name', 'regex', array(':value', '/^[а-яА-Яa-zA-Z0-9\s_]+$/iD' ))
						//->rule('name', 'regex', array(':value', '/^[а-я][А-Я][0-9][.][ ]$u/' ))
						->rule('id_apb', 'digit')
						->rule('id_apb', 'not_empty')
						->rule('duration', 'digit')
						->rule('duration', 'not_empty')
						
						
						;
				if($post->check())
				{		
				$name=Arr::get($post, 'name');
				$id_apb=Arr::get($post, 'id_apb');
				$duration=Arr::get($post, 'duration');
				$is_active = (NULL != Arr::get($post, 'is_active'))? 1 : 0;
				//echo Debug::vars('140', $id_apb, $name, $duration, $is_active); exit;
				Model::factory('apb')->change_config($id_apb, $name, $duration, $is_active);// обновление информации о периметер
				} else {
					
				//echo Debug::vars('174', $post->errors('validation')); exit;	
					
				}
				
				$this->redirect('apb');
		
			break;
			
			
		case 'clear_parking_inside'://очистить таблицу parking_inside
				$id_dev=$this->request->post('id_dev_for_change');
				$id_apb=$this->request->post('id_apb');
				Model::factory('apb')->clear_perimeter_inside($id_apb);// очистка указанного периметра
				$this->redirect('apb/edit_apb/'.$id_apb);
		
			break;
			
		case 'apd_changer_pass_delay'://Записать новые значения задержек
				
				$post=Validation::factory($this->request->post());
				
				$post->rule('delay_pass', 'not_empty')
						->rule('delay_pass', 'is_array')
						->rule('id_apb', 'digit')
						->rule('id_apb', 'not_empty')
						;
						//echo Debug::vars('161',$_POST, $post, $post->check() ); exit;
				if($post->check())
				{
					$id_dev=Arr::get($post, 'delay_pass');
					$id_apb=Arr::get($post, 'id_apb');
					Model::factory('apb')->update_delay_pass($id_apb, $id_dev);// очистка указанного периметра
				} else {
					echo Debug::vars('174', $post->errors('validation')); exit;
					$res=$post->errors('validation');
					$res='post->errors(validation)';
				}
				
				$this->redirect('apb/edit_apb/'.Arr::get($post, 'id_apb'));
		
			break;
			
			
			
			
			default:
				echo Debug::vars('56', $_GET, $_POST); exit;
			break;
		}
		$content='';
        $this->template->content = $content;
		
	}

} 
