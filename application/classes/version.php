<?php defined('SYSPATH') or die('No direct script access.');

class Version {
    
    public static $fileVersion = 'version';
    
    public static function get_current()
    {
        $config = Kohana::$config->load(self::$fileVersion);
        return isset($config['current']) ? $config['current'] : '0.0.0';
    }
    
    public static function get_date($version = NULL)
    {
        $changelog = self::_get_changelog($version);
        return isset($changelog['date']) ? $changelog['date'] : date('Y-m-d');
    }
    
    public static function get_changes($version = NULL)
    {
        $changelog = self::_get_changelog($version);
        return isset($changelog['changes']) ? $changelog['changes'] : array();
    }
    
    public static function get_changelog($version = NULL)
    {
        $config = Kohana::$config->load(self::$fileVersion);
        
        if ($version === NULL) {
            $version = self::get_current();
        }
        
        if (isset($config['changelog'][$version])) {
            return $config['changelog'][$version];
        }
        
        return FALSE;
    }
    
    protected static function _get_changelog($version = NULL)
    {
        $changelog = self::get_changelog($version);
        
        if ($changelog === FALSE) {
            return array();
        }
        
        return $changelog;
    }
    
    /**
     * Получить все доступные версии
     */
    public static function get_all_versions()
    {
        $config = Kohana::$config->load(self::$fileVersion);
        
        if (isset($config['changelog']) && is_array($config['changelog'])) {
            return array_keys($config['changelog']);
        }
        
        return array();
    }
    
    /**
     * Проверить, является ли версия последней
     */
    public static function is_latest($version)
    {
        return $version === self::get_current();
    }
    
    /**
     * Получить следующую версию
     */
    public static function get_next_version($version = NULL)
    {
        if ($version === NULL) {
            $version = self::get_current();
        }
        
        $all_versions = self::get_all_versions();
        
        // Сортируем версии
        usort($all_versions, 'version_compare');
        
        $current_index = array_search($version, $all_versions);
        
        if ($current_index !== FALSE && isset($all_versions[$current_index + 1])) {
            return $all_versions[$current_index + 1];
        }
        
        return FALSE;
    }
    
    /**
     * Получить предыдущую версию
     */
    public static function get_previous_version($version = NULL)
    {
        if ($version === NULL) {
            $version = self::get_current();
        }
        
        $all_versions = self::get_all_versions();
        
        // Сортируем версии
        usort($all_versions, 'version_compare');
        
        $current_index = array_search($version, $all_versions);
        
        if ($current_index !== FALSE && $current_index > 0) {
            return $all_versions[$current_index - 1];
        }
        
        return FALSE;
    }
    
    /**
     * Получить информацию о версии в виде массива
     */
    public static function get_info($version = NULL)
    {
        if ($version === NULL) {
            $version = self::get_current();
        }
        
        return array(
            'version' => $version,
            'date' => self::get_date($version),
            'changes' => self::get_changes($version),
            'is_latest' => self::is_latest($version),
            'next_version' => self::get_next_version($version),
            'previous_version' => self::get_previous_version($version)
        );
    }
    
    /**
     * Проверить существование версии
     */
    public static function exists($version)
    {
        $config = Kohana::$config->load(self::$fileVersion);
        return isset($config['changelog'][$version]);
    }
    
    /**
     * Отобразить информацию о версии
     */
    public static function display($version = NULL, $show_changes = TRUE)
    {
        if ($version === NULL) {
            $version = self::get_current();
        }
        
        $changelog = self::get_changelog($version);
        
        if ($changelog === FALSE) {
            return '<div class="version-error">Версия ' . HTML::chars($version) . ' не найдена</div>';
        }
        
        $html = '<div class="version-info">';
        $html .= '<h3>Версия ' . HTML::chars($version) . '</h3>';
        $html .= '<p><strong>Дата выпуска:</strong> ' . HTML::chars($changelog['date']) . '</p>';
        
        if ($show_changes && !empty($changelog['changes'])) {
            $html .= '<h4>Изменения:</h4>';
            $html .= '<ul class="version-changes">';
            
            foreach ($changelog['changes'] as $change) {
                $html .= '<li>' . HTML::chars($change) . '</li>';
            }
            
            $html .= '</ul>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Получить последние N версий
     */
    public static function get_recent_versions($limit = 5)
    {
        $all_versions = self::get_all_versions();
        
        // Сортируем версии по убыванию
        usort($all_versions, function($a, $b) {
            return version_compare($b, $a);
        });
        
        return array_slice($all_versions, 0, $limit);
    }
    
    /**
     * Получить HTML для отображения истории версий
     */
    public static function get_history_html($limit = NULL)
    {
        $versions = self::get_all_versions();
        
        // Сортируем по убыванию версии
        usort($versions, function($a, $b) {
            return version_compare($b, $a);
        });
        
        if ($limit !== NULL && $limit > 0) {
            $versions = array_slice($versions, 0, $limit);
        }
        
        $html = '<div class="version-history">';
        $html .= '<h3>История версий</h3>';
        $html .= '<div class="timeline">';
        
        foreach ($versions as $index => $version) {
            $info = self::get_info($version);
            $is_current = ($version == self::get_current());
            
            $html .= '<div class="timeline-item' . ($is_current ? ' current' : '') . '">';
            $html .= '<div class="timeline-marker"></div>';
            $html .= '<div class="timeline-content">';
            $html .= '<h4>Версия ' . HTML::chars($version) . ($is_current ? ' <span class="badge">Текущая</span>' : '') . '</h4>';
            $html .= '<div class="timeline-date">' . HTML::chars($info['date']) . '</div>';
            
            if (!empty($info['changes'])) {
                $html .= '<ul class="timeline-changes">';
                foreach ($info['changes'] as $change) {
                    $html .= '<li>' . HTML::chars($change) . '</li>';
                }
                $html .= '</ul>';
            }
            
            $html .= '</div>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Простая ссылка для открытия модального окна
     */
    public static function render_link($text = NULL, $css_class = 'show-version-modal')
    {
        if ($text === NULL) {
            $text = 'Версия ' . self::get_current();
        }
        
        $html = '<a href="#" class="' . HTML::chars($css_class) . '" ';
        $html .= 'data-version="' . HTML::chars(self::get_current()) . '" ';
        $html .= 'data-date="' . HTML::chars(self::get_date()) . '">';
        $html .= HTML::chars($text);
        $html .= '</a>';
        
        return $html;
    }
}