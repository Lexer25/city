<?php defined('SYSPATH') or die('No direct script access.');

class Version {
    
    public static $fileVersion = 'version';
    
    /**
     * Получить текущую версию
     */
    public static function get_current()
    {
        $config = Kohana::$config->load(self::$fileVersion);
        return isset($config['current']) ? $config['current'] : '0.0.0';
    }
    
    /**
     * Получить дату версии
     */
    public static function get_date($version = NULL)
    {
        $changelog = self::get_changelog($version);
        return isset($changelog['date']) ? $changelog['date'] : date('Y-m-d');
    }
    
    /**
     * Получить изменения версии
     */
    public static function get_changes($version = NULL)
    {
        $changelog = self::get_changelog($version);
        return isset($changelog['changes']) ? $changelog['changes'] : array();
    }
    
    /**
     * Получить changelog для версии
     */
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
    
    /**
     * Получить все версии
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
     * Проверить, является ли версия свежей (выпущена менее N дней назад)
     */
    public static function is_fresh($days = 3, $version = NULL)
    {
        if ($version === NULL) {
            $version = self::get_current();
        }
        
        $version_date = self::get_date($version);
        
        if (empty($version_date)) {
            return FALSE;
        }
        
        $version_timestamp = strtotime($version_date);
        $current_timestamp = time();
        
        if ($version_timestamp === FALSE) {
            return FALSE;
        }
        
        $diff_days = ($current_timestamp - $version_timestamp) / (60 * 60 * 24);
        
        return $diff_days <= $days;
    }
    
    /**
     * Получить информацию о версии
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
            'is_latest' => ($version == self::get_current()),
            'is_fresh' => self::is_fresh(3, $version)
        );
    }
    
    /**
     * Рендер ссылки для модального окна (Bootstrap 3)
     */
    public static function render_modal_link($options = array())
    {
        $defaults = array(
            'text' => NULL,
            'fresh_days' => 3,
            'btn_class' => 'btn-link',
            'btn_size' => '',
            'show_icon' => TRUE,
            'modal_title' => 'История изменений версий',
            'modal_id' => NULL
        );
        
        $options = array_merge($defaults, $options);
        
        $version = self::get_current();
        $is_fresh = self::is_fresh($options['fresh_days']);
        
        // Формируем текст ссылки
        if ($options['text'] === NULL) {
            $text = 'Версия ' . $version;
        } else {
            $text = $options['text'];
        }
        
        // Добавляем иконку
        if ($options['show_icon']) {
            $text = '<span class="glyphicon glyphicon-info-sign"></span> ' . $text;
        }
        
        // Добавляем бейдж для свежей версии
        if ($is_fresh) {
            $text .= ' <span class="label label-success" title="Свежий релиз">NEW</span>';
        }
        
        // Классы кнопки
        $btn_classes = 'btn ' . $options['btn_class'];
        if (!empty($options['btn_size'])) {
            $btn_classes .= ' ' . $options['btn_size'];
        }
        
        // ID модального окна
        if ($options['modal_id'] === NULL) {
            $modal_id = 'versionModal_' . md5($version);
        } else {
            $modal_id = $options['modal_id'];
        }
        
        $html = '<button type="button" class="' . $btn_classes . '" ';
        $html .= 'data-toggle="modal" data-target="#' . $modal_id . '">';
        $html .= $text;
        $html .= '</button>';
        
        return $html;
    }
    
    /**
     * Рендер модального окна с аккордеоном (Bootstrap 3) - РЕКОМЕНДУЕМЫЙ
     */
    public static function render_modal_simple($options = array())
    {
        $defaults = array(
            'modal_id' => NULL,
            'modal_title' => 'История изменений',
            'modal_size' => 'modal-lg',
            'fresh_days' => 3,
            'limit_versions' => 5
        );
        
        $options = array_merge($defaults, $options);
        
        $version = self::get_current();
        $all_versions = self::get_all_versions();
        
        // Сортируем версии по убыванию
        usort($all_versions, function($a, $b) {
            return version_compare($b, $a);
        });
        
        // Ограничиваем количество версий
        if ($options['limit_versions'] !== NULL && $options['limit_versions'] > 0) {
            $all_versions = array_slice($all_versions, 0, $options['limit_versions']);
        }
        
        // ID модального окна
        if ($options['modal_id'] === NULL) {
            $modal_id = 'versionModal_' . md5($version);
        } else {
            $modal_id = $options['modal_id'];
        }
        
        $html = '<!-- Version Modal -->';
        $html .= '<div class="modal fade" id="' . $modal_id . '" tabindex="-1" role="dialog" aria-hidden="true">';
        $html .= '<div class="modal-dialog ' . $options['modal_size'] . '">';
        $html .= '<div class="modal-content">';
        
        // Заголовок
        $html .= '<div class="modal-header">';
        $html .= '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
        $html .= '<h4 class="modal-title">' . HTML::chars($options['modal_title']) . '</h4>';
        $html .= '</div>';
        
        // Тело модального окна
        $html .= '<div class="modal-body">';
        
        // Текущая версия вверху
        $current_info = self::get_info();
        $html .= '<div class="alert alert-info">';
        $html .= '<h4 style="margin-top: 0;">';
        $html .= '<span class="glyphicon glyphicon-star"></span> Текущая версия: <strong>' . $version . '</strong>';
        if (self::is_fresh($options['fresh_days'])) {
            $html .= ' <span class="label label-success">NEW</span>';
        }
        $html .= '</h4>';
        $html .= '<p><span class="glyphicon glyphicon-calendar"></span> ';
        $html .= '<strong>Дата выпуска:</strong> ' . date('d.m.Y', strtotime($current_info['date'])) . '</p>';
        
        if (!empty($current_info['changes'])) {
            $html .= '<p><strong>Последние изменения:</strong></p>';
            $html .= '<ul class="list-unstyled" style="margin-left: 15px;">';
            foreach ($current_info['changes'] as $change) {
                $html .= '<li><span class="glyphicon glyphicon-ok text-success"></span> ';
                $html .= HTML::chars($change) . '</li>';
            }
            $html .= '</ul>';
        }
        $html .= '</div>';
        
        // Аккордеон с историей версий
        if (count($all_versions) > 1) {
            $html .= '<h4>История версий:</h4>';
            $html .= '<div class="panel-group" id="versionAccordion">';
            
            foreach ($all_versions as $index => $v) {
                if ($v == $version) continue; // Пропускаем текущую, она уже показана
                
                $info = self::get_info($v);
                $is_fresh = self::is_fresh($options['fresh_days'], $v);
                
                $panel_class = 'panel-default';
                if ($is_fresh) {
                    $panel_class = 'panel-success';
                }
                
                $html .= '<div class="panel ' . $panel_class . '">';
                $html .= '<div class="panel-heading">';
                $html .= '<h4 class="panel-title">';
                $html .= '<a class="accordion-toggle" data-toggle="collapse" data-parent="#versionAccordion" ';
                $html .= 'href="#collapse_' . $v . '">';
                $html .= '<span class="glyphicon glyphicon-tag"></span> Версия ' . $v;
                $html .= ' <small class="text-muted">(' . date('d.m.Y', strtotime($info['date'])) . ')</small>';
                if ($is_fresh) {
                    $html .= ' <span class="label label-success pull-right">Свежая</span>';
                }
                $html .= '</a>';
                $html .= '</h4>';
                $html .= '</div>';
                
                $html .= '<div id="collapse_' . $v . '" class="panel-collapse collapse' . ($index === 1 ? ' in' : '') . '">';
                $html .= '<div class="panel-body">';
                
                if (!empty($info['changes'])) {
                    $html .= '<ul class="list-unstyled">';
                    foreach ($info['changes'] as $change) {
                        $html .= '<li><span class="glyphicon glyphicon-chevron-right text-primary"></span> ';
                        $html .= HTML::chars($change) . '</li>';
                    }
                    $html .= '</ul>';
                } else {
                    $html .= '<p class="text-muted"><em>Нет информации об изменениях</em></p>';
                }
                
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';
            }
            
            $html .= '</div>'; // .panel-group
        }
        
        $html .= '</div>'; // .modal-body
        
        // Футер модального окна
        $html .= '<div class="modal-footer">';
        $html .= '<button type="button" class="btn btn-primary" data-dismiss="modal">';
        $html .= '<span class="glyphicon glyphicon-ok"></span> Закрыть';
        $html .= '</button>';
        $html .= '</div>';
        
        $html .= '</div>'; // .modal-content
        $html .= '</div>'; // .modal-dialog
        $html .= '</div>'; // .modal
        
        return $html;
    }
    
    /**
     * Рендер модального окна со списком
     */
    public static function render_modal_list($options = array())
    {
        $defaults = array(
            'modal_id' => NULL,
            'modal_title' => 'История версий',
            'modal_size' => '',
            'fresh_days' => 3,
            'limit_versions' => 10
        );
        
        $options = array_merge($defaults, $options);
        
        $version = self::get_current();
        $all_versions = self::get_all_versions();
        
        // Сортируем версии по убыванию
        usort($all_versions, function($a, $b) {
            return version_compare($b, $a);
        });
        
        // Ограничиваем количество версий
        if ($options['limit_versions'] !== NULL && $options['limit_versions'] > 0) {
            $all_versions = array_slice($all_versions, 0, $options['limit_versions']);
        }
        
        // ID модального окна
        if ($options['modal_id'] === NULL) {
            $modal_id = 'versionModalList_' . md5($version);
        } else {
            $modal_id = $options['modal_id'];
        }
        
        $html = '<!-- Version List Modal -->';
        $html .= '<div class="modal fade" id="' . $modal_id . '" tabindex="-1" role="dialog">';
        $html .= '<div class="modal-dialog ' . $options['modal_size'] . '" role="document">';
        $html .= '<div class="modal-content">';
        
        // Заголовок
        $html .= '<div class="modal-header">';
        $html .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
        $html .= '<h4 class="modal-title">' . HTML::chars($options['modal_title']) . '</h4>';
        $html .= '</div>';
        
        // Тело модального окна
        $html .= '<div class="modal-body" style="max-height: 400px; overflow-y: auto;">';
        
        // Текущая версия
        $current_info = self::get_info();
        $html .= '<div class="well" style="background-color: #f0f8ff; margin-bottom: 20px;">';
        $html .= '<h4 style="margin-top: 0; color: #31708f;">';
        $html .= '<span class="glyphicon glyphicon-flag"></span> <strong>Текущая версия: ' . $version . '</strong>';
        if (self::is_fresh($options['fresh_days'])) {
            $html .= ' <span class="label label-success">NEW</span>';
        }
        $html .= '</h4>';
        $html .= '<p><span class="glyphicon glyphicon-calendar"></span> <strong>Выпущена:</strong> ';
        $html .= date('d.m.Y', strtotime($current_info['date'])) . '</p>';
        
        if (!empty($current_info['changes'])) {
            $html .= '<p><strong>Изменения:</strong></p>';
            $html .= '<ul style="margin-left: 15px; margin-bottom: 0;">';
            foreach ($current_info['changes'] as $change) {
                $html .= '<li><span class="glyphicon glyphicon-ok" style="color: #5cb85c;"></span> ';
                $html .= HTML::chars($change) . '</li>';
            }
            $html .= '</ul>';
        }
        $html .= '</div>';
        
        // Список предыдущих версий
        $html .= '<h4>Предыдущие версии:</h4>';
        
        foreach ($all_versions as $v) {
            if ($v == $version) continue;
            
            $info = self::get_info($v);
            $is_fresh = self::is_fresh($options['fresh_days'], $v);
            
            $html .= '<div class="panel panel-default"' . ($is_fresh ? ' style="border-left: 4px solid #5cb85c;"' : '') . '>';
            $html .= '<div class="panel-heading" style="padding: 10px 15px;">';
            $html .= '<h5 class="panel-title" style="margin: 0;">';
            $html .= '<span class="glyphicon glyphicon-tag"></span> <strong>Версия ' . $v . '</strong>';
            $html .= ' <small class="text-muted">(' . date('d.m.Y', strtotime($info['date'])) . ')</small>';
            if ($is_fresh) {
                $html .= ' <span class="label label-success pull-right">Свежая</span>';
            }
            $html .= '</h5>';
            $html .= '</div>';
            
            if (!empty($info['changes'])) {
                $html .= '<div class="panel-body" style="padding: 10px 15px;">';
                $html .= '<ul style="margin-bottom: 0; padding-left: 15px;">';
                foreach ($info['changes'] as $change) {
                    $html .= '<li style="margin-bottom: 3px;">' . HTML::chars($change) . '</li>';
                }
                $html .= '</ul>';
                $html .= '</div>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>'; // .modal-body
        
        // Футер
        $html .= '<div class="modal-footer">';
        $html .= '<button type="button" class="btn btn-default" data-dismiss="modal">';
        $html .= '<span class="glyphicon glyphicon-remove"></span> Закрыть';
        $html .= '</button>';
        $html .= '</div>';
        
        $html .= '</div>'; // .modal-content
        $html .= '</div>'; // .modal-dialog
        $html .= '</div>'; // .modal
        
        return $html;
    }
    
    /**
     * Полный рендер (ссылка + модальное окно) - ОСНОВНОЙ МЕТОД
     */
    public static function render_full($options = array())
    {
        $defaults = array(
            'link_text' => NULL,
            'link_btn_class' => 'btn-link',
            'link_btn_size' => 'btn-sm',
            'fresh_days' => 3,
            'modal_title' => 'История изменений версий',
            'modal_type' => 'simple', // 'simple' или 'list'
            'show_icon' => TRUE,
            'limit_versions' => 5,
            'modal_id' => NULL
        );
        
        $options = array_merge($defaults, $options);
        
        $version = self::get_current();
        
        // Уникальный ID для модального окна
        if ($options['modal_id'] === NULL) {
            $modal_id = 'versionModal_' . md5($version . time());
        } else {
            $modal_id = $options['modal_id'];
        }
        
        // Ссылка
        $link_options = array(
            'text' => $options['link_text'],
            'fresh_days' => $options['fresh_days'],
            'btn_class' => $options['link_btn_class'],
            'btn_size' => $options['link_btn_size'],
            'show_icon' => $options['show_icon'],
            'modal_title' => $options['modal_title'],
            'modal_id' => $modal_id
        );
        
        $link_html = self::render_modal_link($link_options);
        
        // Модальное окно в зависимости от типа
        $modal_options = array(
            'modal_id' => $modal_id,
            'modal_title' => $options['modal_title'],
            'fresh_days' => $options['fresh_days'],
            'limit_versions' => $options['limit_versions']
        );
        
        if ($options['modal_type'] === 'list') {
            $modal_html = self::render_modal_list($modal_options);
        } else {
            $modal_html = self::render_modal_simple($modal_options);
        }
        
        return $link_html . $modal_html;
    }
    
    /**
     * Рендер бейджа версии
     */
    public static function render_badge($options = array())
    {
        $defaults = array(
            'show_version' => TRUE,
            'show_date' => TRUE,
            'fresh_days' => 3,
            'badge_class' => 'label-default',
            'popover' => TRUE,
            'show_new_badge' => TRUE
        );
        
        $options = array_merge($defaults, $options);
        
        $version = self::get_current();
        $is_fresh = self::is_fresh($options['fresh_days']);
        
        // Определяем класс бейджа
        $badge_class = $options['badge_class'];
        if ($is_fresh) {
            $badge_class = 'label-success';
        }
        
        // Формируем текст
        $text = '';
        if ($options['show_version']) {
            $text .= 'v' . $version;
        }
        
        if ($options['show_date']) {
            $date = date('d.m.y', strtotime(self::get_date()));
            $text .= ($options['show_version'] ? ' ' : '') . '(' . $date . ')';
        }
        
        $html = '<span class="label ' . $badge_class . '"';
        
        // Добавляем popover
        if ($options['popover']) {
            $changes = self::get_changes();
            $popover_content = '<strong>Дата:</strong> ' . date('d.m.Y', strtotime(self::get_date())) . '<br>';
            
            if (!empty($changes)) {
                $popover_content .= '<strong>Изменения:</strong><ul style="padding-left: 15px; margin-bottom: 0;">';
                foreach ($changes as $change) {
                    $popover_content .= '<li>' . HTML::chars($change) . '</li>';
                }
                $popover_content .= '</ul>';
            } else {
                $popover_content .= 'Нет информации об изменениях';
            }
            
            $html .= ' data-toggle="popover" data-html="true" ';
            $html .= 'data-title="Версия ' . $version . '" ';
            $html .= 'data-content="' . HTML::chars($popover_content) . '" ';
            $html .= 'data-placement="bottom"';
        }
        
        $html .= '>';
        $html .= $text;
        
        // Добавляем маленький бейдж "NEW" для свежей версии
        if ($is_fresh && $options['show_new_badge'] && !$options['popover']) {
            $html .= ' <span class="badge" style="background-color: #fff; color: #5cb85c;">NEW</span>';
        }
        
        $html .= '</span>';
        
        // JavaScript для инициализации popover
        if ($options['popover']) {
            $html .= '<script>
            $(document).ready(function(){
                $(\'[data-toggle="popover"]\').popover();
            });
            </script>';
        }
        
        return $html;
    }
    
    /**
     * Рендер для футера (компактный вариант)
     */
    public static function render_footer($options = array())
    {
        $defaults = array(
            'fresh_days' => 3,
            'show_copyright' => TRUE,
            'copyright_text' => '&copy; ' . date('Y') . ' Все права защищены',
            'modal_type' => 'simple'
        );
        
        $options = array_merge($defaults, $options);
        
        $version = self::get_current();
        $is_fresh = self::is_fresh($options['fresh_days']);
        $modal_id = 'footerVersionModal_' . md5($version);
        
        $html = '<div class="footer-version clearfix">';
        
        if ($options['show_copyright']) {
            $html .= '<div class="pull-left text-muted small">';
            $html .= $options['copyright_text'];
            $html .= '</div>';
        }
        
        $html .= '<div class="pull-right">';
        $html .= '<button type="button" class="btn btn-link btn-xs" ';
        $html .= 'data-toggle="modal" data-target="#' . $modal_id . '" ';
        $html .= 'style="padding: 0; color: #777;">';
        
        if ($is_fresh) {
            $html .= '<span class="label label-success" style="margin-right: 3px; font-size: 9px;">NEW</span> ';
        }
        
        $html .= '<span class="glyphicon glyphicon-info-sign" style="font-size: 11px;"></span> ';
        $html .= '<small>v' . $version . '</small>';
        $html .= '</button>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Модальное окно для футера
        $modal_options = array(
            'modal_id' => $modal_id,
            'modal_title' => 'Информация о версии',
            'modal_size' => 'modal-sm',
            'fresh_days' => $options['fresh_days'],
            'limit_versions' => 3
        );
        
        if ($options['modal_type'] === 'list') {
            $html .= self::render_modal_list($modal_options);
        } else {
            $html .= self::render_modal_simple($modal_options);
        }
        
        return $html;
    }
    
    /**
     * Рендер кнопки с подсветкой свежей версии
     */
    public static function render_fresh_button($options = array())
    {
        $defaults = array(
            'text' => NULL,
            'fresh_days' => 3,
            'btn_class' => 'btn-default',
            'btn_size' => 'btn-sm',
            'show_icon' => TRUE,
            'show_new_label' => TRUE,
            'modal_id' => NULL
        );
        
        $options = array_merge($defaults, $options);
        
        $version = self::get_current();
        $is_fresh = self::is_fresh($options['fresh_days']);
        
        // Если версия свежая - меняем класс кнопки
        if ($is_fresh) {
            $options['btn_class'] = 'btn-success';
        }
        
        // Формируем текст
        if ($options['text'] === NULL) {
            $text = 'Версия ' . $version;
        } else {
            $text = $options['text'];
        }
        
        // Добавляем иконку
        if ($options['show_icon']) {
            $text = '<span class="glyphicon glyphicon-certificate"></span> ' . $text;
        }
        
        // Добавляем метку NEW
        if ($is_fresh && $options['show_new_label']) {
            $text .= ' <span class="badge" style="background-color: #fff; color: #5cb85c;">NEW</span>';
        }
        
        // Классы кнопки
        $btn_classes = 'btn ' . $options['btn_class'] . ' ' . $options['btn_size'];
        
        // ID модального окна
        if ($options['modal_id'] === NULL) {
            $modal_id = 'versionModalFresh_' . md5($version);
        } else {
            $modal_id = $options['modal_id'];
        }
        
        $html = '<button type="button" class="' . $btn_classes . '" ';
        $html .= 'data-toggle="modal" data-target="#' . $modal_id . '">';
        $html .= $text;
        $html .= '</button>';
        
        return $html;
    }
    
    /**
     * Получить CSS класс для свежей версии
     */
    public static function get_fresh_css_class($days = 3, $version = NULL, $base_class = '')
    {
        $classes = $base_class;
        if (self::is_fresh($days, $version)) {
            $classes .= ($classes ? ' ' : '') . 'version-fresh';
        }
        return $classes;
    }
    
    /**
     * Простая проверка и вывод уведомления о свежей версии
     */
    public static function show_fresh_alert($days = 3, $message = NULL)
    {
        if (!self::is_fresh($days)) {
            return '';
        }
        
        if ($message === NULL) {
            $message = 'У вас свежая версия приложения! Обновление было выпущено менее ' . $days . ' дней назад.';
        }
        
        return '<div class="alert alert-success alert-dismissible" style="margin: 10px 0;">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <span class="glyphicon glyphicon-ok"></span> ' . HTML::chars($message) . '
                </div>';
    }
}