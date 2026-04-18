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

        $results = $query->as_array();
        // Конвертируем текстовые поля из win1251 в UTF-8
        foreach ($results as &$row) {
            if (isset($row['NAME']) && is_string($row['NAME'])) {
                $row['NAME'] = iconv('windows-1251', 'UTF-8', $row['NAME']);
            }
            if (isset($row['SOUND']) && is_string($row['SOUND'])) {
                $row['SOUND'] = iconv('windows-1251', 'UTF-8', $row['SOUND']);
            }
        }
        return $results;
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
        if ($result) {
            // Конвертируем текстовые поля из win1251 в UTF-8
            if (isset($result['NAME']) && is_string($result['NAME'])) {
                $result['NAME'] = iconv('windows-1251', 'UTF-8', $result['NAME']);
            }
            if (isset($result['SOUND']) && is_string($result['SOUND'])) {
                $result['SOUND'] = iconv('windows-1251', 'UTF-8', $result['SOUND']);
            }
        }
        return $result ? $result : FALSE;
    }
    
    /**
     * Обновить тип события
     */
    public function update_eventtype($id, $data)
    {
        // Конвертируем текстовые поля из UTF-8 в win1251 перед сохранением
        if (isset($data['NAME']) && is_string($data['NAME'])) {
            $data['NAME'] = iconv('UTF-8', 'windows-1251', $data['NAME']);
        }
        if (isset($data['SOUND']) && is_string($data['SOUND'])) {
            $data['SOUND'] = iconv('UTF-8', 'windows-1251', $data['SOUND']);
        }
        
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
        // Конвертируем текстовые поля из UTF-8 в win1251 перед сохранением
        if (isset($data['NAME']) && is_string($data['NAME'])) {
            $data['NAME'] = iconv('UTF-8', 'windows-1251', $data['NAME']);
        }
        if (isset($data['SOUND']) && is_string($data['SOUND'])) {
            $data['SOUND'] = iconv('UTF-8', 'windows-1251', $data['SOUND']);
        }
        
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
        
        $results = $query->as_array();
        // Конвертируем текстовые поля из win1251 в UTF-8
        foreach ($results as &$row) {
            if (isset($row['NAME']) && is_string($row['NAME'])) {
                $row['NAME'] = iconv('windows-1251', 'UTF-8', $row['NAME']);
            }
        }
        return $results;
    }
}
