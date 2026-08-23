<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отладка контроллеров Артонит</title>
    <style>
        /* Основные стили */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
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
        
        /* Форма */
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
        
        .btn-success {
            background: #2ecc71;
            color: white;
        }
        
        .btn-success:hover {
            background: #27ae60;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
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
        
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        
        .status-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-info {
            background: #d1ecf1;
            color: #0c5460;
        }
        
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
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.6;
        }
        
        .result-data .key {
            color: #9cdcfe;
        }
        
        .result-data .value {
            color: #ce9178;
        }
        
        .result-data .string {
            color: #ce9178;
        }
        
        .result-data .number {
            color: #b5cea8;
        }
        
        .result-data .boolean {
            color: #569cd6;
        }
        
        .result-data .null {
            color: #569cd6;
        }
        
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
        
        /* Специальные стили для разных типов ответов */
        .json-view {
            color: #d4d4d4;
        }
        
        .json-view .json-key {
            color: #9cdcfe;
        }
        
        .json-view .json-string {
            color: #ce9178;
        }
        
        .json-view .json-number {
            color: #b5cea8;
        }
        
        .json-view .json-boolean {
            color: #569cd6;
        }
        
        .json-view .json-null {
            color: #569cd6;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        /* Адаптивность */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .form-group {
                min-width: 100%;
            }
            
            .form-actions {
                flex-wrap: wrap;
            }
            
            .form-actions .btn {
                flex: 1;
                text-align: center;
            }
            
            .result-meta {
                grid-template-columns: 1fr;
            }
        }
        
        /* Анимация загрузки */
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .loading.active {
            display: block;
        }
        
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
        
        /* Вкладки */
        .tabs {
            display: flex;
            gap: 5px;
            margin-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .tab {
            padding: 8px 20px;
            cursor: pointer;
            border: none;
            background: none;
            font-size: 14px;
            color: #7f8c8d;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .tab:hover {
            color: #2c3e50;
        }
        
        .tab.active {
            color: #3498db;
            border-bottom-color: #3498db;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
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
        <form method="POST" action="" id="commandForm">
            <div class="form-section">
                <div class="form-row">
                    <div class="form-group">
                        <label for="dev_name">📟 Имя контроллера</label>
                        <input type="text" 
                               id="dev_name" 
                               name="dev_name" 
                               value="<?php echo htmlspecialchars($defaults['dev_name']); ?>" 
                               placeholder="VP3 K3\1"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="ip_server">🌐 IP сервера</label>
                        <input type="text" 
                               id="ip_server" 
                               name="ip_server" 
                               value="<?php echo htmlspecialchars($defaults['ip_server']); ?>" 
                               placeholder="127.0.0.1"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="port">🔌 Порт</label>
                        <input type="number" 
                               id="port" 
                               name="port" 
                               value="<?php echo htmlspecialchars($defaults['port']); ?>" 
                               placeholder="1967"
                               required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <label for="command">⚡ Команда</label>
                        <select id="command_select" name="command_select" onchange="updateCommandField()">
                            <option value="">-- Выберите команду --</option>
                            <?php foreach ($command_list as $value => $label): ?>
                            <option value="<?php echo $value; ?>" <?php echo ($defaults['command'] == $value) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
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
            
            <!-- Скрытое поле для отправки команды -->
            <input type="hidden" name="command" id="command_hidden" value="<?php echo htmlspecialchars($defaults['command']); ?>">
        </form>
        
        <!-- Индикатор загрузки -->
        <div class="loading" id="loadingIndicator">
            <div class="spinner"></div>
            <p style="margin-top: 10px; color: #7f8c8d;">Выполняется команда...</p>
        </div>
        
        <!-- Результаты -->
        <?php if (!empty($result)): ?>
        <div class="result-section">
            <div class="result-header">
                <h3 style="font-weight: 300; color: #2c3e50;">
                    📊 Результат выполнения
                </h3>
                <span class="result-status status-<?php echo $result['status']; ?>">
                    <?php
                        $status_labels = array(
                            'success' => '✅ Успешно',
                            'error' => '❌ Ошибка',
                            'warning' => '⚠️ Предупреждение',
                            'info' => 'ℹ️ Информация'
                        );
                        // Исправлено для PHP 5.6 - заменен оператор ??
                        echo isset($status_labels[$result['status']]) ? $status_labels[$result['status']] : $result['status'];
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
                    <span class="meta-label">Время выполнения</span>
                    <span class="meta-value"><?php echo $result['execution_time']; ?> сек</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Отправлено</span>
                    <span class="meta-value" style="font-size: 11px;">
                        <?php echo isset($result['command_parsed']) ? htmlspecialchars($result['command_parsed']) : '—'; ?>
                    </span>
                </div>
            </div>
            
            <!-- Вкладки для отображения результатов -->
            <div class="tabs">
                <button class="tab active" data-tab="parsed" onclick="switchTab('parsed')">
                    📋 Разобранный ответ
                </button>
                <button class="tab" data-tab="raw" onclick="switchTab('raw')">
                    📄 Сырой ответ
                </button>
                <?php if (!empty($result['raw_response']) && $result['raw_response'] != $result['data']): ?>
                <button class="tab" data-tab="compare" onclick="switchTab('compare')">
                    🔄 Сравнение
                </button>
                <?php endif; ?>
            </div>
            
            <!-- Разобранный ответ -->
            <div class="tab-content active" id="tab-parsed">
                <div class="result-data">
                    <?php if (is_array($result['data'])): ?>
                        <?php echo formatArray($result['data']); ?>
                    <?php else: ?>
                        <span style="color: #d4d4d4;"><?php echo htmlspecialchars($result['data']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Сырой ответ -->
            <div class="tab-content" id="tab-raw">
                <div class="result-data">
                    <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word;"><?php echo htmlspecialchars($result['raw_response']); ?></pre>
                </div>
            </div>
            
            <!-- Сравнение -->
            <?php if (!empty($result['raw_response']) && $result['raw_response'] != $result['data']): ?>
            <div class="tab-content" id="tab-compare">
                <div class="result-data" style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 300px;">
                        <h4 style="color: #9cdcfe; margin-bottom: 10px;">📄 Сырой ответ</h4>
                        <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word;"><?php echo htmlspecialchars($result['raw_response']); ?></pre>
                    </div>
                    <div style="flex: 1; min-width: 300px;">
                        <h4 style="color: #ce9178; margin-bottom: 10px;">📋 Разобранный ответ</h4>
                        <?php if (is_array($result['data'])): ?>
                            <?php echo formatArray($result['data']); ?>
                        <?php else: ?>
                            <span style="color: #d4d4d4;"><?php echo htmlspecialchars($result['data']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Информация о модуле -->
        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #ecf0f1; font-size: 12px; color: #95a5a6; text-align: center;">
            <p>
                Модуль отладки контроллеров Артонит v1.0 &bull; 
                Использует TS2client &bull; 
                <?php echo date('d.m.Y H:i:s'); ?>
            </p>
            <p style="margin-top: 5px;">
                🟢 Доступные команды: getversion, getdevicetime, getjmp, getmac, getkeycount, synctime, opendoor, closedoor
            </p>
        </div>
    </div>
    
    <script>
        // Функция для обновления поля команды
        function updateCommandField() {
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
        
        // Переключение вкладок
        function switchTab(tabId) {
            // Скрыть все вкладки
            document.querySelectorAll('.tab-content').forEach(function(el) {
                el.classList.remove('active');
            });
            
            // Показать выбранную вкладку
            document.getElementById('tab-' + tabId).classList.add('active');
            
            // Обновить кнопки
            document.querySelectorAll('.tab').forEach(function(el) {
                el.classList.remove('active');
                if (el.dataset.tab === tabId) {
                    el.classList.add('active');
                }
            });
        }
        
        // Очистка результатов
        function clearResults() {
            var resultSection = document.querySelector('.result-section');
            if (resultSection) {
                resultSection.remove();
            }
            // Перезагружаем страницу без параметров
            window.location.href = window.location.pathname;
        }
        
        // Показ индикатора загрузки при отправке формы
        document.getElementById('commandForm').addEventListener('submit', function() {
            document.getElementById('loadingIndicator').classList.add('active');
            document.getElementById('btnExecute').disabled = true;
            document.getElementById('btnExecute').textContent = '⏳ Выполняется...';
        });
        
        // Инициализация при загрузке
        document.addEventListener('DOMContentLoaded', function() {
            updateCommandField();
            
            // Если есть своя команда, показываем поле
            var customInput = document.getElementById('custom_command');
            if (customInput.value) {
                document.getElementById('command_select').value = 'custom';
                updateCommandField();
            }
        });
        
        // Поддержка Enter в поле custom_command
        document.getElementById('custom_command').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('commandForm').submit();
            }
        });
    </script>
</body>
</html>

<?php
/**
 * Функция форматирования массива в виде дерева
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
                    $display = '<span class="null">null</span>';
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