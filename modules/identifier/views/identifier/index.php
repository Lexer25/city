
<br>
<br>
<br>
<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title"><?php echo htmlspecialchars(__('Информация по идентификаторам') . ' ' . date('Y-m-d H:i:s')) ?></h3>
  </div>
  
  <div class="panel-body">
    <?php
    echo Form::open('identifier/action');
    ?>
        
    <div class="input-group mb-3">
        <label for="event_date_picker" class="w-100 mb-1">Дата события:</label>
        <?php
        // Определяем значение для поля event_date
        // Если в сессии есть event_date, используем его, иначе текущую дату
		
		
		$session_event_date = Cookie::get('session_event_date');
		
		
        if (isset($session_event_date) && !empty($session_event_date)) {
            $event_date_value = $session_event_date;
        } else {
            $event_date_value = date('Y-m-d');
        }
        
        // Обеспечиваем, что дата не больше текущей
		$current_date = date('Y-m-d');
        if ($event_date_value > $current_date) {
            $event_date_value = $current_date;
        }
        
		
		
		
		
        echo Form::input('event_date', $event_date_value, [
            'type' => 'date',
            'class' => 'form-control date-picker',
            'placeholder' => 'Выберите дату',
            'max' => $current_date,
            'id' => 'event_date_picker',
            'title' => 'Выберите дату не позднее сегодняшнего дня',
            'required' => 'required'
        ]);
        ?>
    </div>
    <small class="text-muted">Максимальная доступная дата: <?php echo htmlspecialchars(date('d.m.Y')); ?></small>
    
    <!-- Добавленное поле для количества строк -->
    <div class="input-group mt-3 mb-3">
        <label for="rows_per_page" class="w-100 mb-1">Количество строк на странице:</label>
        <?php
        // Определяем значение для поля rows_per_page
        //$rows_value = 50; // Значение по умолчанию
		
		$session_rows_per_page = Cookie::get('session_rows_per_page');
		
		 if (isset($session_rows_per_page) && !empty($session_rows_per_page)) {
            $rows_value = $session_rows_per_page;
        } else {
            $rows_value = 50;
        }
		
		
        
        if (isset($rows_per_page) && $rows_per_page !== '' && is_numeric($rows_per_page)) {
            $rows_value = (int)$rows_per_page;
            // Ограничиваем значение в допустимом диапазоне
            if ($rows_value < 1) $rows_value = 1;
            if ($rows_value > 500) $rows_value = 500;
        }
        
        echo Form::input('rows_per_page', $rows_value, [
            'type' => 'number',
            'class' => 'form-control',
            'id' => 'rows_per_page',
            'min' => '1',
            'max' => '500',
            'step' => '1',
            'placeholder' => 'Введите число от 1 до 500',
            'title' => 'Введите количество строк для отображения на странице',
            'required' => 'required'
        ]);
        ?>
    </div>
    <small class="text-muted">Допустимый диапазон: от 1 до 500</small>
	<?php
		echo '<br>';
		 echo Form::button('todo', 'Получить', [
            'type' => 'submit',
            'class' => 'btn btn-primary btn-lg'
        ]);
	?>
 <hr>

<?php
	echo Form::open('identifier/action');
	echo __('Список идентификаторов, не имеющих отметки о событиях');
		echo '<br>';
		 echo Form::button('todo', 'Получить', [
            'type' => 'submit',
            'class' => 'btn btn-primary btn-lg'
        ]);
	echo Form::close();
	
?> 
 <hr>   
 
<?php
	echo Form::open('identifier/action');
	echo __('Список всех идентификаторов с датой последнего события.');
	echo __('Список всех идентификаторов с датой последнего события.');
		echo '<br>';
		 echo Form::button('execute', 'Получить', [
            'type' => 'submit',
            'class' => 'btn btn-primary btn-lg'
        ]);
	echo Form::close();
	
?> 
 <hr>   
 
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var datePicker = document.getElementById('event_date_picker');
    var rowsPerPage = document.getElementById('rows_per_page');
    var today = new Date().toISOString().split('T')[0];
    
    // Устанавливаем максимальную дату
    datePicker.max = today;
    
    // Валидация даты при изменении
    datePicker.addEventListener('change', function() {
        if (this.value > today) {
            alert('Нельзя выбирать будущие даты!');
            this.value = today;
        }
    });
    
    // Валидация количества строк при изменении
    rowsPerPage.addEventListener('change', function() {
        var value = parseInt(this.value);
        if (isNaN(value) || value < 1) {
            this.value = 1;
        } else if (value > 500) {
            this.value = 500;
        }
    });
    
    rowsPerPage.addEventListener('input', function() {
        var value = parseInt(this.value);
        if (isNaN(value) || value < 1) {
            this.value = 1;
        } else if (value > 500) {
            this.value = 500;
        }
    });
    
    // Находим форму и добавляем валидацию при отправке
    var form = datePicker.closest('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Валидация даты
            if (datePicker.value > today) {
                e.preventDefault();
                alert('Ошибка: выбрана будущая дата. Пожалуйста, выберите текущую или прошедшую дату.');
                datePicker.value = today;
                datePicker.focus();
                return false;
            }
            
            // Валидация количества строк
            var rowsValue = parseInt(rowsPerPage.value);
            if (isNaN(rowsValue) || rowsValue < 1 || rowsValue > 500) {
                e.preventDefault();
                alert('Ошибка: количество строк должно быть числом от 1 до 500.');
                rowsPerPage.value = 50;
                rowsPerPage.focus();
                return false;
            }
            
            // Проверяем, выбран ли radio button
            var radioButtons = form.querySelectorAll('input[name="todo"]');
            var radioSelected = false;
            
            for (var i = 0; i < radioButtons.length; i++) {
                if (radioButtons[i].checked) {
                    radioSelected = true;
                    break;
                }
            }
            
            if (!radioSelected) {
                e.preventDefault();
                alert('Пожалуйста, выберите один из вариантов действия.');
                return false;
            }
        });
    }
});
</script>