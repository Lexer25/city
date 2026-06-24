<?php
/**
 * Страница управления транспортными серверами
 * @var array $listTS - список серверов
 * @var bool $is_logged_in - флаг авторизации
 */
?>
<div class="row">
    <div class="col-md-12">
        <?php include Kohana::find_file('views', 'alert_line'); ?>
        
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo __('Управление транспортными серверами'); ?></h3>
            </div>
            <div class="panel-body">
                <?php echo View::factory('ts/_table_servers_simple', array(
                    'listTS' => $listTS,
                    'is_logged_in' => $is_logged_in
                )); ?>
                
                <?php if ($is_logged_in): ?>
                    <hr>
                    <?php echo View::factory('ts/_form_add_server_simple'); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>