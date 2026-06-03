<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<!-- Static navbar -->
<nav class="navbar navbar-default navbar-fixed-top disable" role="navigation">
    <div class="container-fluid">
	<div class="navbar-collapse collapse">

  
			 <?php // В любом представлении (view)
			 
	echo Menu_Renderer::render('nav navbar-nav');
	?>
	</div>
       <!-- В шаблоне -->
  
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

        <!-- Версия и время -->
        <div >
            <?php 
            // подсветка версии в течении 3 суток после обновления.
            // если дата обновления отсутствует, то выводится только версия, без даты обновления
            
            $color = null;
            $lightVerDay = 3;
            
            if (!empty($config->timeUpdate)) {
                $lightVerDay = Arr::get($config, 'lightVerDay', 3);
                
                $current_date = new DateTime();
                try {
                    $update_date = new DateTime($config->timeUpdate);
                } catch (Exception $e) {
                    Kohana::$log->add(Log::ERROR, 'Invalid date format in config: :date', [
                        ':date' => $config->timeUpdate
                    ]);
                    echo __('Версия :ver', array(':ver' => $config->ver));
                    return;
                }

                $interval = $current_date->diff($update_date);
                $days_diff = $interval->days;
                
                if ($days_diff < $lightVerDay) {
                    $color = 'label-success';
                    echo __('<span class="label :color">Версия :ver обновление :timeUpdate</span>', array(
                        ':ver' => $config->ver,
                        ':timeUpdate' => $config->timeUpdate,
                        ':color' => $color,
                    ));
                } else {
                    echo __('Версия :ver обновление :timeUpdate', array(
                        ':ver' => $config->ver,
                        ':timeUpdate' => $config->timeUpdate,
                    ));
                }
            } else {        
                echo __('Версия :ver', array(
                    ':ver' => $config->ver,
                ));
            }
            
            echo '<br>';
            echo __('timerefresh', array ('tr' => date("d.m.Y H:i", time())));
            ?>
        </div>
    </div>
	
</nav>
<style>
/* В city.css добавьте в самый конец */

/* Ограничиваем ширину меню и добавляем прокрутку */
@media (max-width: 1400px) {
    .navbar-collapse {
        overflow-x: auto;
        overflow-y: visible;
        white-space: nowrap;
    }
    
    .navbar-nav {
        display: inline-block;
        float: none;
        white-space: nowrap;
    }
    
    .navbar-nav > li {
        display: inline-block;
        float: none;
    }
    
    /* Стили для скроллбара */
    .navbar-collapse::-webkit-scrollbar {
        height: 3px;
    }
    
    .navbar-collapse::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .navbar-collapse::-webkit-scrollbar-thumb {
        background: #888;
    }
}

/* Компактный режим для всех экранов */
.navbar-nav > li > a {
    padding: 15px 10px !important;
    font-size: 13px !important;
}

/* Скрываем иконки везде для экономии места */
.navbar-nav > li > a > i,
.navbar-nav > li > a > [class^="fa-"] {
    display: none !important;
}

/* Убираем лишние отступы */
.container-fluid {
    padding-left: 10px;
    padding-right: 10px;
}
</style>


