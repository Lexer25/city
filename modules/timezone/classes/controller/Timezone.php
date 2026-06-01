<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Timezone extends Controller_Template {
    
    private $maxTimezones = 16; // Максимальное количество временных зон
    
    public function before()
    {
        parent::before();
        $session = Session::instance();
    }
    
    /**
     * Список временных зон
     */
    public function action_index()
    {
        $timezones = Model::factory('Timezone')->getTimezoneList();
        
        $content = View::factory('timezone/index', array(
            'timezones' => $timezones,
            'maxTimezones' => $this->maxTimezones,
            'currentCount' => count($timezones),
        ));
        
        $this->template->content = $content;
    }
    
    /**
     * Добавление временной зоны
     */
    public function action_add()
    {
        // Проверяем количество существующих зон
        $currentCount = Model::factory('Timezone')->getTimezoneCount();
        
        if ($currentCount >= $this->maxTimezones) {
            Session::instance()->set('message', __('Достигнуто максимальное количество временных зон (:count из :max)', array(
                ':count' => $currentCount,
                ':max' => $this->maxTimezones
            )));
            Session::instance()->set('message_type', 'danger');
            $this->redirect('timezone');
        }
        
        if ($this->request->method() == HTTP_Request::POST) {
            $post = $this->request->post();
            
            $name = Arr::get($post, 'name');
            $timeStart = Arr::get($post, 'time_start');
            $timeEnd = Arr::get($post, 'time_end');
            $flag = $this->buildFlagMask($post);
            
            // Валидация
            $errors = array();
            if (empty($name)) {
                $errors['name'] = __('Название временной зоны обязательно');
            }
            if (empty($timeStart)) {
                $errors['time_start'] = __('Время начала обязательно');
            }
            if (empty($timeEnd)) {
                $errors['time_end'] = __('Время окончания обязательно');
            }
            
            if (empty($errors)) {
                $result = Model::factory('Timezone')->addTimezone($name, $timeStart, $timeEnd, $flag);
                
                if ($result) {
                    Session::instance()->set('message', __('Временная зона успешно добавлена'));
                    Session::instance()->set('message_type', 'success');
                } else {
                    Session::instance()->set('message', __('Ошибка при добавлении временной зоны'));
                    Session::instance()->set('message_type', 'danger');
                }
                
                $this->redirect('timezone');
            }
            
            $content = View::factory('timezone/add', array(
                'errors' => $errors,
                'post' => $post,
                'maxTimezones' => $this->maxTimezones,
                'currentCount' => $currentCount,
            ));
        } else {
            $content = View::factory('timezone/add', array(
                'errors' => array(),
                'post' => array(),
                'maxTimezones' => $this->maxTimezones,
                'currentCount' => $currentCount,
            ));
        }
        
        $this->template->content = $content;
    }
    
    /**
     * Редактирование временной зоны
     */
    public function action_edit()
    {
        $id = $this->request->param('id');
        
        if ($id === NULL) {
            $this->redirect('timezone');
        }
        
        $timezone = Model::factory('Timezone')->getTimezoneById($id);
        
        if (empty($timezone)) {
            $this->redirect('timezone');
        }
        
        if ($this->request->method() == HTTP_Request::POST) {
            $post = $this->request->post();
            
            $name = Arr::get($post, 'name');
            $timeStart = Arr::get($post, 'time_start');
            $timeEnd = Arr::get($post, 'time_end');
            $flag = $this->buildFlagMask($post);
            
            // Валидация
            $errors = array();
            if (empty($name)) {
                $errors['name'] = __('Название временной зоны обязательно');
            }
            if (empty($timeStart)) {
                $errors['time_start'] = __('Время начала обязательно');
            }
            if (empty($timeEnd)) {
                $errors['time_end'] = __('Время окончания обязательно');
            }
            
            if (empty($errors)) {
                $result = Model::factory('Timezone')->updateTimezone($id, $name, $timeStart, $timeEnd, $flag);
                
                if ($result) {
                    Session::instance()->set('message', __('Временная зона успешно обновлена'));
                    Session::instance()->set('message_type', 'success');
                } else {
                    Session::instance()->set('message', __('Ошибка при обновлении временной зоны'));
                    Session::instance()->set('message_type', 'danger');
                }
                
                $this->redirect('timezone');
            }
            
            $content = View::factory('timezone/edit', array(
                'timezone' => $timezone,
                'errors' => $errors,
                'post' => $post,
            ));
        } else {
            // Расшифровываем флаги для отображения
            $flags = $this->parseFlagMask(Arr::get($timezone, 'flag', 0));
            
            $content = View::factory('timezone/edit', array(
                'timezone' => $timezone,
                'flags' => $flags,
                'errors' => array(),
                'post' => array(),
            ));
        }
        
        $this->template->content = $content;
    }
    
    /**
     * Удаление временной зоны
     */
    public function action_delete()
    {
        $id = $this->request->param('id');
        
        if ($id !== NULL) {
            $result = Model::factory('Timezone')->deleteTimezone($id);
            
            if ($result) {
                Session::instance()->set('message', __('Временная зона успешно удалена'));
                Session::instance()->set('message_type', 'success');
            } else {
                Session::instance()->set('message', __('Ошибка при удалении временной зоны'));
                Session::instance()->set('message_type', 'danger');
            }
        }
        
        $this->redirect('timezone');
    }
    
    /**
     * Построение битовой маски из данных формы
     */
    private function buildFlagMask($post)
    {
        $mask = 0;
        
        // Дни недели: 1-пн, 2-вт, 4-ср, 8-чт, 10-пт, 20-сб, 40-вс
        if (Arr::get($post, 'day_mon')) $mask |= 1;
        if (Arr::get($post, 'day_tue')) $mask |= 2;
        if (Arr::get($post, 'day_wed')) $mask |= 4;
        if (Arr::get($post, 'day_thu')) $mask |= 8;
        if (Arr::get($post, 'day_fri')) $mask |= 16;
        if (Arr::get($post, 'day_sat')) $mask |= 32;
        if (Arr::get($post, 'day_sun')) $mask |= 64;
        
        // Специальные режимы
        if (Arr::get($post, 'flag_holidays')) $mask |= 128;   // 80 - праздники
        if (Arr::get($post, 'flag_night')) $mask |= 256;     // 100 - ночная
        if (Arr::get($post, 'flag_roundclock')) $mask |= 512; // 200 - круглосуточная
        
        return $mask;
    }
    
    /**
     * Разбор битовой маски для отображения
     */
    private function parseFlagMask($mask)
    {
        return array(
            'day_mon' => ($mask & 1) ? true : false,
            'day_tue' => ($mask & 2) ? true : false,
            'day_wed' => ($mask & 4) ? true : false,
            'day_thu' => ($mask & 8) ? true : false,
            'day_fri' => ($mask & 16) ? true : false,
            'day_sat' => ($mask & 32) ? true : false,
            'day_sun' => ($mask & 64) ? true : false,
            'flag_holidays' => ($mask & 128) ? true : false,
            'flag_night' => ($mask & 256) ? true : false,
            'flag_roundclock' => ($mask & 512) ? true : false,
        );
    }
}