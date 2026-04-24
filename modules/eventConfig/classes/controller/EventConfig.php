<?php defined('SYSPATH') or die('No direct script access.');

class Controller_EventConfig extends Controller_Template {
    
    public $template = 'template';
    
    public function before()
    {
        parent::before();
		$session = Session::instance();
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
        
        $session = Session::instance();
        $flash_success = $session->get('flash_success');
        $flash_error = $session->get('flash_error');
        $session->delete('flash_success');
        $session->delete('flash_error');
        
        // Получаем ошибки валидации из сессии (если есть)
        $validation_errors = $session->get('validation_errors', array());
        $session->delete('validation_errors');
        // Получаем сохранённые данные из сессии (если есть)
        $old_input = $session->get('old_input', array());
        $session->delete('old_input');
        
        $content = View::factory('index')
            ->set('eventtypes', $eventtypes)
            ->set('parents', $parents)
            ->set('flash_success', $flash_success)
            ->set('flash_error', $flash_error)
            ->set('validation_errors', $validation_errors)
            ->set('old_input', $old_input);
        
        $this->template->content = $content;
    }
    
    /**
     * Редактирование типа события (форма)
     */
    public function action_edit()
    {
        $id = $this->request->param('id');
        if ($id === null || $id === '') {
            $this->redirect('eventConfig');
        }
        
        $model = Model::factory('Eventtype');
        $eventtype = $model->get_one($id);
        
        if (!$eventtype) {
            $this->redirect('eventConfig');
        }
        
        $session = Session::instance();
        
        // Получаем ошибки валидации из сессии (если есть)
        $validation_errors = $session->get('validation_errors', array());
        $session->delete('validation_errors');
        
        // Получаем сохранённые данные из сессии (если есть)
        $old_input = $session->get('old_input', array());
        $session->delete('old_input');
        
        // Если есть старые данные, подменяем поля eventtype
        if (!empty($old_input)) {
            // Объединяем данные из базы с введёнными (приоритет у old_input)
            foreach ($old_input as $key => $value) {
                if (array_key_exists($key, $eventtype)) {
                    $eventtype[$key] = $value;
                }
            }
            // Также убедимся, что ID_EVENTTYPE остаётся исходным
            $eventtype['ID_EVENTTYPE'] = $id;
        }
        
        $parents = $model->get_parents();
        
        $content = View::factory('edit')
            ->set('eventtype', $eventtype)
            ->set('parents', $parents)
            ->set('validation_errors', $validation_errors);
        
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
    
    // Валидация входных данных с учётом Kohana 3.3
    $validation = Validation::factory($_POST)
        ->rule('ID_EVENTTYPE', 'not_empty')
        ->rule('ID_EVENTTYPE', 'digit')
        ->rule('NAME', 'not_empty')
        ->rule('NAME', 'max_length', array(':value', 255))
        ->rule('FLAG', 'digit')
        ->rule('FLAG', 'range', array(':value', 0, 1))
        ->rule('COLOR', 'digit')
        ->rule('COLOR', 'range', array(':value', 0, 16777215))
        ->rule('SOUND', 'max_length', array(':value', 255))
        ->rule('ACTIVE', 'digit')
        ->rule('ACTIVE', 'range', array(':value', 0, 1));
    
    // ID_PARENT может быть пустым или цифровым
    $id_parent = $this->request->post('ID_PARENT');
    if ($id_parent !== '' && $id_parent !== null) {
        $validation->rule('ID_PARENT', 'digit');
    }
    
    $session = Session::instance();
    
    if (!$validation->check()) {
        // Сохраняем ошибки валидации в сессии
        $errors = $validation->errors('validation');
        $session->set('validation_errors', $errors);
        // Сохраняем введённые данные для повторного заполнения формы
        $session->set('old_input', $_POST);
        // Перенаправляем обратно на главную страницу (модальное окно откроется через JS)
        $this->redirect('eventConfig');
        return;
    }
    
    // Проверка существования записи
    $model = Model::factory('Eventtype');
    $existing = $model->get_one($id);
    if (!$existing) {
        $session->set('flash_error', 'Событие с указанным ID не найдено');
        $this->redirect('eventConfig');
        return;
    }
    
    // Подготовка данных для обновления
    $data = array(
        'NAME' => $this->request->post('NAME'),
        'FLAG' => (int)$this->request->post('FLAG'),
        'COLOR' => (int)$this->request->post('COLOR'),
        'SOUND' => $this->request->post('SOUND'),
        'ACTIVE' => (int)$this->request->post('ACTIVE'),
        'ID_PARENT' => $this->request->post('ID_PARENT') ? (int)$this->request->post('ID_PARENT') : NULL,
    );
    
    // Гарантируем, что NAME и SOUND не NULL
    if ($data['NAME'] === null) $data['NAME'] = '';
    if ($data['SOUND'] === null) $data['SOUND'] = '';
    
    try {
        $model->update_eventtype($id, $data);
        // Успешное обновление
        $session->set('flash_success', 'Изменения успешно сохранены.');
    } catch (Exception $e) {
        // Ошибка базы данных
        $session->set('flash_error', 'Ошибка при сохранении: ' . $e->getMessage());
    }
    
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

        // Валидация входных данных с учётом Kohana 3.3
        $validation = Validation::factory($_POST)
            ->rule('NAME', 'not_empty')
            ->rule('NAME', 'max_length', array(':value', 255))
            ->rule('FLAG', 'digit')
            ->rule('FLAG', 'range', array(':value', 0, 1))
            ->rule('COLOR', 'digit')
            ->rule('COLOR', 'range', array(':value', 0, 16777215))
            ->rule('SOUND', 'max_length', array(':value', 255))
            ->rule('ACTIVE', 'digit')
            ->rule('ACTIVE', 'range', array(':value', 0, 1));
        
        // ID_PARENT может быть пустым или цифровым
        $id_parent = $this->request->post('ID_PARENT');
        if ($id_parent !== '' && $id_parent !== null) {
            $validation->rule('ID_PARENT', 'digit');
        }
        
        $session = Session::instance();
        
        if (!$validation->check()) {
            // Сохраняем ошибки валидации в сессии
            $errors = $validation->errors('validation');
            $session->set('validation_errors', $errors);
            // Сохраняем введённые данные для повторного заполнения формы
            $session->set('old_input', $_POST);
            // Перенаправляем обратно на главную страницу (вторая закладка будет активна)
            $this->redirect('eventConfig');
            return;
        }

        $data = array(
            'NAME' => $this->request->post('NAME'),
            'FLAG' => (int)$this->request->post('FLAG'),
            'COLOR' => (int)$this->request->post('COLOR'),
            'SOUND' => $this->request->post('SOUND'),
            'ACTIVE' => (int)$this->request->post('ACTIVE'),
            'ID_PARENT' => $this->request->post('ID_PARENT') ? (int)$this->request->post('ID_PARENT') : NULL,
        );

        // Гарантируем, что NAME и SOUND не NULL
        if ($data['NAME'] === null) $data['NAME'] = '';
        if ($data['SOUND'] === null) $data['SOUND'] = '';

        $model = Model::factory('Eventtype');
        try {
            $model->insert_eventtype($data);
            // Успешное добавление
            $session->set('flash_success', 'Новый тип события успешно добавлен.');
        } catch (Exception $e) {
            // Ошибка базы данных
            $session->set('flash_error', 'Ошибка при добавлении: ' . $e->getMessage());
        }
        
        $this->redirect('eventConfig');
    }
}
