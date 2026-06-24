<?php
/**
 * Представление списка транспортных серверов
 * @var array $listTS - список серверов
 * @var array $listTsType - список типов серверов
 * @var bool $is_logged_in - флаг авторизации
 */
?>

<?php 
	include Kohana::find_file('views', 'alert_line');
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Список зарегистрированных транспортных серверов'); ?></h3>
    </div>
    <div class="panel-body">
        
        <!-- Таблица серверов -->
        <?php echo View::factory('ts/_table_servers', array(
            'listTS' => $listTS,
            'is_logged_in' => $is_logged_in
        )); ?>
        
        <!-- Форма добавления -->
        <?php if ($is_logged_in): ?>
            <?php echo View::factory('ts/_form_add_server'); ?>
        <?php endif; ?>
        
    </div>
</div>

<!-- Блок типов серверов -->
<?php echo View::factory('ts/_table_types', array(
    'listTsType' => $listTsType,
    'is_logged_in' => $is_logged_in
)); ?>