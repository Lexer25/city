<?php defined('SYSPATH') or die('No direct script access.');

class Controller_TS extends Controller_Template { 

    public $template = 'template';
    
    public function before()
    {
        parent::before();
        
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
    
    public function action_index()
    {
        $ts = Model::factory('tss');
        $listTS = $ts->get_list();
        $listTsType = $ts->get_list_type();
        
        $content = View::factory('ts/list', array(
            'listTS' => $listTS,
            'listTsType' => $listTsType,
            'is_logged_in' => Auth::instance()->logged_in()
        ));
        
        $this->template->content = $content;
    }
    
    public function action_control()
    {
        //echo Debug::vars('40', $_POST);exit;
		$post = Validation::factory($this->request->post());
        $post->rule('todo', 'not_empty');
        
        if ($post->check()) {
            $todo = Arr::get($post, 'todo');
        } else {
            $todo = 'no';
        }
        
        switch ($todo) {
            
            // ========== РАБОТА С СЕРВЕРАМИ ==========
            
            case 'add_server':
                $this->_add_server();
                break;
                
            case 'edit_server':
                $this->_edit_server();
                break;
                
            case 'update_server':
                $this->_update_server();
                break;
                
            case 'del_server':
                $this->_delete_server();
                break;
            
          
        }
    }
    
    // ========== МЕТОДЫ ДЛЯ РАБОТЫ С СЕРВЕРАМИ ==========
    
    /**
     * Добавление нового сервера
     */
		private function _add_server()
		{
			$data = Validation::factory($this->request->post());
			$data->rule('name', 'not_empty')
				 ->rule('ip', 'not_empty')
				 ->rule('port', 'not_empty')
				 ->rule('port', 'digit');

			if ($data->check()) {
				try {
					$ts = Model::factory('tss');
					$result = $ts->add_server($data);
					
					if ($result) {
						Session::instance()->set('ok_mess', array(
							'ok_mess' => __('Сервер :name добавлен успешно', array(':name' => Arr::get($data, 'name')))
						));
					} else {
						Session::instance()->set('e_mess', array(
							'e_mess' => __('Ошибка при добавлении сервера')
						));
					}
				} catch (Exception $e) {
					Session::instance()->set('e_mess', array(
						'e_mess' => __('Ошибка при добавлении сервера: :error', array(':error' => $e->getMessage()))
					));
				}
			} else {
				Session::instance()->set('e_mess', $data->errors('Valid_mess'));
			}
			
			$this->redirect('ts');
		}
    
    /**
     * Редактирование сервера - открытие формы
     */
    private function _edit_server()
    {
        $data = Validation::factory($this->request->post());
        $data->rule('id', 'not_empty')
             ->rule('id', 'digit');
        
        if ($data->check()) {
            $server_id = Arr::get($data, 'id');
            
            // Получаем данные сервера
            $ts = Model::factory('tss');
            $server = $ts->get_server_by_id($server_id);
            
            if ($server) {
                // Получаем список типов
                $types = $ts->get_list_type();
                
                // Создаем форму редактирования
                $content = View::factory('ts/edit_server', array(
                    'server' => $server,
                    'types' => $types,
                    'is_logged_in' => Auth::instance()->logged_in()
                ));
                
                $this->template->content = $content;
                return;
            } else {
                Session::instance()->set('e_mess', array(
                    'e_mess' => __('Сервер не найден')
                ));
            }
        } else {
            Session::instance()->set('e_mess', $data->errors('Valid_mess'));
        }
        
        $this->redirect('ts');
    }
    
    /**
     * Обновление данных сервера
     */
    private function _update_server()
    {
        $data = Validation::factory($this->request->post());
        $data->rule('id', 'not_empty')
             ->rule('id', 'digit')
             ->rule('name', 'not_empty')
             ->rule('ip', 'not_empty')
             ->rule('port', 'not_empty')
             ->rule('port', 'digit')
             ->rule('id_type', 'not_empty')
             ->rule('id_type', 'digit');
        
        if ($data->check()) {
            try {
                // Здесь нужно добавить логику обновления сервера
				$ts = Model::factory('tss');
				$server = $ts->update_server_by_id($data);
                // Пока просто заглушка
                Session::instance()->set('ok_mess', array(
                    'ok_mess' => __('Сервер :name обновлен успешно', array(':name' => Arr::get($data, 'name')))
                ));
            } catch (Exception $e) {
                Session::instance()->set('e_mess', array(
                    'e_mess' => __('Ошибка при обновлении сервера: :error', array(':error' => $e->getMessage()))
                ));
            }
        } else {
            Session::instance()->set('e_mess', $data->errors('Valid_mess'));
        }
        
        $this->redirect('ts');
    }
    
    /**
     * Удаление сервера
     */
    private function _delete_server()
    {
        $data = Validation::factory($this->request->post());
        $data->rule('id', 'not_empty')
             ->rule('id', 'digit');
        
        if ($data->check()) {
            try {
                // Здесь нужно добавить логику удаления сервера
                // Пока просто заглушка
                Session::instance()->set('ok_mess', array(
                    'ok_mess' => __('Сервер удален успешно')
                ));
            } catch (Exception $e) {
                Session::instance()->set('e_mess', array(
                    'e_mess' => __('Ошибка при удалении сервера: :error', array(':error' => $e->getMessage()))
                ));
            }
        } else {
            Session::instance()->set('e_mess', $data->errors('Valid_mess'));
        }
        
        $this->redirect('ts');
    }
    
   
}
