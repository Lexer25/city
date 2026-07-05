<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_Floorplan_Installm extends Model
{
    /**
     * Прочитать SQL файл
     */
    private function readSqlFile($filename)
    {
        $path = MODPATH . 'floorplan/sql/' . $filename;
        if (!file_exists($path)) {
            return false;
        }
        return file_get_contents($path);
    }

    /**
     * Выполнить SQL скрипт (разбивка по командам)
     */
    private function executeSqlScript($sql)
    {
        if (empty($sql)) {
            return array('success' => false, 'error' => 'Empty SQL');
        }
        
        $db = Database::instance('fb');
        $results = array();
        
        // Разбиваем на отдельные команды
        $commands = $this->splitSqlCommands($sql);
        
        foreach ($commands as $command) {
            $command = trim($command);
            if (empty($command)) continue;
            
            try {
                DB::query(Database::RAW, $command)->execute($db);
                $results[] = array('success' => true, 'command' => substr($command, 0, 100) . '...');
            } catch (Exception $e) {
                $results[] = array('success' => false, 'command' => substr($command, 0, 100) . '...', 'error' => $e->getMessage());
            }
        }
        
        return $results;
    }

    /**
     * Разбить SQL на отдельные команды
     */
    private function splitSqlCommands($sql)
    {
        // Убираем комментарии
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // Разбиваем по GO или ; с учетом SET TERM
        $commands = array();
        $current = '';
        $lines = explode("\n", $sql);
        $inTrigger = false;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) continue;
            
            // Проверяем SET TERM
            if (stripos($line, 'SET TERM') === 0) {
                continue;
            }
            
            // Проверяем начало триггера
            if (stripos($line, 'CREATE TRIGGER') === 0) {
                $inTrigger = true;
                $current = $line . "\n";
                continue;
            }
            
            if ($inTrigger) {
                $current .= $line . "\n";
                if (stripos($line, 'END^') !== false) {
                    $commands[] = $current;
                    $current = '';
                    $inTrigger = false;
                }
                continue;
            }
            
            // Обычная команда
            if (substr($line, -1) === ';') {
                $current .= $line . "\n";
                $commands[] = $current;
                $current = '';
            } else {
                $current .= $line . "\n";
            }
        }
        
        if (!empty($current)) {
            $commands[] = $current;
        }
        
        return $commands;
    }

    /**
     * Проверить существование таблицы
     */
    public function tableExists($tableName)
    {
        try {
            $sql = "SELECT 1 FROM RDB$RELATIONS WHERE RDB$RELATION_NAME = '" . strtoupper($tableName) . "'";
            $result = DB::query(Database::SELECT, $sql)
                ->execute(Database::instance('fb'))
                ->as_array();
            return count($result) > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Проверить существование генератора
     */
    public function generatorExists($generatorName)
    {
        try {
            $sql = "SELECT 1 FROM RDB$GENERATORS WHERE RDB$GENERATOR_NAME = '" . strtoupper($generatorName) . "'";
            $result = DB::query(Database::SELECT, $sql)
                ->execute(Database::instance('fb'))
                ->as_array();
            return count($result) > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Проверить существование триггера
     */
    public function triggerExists($triggerName)
    {
        try {
            $sql = "SELECT 1 FROM RDB$TRIGGERS WHERE RDB$TRIGGER_NAME = '" . strtoupper($triggerName) . "'";
            $result = DB::query(Database::SELECT, $sql)
                ->execute(Database::instance('fb'))
                ->as_array();
            return count($result) > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Проверить наличие всех необходимых таблиц
     */
    public function checkDatabase()
    {
        $result = array(
            'tables' => array(),
            'generators' => array(),
            'triggers' => array(),
            'all_ok' => true,
        );
        
        $tables = array('FLOORPLAN', 'FLOORPLAN_POINT', 'BUILDING');
        foreach ($tables as $table) {
            $exists = $this->tableExists($table);
            $result['tables'][$table] = $exists;
            if (!$exists) {
                $result['all_ok'] = false;
            }
        }
        
        $generators = array('GEN_FLOORPLAN_ID', 'GEN_FLOORPLAN_POINT_ID', 'GEN_BUILDING_ID');
        foreach ($generators as $generator) {
            $exists = $this->generatorExists($generator);
            $result['generators'][$generator] = $exists;
            if (!$exists) {
                $result['all_ok'] = false;
            }
        }
        
        $triggers = array('TRG_FLOORPLAN_BI', 'TRG_FLOORPLAN_POINT_BI', 'TRG_BUILDING_BI');
        foreach ($triggers as $trigger) {
            $exists = $this->triggerExists($trigger);
            $result['triggers'][$trigger] = $exists;
            if (!$exists) {
                $result['all_ok'] = false;
            }
        }
        
        return $result;
    }

    /**
     * Установить базу данных
     */
    public function installDatabase()
    {
        $sql = $this->readSqlFile('install.sql');
        if (!$sql) {
            return array(
                'success' => false,
                'error' => 'Не найден файл install.sql'
            );
        }
        
        $results = $this->executeSqlScript($sql);
        
        $errors = array();
        $successes = array();
        foreach ($results as $result) {
            if ($result['success']) {
                $successes[] = $result['command'];
            } else {
                $errors[] = $result['command'] . ' - ' . $result['error'];
            }
        }
        
        return array(
            'success' => empty($errors),
            'messages' => $successes,
            'errors' => $errors,
            'results' => $results,
        );
    }

    /**
     * Удалить базу данных
     */
    public function uninstallDatabase()
    {
        $sql = $this->readSqlFile('uninstall.sql');
        if (!$sql) {
            return array(
                'success' => false,
                'error' => 'Не найден файл uninstall.sql'
            );
        }
        
        $results = $this->executeSqlScript($sql);
        
        $errors = array();
        $successes = array();
        foreach ($results as $result) {
            if ($result['success']) {
                $successes[] = $result['command'];
            } else {
                $errors[] = $result['command'] . ' - ' . $result['error'];
            }
        }
        
        return array(
            'success' => empty($errors),
            'messages' => $successes,
            'errors' => $errors,
            'results' => $results,
        );
    }

    /**
     * Обновить базу данных с версии 1 до версии 2
     */
    public function upgradeDatabase()
    {
        $sql = $this->readSqlFile('upgrade_v1_to_v2.sql');
        if (!$sql) {
            return array(
                'success' => false,
                'error' => 'Не найден файл upgrade_v1_to_v2.sql'
            );
        }
        
        $results = $this->executeSqlScript($sql);
        
        $errors = array();
        $successes = array();
        foreach ($results as $result) {
            if ($result['success']) {
                $successes[] = $result['command'];
            } else {
                $errors[] = $result['command'] . ' - ' . $result['error'];
            }
        }
        
        return array(
            'success' => empty($errors),
            'messages' => $successes,
            'errors' => $errors,
            'results' => $results,
        );
    }
}