<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * 10.03.2026 создано вместе с deepseek контроллер для управления файлом конфигурации
 * 11.03.2026 добавлена поддержка папки backup
 */

class Controller_Setting extends Controller_Template {
    
    public $template = 'template';
    
    /**
     * @var array Конфигурация модуля
     */
    protected $_module_config;
    
    /**
     * @var string Путь к конфигурационному файлу
     */
    protected $_config_file;
    
    /**
     * @var string Путь к папке с бэкапами
     */
    protected $_backup_dir;
    
    public function before()
    {
        parent::before();
        
        // Загружаем конфиг модуля
        $this->_module_config = Kohana::$config->load('setting');
        
        // Проверяем, включен ли модуль
        if (!$this->_module_config->get('enable_module', true)) {
            throw new HTTP_Exception_404('Module disabled');
        }
        
        // Проверяем права доступа
        $required_role = $this->_module_config->get('required_role', 'admin');
        if (!Auth::instance()->logged_in($required_role)) {
            HTTP::redirect('dashboard');
        }
        
        // Определяем путь к конфигурационному файлу
        $this->_config_file = APPPATH . 'config/artonitcity_config.php';
        
        // Определяем путь к папке с бэкапами
        $this->_backup_dir = APPPATH . 'config\backup';
        
        // Создаем папку для бэкапов, если её нет
        if (!is_dir($this->_backup_dir)) {
            mkdir($this->_backup_dir, 0755, true);
        }
        
        // Подключаем i18n
        I18n::lang('ru');
    }
    
    /**
     * Главная страница - редактирование конфигурации
     */
    public function action_index()
    {
        $message = Session::instance()->get_once('message', '');
        $config_data = $this->_read_config();
        
        // Обработка сохранения
        if ($this->request->method() === Request::POST) {
            $message = $this->_process_save($config_data);
        }
        
        // Получаем структуру для отображения
        $config_structure = $this->_get_config_structure();
        
        $this->template->title = __('setting.page_title');
        $this->template->content = View::factory('setting/edit')
            ->set('config_structure', $config_structure)
            ->set('config_data', $config_data)
            ->set('message', $message)
            ->set('config_file', $this->_config_file)
            ->set('module_config', $this->_module_config);
    }
    
    /**
     * Просмотр бэкапов
     */
    public function action_backups()
    {
        $backups = $this->_get_backups();
        
        $this->template->title = __('setting.backups_title');
        $this->template->content = View::factory('setting/backups')
            ->set('backups', $backups);
    }
    
    /**
     * Восстановление из бэкапа
     */
    public function action_restore()
    {
        $backup_file = $this->request->query('file');
        
        if ($this->_restore_backup($backup_file)) {
            Session::instance()->set('message', '<div class="alert alert-success">' . __('setting.restore_success') . '</div>');
        } else {
            Session::instance()->set('message', '<div class="alert alert-danger">' . __('setting.restore_error') . '</div>');
        }
        
        HTTP::redirect('setting');
    }
    
    /**
     * Скачивание бэкапа
     */
    public function action_download_backup()
    {
        $backup_file = $this->request->query('file');
        
        // Проверяем, что файл находится в папке backup (безопасность)
        $real_path = realpath($backup_file);
        $backup_dir_real = realpath($this->_backup_dir);
        
        if ($real_path && strpos($real_path, $backup_dir_real) === 0 && file_exists($real_path)) {
            $this->response->send_file($real_path, basename($real_path));
        } else {
            HTTP::redirect('setting/backups');
        }
    }
    
    /**
     * Чтение конфигурационного файла
     */
    protected function _read_config()
    {
        if (!file_exists($this->_config_file)) {
            throw new Kohana_Exception('Configuration file not found: :file', [
                ':file' => $this->_config_file
            ]);
        }
        
        return include $this->_config_file;
    }
    
    /**
     * Обработка сохранения
     */
    protected function _process_save(&$config_data)
    {
        $post = $this->request->post();
        $protected_keys = $this->_module_config->get('protected_keys', []);
        
        try {
            // Создаем копию для изменений
            $new_config = $config_data;
            
            // Обновляем значения
            foreach ($new_config as $key => $value) {
                // Пропускаем защищенные ключи
                if (in_array($key, $protected_keys)) {
                    continue;
                }
                
                // Обрабатываем в зависимости от типа
                if (is_bool($value)) {
                    // Для булевых значений
                    $new_config[$key] = $this->_process_boolean($key, $post);
                } 
                elseif (is_array($value)) {
                    // Для массивов
                    $new_config[$key] = $this->_process_array($key, $post, $value);
                } 
                elseif (is_numeric($value)) {
                    // Для чисел
                    $new_config[$key] = $this->_process_numeric($key, $post, $value);
                } 
                else {
                    // Для строк
                    $new_config[$key] = $this->_process_string($key, $post, $value);
                }
            }
            
            // Сохраняем
            if ($this->_save_config($new_config)) {
                // Обновляем оригинальный массив
                $config_data = $new_config;
                return '<div class="alert alert-success">' . __('setting.save_success') . '</div>';
            } else {
                return '<div class="alert alert-danger">' . __('setting.save_error') . '</div>';
            }
            
        } catch (Exception $e) {
            // Логируем ошибку
            Kohana::$log->add(Log::ERROR, 'Save error: ' . $e->getMessage());
            return '<div class="alert alert-danger">Ошибка: ' . HTML::chars($e->getMessage()) . '</div>';
        }
    }
    
    /**
     * Обработка булевых значений
     */
    protected function _process_boolean($key, $post)
    {
        return isset($post[$key]) && ($post[$key] === 'true' || $post[$key] === '1' || $post[$key] === 'on');
    }
    
    /**
     * Обработка числовых значений
     */
    protected function _process_numeric($key, $post, $default)
    {
        if (!isset($post[$key])) {
            return $default;
        }
        
        // Проверяем, не массив ли пришел
        if (is_array($post[$key])) {
            return $default;
        }
        
        return is_numeric($post[$key]) ? (int) $post[$key] : $default;
    }
    
    /**
     * Обработка строковых значений
     */
    protected function _process_string($key, $post, $default)
    {
        if (!isset($post[$key])) {
            return $default;
        }
        
        // Проверяем, не массив ли пришел
        if (is_array($post[$key])) {
            return $default;
        }
        
        return (string) $post[$key];
    }
    
    /**
     * Обработка массивов
     */
    protected function _process_array($key, $post, $original_value)
    {
        // Определяем тип массива
        $is_assoc = $this->_is_assoc($original_value);
        
        // Пробуем получить значение из POST
        if (isset($post[$key]) && is_array($post[$key])) {
            // Пришло как массив (например, от чекбоксов)
            return $this->_process_assoc_array($post[$key], $original_value);
        } 
        elseif (isset($post[$key . '_array'])) {
            // Пришло как строка с запятыми
            return $this->_parse_array($post[$key . '_array']);
        }
        
        // Если ничего не пришло, возвращаем пустой массив для индексированных
        // или исходные значения с false для ассоциативных
        if ($is_assoc) {
            $result = [];
            foreach ($original_value as $k => $v) {
                $result[$k] = false;
            }
            return $result;
        }
        
        return [];
    }
    
    /**
     * Проверка, является ли массив ассоциативным
     */
    protected function _is_assoc($array)
    {
        if (!is_array($array)) {
            return false;
        }
        return array_keys($array) !== range(0, count($array) - 1);
    }
    
    /**
     * Обработка ассоциативного массива (с ключами)
     */
    protected function _process_assoc_array($post_value, $original_value)
    {
        $result = [];
        
        foreach ($original_value as $k => $v) {
            if (is_bool($v)) {
                // Для булевых значений
                $result[$k] = isset($post_value[$k]) && ($post_value[$k] === 'true' || $post_value[$k] === '1' || $post_value[$k] === 'on');
            } elseif (isset($post_value[$k])) {
                // Для других типов
                $result[$k] = $post_value[$k];
            } else {
                $result[$k] = $v;
            }
        }
        
        return $result;
    }
    
    /**
     * Парсинг строки в массив
     */
    protected function _parse_array($string)
    {
        if (empty($string)) {
            return [];
        }
        
        // Убеждаемся, что это строка
        if (!is_string($string)) {
            return [];
        }
        
        $items = explode(',', $string);
        $result = [];
        
        foreach ($items as $item) {
            $item = trim($item);
            if ($item !== '') {
                if (is_numeric($item)) {
                    $result[] = (int) $item;
                } else {
                    $result[] = $item;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Сохранение конфигурации
     */
    protected function _save_config($data)
    {
        // Создаем бэкап
        $this->_create_backup();
        
        // Формируем содержимое
        $content = "<?php defined('SYSPATH') or die('No direct script access.');\n\n";
        $content .= "/**\n";
        $content .= " * Конфигурационный файл Artonit City\n";
        $content .= " * Сгенерировано: " . date('Y-m-d H:i:s') . "\n";
        $content .= " */\n\n";
        $content .= "return array(\n";
        
        foreach ($data as $key => $value) {
            $content .= $this->_format_value($key, $value, 1);
        }
        
        $content .= ");\n";
        
        // Сохраняем
        return file_put_contents($this->_config_file, $content) !== false;
    }
    
    /**
     * Форматирование значения
     */
    protected function _format_value($key, $value, $indent = 1)
    {
        $indent_str = str_repeat('    ', $indent);
        
        if (is_array($value)) {
            $result = $indent_str . "'$key' => array(\n";
            foreach ($value as $k => $v) {
                if (is_string($k)) {
                    $result .= $indent_str . "    '" . addslashes($k) . "' => ";
                } else {
                    $result .= $indent_str . "    ";
                }
                $result .= $this->_format_primitive($v);
                $result .= ",\n";
            }
            $result .= $indent_str . "),\n";
        } else {
            $result = $indent_str . "'$key' => " . $this->_format_primitive($value) . ",\n";
        }
        
        return $result;
    }
    
    /**
     * Форматирование примитивных типов
     */
    protected function _format_primitive($value)
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        } 
        
        if (is_numeric($value)) {
            return $value;
        } 
        
        if (is_string($value)) {
            // Экранируем только необходимые символы для PHP
            // - обратную косую черту \ => \\
            // - одинарную кавычку ' => \'
            $escaped = str_replace(
                ['\\', "'"],  // Что заменяем
                ['\\\\', "\\'"],  // На что заменяем
                $value
            );
            return "'" . $escaped . "'";
        } 
        
        if (is_null($value)) {
            return 'null';
        }
        
        // Для остальных типов приводим к строке
        return "'" . (string)$value . "'";
    }
    
    /**
     * Создание бэкапа в папке backup
     */
    protected function _create_backup()
    {
        $date_format = $this->_module_config->get('backup_date_format', 'Y-m-d_H-i-s');
        $backup_file = $this->_backup_dir . '/artonitcity_config.php.backup_' . date($date_format);
        
        copy($this->_config_file, $backup_file);
        
        // Ограничиваем количество бэкапов
        $this->_cleanup_old_backups();
        
        return $backup_file;
    }
    
    /**
     * Очистка старых бэкапов в папке backup
     */
    protected function _cleanup_old_backups()
    {
        $max_backups = $this->_module_config->get('max_backups', 50);
        $pattern = $this->_backup_dir . '/artonitcity_config.php.backup_*';
        $backups = glob($pattern);
        
        if (count($backups) > $max_backups) {
            // Сортируем по дате (старые первые)
            usort($backups, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            // Удаляем лишние
            $to_delete = array_slice($backups, 0, count($backups) - $max_backups);
            foreach ($to_delete as $file) {
                unlink($file);
            }
        }
    }
    
    /**
     * Получение списка бэкапов из папки backup
     */
    protected function _get_backups()
    {
        $pattern = $this->_backup_dir . '/artonitcity_config.php.backup_*';
        $backups = glob($pattern);
        
        $result = [];
        foreach ($backups as $backup) {
            $result[] = [
                'file' => $backup,
                'filename' => basename($backup),
                'date' => date('Y-m-d H:i:s', filemtime($backup)),
                'size' => filesize($backup),
                'readable_size' => $this->_format_size(filesize($backup))
            ];
        }
        
        // Сортируем по дате (новые сверху)
        usort($result, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return $result;
    }
    
    /**
     * Восстановление из бэкапа
     */
    protected function _restore_backup($backup_file)
    {
        if ($backup_file && file_exists($backup_file)) {
            // Проверяем, что файл находится в папке backup (безопасность)
            $real_path = realpath($backup_file);
            $backup_dir_real = realpath($this->_backup_dir);
            
            if ($real_path && strpos($real_path, $backup_dir_real) === 0) {
                return copy($real_path, $this->_config_file);
            }
        }
        return false;
    }
    
    /**
     * Форматирование размера файла
     */
    protected function _format_size($bytes)
    {
        $units = ['Б', 'КБ', 'МБ', 'ГБ'];
        $i = 0;
        while ($bytes > 1024 && $i < 3) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * Получение структуры для отображения
     */
    protected function _get_config_structure()
    {
        return [
            'Основные настройки' => [
                'city_name' => 'Название города/объекта',
                'developer' => 'Разработчик',
                'ver' => 'Версия',
                'timeUpdate' => 'Дата обновления',
                'lightVerDay' => 'Подсветка версии (дней)',
            ],
            'Пути и директории' => [
                'dir_log' => 'Директория логов',
                'dir_compare' => 'Директория сравнения',
                'curl_place' => 'Путь к curl',
            ],
            'Настройки отображения' => [
                'name_device_fro_test' => 'Тестовое устройство',
                'count_day_befor_end_time' => 'Дней до окончания',
                'stat_day_befor' => 'Статистика за дней',
                'baseFormatRfid' => 'Формат RFID',
            ],
            'Коды аналитики' => [
                'analit_ok' => 'OK (через запятую)',
                'analit_err' => 'Ошибки (через запятую)',
                'analit_transit' => 'Переходные (через запятую)',
            ],
            'Доступ без авторизации' => [
                'view_without_auth' => 'view_without_auth',
            ],
            'Основные окна' => [
                'main_windows' => 'main_windows',
            ],
        ];
    }
}
