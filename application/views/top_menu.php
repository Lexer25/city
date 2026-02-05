<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<!-- Static navbar -->
<nav class="navbar navbar-default navbar-fixed-top disable" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only"><?php echo HTML::chars(__('Toggle navigation')) ?></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <?php echo HTML::anchor('dashboard', HTML::chars(__('City')), array('class' => 'navbar-brand')) ?>
        </div>
        
        <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <?php
                // ВЫНЕСЕНО ИЗ ЦИКЛА: конфигурация загружается один раз
                $config = Kohana::$config->load('artonitcity_config');
                $view_without_auth = (array) $config->get('view_without_auth', array());
                $logged_in = Auth::instance()->logged_in();
                $menu_active = Arr::get($_SESSION, 'menu_active', '');
                
                // Массив пунктов меню вынесен в отдельную переменную для ясности
                $menu_items = array(
                    'load' => array(
                        'title' => __('Load'),
                        'url' => 'dev/load',
                        'condition' => (isset($view_without_auth['load']) && $view_without_auth['load']) ? true : $logged_in
                    ),
                    'load_order' => array(
                        'title' => __('Load_order'),
                        'url' => 'dev/load_order',
                        'condition' => (isset($view_without_auth['load_order']) && $view_without_auth['load_order']) ? true : $logged_in
                    ),
                    'device_control' => array(
                        'title' => __('device_control'),
                        'url' => 'dev/device_control',
                        'condition' => (isset($view_without_auth['device_control']) && $view_without_auth['device_control']) ? true : $logged_in
                    ),
                    'events' => array(
                        'title' => __('events'),
                        'url' => 'event',
                        'condition' => (isset($view_without_auth['events']) && $view_without_auth['events']) ? true : $logged_in
                    ),
                    'people' => array(
                        'title' => __('people'),
                        'url' => 'people/peopleInfo',
                        'condition' => (isset($view_without_auth['people']) && $view_without_auth['people']) ? true : $logged_in
                    ),
                    'door' => array(
                        'title' => __('door'),
                        'url' => 'door/doorInfo',
                        'condition' => (isset($view_without_auth['door']) && $view_without_auth['door']) ? true : $logged_in
                    ),
                    'log' => array(
                        'title' => __('log'),
                        'url' => 'dashboard/log',
                        'condition' => (isset($view_without_auth['log']) && $view_without_auth['log']) ? true : $logged_in
                    ),
                    'identifier' => array(
                        'title' => __('identifier'),
                        'url' => 'identifier',
                        'condition' => $logged_in
                    ),
                    'services' => array(
                        'title' => __('services'),
                        'url' => 'dashboard/services',
                        'condition' => true
                    ),
                    'skud' => array(
                        'title' => __('сводная'),
                        'url' => 'skud',
                        'condition' => true
                    ),
					
					'eximdata' => array(
                        'title' => __('Экспорт/импорт'),
                        'url' => 'eximdata',
                        'condition' => true
                    ),
					
					'apb' => array(
                        'title' => __('АПБ'),
                        'url' => 'apb',
                        'condition' => true
                    )
					
					
					
					
                );
                
                // Вывод основных пунктов меню с безопасным экранированием
                foreach ($menu_items as $key => $item) {
                    // Более читаемое условие
                    $is_visible = false;
                    if (isset($item['condition'])) {
                        if (is_bool($item['condition'])) {
                            $is_visible = $item['condition'];
                        } elseif (isset($view_without_auth[$key])) {
                            $is_visible = (bool)$view_without_auth[$key];
                        } else {
                            $is_visible = $logged_in;
                        }
                    }
                    
                    if ($is_visible) {
                        $active_class = ($menu_active == $key) ? ' class="active"' : '';
                        $url = $item['url'];
                        $title = HTML::chars($item['title']);
                        echo '<li' . $active_class . '>' . HTML::anchor($url, $title) . '</li>';
						
						
                    }
                }
                
               
       
                ?>
            </ul>
            
            <!-- Правая часть меню (авторизация) -->
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <?php if (Auth::instance()->logged_in()): ?>
                       <div class="navbar-text" style="padding-right: 15px;">
							<span class="glyphicon glyphicon-user" style="margin-right: 5px;"></span>
							<span style="display: inline-block; margin-right: 10px; vertical-align: middle;">
								<?php echo HTML::chars(Auth::instance()->get_user()) ?>
							</span>
							<span style="display: inline-block; vertical-align: middle;">
								<?php echo HTML::anchor(
									'logout', 
									HTML::chars(__('logout')), 
									array(
										'class' => 'btn btn-xs btn-default',
										'onclick' => 'return confirm(\'' . HTML::chars(__('confirm.delete')) . '\')'
									)
								) ?>
							</span>
						</div>
                    <?php else: ?>
                        <?php echo Form::open('dashboard', array('method' => 'post', 'class' => 'navbar-form form-inline')) ?>
                            <?php 
                            // Добавляем CSRF защиту если она включена в Kohana
							if (class_exists('Security') && method_exists('Security', 'token')) {
                                echo Form::hidden('csrf', Security::token());
                            }
                            ?>
                            <div class="form-group">
                                <label for="inputUsername" class="sr-only"><?php echo HTML::chars(__('Username')) ?></label>
                                <input type="text" class="form-control input-sm" id="inputUsername" 
                                       placeholder="<?php echo HTML::chars(__('Username')) ?>" 
                                       name="username"
                                       value="<?php echo HTML::chars(Arr::get($_POST, 'username', '')) ?>"
                                       required>
                            </div>
                            <div class="form-group">    
                                <label for="inputPassword" class="sr-only"><?php echo HTML::chars(__('Password')) ?></label>
                                <input type="password" class="form-control input-sm" id="inputPassword" 
                                       placeholder="<?php echo HTML::chars(__('Password')) ?>" 
                                       name="password"
                                       required>
                            </div>
                            <div class="checkbox input-sm">
                                <label>
                                    <input type="checkbox" name="remember" 
                                           <?php echo (Arr::get($_POST, 'remember') ? 'checked' : '') ?>>
                                    <?php echo HTML::chars(__('Remember me')) ?>
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <span class="glyphicon glyphicon-log-in"></span> 
                                <?php echo HTML::chars(__('Login')) ?>
                            </button>
                        <?php echo Form::close() ?>
                        <?php
                        // Отображение ошибок входа (если есть)
                        $errors = Session::instance()->get_once('login_errors', array());
                        if (!empty($errors)) {
                            echo '<div class="alert alert-danger alert-dismissible" style="margin-top: 5px;">';
                            echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
                            echo '<span aria-hidden="true">&times;</span>';
                            echo '</button>';
                            foreach ($errors as $error) {
                                echo '<p style="margin: 0;">' . HTML::chars($error) . '</p>';
                            }
                            echo '</div>';
                        }
                        ?>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
 
			<!-- Версия и время - с отступом сверху -->
			<div style="margin-top: 10px; padding-left: 15px; font-size: 12px; line-height: 1.2;">
				<?php
				// Отображение версии
				if (class_exists('Version')) {
					echo Version::render_full() . '<br>';
				}
				
				// Время обновления
				echo HTML::chars(__('timerefresh', array('tr' => date("d.m.Y H:i", time()))));
				?>
			</div>
    </div>
</nav>