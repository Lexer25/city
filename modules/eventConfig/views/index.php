<?php if (!empty($flash_success)): ?>
<div class="alert alert-success">
    <?php echo HTML::chars($flash_success); ?>
</div>
<?php endif; ?>
<?php if (!empty($flash_error)): ?>
<div class="alert alert-danger">
    <?php echo HTML::chars($flash_error); ?>
</div>
<?php endif; ?>
<h1>Типы событий</h1>

<a href="<?php echo URL::site('eventConfig/add'); ?>" class="btn btn-success">Добавить новый тип</a>

<table class="table table-bordered table-striped table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Флаг</th>
            <th>Цвет</th>
            <th>Записывать в базу данных</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($eventtypes as $event): 
        $color = str_pad(dechex($event['COLOR']), 6, '0', STR_PAD_LEFT);
        ?>
        <tr>
            <td><?php echo $event['ID_EVENTTYPE']; ?></td>
            <td><?php echo HTML::chars($event['NAME']); ?></td>
            <td><?php echo $event['FLAG']; ?></td>
            <td>
                <div class="color-box" style="display: inline-block; width: 20px; height: 20px; border: 1px solid #000; background-color: #<?php echo $color; ?>;" title="COLOR=<?php echo $event['COLOR']; ?> hex=#<?php echo $color; ?>"></div>
                #<?php echo $color; ?>
                <small class="text-muted">(<?php echo $event['COLOR']; ?>)</small>
            </td>
            <td class="<?php echo $event['ACTIVE'] ? 'text-success' : 'text-danger'; ?>">
                <?php echo $event['ACTIVE'] ? 'Да' : 'Нет'; ?>
            </td>
            <td>
                <a href="<?php echo URL::site('eventConfig/edit/'.$event['ID_EVENTTYPE']); ?>" class="btn btn-primary btn-xs">Редактировать</a>
                <!-- Удаление запрещено по ТЗ -->
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p><small>Всего записей: <?php echo count($eventtypes); ?></small></p>