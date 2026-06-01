<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Редактирование праздника'); ?></h3>
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
        
        <form method="POST" action="<?php echo URL::site('holiday/edit/' . Arr::get($holiday, 'id_holiday')); ?>">
            <div class="form-group">
                <label for="id"><?php echo __('ID'); ?></label>
                <input type="text" class="form-control" id="id" value="<?php echo htmlspecialchars(Arr::get($holiday, 'id_holiday')); ?>" disabled>
            </div>
            
            <div class="form-group <?php echo isset($errors['name']) ? 'has-error' : ''; ?>">
                <label for="name"><?php echo __('Название праздника'); ?> *</label>
                <input type="text" class="form-control" id="name" name="name" 
                       value="<?php echo isset($post['name']) ? htmlspecialchars($post['name']) : htmlspecialchars(Arr::get($holiday, 'name')); ?>" 
                       required>
                <?php if (isset($errors['name'])): ?>
                    <span class="help-block"><?php echo $errors['name']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group <?php echo isset($errors['date']) ? 'has-error' : ''; ?>">
               
<div class="form-group <?php echo isset($errors['date']) ? 'has-error' : ''; ?>">
    <label for="date"><?php echo __('Дата'); ?> *</label>
    <input type="date" class="form-control" id="date" name="date" 
           value="<?php echo isset($post['date']) ? htmlspecialchars($post['date']) : (isset($holiday) ? htmlspecialchars(Arr::get($holiday, 'date')) : ''); ?>" 
           required>
    <?php if (isset($errors['date'])): ?>
        <span class="help-block"><?php echo $errors['date']; ?></span>
    <?php endif; ?>
</div>
                <?php if (isset($errors['date'])): ?>
                    <span class="help-block"><?php echo $errors['date']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary"><?php echo __('Сохранить'); ?></button>
                <a href="<?php echo URL::site('holiday'); ?>" class="btn btn-default"><?php echo __('Отмена'); ?></a>
            </div>
        </form>
        
    </div>
</div>

<script type="text/javascript">
    $(function() {
        // Инициализация datetimepicker
        var datepicker = $('#datetimepicker').datetimepicker({
            language: 'ru',
            format: 'yyyy-mm-dd',
            weekStart: 1,
            autoclose: true,
            todayHighlight: true,
            minView: 2,
            startView: 2,
            forceParse: false
        });
        
        // Установка начального значения, если оно есть
        var initialDate = $('#date').val();
        if (initialDate && initialDate !== '0000-00-00') {
            var parts = initialDate.split('-');
            if (parts.length === 3) {
                var dateObj = new Date(parts[0], parts[1] - 1, parts[2]);
                datepicker.data('DateTimePicker').setDate(dateObj);
            }
        }
        
        // Дополнительная обработка для корректного отображения выбранной даты
        $('#datetimepicker').on('dp.change', function(e) {
            var selectedDate = e.date;
            if (selectedDate) {
                var year = selectedDate.getFullYear();
                var month = ('0' + (selectedDate.getMonth() + 1)).slice(-2);
                var day = ('0' + selectedDate.getDate()).slice(-2);
                $('#date').val(year + '-' + month + '-' + day);
            }
        });
    });
</script>