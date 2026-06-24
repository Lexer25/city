<?php
/**
 * Вкладка управления типами
 */
$ts = Model::factory('tss');
$listTypes = $ts->get_list_type();
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Список типов транспортных серверов'); ?></h3>
    </div>
    <div class="panel-body">
        <?php echo View::factory('ts/_table_types', array(
            'listTypes' => $listTypes,
            'is_logged_in' => $is_logged_in
        )); ?>
        
        <?php if ($is_logged_in): ?>
            <?php echo View::factory('ts/_form_add_type'); ?>
        <?php endif; ?>
    </div>
</div>