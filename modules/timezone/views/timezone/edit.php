<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Редактирование временной зоны'); ?></h3>
    </div>
    <div class="panel-body">
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo URL::site('timezone/edit/' . Arr::get($timezone, 'id_timezone')); ?>">
            <div class="form-group">
                <label for="id"><?php echo __('ID'); ?></label>
                <input type="text" class="form-control" id="id" value="<?php echo htmlspecialchars(Arr::get($timezone, 'id_timezone')); ?>" disabled>
            </div>
            
            <div class="form-group <?php echo isset($errors['name']) ? 'has-error' : ''; ?>">
                <label for="name"><?php echo __('Название временной зоны'); ?> *</label>
                <input type="text" class="form-control" id="name" name="name" 
                       value="<?php echo isset($post['name']) ? htmlspecialchars($post['name']) : htmlspecialchars(Arr::get($timezone, 'name')); ?>" 
                       required>
                <?php if (isset($errors['name'])): ?>
                    <span class="help-block"><?php echo $errors['name']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group <?php echo isset($errors['time_start']) ? 'has-error' : ''; ?>">
                        <label for="time_start"><?php echo __('Время начала'); ?> *</label>
                        <div class="input-group date" id="datetimepicker_start">
                            <input type="text" class="form-control" id="time_start" name="time_start" 
                                   value="<?php echo isset($post['time_start']) ? htmlspecialchars($post['time_start']) : substr(htmlspecialchars(Arr::get($timezone, 'timestart')), 0, 8); ?>" 
                                   required>
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-time"></span>
                            </span>
                        </div>
                        <?php if (isset($errors['time_start'])): ?>
                            <span class="help-block"><?php echo $errors['time_start']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group <?php echo isset($errors['time_end']) ? 'has-error' : ''; ?>">
                        <label for="time_end"><?php echo __('Время окончания'); ?> *</label>
                        <div class="input-group date" id="datetimepicker_end">
                            <input type="text" class="form-control" id="time_end" name="time_end" 
                                   value="<?php echo isset($post['time_end']) ? htmlspecialchars($post['time_end']) : substr(htmlspecialchars(Arr::get($timezone, 'timeend')), 0, 8); ?>" 
                                   required>
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-time"></span>
                            </span>
                        </div>
                        <?php if (isset($errors['time_end'])): ?>
                            <span class="help-block"><?php echo $errors['time_end']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><?php echo __('Дни недели'); ?></h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="day_mon" value="1" <?php echo isset($flags['day_mon']) && $flags['day_mon'] ? 'checked' : ''; ?>>
                                    <?php echo __('Понедельник'); ?>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="day_tue" value="1" <?php echo isset($flags['day_tue']) && $flags['day_tue'] ? 'checked' : ''; ?>>
                                    <?php echo __('Вторник'); ?>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="day_wed" value="1" <?php echo isset($flags['day_wed']) && $flags['day_wed'] ? 'checked' : ''; ?>>
                                    <?php echo __('Среда'); ?>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="day_thu" value="1" <?php echo isset($flags['day_thu']) && $flags['day_thu'] ? 'checked' : ''; ?>>
                                    <?php echo __('Четверг'); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="day_fri" value="1" <?php echo isset($flags['day_fri']) && $flags['day_fri'] ? 'checked' : ''; ?>>
                                    <?php echo __('Пятница'); ?>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="day_sat" value="1" <?php echo isset($flags['day_sat']) && $flags['day_sat'] ? 'checked' : ''; ?>>
                                    <?php echo __('Суббота'); ?>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="day_sun" value="1" <?php echo isset($flags['day_sun']) && $flags['day_sun'] ? 'checked' : ''; ?>>
                                    <?php echo __('Воскресенье'); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><?php echo __('Специальные режимы'); ?></h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="flag_holidays" value="1" <?php echo isset($flags['flag_holidays']) && $flags['flag_holidays'] ? 'checked' : ''; ?>>
                                    <?php echo __('Учитывать праздники'); ?>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="flag_night" value="1" <?php echo isset($flags['flag_night']) && $flags['flag_night'] ? 'checked' : ''; ?>>
                                    <?php echo __('Ночной режим'); ?>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="flag_roundclock" value="1" id="roundclock_checkbox" <?php echo isset($flags['flag_roundclock']) && $flags['flag_roundclock'] ? 'checked' : ''; ?>>
                                    <?php echo __('Круглосуточно'); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary"><?php echo __('Сохранить'); ?></button>
                <a href="<?php echo URL::site('timezone'); ?>" class="btn btn-default"><?php echo __('Отмена'); ?></a>
            </div>
        </form>
        
    </div>
</div>

<script type="text/javascript">
    $(function() {
        // Инициализация datetimepicker для времени начала (только время, без даты)
        $('#datetimepicker_start').datetimepicker({
            format: 'HH:mm:ss',
            language: 'ru',
            pickDate: false,
            pickSeconds: true,
            pick12HourFormat: false
        });
        
        // Инициализация datetimepicker для времени окончания
        $('#datetimepicker_end').datetimepicker({
            format: 'HH:mm:ss',
            language: 'ru',
            pickDate: false,
            pickSeconds: true,
            pick12HourFormat: false
        });
        
        // При выборе "Круглосуточно" устанавливаем время 00:00:00 - 23:59:59
        $('#roundclock_checkbox').on('change', function() {
            if ($(this).is(':checked')) {
                $('#time_start').val('00:00:00');
                $('#time_end').val('23:59:59');
                $('#time_start').prop('readonly', true);
                $('#time_end').prop('readonly', true);
                // Обновляем datetimepicker
                $('#datetimepicker_start').data('DateTimePicker').setDate(new Date(0, 0, 0, 0, 0, 0));
                $('#datetimepicker_end').data('DateTimePicker').setDate(new Date(0, 0, 0, 23, 59, 59));
            } else {
                $('#time_start').prop('readonly', false);
                $('#time_end').prop('readonly', false);
            }
        });
        
        // Проверка при загрузке
        if ($('#roundclock_checkbox').is(':checked')) {
            $('#time_start').prop('readonly', true);
            $('#time_end').prop('readonly', true);
        }
    });
</script>