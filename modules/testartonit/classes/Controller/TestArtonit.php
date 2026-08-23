<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Контроллер для отладки работы с контроллерами Артонит
 * Поддерживает два способа: TS2 и HTTP (прямое чтение веб-форм)
 * Метод выполнения определяется автоматически по выбранной команде
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
        'command_parsed' => '',
        'method' => 'ts2',
        'controller_ip' => ''
    );
    
    /**
     * Карта соответствия команд и методов
     */
    private $commandMethodMap = array(
        // TS2 команды
        'getversion' => 'ts2',
        'getdevicetime' => 'ts2',
        'getjmp' => 'ts2',
        'getmac' => 'ts2',
        'getkeycount' => 'ts2',
        'synctime' => 'ts2',
        'opendoor' => 'ts2',
        'closedoor' => 'ts2',
        // HTTP команды
        'getdevicemode' => 'http',
        'getdoormode' => 'http',
        'getinputports' => 'http',
        'getscudmode' => 'http',
        'getallinfo' => 'http'
    );
    
    /**
     * Главная страница модуля
     */
    public function action_index()
    {
        $post = $this->request->post();
        
        // Если есть команда - выполняем
        if (!empty($post) && isset($post['command'])) {
            $this->executeCommand($post);
        }
        
        $view = View::factory('testartonit/index');
        
        // Настройки по умолчанию
        $view->defaults = array(
            'dev_name' => isset($post['dev_name']) ? $post['dev_name'] : 'VP3 K3\1',
            'ip_server' => isset($post['ip_server']) ? $post['ip_server'] : '127.0.0.1',
            'port' => isset($post['port']) ? $post['port'] : '1967',
            'command' => isset($post['command']) ? $post['command'] : 'getversion'
        );
        
        $view->result = $this->result;
        $view->command_list = $this->getCommandList();
        
        $this->response->body($view);
    }
    
    /**
     * Выполнение команды
     */
    private function executeCommand($post)
    {
        $start_time = microtime(true);
        
        try {
            if (empty($post['command'])) {
                throw new Exception('Не указана команда для выполнения');
            }
            
            if (empty($post['dev_name'])) {
                throw new Exception('Не указано имя контроллера');
            }
            
            if (empty($post['ip_server'])) {
                throw new Exception('Не указан IP адрес сервера');
            }
            
            if (empty($post['port']) || !is_numeric($post['port'])) {
                throw new Exception('Не указан порт сервера');
            }
            
            $command = trim($post['command']);
            $dev_name = trim($post['dev_name']);
            $ip_server = trim($post['ip_server']);
            $port = (int)$post['port'];
            
            // Определяем метод выполнения по команде
            $method = $this->getMethodForCommand($command);
            
            $this->result['command_sent'] = $command;
            $this->result['method'] = $method;
            
            Kohana::$log->add(Log::DEBUG, "TestArtonit: Выполнение команды '{$command}' для '{$dev_name}' через {$method}");
            
            if ($method === 'http') {
                // HTTP метод: сначала получаем IP через DeviceInfo
                $controllerIp = $this->getControllerIpViaTs2($dev_name, $ip_server, $port);
                
                if (empty($controllerIp)) {
                    throw new Exception('Не удалось получить IP адрес контроллера через DeviceInfo');
                }
                
                $this->result['controller_ip'] = $controllerIp;
                Kohana::$log->add(Log::DEBUG, "TestArtonit: Получен IP контроллера: {$controllerIp}");
                
                // Выполняем HTTP команду
                $response = $this->executeViaHttp($controllerIp, $command);
                
            } else {
                // TS2 метод
                $response = $this->executeViaTs2($dev_name, $ip_server, $port, $command);
            }
            
            $this->processResponse($response, $command, $method);
            $this->result['status'] = 'success';
            $this->result['message'] = 'Команда выполнена успешно';
            
        } catch (Exception $e) {
            $this->result['status'] = 'error';
            $this->result['message'] = 'Ошибка: ' . $e->getMessage();
            Kohana::$log->add(Log::ERROR, "TestArtonit: Ошибка выполнения: " . $e->getMessage());
        }
        
        $this->result['execution_time'] = round(microtime(true) - $start_time, 4);
    }
    
    /**
     * Определение метода выполнения по команде
     */
    private function getMethodForCommand($command)
    {
        $command = strtolower(trim($command));
        
        // Если команда в карте - возвращаем соответствующий метод
        if (isset($this->commandMethodMap[$command])) {
            return $this->commandMethodMap[$command];
        }
        
        // Для неизвестных команд - по умолчанию TS2
        return 'ts2';
    }
    
    /**
     * Получение IP адреса контроллера через TS2 команду DeviceInfo
     */
    private function getControllerIpViaTs2($dev_name, $ip_server, $port)
    {
        try {
            $ts2client = new TS2client($ip_server, $port);
            $ts2client->startServer();
            
            if (!$ts2client->connReady) {
                throw new Exception('Не удалось подключиться к серверу TS2');
            }
            
            // Отправляем команду логина
            $ts2client->sendMessage('r77 login name="3", password="3"');
            $loginResponse = $ts2client->readMessage();
            
            if (strpos($loginResponse, 'r77 OK') === false) {
                throw new Exception('Ошибка авторизации на TS2: ' . $loginResponse);
            }
            
            // Формируем команду DeviceInfo
            $commandId = 't45';
            $fullCommand = $commandId . ' DeviceInfo name="' . $dev_name . '"';
            $this->result['command_parsed'] = $fullCommand;
            
            Kohana::$log->add(Log::DEBUG, "TestArtonit: Отправка DeviceInfo: " . $fullCommand);
            
            $ts2client->sendMessage($fullCommand);
            
            // Читаем ответ
            $response = '';
            $maxAttempts = 15;
            $attempt = 0;
            
            while ($attempt < $maxAttempts) {
                $response = $ts2client->readMessage();
                
                if (strpos($response, $commandId) === 0) {
                    Kohana::$log->add(Log::DEBUG, "TestArtonit: Получен ответ DeviceInfo: " . $response);
                    break;
                }
                
                $attempt++;
                usleep(50000);
            }
            
            $ts2client->stopClient();
            
            if (empty($response) || strpos($response, $commandId) !== 0) {
                throw new Exception('Не получен ответ на DeviceInfo команду');
            }
            
            $controllerIp = $this->parseDeviceInfoResponse($response);
            
            if (empty($controllerIp)) {
                throw new Exception('Не удалось извлечь IP адрес из ответа DeviceInfo');
            }
            
            return $controllerIp;
            
        } catch (Exception $e) {
            if (isset($ts2client)) {
                $ts2client->stopClient();
            }
            throw $e;
        }
    }
    
    /**
     * Парсинг ответа DeviceInfo
     */
    private function parseDeviceInfoResponse($response)
    {
        $result = '';
        
        $clean = $response;
        if (strpos($clean, 'OK') !== false) {
            $clean = trim(str_replace('OK', '', $clean));
        }
        
        if (preg_match('/ConnectionString\s*=\s*"([^"]+)"/i', $clean, $matches)) {
            $result = trim($matches[1]);
            Kohana::$log->add(Log::DEBUG, "TestArtonit: Извлечен IP из ConnectionString: " . $result);
            return $result;
        }
        
        if (preg_match('/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', $clean, $matches)) {
            $result = trim($matches[1]);
            Kohana::$log->add(Log::DEBUG, "TestArtonit: Извлечен IP по маске: " . $result);
            return $result;
        }
        
        Kohana::$log->add(Log::ERROR, "TestArtonit: Не удалось извлечь IP из ответа: " . $response);
        return '';
    }
    
    /**
     * Выполнение команды через TS2
     */
    private function executeViaTs2($dev_name, $ip_server, $port, $command)
    {
        try {
            $ts2client = new TS2client($ip_server, $port);
            $ts2client->startServer();
            
            if (!$ts2client->connReady) {
                throw new Exception('Не удалось подключиться к серверу');
            }
            
            $ts2client->sendMessage('r77 login name="3", password="3"');
            $loginResponse = $ts2client->readMessage();
            
            if (strpos($loginResponse, 'r77 OK') === false) {
                throw new Exception('Ошибка авторизации: ' . $loginResponse);
            }
            
            $fullCommand = 'r77 exec device="' . $dev_name . '", command="' . $command . '"';
            $this->result['command_parsed'] = $fullCommand;
            
            $ts2client->sendMessage($fullCommand);
            
            $response = '';
            $maxAttempts = 10;
            $attempt = 0;
            
            while ($attempt < $maxAttempts) {
                $response = $ts2client->readMessage();
                if (strpos($response, 'r77 OK') !== false || strpos($response, 'ERR') !== false) {
                    break;
                }
                $attempt++;
                usleep(50000);
            }
            
            if (empty($response)) {
                throw new Exception('Не получен ответ от устройства');
            }
            
            $ts2client->stopClient();
            return $response;
            
        } catch (Exception $e) {
            if (isset($ts2client)) {
                $ts2client->stopClient();
            }
            throw $e;
        }
    }
    
    /**
     * Выполнение команды через HTTP
     */
    private function executeViaHttp($ip, $command)
    {
        try {
            $httpClient = new artonitHTTP($ip);
            
            if (!$httpClient->isOnline) {
                throw new Exception('Контроллер не доступен по HTTP (IP: ' . $ip . ')');
            }
            
            $data = array();
            $response = '';
            
            switch (strtolower($command)) {
                case 'getversion':
                    $httpClient->getSoftVersion();
                    $data['version'] = $httpClient->softVersion;
                    $response = $httpClient->softVersion;
                    break;
                    
                case 'getdevicemode':
                    $httpClient->getDeviceMode();
                    $data = array(
                        'isWp' => $httpClient->isWp,
                        'isTest' => $httpClient->isTest,
                        'mac' => $httpClient->mac_address
                    );
                    $response = 'WP: ' . ($httpClient->isWp ? 'Включен' : 'Выключен') . "\n";
                    $response .= 'Test: ' . ($httpClient->isTest ? 'Включен' : 'Выключен') . "\n";
                    $response .= 'MAC: ' . $httpClient->mac_address;
                    break;
                    
                case 'getdoormode':
                    $httpClient->getDoorMode();
                    $data = array(
                        'door_a' => $httpClient->doorMode[0],
                        'door_b' => $httpClient->doorMode[1]
                    );
                    $response = 'Дверь A: ' . $httpClient->doorMode[0] . "\n";
                    $response .= 'Дверь B: ' . $httpClient->doorMode[1];
                    break;
                    
                case 'getinputports':
                    $httpClient->getInputPortState();
                    $data = array();
                    $response = "Состояние входных портов:\n";
                    foreach ($httpClient->portStateInput as $i => $state) {
                        $portName = 'IN' . ($i + 1);
                        $data[$portName] = $state;
                        $response .= $portName . ': ' . $state . "\n";
                    }
                    break;
                    
                case 'getscudmode':
                    $httpClient->getScudMode();
                    $data['scud_mode'] = $httpClient->scud;
                    $response = 'Режим СКУД: ' . $httpClient->scud;
                    break;
                    
                case 'getkeycount':
                    $httpClient->getDeviceInfo();
                    $data = array();
                    $response = "Количество ключей:\n";
                    if (isset($httpClient->keyCount['0'])) {
                        $data['door_0'] = $httpClient->keyCount['0'];
                        $response .= 'Дверь 0: ' . $httpClient->keyCount['0'] . "\n";
                    }
                    if (isset($httpClient->keyCount['1'])) {
                        $data['door_1'] = $httpClient->keyCount['1'];
                        $response .= 'Дверь 1: ' . $httpClient->keyCount['1'] . "\n";
                    }
                    if (empty($data)) {
                        $response = 'Не удалось получить количество ключей';
                    }
                    break;
                    
                case 'getallinfo':
                    $httpClient->getDeviceInfo();
                    $data = array(
                        'ip' => $httpClient->ip_address,
                        'mac' => $httpClient->mac_address,
                        'online' => $httpClient->isOnline,
                        'isWp' => $httpClient->isWp,
                        'isTest' => $httpClient->isTest,
                        'scud_mode' => $httpClient->scud,
                        'soft_version' => $httpClient->softVersion,
                        'door_a_mode' => $httpClient->doorMode[0],
                        'door_b_mode' => $httpClient->doorMode[1],
                        'key_count' => $httpClient->keyCount,
                        'port_state' => $httpClient->portStateInput
                    );
                    $response = "=== ВСЯ ИНФОРМАЦИЯ О КОНТРОЛЛЕРЕ ===\n";
                    $response .= "IP адрес: " . $data['ip'] . "\n";
                    $response .= "MAC адрес: " . $data['mac'] . "\n";
                    $response .= "Online: " . ($data['online'] ? 'Да' : 'Нет') . "\n";
                    $response .= "WP: " . ($data['isWp'] ? 'Включен' : 'Выключен') . "\n";
                    $response .= "Test: " . ($data['isTest'] ? 'Включен' : 'Выключен') . "\n";
                    $response .= "Режим СКУД: " . $data['scud_mode'] . "\n";
                    $response .= "Версия прошивки: " . $data['soft_version'] . "\n";
                    $response .= "Дверь A: " . $data['door_a_mode'] . "\n";
                    $response .= "Дверь B: " . $data['door_b_mode'] . "\n";
                    $response .= "\nКоличество ключей:\n";
                    if (isset($data['key_count']['0'])) {
                        $response .= "  Дверь 0: " . $data['key_count']['0'] . "\n";
                    }
                    if (isset($data['key_count']['1'])) {
                        $response .= "  Дверь 1: " . $data['key_count']['1'] . "\n";
                    }
                    $response .= "\nСостояние входов:\n";
                    foreach ($data['port_state'] as $i => $state) {
                        $response .= "  IN" . ($i + 1) . ": " . $state . "\n";
                    }
                    break;
                    
                default:
                    $httpClient->getDeviceInfo();
                    $data = array(
                        'soft_version' => $httpClient->softVersion,
                        'isWp' => $httpClient->isWp,
                        'isTest' => $httpClient->isTest,
                        'mac' => $httpClient->mac_address,
                        'scud_mode' => $httpClient->scud,
                        'door_a_mode' => $httpClient->doorMode[0],
                        'door_b_mode' => $httpClient->doorMode[1]
                    );
                    $response = "Доступная информация об устройстве:\n";
                    foreach ($data as $key => $value) {
                        $response .= $key . ': ' . $value . "\n";
                    }
                    break;
            }
            
            $httpClient->disconnect();
            $this->result['data'] = $data;
            
            return $response;
            
        } catch (Exception $e) {
            if (isset($httpClient)) {
                $httpClient->disconnect();
            }
            throw $e;
        }
    }
    
    /**
     * Обработка ответа
     */
    private function processResponse($response, $originalCommand, $method = 'ts2')
    {
        $this->result['raw_response'] = $response;
        
        if ($method === 'http' && !empty($this->result['data'])) {
            return;
        }
        
        $parsed = $this->parseTs2Response($response, $originalCommand);
        
        if ($parsed !== false) {
            $this->result['data'] = $parsed;
        } else {
            $this->result['data'] = $response;
        }
        
        if (strpos($response, 'ERR') !== false) {
            $this->result['status'] = 'warning';
            $this->result['message'] = 'Устройство вернуло ошибку';
        } else {
            $this->result['status'] = 'success';
            $this->result['message'] = 'Команда выполнена успешно';
        }
    }
    
    /**
     * Парсинг ответа от TS2
     */
    private function parseTs2Response($response, $command)
    {
        $result = array();
        
        $clean = $response;
        if (strpos($clean, 'r77 OK') !== false) {
            $clean = trim(str_replace('r77 OK', '', $clean));
        }
        $clean = trim($clean);
        
        switch (strtolower($command)) {
            case 'getversion':
                if (preg_match('/([0-9]+\.[0-9]+)/', $clean, $matches)) {
                    $result['version'] = $matches[1];
                }
                if (preg_match('/([A-Za-z]+\s+[0-9]+\s+[0-9]{4})/', $clean, $matches)) {
                    $result['build_date'] = $matches[1];
                }
                if (preg_match('/(www\.[a-z0-9]+\.[a-z]+)/', $clean, $matches)) {
                    $result['url'] = $matches[1];
                }
                if (empty($result)) {
                    $result['raw'] = $clean;
                }
                break;
                
            case 'getdevicetime':
                if (preg_match('/(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})/', $clean, $matches)) {
                    $result['device_time'] = $matches[1];
                    $result['server_time'] = date('Y-m-d H:i:s');
                    $diff = time() - strtotime($matches[1]);
                    $result['time_diff'] = $diff . ' сек';
                    $result['time_diff_abs'] = abs($diff) . ' сек';
                    $result['status'] = abs($diff) < 5 ? '✅ Время синхронизировано' : '⚠️ Требуется синхронизация времени';
                } else {
                    $result['raw'] = $clean;
                }
                break;
                
            case 'getjmp':
            case 'getjumpers':
                if (preg_match('/Jmp=(\d+)/', $clean, $matches)) {
                    $jmp = intval($matches[1]);
                    $result['raw'] = $jmp;
                    $result['binary'] = sprintf('%08b', $jmp);
                    $result['bits'] = array(
                        'bit0 (WP)' => ($jmp & 1) ? '1 ✅' : '0 ❌',
                        'bit1 (Test)' => ($jmp & 2) ? '1 ✅' : '0 ❌',
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
                
            default:
                $result['raw'] = $clean;
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
        
        return empty($result) ? false : $result;
    }
    
    /**
     * Список всех команд с группировкой
     */
    private function getCommandList()
    {
        return array(
            '📡 TS2 Команды' => array(
                'getversion' => 'Получить версию',
                'getdevicetime' => 'Получить время устройства',
                'getjmp' => 'Получить состояние джамперов',
                'getmac' => 'Получить MAC адрес',
                'getkeycount' => 'Получить количество ключей',
                'synctime' => 'Синхронизировать время',
                'opendoor' => 'Открыть дверь',
                'closedoor' => 'Закрыть дверь',
            ),
            '🌐 HTTP Команды' => array(
                'getversion' => 'Получить версию',
                'getdevicemode' => 'Состояние WP/Test/MAC',
                'getdoormode' => 'Режим работы дверей',
                'getinputports' => 'Состояние входов',
                'getscudmode' => 'Режим СКУД',
                'getkeycount' => 'Количество ключей',
                'getallinfo' => 'Вся информация',
            )
        );
    }
}