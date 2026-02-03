<?php defined('SYSPATH') or die('No direct script access.');
// application/classes/Version/Modal.php

class Modal {
    
    /**
     * Генерация модального окна с Bootstrap 3 (если используется)
     */
    public static function bootstrap($title = 'История изменений')
    {
        $version = Version::get_current();
        $changelog = Version::get_changelog();
        $all_versions = Version::get_all_versions();
        
        // Сортируем версии по убыванию
        usort($all_versions, function($a, $b) {
            return version_compare($b, $a);
        });
        
        $modal_id = 'versionModal' . rand(1000, 9999);
        
        $html = '
        <!-- Модальное окно версий -->
        <div class="modal fade" id="' . $modal_id . '" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">' . HTML::chars($title) . '</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="current-version alert alert-info">
                                    <strong>Текущая версия: ' . HTML::chars($version) . '</strong>
                                    <br>Дата выпуска: ' . HTML::chars(Version::get_date()) . '
                                </div>
                                
                                <div class="version-timeline">';
        
        foreach ($all_versions as $v) {
            $info = Version::get_info($v);
            $is_current = ($v == $version);
            
            $html .= '
                                <div class="version-entry' . ($is_current ? ' current' : '') . '">
                                    <div class="version-header">
                                        <h5>
                                            Версия ' . HTML::chars($v) . '
                                            <small>' . HTML::chars($info['date']) . '</small>
                                            ' . ($is_current ? '<span class="label label-primary">Текущая</span>' : '') . '
                                        </h5>
                                    </div>';
            
            if (!empty($info['changes'])) {
                $html .= '<ul class="version-changes">';
                foreach ($info['changes'] as $change) {
                    $html .= '<li>' . HTML::chars($change) . '</li>';
                }
                $html .= '</ul>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Ссылка для открытия -->
        <a href="#" data-toggle="modal" data-target="#' . $modal_id . '" class="version-link">
            <i class="glyphicon glyphicon-info-sign"></i> Версия ' . HTML::chars($version) . '
        </a>
        
        <style>
        .version-entry {
            border-left: 3px solid #ddd;
            padding-left: 15px;
            margin-bottom: 20px;
        }
        .version-entry.current {
            border-left-color: #337ab7;
        }
        .version-changes {
            margin-top: 10px;
            padding-left: 20px;
        }
        .version-changes li {
            margin-bottom: 5px;
        }
        </style>';
        
        return $html;
    }
    
    /**
     * Простое модальное окно на чистом JavaScript
     */
    public static function simple()
    {
        $version = Version::get_current();
        $all_versions = Version::get_all_versions();
        
        usort($all_versions, function($a, $b) {
            return version_compare($b, $a);
        });
        
        $modal_id = 'simpleVersionModal_' . md5($version);
        
        $html = '
        <style>
        .simple-version-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        .simple-version-modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 800px;
            max-height: 70vh;
            overflow-y: auto;
            border-radius: 5px;
        }
        .simple-version-close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .simple-version-close:hover {
            color: black;
        }
        </style>
        
        <!-- Ссылка -->
        <a href="#" onclick="openSimpleVersionModal(\'' . $modal_id . '\')" 
           style="color: #666; text-decoration: none;">
           <small>v' . HTML::chars($version) . '</small>
        </a>
        
        <!-- Модальное окно -->
        <div id="' . $modal_id . '" class="simple-version-modal">
            <div class="simple-version-modal-content">
                <span class="simple-version-close" onclick="closeSimpleVersionModal(\'' . $modal_id . '\')">&times;</span>
                <h3>История изменений</h3>';
        
        foreach ($all_versions as $v) {
            $info = Version::get_info($v);
            $is_current = ($v == $version);
            
            $html .= '
                <div style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                    <h4 style="margin-top: 0;">
                        Версия ' . HTML::chars($v) . '
                        ' . ($is_current ? '<span style="background: #337ab7; color: white; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Текущая</span>' : '') . '
                    </h4>
                    <div style="color: #666; margin-bottom: 8px;">
                        <small>' . HTML::chars($info['date']) . '</small>
                    </div>';
            
            if (!empty($info['changes'])) {
                $html .= '<ul style="margin: 0; padding-left: 20px;">';
                foreach ($info['changes'] as $change) {
                    $html .= '<li style="margin-bottom: 3px;">' . HTML::chars($change) . '</li>';
                }
                $html .= '</ul>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '
            </div>
        </div>
        
        <script>
        function openSimpleVersionModal(modalId) {
            document.getElementById(modalId).style.display = "block";
        }
        function closeSimpleVersionModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }
        
        // Закрыть при клике вне окна
        window.onclick = function(event) {
            var modals = document.getElementsByClassName("simple-version-modal");
            for (var i = 0; i < modals.length; i++) {
                if (event.target == modals[i]) {
                    modals[i].style.display = "none";
                }
            }
        }
        </script>';
        
        return $html;
    }
}