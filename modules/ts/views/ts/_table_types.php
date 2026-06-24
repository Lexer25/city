<?php
/**
 * Таблица типов транспортных серверов
 * @var array $listTypes
 * @var bool $is_logged_in
 */
?>
<?php echo Form::open('ts/control_types', array('method' => 'POST')); ?>
<div class="table-responsive">
    <table class="table table-striped table-hover table-condensed">
        <thead>
            <tr>
                <th width="30"><?php echo __('№'); ?></th>
                <th><?php echo __('ID'); ?></th>
                <th><?php echo __('Название'); ?></th>
                <th><?php echo __('Описание'); ?></th>
                <th><?php echo __('Активен'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($listTypes as $index => $type): ?>
                <tr>
                    <td>
                        <?php if ($is_logged_in): ?>
                            <?php echo Form::radio('id', $type['ID'], $index === 0); ?>
                        <?php else: ?>
                            <?php echo $index + 1; ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo HTML::chars($type['ID']); ?></td>
                    <td><?php echo HTML::chars($type['NAME']); ?></td>
                    <td><?php echo HTML::chars($type['DESCRIPTION']); ?></td>
                    <td>
                        <?php if (!empty($type['IS_ENABLED'])): ?>
                            <span class="label label-success"><?php echo __('Да'); ?></span>
                        <?php else: ?>
                            <span class="label label-default"><?php echo __('Нет'); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($is_logged_in): ?>
    <div class="btn-group">
        <?php echo Form::button('todo', __('Редактировать'), array(
            'value' => 'edit_type',
            'class' => 'btn btn-warning btn-sm',
            'type' => 'submit'
        )); ?>
        
        <?php echo Form::button('todo', __('Удалить'), array(
            'value' => 'del_type',
            'class' => 'btn btn-danger btn-sm',
            'type' => 'submit',
            'onclick' => 'return confirm(\''.__('Вы уверены, что хотите удалить этот тип?').'\') ? true : false;'
        )); ?>
    </div>
<?php endif; ?>
<?php echo Form::close(); ?>