<?php
// modules/controller_analyzer/classes/ControllerScanner.php
class ControllerScanner {
    
    protected static $_map = null;
    
    public static function get_map($force_refresh = FALSE)
    {
        if (self::$_map === null || $force_refresh) {
            self::$_map = self::build_map();
        }
        
        return self::$_map;
    }
    
    protected static function build_map()
    {
        $map = array();
        $controllers = self::find_all_controllers();
    echo Debug::vars('20', $controllers);exit;    
        foreach ($controllers as $controller_class) {
            $controller_name = str_replace('Controller_', '', $controller_class);
            $map[$controller_name] = self::analyze_controller($controller_class);
        }
        
        return $map;
    }
    
    protected static function find_all_controllers()
    {
        $controllers = array();
        
        // Сканируем application
        if (is_dir(APPPATH.'classes/Controller')) {
            $controllers = array_merge($controllers, 
                self::scan_directory(APPPATH.'classes/Controller')
            );
        }
   echo Debug::vars('39', $controllers);exit;     
        // Сканируем модули
        foreach (Kohana::modules() as $module_path) {
            $controller_path = $module_path.'classes/Controller';
            if (is_dir($controller_path)) {
                $controllers = array_merge($controllers, 
                    self::scan_directory($controller_path)
                );
            }
        }
        
        // Исключаем системные контроллеры
        $exclude = array(
            'Controller',
            'Controller_Template',
            'Controller_REST',
            'Controller_Analyzer'
        );
        
        return array_diff(array_unique($controllers), $exclude);
    }
    
    protected static function scan_directory($directory)
    {
        $controllers = array();
       $directory = "C:/xampp/htdocs/city/application/classes/Controller"; 
	   
        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
      //==========================
	  
	  
/* 	  $count = 0;
foreach ($iterator as $item) {
    $count++;
    echo $count . ": " . $item->getFilename() . "<br>";
}
echo "Всего (включая . и ..): " . $count; */
//echo Debug::vars('80');exit;
      //==========================
	  
	//  echo Debug::vars('83', get_declared_classes() );exit;//список всех зарегистрированных классов
	  echo Debug::vars('83 TS2client', class_exists('TS2client') );//exit;//список всех зарегистрированных классов
	  echo Debug::vars('85 dev', class_exists('Controller_Dev') );exit;//список всех зарегистрированных классов
            foreach ($iterator as $file) {
				
                if ($file->isFile() && $file->getExtension() === 'php') {
            echo Debug::vars('86', $file->getPathname());//exit;   
				$class_name = self::file_to_classname($file->getPathname());                
			echo Debug::vars('87', $class_name);//exit;   
                    if (class_exists($class_name)) {//проверка существования класса
                        $reflection = new ReflectionClass($class_name);
                        echo Debug::vars('89', $reflection);//exit;   
                        if ($reflection->isSubclassOf('Controller') && 
                            !$reflection->isAbstract()) {
                            $controllers[] = $class_name;
                        }
                    } else {
						echo Debug::vars('96 no if', $class_name);exit;
					}
                }
            }
        } catch (Exception $e) {
            // Игнорируем ошибки доступа
        }
     echo Debug::vars('101', $controllers);exit;   
        return $controllers;
    }
    
    protected static function file_to_classname($filepath)
    {
        // Определяем базовый путь
        $base_paths = array(
            APPPATH,
            MODPATH,
            SYSPATH
        );
	$filepath = str_replace('/', '\\', $filepath);
	    foreach ($base_paths as $base) {
            if (strpos($filepath, $base) === 0) {
                $relative_path = substr($filepath, strlen($base));
                $class_path = str_replace(array('/', '.php'), array('_', ''), $relative_path);
                return ucfirst($class_path);
            } 
        }
        return '';
    }
    
    protected static function analyze_controller($class_name)
    {
        try {
            $reflection = new ReflectionClass($class_name);
            
            $controller_info = array(
                'class' => $class_name,
                'file' => $reflection->getFileName(),
                'actions' => array(),
                'parent' => $reflection->getParentClass() ? 
                    $reflection->getParentClass()->getName() : null
            );
            
            // Получаем все публичные методы
            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->class !== $class_name) {
                    continue;
                }
                
                if (strpos($method->name, 'action_') === 0) {
                    $action_name = substr($method->name, 7);
                    $controller_info['actions'][$action_name] = 
                        self::analyze_action($method);
                }
            }
            
            return $controller_info;
            
        } catch (Exception $e) {
            return array(
                'class' => $class_name,
                'error' => $e->getMessage(),
                'actions' => array()
            );
        }
    }
    
    protected static function analyze_action(ReflectionMethod $method)
    {
        $source = file_get_contents($method->getFileName());
        $lines = explode("\n", $source);
        
        $action_info = array(
            'method' => $method->name,
            'view' => null,
            'line' => $method->getStartLine()
        );
        
        $method_code = implode("\n", array_slice($lines, 
            $method->getStartLine() - 1, 
            $method->getEndLine() - $method->getStartLine() + 1
        ));
        
        // Поиск View::factory
        if (preg_match('/View::factory\(["\']([^"\']+)["\']\)/', $method_code, $matches)) {
            $action_info['view'] = $matches[1];
        }
        // Поиск $this->template
        elseif (preg_match('/\$this->template\s*=\s*["\']([^"\']+)["\']/', $method_code, $matches)) {
            $action_info['view'] = $matches[1];
            $action_info['view_type'] = 'template';
        }
        
        return $action_info;
    }
    
   public static function get_statistics()
{
    $map = self::get_map();
 echo Debug::vars('182', $map);exit;   
    $stats = array(
        'total_controllers' => count($map),
        'total_actions' => 0,
        'controllers_with_views' => 0,
        'views_count' => 0,
        'unique_views' => array()
    );
    
    foreach ($map as $controller) {
        $has_views = false;
        
        foreach ($controller['actions'] as $action) {
            $stats['total_actions']++;
            
            if (!empty($action['view'])) {
                $has_views = true;
                $stats['views_count']++;
                
                $view = $action['view'];
                if (!isset($stats['unique_views'][$view])) {
                    $stats['unique_views'][$view] = 0;
                }
                $stats['unique_views'][$view]++;
            }
        }
        
        if ($has_views) {
            $stats['controllers_with_views']++;
        }
    }
    
    // Добавляем счетчик уникальных представлений
    $stats['unique_views_count'] = count($stats['unique_views']);
    
    return $stats;
}
}
