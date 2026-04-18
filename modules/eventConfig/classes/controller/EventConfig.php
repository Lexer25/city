<?php defined('SYSPATH') or die('No direct script access.');

class Controller_EventConfig extends Controller_Template {
    
    public $template = 'template';
    
    public function before()
    {
        parent::before();
        // Устанавливаем активный пункт меню
        $_SESSION['menu_active'] = 'eventConfig';
    }
    
    /**
     * Главная страница - список типов событий
     */
    public function action_index()
    {
        $model = Model::factory('Eventtype');
        $eventtypes = $model->get_all();
        $parents = $model->get_parents();
        
        $content = View::factory('index')
            ->set('eventtypes', $eventtypes)
            ->set('parents', $parents);
        
        $this->template->content = $content;
    }
    
    /**
     * Редактирование типа события (форма)
     */
    public function action_edit()
    {
        $id = $this->request->param('id');
        if (!$id) {
            $this->redirect('eventConfig');
        }
        
        $model = Model::factory('Eventtype');
        $eventtype = $model->get_one($id);
        
        if (!$eventtype) {
            $this->redirect('eventConfig');
        }
        
        $parents = $model->get_parents();
        
        $content = View::factory('edit')
            ->set('eventtype', $eventtype)
            ->set('parents', $parents);
        
        $this->template->content = $content;
    }
    
    /**
     * Сохранение изменений типа события
     */
    public function action_save()
    {
        if ($this->request->method() !== 'POST') {
            $this->redirect('eventConfig');
        }
        
        $id = $this->request->post('ID_EVENTTYPE');
        $data = array(
            'NAME' => $this->request->post('NAME'),
            'FLAG' => (int)$this->request->post('FLAG'),
            'COLOR' => (int)$this->request->post('COLOR'),
            'SOUND' => $this->request->post('SOUND'),
            'ACTIVE' => (int)$this->request->post('ACTIVE'),
            'ID_PARENT' => $this->request->post('ID_PARENT') ? (int)$this->request->post('ID_PARENT') : NULL,
        );
        
        $model = Model::factory('Eventtype');
        $model->update_eventtype($id, $data);
        
        $this->redirect('eventConfig');
    }
    
    /**
     * Добавление нового типа события (форма)
     */
    public function action_add()
    {
        $model = Model::factory('Eventtype');
        $parents = $model->get_parents();
        
        $content = View::factory('add')
            ->set('parents', $parents);
        
        $this->template->content = $content;
    }
    
    /**
     * Сохранение нового типа события
     */
    public function action_create()
    {
        if ($this->request->method() !== 'POST') {
            $this->redirect('eventConfig');
        }
        
        $data = array(
            'NAME' => $this->request->post('NAME'),
            'FLAG' => (int)$this->request->post('FLAG'),
            'COLOR' => (int)$this->request->post('COLOR'),
            'SOUND' => $this->request->post('SOUND'),
            'ACTIVE' => (int)$this->request->post('ACTIVE'),
            'ID_PARENT' => $this->request->post('ID_PARENT') ? (int)$this->request->post('ID_PARENT') : NULL,
        );
        
        $model = Model::factory('Eventtype');
        $model->insert_eventtype($data);
        
        $this->redirect('eventConfig');
    }
}