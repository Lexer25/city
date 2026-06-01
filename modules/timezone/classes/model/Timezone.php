<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_Timezone extends Model
{
    /**
     * Преобразование ключей массива из верхнего регистра в нижний
     * и конвертация кодировки из Windows-1251 в UTF-8
     */
    private function convertToUtf8($data)
    {
        if (is_array($data)) {
            $result = array();
            foreach ($data as $key => $value) {
                $newKey = is_string($key) ? strtolower($key) : $key;
                
                if (is_array($value)) {
                    $result[$newKey] = $this->convertToUtf8($value);
                } elseif (is_string($value)) {
                    $result[$newKey] = iconv('Windows-1251', 'UTF-8//IGNORE', $value);
                } else {
                    $result[$newKey] = $value;
                }
            }
            return $result;
        } elseif (is_string($data)) {
            return iconv('Windows-1251', 'UTF-8//IGNORE', $data);
        }
        return $data;
    }
    
    /**
     * Получить количество временных зон
     */
    public function getTimezoneCount()
    {
        $sql = "SELECT COUNT(*) as cnt FROM timezone WHERE id_db = 1";
        
        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();
        
       
        
        return (int)$query[0]['CNT'];
    }
    
    /**
     * Получить список всех временных зон
     */
    public function getTimezoneList()
    {
        $sql = "SELECT id_timezone, id_db, name, timestart, timeend, flag 
                FROM timezone 
                WHERE id_db = 1
                ORDER BY id_timezone";
        
        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();
        
        return $this->convertToUtf8($query);
    }
    
    /**
     * Получить временную зону по ID
     */
    public function getTimezoneById($id)
    {
        $sql = "SELECT id_timezone, id_db, name, timestart, timeend, flag 
                FROM timezone 
                WHERE id_timezone = " . intval($id) . " AND id_db = 1";
        
        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();
        
        if (count($query) > 0) {
            $result = $this->convertToUtf8($query);
            return $result[0];
        }
        
        return null;
    }
    
    /**
     * Добавить временную зону
     */
    public function addTimezone($name, $timeStart, $timeEnd, $flag)
    {
        $nameForDb = iconv('UTF-8', 'Windows-1251//IGNORE', $name);
        $nameForDb = addslashes($nameForDb);
        
        $sql = "INSERT INTO timezone (id_timezone, id_db, name, timestart, timeend, flag) 
                VALUES (NULL, 1, '{$nameForDb}', '{$timeStart}', '{$timeEnd}', {$flag})";
        
        try {
            DB::query(Database::INSERT, $sql)
                ->execute(Database::instance('fb'));
            
            return true;
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error adding timezone: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Обновить временную зону
     */
    public function updateTimezone($id, $name, $timeStart, $timeEnd, $flag)
    {
        $nameForDb = iconv('UTF-8', 'Windows-1251//IGNORE', $name);
        $nameForDb = addslashes($nameForDb);
        
        $sql = "UPDATE timezone 
                SET name = '{$nameForDb}', 
                    timestart = '{$timeStart}', 
                    timeend = '{$timeEnd}',
                    flag = {$flag}
                WHERE id_timezone = " . intval($id) . " AND id_db = 1";
        
        try {
            DB::query(Database::UPDATE, $sql)
                ->execute(Database::instance('fb'));
            
            return true;
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error updating timezone: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Удалить временную зону
     */
    public function deleteTimezone($id)
    {
        try {
            $sql = "DELETE FROM timezone WHERE id_timezone = " . intval($id) . " AND id_db = 1";
            
            DB::query(Database::DELETE, $sql)
                ->execute(Database::instance('fb'));
            
            return true;
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error deleting timezone: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Расшифровка флагов для отображения
     */
    public function parseFlags($flag)
    {
        $days = array();
        
        if ($flag & 1) $days[] = 'Пн';
        if ($flag & 2) $days[] = 'Вт';
        if ($flag & 4) $days[] = 'Ср';
        if ($flag & 8) $days[] = 'Чт';
        if ($flag & 16) $days[] = 'Пт';
        if ($flag & 32) $days[] = 'Сб';
        if ($flag & 64) $days[] = 'Вс';
        
        $special = array();
        if ($flag & 128) $special[] = 'Праздники';
        if ($flag & 256) $special[] = 'Ночная';
        if ($flag & 512) $special[] = 'Круглосуточная';
        
        return array(
            'days' => implode(', ', $days),
            'special' => implode(', ', $special)
        );
    }
}
