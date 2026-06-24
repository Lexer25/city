<?php
/**
 * Вкладка управления привязкой серверов к типам
 */
$ts = Model::factory('tss');
$listLinks = $ts->get_links_list();
$listServers = $ts->get_list_servers_only();
$listTypes = $ts->get_list_type();
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Привязка серверов к типам'); ?></h3>
    </div>
    <div class="panel-body">
        <?php echo View::factory('ts/_table_links', array(
            'listLinks' => $listLinks,
            'is_logged_in' => $is_logged_in
        )); ?>
        
        <?php if ($is_logged_in): ?>
            <?php echo View::factory('ts/_form_add_link', array(
                'listServers' => $listServers,
                'listTypes' => $listTypes
            )); ?>
        <?php endif; ?>
    </div>
</div>