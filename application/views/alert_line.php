<?php
//echo Debug::vars('2', $_SESSION);

// CSS стили для alert-danger (красные ошибки) и alert-success
?>
<style>
.alert {
  padding: 15px;
  margin-bottom: 20px;
  border: 1px solid transparent;
  border-radius: 4px;
  text-shadow: 0 1px 0 rgba(255, 255, 255, 0.2);
  -webkit-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.25), 0 1px 2px rgba(0, 0, 0, 0.05);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.25), 0 1px 2px rgba(0, 0, 0, 0.05);
}

.alert-danger {
  background-image: -webkit-linear-gradient(top, #f2dede 0%, #e7c3c3 100%);
  background-image: linear-gradient(to bottom, #f2dede 0%, #e7c3c3 100%);
  background-repeat: repeat-x;
  border-color: #dca7a7;
  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#fff2dede', endColorstr='#ffe7c3c3', GradientType=0);
}

.alert-success {
  background-image: -webkit-linear-gradient(top, #dff0d8 0%, #c8e5bc 100%);
  background-image: linear-gradient(to bottom, #dff0d8 0%, #c8e5bc 100%);
  background-repeat: repeat-x;
  border-color: #b2dba1;
  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffdff0d8', endColorstr='#ffc8e5bc', GradientType=0);
}

.alert-dismissible {
  padding-right: 35px;
}

.close {
  float: right;
  font-size: 21px;
  font-weight: bold;
  line-height: 1;
  color: #000;
  text-shadow: 0 1px 0 #fff;
  opacity: 0.2;
  filter: alpha(opacity=20);
}

.close:hover,
.close:focus {
  color: #000;
  text-decoration: none;
  cursor: pointer;
  opacity: 0.5;
  filter: alpha(opacity=50);
}
</style>
<?php

// Проверка и отображение сообщений об ошибках
$error_message = Session::instance()->get('e_mess');
if (!empty($error_message)) {
    include Kohana::find_file('views', 'alertState');
    
    $param = '';
    
    // Обработка разных форматов данных в сессии
    if (is_array($error_message)) {
        if (isset($error_message['result'])) {
            // Если данные в формате array('result' => 'текст')
            $param = $error_message['result'];
        } else {
            // Если это простой массив с сообщениями
            foreach($error_message as $key => $value) {
                $param .= $value . '<br>';
            }
        }
    } else {
        // Если это строка
        $param = (string)$error_message;
    }
    
    if (!empty($param)) {
        ?>
        <div id="error-alert" class="alert alert-danger alert-dismissible" role="alert">
            <?php echo $param; ?>
            <!-- <button type="button" class="close" data-dismiss="alert" aria-label="Закрыть">
                <span aria-hidden="true">&times;</span>
            </button> -->
        </div>
        <?php
    }
}
Session::instance()->delete('e_mess');

// Проверка и отображение сообщений об успехе
$ok_mess = Validation::Factory(Session::instance()->as_array())
    ->rule('ok_mess','is_array')
    ->rule('ok_mess','not_empty');
    
if($ok_mess->check()) {
    include Kohana::find_file('views', 'alertState');
    
    $param = '';
    foreach(Arr::get($ok_mess, 'ok_mess') as $key => $value) {
        $param .= $value . '<br>';
    }
    ?>
    <div id="success-alert" class="alert alert-success alert-dismissible" role="alert">
        <?php echo $param; ?>
        <!-- <button type="button" class="close" data-dismiss="alert" aria-label="Закрыть">
            <span aria-hidden="true">&times;</span>
        </button> -->
    </div>
    <?php
}
Session::instance()->delete('ok_mess');
?>

<script>
$(function(){
    // Автоматическое скрытие через 5 секунд
    window.setTimeout(function(){
        $('#error-alert, #success-alert').fadeOut('slow', function(){
            $(this).remove();
        });
    }, 5000);
    
    // Скрытие при клике на alert-danger или alert-success
    $('#error-alert, #success-alert').on('click', function(){
        $(this).fadeOut('slow', function(){
            $(this).remove();
        });
    });

    // Обработчик для кнопки закрытия
    $('.close').on('click', function(){
        $(this).closest('.alert').fadeOut('slow', function(){
            $(this).remove();
        });
    });
});
</script>