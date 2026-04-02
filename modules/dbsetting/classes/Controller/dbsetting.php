<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Controller_Dbsetting
 * Database management module for Firebird ODBC.
 */
class Controller_Dbsetting extends Controller_Template {
    public $template = 'template';
    
    // Module configuration
    protected $config;
    
    // Available ODBC DSNs
    protected $odbc_dsns;
    
    // Current selected DSN (from session)
    protected $current_dsn;
    
    public function before()
    {
        parent::before();
        
        // Load module configuration
        $this->config = Kohana::$config->load('dbsetting');
        $this->odbc_dsns = $this->config->get('odbc_dsns', array());
        
        // Get current DSN from session
        $this->current_dsn = Session::instance()->get('current_dsn', 'odbc:HL');
        
        // Set template variables
        $this->template->title = __('Database Settings');
    }
    
    /**
     * Main page with controls
     */
    public function action_index()
    {
        $content = View::factory('dbsetting/index')
            ->set('odbc_dsns', $this->odbc_dsns)
            ->set('current_dsn', $this->current_dsn)
            ->set('service_status', $this->get_service_status())
            ->set('backup_dir', $this->config->get('backup_dir'))
            ->set('database_path', $this->config->get('database_path'));
        
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
                // Store in session
                Session::instance()->set('current_dsn', $this->odbc_dsns[$selected]);
                
                // Update database configuration file (optional)
                // $this->update_database_config($this->odbc_dsns[$selected]);
                
                // Set success message
                Session::instance()->set('flash_message', array(
                    'type' => 'success',
                    'text' => __('Database DSN changed to ') . $selected
                ));
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
     * Create database backup
     */
    public function action_backup()
    {
        $backup_dir = $this->config->get('backup_dir');
        $database_path = $this->config->get('database_path');
        $firebird_bin = $this->config->get('firebird_bin');
        
        // Ensure backup directory exists
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0777, true);
        }
        
        $timestamp = date('Ymd_His');
        $backup_file = $backup_dir . 'backup_' . $timestamp . '.fbk';
        
        $gbak = escapeshellarg($firebird_bin . 'gbak.exe');
        $db = escapeshellarg($database_path);
        $backup = escapeshellarg($backup_file);
        
        // Build command
        $command = $gbak . ' -b -user SYSDBA -masterkey ' . $db . ' ' . $backup;
        
        exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            Session::instance()->set('flash_message', array(
                'type' => 'success',
                'text' => __('Backup created successfully: ') . $backup_file
            ));
        } else {
            Session::instance()->set('flash_message', array(
                'type' => 'error',
                'text' => __('Backup failed. Error code: ') . $return_var
            ));
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
     * Get Firebird service status
     */
    protected function get_service_status()
    {
        $service = $this->config->get('service_name', 'FirebirdServerDefault');
        $command = 'sc query ' . escapeshellarg($service) . ' | find "STATE"';
        
        exec($command, $output, $return_var);
        
        if ($return_var === 0 && !empty($output)) {
            $status_line = $output[0];
            if (strpos($status_line, 'RUNNING') !== false) {
                return 'running';
            } elseif (strpos($status_line, 'STOPPED') !== false) {
                return 'stopped';
            }
        }
        
        return 'unknown';
    }
    
    /**
     * Stop Firebird service
     */
    public function action_stop_service()
    {
        $service = $this->config->get('service_name', 'FirebirdServerDefault');
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
        $service = $this->config->get('service_name', 'FirebirdServerDefault');
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
        $service = $this->config->get('service_name', 'FirebirdServerDefault');
        exec('net stop ' . escapeshellarg($service));
    }
    
    /**
     * Start service helper
     */
    protected function start_service()
    {
        $service = $this->config->get('service_name', 'FirebirdServerDefault');
        exec('net start ' . escapeshellarg($service));
    }
    
    /**
     * Update database.php config file (optional)
     */
    protected function update_database_config($dsn)
    {
        $config_path = APPPATH . 'config/database.php';
        if (file_exists($config_path)) {
            $content = file_get_contents($config_path);
            // Simple replacement of dsn line (this is a naive implementation)
            $new_content = preg_replace(
                "/'dsn'\s*=>\s*'odbc:[^']*'/",
                "'dsn' => '$dsn'",
                $content
            );
            file_put_contents($config_path, $new_content);
        }
    }
}