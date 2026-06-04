<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Holiday extends Controller_Template {
    
	 public function before()
		{
			parent::before();
			$session = Session::instance();

			// Передаем в шаблон флаг авторизации
			$this->is_admin = Auth::instance()->logged_in('admin');
			View::bind_global('is_admin', $this->is_admin);
		}
    
    /**
     * Список праздников
     */
    public function action_index()
    {
        $holidays = Model::factory('Holiday')->getHolidayList();
        
        $content = View::factory('holiday/index', array(
            'holidays' => $holidays,
        ));
        
        $this->template->content = $content;
    }
    
    /**
     * Добавление праздника
     */
    public function action_add()
    {
        if ($this->request->method() == HTTP_Request::POST) {
            $post = $this->request->post();
            
            $name = Arr::get($post, 'name');
            $date = Arr::get($post, 'date');
            
            // Валидация
            $errors = array();
            if (empty($name)) {
                $errors['name'] = __('Название праздника обязательно');
            }
            if (empty($date)) {
                $errors['date'] = __('Дата праздника обязательна');
            }
            
            if (empty($errors)) {
                $result = Model::factory('Holiday')->addHoliday($name, $date);
                
                if ($result) {
                    Session::instance()->set('message', __('Праздник успешно добавлен'));
                    Session::instance()->set('message_type', 'success');
                } else {
                    Session::instance()->set('message', __('Ошибка при добавлении праздника'));
                    Session::instance()->set('message_type', 'danger');
                }
                
                $this->redirect('holiday');
            }
            
            $content = View::factory('holiday/add', array(
                'errors' => $errors,
                'post' => $post,
            ));
        } else {
            $content = View::factory('holiday/add', array(
                'errors' => array(),
                'post' => array(),
            ));
        }
        
        $this->template->content = $content;
    }
    
    /**
     * Редактирование праздника
     */
    public function action_edit()
    {
        $id = $this->request->param('id');
        
        if ($id === NULL) {
            $this->redirect('holiday');
        }
        
        $holiday = Model::factory('Holiday')->getHolidayById($id);
        
        if (empty($holiday)) {
            $this->redirect('holiday');
        }
        
        if ($this->request->method() == HTTP_Request::POST) {
            $post = $this->request->post();
            
            $name = Arr::get($post, 'name');
            $date = Arr::get($post, 'date');
            
            // Валидация
            $errors = array();
            if (empty($name)) {
                $errors['name'] = __('Название праздника обязательно');
            }
            if (empty($date)) {
                $errors['date'] = __('Дата праздника обязательна');
            }
            
            if (empty($errors)) {
                $result = Model::factory('Holiday')->updateHoliday($id, $name, $date);
                
                if ($result) {
                    Session::instance()->set('message', __('Праздник успешно обновлен'));
                    Session::instance()->set('message_type', 'success');
                } else {
                    Session::instance()->set('message', __('Ошибка при обновлении праздника'));
                    Session::instance()->set('message_type', 'danger');
                }
                
                $this->redirect('holiday');
            }
            
            $content = View::factory('holiday/edit', array(
                'holiday' => $holiday,
                'errors' => $errors,
                'post' => $post,
            ));
        } else {
            $content = View::factory('holiday/edit', array(
                'holiday' => $holiday,
                'errors' => array(),
                'post' => array(),
            ));
        }
        
        $this->template->content = $content;
    }
    
    /**
     * Удаление праздника
     */
    public function action_delete()
    {
        $id = $this->request->param('id');
        
        if ($id !== NULL) {
            $result = Model::factory('Holiday')->deleteHoliday($id);
            
            if ($result) {
                Session::instance()->set('message', __('Праздник успешно удален'));
                Session::instance()->set('message_type', 'success');
            } else {
                Session::instance()->set('message', __('Ошибка при удалении праздника'));
                Session::instance()->set('message_type', 'danger');
            }
        }
        
        $this->redirect('holiday');
    }
}
