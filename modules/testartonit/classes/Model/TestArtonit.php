<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Модель для тестирования контроллеров Артонит
 */
class Model_TestArtonit extends Model
{
    /**
     * Проверка доступности устройства
     */
    public function checkDevice($ip, $port, $dev_name)
    {
        try {
            $ts2 = new TS2client($ip, $port, 5);
            $ts2->setSettings(5, 1, false, false);
            
            if (!$ts2->startServer()) {
                return array(
                    'status' => 'error',
                    'message' => 'Не удалось подключиться к серверу'
                );
            }
            
            $loginResponse = $ts2->sendCommandWithResponse('r77 login name="3", password="3"');
            
            if (!$ts2->isCommandSuccessful($loginResponse)) {
                return array(
                    'status' => 'error',
                    'message' => 'Ошибка авторизации'
                );
            }
            
            $response = $ts2->sendCommandWithResponse(
                'r77 exec device="' . $dev_name . '", command="getversion"'
            );
            
            $ts2->close();
            
            if (strpos($response, 'www.artonit.ru') !== false) {
                return array(
                    'status' => 'success',
                    'message' => 'Устройство найдено',
                    'data' => $response
                );
            } else {
                return array(
                    'status' => 'warning',
                    'message' => 'Устройство не является контроллером Артонит'
                );
            }
            
        } catch (Exception $e) {
            return array(
                'status' => 'error',
                'message' => 'Ошибка: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Получение списка устройств из БД (для удобства)
     */
    public function getDeviceList()
    {
        $sql = 'SELECT d.name, d.id_dev, d.id_reader, d.netaddr 
                FROM device d 
                WHERE d.id_reader IS NOT NULL 
                AND d."ACTIVE" = 1 
                ORDER BY d.name';
        
        try {
            $query = DB::query(Database::SELECT, DB::expr($sql))
                ->execute(Database::instance('fb'))
                ->as_array();
            
            $devices = array();
            foreach ($query as $row) {
                $devices[] = array(
                    'id' => $row['ID_DEV'],
                    'name' => iconv('windows-1251', 'UTF-8', $row['NAME']),
                    'id_reader' => $row['ID_READER'],
                    'netaddr' => $row['NETADDR']
                );
            }
            
            return $devices;
            
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, "Ошибка получения списка устройств: " . $e->getMessage());
            return array();
        }
    }
}