<?php defined('SYSPATH') or die('No direct script access.');

class Controller_About extends Controller_Template {

    public $template = 'template';

    public function before()
    {
        parent::before();
        
        // Устанавливаем заголовок страницы
        $this->template->title = __('О программе');
    }

    public function action_index()
    {
        // Получаем информацию о разработчике
        $developer_info = array(
            'name' => 'Артонит Сити панель управления СКУД',
            'company' => 'ООО "Артсек"',
            'email' => 'support@artsec.ru',
            'website_1' => 'http://artsec.ru',
            'website_2' => 'http://artonit.ru'
        );
        
        // Получаем историю версий
        $version_history = $this->get_version_history();
        
        // Получаем текущую версию
        $current_version = $this->get_current_version();
        
        // Получаем список всех модулей с их версиями
        $modules_list = $this->get_all_modules_with_versions();
        
        // Формируем контент
        $content = View::factory('about/index')
            ->set('developer', $developer_info)
            ->set('version_history', $version_history)
            ->set('current_version', $current_version)
            ->set('modules_list', $modules_list);
            
        $this->template->content = $content;
    }
    
    /**
     * Получение истории версий из файлов
     * @return array Массив с информацией о версиях
     */
    private function get_version_history()
    {
        $versions = array();
        $version_dir = MODPATH . 'about/versions/';
        
        if (is_dir($version_dir)) {
            $files = scandir($version_dir);
            
            foreach ($files as $file) {
                if (preg_match('/^v?([0-9]+\.[0-9]+\.[0-9]+)$/i', $file, $matches)) {
                    $version = $matches[1];
                    $version_file = $version_dir . $file;
                    
                    if (is_file($version_file)) {
                        $content = file_get_contents($version_file);
                        $versions[$version] = array(
                            'version' => $version,
                            'date' => date('Y-m-d', filemtime($version_file)),
                            'changes' => trim($content)
                        );
                    }
                }
            }
            
            // Сортируем версии в порядке убывания
            uksort($versions, 'version_compare');
            $versions = array_reverse($versions, true);
        }
        
        return $versions;
    }
    
    /**
     * Получение текущей версии
     * @return string Текущая версия
     */
    private function get_current_version()
    {
        $version_history = $this->get_version_history();
        
        if (!empty($version_history)) {
            reset($version_history);
            return key($version_history);
        }
        
        return ABOUT_VERSION; // Используем константу модуля
    }
    
    /**
     * Получение списка всех модулей с их версиями
     * @return array Массив с информацией о модулях
     */
    private function get_all_modules_with_versions()
    {
        $modules = array();
        
        // Получаем список активных модулей из конфигурации Kohana
        $active_modules = Kohana::modules();
        
        foreach ($active_modules as $module_name => $module_path) {
            // Формируем имя константы для модуля
            $const_name = strtoupper($module_name) . '_VERSION';
            
            // Проверяем наличие init.php файла
            $init_file = $module_path . 'init.php';
            $has_init = file_exists($init_file);
            
            // Получаем версию модуля
            $version = 'Не определена';
            if (defined($const_name)) {
                $version = constant($const_name);
            } else {
                // Пробуем найти альтернативные способы получения версии
                $version = $this->get_module_version_alternative($module_path);
            }
            
            // Получаем дополнительную информацию о модуле
            $modules[$module_name] = array(
                'name' => $module_name,
                'name_display' => $this->format_module_name($module_name),
                'version' => $version,
                'path' => $module_path,
                'has_init' => $has_init,
                'const_defined' => defined($const_name),
                'is_core' => $this->is_core_module($module_name),
                'status' => $this->get_module_status($module_name, $version)
            );
        }
        
        // Сортируем модули: сначала системные, потом пользовательские
        uasort($modules, function($a, $b) {
            if ($a['is_core'] == $b['is_core']) {
                return strcasecmp($a['name'], $b['name']);
            }
            return $a['is_core'] ? -1 : 1;
        });
        
        return $modules;
    }
    
    /**
     * Альтернативные способы получения версии модуля
     * @param string $module_path Путь к модулю
     * @return string Версия модуля
     */
    private function get_module_version_alternative($module_path)
    {
        // Проверяем наличие файла version.php
        $version_file = $module_path . 'version.php';
        if (file_exists($version_file)) {
            $version_data = include $version_file;
            if (is_array($version_data) && isset($version_data['version'])) {
                return $version_data['version'];
            } elseif (is_string($version_data)) {
                return $version_data;
            }
        }
        
        // Проверяем наличие файла config/version.php
        $config_file = $module_path . 'config/version.php';
        if (file_exists($config_file)) {
            $config = include $config_file;
            if (isset($config['version'])) {
                return $config['version'];
            }
        }
        
        // Проверяем наличие файла VERSION
        $version_txt = $module_path . 'VERSION';
        if (file_exists($version_txt)) {
            return trim(file_get_contents($version_txt));
        }
        
        return 'Не определена';
    }
    
    /**
     * Форматирование имени модуля для отображения
     * @param string $module_name Оригинальное имя модуля
     * @return string Отформатированное имя
     */
    private function format_module_name($module_name)
    {
        // Преобразуем camelCase в читаемый текст
        $formatted = preg_replace('/(?<=\\p{L})(?=\\p{Lu})/u', ' ', $module_name);
        $formatted = ucfirst(strtolower($formatted));
        
        // Специальные случаи
        $special_names = array(
            'about' => 'О системе',
            'eventconfig' => 'Конфигурация событий',
            'accesscontrol' => 'Контроль доступа',
            'monitoring' => 'Мониторинг',
            'reports' => 'Отчеты',
            'users' => 'Пользователи и роли'
        );
        
        $key = strtolower($module_name);
        if (isset($special_names[$key])) {
            return $special_names[$key];
        }
        
        return $formatted;
    }
    
    /**
     * Проверка, является ли модуль системным
     * @param string $module_name Имя модуля
     * @return bool
     */
    private function is_core_module($module_name)
    {
        $core_modules = array('auth', 'database', 'orm', 'cache', 'pagination');
        return in_array(strtolower($module_name), $core_modules);
    }
    
    /**
     * Получение статуса модуля
     * @param string $module_name Имя модуля
     * @param string $version Версия модуля
     * @return array Статус модуля
     */
    private function get_module_status($module_name, $version)
    {
        $status = array(
            'class' => 'success',
            'text' => 'Активен'
        );
        
        if ($version === 'Не определена') {
            $status['class'] = 'warning';
            $status['text'] = 'Версия не определена';
        }
        
        if ($this->is_module_outdated($module_name, $version)) {
            $status['class'] = 'danger';
            $status['text'] = 'Требует обновления';
        }
        
        return $status;
    }
    
    /**
     * Проверка устарела ли версия модуля
     * @param string $module_name Имя модуля
     * @param string $version Версия модуля
     * @return bool
     */
    private function is_module_outdated($module_name, $version)
    {
        // Здесь можно добавить логику проверки актуальности версий
        // Например, сравнение с последней известной версией
        static $latest_versions = array(
            'about' => '1.0.0',
            'eventconfig' => '2.1.3'
        );
        
        $key = strtolower($module_name);
        if (isset($latest_versions[$key]) && $version !== 'Не определена') {
            return version_compare($version, $latest_versions[$key], '<');
        }
        
        return false;
    }
}