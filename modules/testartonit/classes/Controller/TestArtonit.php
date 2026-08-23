<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Контроллер для отладки работы с контроллерами Артонит
 * Модуль testartonit
 */
class Controller_TestArtonit extends Controller
{
    /**
     * Таймаут выполнения команд
     */
    private $timeout = 30;
    
    /**
     * Результат выполнения
     */
    private $result = array(
        'status' => 'info',
        'message' => '',
        'data' => '',
        'raw_response' => '',
        'execution_time' => 0,
        'command_sent' => '',
        'command_parsed' => ''
    );
    
    /**
     * Главная страница модуля
     */
    public function action_index()
    {
        // Получаем данные из POST запроса
        $post = $this->request->post();
        
        // Если есть команда - выполняем
        if (!empty($post) && isset($post['command'])) {
            $this->executeCommand($post);
        }
        
        // Подготавливаем данные для шаблона
        $view = View::factory('testartonit/index');
        
        // Передаем настройки по умолчанию (исправлено для PHP 5.6)
        $view->defaults = array(
            'dev_name' => isset($post['dev_name']) ? $post['dev_name'] : 'VP3 K3\1',
            'ip_server' => isset($post['ip_server']) ? $post['ip_server'] : '127.0.0.1',
            'port' => isset($post['port']) ? $post['port'] : '1967',
            'command' => isset($post['command']) ? $post['command'] : 'getversion'
        );
        
        // Передаем результат выполнения
        $view->result = $this->result;
        
        // Передаем список команд для выпадающего списка
        $view->command_list = $this->getCommandList();
        
        // Отображаем страницу
        $this->response->body($view);
    }
    
    /**
     * Выполнение команды
     */
    private function executeCommand($post)
    {
        $start_time = microtime(true);
        
        try {
            // Валидация входных данных
            if (empty($post['dev_name'])) {
                throw new Exception('Не указано имя устройства');
            }
            
            if (empty($post['ip_server'])) {
                throw new Exception('Не указан IP адрес сервера');
            }
            
            if (empty($post['port']) || !is_numeric($post['port'])) {
                throw new Exception('Не указан порт сервера');
            }
            
            if (empty($post['command'])) {
                throw new Exception('Не указана команда для выполнения');
            }
            
            // Подготовка данных
            $dev_name = trim($post['dev_name']);
            $ip_server = trim($post['ip_server']);
            $port = (int)$post['port'];
            $command = trim($post['command']);
            
            // Сохраняем отправленную команду
            $this->result['command_sent'] = $command;
            
            // Логируем начало выполнения
            Kohana::$log->add(Log::DEBUG, "TestArtonit: Выполнение команды '{$command}' для устройства '{$dev_name}'");
            
            // Выполняем команду
            $response = $this->sendCommandToDevice($dev_name, $ip_server, $port, $command);
            
            // Обрабатываем ответ
            $this->processResponse($response, $command);
            
            // Успешное выполнение
            $this->result['status'] = 'success';
            $this->result['message'] = 'Команда выполнена успешно';
            
        } catch (Exception $e) {
            // Ошибка выполнения
            $this->result['status'] = 'error';
            $this->result['message'] = 'Ошибка: ' . $e->getMessage();
            
            Kohana::$log->add(Log::ERROR, "TestArtonit: Ошибка выполнения: " . $e->getMessage());
        }
        
        // Время выполнения
        $this->result['execution_time'] = round(microtime(true) - $start_time, 4);
    }
    
    /**
     * Отправка команды на устройство через TS2
     */
    private function sendCommandToDevice($dev_name, $ip_server, $port, $command)
    {
        try {
            // 1. Создаем клиент TS2
            $ts2client = new TS2client($ip_server, $port);
            
            // 2. Запускаем сервер (подключаемся)
            $ts2client->startServer();
            
            // 3. Проверяем соединение
            if (!$ts2client->connReady) {
                throw new Exception('Не удалось подключиться к серверу');
            }
            
            // 4. Отправляем команду логина
            $loginCommand = 'r77 login name="3", password="3"';
            $ts2client->sendMessage($loginCommand);
            
            // 5. Читаем ответ на логин
            $loginResponse = $ts2client->readMessage();
            
            // Проверяем успешность логина
            if (strpos($loginResponse, 'r77 OK') === false) {
                throw new Exception('Ошибка авторизации на сервере: ' . $loginResponse);
            }
            
            // 6. Формируем команду для устройства
            $fullCommand = 'r77 exec device="' . $dev_name . '", command="' . $command . '"';
            
            // Сохраняем полную команду для отладки
            $this->result['command_parsed'] = $fullCommand;
            
            // 7. Отправляем команду
            $ts2client->sendMessage($fullCommand);
            
            // 8. Читаем ответ с таймаутом (делаем несколько попыток)
            $response = '';
            $maxAttempts = 10;
            $attempt = 0;
            
            while ($attempt < $maxAttempts) {
                $response = $ts2client->readMessage();
                
                // Проверяем, что ответ содержит 'r77 OK' или 'ERR'
                if (strpos($response, 'r77 OK') !== false || strpos($response, 'ERR') !== false) {
                    break;
                }
                
                $attempt++;
                usleep(50000); // Ждем 50ms перед следующей попыткой
            }
            
            // 9. Проверяем, что получили ответ
            if (empty($response)) {
                throw new Exception('Не получен ответ от устройства');
            }
            
            // 10. Закрываем соединение
            $ts2client->stopClient();
            
            return $response;
            
        } catch (Exception $e) {
            // Закрываем соединение в случае ошибки
            if (isset($ts2client)) {
                $ts2client->stopClient();
            }
            throw $e;
        }
    }
    
    /**
     * Обработка ответа от устройства
     */
    private function processResponse($response, $originalCommand)
    {
        // Сохраняем сырой ответ
        $this->result['raw_response'] = $response;
        
        // Парсим ответ в зависимости от команды
        $parsed = $this->parseResponse($response, $originalCommand);
        
        if ($parsed !== false) {
            $this->result['data'] = $parsed;
        } else {
            // Если не удалось распарсить, показываем сырой ответ
            $this->result['data'] = $response;
        }
        
        // Проверяем наличие ошибки в ответе
        if (strpos($response, 'ERR') !== false) {
            $this->result['status'] = 'warning';
            $this->result['message'] = 'Устройство вернуло ошибку';
        } else {
            $this->result['status'] = 'success';
            $this->result['message'] = 'Команда выполнена успешно';
        }
    }
    
    /**
     * Парсинг ответа в зависимости от команды
     */
    private function parseResponse($response, $command)
    {
        $result = array();
        
        // Удаляем префикс "r77 OK" если есть
        $clean = $response;
        if (strpos($clean, 'r77 OK') !== false) {
            $clean = trim(str_replace('r77 OK', '', $clean));
        }
        
        // Удаляем лишние пробелы
        $clean = trim($clean);
        
        // Парсим в зависимости от команды
        switch (strtolower($command)) {
            case 'getversion':
                // Ищем версию
                if (preg_match('/([0-9]+\.[0-9]+)/', $clean, $matches)) {
                    $result['version'] = $matches[1];
                }
                
                // Ищем дату сборки
                if (preg_match('/([A-Za-z]+\s+[0-9]+\s+[0-9]{4})/', $clean, $matches)) {
                    $result['build_date'] = $matches[1];
                }
                
                // Ищем URL
                if (preg_match('/(www\.[a-z0-9]+\.[a-z]+)/', $clean, $matches)) {
                    $result['url'] = $matches[1];
                }
                
                // Если ничего не найдено, возвращаем как есть
                if (empty($result)) {
                    $result['raw'] = $clean;
                }
                break;
                
            case 'getdevicetime':
                // Ищем время
                if (preg_match('/(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})/', $clean, $matches)) {
                    $result['device_time'] = $matches[1];
                    $result['server_time'] = date('Y-m-d H:i:s');
                    
                    // Вычисляем разницу
                    $device_timestamp = strtotime($matches[1]);
                    $server_timestamp = time();
                    $diff = $server_timestamp - $device_timestamp;
                    
                    $result['time_diff'] = $diff . ' сек';
                    $result['time_diff_abs'] = abs($diff) . ' сек';
                    
                    if (abs($diff) < 5) {
                        $result['status'] = '✅ Время синхронизировано';
                    } else {
                        $result['status'] = '⚠️ Требуется синхронизация времени';
                    }
                } else {
                    $result['raw'] = $clean;
                }
                break;
                
            case 'getjmp':
            case 'getjumpers':
                // Ищем состояние джамперов
                if (preg_match('/Jmp=(\d+)/', $clean, $matches)) {
                    $jmp = intval($matches[1]);
                    $result['raw'] = $jmp;
                    $result['binary'] = sprintf('%08b', $jmp);
                    $result['bits'] = array(
                        'bit0 (WP)' => ($jmp & 1) ? '1 ✅' : '0 ❌',
                        'bit1 (Test)' => ($jmp & 2) ? '1 ✅' : '0 ❌',
                        'bit2' => ($jmp & 4) ? '1' : '0',
                        'bit3' => ($jmp & 8) ? '1' : '0',
                        'bit4' => ($jmp & 16) ? '1' : '0',
                        'bit5' => ($jmp & 32) ? '1' : '0',
                        'bit6' => ($jmp & 64) ? '1' : '0',
                        'bit7' => ($jmp & 128) ? '1' : '0',
                    );
                    
                    $result['description'] = array();
                    if ($jmp & 1) $result['description'][] = '🔒 WP включен (защита от записи)';
                    if ($jmp & 2) $result['description'][] = '🧪 Test режим включен';
                    if (!($jmp & 1) && !($jmp & 2)) {
                        $result['description'][] = '✅ Нормальный режим работы';
                    }
                } else {
                    $result['raw'] = $clean;
                }
                break;
                
            case 'getkeycount':
                // Ищем количество ключей
                if (preg_match_all('/Count=(\d+)/', $clean, $matches)) {
                    if (isset($matches[1][0])) {
                        $result['door_0'] = (int)$matches[1][0];
                    }
                    if (isset($matches[1][1])) {
                        $result['door_1'] = (int)$matches[1][1];
                    }
                    if (isset($matches[1][2])) {
                        $result['door_2'] = (int)$matches[1][2];
                    }
                    $result['total'] = array_sum($result);
                } else {
                    $result['raw'] = $clean;
                }
                break;
                
            case 'getmac':
                // Ищем MAC адрес
                if (preg_match('/([0-9A-F]{2}[:-]){5}([0-9A-F]{2})/i', $clean, $matches)) {
                    $result['mac'] = strtoupper($matches[0]);
                } else {
                    $result['raw'] = $clean;
                }
                break;
                
            case 'synctime':
                // Синхронизация времени
                if (strpos($clean, 'OK') !== false) {
                    $result['status'] = '✅ Время успешно синхронизировано';
                } else {
                    $result['status'] = '⚠️ Синхронизация не подтверждена';
                }
                $result['raw'] = $clean;
                break;
                
            case 'opendoor':
            case 'closedoor':
                // Открытие/закрытие двери
                if (strpos($clean, 'OK') !== false) {
                    $result['status'] = '✅ Команда выполнена успешно';
                } else {
                    $result['status'] = '⚠️ Не удалось выполнить команду';
                }
                $result['raw'] = $clean;
                break;
                
            default:
                // Для неизвестных команд - сырой ответ
                $result['raw'] = $clean;
                // Пробуем распарсить как key=value
                if (strpos($clean, '=') !== false) {
                    $pairs = explode(',', $clean);
                    foreach ($pairs as $pair) {
                        $pair = trim($pair);
                        if (strpos($pair, '=') !== false) {
                            list($key, $value) = explode('=', $pair, 2);
                            $result[trim($key)] = trim($value);
                        }
                    }
                }
                break;
        }
        
        // Если результат пустой, возвращаем false
        return empty($result) ? false : $result;
    }
    
    /**
     * Получение списка доступных команд
     */
    private function getCommandList()
    {
        return array(
            'getversion' => 'Получить версию',
            'getdevicetime' => 'Получить время устройства',
            'getjmp' => 'Получить состояние джамперов',
            'getmac' => 'Получить MAC адрес',
            'getkeycount' => 'Получить количество ключей',
            'synctime' => 'Синхронизировать время',
            'opendoor' => 'Открыть дверь',
            'closedoor' => 'Закрыть дверь',
            'getconfig' => 'Получить конфигурацию',
            'getstatus' => 'Получить статус',
            'getalarm' => 'Получить состояние тревоги',
            'clearkeys' => 'Очистить ключи'
        );
    }
}


