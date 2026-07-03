<?php
// application/classes/Controller/Template.php
class Controller_Template extends Kohana_Controller_Template {
    
   
	/**
     * Переопределяем before() для автоматической подготовки данных
     */
    public function before() {
        // Вызываем родительский метод
        parent::before();
        
        // Если шаблон не задан или не является объектом View, выходим
        if (!is_object($this->template)) {
            return;
        }
        
        // Загружаем конфиг
        $config = Kohana::$config->load('artonitcity_config');
        
        
        // Подготавливаем данные
        $this->_prepareTemplateData($config);
    }
    
    /**
     * Подготовка данных для шаблона
     */
    protected function _prepareTemplateData($config) {
        // Проверяем, установлены ли уже данные (чтобы не перезаписывать)
        // В Kohana View нет метода as_array(), используем прямой доступ к переменным
        $has_site = isset($this->template->site);
        $has_menu = isset($this->template->menu);
        $has_auth = isset($this->template->auth);
        $has_version = isset($this->template->version);
        $has_flash = isset($this->template->flash);
        $has_odbc = isset($this->template->has_odbc);
        
        // Подготавливаем данные только для отсутствующих ключей
        if (!$has_site) {
            $this->template->set('site', array(
                'city_name' => Arr::get($config, 'city_name', ''),
                'title' => $this->_getPageTitle(),
                'full_width' => isset($this->full_width) ? $this->full_width : false,
            ));
        }
        
        if (!$has_menu) {
            $this->template->set('menu', array(
                'menu_html' => Menu_Renderer::render('menu', 'nav navbar-nav'),
                'adm_html' => Menu_Renderer::render('adm', 'nav navbar-nav'),
            ));
        }
        
        if (!$has_auth) {
            $this->template->set('auth', $this->_getAuthData());
        }
        
        if (!$has_version) {
            $this->template->set('version', $this->_getVersionData($config));
        }
        
        if (!$has_flash) {
            $this->template->set('flash', $this->_getFlashMessage());
        }
	
		if (!$has_odbc) {
            $this->template->set('odbc', $this->_getODBC());
        }
		
		
    }
    
/**
 * Получение DSN из конфигурации базы данных
 */
protected function _getODBC() {
    $config = Kohana::$config->load('database');
	//echo Debug::vars('78', $config['fb']['connection']['dsn']);exit;
    $result=array(
		
		'dsn'=> isset($config['fb']['connection']['dsn'])?  $config['fb']['connection']['dsn'] : '---',
	
	
	);
   
    
    return $result;
}
    
    /**
     * Получение заголовка страницы
     */
    protected function _getPageTitle() {
        return isset($this->title) ? $this->title : '';
    }
    
    /**
     * Получение данных авторизации
     */
    protected function _getAuthData() {
        $auth = Auth::instance();
        $session = Session::instance();
        
        return array(
            'logged_in' => $auth->logged_in(),
            'username'  => $auth->logged_in() ? $auth->get_user() : '',
            'errors'    => $session->get_once('login_errors', array()),
            'csrf_token' => $this->_getCsrfToken(),
            'post_data' => array(
                'username' => Arr::get($_POST, 'username', ''),
                'remember' => Arr::get($_POST, 'remember', false),
            ),
        );
    }
    
    /**
     * Получение CSRF-токена
     */
    protected function _getCsrfToken() {
        if (class_exists('Security') && method_exists('Security', 'token')) {
            return Security::token();
        }
        return null;
    }
    
    /**
     * Получение данных о версии
     */
    protected function _getVersionData($config) {
        $result = array(
            'text' => '',
            'color' => '',
            'ver' => Arr::get($config, 'ver', ''),
            'timeUpdate' => Arr::get($config, 'timeUpdate', null),
        );
        
        if (empty($result['ver'])) {
            return $result;
        }
        
        $lightVerDay = Arr::get($config, 'lightVerDay', 3);
        $timeUpdate = $result['timeUpdate'];
        
        if ($timeUpdate) {
            try {
                $current_date = new DateTime();
                $update_date = new DateTime($timeUpdate);
                $interval = $current_date->diff($update_date);
                $days_diff = $interval->days;
                
                if ($days_diff < $lightVerDay) {
                    $result['color'] = 'label-success';
                    $result['text'] = __('<span class="label :color">Версия :ver обновление :timeUpdate</span>', array(
                        ':ver' => HTML::chars($result['ver']),
                        ':timeUpdate' => HTML::chars($timeUpdate),
                        ':color' => $result['color'],
                    ));
                } else {
                    $result['text'] = __('Версия :ver обновление :timeUpdate', array(
                        ':ver' => HTML::chars($result['ver']),
                        ':timeUpdate' => HTML::chars($timeUpdate),
                    ));
                }
            } catch (Exception $e) {
                Kohana::$log->add(Log::ERROR, 'Invalid date format in config: :date', [
                    ':date' => $timeUpdate
                ]);
                $result['text'] = __('Версия :ver', array(':ver' => HTML::chars($result['ver'])));
            }
        } else {
            $result['text'] = __('Версия :ver', array(':ver' => HTML::chars($result['ver'])));
        }
        
        return $result;
    }
    
    /**
     * Получение flash-сообщения
     */
    protected function _getFlashMessage() {
        $session = Session::instance();
        $flash = $session->get('flash_message');
        
        if ($flash) {
            $session->delete('flash_message');
            
            $type = Arr::get($flash, 'type', 'info');
            $alert_class = $this->_getAlertClass($type);
            
            return array(
                'type' => $type,
                'text' => Arr::get($flash, 'text', ''),
                'class' => $alert_class,
            );
        }
        
        return null;
    }
    
    /**
     * Получение класса для alert
     */
    protected function _getAlertClass($type) {
        $map = array(
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info',
        );
        
        return Arr::get($map, $type, 'alert-info');
    }
    
    /**
     * Установка заголовка страницы
     */
    protected function set_title($title) {
        $this->title = $title;
        
        // Если шаблон уже существует, обновляем данные
        if (isset($this->template) && is_object($this->template)) {
            $site = $this->template->get('site');
            if ($site !== null) {
                $site['title'] = $title;
                $this->template->set('site', $site);
            }
        }
    }
    
    /**
     * Установка full-width режима
     */
    protected function set_full_width($enabled = true) {
        $this->full_width = $enabled;
        
        // Если шаблон уже существует, обновляем данные
        if (isset($this->template) && is_object($this->template)) {
            $site = $this->template->get('site');
            if ($site !== null) {
                $site['full_width'] = $enabled;
                $this->template->set('site', $site);
            }
        }
    }
}
