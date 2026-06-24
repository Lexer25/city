<?php
/**
 * Страница управления привязкой серверов к типам
 * @var array $listLinks - список связей
 * @var array $listServers - список серверов
 * @var array $listTypes - список типов
 * @var bool $is_logged_in - флаг авторизации
 */
?>
<div class="row">
    <div class="col-md-12">
        <?php include Kohana::find_file('views', 'alert_line'); ?>
        
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo __('Привязка транспортных серверов к типам'); ?></h3>
            </div>
            <div class="panel-body">
                <?php echo View::factory('ts/_table_links', array(
                    'listLinks' => $listLinks,
                    'is_logged_in' => $is_logged_in
                )); ?>
                
                <?php if ($is_logged_in): ?>
                    <hr>
                    <?php echo View::factory('ts/_form_add_link', array(
                        'listServers' => $listServers,
                        'listTypes' => $listTypes
                    )); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>