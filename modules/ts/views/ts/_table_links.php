<?php
/**
 * Таблица связей сервер-тип
 * @var array $listLinks
 * @var bool $is_logged_in
 */
?>
<?php echo Form::open('ts/control_links', array('method' => 'POST')); ?>
<div class="table-responsive">
    <table class="table table-striped table-hover table-condensed">
        <thead>
            <tr>
                <th width="30"><?php echo __('№'); ?></th>
                <th><?php echo __('ID сервера'); ?></th>
                <th><?php echo __('Сервер'); ?></th>
                <th><?php echo __('ID типа'); ?></th>
                <th><?php echo __('Тип'); ?></th>
                <?php if ($is_logged_in): ?>
                    <th width="60"><?php echo __('Действие'); ?></th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($listLinks)): ?>
                <tr>
                    <td colspan="<?php echo $is_logged_in ? 6 : 5; ?>" class="text-center">
                        <div class="alert alert-info">
                            <?php echo __('Нет привязанных серверов к типам'); ?>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($listLinks as $index => $link): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo HTML::chars($link['ID_SERVER']); ?></td>
                        <td><?php echo HTML::chars($link['SERVER_NAME']); ?></td>
                        <td><?php echo HTML::chars($link['ID_TYPE']); ?></td>
                        <td><?php echo HTML::chars($link['TYPE_NAME']); ?></td>
                        <?php if ($is_logged_in): ?>
                            <td>
                                <button type="submit" 
                                        name="todo" 
                                        value="del_link"
                                        class="btn btn-danger btn-xs"
                                        onclick="return confirm('<?php echo __('Удалить эту связь?'); ?>')"
                                        formaction="<?php echo URL::site('ts/control_links'); ?>">
                                    <span class="glyphicon glyphicon-trash"></span>
                                </button>
                                <?php echo Form::hidden('id_server', $link['ID_SERVER']); ?>
                                <?php echo Form::hidden('id_type', $link['ID_TYPE']); ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php echo Form::close(); ?>