<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отладка контроллеров Артонит</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
            padding-bottom: 15px;
            margin-bottom: 25px;
            font-weight: 300;
        }
        h1 small {
            font-size: 14px;
            color: #7f8c8d;
            font-weight: normal;
            margin-left: 15px;
        }
        .form-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #555;
            margin-bottom: 5px;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3498db;
        }
        .form-group input[type="text"] {
            font-family: 'Consolas', monospace;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 5px;
        }
        .btn {
            padding: 10px 25px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #3498db;
            color: white;
        }
        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(52,152,219,0.3);
        }
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .btn-danger:hover {
            background: #c0392b;
        }
        
        /* Результаты */
        .result-section {
            margin-top: 25px;
            border-top: 2px solid #ecf0f1;
            padding-top: 25px;
        }
        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .result-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .status-success { background: #d4edda; color: #155724; }
        .status-error { background: #f8d7da; color: #721c24; }
        .status-warning { background: #fff3cd; color: #856404; }
        .status-info { background: #d1ecf1; color: #0c5460; }
        
        .result-message {
            padding: 12px 15px;
            background: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .result-data {
            background: #1e1e1e;
            color: #d4d4d4;
            border-radius: 6px;
            padding: 15px;
            overflow-x: auto;
            font-family: 'Consolas', monospace;
            font-size: 13px;
            line-height: 1.6;
        }
        .result-data .key { color: #9cdcfe; }
        .result-data .string { color: #ce9178; }
        .result-data .number { color: #b5cea8; }
        .result-data .boolean { color: #569cd6; }
        
        .result-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        .result-meta .meta-item {
            display: flex;
            flex-direction: column;
        }
        .result-meta .meta-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #7f8c8d;
            font-weight: 600;
        }
        .result-meta .meta-value {
            font-size: 14px;
            font-family: 'Consolas', monospace;
        }
        
        /* Информация о методах */
        .method-info {
            margin-top: 10px;
            padding: 10px 15px;
            background: #e8f4fd;
            border-left: 4px solid #3498db;
            border-radius: 4px;
            font-size: 13px;
            color: #2c3e50;
        }
        .method-info strong {
            color: #2980b9;
        }
        .method-info ul {
            margin: 5px 0 0 20px;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .loading.active { display: block; }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .container { padding: 15px; }
            .form-group { min-width: 100%; }
            .form-actions { flex-wrap: wrap; }
            .form-actions .btn { flex: 1; text-align: center; }
            .result-meta { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>
            🔧 Отладка контроллеров Артонит
            <small>v1.0</small>
        </h1>
        
<!-- Форма -->
<!-- Форма -->
<form method="POST" action="" id="commandForm">
    <div class="form-section">
        <div class="form-row">
            <div class="form-group" style="flex: 2;">
                <label for="dev_name">📟 Имя контроллера</label>
                <input type="text" 
                       id="dev_name" 
                       name="dev_name" 
                       value="<?php echo htmlspecialchars($defaults['dev_name']); ?>" 
                       placeholder="VP3 K3\1"
                       required>
            </div>
            
            <div class="form-group" style="flex: 2;">
                <label for="ip_server">🌐 IP сервера</label>
                <input type="text" 
                       id="ip_server" 
                       name="ip_server" 
                       value="<?php echo htmlspecialchars($defaults['ip_server']); ?>" 
                       placeholder="127.0.0.1"
                       required>
            </div>
            
            <div class="form-group" style="flex: 1;">
                <label for="port">🔌 Порт сервера</label>
                <input type="number" 
                       id="port" 
                       name="port" 
                       value="<?php echo htmlspecialchars($defaults['port']); ?>" 
                       placeholder="1967"
                       required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group" style="flex: 3;">
                <label for="command">⚡ Команда</label>
                <select id="command_select" name="command_select" onchange="updateCommand()">
                    <option value="">-- Выберите команду --</option>
                    <?php foreach ($command_list as $group_name => $commands): ?>
                    <optgroup label="<?php echo $group_name; ?>">
                        <?php foreach ($commands as $value => $label): ?>
                        <option value="<?php echo $value; ?>" 
                            <?php echo ($defaults['command'] == $value) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <?php endforeach; ?>
                    <option value="custom">✏️ Своя команда</option>
                </select>
            </div>
            
            <div class="form-group" style="flex: 2;" id="custom_command_group" style="display: none;">
                <label for="custom_command">✏️ Введите команду</label>
                <input type="text" 
                       id="custom_command" 
                       name="custom_command" 
                       placeholder="Введите любую команду..."
                       value="<?php echo htmlspecialchars($defaults['command']); ?>">
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary" id="btnExecute">
                ▶️ Выполнить
            </button>
            <button type="reset" class="btn btn-secondary">
                🔄 Сбросить
            </button>
            <button type="button" class="btn btn-danger" onclick="clearResults()">
                🗑️ Очистить результат
            </button>
        </div>
    </div>
    
    <input type="hidden" name="command" id="command_hidden" value="<?php echo htmlspecialchars($defaults['command']); ?>">
</form>

<!-- Информация -->
<div class="method-info">
    <strong>ℹ️ Автоматическое определение метода:</strong>
    Метод выполнения (TS2 или HTTP) определяется автоматически в зависимости от выбранной команды.
    <ul>
        <li><strong>📡 TS2 команды:</strong> getversion, getdevicetime, getjmp, getmac, getkeycount, synctime, opendoor, closedoor</li>
        <li><strong>🌐 HTTP команды:</strong> getdevicemode, getdoormode, getinputports, getscudmode, getkeycount, getallinfo</li>
        <li><strong>⚠️ Примечание:</strong> Команда <strong>getversion</strong> доступна в обоих методах</li>
    </ul>
</div>

<!-- Информация о методах -->
<div class="method-info" id="methodInfo">
    <strong>ℹ️ TS2 метод:</strong>
    Команды выполняются через транспортный сервер.
    <ul>
        <li><strong>Доступные команды:</strong> getversion, getdevicetime, getjmp, getmac, getkeycount, synctime, opendoor, closedoor</li>
        <li><strong>Преимущества:</strong> Работает через ТС2, поддерживает управление дверями</li>
    </ul>
</div>

<div class="method-info" id="httpMethodInfo" style="display: none; border-left-color: #2ecc71; background: #e8f8f0;">
    <strong>🌐 HTTP метод:</strong>
    Сначала через TS2 отправляется команда <strong>DeviceInfo</strong> для получения IP адреса контроллера, затем выполняется HTTP запрос напрямую.
    <ul>
        <li><strong>Последовательность:</strong> DeviceInfo → получение IP → HTTP запрос</li>
        <li><strong>Доступные команды:</strong> getversion, getdevicemode, getdoormode, getinputports, getscudmode, getkeycount, getallinfo</li>
        <li><strong>Преимущества:</strong> Не требует знания IP контроллера, получает данные напрямую</li>
    </ul>
</div>
        
<!-- Информация о методах -->
<div class="method-info" id="methodInfo">
    <strong>ℹ️ TS2 метод:</strong>
    Требуется имя устройства, IP и порт сервера. Команды выполняются через транспортный сервер.
    <ul>
        <li><strong>Доступные команды:</strong> getversion, getdevicetime, getjmp, getmac, getkeycount, synctime, opendoor, closedoor</li>
        <li><strong>Преимущества:</strong> Работает через ТС2, поддерживает управление дверями</li>
    </ul>
</div>

<!-- Информация об HTTP методе (показывается при выборе HTTP) -->
<div class="method-info" id="httpMethodInfo" style="display: none; border-left-color: #2ecc71; background: #e8f8f0;">
    <strong>🌐 HTTP метод:</strong>
    Сначала через TS2 отправляется команда <strong>DeviceInfo</strong> для получения IP адреса контроллера, затем выполняется HTTP запрос напрямую.
    <ul>
        <li><strong>Последовательность:</strong> DeviceInfo → получение IP → HTTP запрос</li>
        <li><strong>Доступные команды:</strong> getversion, getdevicemode, getdoormode, getinputports, getscudmode, getkeycount, getallinfo</li>
        <li><strong>Преимущества:</strong> Не требует знания IP контроллера, получает данные напрямую</li>
    </ul>
</div>
        
        <div class="loading" id="loadingIndicator">
            <div class="spinner"></div>
            <p style="margin-top: 10px; color: #7f8c8d;">Выполняется команда...</p>
        </div>
        
        <!-- Результаты -->
        <?php if (!empty($result)): ?>
        <div class="result-section">
            <div class="result-header">
                <h3 style="font-weight: 300; color: #2c3e50;">
                    📊 Результат выполнения (<?php echo $result['method'] == 'ts2' ? 'TS2' : 'HTTP'; ?>)
                </h3>
                <span class="result-status status-<?php echo $result['status']; ?>">
                    <?php
                        $labels = array(
                            'success' => '✅ Успешно',
                            'error' => '❌ Ошибка',
                            'warning' => '⚠️ Предупреждение',
                            'info' => 'ℹ️ Информация'
                        );
                        echo isset($labels[$result['status']]) ? $labels[$result['status']] : $result['status'];
                    ?>
                </span>
            </div>
            
            <div class="result-message">
                <?php echo htmlspecialchars($result['message']); ?>
            </div>
            
            <div class="result-meta">
                <div class="meta-item">
                    <span class="meta-label">Команда</span>
                    <span class="meta-value">
                        <?php echo isset($result['command_sent']) ? htmlspecialchars($result['command_sent']) : '—'; ?>
                    </span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Метод</span>
                    <span class="meta-value"><?php echo strtoupper($result['method']); ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Время выполнения</span>
                    <span class="meta-value"><?php echo $result['execution_time']; ?> сек</span>
                </div>
                <?php if (!empty($result['command_parsed'])): ?>
                <div class="meta-item">
                    <span class="meta-label">Отправлено</span>
                    <span class="meta-value" style="font-size: 11px;">
                        <?php echo htmlspecialchars($result['command_parsed']); ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="result-data">
                <?php if (is_array($result['data'])): ?>
                    <?php echo formatArray($result['data']); ?>
                <?php else: ?>
                    <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word;"><?php echo htmlspecialchars($result['data']); ?></pre>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($result['raw_response']) && $result['raw_response'] != $result['data']): ?>
            <div style="margin-top: 15px;">
                <h4 style="color: #7f8c8d; font-weight: 300;">📄 Сырой ответ:</h4>
                <div class="result-data" style="margin-top: 5px;">
                    <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word;"><?php echo htmlspecialchars($result['raw_response']); ?></pre>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #ecf0f1; font-size: 12px; color: #95a5a6; text-align: center;">
            <p>
                Модуль отладки контроллеров Артонит v1.0 &bull; 
                <?php echo date('d.m.Y H:i:s'); ?>
            </p>
        </div>
    </div>
    
    <script>
function updateMethod() {
    var method = document.getElementById('method').value;
    var ts2Info = document.getElementById('methodInfo');
    var httpInfo = document.getElementById('httpMethodInfo');
    
    if (method === 'ts2') {
        ts2Info.style.display = 'block';
        httpInfo.style.display = 'none';
    } else {
        ts2Info.style.display = 'none';
        httpInfo.style.display = 'block';
    }
    
    updateCommand();
}

function updateCommand() {
    var select = document.getElementById('command_select');
    var customGroup = document.getElementById('custom_command_group');
    var customInput = document.getElementById('custom_command');
    var hiddenInput = document.getElementById('command_hidden');
    
    if (select.value === 'custom') {
        customGroup.style.display = 'block';
        hiddenInput.value = customInput.value || '';
        customInput.required = true;
    } else {
        customGroup.style.display = 'none';
        hiddenInput.value = select.value;
        customInput.required = false;
    }
}
    </script>
</body>
</html>

<?php
/**
 * Форматирование массива
 */
function formatArray($data, $level = 0)
{
    if (!is_array($data)) {
        return '<span class="string">' . htmlspecialchars($data) . '</span>';
    }
    
    $result = '';
    $indent = str_repeat('  ', $level);
    
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $result .= $indent . '<span class="key">' . htmlspecialchars($key) . '</span>: {<br>';
            $result .= formatArray($value, $level + 1);
            $result .= $indent . '}<br>';
        } else {
            $type = gettype($value);
            switch ($type) {
                case 'string':
                    $display = '<span class="string">' . htmlspecialchars($value) . '</span>';
                    break;
                case 'integer':
                case 'double':
                    $display = '<span class="number">' . htmlspecialchars($value) . '</span>';
                    break;
                case 'boolean':
                    $display = '<span class="boolean">' . ($value ? 'true' : 'false') . '</span>';
                    break;
                case 'NULL':
                    $display = '<span class="boolean">null</span>';
                    break;
                default:
                    $display = '<span class="string">' . htmlspecialchars($value) . '</span>';
            }
            $result .= $indent . '<span class="key">' . htmlspecialchars($key) . '</span>: ' . $display . '<br>';
        }
    }
    
    return $result;
}
?>