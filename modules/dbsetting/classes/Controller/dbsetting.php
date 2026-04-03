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
    
    public function before()
    {
        parent::before();
        
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
}