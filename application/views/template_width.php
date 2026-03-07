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

    <!-- Bootstrap core CSS -->
    <!--<?php echo HTML::style('static/css/bootstrap.css'); ?>-->
    <?php echo HTML::style('static/css/modal.css'); ?>
    <?php echo HTML::style('static/css/city.css'); ?>
	
    
    <link rel="stylesheet" type="text/css" media="all" href="/city/static/css/theme.blue.css">
    <link rel="stylesheet" type="text/css" media="all" href="/city/static/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" media="all" href="/city/static/css/theme.default.min.css">
    <link rel="stylesheet" href="/city/static/css/themes/blue/style.css" type="text/css" media="print, projection, screen" />
    
    <!-- 5. Подключить CSS платформы Twitter Bootstrap 3 -->  
    <link rel="stylesheet" href="/city/static/css/bootstrap.min.css" />
    <!-- 6. Подключить CSS виджета "Bootstrap datetimepicker" -->  
    <link rel="stylesheet" href="/city/static/css/bootstrap-datetimepicker.min.css" />
</head>

<body>

        <div class="row">

		<span id="time-top"></span>
            <?php
            // Подготавливаем данные для меню
			$menu_data = array(
				'menu_active' => Arr::get($_SESSION, 'menu_active', ''), // Текущий активный пункт
				'config' => Kohana::$config->load('artonitcity_config'), // Конфигурация
				'logged_in' => Auth::instance()->logged_in(),            // Статус авторизации
				'user' => Auth::instance()->get_user(),                    // Данные пользователя
				'view_without_auth' => (array) $config->get('view_without_auth', array())// список пунктов меню без авторизации
			);
            
            // Безопасно подключаем меню через View::factory
            echo View::factory('top_menu', $menu_data)->render();
            
            // Основной контент
            echo $content;
            ?>
            <button onclick="topFunction()" id="myBtn" title="<?php echo __('top'); ?>">
                <?php echo __('top'); ?>
            </button>
        </div>

<span id="time-bottom" style="display:none;">Страница подготовлена за <?php echo round(microtime(TRUE) - START_TIME, 3); ?> сек.</span>
    <!-- JavaScript -->
    <script type="text/javascript" src="/city/static/js/jquery-2.2.4.js"></script>
    <script type="text/javascript" src="/city/static/js/moment-with-locales.min.js"></script>
    <script type="text/javascript" src="/city/static/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/city/static/js/bootstrap-datetimepicker.min.js"></script>
    <script type="text/javascript" src="/city/static/js/jquery.tablesorter.js"></script>
    <script type="text/javascript" src="/city/static/js/jquery.tablesorter.widgets.js"></script>
    <script type="text/javascript" src="/city/static/js/jquery.tablesorter.pager.js"></script>
    <script type="text/javascript" src="/city/static/js/crm2_template_tablesorter.js"></script>

    <script type="text/javascript">
    window.onscroll = function() {scrollFunction()};

    function scrollFunction() {
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            document.getElementById("myBtn").style.display = "block";
        } else {
            document.getElementById("myBtn").style.display = "none";
        }
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
</body>
</html>