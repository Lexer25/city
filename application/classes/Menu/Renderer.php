<?php // application/classes/Menu/Renderer.php
	defined('SYSPATH') or die('No direct script access.');

class Menu_Renderer {
    
    /**
     * Получить все пункты меню, отсортированные по order
     * @return array
     */
    public static function get_items()
    {
        $menu_items = Kohana::$config->load('menu')->as_array();
        
        // Сортируем по полю order
        uasort($menu_items, function($a, $b) {
            $order_a = isset($a['order']) ? $a['order'] : 999;
            $order_b = isset($b['order']) ? $b['order'] : 999;
            return $order_a - $order_b;
        });
        
        return $menu_items;
    }
    
    /**
     * Получить URL пункта меню
     * @param array $item
     * @return string
     */
    private static function get_url($item)
    {
        if (isset($item['route'])) {
            $params = isset($item['params']) ? $item['params'] : array();
            return Route::get($item['route'])->uri($params);
        } elseif (isset($item['url'])) {
            return $item['url'];
        }
        return '#';
    }
    
    /**
     * Проверить, активен ли пункт меню
     * @param array $item
     * @return bool
     */
    private static function is_active($item)
    {
        $current_uri = Request::current()->uri();
        $item_url = self::get_url($item);
        
        // Простое сравнение URL
        if ($current_uri === $item_url) {
            return true;
        }
        
        // Проверка на вложенные URL (например, /blog/post/1 для меню /blog)
        if (strpos($current_uri, $item_url) === 0 && $item_url !== '/') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Рекурсивно отрендерить пункты меню
     * @param array $items
     * @param int $depth
     * @return string
     */
    private static function render_items($items, $depth = 0)
    {
        $html = '';
        $current_uri = Request::current()->uri();
        
        foreach ($items as $key => $item) {
            $is_active = self::is_active($item);
            $has_children = isset($item['children']) && !empty($item['children']);
            
            // Формируем классы для пункта меню
            $li_classes = array();
            if ($is_active) {
                $li_classes[] = 'active';
            }
            if ($has_children) {
                $li_classes[] = 'dropdown';
            }
            $li_class_attr = !empty($li_classes) ? ' class="' . implode(' ', $li_classes) . '"' : '';
            
            $html .= '<li' . $li_class_attr . '>';
            
            // Ссылка
            $url = self::get_url($item);
            $icon_html = isset($item['icon']) ? '<i class="' . $item['icon'] . '"></i> ' : '';
            
            $a_classes = array();
            if ($has_children) {
                $a_classes[] = 'dropdown-toggle';
            }
            $a_class_attr = !empty($a_classes) ? ' class="' . implode(' ', $a_classes) . '"' : '';
            
            $data_attr = $has_children ? ' data-toggle="dropdown"' : '';
            
            $html .= '<a href="' . $url . '"' . $a_class_attr . $data_attr . '>';
            $html .= $icon_html . HTML::chars($item['title']);
            if ($has_children) {
                $html .= ' <b class="caret"></b>';
            }
            $html .= '</a>';
            
            // Вложенные пункты
            if ($has_children) {
                $child_items = isset($item['children']) ? $item['children'] : array();
                
                // Сортируем дочерние пункты
                uasort($child_items, function($a, $b) {
                    $order_a = isset($a['order']) ? $a['order'] : 999;
                    $order_b = isset($b['order']) ? $b['order'] : 999;
                    return $order_a - $order_b;
                });
                
                $html .= '<ul class="dropdown-menu">';
                foreach ($child_items as $child_key => $child) {
                    $child_url = self::get_url($child);
                    $child_icon = isset($child['icon']) ? '<i class="' . $child['icon'] . '"></i> ' : '';
                    $child_active = self::is_active($child);
                    $child_class = $child_active ? ' class="active"' : '';
                    
                    $html .= '<li' . $child_class . '>';
                    $html .= '<a href="' . $child_url . '">';
                    $html .= $child_icon . HTML::chars($child['title']);
                    $html .= '</a>';
                    $html .= '</li>';
                }
                $html .= '</ul>';
            }
            
            $html .= '</li>';
        }
        
        return $html;
    }
    
    /**
     * Отрендерить меню как HTML
     * @param string $ul_class Класс для ul
     * @return string
     */
    public static function render($ul_class = 'nav')
    {
        $items = self::get_items();
        
        if (empty($items)) {
            return '';
        }
        
        $class_attr = $ul_class ? ' class="' . $ul_class . '"' : '';
        $html = '<ul' . $class_attr . '>';
        $html .= self::render_items($items);
        $html .= '</ul>';
        
        return $html;
    }
}
