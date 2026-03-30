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
            'name' => 'Ваше Имя',
            'company' => 'Название компании',
            'email' => 'email@example.com',
            'website' => 'http://example.com'
        );
        
        // Получаем историю версий
        $version_history = $this->get_version_history();
        
        // Получаем текущую версию
        $current_version = $this->get_current_version();
        
        // Формируем контент
        $content = View::factory('about/index')
            ->set('developer', $developer_info)
            ->set('version_history', $version_history)
            ->set('current_version', $current_version);
            
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
        
        return '1.0.0';
    }
}