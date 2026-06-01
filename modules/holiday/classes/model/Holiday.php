<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_Holiday extends Model
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
     * Получить список всех праздников
     */
    public function getHolidayList()
    {
        $sql = "SELECT id_holiday, id_db, name, \"DATE\" 
                FROM holiday 
                ORDER BY \"DATE\"";
        
        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();
        
        return $this->convertToUtf8($query);
    }
    
    /**
     * Получить праздник по ID
     */
    public function getHolidayById($id)
    {
        $sql = "SELECT id_holiday, id_db, name, \"DATE\" 
                FROM holiday 
                WHERE id_holiday = " . intval($id);
        
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
     * Добавить праздник
     */
    public function addHoliday($name, $date)
    {
      
	   $nameForDb = iconv('UTF-8', 'Windows-1251//IGNORE', $name);
        $nameForDb = addslashes($nameForDb);
        
        // Преобразуем дату из формата YYYY-MM-DD в формат Firebird
        $dateForDb = $date; // Предполагается что дата приходит в формате YYYY-MM-DD
        
        $sql = "INSERT INTO holiday (id_db, name, \"DATE\") 
                VALUES (1, '{$nameForDb}', '{$dateForDb}')";

        try {
            DB::query(Database::INSERT, $sql)
                ->execute(Database::instance('fb'));
            
            return true;
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error adding holiday: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Обновить праздник
     */
    public function updateHoliday($id, $name, $date)
    {
        $nameForDb = iconv('UTF-8', 'Windows-1251//IGNORE', $name);
        $nameForDb = addslashes($nameForDb);
        
        $dateForDb = $date;
        
        $sql = "UPDATE holiday 
                SET name = '{$nameForDb}', 
                    \"DATE\" = '{$dateForDb}'
                WHERE id_holiday = " . intval($id);
        
        try {
            DB::query(Database::UPDATE, $sql)
                ->execute(Database::instance('fb'));
            
            return true;
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error updating holiday: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Удалить праздник
     */
    public function deleteHoliday($id)
    {
        try {
            $sql = "DELETE FROM holiday WHERE id_holiday = " . intval($id);
            
            DB::query(Database::DELETE, $sql)
                ->execute(Database::instance('fb'));
            
            return true;
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error deleting holiday: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Проверить существование праздника на дату
     */
    public function checkHolidayByDate($date)
    {
        $sql = "SELECT COUNT(*) as cnt 
                FROM holiday 
                WHERE \"DATE\" = '{$date}'";
        
        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();
        
        $result = $this->convertToUtf8($query);
        
        return $result[0]['CNT'] > 0;
    }
}
