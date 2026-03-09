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

    <!-- CSS - ТОЛЬКО НЕОБХОДИМОЕ -->
    <link rel="stylesheet" href="/city/static/css/bootstrap.min.css">
    <link rel="stylesheet" href="/city/static/css/bootstrap-datetimepicker.min.css">
    <link rel="stylesheet" href="/city/static/css/modal.css">
    <link rel="stylesheet" href="/city/static/css/city.css">
    
    <!-- Tablesorter theme (только один) -->
    <link rel="stylesheet" href="/city/static/css/theme.blue.css">
    
    <!-- JavaScript в HEAD (только критический) -->
    <script src="/city/static/js/jquery-2.2.4.js"></script>
</head>

<body>
	<!-- В template.php -->
	<?php if (isset($full_width) && $full_width): ?>
		<!-- Широкий режим -->
		<div class="container-fluid">
			<div class="row">
				 <?php
            // Подготовка данных для меню
            $menu_data = array(
                'menu_active' => Arr::get($_SESSION, 'menu_active', ''),
                'config' => $config,  // Используем уже загруженный конфиг
                'logged_in' => Auth::instance()->logged_in(),
                'user' => Auth::instance()->get_user(),
                'view_without_auth' => (array) $config->get('view_without_auth', array())
            );
            
            echo View::factory('top_menu', $menu_data)->render();
            echo $content;
            ?>
            
            <button onclick="topFunction()" id="myBtn" title="<?php echo __('top'); ?>">
                <?php echo __('top'); ?>
            </button>
			
			</div>
		</div>
	<?php else: ?>
		<!-- Обычный режим -->
		<div class="container">
			<div class="row">
				 <?php
            // Подготовка данных для меню
            $menu_data = array(
                'menu_active' => Arr::get($_SESSION, 'menu_active', ''),
                'config' => $config,  // Используем уже загруженный конфиг
                'logged_in' => Auth::instance()->logged_in(),
                'user' => Auth::instance()->get_user(),
                'view_without_auth' => (array) $config->get('view_without_auth', array())
            );
            
            echo View::factory('top_menu', $menu_data)->render();
            echo $content;
            ?>
            
            <button onclick="topFunction()" id="myBtn" title="<?php echo __('top'); ?>">
                <?php echo __('top'); ?>
            </button>
			</div>
		</div>
	<?php endif; ?>
   
    
    <span id="time-bottom" style="display:none;">Страница подготовлена за <?php echo round(microtime(TRUE) - START_TIME, 3); ?> сек.</span>

    <!-- JavaScript в конце body (для скорости загрузки) -->
    <script src="/city/static/js/moment-with-locales.min.js"></script>
    <script src="/city/static/js/bootstrap.min.js"></script>
    <script src="/city/static/js/bootstrap-datetimepicker.min.js"></script>
    <script src="/city/static/js/jquery.tablesorter.js"></script>
    <script src="/city/static/js/jquery.tablesorter.widgets.js"></script>
    <script src="/city/static/js/crm2_template_tablesorter.js"></script>
    
    <!-- Опционально: tablesorter.pager только если нужна пагинация -->
    <?php if (isset($use_pager) && $use_pager): ?>
        <script src="/city/static/js/jquery.tablesorter.pager.js"></script>
    <?php endif; ?>

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
</body>
</html>