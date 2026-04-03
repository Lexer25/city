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
                            <label for="database_path">База данных для резервного копирования:</label>
                            <div class="input-group">
                                <input type="text" name="database_path" id="database_path"
                                       class="form-control input-sm"
                                       value="<?php echo HTML::chars($database_path); ?>"
                                       placeholder="C:\path\to\database.fdb" required>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default btn-sm" onclick="browseDatabaseFile()">
                                        <span class="glyphicon glyphicon-folder-open"></span> Обзор
                                    </button>
                                </span>
                            </div>
                            <small class="text-muted">Текущая база данных из настроек Firebird</small>
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

<script>
function browseDatabaseFile() {
    // Create a file input element
    var fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = '.fdb,.FDB,.gdb,.GDB';
    fileInput.style.display = 'none';
    
    // Add change event listener
    fileInput.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            var filePath = this.files[0].path || this.files[0].name;
            document.getElementById('database_path').value = filePath;
            updateFilenamePreview();
        }
    });
    
    // Trigger file dialog
    document.body.appendChild(fileInput);
    fileInput.click();
    document.body.removeChild(fileInput);
}

function updateFilenamePreview() {
    var dbPath = document.getElementById('database_path').value;
    if (!dbPath) return;
    
    // Extract filename without extension
    var filename = dbPath.split('\\').pop().split('/').pop();
    var dbName = filename.replace(/\.[^/.]+$/, ""); // Remove extension
    
    // Generate timestamp
    var now = new Date();
    var year = now.getFullYear();
    var month = String(now.getMonth() + 1).padStart(2, '0');
    var day = String(now.getDate()).padStart(2, '0');
    var hours = String(now.getHours()).padStart(2, '0');
    var minutes = String(now.getMinutes()).padStart(2, '0');
    var seconds = String(now.getSeconds()).padStart(2, '0');
    
    var timestamp = year + '-' + month + '-' + day + '_' + hours + minutes + seconds;
    var preview = dbName + '_' + timestamp + '.fbk';
    
    // Update preview (we'll need to add an element for this)
    var previewElement = document.getElementById('filename_preview');
    if (previewElement) {
        previewElement.textContent = preview;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add preview element if it doesn't exist
    var previewWell = document.querySelector('.well.well-sm');
    if (previewWell && !document.getElementById('filename_preview')) {
        previewWell.id = 'filename_preview';
    }
    
    // Add event listener to database path input
    var dbInput = document.getElementById('database_path');
    if (dbInput) {
        dbInput.addEventListener('input', updateFilenamePreview);
        dbInput.addEventListener('change', updateFilenamePreview);
    }
});
</script>