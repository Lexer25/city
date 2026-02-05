<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title"><?php echo __('Информация по идентификаторам').' '.date('Y-m-d H:i:s')?></h3>
  </div>
  
  <div class="panel-body">

    <?php
    $t1 = microtime(true);
    echo Form::open('identifier/action');
    ?>

    <!-- Первый fieldset: фильтр по дате -->
    <fieldset class="well">
        <legend>Фильтр по дате событий</legend>
        
        <div class="input-group mb-3">
            <?php
            $current_date = date('Y-m-d');
            
            echo Form::input('event_date', $current_date, [
                'type' => 'date',
                'class' => 'form-control date-picker',
                'placeholder' => 'Выберите дату',
                'max' => $current_date,
                'id' => 'event_date_picker',
                'onfocus' => 'this.max=new Date().toISOString().split("T")[0]'
            ]);
            ?>
            <div class="input-group-append">
                <?php
                echo Form::button('todo', 'Идентификаторы без событий до указанной даты', [
                    'type' => 'submit',
                    'class' => 'btn btn-info',
                    'name' => 'cardNoEvent',
                    'value' => 'cardNoEventDate',
                    'id' => 'dateSubmitBtn'
                ]);
                ?>
            </div>
        </div>
        <small class="text-muted">Максимальная доступная дата: <?php echo date('d.m.Y'); ?></small>
        
       
    </fieldset>
	<?php
	echo Form::close();
	echo Form::open('identifier/action');
	?>

    <!-- Второй fieldset: выбор действия -->
    <fieldset class="well mt-4">
        <legend>Действия с идентификаторами</legend>
        
        <?php
       
        foreach ($options as $value => $label) {
            echo '<div class="radio">';
            echo '<label>';
            echo Form::radio('todo', $value, false, ['id' => $value]);
            echo ' ' . $label;
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
    </fieldset>

    <?php echo Form::close(); ?>

  </div>
</div>
 <!-- JavaScript для дополнительной проверки -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var datePicker = document.getElementById('event_date_picker');
            var submitBtn = document.getElementById('dateSubmitBtn');
            
            // Устанавливаем максимальную дату как текущую
            var today = new Date().toISOString().split('T')[0];
            datePicker.max = today;
            
            // Проверка при выборе даты
            datePicker.addEventListener('change', function() {
                var selectedDate = new Date(this.value);
                var currentDate = new Date();
                currentDate.setHours(0,0,0,0);
                
                if (selectedDate > currentDate) {
                    alert('Нельзя выбирать будущие даты!');
                    this.value = today;
                }
            });
            
            // Проверка при отправке
           submitBtn.addEventListener('click', function(e) {
    var selectedDate = new Date(datePicker.value);
    var currentDate = new Date();
    
    // Убираем время у обеих дат для корректного сравнения
    selectedDate.setHours(0, 0, 0, 0);
    currentDate.setHours(0, 0, 0, 0);
    
    if (selectedDate > currentDate) {
        e.preventDefault();
        alert('Ошибка: выбрана будущая дата. Пожалуйста, выберите текущую или прошедшую дату.');
        datePicker.focus();
        return false;
    }
});
        });
        </script>