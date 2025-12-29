<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Artonit City <?echo  isset(Kohana::$config->load('artonitcity_config')->city_name)? Kohana::$config->load('artonitcity_config')->city_name : '';?></title>

    <!-- Bootstrap core CSS -->
    <?= HTML::style('static/css/bootstrap.css'); ?>
	<?= HTML::style('static/css/modal.css'); ?>

	<?= HTML::style('static/css/city.css'); ?>
	<?//= HTML::style('static/css/modal.css'); ?>
	<link rel="stylesheet" href="/city/static/css/themes/blue/style.css" type="text/css" media="print, projection, screen" />
	 
<!-- ... -->
  <!-- 1. Подключить библиотеку jQuery -->
  <!-- <script type="text/javascript" src="/city/static/js/jquery-1.11.1.min.js"></script>  --> 
   <script type="text/javascript" src="/city/static/js/jquery-2.2.4.js"></script>
  
 
  <!-- 3. Подключить скрипт платформы Twitter Bootstrap 3 -->
  <script type="text/javascript" src="/city/static/js/bootstrap.min.js"></script>
 

 
  
  
  
    
  
  </head>
    <body>
		<div class="container">
				<!-- Static navbar -->

			<div class="panel panel-primary">
			  <div class="panel-heading">
				<h3 class="panel-title"><?echo __('err_mess')?></h3>
			  </div>
			  <div class="panel-body">
				

		<span class="type">
			<?php //echo __('У нас ошибка... не волнуйтесь! ').'<br>'; 
			echo __('Класс ошибки ').$class.'<br>';
			
			switch ($class){
				
				case 'Database_Exception':
					echo __('Ошибка базы данных.');
					switch ($code){
						case 0;
							echo __('Не могу найти источник данных либо в SQL запросе нехватает данных').'<br>'; 
							if(isset(Kohana::$config->load('database')->fb)){
								 echo __('dns: :db<br> PHP: :phpver, <br> :fver', array(
										':db'=> Arr::get(
											Arr::get(
													Kohana::$config->load('database')->fb,
													'connection' 
													),
										'dsn'),
										'ver'=> Kohana::$config->load('artonitcity_config')->ver,
										'developer'=> Kohana::$config->load('artonitcity_config')->developer,
										':phpver'=>phpversion(),
										':fver'=>Kohana::version(),
										)).'<br>';
									} else {
									echo __('No_db_config');
									}
													break;
						case '42S22';
							echo __('Таблицы базы данных не соответствуют CityCRM').'<br>'; 
						break;
						default:
							echo __('Код этой ошибки пока не встречался '. $code);
						break;
						
					}
					echo '<br>';
					//echo __('Текст ошибки ').htmlspecialchars( (string) $message, ENT_QUOTES | ENT_IGNORE, Kohana::$charset, TRUE).'<br>';
				break;
				
				case 'Kohana_HTTP_Exception':
					echo __('что-то у нас с HTML неправильно...');
				break;
				
				default:
					//echo __('Этот класс ошибок пока не имеет описания.');
					
				break;
				
				
			}
			echo '<br>';
			echo '<br>';
			echo '<br>';
			echo '<br>';
			echo __('Код ошибки ').$code.'<br>';
			echo __('Текст ошибки ').htmlspecialchars( (string) $message, ENT_QUOTES | ENT_IGNORE, Kohana::$charset, TRUE).'<br>';
			echo __('Информация об ошибке записана в лог-файл и будет доступна разработчику').'<br>';
			
			?> 
			<br>
			<br>
			<br>
			<br>
			<!---
				[ <?php echo $code ?> ]:</span> <span class="message">
					<?php echo htmlspecialchars( (string) $message, ENT_QUOTES | ENT_IGNORE, Kohana::$charset, TRUE); ?>
					-->
		</span>

				
			  </div>
			</div>
		</div>
		<div id="kohana_error">

	
	
</div>
	
  </body>
</html>
