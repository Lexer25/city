<div class="container">
    <h2>Настройки базы данных <small>Firebird ODBC</small></h2>
    
    <?php if (Session::instance()->get('flash_message')): ?>
        <?php
        $flash = Session::instance()->get('flash_message');
        $type = Arr::get($flash, 'type', 'info');
        $text = Arr::get($flash, 'text', '');
        $alert_class = 'alert-' . ($type === 'error' ? 'danger' : $type);
        ?>
        <div class="alert <?php echo $alert_class; ?> alert-dismissible fade in" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <?php echo HTML::chars($text); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($db_error) && !empty($db_error)): ?>
        <div class="alert alert-warning alert-dismissible fade in" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4><i class="glyphicon glyphicon-warning-sign"></i> Ошибка подключения к базе данных</h4>
            <p>Текущее подключение к базе данных не работает с ошибкой: <code><?php echo HTML::chars($db_error); ?></code></p>
            <p>Этот модуль позволяет исправить подключение к базе данных. Пожалуйста, выберите рабочий DSN из списка ниже.</p>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-12">
            <!-- ODBC Selection -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">ODBC База данных <small>DSN из реестра Windows</small></h3>
                </div>
                <div class="panel-body">
                    <form action="<?php echo URL::site('dbsetting/select_dsn'); ?>" method="post" class="form-inline">
                        <div class="form-group">
                            <label>Текущий: <strong><?php echo HTML::chars($current_dsn); ?></strong></label>
                            <small class="text-muted">(сохранено в config/database.php)</small>
                        </div>
                        <div class="form-group" style="margin-left: 20px;">
                            <label for="dsn">Переключиться на:</label>
                            <select name="dsn" id="dsn" class="form-control input-sm">
                                <?php foreach ($odbc_dsns as $name => $dsn): ?>
                                    <option value="<?php echo HTML::chars($name); ?>" <?php echo ($dsn === $current_dsn) ? 'selected' : ''; ?>>
                                        <?php echo HTML::chars($name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Переключить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <!-- Backup -->
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h3 class="panel-title">Резервное копирование</h3>
                </div>
                <div class="panel-body">
                    <form action="<?php echo URL::site('dbsetting/backup'); ?>" method="post">
                        <div class="form-group">
                            <label>Путь к папке с базой данных:</label>
                            <div class="input-group">
                                <input type="text" name="database_dir" id="database_dir"
                                       class="form-control input-sm"
                                       value="<?php echo HTML::chars($database_dir); ?>"
                                       placeholder="D:\rrr\hl" required>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-primary btn-sm" onclick="saveDatabaseDir()">
                                        <span class="glyphicon glyphicon-floppy-disk"></span> Сохранить путь
                                    </button>
                                </span>
                            </div>
                            <small class="text-muted">Папка, в которой находится файл базы данных</small>
                        </div>

                        <div class="form-group">
                            <label>Имя файла базы данных:</label>
                            <div class="input-group">
                                <input type="text" name="database_filename" id="database_filename"
                                       class="form-control input-sm"
                                       value="<?php echo HTML::chars($database_filename); ?>"
                                       placeholder="ShieldPro_rest.GDB" required>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default btn-sm" onclick="browseDatabaseFile()">
                                        <span class="glyphicon glyphicon-folder-open"></span> Обзор
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="saveDatabaseFilename()">
                                        <span class="glyphicon glyphicon-floppy-disk"></span> Сохранить
                                    </button>
                                </span>
                            </div>
                            <small class="text-muted">Имя файла базы данных (выберите через Обзор или введите вручную)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="backup_dir">Папка для сохранения резервной копии:</label>
                            <input type="text" name="backup_dir" id="backup_dir"
                                   class="form-control input-sm"
                                   value="<?php echo HTML::chars($backup_dir); ?>"
                                   placeholder="C:\service_skud\" required>
                            <small class="text-muted">По умолчанию: C:\service_skud\</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Сгенерированное имя файла резервной копии:</label>
                            <div class="well well-sm" style="margin-bottom: 0; font-family: monospace;">
                                <?php
                                $db_filename = pathinfo($database_path, PATHINFO_FILENAME);
                                $timestamp = date('Y-m-d_His');
                                $preview_filename = $db_filename . '_' . $timestamp . '.fbk';
                                echo HTML::chars($preview_filename);
                                ?>
                            </div>
                            <small class="text-muted">Формат: имя_базы_данных_год-месяц-день_время.fbk</small>
                        </div>
                        
                        <button type="submit" class="btn btn-success">
                            <span class="glyphicon glyphicon-floppy-disk"></span> Создать резервную копию
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <!-- Restore -->
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h3 class="panel-title">Восстановление</h3>
                </div>
                <div class="panel-body">
                    <form action="<?php echo URL::site('dbsetting/restore'); ?>" method="post" class="form-inline">
                        <div class="form-group" style="width: 70%;">
                            <input type="text" name="backup_file" class="form-control input-sm" placeholder="C:\backup\backup.fbk" required style="width: 100%;">
                        </div>
                        <button type="submit" class="btn btn-warning btn-sm">
                            <span class="glyphicon glyphicon-import"></span> Восстановить
                        </button>
                    </form>
                    <p class="help-block small">Сервис будет остановлен во время восстановления.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <!-- Service Status -->
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">Сервис Firebird</h3>
                </div>
                <div class="panel-body">
                    <?php
                    $status = $service_status;
                    $label_class = ($status === 'running') ? 'label-success' :
                                   (($status === 'stopped') ? 'label-danger' : 'label-default');
                    ?>
                    <div class="form-inline">
                        <div class="form-group">
                            <label>Статус:</label>
                            <span class="label <?php echo $label_class; ?>" style="margin-left: 10px;">
                                <?php
                                $status_text = $status;
                                if ($status === 'running') $status_text = 'запущен';
                                elseif ($status === 'stopped') $status_text = 'остановлен';
                                elseif ($status === 'unknown') $status_text = 'неизвестен';
                                echo HTML::chars($status_text);
                                ?>
                            </span>
                        </div>
                        <div class="form-group" style="margin-left: 20px;">
                            <a href="<?php echo URL::site('dbsetting/start_service'); ?>" class="btn btn-success btn-sm">
                                <span class="glyphicon glyphicon-play"></span> Запустить
                            </a>
                            <a href="<?php echo URL::site('dbsetting/stop_service'); ?>" class="btn btn-danger btn-sm">
                                <span class="glyphicon glyphicon-stop"></span> Остановить
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <!-- System Information -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Системная информация</h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>PHP:</strong> <?php echo PHP_VERSION; ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Kohana:</strong> <?php echo Kohana::VERSION; ?>
                        </div>
                        <div class="col-md-3">
                            <strong>ОС:</strong> <?php echo PHP_OS; ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Модуль:</strong> <?php echo DBSETTING_MODULE_VERSION; ?>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 15px;">
                        <div class="col-md-12">
                            <a href="<?php echo URL::site('dbsetting/edit_config'); ?>" class="btn btn-info btn-sm" target="_blank">
                                <span class="glyphicon glyphicon-edit"></span> Редактировать файл конфигурации
                            </a>
                            <small class="text-muted" style="margin-left: 10px;">
                                Редактировать module/dbsetting/config/dbsetting.php
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="alert alert-warning small">
        <strong>Внимание!</strong> Эти операции влияют на базу данных и сервис. Используйте с осторожностью.
    </div>
</div>

<style>
.glyphicon.spinning {
    animation: spin 1s infinite linear;
    -webkit-animation: spin2 1s infinite linear;
}
@keyframes spin {
    from { transform: scale(1) rotate(0deg); }
    to { transform: scale(1) rotate(360deg); }
}
@-webkit-keyframes spin2 {
    from { -webkit-transform: rotate(0deg); }
    to { -webkit-transform: rotate(360deg); }
}
</style>

<script>
// CSRF token for AJAX requests
var csrf_token = '<?php echo md5(session_id() . "dbsetting_save_path"); ?>';
console.log('dbsetting script loaded, csrf_token:', csrf_token);

function browseDatabaseFile() {
    // Create a file input element
    var fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = '.fdb,.FDB,.gdb,.GDB';
    fileInput.style.display = 'none';
    
    // Add change event listener
    fileInput.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            console.log('File selected:', this.files[0]);
            console.log('File path property:', this.files[0].path);
            console.log('Input value:', this.value);
            var filePath = '';
            // Try to get full path from various properties
            if (this.files[0].path) {
                filePath = this.files[0].path; // Full path in some environments (Electron, older Chrome)
            } else if (this.value) {
                // In some browsers, input.value contains the full path (though may be fakepath)
                filePath = this.value;
                // Remove fakepath prefix if present
                if (filePath.indexOf('C:\\fakepath\\') === 0) {
                    filePath = filePath.substring(12);
                }
            } else {
                filePath = this.files[0].name; // Fallback to filename only
            }
            console.log('Selected filePath:', filePath);
            
            // Split into directory and filename
            var lastSeparator = Math.max(filePath.lastIndexOf('\\'), filePath.lastIndexOf('/'));
            if (lastSeparator >= 0) {
                var dir = filePath.substring(0, lastSeparator);
                var filename = filePath.substring(lastSeparator + 1);
                document.getElementById('database_dir').value = dir;
                document.getElementById('database_filename').value = filename;
            } else {
                // No separator, treat as filename only
                document.getElementById('database_filename').value = filePath;
                // Keep directory unchanged
            }
            
            // Check if the path looks like a filename only (no directory separators)
            if (filePath && !filePath.includes('\\') && !filePath.includes('/') && !filePath.includes(':')) {
                console.warn('Браузер предоставил только имя файла, а не полный путь.');
                alert('Внимание: Браузер не предоставляет полный путь к файлу.\n\nВыбран только файл: ' + filePath + '\n\nПожалуйста, скопируйте полный путь к файлу из проводника Windows и вставьте его в поле вручную.');
            }
        }
    });
    
    // Trigger file dialog
    document.body.appendChild(fileInput);
    fileInput.click();
    document.body.removeChild(fileInput);
}

function saveDatabaseDir() {
    var dbDir = document.getElementById('database_dir').value;
    if (!dbDir) {
        alert('Пожалуйста, укажите путь к папке базы данных.');
        return;
    }
    
    // Show loading indicator
    var saveBtn = document.querySelector('button[onclick="saveDatabaseDir()"]');
    var originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<span class="glyphicon glyphicon-refresh spinning"></span> Сохранение...';
    saveBtn.disabled = true;
    
    // Create form data
    var formData = new FormData();
    formData.append('database_dir', dbDir);
    formData.append('csrf_token', csrf_token);
    
    // Send POST request
    fetch('<?php echo URL::site("dbsetting/save_database_dir"); ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json().then(data => ({ ok: true, data }));
        } else {
            return response.text().then(text => ({ ok: false, text }));
        }
    })
    .then(result => {
        // Restore button
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
        
        if (result.ok) {
            var data = result.data;
            if (data.success) {
                alert('Путь к папке успешно сохранен в конфигурации.');
            } else {
                alert('Ошибка: ' + (data.message || 'Не удалось сохранить путь.'));
            }
        } else {
            // Non-JSON response, likely HTML error page
            console.error('Non-JSON response:', result.text.substring(0, 200));
            alert('Сервер вернул некорректный ответ. Возможно, произошла ошибка на сервере. Проверьте консоль для деталей.');
        }
    })
    .catch(error => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
        alert('Ошибка сети: ' + error.message);
    });
}

function saveDatabaseFilename() {
    console.log('saveDatabaseFilename called');
    var dbFilename = document.getElementById('database_filename').value;
    console.log('dbFilename:', dbFilename);
    if (!dbFilename) {
        alert('Пожалуйста, укажите имя файла базы данных.');
        return;
    }
    
    // Show loading indicator
    var saveBtn = document.querySelector('button[onclick="saveDatabaseFilename()"]');
    var originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<span class="glyphicon glyphicon-refresh spinning"></span> Сохранение...';
    saveBtn.disabled = true;
    
    // Create form data
    var formData = new FormData();
    formData.append('database_filename', dbFilename);
    formData.append('csrf_token', csrf_token);
    
    // Send POST request
    fetch('<?php echo URL::site("dbsetting/save_database_filename"); ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json().then(data => ({ ok: true, data }));
        } else {
            return response.text().then(text => ({ ok: false, text }));
        }
    })
    .then(result => {
        // Restore button
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
        
        if (result.ok) {
            var data = result.data;
            if (data.success) {
                alert('Имя файла базы данных успешно сохранено в конфигурации.');
            } else {
                alert('Ошибка: ' + (data.message || 'Не удалось сохранить имя файла.'));
            }
        } else {
            // Non-JSON response, likely HTML error page
            console.error('Non-JSON response:', result.text.substring(0, 200));
            alert('Сервер вернул некорректный ответ. Возможно, произошла ошибка на сервере. Проверьте консоль для деталей.');
        }
    })
    .catch(error => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
        alert('Ошибка сети: ' + error.message);
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // No special initialization needed
});
</script>