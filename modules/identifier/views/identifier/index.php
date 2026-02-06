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
        $current_date = date('Y-m-d');
        
        echo Form::input('event_date', $current_date, [
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
    
    <?php
    foreach ($options as $value => $label) {
        echo '<div class="radio mt-2">';
        echo '<label>';
        echo Form::radio('todo', $value, false, [
            'id' => 'todo_' . $value,
            'required' => 'required'
        ]);
        echo ' ' . htmlspecialchars($label);
        echo '</label>';
        echo '</div>';
    }
    ?>
    
    <div class="text-center mt-3">
        <?php
        echo Form::button('execute', 'Выполнить', [
            'type' => 'submit',
            'class' => 'btn btn-primary btn-lg'
        ]);
        ?>
    </div>

    <?php echo Form::close(); ?>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var datePicker = document.getElementById('event_date_picker');
    var today = new Date().toISOString().split('T')[0];
    
    // Устанавливаем максимальную дату
    datePicker.max = today;
    
    // Валидация при изменении
    datePicker.addEventListener('change', function() {
        if (this.value > today) {
            alert('Нельзя выбирать будущие даты!');
            this.value = today;
        }
    });
    
    // Находим форму и добавляем валидацию при отправке
    var form = datePicker.closest('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (datePicker.value > today) {
                e.preventDefault();
                alert('Ошибка: выбрана будущая дата. Пожалуйста, выберите текущую или прошедшую дату.');
                datePicker.value = today;
                datePicker.focus();
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