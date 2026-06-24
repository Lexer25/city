<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_tss extends Model {
    
    // ============================================================
    // МОДУЛЬ 1: ТИПЫ ТРАНСПОРТНЫХ СЕРВЕРОВ
    // ============================================================
    
    public function get_list_type()
    {
        $sql = 'SELECT * FROM servertype ORDER BY id';
        
        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();
            
        foreach ($query as &$row) {
            $row['NAME'] = iconv('Windows-1251', 'UTF-8//IGNORE', $row['NAME']);
            $row['DESCRIPTION'] = iconv('Windows-1251', 'UTF-8//IGNORE', $row['DESCRIPTION']);
        }
        return $query;
    }
    
    public function get_type_by_id($id)
    {
        $sql = 'SELECT * FROM servertype WHERE id = ' . (int)$id;
        
        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();
            
        if (!empty($query)) {
            $row = $query[0];
            $row['NAME'] = iconv('Windows-1251', 'UTF-8//IGNORE', $row['NAME']);
            $row['DESCRIPTION'] = iconv('Windows-1251', 'UTF-8//IGNORE', $row['DESCRIPTION']);
            return $row;
        }
        return null;
    }
    
    public function add_type($data)
    {
        $sql = 'INSERT INTO servertype (NAME, DESCRIPTION, IS_ENABLED, DATECREATED) 
                VALUES (
                    \'' . addslashes(Arr::get($data, 'name')) . '\',
                    \'' . addslashes(Arr::get($data, 'description')) . '\',
                    1,
                    CURRENT_TIMESTAMP
                )';
        
        try {
            DB::query(Database::UPDATE, iconv('UTF-8', 'Windows-1251', $sql))
                ->execute(Database::instance('fb'));
            return true;
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error adding type: ' . $e->getMessage());
            return false;
        }
    }
    
    public function update_type($data)
    {
        $sql = 'UPDATE servertype 
                SET NAME = \'' . addslashes(Arr::get($data, 'name')) . '\',
                    DESCRIPTION = \'' . addslashes(Arr::get($data, 'description')) . '\',
                    DATECHANGE = CURRENT_TIMESTAMP
                WHERE id = ' . (int)Arr::get($data, 'id');
        
        try {
            DB::query(Database::UPDATE, iconv('UTF-8', 'Windows-1251', $sql))
                ->execute(Database::instance('fb'));
            return true;
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error updating type: ' . $e->getMessage());
            return false;
        }
    }
    
    public function delete_type($id)
    {
        // Проверяем, используется ли тип
        $check = 'SELECT COUNT(*) as cnt FROM servertypelist WHERE id_type = ' . (int)$id;
        $count = DB::query(Database::SELECT, $check)
            ->execute(Database::instance('fb'))
            ->get('CNT');
            
        if ($count > 0) {
            return false; // Тип используется
        }
        
        $sql = 'DELETE FROM servertype WHERE id = ' . (int)$id;
        
        try {
            DB::query(Database::DELETE, $sql)
                ->execute(Database::instance('fb'));
            return true;
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error deleting type: ' . $e->getMessage());
            return false;
        }
    }
    
    // ============================================================
    // МОДУЛЬ 2: СЕРВЕРЫ (БЕЗ ПРИВЯЗКИ К ТИПУ)
    // ============================================================
    
    public function get_list_servers_only()
    {
        $sql = 'SELECT id_server, name, ip, port, "ACTIVE" FROM server ORDER BY id_server';
        
        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();
            
        foreach ($query as &$row) {
            $row['NAME'] = iconv('Windows-1251', 'UTF-8//IGNORE', $row['NAME']);
        }
        return $query;
    }
    
    public function get_server_by_id($id)
    {
        $sql = 'SELECT id_server, name, ip, port, "ACTIVE" FROM server WHERE id_server = ' . (int)$id;
        
        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();
            
        if (!empty($query)) {
            $row = $query[0];
            $row['NAME'] = iconv('Windows-1251', 'UTF-8//IGNORE', $row['NAME']);
            return $row;
        }
        return null;
    }
    
    public function add_server_only($data)
    {
        $id_server = DB::query(Database::SELECT, 'SELECT id_server FROM SERVER_GETID(1)')
            ->execute(Database::instance('fb'))
            ->get('ID_SERVER');
        
        $sql = 'INSERT INTO SERVER (ID_SERVER, ID_DB, NAME, IP, PORT, "ACTIVE") 
                VALUES (
                    ' . (int)$id_server . ',
                    1,
                    \'' . addslashes(Arr::get($data, 'name')) . '\',
                    ' . (int)Arr::get($data, 'ip') . ',
                    ' . (int)Arr::get($data, 'port') . ',
                    1
                )';
        
        try {
            DB::query(Database::UPDATE, iconv('UTF-8', 'Windows-1251', $sql))
                ->execute(Database::instance('fb'));
            return true;
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error adding server: ' . $e->getMessage());
            return false;
        }
    }
    
    public function update_server($data)
    {
        $sql = 'UPDATE SERVER 
                SET NAME = \'' . addslashes(Arr::get($data, 'name')) . '\',
                    IP = ' . (int)Arr::get($data, 'ip') . ',
                    PORT = ' . (int)Arr::get($data, 'port') . ',
                    "ACTIVE" = ' . (int)Arr::get($data, 'is_active', 1) . '
                WHERE ID_SERVER = ' . (int)Arr::get($data, 'id');
        
        try {
            DB::query(Database::UPDATE, iconv('UTF-8', 'Windows-1251', $sql))
                ->execute(Database::instance('fb'));
            return true;
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error updating server: ' . $e->getMessage());
            return false;
        }
    }
    
    public function delete_server($id)
    {
        // Сначала удаляем все связи
        $sql_links = 'DELETE FROM servertypelist WHERE id_server = ' . (int)$id;
        DB::query(Database::DELETE, $sql_links)
            ->execute(Database::instance('fb'));
        
        $sql = 'DELETE FROM SERVER WHERE id_server = ' . (int)$id;
        
        try {
            DB::query(Database::DELETE, $sql)
                ->execute(Database::instance('fb'));
            return true;
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error deleting server: ' . $e->getMessage());
            return false;
        }
    }
    
    // ============================================================
    // МОДУЛЬ 3: СВЯЗИ СЕРВЕР-ТИП
    // ============================================================
    
    public function get_links_list()
    {
        $sql = 'SELECT 
                    stl.id_server,
                    stl.id_type,
                    s.name as server_name,
                    st.name as type_name
                FROM servertypelist stl
                JOIN server s ON s.id_server = stl.id_server
                JOIN servertype st ON st.id = stl.id_type
                ORDER BY s.name, st.name';
        
        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();
            
        foreach ($query as &$row) {
            $row['SERVER_NAME'] = iconv('Windows-1251', 'UTF-8//IGNORE', $row['SERVER_NAME']);
            $row['TYPE_NAME'] = iconv('Windows-1251', 'UTF-8//IGNORE', $row['TYPE_NAME']);
        }
        return $query;
    }
    
    public function add_link($data)
    {
        $sql = 'INSERT INTO servertypelist (id_server, id_type) 
                VALUES (
                    ' . (int)Arr::get($data, 'id_server') . ',
                    ' . (int)Arr::get($data, 'id_type') . '
                )';
        
        try {
            DB::query(Database::INSERT, $sql)
                ->execute(Database::instance('fb'));
            return true;
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error adding link: ' . $e->getMessage());
            return false;
        }
    }
    
    public function delete_link($data)
    {
        $sql = 'DELETE FROM servertypelist 
                WHERE id_server = ' . (int)Arr::get($data, 'id_server') . '
                AND id_type = ' . (int)Arr::get($data, 'id_type');
        
        try {
            DB::query(Database::DELETE, $sql)
                ->execute(Database::instance('fb'));
            return true;
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error deleting link: ' . $e->getMessage());
            return false;
        }
    }
    
    // ============================================================
    // УСТАРЕВШИЕ МЕТОДЫ (для обратной совместимости)
    // ============================================================
    
    /**
     * @deprecated Используйте get_list_servers_only()
     */
    public function get_list()
    {
        return $this->get_list_servers_only();
    }
    
    /**
     * @deprecated Используйте add_server_only()
     */
    public function add_server($data)
    {
        return $this->add_server_only($data);
    }
}