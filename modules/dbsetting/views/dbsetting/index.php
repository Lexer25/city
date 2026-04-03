<div class="container">
    <h2>Database Settings <small>Firebird ODBC</small></h2>
    
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
    
    <div class="row">
        <div class="col-md-12">
            <!-- ODBC Selection -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">ODBC Database <small>DSN from Windows Registry</small></h3>
                </div>
                <div class="panel-body">
                    <form action="<?php echo URL::site('dbsetting/select_dsn'); ?>" method="post" class="form-inline">
                        <div class="form-group">
                            <label>Current: <strong><?php echo HTML::chars($current_dsn); ?></strong></label>
                            <small class="text-muted">(saved in config/database.php)</small>
                        </div>
                        <div class="form-group" style="margin-left: 20px;">
                            <label for="dsn">Switch to:</label>
                            <select name="dsn" id="dsn" class="form-control input-sm">
                                <?php foreach ($odbc_dsns as $name => $dsn): ?>
                                    <option value="<?php echo HTML::chars($name); ?>" <?php echo ($dsn === $current_dsn) ? 'selected' : ''; ?>>
                                        <?php echo HTML::chars($name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Switch</button>
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
                    <h3 class="panel-title">Backup</h3>
                </div>
                <div class="panel-body">
                    <p><small>DB: <code><?php echo HTML::chars($database_path); ?></code></small></p>
                    <form action="<?php echo URL::site('dbsetting/backup'); ?>" method="post">
                        <button type="submit" class="btn btn-success">
                            <span class="glyphicon glyphicon-floppy-disk"></span> Create Backup
                        </button>
                        <small class="text-muted">Backup dir: <?php echo HTML::chars($backup_dir); ?></small>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <!-- Restore -->
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h3 class="panel-title">Restore</h3>
                </div>
                <div class="panel-body">
                    <form action="<?php echo URL::site('dbsetting/restore'); ?>" method="post" class="form-inline">
                        <div class="form-group" style="width: 70%;">
                            <input type="text" name="backup_file" class="form-control input-sm" placeholder="C:\backup\backup.fbk" required style="width: 100%;">
                        </div>
                        <button type="submit" class="btn btn-warning btn-sm">
                            <span class="glyphicon glyphicon-import"></span> Restore
                        </button>
                    </form>
                    <p class="help-block small">Service will be stopped during restore.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <!-- Service Status -->
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">Firebird Service</h3>
                </div>
                <div class="panel-body">
                    <?php
                    $status = $service_status;
                    $label_class = ($status === 'running') ? 'label-success' :
                                   (($status === 'stopped') ? 'label-danger' : 'label-default');
                    ?>
                    <div class="form-inline">
                        <div class="form-group">
                            <label>Status:</label>
                            <span class="label <?php echo $label_class; ?>" style="margin-left: 10px;">
                                <?php echo HTML::chars(ucfirst($status)); ?>
                            </span>
                        </div>
                        <div class="form-group" style="margin-left: 20px;">
                            <a href="<?php echo URL::site('dbsetting/start_service'); ?>" class="btn btn-success btn-sm">
                                <span class="glyphicon glyphicon-play"></span> Start
                            </a>
                            <a href="<?php echo URL::site('dbsetting/stop_service'); ?>" class="btn btn-danger btn-sm">
                                <span class="glyphicon glyphicon-stop"></span> Stop
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
                    <h3 class="panel-title">System Information</h3>
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
                            <strong>OS:</strong> <?php echo PHP_OS; ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Module:</strong> <?php echo DBSETTING_MODULE_VERSION; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="alert alert-warning small">
        <strong>Warning:</strong> These operations affect the database and service. Use with caution.
    </div>
</div>