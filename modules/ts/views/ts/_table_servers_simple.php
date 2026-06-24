<?php
/**
 * Таблица транспортных серверов (без типов)
 * @var array $listTS
 * @var bool $is_logged_in
 */
?>
<?php echo Form::open('ts/control_servers', array('method' => 'POST')); ?>
<div class="table-responsive">
    <table class="table table-striped table-hover table-condensed">
        <thead>
            <tr>
                <th width="30"><?php echo __('№'); ?></th>
                <th><?php echo __('ID'); ?></th>
                <th><?php echo __('Название'); ?></th>
                <th><?php echo __('IP'); ?></th>
                <th><?php echo __('Порт'); ?></th>
                <th><?php echo __('Статус'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($listTS)): ?>
                <tr>
                    <td colspan="6" class="text-center">
                        <div class="alert alert-info">
                            <?php echo __('Нет зарегистрированных транспортных серверов'); ?>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
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
                                echo long2ip($server['IP']);
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
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($is_logged_in && !empty($listTS)): ?>
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
<?php echo Form::close(); ?>