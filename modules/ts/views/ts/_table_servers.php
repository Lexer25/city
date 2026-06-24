<?php
/**
 * Таблица транспортных серверов
 * @var array $listTS
 * @var bool $is_logged_in
 */
?>
<?php echo Form::open('ts/control', array('method' => 'POST')); ?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Список транспортных серверов'); ?></h3>
    </div>
    <div class="panel-body">
        <?php if (empty($listTS)): ?>
            <div class="alert alert-info">
                <?php echo __('Нет зарегистрированных транспортных серверов'); ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-condensed">
                    <thead>
                        <tr>
                            <th width="30"><?php echo __('№'); ?></th>
                            <th><?php echo __('ID_SERVER'); ?></th>
                            <th><?php echo __('NAME'); ?></th>
                            <th><?php echo __('IP'); ?></th>
                            <th><?php echo __('PORT'); ?></th>
                            <th><?php echo __('ACTIVE'); ?></th>
                            <th><?php echo __('NAMETYPE'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listTS as $index => $server): ?>
                            <tr>
                                <td>
                                    <?php if ($is_logged_in): ?>
                                        <?php echo Form::radio('id', $server['ID_SERVER'], $index === 0); ?>
                                    <?php else: ?>
                                        <?php echo $index + 1; ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo HTML::chars($server['ID_SERVER']); ?></td>
                                <td><?php echo HTML::chars($server['NAME']); ?></td>
                                <td>
                                    <?php 
                                    if (method_exists('Model_Stat', 'IntToIP')) {
                                        echo Model::factory('Stat')->IntToIP($server['IP']);
                                    } else {
                                        echo HTML::chars($server['IP']);
                                    }
                                    ?>
                                </td>
                                <td><?php echo HTML::chars($server['PORT']); ?></td>
                                <td>
                                    <?php if ($server['ACTIVE']): ?>
                                        <span class="label label-success"><?php echo __('Активен'); ?></span>
                                    <?php else: ?>
                                        <span class="label label-danger"><?php echo __('Неактивен'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo HTML::chars($server['NAMETYPE']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($is_logged_in): ?>
                <div class="btn-group">
                    <?php echo Form::button('todo', __('Редактировать'), array(
                        'value' => 'edit_server',
                        'class' => 'btn btn-warning btn-sm',
                        'type' => 'submit'
                    )); ?>
                    
                    <?php echo Form::button('todo', __('Удалить'), array(
                        'value' => 'del_server',
                        'class' => 'btn btn-danger btn-sm',
                        'type' => 'submit',
                        'onclick' => 'return confirm(\''.__('Вы уверены, что хотите удалить этот сервер?').'\') ? true : false;'
                    )); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php echo Form::close(); ?>