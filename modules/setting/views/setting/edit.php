<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('setting.page_title'); ?>: <?php echo basename($config_file); ?></h3>
    </div>
    <div class="panel-body">
        
        <?php echo $message; ?>
        
        <div class="alert alert-info">
            <strong><?php echo __('setting.warning'); ?></strong>
            <br>
            <a href="<?php echo URL::site('setting/backups'); ?>" class="btn btn-info btn-xs">
                <span class="glyphicon glyphicon-folder-open"></span> <?php echo __('setting.view_backups'); ?>
            </a>
        </div>
        
        <?php echo Form::open(); ?>
        
        <!-- Основные настройки -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">Основные настройки</h4>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Название города/объекта:</label>
                            <?php echo Form::input('city_name', Arr::get($config_data, 'city_name'), array('class' => 'form-control')); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Разработчик:</label>
                            <?php echo Form::input('developer', Arr::get($config_data, 'developer'), array('class' => 'form-control')); ?>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Версия:</label>
                            <?php echo Form::input('ver', Arr::get($config_data, 'ver'), array('class' => 'form-control')); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <!-- Дата обновления с календарем -->
                        <div class="form-group">
                            <label>Дата обновления:</label>
                            <div class="input-group date" id="datetimepicker_timeUpdate">
                                <input type="text" class="form-control" name="timeUpdate" value="<?php echo HTML::chars(Arr::get($config_data, 'timeUpdate')); ?>" placeholder="YYYY-MM-DD">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Подсветка версии (дней):</label>
                            <?php echo Form::input('lightVerDay', Arr::get($config_data, 'lightVerDay', 3), array('class' => 'form-control', 'type' => 'number')); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Пути и директории -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">Пути и директории</h4>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label>Директория логов:</label>
                    <?php echo Form::input('dir_log', Arr::get($config_data, 'dir_log'), array('class' => 'form-control')); ?>
					<span class="text-success">Этот путь используется для вывода списка log-файлов</span>
                </div>
                <div class="form-group">
                    <label>Директория сравнения:</label>
                    <?php echo Form::input('dir_compare', Arr::get($config_data, 'dir_compare'), array('class' => 'form-control')); ?>
                </div>
                <div class="form-group">
                    <label>Путь к curl:</label>
                    <?php echo Form::input('curl_place', Arr::get($config_data, 'curl_place'), array('class' => 'form-control')); ?>
                </div>
            </div>
        </div>
        
        <!-- Настройки отображения -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">Настройки отображения</h4>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Дней до окончания:</label>
                            <?php echo Form::input('count_day_befor_end_time', Arr::get($config_data, 'count_day_befor_end_time', 30), array('class' => 'form-control', 'type' => 'number')); ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Статистика за дней:</label>
                            <?php echo Form::input('stat_day_befor', Arr::get($config_data, 'stat_day_befor', 2), array('class' => 'form-control', 'type' => 'number')); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Формат RFID - радио-кнопки -->
                <div class="form-group">
                    <label class="control-label">Формат хранение RFID в базе данных</label>
                    <?php $rfid_value = Arr::get($config_data, 'baseFormatRfid', '0'); ?>
                    
                    <div class="well well-sm" style="background-color: #f9f9f9; margin-top: 10px;">
                        <div class="radio">
                            <label>
                                <input type="radio" name="baseFormatRfid" value="0" <?php echo ($rfid_value == '0') ? 'checked' : ''; ?>>
                                <strong>Формат 0</strong> - HEX 8 byte
                            </label>
                            <div style="margin-left: 25px; margin-top: 5px; color: #666; font-size: 12px;">
                                <code>00124CD8</code> - стандартный формат, 8 байт в шестнадцатеричном виде<br>
                                <span class="text-success">Номер идентификатора в шестнадцатеричном формате</span>
                            </div>
                        </div>
                        
                        <div class="radio" style="margin-top: 15px;">
                            <label>
                                <input type="radio" name="baseFormatRfid" value="1" <?php echo ($rfid_value == '1') ? 'checked' : ''; ?>>
                                <strong>Формат 1</strong> - 001A 10 byte
                            </label>
                            <div style="margin-left: 25px; margin-top: 5px; color: #666; font-size: 12px;">
                                <code>262F8F001A</code> - расширенный формат, 10 байт с префиксом 001A<br>
                                <span class="text-info">Номер идентификатора в формате Адемант</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Коды аналитики -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">Коды аналитики</h4>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label>OK (через запятую):</label>
                    <?php 
                    $analit_ok = Arr::get($config_data, 'analit_ok', []);
                    echo Form::input('analit_ok_array', is_array($analit_ok) ? implode(', ', $analit_ok) : '', array('class' => 'form-control')); 
                    ?>
                    <span class="help-block">Коды, которые следует рассматривать как правильную работу системы</span>
                </div>
                <div class="form-group">
                    <label>Ошибки (через запятую):</label>
                    <?php 
                    $analit_err = Arr::get($config_data, 'analit_err', []);
                    echo Form::input('analit_err_array', is_array($analit_err) ? implode(', ', $analit_err) : '', array('class' => 'form-control')); 
                    ?>
                    <span class="help-block">Коды, которые следует рассматривать как нарушение работы системы</span>
                </div>
                <div class="form-group">
                    <label>Переходные (через запятую):</label>
                    <?php 
                    $analit_transit = Arr::get($config_data, 'analit_transit', []);
                    echo Form::input('analit_transit_array', is_array($analit_transit) ? implode(', ', $analit_transit) : '', array('class' => 'form-control')); 
                    ?>
                    <span class="help-block">Коды переходных процессов (карта на удалении, но еще не удалена)</span>
                </div>
            </div>
        </div>
        
        <!-- Доступ без авторизации -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">Доступ без авторизации</h4>
            </div>
            <div class="panel-body">
                <div class="row">
                    <?php 
                    $view_without_auth = Arr::get($config_data, 'view_without_auth', []);
                    $items = ['load', 'load_order', 'device_control', 'events', 'people', 'door', 'log'];
                    foreach ($items as $item): 
                    ?>
                    <div class="col-md-3">
                        <div class="checkbox">
                            <label>
                                <?php echo Form::checkbox('view_without_auth[' . $item . ']', 'true', (bool) Arr::get($view_without_auth, $item, false)); ?>
                                <?php echo ucfirst(__('setting.'.$item)); ?>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Основные окна -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">Основные окна</h4>
            </div>
            <div class="panel-body">
                <div class="row">
                    <?php 
                    $main_windows = Arr::get($config_data, 'main_windows', []);
                    for ($i = 1; $i <= 5; $i++): 
                        $window_key = 'windows' . $i;
                    ?>
                    <div class="col-md-2">
                        <div class="checkbox">
                            <label>
                                <?php echo Form::checkbox('main_windows[' . $window_key . ']', 'true', (bool) Arr::get($main_windows, $window_key, false)); ?>
                                Окно <?php echo __('settint.windows'.$i); ?>
                            </label>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
        
        <!-- Кнопки -->
        <div class="form-group">
            <?php echo Form::submit('save', __('setting.save'), array('class' => 'btn btn-primary')); ?>
            <a href="<?php echo URL::site('dashboard'); ?>" class="btn btn-default"><?php echo __('setting.cancel'); ?></a>
        </div>
        
        <?php echo Form::close(); ?>
        
    </div>
</div>

<!-- JavaScript для календаря и подтверждения -->
<script>
$(document).ready(function() {
    // Инициализация календаря для поля "Дата обновления"
    if ($.fn.datetimepicker) {
        $('#datetimepicker_timeUpdate').datetimepicker({
            language: 'ru',
            format: 'YYYY-MM-DD',
            minView: 'month',
            maxView: 'year',
            autoclose: true,
            todayHighlight: true,
            todayBtn: true
        });
    } else {
        console.log('DateTimePicker не загружен, используется обычное поле ввода');
    }
    
    // Подтверждение сохранения
    $('form').on('submit', function() {
        return confirm('<?php echo __('setting.confirm_save'); ?>');
    });
    
    // Добавляем подсветку для выбранного радио
    $('input[type="radio"]').on('change', function() {
        $('.well .radio').removeClass('bg-info');
        $(this).closest('.radio').addClass('bg-info');
    });
    
    // Подсвечиваем текущее выбранное
    $('input[type="radio"]:checked').closest('.radio').addClass('bg-info');
});
</script>

<!-- Дополнительные стили -->
<style>
.well .radio {
    padding: 8px;
    margin: 0;
    border-radius: 4px;
    transition: background-color 0.2s;
}
.well .radio.bg-info {
    background-color: #d9edf7;
}
.well .radio:hover {
    background-color: #f5f5f5;
}
.well .radio.bg-info:hover {
    background-color: #c4e3f3;
}
code {
    background-color: #f5f5f5;
    border: 1px solid #ddd;
    padding: 2px 4px;
    border-radius: 3px;
    color: #c7254e;
}

/* Стили для календаря */
.input-group.date {
    width: 100%;
}
.input-group.date .input-group-addon {
    cursor: pointer;
}
.input-group.date .input-group-addon:hover {
    background-color: #e6e6e6;
}
</style>