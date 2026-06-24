<?php
/**
 * Главная страница с тремя вкладками
 * @var bool $is_logged_in
 */
?>
<div class="row">
    <div class="col-md-12">
        <?php include Kohana::find_file('views', 'alert_line'); ?>
        
        <ul class="nav nav-tabs" role="tablist">
            <li class="active">
                <a href="#types" role="tab" data-toggle="tab">
                    <span class="glyphicon glyphicon-tags"></span> 
                    <?php echo __('Типы ТС'); ?>
                </a>
            </li>
            <li>
                <a href="#servers" role="tab" data-toggle="tab">
                    <span class="glyphicon glyphicon-hdd"></span> 
                    <?php echo __('Сервера ТС'); ?>
                </a>
            </li>
            <li>
                <a href="#links" role="tab" data-toggle="tab">
                    <span class="glyphicon glyphicon-link"></span> 
                    <?php echo __('Привязка ТС к типам'); ?>
                </a>
            </li>
        </ul>
        
        <div class="tab-content">
            <!-- Вкладка 1: Типы -->
            <div class="tab-pane fade in active" id="types">
                <?php 
                echo View::factory('ts/_types_tab', array(
                    'is_logged_in' => $is_logged_in
                )); 
                ?>
            </div>
            
            <!-- Вкладка 2: Серверы -->
            <div class="tab-pane fade" id="servers">
                <?php 
                echo View::factory('ts/_servers_tab', array(
                    'is_logged_in' => $is_logged_in
                )); 
                ?>
            </div>
            
            <!-- Вкладка 3: Привязка -->
            <div class="tab-pane fade" id="links">
                <?php 
                echo View::factory('ts/_links_tab', array(
                    'is_logged_in' => $is_logged_in
                )); 
                ?>
            </div>
        </div>
    </div>
</div>