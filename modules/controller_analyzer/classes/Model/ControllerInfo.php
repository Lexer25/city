<?php
// modules/controller_analyzer/classes/Model/ControllerInfo.php
class Model_ControllerInfo {
    
    protected $_data = array();
    
    public static function factory($controller_name)
    {
        $info = Helper_ControllerScan::get_controller_details($controller_name);
        
        $model = new Model_ControllerInfo();
        $model->_data = $info;
        
        return $model;
    }
    
    public function get_class()
    {
        return isset($this->_data['class']) ? $this->_data['class'] : null;
    }
    
    public function get_actions()
    {
        return isset($this->_data['actions']) ? $this->_data['actions'] : array();
    }
    
    public function get_methods()
    {
        return isset($this->_data['methods']) ? $this->_data['methods'] : array();
    }
    
    public function get_file()
    {
        return isset($this->_data['file']) ? $this->_data['file'] : null;
    }
    
    public function has_action($action_name)
    {
        return isset($this->_data['actions'][$action_name]);
    }
    
    public function get_action_info($action_name)
    {
        return isset($this->_data['actions'][$action_name]) 
            ? $this->_data['actions'][$action_name] 
            : null;
    }
    
    public function get_views()
    {
        $views = array();
        
        foreach ($this->get_actions() as $action => $info) {
            if (!empty($info['view'])) {
                $views[$action] = $info['view'];
            }
        }
        
        return $views;
    }
}