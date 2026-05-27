<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Artonit City - панель управления СКУД Артонит">
    <meta name="author" content="www.artonit.ru">
    <link rel="shortcut icon" href="/city/favicon.ico">

    <title>Artonit City 
    <?php 
    $config = Kohana::$config->load('artonitcity_config');
    $city_name = Arr::get($config, 'city_name', '');
    $page_title = isset($title) ? $title : '';
    
    if ($city_name) {
        echo HTML::chars($city_name);
    }
    if ($page_title) {
        echo ' - ' . HTML::chars($page_title);
    }
    ?>
    </title>

 <!-- CSS -->
    <link rel="stylesheet" href="/city/static/css/bootstrap.min.css">
    <link rel="stylesheet" href="/city/static/css/bootstrap-datetimepicker.min.css">
    <link rel="stylesheet" href="/city/static/css/modal.css">
    <link rel="stylesheet" href="/city/static/css/city.css">
   
    <link rel="stylesheet" href="/city/static/css/2.31.3/theme.blue.min.css">
    
    <!-- JavaScript библиотеки -->
    <script src="/city/static/js/jquery-2.2.4.js"></script>
    <script src="/city/static/js/moment-with-locales.min.js"></script>
    <script src="/city/static/js/bootstrap.min.js"></script>
    <script src="/city/static/js/bootstrap-datetimepicker.min.js"></script>
   

<!-- В head или перед закрывающим body -->
<link rel="stylesheet" href="/city/static/css/2.31.3/jquery.tablesorter.pager.min.css">

<script src="/city/static/js/2.31.3/jquery.tablesorter.min.js"></script>
<script src="/city/static/js/2.31.3/jquery.tablesorter.widgets.min.js"></script>
<script src="/city/static/js/2.31.3/jquery.tablesorter.pager.min.js"></script>


</head>

<body>
<?php
// В начале файла template.php (после открытия тега body)
// Получаем flash-сообщение из сессии
$flash_message = Session::instance()->get('flash_message');
if ($flash_message) {
    // Удаляем сообщение из сессии, чтобы оно не отображалось повторно
    Session::instance()->delete('flash_message');
    
    $type = Arr::get($flash_message, 'type', 'info');
    $text = Arr::get($flash_message, 'text', '');
    
    // Определяем класс Bootstrap alert в зависимости от типа
    $alert_class = '';
    switch ($type) {
        case 'success':
            $alert_class = 'alert-success';
            break;
        case 'error':
            $alert_class = 'alert-danger';
            break;
        case 'warning':
            $alert_class = 'alert-warning';
            break;
        case 'info':
        default:
            $alert_class = 'alert-info';
            break;
    }
    
    // Выводим сообщение
    echo '<div class="alert ' . $alert_class . ' alert-dismissible fade in" role="alert">';
    echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
    echo '<span aria-hidden="true">&times;</span>';
    echo '</button>';
    echo htmlspecialchars($text);
    echo '</div>';
}
?>

	<!-- В template.php установка ширины страницы-->
	<!-- для вывода страницы во всю ширину в контроллере необходимо указать $this->template->full_width = true; -->
	<?php
// Определяем класс контейнера в зависимости от режима
$container_class = (isset($full_width) && $full_width) ? 'container-fluid' : 'container';
?>

<div class="<?php echo $container_class; ?>">
    <span id="time-top"></span>
    
        <?php
        // Подготовка данных для меню (код не меняется)
        $menu_data = array(
            'menu_active' => Arr::get($_SESSION, 'menu_active', ''),
            'config' => $config,
            'logged_in' => Auth::instance()->logged_in(),
            'user' => Auth::instance()->get_user(),
            'view_without_auth' => (array) $config->get('view_without_auth', array())
        );
        
        echo View::factory('top_menu', $menu_data)->render();
		?>
		

			<?php			
				echo $content;
			?>
 
        <button onclick="topFunction()" id="myBtn" title="<?php echo __('top'); ?>">
            <?php echo __('top'); ?>
        </button>

   
</div>

<!-- Остальная часть шаблона (таймеры и скрипты) остаётся без изменений -->
<span id="time-bottom" style="display:none;">Страница подготовлена за <?php echo round(microtime(TRUE) - START_TIME, 3); ?> сек.</span>
    
    <script>
    window.onscroll = function() {scrollFunction()};

    function scrollFunction() {
        var btn = document.getElementById("myBtn");
        if (!btn) return;
        btn.style.display = (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) 
            ? "block" : "none";
    }

    function topFunction() {
        document.body.scrollTop = 0;
        document.documentElement.scrollTop = 0;
    }

    document.addEventListener('DOMContentLoaded', function() {
        var bottomTime = document.getElementById('time-bottom');
        var topTime = document.getElementById('time-top');
        if (bottomTime && topTime) {
            topTime.textContent = bottomTime.textContent;
        }
    });
    </script>

<script>
$(document).ready(function() {
    $('.dropdown-toggle').dropdown();
});
</script>

<style>
/* В вашем CSS файле */
.nav {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;  /* Горизонтальное расположение */
    gap: 20px;      /* Расстояние между пунктами */
}

.nav li {
    display: inline-block;  /* Альтернатива для старых браузеров */
}

/* Или используйте float (старый способ) */
.nav li {
    float: left;
    margin-right: 20px;
}

.nav:after {
    content: "";
    display: table;
    clear: both;
}
</style>
</body>
</html>