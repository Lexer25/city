<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Конфигурация типов событий</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .actions a { margin-right: 10px; }
        .add-btn { display: inline-block; margin-bottom: 15px; padding: 8px 15px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px; }
        .add-btn:hover { background: #45a049; }
        .color-box { display: inline-block; width: 20px; height: 20px; border: 1px solid #000; }
        .active-yes { color: green; }
        .active-no { color: red; }
    </style>
</head>
<body>
    <h1>Типы событий</h1>
    
    <a href="<?php echo URL::site('eventConfig/add'); ?>" class="add-btn">Добавить новый тип</a>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Флаг</th>
                <th>Цвет</th>
                <th>Звук</th>
                <th>Активен</th>
                <th>Родитель</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($eventtypes as $event): 
			//echo Debug::vars('41', $event);exit;
			// Kohana::$log->add(Log::ERROR, Debug::vars('41', $event));
			$color= str_pad(dechex($event['COLOR']), 6, '0', STR_PAD_LEFT);
			?>
            <tr>
                <td><?php echo $event['ID_EVENTTYPE']; ?></td>
                <td><?php echo HTML::chars($event['NAME']); ?></td>
                <td><?php echo $event['FLAG']; ?></td>
                <td>
                    <div class="color-box" style="background-color: #<?php echo $color; ?>;" title="COLOR=<?php echo $event['COLOR']; ?> hex=#<?php echo $color; ?>"></div>
                    #<?php echo $color; ?>
                    <small class="text-muted">(<?php echo $event['COLOR']; ?>)</small>
                </td>
                <td><?php echo HTML::chars($event['SOUND']); ?></td>
                <td class="<?php echo $event['ACTIVE'] ? 'active-yes' : 'active-no'; ?>">
                    <?php echo $event['ACTIVE'] ? 'Да' : 'Нет'; ?>
                </td>
                <td>
                    <?php
                    if ($event['ID_PARENT']) {
                        foreach ($parents as $parent) {
                            if ($parent['ID_EVENTTYPE'] == $event['ID_PARENT']) {
                                echo HTML::chars($parent['NAME']) . ' ('.$event['ID_PARENT'].')';
                                break;
                            }
                        }
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
                <td class="actions">
                    <a href="<?php echo URL::site('eventConfig/edit/'.$event['ID_EVENTTYPE']); ?>">Редактировать</a>
                    <!-- Удаление запрещено по ТЗ -->
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <p><small>Всего записей: <?php echo count($eventtypes); ?></small></p>
</body>
</html>