<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_Eventtype extends Model
{
    protected $_table_name = 'EVENTTYPE';
    protected $_primary_key = 'ID_EVENTTYPE';
    
    /**
     * Получить все типы событий
     */
    public function get_all()
    {
        $query = DB::select()
            ->from($this->_table_name)
            ->order_by('ID_EVENTTYPE', 'ASC')
            ->execute(Database::instance('fb'));
 
        return $query->as_array();
    }
    
    /**
     * Получить один тип события по ID
     */
    public function get_one($id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return FALSE;
        }
        $query = DB::select()
            ->from($this->_table_name)
            ->where('ID_EVENTTYPE', '=', $id)
            ->execute(Database::instance('fb'));
        
        $result = $query->current();
        return $result ? $result : FALSE;
    }
    
    /**
     * Обновить тип события
     */
    public function update_eventtype($id, $data)
    {
        $result = DB::update($this->_table_name)
            ->set($data)
            ->where('ID_EVENTTYPE', '=', $id)
            ->execute(Database::instance('fb'));
        
        return $result;
    }
    
    /**
     * Добавить новый тип события
     */
    public function insert_eventtype($data)
    {
        // Если ID_EVENTTYPE не указан, генерируем следующий
        if (!isset($data['ID_EVENTTYPE']) || empty($data['ID_EVENTTYPE'])) {
            $max_id = DB::select(DB::expr('MAX(ID_EVENTTYPE) as max_id'))
                ->from($this->_table_name)
                ->execute(Database::instance('fb'))
                ->get('max_id');
            
            $data['ID_EVENTTYPE'] = $max_id + 1;
        }
        
        // Устанавливаем значения по умолчанию, если не указаны
        if (!isset($data['ID_DB'])) {
            $data['ID_DB'] = 1;
        }
        if (!isset($data['FLAG'])) {
            $data['FLAG'] = 0;
        }
        if (!isset($data['COLOR'])) {
            $data['COLOR'] = 16777215; // белый
        }
        if (!isset($data['ACTIVE'])) {
            $data['ACTIVE'] = 1;
        }
        
        $columns = array_keys($data);
        $values = array_values($data);
        
        $result = DB::insert($this->_table_name, $columns)
            ->values($values)
            ->execute(Database::instance('fb'));
        
        return $result;
    }
    
    /**
     * Получить родительские типы событий (где ID_PARENT IS NULL)
     */
    public function get_parents()
    {
        $query = DB::select()
            ->from($this->_table_name)
            ->where('ID_PARENT', 'IS', NULL)
            ->or_where('ID_PARENT', '=', 0)
            ->order_by('NAME', 'ASC')
            ->execute(Database::instance('fb'));
        
        return $query->as_array();
    }
}
