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
    
    /**
     * Главная страница - три вкладки
     */
    public function action_index()
    {
        $content = View::factory('ts/index', array(
            'is_logged_in' => Auth::instance()->logged_in()
        ));
        
        $this->template->content = $content;
    }
    
    // ============================================================
    // МОДУЛЬ 1: УПРАВЛЕНИЕ ТИПАМИ ТРАНСПОРТНЫХ СЕРВЕРОВ
    // ============================================================
    
    public function action_types()
    {
        $ts = Model::factory('tss');
        $listTypes = $ts->get_list_type();
        
        $content = View::factory('ts/types', array(
            'listTypes' => $listTypes,
            'is_logged_in' => Auth::instance()->logged_in()
        ));
        
        $this->template->content = $content;
    }
    
    public function action_control_types()
    {
        $post = Validation::factory($this->request->post());
        $post->rule('todo', 'not_empty');
        
        if ($post->check()) {
            $todo = Arr::get($post, 'todo');
        } else {
            $todo = 'no';
        }
        
        switch ($todo) {
            case 'add_type':
                $this->_add_type();
                break;
            case 'edit_type':
                $this->_edit_type();
                break;
            case 'update_type':
                $this->_update_type();
                break;
            case 'del_type':
                $this->_delete_type();
                break;
        }
    }
    
    /**
     * Добавление типа ТС
     */
    private function _add_type()
    {
        $data = Validation::factory($this->request->post());
        $data->rule('name', 'not_empty')
             ->rule('description', 'not_empty');

        if ($data->check()) {
            try {
                $ts = Model::factory('tss');
                $result = $ts->add_type($data);
                
                if ($result) {
                    Session::instance()->set('ok_mess', array(
                        'ok_mess' => __('Тип :name добавлен успешно', array(':name' => Arr::get($data, 'name')))
                    ));
                } else {
                    Session::instance()->set('e_mess', array(
                        'e_mess' => __('Ошибка при добавлении типа')
                    ));
                }
            } catch (Exception $e) {
                Session::instance()->set('e_mess', array(
                    'e_mess' => __('Ошибка при добавлении типа: :error', array(':error' => $e->getMessage()))
                ));
            }
        } else {
            Session::instance()->set('e_mess', $data->errors('Valid_mess'));
        }
        
        $this->redirect('ts/types');
    }
    
    /**
     * Редактирование типа - открытие формы
     */
    private function _edit_type()
    {
        $data = Validation::factory($this->request->post());
        $data->rule('id', 'not_empty')
             ->rule('id', 'digit');
        
        if ($data->check()) {
            $type_id = Arr::get($data, 'id');
            $ts = Model::factory('tss');
            $type = $ts->get_type_by_id($type_id);
            
            if ($type) {
                $content = View::factory('ts/edit_type', array(
                    'type' => $type,
                    'is_logged_in' => Auth::instance()->logged_in()
                ));
                
                $this->template->content = $content;
                return;
            } else {
                Session::instance()->set('e_mess', array(
                    'e_mess' => __('Тип не найден')
                ));
            }
        } else {
            Session::instance()->set('e_mess', $data->errors('Valid_mess'));
        }
        
        $this->redirect('ts/types');
    }
    
    /**
     * Обновление типа
     */
    private function _update_type()
    {
        $data = Validation::factory($this->request->post());
        $data->rule('id', 'not_empty')
             ->rule('id', 'digit')
             ->rule('name', 'not_empty')
             ->rule('description', 'not_empty');
        
        if ($data->check()) {
            try {
                $ts = Model::factory('tss');
                $result = $ts->update_type($data);
                
                if ($result) {
                    Session::instance()->set('ok_mess', array(
                        'ok_mess' => __('Тип :name обновлен успешно', array(':name' => Arr::get($data, 'name')))
                    ));
                } else {
                    Session::instance()->set('e_mess', array(
                        'e_mess' => __('Ошибка при обновлении типа')
                    ));
                }
            } catch (Exception $e) {
                Session::instance()->set('e_mess', array(
                    'e_mess' => __('Ошибка при обновлении типа: :error', array(':error' => $e->getMessage()))
                ));
            }
        } else {
            Session::instance()->set('e_mess', $data->errors('Valid_mess'));
        }
        
        $this->redirect('ts/types');
    }
    
    /**
     * Удаление типа
     */
    private function _delete_type()
    {
        $data = Validation::factory($this->request->post());
        $data->rule('id', 'not_empty')
             ->rule('id', 'digit');
        
        if ($data->check()) {
            try {
                $ts = Model::factory('tss');
                $result = $ts->delete_type(Arr::get($data, 'id'));
                
                if ($result) {
                    Session::instance()->set('ok_mess', array(
                        'ok_mess' => __('Тип удален успешно')
                    ));
                } else {
                    Session::instance()->set('e_mess', array(
                        'e_mess' => __('Ошибка при удалении типа: тип используется')
                    ));
                }
            } catch (Exception $e) {
                Session::instance()->set('e_mess', array(
                    'e_mess' => __('Ошибка при удалении типа: :error', array(':error' => $e->getMessage()))
                ));
            }
        } else {
            Session::instance()->set('e_mess', $data->errors('Valid_mess'));
        }
        
        $this->redirect('ts/types');
    }
    
    // ============================================================
    // МОДУЛЬ 2: УПРАВЛЕНИЕ СЕРВЕРАМИ (БЕЗ ПРИВЯЗКИ К ТИПУ)
    // ============================================================
    
    public function action_servers()
    {
        $ts = Model::factory('tss');
        $listTS = $ts->get_list_servers_only();
        
        $content = View::factory('ts/servers', array(
            'listTS' => $listTS,
            'is_logged_in' => Auth::instance()->logged_in()
        ));
        
        $this->template->content = $content;
    }
    
    public function action_control_servers()
    {
        $post = Validation::factory($this->request->post());
        $post->rule('todo', 'not_empty');
        
        if ($post->check()) {
            $todo = Arr::get($post, 'todo');
        } else {
            $todo = 'no';
        }
        
        switch ($todo) {
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
    
    /**
     * Добавление нового сервера (без типа)
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
                $result = $ts->add_server_only($data);
                
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
        
        $this->redirect('ts/servers');
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
            $ts = Model::factory('tss');
            $server = $ts->get_server_by_id($server_id);
            
            if ($server) {
                $content = View::factory('ts/edit_server', array(
                    'server' => $server,
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
        
        $this->redirect('ts/servers');
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
             ->rule('port', 'digit');
        
        if ($data->check()) {
            try {
                $ts = Model::factory('tss');
                $result = $ts->update_server($data);
                
                if ($result) {
                    Session::instance()->set('ok_mess', array(
                        'ok_mess' => __('Сервер :name обновлен успешно', array(':name' => Arr::get($data, 'name')))
                    ));
                } else {
                    Session::instance()->set('e_mess', array(
                        'e_mess' => __('Ошибка при обновлении сервера')
                    ));
                }
            } catch (Exception $e) {
                Session::instance()->set('e_mess', array(
                    'e_mess' => __('Ошибка при обновлении сервера: :error', array(':error' => $e->getMessage()))
                ));
            }
        } else {
            Session::instance()->set('e_mess', $data->errors('Valid_mess'));
        }
        
        $this->redirect('ts/servers');
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
                $ts = Model::factory('tss');
                $result = $ts->delete_server(Arr::get($data, 'id'));
                
                if ($result) {
                    Session::instance()->set('ok_mess', array(
                        'ok_mess' => __('Сервер удален успешно')
                    ));
                } else {
                    Session::instance()->set('e_mess', array(
                        'e_mess' => __('Ошибка при удалении сервера')
                    ));
                }
            } catch (Exception $e) {
                Session::instance()->set('e_mess', array(
                    'e_mess' => __('Ошибка при удалении сервера: :error', array(':error' => $e->getMessage()))
                ));
            }
        } else {
            Session::instance()->set('e_mess', $data->errors('Valid_mess'));
        }
        
        $this->redirect('ts/servers');
    }
    
    // ============================================================
    // МОДУЛЬ 3: ПРИВЯЗКА СЕРВЕРОВ К ТИПАМ
    // ============================================================
    
    public function action_links()
    {
        $ts = Model::factory('tss');
        $listLinks = $ts->get_links_list();
        $listServers = $ts->get_list_servers_only();
        $listTypes = $ts->get_list_type();
        
        $content = View::factory('ts/links', array(
            'listLinks' => $listLinks,
            'listServers' => $listServers,
            'listTypes' => $listTypes,
            'is_logged_in' => Auth::instance()->logged_in()
        ));
        
        $this->template->content = $content;
    }
    
    public function action_control_links()
    {
        $post = Validation::factory($this->request->post());
        $post->rule('todo', 'not_empty');
        
        if ($post->check()) {
            $todo = Arr::get($post, 'todo');
        } else {
            $todo = 'no';
        }
        
        switch ($todo) {
            case 'add_link':
                $this->_add_link();
                break;
            case 'del_link':
                $this->_delete_link();
                break;
        }
    }
    
    /**
     * Добавление связи сервер-тип
     */
    private function _add_link()
    {
        $data = Validation::factory($this->request->post());
        $data->rule('id_server', 'not_empty')
             ->rule('id_server', 'digit')
             ->rule('id_type', 'not_empty')
             ->rule('id_type', 'digit');
        
        if ($data->check()) {
            try {
                $ts = Model::factory('tss');
                $result = $ts->add_link($data);
                
                if ($result) {
                    Session::instance()->set('ok_mess', array(
                        'ok_mess' => __('Связь добавлена успешно')
                    ));
                } else {
                    Session::instance()->set('e_mess', array(
                        'e_mess' => __('Ошибка при добавлении связи')
                    ));
                }
            } catch (Exception $e) {
                Session::instance()->set('e_mess', array(
                    'e_mess' => __('Ошибка при добавлении связи: :error', array(':error' => $e->getMessage()))
                ));
            }
        } else {
            Session::instance()->set('e_mess', $data->errors('Valid_mess'));
        }
        
        $this->redirect('ts/links');
    }
    
    /**
     * Удаление связи
     */
    private function _delete_link()
    {
        $data = Validation::factory($this->request->post());
        $data->rule('id_server', 'not_empty')
             ->rule('id_server', 'digit')
             ->rule('id_type', 'not_empty')
             ->rule('id_type', 'digit');
        
        if ($data->check()) {
            try {
                $ts = Model::factory('tss');
                $result = $ts->delete_link($data);
                
                if ($result) {
                    Session::instance()->set('ok_mess', array(
                        'ok_mess' => __('Связь удалена успешно')
                    ));
                } else {
                    Session::instance()->set('e_mess', array(
                        'e_mess' => __('Ошибка при удалении связи')
                    ));
                }
            } catch (Exception $e) {
                Session::instance()->set('e_mess', array(
                    'e_mess' => __('Ошибка при удалении связи: :error', array(':error' => $e->getMessage()))
                ));
            }
        } else {
            Session::instance()->set('e_mess', $data->errors('Valid_mess'));
        }
        
        $this->redirect('ts/links');
    }
}