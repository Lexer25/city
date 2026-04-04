<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Controller_Dbsetting
 * Database management module for Firebird ODBC.
 */
class Controller_Dbsetting extends Controller_Template {
    public $template = 'template';
    
    // Module configuration
    protected $config;
    
    // Available ODBC DSNs from Windows Registry
    protected $odbc_dsns;
    
    // Current selected DSN (from session)
    protected $current_dsn;
    
    // Database error message if connection fails
    protected $db_error = null;
    
    public function before()
    {
        
		try {
            parent::before();
        } catch (Exception $e) {
            // Check if this is a database connection error
            if (strpos($e->getMessage(), 'unavailable database') !== false ||
                strpos($e->getMessage(), 'SQLConnect') !== false ||
                strpos($e->getMessage(), 'Database_Exception') !== false) {
                // This is a database connection error - we can continue since this module
                // is specifically for fixing database connections
                Log::instance()->add(Log::WARNING, 'Database connection failed in dbsetting module: ' . $e->getMessage());
                // Store the error to display to user
                $this->db_error = $e->getMessage();
            } else {
                // Re-throw non-database exceptions
                throw $e;
            }
        }
    
        // Load module configuration
        $this->config = Kohana::$config->load('dbsetting');
        
        // Get ODBC DSNs from Windows Registry
        $this->odbc_dsns = $this->get_odbc_dsns_from_registry();
        
        // Get current DSN from session or read from database.php
        $this->current_dsn = Session::instance()->get('current_dsn', $this->get_current_dsn_from_config());
     
        // Set template variables
        $this->template->title = __('Database Settings');
    }
    
    /**
     * Main page with controls
     */
    public function action_index()
    {
        try {
            $service_status = $this->get_service_status();
        } catch (Exception $e) {
            // If we can't get service status, set it to unknown
            $service_status = 'unknown';
            Log::instance()->add(Log::ERROR, 'Failed to get service status: ' . $e->getMessage());
        }
        
        $database_path = $this->config->get('database_path');
        $database_dir = '';
        $database_filename = '';
        if (!empty($database_path)) {
            // Extract directory and filename
            $database_dir = dirname($database_path);
            $database_filename = basename($database_path);
        }
        
        $content = View::factory('dbsetting/index')
            ->set('odbc_dsns', $this->odbc_dsns)
            ->set('current_dsn', $this->current_dsn)
            ->set('service_status', $service_status)
            ->set('backup_dir', $this->config->get('backup_dir'))
            ->set('database_path', $database_path) // keep for compatibility
            ->set('database_dir', $database_dir)
            ->set('database_filename', $database_filename)
            ->set('db_error', $this->db_error);
        
        $this->template->content = $content;
    }
    
    /**
     * Action to select ODBC DSN
     */
    public function action_select_dsn()
    {
        if ($this->request->method() === 'POST') {
            $selected = $this->request->post('dsn');
            
            if (array_key_exists($selected, $this->odbc_dsns)) {
                $dsn_value = $this->odbc_dsns[$selected];
                
                // Store in session
                Session::instance()->set('current_dsn', $dsn_value);
                
                // Update database configuration file
                if ($this->update_database_config($dsn_value)) {
                    // Set success message
                    Session::instance()->set('flash_message', array(
                        'type' => 'success',
                        'text' => __('Database DSN changed to ') . $selected . ' and saved to config file'
                    ));
                } else {
                    // Set error message if config update failed
                    Session::instance()->set('flash_message', array(
                        'type' => 'error',
                        'text' => __('Failed to update database configuration file. Check logs for details.')
                    ));
                }
            } else {
                Session::instance()->set('flash_message', array(
                    'type' => 'error',
                    'text' => __('Invalid DSN selected.')
                ));
            }
        }
        
        $this->redirect('dbsetting');
    }
    
    /**
     * Save selected database path to module configuration
     */
    public function action_save_database_path()
    {
        // Enable error reporting for debugging
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        Log::instance()->add(Log::DEBUG, 'save_database_path called, method: ' . $this->request->method() . ', is_ajax: ' . ($this->request->is_ajax() ? 'true' : 'false'));
        
        if ($this->request->method() !== 'POST') {
            $this->redirect('dbsetting');
            return;
        }
        
        // Disable template rendering for this action
        $this->auto_render = false;
        
        $database_path = $this->request->post('database_path');
        Log::instance()->add(Log::DEBUG, 'database_path posted: ' . $database_path);
        
        // Validate CSRF token (temporarily disabled for debugging)
        /*
        $posted_token = $this->request->post('csrf_token');
        $expected_token = md5(session_id() . 'dbsetting_save_path');
        if ($posted_token !== $expected_token) {
            $error = __('Ошибка проверки токена безопасности. Пожалуйста, обновите страницу и попробуйте снова.');
            if ($this->request->is_ajax()) {
                $this->response->headers('Content-Type', 'application/json');
                $this->response->body(json_encode(array(
                    'success' => false,
                    'message' => $error
                )));
                return;
            } else {
                Session::instance()->set('flash_message', array(
                    'type' => 'error',
                    'text' => $error
                ));
                $this->redirect('dbsetting');
                return;
            }
        }
        */
        
        if (empty($database_path)) {
            $error = __('Путь к базе данных не может быть пустым.');
            if ($this->request->is_ajax()) {
                $this->response->headers('Content-Type', 'application/json');
                $this->response->body(json_encode(array(
                    'success' => false,
                    'message' => $error
                )));
                return;
            } else {
                Session::instance()->set('flash_message', array(
                    'type' => 'error',
                    'text' => $error
                ));
                $this->redirect('dbsetting');
                return;
            }
        }
        
        try {
            // Decode URL-encoded paths (browsers encode : and \ in POST data)
            $database_path = urldecode($database_path);
            
            $file_exists = file_exists($database_path);
            
            // Update module configuration
            $success = $this->update_module_database_path($database_path);
        } catch (Exception $e) {
            Log::instance()->add(Log::ERROR, 'Exception in save_database_path: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            if ($this->request->is_ajax()) {
                $this->response->headers('Content-Type', 'application/json');
                $this->response->body(json_encode(array(
                    'success' => false,
                    'message' => __('Внутренняя ошибка сервера: ') . $e->getMessage()
                )));
                return;
            } else {
                Session::instance()->set('flash_message', array(
                    'type' => 'error',
                    'text' => __('Внутренняя ошибка сервера: ') . $e->getMessage()
                ));
                $this->redirect('dbsetting');
                return;
            }
        }
        
        if ($this->request->is_ajax()) {
            $this->response->headers('Content-Type', 'application/json');
            if ($success) {
                $this->response->body(json_encode(array(
                    'success' => true,
                    'message' => __('Путь к базе данных сохранен в конфигурации: ') . HTML::chars($database_path),
                    'file_exists' => $file_exists
                )));
            } else {
                $this->response->body(json_encode(array(
                    'success' => false,
                    'message' => __('Не удалось сохранить путь к базе данных в конфигурации. Проверьте логи.')
                )));
            }
            return;
        } else {
            // Non-AJAX request: set flash messages and redirect
            if (!$file_exists) {
                Session::instance()->set('flash_message', array(
                    'type' => 'warning',
                    'text' => __('Файл базы данных не найден: ') . HTML::chars($database_path) .
                             __(' (путь сохранен, но файл отсутствует)')
                ));
            }
            
            if ($success) {
                Session::instance()->set('flash_message', array(
                    'type' => 'success',
                    'text' => __('Путь к базе данных сохранен в конфигурации: ') . HTML::chars($database_path)
                ));
            } else {
                Session::instance()->set('flash_message', array(
                    'type' => 'error',
                    'text' => __('Не удалось сохранить путь к базе данных в конфигурации. Проверьте логи.')
                ));
            }
            
            $this->redirect('dbsetting');
        }
    }
    
    /**
     * Save database directory (folder) only
     */
    public function action_save_database_dir()
    {
        // Enable error reporting for debugging
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        Log::instance()->add(Log::DEBUG, 'save_database_dir called, method: ' . $this->request->method() . ', is_ajax: ' . ($this->request->is_ajax() ? 'true' : 'false'));
        
        if ($this->request->method() !== 'POST') {
            $this->redirect('dbsetting');
            return;
        }
        
        // Disable template rendering for this action
        $this->auto_render = false;
        
        $database_dir = $this->request->post('database_dir');
        Log::instance()->add(Log::DEBUG, 'database_dir posted: ' . $database_dir);
        
        // Validate CSRF token (temporarily disabled for debugging)
        /*
        $posted_token = $this->request->post('csrf_token');
        $expected_token = md5(session_id() . 'dbsetting_save_path');
        if ($posted_token !== $expected_token) {
            $error = __('Ошибка проверки токена безопасности. Пожалуйста, обновите страницу и попробуйте снова.');
            if ($this->request->is_ajax()) {
                $this->response->headers('Content-Type', 'application/json');
                $this->response->body(json_encode(array(
                    'success' => false,
                    'message' => $error
                )));
                return;
            } else {
                Session::instance()->set('flash_message', array(
                    'type' => 'error',
                    'text' => $error
                ));
                $this->redirect('dbsetting');
                return;
            }
        }
        */
        
        if (empty($database_dir)) {
            $error = __('Путь к папке базы данных не может быть пустым.');
            if ($this->request->is_ajax()) {
                $this->response->headers('Content-Type', 'application/json');
                $this->response->body(json_encode(array(
                    'success' => false,
                    'message' => $error
                )));
                return;
            } else {
                Session::instance()->set('flash_message', array(
                    'type' => 'error',
                    'text' => $error
                ));
                $this->redirect('dbsetting');
                return;
            }
        }
        
        // Decode URL-encoded paths (browsers encode : and \ in POST data)
        $database_dir = urldecode($database_dir);
        
        // Get current database filename from config
        $current_path = $this->config->get('database_path');
        $database_filename = '';
        if (!empty($current_path)) {
            $database_filename = basename($current_path);
        } else {
            // If no current path, default to something?
            $database_filename = 'database.fdb';
        }
        
        // Build new full path
        $new_database_path = rtrim($database_dir, '\\/') . DIRECTORY_SEPARATOR . $database_filename;
        
        try {
            $file_exists = file_exists($new_database_path);
            
            // Update module configuration
            $success = $this->update_module_database_path($new_database_path);
        } catch (Exception $e) {
            Log::instance()->add(Log::ERROR, 'Exception in save_database_dir: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            if ($this->request->is_ajax()) {
                $this->response->headers('Content-Type', 'application/json');
                $this->response->body(json_encode(array(
                    'success' => false,
                    'message' => __('Внутренняя ошибка сервера: ') . $e->getMessage()
                )));
                return;
            } else {
                Session::instance()->set('flash_message', array(
                    'type' => 'error',
                    'text' => __('Внутренняя ошибка сервера: ') . $e->getMessage()
                ));
                $this->redirect('dbsetting');
                return;
            }
        }
        
        if ($this->request->is_ajax()) {
            $this->response->headers('Content-Type', 'application/json');
            if ($success) {
                $this->response->body(json_encode(array(
                    'success' => true,
                    'message' => __('Путь к папке базы данных сохранен в конфигурации: ') . HTML::chars($database_dir),
                    'file_exists' => $file_exists,
                    'new_full_path' => $new_database_path
                )));
            } else {
                $this->response->body(json_encode(array(
                    'success' => false,
                    'message' => __('Не удалось сохранить путь к папке базы данных в конфигурации. Проверьте логи.')
                )));
            }
            return;
        } else {
            // Non-AJAX request: set flash messages and redirect
            if (!$file_exists) {
                Session::instance()->set('flash_message', array(
                    'type' => 'warning',
                    'text' => __('Файл базы данных не найден: ') . HTML::chars($new_database_path) .
                             __(' (путь сохранен, но файл отсутствует)')
                ));
            }
            
            if ($success) {
                Session::instance()->set('flash_message', array(
                    'type' => 'success',
                    'text' => __('Путь к папке базы данных сохранен в конфигурации: ') . HTML::chars($database_dir)
                ));
            } else {
                Session::instance()->set('flash_message', array(
                    'type' => 'error',
                    'text' => __('Не удалось сохранить путь к папке базы данных в конфигурации. Проверьте логи.')
                ));
            }
            
            $this->redirect('dbsetting');
        }
    }
    
    /**
     * Save database filename only
     */
    public function action_save_database_filename()
    {
        // Enable error reporting for debugging
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        Log::instance()->add(Log::DEBUG, 'save_database_filename called, method: ' . $this->request->method() . ', is_ajax: ' . ($this->request->is_ajax() ? 'true' : 'false'));
        // Also write to debug.txt for certainty
        file_put_contents(APPPATH . '../debug.txt', date('Y-m-d H:i:s') . ' save_database_filename called, method: ' . $this->request->method() . ', is_ajax: ' . ($this->request->is_ajax() ? 'true' : 'false') . PHP_EOL, FILE_APPEND);
        
        if ($this->request->method() !== 'POST') {
            $this->redirect('dbsetting');
            return;
        }
        
        // Disable template rendering for this action
        $this->auto_render = false;
        
        $database_filename = $this->request->post('database_filename');
        Log::instance()->add(Log::DEBUG, 'database_filename posted: ' . $database_filename);
        
        // Validate CSRF token (temporarily disabled for debugging)
        /*
        $posted_token = $this->request->post('csrf_token');
        $expected_token = md5(session_id() . 'dbsetting_save_path');
        if ($posted_token !== $expected_token) {
            $error = __('Ошибка проверки токена безопасности. Пожалуйста, обновите страницу и попробуйте снова.');
            if ($this->request->is_ajax()) {
                $this->response->headers('Content-Type', 'application/json');
                $this->response->body(json_encode(array(
                    'success' => false,
                    'message' => $error
                )));
                return;
            } else {
                Session::instance()->set('flash_message', array(
                    'type' => 'error',
                    'text' => $error
                ));
                $this->redirect('dbsetting');
                return;
            }
        }
        */
        
        if (empty($database_filename)) {
            $error = __('Имя файла базы данных не может быть пустым.');
            if ($this->request->is_ajax()) {
                $this->response->headers('Content-Type', 'application/json');
                $this->response->body(json_encode(array(
                    'success' => false,
                    'message' => $error
                )));
                return;
            } else {
                Session::instance()->set('flash_message', array(
                    'type' => 'error',
                    'text' => $error
                ));
                $this->redirect('dbsetting');
                return;
            }
        }
        
        // Decode URL-encoded paths (browsers encode : and \ in POST data)
        $database_filename = urldecode($database_filename);
        
        // Get current database directory from config
        $current_path = $this->config->get('database_path');
        $database_dir = '';
        if (!empty($current_path)) {
            $database_dir = dirname($current_path);
        } else {
            // If no current path, default to something?
            $database_dir = 'C:\\';
        }
        
        // Build new full path
        $new_database_path = rtrim($database_dir, '\\/') . DIRECTORY_SEPARATOR . $database_filename;
        
        try {
            $file_exists = file_exists($new_database_path);
            
            // Update module configuration
            $success = $this->update_module_database_path($new_database_path);
        } catch (Exception $e) {
            Log::instance()->add(Log::ERROR, 'Exception in save_database_filename: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            if ($this->request->is_ajax()) {
                $this->response->headers('Content-Type', 'application/json');
                $this->response->body(json_encode(array(
                    'success' => false,
                    'message' => __('Внутренняя ошибка сервера: ') . $e->getMessage()
                )));
                return;
            } else {
                Session::instance()->set('flash_message', array(
                    'type' => 'error',
                    'text' => __('Внутренняя ошибка сервера: ') . $e->getMessage()
                ));
                $this->redirect('dbsetting');
                return;
            }
        }
        
        if ($this->request->is_ajax()) {
            $this->response->headers('Content-Type', 'application/json');
            if ($success) {
                $this->response->body(json_encode(array(
                    'success' => true,
                    'message' => __('Имя файла базы данных сохранено в конфигурации: ') . HTML::chars($database_filename),
                    'file_exists' => $file_exists,
                    'new_full_path' => $new_database_path
                )));
            } else {
                $this->response->body(json_encode(array(
                    'success' => false,
                    'message' => __('Не удалось сохранить имя файла базы данных в конфигурации. Проверьте логи.')
                )));
            }
            return;
        } else {
            // Non-AJAX request: set flash messages and redirect
            if (!$file_exists) {
                Session::instance()->set('flash_message', array(
                    'type' => 'warning',
                    'text' => __('Файл базы данных не найден: ') . HTML::chars($new_database_path) .
                             __(' (имя файла сохранено, но файл отсутствует)')
                ));
            }
            
            if ($success) {
                Session::instance()->set('flash_message', array(
                    'type' => 'success',
                    'text' => __('Имя файла базы данных сохранено в конфигурации: ') . HTML::chars($database_filename)
                ));
            } else {
                Session::instance()->set('flash_message', array(
                    'type' => 'error',
                    'text' => __('Не удалось сохранить имя файла базы данных в конфигурации. Проверьте логи.')
                ));
            }
            
            $this->redirect('dbsetting');
        }
    }
    
    /**
     * Create database backup
     */
    public function action_backup()
    {
        // Get parameters from POST or use defaults from config
        //$database_path = $this->request->post('database_path', $this->config->get('database_path'));
        //$backup_dir = $this->request->post('backup_dir', $this->config->get('backup_dir'));
        $firebird_bin = $this->config->get('firebird_bin');

        $database_path = Arr::get($_POST, 'database_path');
        $backup_dir = Arr::get($_POST, 'backup_dir');
        //$firebird_bin = Arr::get($_POST, 'firebird_bin');

    //echo Debug::vars('128', $database_path);//exit;   
     //echo Debug::vars('131', $firebird_bin);//exit;   
        // Decode URL-encoded paths (browsers encode : and \ in POST data)
        $database_path = urldecode($database_path);
        $backup_dir = urldecode($backup_dir);
        // Log for debugging
        Log::instance()->add(Log::DEBUG, 'Backup attempt - Database path: ' . $database_path);
        Log::instance()->add(Log::DEBUG, 'Backup attempt - Backup dir: ' . $backup_dir);
        
        // Validate database path
        if (empty($database_path)) {
            Session::instance()->set('flash_message', array(
                'type' => 'error',
                'text' => 'Путь к базе данных пуст.'
            ));
            $this->redirect('dbsetting');
            return;
        }
        
        // Check if file exists
        if (!file_exists($database_path)) {
            Session::instance()->set('flash_message', array(
                'type' => 'error',
                'text' => 'Файл базы данных не найден: ' . HTML::chars($database_path) .
                         '. Пожалуйста, проверьте путь и убедитесь, что файл существует.'
            ));
            $this->redirect('dbsetting');
            return;
        }
        
        // Ensure backup directory exists
        if (!is_dir($backup_dir)) {
            if (!mkdir($backup_dir, 0777, true)) {
                Session::instance()->set('flash_message', array(
                    'type' => 'error',
                    'text' => 'Не удалось создать папку для резервных копий: ' . HTML::chars($backup_dir)
                ));
                $this->redirect('dbsetting');
                return;
            }
        }
        
        // Generate filename: database filename + year-month-day-time
        $db_filename = pathinfo($database_path, PATHINFO_FILENAME); // e.g., "CITY"
        $timestamp = date('Y-m-d_His'); // e.g., "2026-04-03_083138"
        $backup_file = $backup_dir . $db_filename . '_' . $timestamp . '.fbk';
  
        $gbak = escapeshellarg($firebird_bin . 'gbak.exe');
        $db = '127.0.0.1:'.escapeshellarg($database_path);
        $backup = escapeshellarg($backup_file);
        
        // Build command
        $command = $gbak . ' -b -v -ig -g -user SYSDBA -PASSWORD temp ' . $db . ' ' . $backup;
   
        Log::instance()->add(Log::DEBUG, 'Executing backup command: ' . $command);
        exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            Session::instance()->set('flash_message', array(
                'type' => 'success',
                'text' => 'Резервная копия успешно создана: ' . $backup_file
            ));
            Log::instance()->add(Log::INFO, 'Backup created: ' . $backup_file);
        } else {
            Session::instance()->set('flash_message', array(
                'type' => 'error',
                'text' => 'Ошибка создания резервной копии. Код ошибки: ' . $return_var . '. Проверьте логи приложения для деталей.'
            ));
            Log::instance()->add(Log::ERROR, 'Backup failed. Command: ' . $command . ', Output: ' . implode("\n", $output));
        }
        
        $this->redirect('dbsetting');
    }
    
    /**
     * Restore database from backup
     */
    public function action_restore()
    {
        if ($this->request->method() === 'POST') {
            $backup_file = $this->request->post('backup_file');
            $restore_path = $this->config->get('database_path');
            $firebird_bin = $this->config->get('firebird_bin');
            
            if (!file_exists($backup_file)) {
                Session::instance()->set('flash_message', array(
                    'type' => 'error',
                    'text' => __('Backup file not found.')
                ));
                $this->redirect('dbsetting');
                return;
            }
            
            // Stop service before restore
            $this->stop_service();
            
            $gbak = escapeshellarg($firebird_bin . 'gbak.exe');
            $backup = escapeshellarg($backup_file);
            $restore = escapeshellarg($restore_path);
            
            $command = $gbak . ' -c -user SYSDBA -masterkey ' . $backup . ' ' . $restore;
            
            exec($command, $output, $return_var);
            
            // Start service after restore
            $this->start_service();
            
            if ($return_var === 0) {
                Session::instance()->set('flash_message', array(
                    'type' => 'success',
                    'text' => __('Database restored successfully from ') . $backup_file
                ));
            } else {
                Session::instance()->set('flash_message', array(
                    'type' => 'error',
                    'text' => __('Restore failed. Error code: ') . $return_var
                ));
            }
        }
        
        $this->redirect('dbsetting');
    }
    
    /**
     * Find the correct Firebird service name
     * @return string|null The service name if found, null otherwise
     */
    protected function find_firebird_service()
    {
        // Try multiple possible service names
        $possible_services = array(
            $this->config->get('service_name', 'FirebirdServerDefault'),
            'FirebirdServerDefaultInstance',
            'FirebirdServerDefault',
            'FirebirdServer',
            'Firebird'
        );
        
        // Remove duplicates while preserving order
        $possible_services = array_unique($possible_services);
        
        foreach ($possible_services as $service) {
            $command = 'sc query ' . escapeshellarg($service) . ' 2>nul';
            exec($command, $output, $return_var);
            
            if ($return_var === 0) {
                // Service exists
                return $service;
            }
        }
        
        return null;
    }
    
    /**
     * Get Firebird service status
     */
    protected function get_service_status()
    {
        $service = $this->find_firebird_service();
     
        if ($service) {
            // Get full service output without filtering
            $command = 'sc query ' . escapeshellarg($service) . ' 2>nul';
            exec($command, $output, $return_var);
            
            if ($return_var === 0 && !empty($output)) {
                // Parse the output for state codes (language-independent)
                // State codes: 4 = RUNNING, 1 = STOPPED
                foreach ($output as $line) {
                    // Check for state code 4 (RUNNING)
                  
                    // check for text patterns as fallback
                    if (strpos($line, 'RUNNING') !== false) {
                        return 'running';
                    }
                    if (strpos($line, 'STOPPED') !== false) {
                        return 'stopped';
                    }
                }
            }
        }
        
        // If we couldn't determine status, try alternative method
        // Check if any Firebird process is running
        $command = 'tasklist /FI "IMAGENAME eq fbserver.exe" /FI "STATUS eq running" 2>nul | find "fbserver.exe"';
        exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            return 'running (detected via process)';
        }
        
        return 'unknown';
    }
    
    /**
     * Stop Firebird service
     */
    public function action_stop_service()
    {
        $service = $this->find_firebird_service();
        
        if (!$service) {
            Session::instance()->set('flash_message', array(
                'type' => 'error',
                'text' => __('Firebird service not found.')
            ));
            $this->redirect('dbsetting');
            return;
        }
        
        $command = 'net stop ' . escapeshellarg($service);
        
        exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            Session::instance()->set('flash_message', array(
                'type' => 'success',
                'text' => __('Firebird service stopped.')
            ));
        } else {
            Session::instance()->set('flash_message', array(
                'type' => 'error',
                'text' => __('Failed to stop service.')
            ));
        }
        
        $this->redirect('dbsetting');
    }
    
    /**
     * Start Firebird service
     */
    public function action_start_service()
    {
        $service = $this->find_firebird_service();
        
        if (!$service) {
            Session::instance()->set('flash_message', array(
                'type' => 'error',
                'text' => __('Firebird service not found.')
            ));
            $this->redirect('dbsetting');
            return;
        }
        
        $command = 'net start ' . escapeshellarg($service);
        
        exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            Session::instance()->set('flash_message', array(
                'type' => 'success',
                'text' => __('Firebird service started.')
            ));
        } else {
            Session::instance()->set('flash_message', array(
                'type' => 'error',
                'text' => __('Failed to start service.')
            ));
        }
        
        $this->redirect('dbsetting');
    }
    
    /**
     * Stop service helper
     */
    protected function stop_service()
    {
        $service = $this->find_firebird_service();
        if ($service) {
            exec('net stop ' . escapeshellarg($service));
        }
    }
    
    /**
     * Start service helper
     */
    protected function start_service()
    {
        $service = $this->find_firebird_service();
        if ($service) {
            exec('net start ' . escapeshellarg($service));
        }
    }
    
    /**
     * Get ODBC DSNs from Windows Registry
     */
    protected function get_odbc_dsns_from_registry()
    {
        $dsns = array();
        
        // Registry paths to check
        $registry_paths = array(
            'HKEY_CURRENT_USER\Software\ODBC\ODBC.INI\ODBC Data Sources',
            'HKEY_LOCAL_MACHINE\SOFTWARE\ODBC\ODBC.INI\ODBC Data Sources'
        );
        
        foreach ($registry_paths as $registry_path) {
            $command = 'reg query "' . $registry_path . '" 2>nul';
            exec($command, $output, $return_var);
            
            if ($return_var === 0 && !empty($output)) {
                foreach ($output as $line) {
                    // Improved regex to handle DSN names with spaces and special characters
                    if (preg_match('/^\s*([^\s].*?)\s+REG_SZ\s+(.*)$/', $line, $matches)) {
                        $dsn_name = trim($matches[1]);
                        // Skip empty lines and default entries
                        if (!empty($dsn_name) && $dsn_name !== '(Default)') {
                            $dsns[$dsn_name] = 'odbc:' . $dsn_name;
                        }
                    }
                }
            }
        }
        
        // If no DSNs found, return default ones
        if (empty($dsns)) {
            $dsns = array(
                'SDUO' => 'odbc:SDUO',
                'Kalibr' => 'odbc:Kalibr',
                'Kalibr_25' => 'odbc:Kalibr_25',
                'HL' => 'odbc:HL',
            );
        }
        
        // Sort DSNs alphabetically for better UX
        ksort($dsns);
        
        return $dsns;
    }
    
    /**
     * Get current DSN from database.php config file
     */
    protected function get_current_dsn_from_config()
    {
        $config_path = $this->config->get('database_config_path', APPPATH . 'config/database.php');
        
        if (file_exists($config_path)) {
            $content = file_get_contents($config_path);
            // Extract dsn value from config
            if (preg_match("/'dsn'\s*=>\s*'([^']*)'/", $content, $matches)) {
                return $matches[1];
            }
        }
        
        return 'odbc:HL'; // Default fallback
    }
    
    /**
     * Update database.php config file
     */
    protected function update_database_config($dsn)
    {
        $config_path = $this->config->get('database_config_path', APPPATH . 'config/database.php');
        
        if (!file_exists($config_path)) {
            Log::instance()->add(Log::ERROR, 'Database config file not found: ' . $config_path);
            return false;
        }
        
        $content = file_get_contents($config_path);
        if ($content === false) {
            Log::instance()->add(Log::ERROR, 'Failed to read database config file: ' . $config_path);
            return false;
        }
        
        // Validate DSN format
        if (!preg_match('/^odbc:[a-zA-Z0-9_\-\.\s]+$/', $dsn)) {
            Log::instance()->add(Log::ERROR, 'Invalid DSN format: ' . $dsn);
            return false;
        }
        
        // Escape single quotes in DSN for replacement
        $escaped_dsn = str_replace("'", "\\'", $dsn);
        
        // Replace dsn line with new value - more robust pattern
        $new_content = preg_replace(
            "/('dsn'\\s*=>\\s*')[^']*(')/",
            "\$1$escaped_dsn\$2",
            $content
        );
        
        // Check if replacement was successful
        if ($new_content === $content) {
            // Try alternative pattern with double quotes
            $new_content = preg_replace(
                '/("dsn"\\s*=>\\s*")[^"]*(")/',
                "\$1$dsn\$2",
                $content
            );
        }
        
        if ($new_content === $content) {
            Log::instance()->add(Log::ERROR, 'Failed to find dsn configuration in config file');
            return false;
        }
        
        // Create backup before writing
        $backup_path = $config_path . '.backup_' . date('Y-m-d_His');
        if (!copy($config_path, $backup_path)) {
            Log::instance()->add(Log::WARNING, 'Failed to create backup of config file');
        }
        
        $result = file_put_contents($config_path, $new_content);
        if ($result === false) {
            Log::instance()->add(Log::ERROR, 'Failed to write database config file: ' . $config_path);
            return false;
        }
        
        return true;
    }

    /**
     * Update module configuration file (dbsetting.php) with new database path
     * @param string $database_path New database file path
     * @return bool Success
     */
    protected function update_module_database_path($database_path)
    {
        // Debug logging
        file_put_contents('debug.txt', date('Y-m-d H:i:s') . ' update_module_database_path called with: ' . $database_path . PHP_EOL, FILE_APPEND);
        
        $module_config_path = MODPATH . 'dbsetting/config/dbsetting.php';
        
        if (!file_exists($module_config_path)) {
            Log::instance()->add(Log::ERROR, 'Module config file not found: ' . $module_config_path);
            return false;
        }
        
        $content = file_get_contents($module_config_path);
        if ($content === false) {
            Log::instance()->add(Log::ERROR, 'Failed to read module config file: ' . $module_config_path);
            return false;
        }
        
        // Validate path contains at least something
        if (empty($database_path)) {
            Log::instance()->add(Log::ERROR, 'Empty database path provided');
            return false;
        }
        
        // Escape single quotes in path for replacement
        $escaped_path = str_replace("'", "\\'", $database_path);
        
        // Replace database_path line with new value - pattern matches 'database_path' =>'...'
        // Uses multiline mode (m) to match start of line, and negative lookahead to skip commented lines
        $new_content = preg_replace(
            "/^(?!\\s*\\/\\/)(\\s*'database_path'\\s*=>\\s*')[^']*(')/m",
            "\$1$escaped_path\$2",
            $content
        );
        
        // Check if replacement was successful
        if ($new_content === $content) {
            // Try alternative pattern with double quotes
            $new_content = preg_replace(
                '/^(?!\\s*\\/\\/)(\\s*"database_path"\\s*=>\\s*")[^"]*(")/m',
                "\$1$database_path\$2",
                $content
            );
        }
        
        if ($new_content === $content) {
            Log::instance()->add(Log::ERROR, 'Failed to find database_path configuration in module config file');
            return false;
        }
        
        // Create backup before writing
        $backup_path = $module_config_path . '.backup_' . date('Y-m-d_His');
        if (!copy($module_config_path, $backup_path)) {
            Log::instance()->add(Log::WARNING, 'Failed to create backup of module config file');
        }
        
        $result = file_put_contents($module_config_path, $new_content);
        if ($result === false) {
            Log::instance()->add(Log::ERROR, 'Failed to write module config file: ' . $module_config_path);
            return false;
        }
        
        // Clear config cache to reload new values
        Kohana::$config->load('dbsetting', true);
        
        return true;
    }

    /**
     * Display configuration editor modal
     */
    public function action_edit_config()
    {
        $config_path = $this->config->get('database_config_path');
        $module_config_path = MODPATH . 'dbsetting/config/dbsetting.php';
        
        // Read current config file
        $config_content = '';
        if (file_exists($module_config_path)) {
            $config_content = file_get_contents($module_config_path);
        }
        
        $content = View::factory('dbsetting/config_editor')
            ->set('config_content', $config_content)
            ->set('config_path', $module_config_path);
        
        $this->template->title = 'Редактирование конфигурации';
        $this->template->content = $content;
    }
    
    /**
     * Save configuration changes
     */
    public function action_save_config()
    {
        if ($this->request->method() !== 'POST') {
            $this->redirect('dbsetting');
            return;
        }
        
        // Validate CSRF token
        $posted_token = $this->request->post('csrf_token');
        $expected_token = md5(session_id() . 'dbsetting_config_edit');
        if ($posted_token !== $expected_token) {
            Session::instance()->set('flash_message', array(
                'type' => 'error',
                'text' => 'Ошибка проверки токена безопасности. Пожалуйста, попробуйте снова.'
            ));
            $this->redirect('dbsetting');
            return;
        }
        
        $config_content = $this->request->post('config_content');
        $module_config_path = MODPATH . 'dbsetting/config/dbsetting.php';
        
        // Validate that we're editing the correct file
        if (empty($config_content) || !file_exists($module_config_path)) {
            Session::instance()->set('flash_message', array(
                'type' => 'error',
                'text' => 'Неверная конфигурация или файл не найден.'
            ));
            $this->redirect('dbsetting');
            return;
        }
        
        // Basic PHP syntax validation - check if it contains valid PHP opening tag
        if (strpos($config_content, '<?php') === false) {
            Session::instance()->set('flash_message', array(
                'type' => 'error',
                'text' => 'Конфигурация должна начинаться с PHP открывающего тега <?php'
            ));
            $this->redirect('dbsetting');
            return;
        }
        
        // Create backup before editing
        $backup_path = $module_config_path . '.backup_' . date('Y-m-d_His');
        if (!copy($module_config_path, $backup_path)) {
            Log::instance()->add(Log::WARNING, 'Failed to create backup of module config file');
        }
        
        // Write new content
        $result = file_put_contents($module_config_path, $config_content);
        
        if ($result === false) {
            Session::instance()->set('flash_message', array(
                'type' => 'error',
                'text' => 'Не удалось сохранить файл конфигурации. Проверьте права доступа.'
            ));
            Log::instance()->add(Log::ERROR, 'Failed to write module config file: ' . $module_config_path);
        } else {
            Session::instance()->set('flash_message', array(
                'type' => 'success',
                'text' => 'Конфигурация успешно сохранена. Создана резервная копия: ' . basename($backup_path)
            ));
            Log::instance()->add(Log::INFO, 'Module configuration updated: ' . $module_config_path);
            
            // Clear config cache to reload new values
            Kohana::$config->load('dbsetting', true);
        }
        
        $this->redirect('dbsetting');
    }
}
