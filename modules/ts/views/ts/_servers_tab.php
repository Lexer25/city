<?php
/**
 * Вкладка управления серверами
 */
$ts = Model::factory('tss');
$listTS = $ts->get_list_servers_only();
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Список транспортных серверов'); ?></h3>
    </div>
    <div class="panel-body">
        <?php echo View::factory('ts/_table_servers_simple', array(
            'listTS' => $listTS,
            'is_logged_in' => $is_logged_in
        )); ?>
        
        <?php if ($is_logged_in): ?>
            <?php echo View::factory('ts/_form_add_server_simple'); ?>
        <?php endif; ?>
    </div>
</div>