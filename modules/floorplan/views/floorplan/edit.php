<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">
            <span class="glyphicon glyphicon-<?php echo $mode == 'view' ? 'eye-open' : 'edit'; ?>"></span>
            <?php echo $mode == 'view' ? 'Просмотр' : 'Редактирование'; ?> плана: <?php echo htmlspecialchars($floorplan['name']); ?>
        </h3>
    </div>
    <div class="panel-body">

        <?php if ($mode == 'edit'): ?>
            <!-- Форма обновления плана -->
            <div class="row" style="margin-bottom: 15px;">
                <div class="col-md-12">
                    <form method="POST" action="<?php echo URL::site('floorplan/edit/' . $floorplan['id_floorplan']); ?>" class="form-inline">
                        <input type="hidden" name="action" value="update_plan">
                        <div class="form-group">
                            <label>Название: </label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($floorplan['name']); ?>" class="form-control" style="width: 200px;">
                        </div>
                        <div class="form-group">
                            <label>Описание: </label>
                            <input type="text" name="description" value="<?php echo htmlspecialchars($floorplan['description']); ?>" class="form-control" style="width: 250px;">
                        </div>
                        <button type="submit" class="btn btn-primary">Обновить</button>
                        <a href="<?php echo URL::site('floorplan'); ?>" class="btn btn-default">Назад</a>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="row" style="margin-bottom: 15px;">
                <div class="col-md-12">
                    <a href="<?php echo URL::site('floorplan'); ?>" class="btn btn-default">
                        <span class="glyphicon glyphicon-arrow-left"></span> Назад
                    </a>
                    <?php if ($is_admin): ?>
                        <a href="<?php echo URL::site('floorplan/edit/' . $floorplan['id_floorplan']); ?>" class="btn btn-primary">
                            <span class="glyphicon glyphicon-edit"></span> Редактировать
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Сообщения -->
        <?php
        $message = Session::instance()->get_once('message');
        $message_type = Session::instance()->get_once('message_type', 'info');
        if ($message):
        ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade in">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Контейнер для плана -->
        <div class="floorplan-container" style="position: relative; border: 1px solid #ddd; border-radius: 4px; overflow: auto; background: #fafafa;">
            <div id="floorplanCanvas" style="position: relative; width: <?php echo $floorplan['width']; ?>px; height: <?php echo $floorplan['height']; ?>px; margin: 0 auto;">
                <!-- Изображение плана -->
                <img src="<?php echo URL::base() . $floorplan['image']; ?>" 
                     id="floorplanImage" 
                     style="width: 100%; height: 100%; display: block;"
                     alt="<?php echo htmlspecialchars($floorplan['name']); ?>">

                <!-- Точки на плане -->
                <?php foreach ($points as $point): 
                    $status = isset($deviceStatuses[$point['id_dev']]) ? $deviceStatuses[$point['id_dev']]['status'] : 'unknown';
                    $statusClass = $status == 'online' ? 'status-online' : 'status-offline';
                ?>
                    <div class="floorplan-point <?php echo $statusClass; ?> <?php echo $mode == 'edit' ? 'draggable' : ''; ?>" 
                         data-point-id="<?php echo $point['id_point']; ?>"
                         data-device-id="<?php echo $point['id_dev']; ?>"
                         style="position: absolute; left: <?php echo $point['x_pos']; ?>%; top: <?php echo $point['y_pos']; ?>%; cursor: <?php echo $mode == 'edit' ? 'grab' : 'default'; ?>; transform: translate(-50%, -50%); z-index: 10;">
                        
                        <div class="point-icon" title="<?php echo htmlspecialchars($point['label'] ?: $point['device_name']); ?>">
                            <?php if ($point['point_type'] == 'door'): ?>
                                <span class="glyphicon glyphicon-<?php echo $status == 'online' ? 'ok-circle text-success' : 'ban-circle text-danger'; ?>" style="font-size: 28px;"></span>
                            <?php elseif ($point['point_type'] == 'turnstile'): ?>
                                <span class="glyphicon glyphicon-<?php echo $status == 'online' ? 'unchecked text-success' : 'remove-circle text-danger'; ?>" style="font-size: 28px;"></span>
                            <?php else: ?>
                                <span class="glyphicon glyphicon-<?php echo $status == 'online' ? 'record text-success' : 'record text-danger'; ?>" style="font-size: 28px;"></span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($point['label']): ?>
                            <div class="point-label" style="position: absolute; bottom: -22px; left: 50%; transform: translateX(-50%); font-size: 10px; white-space: nowrap; background: rgba(255,255,255,0.9); padding: 1px 6px; border-radius: 3px; border: 1px solid #ddd;">
                                <?php echo htmlspecialchars($point['label']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($mode == 'edit'): ?>
                            <div class="point-actions" style="position: absolute; top: -30px; left: 50%; transform: translateX(-50%); display: none; z-index: 20;">
                                <button class="btn btn-xs btn-danger delete-point" data-point-id="<?php echo $point['id_point']; ?>">
                                    <span class="glyphicon glyphicon-trash"></span>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Информация о точках -->
        <div class="row" style="margin-top: 15px;">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">Точки прохода на плане</h4>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-condensed">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Тип</th>
                                        <th>Устройство</th>
                                        <th>Метка</th>
                                        <th>Позиция</th>
                                        <th>Статус</th>
                                        <th><?php echo $mode == 'edit' ? 'Действия' : ''; ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($points)): ?>
                                        <?php foreach ($points as $point): 
                                            $status = isset($deviceStatuses[$point['id_dev']]) ? $deviceStatuses[$point['id_dev']]['status'] : 'unknown';
                                        ?>
                                            <tr>
                                                <td><?php echo $point['id_point']; ?></td>
                                                <td><?php echo $point['point_type']; ?></td>
                                                <td><?php echo htmlspecialchars($point['device_name'] ?: 'Не привязано'); ?></td>
                                                <td><?php echo htmlspecialchars($point['label']); ?></td>
                                                <td>X: <?php echo round($point['x_pos'], 1); ?>% Y: <?php echo round($point['y_pos'], 1); ?>%</td>
                                                <td>
                                                    <span class="label label-<?php echo $status == 'online' ? 'success' : 'danger'; ?>">
                                                        <?php echo $status == 'online' ? 'Online' : 'Offline'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($mode == 'edit'): ?>
                                                        <button class="btn btn-xs btn-danger delete-point" data-point-id="<?php echo $point['id_point']; ?>">
                                                            <span class="glyphicon glyphicon-trash"></span>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">Нет точек на плане</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($mode == 'edit'): ?>
            <!-- Форма добавления точки -->
            <div class="row" style="margin-top: 15px;">
                <div class="col-md-12">
                    <div class="panel panel-success">
                        <div class="panel-heading">
                            <h4 class="panel-title">Добавить точку прохода</h4>
                        </div>
                        <div class="panel-body">
                            <form method="POST" action="<?php echo URL::site('floorplan/edit/' . $floorplan['id_floorplan']); ?>" class="form-inline">
                                <input type="hidden" name="action" value="add_point">
                                <div class="form-group">
                                    <label>X: </label>
                                    <input type="number" name="x" step="0.1" class="form-control" style="width: 80px;" required>
                                </div>
                                <div class="form-group">
                                    <label>Y: </label>
                                    <input type="number" name="y" step="0.1" class="form-control" style="width: 80px;" required>
                                </div>
                                <div class="form-group">
                                    <label>Устройство: </label>
                                    <select name="device_id" class="form-control" style="width: 200px;" required>
                                        <option value="">Выберите устройство</option>
                                        <?php foreach ($availableDevices as $device): ?>
                                            <option value="<?php echo $device['id_dev']; ?>">
                                                <?php echo htmlspecialchars($device['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                              <div class="form-group">
								<label>Тип: </label>
								<select name="point_type" class="form-control" style="width: 120px;">  <!-- <-- ИСПРАВЛЕНО -->
									<option value="door">Дверь</option>
									<option value="turnstile">Турникет</option>
									<option value="reader">Считыватель</option>
									<option value="camera">Камера</option>
								</select>
							</div>
                                <div class="form-group">
                                    <label>Метка: </label>
                                    <input type="text" name="label" class="form-control" style="width: 150px;">
                                </div>
                                <button type="submit" class="btn btn-success">Добавить</button>
                            </form>
                            <small class="text-muted">Подсказка: X и Y - это процентное положение от левого и верхнего края (0-100)</small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php if ($mode == 'edit'): ?>
<!-- JS для управления точками -->
<script>
$(document).ready(function() {
    var $points = $('.floorplan-point.draggable');
    var $container = $('#floorplanCanvas');

    if ($points.length > 0 && $container.length > 0) {
        // Перетаскивание точек
        $points.draggable({
            containment: $container,
            cursor: 'grab',
            handle: '.point-icon',
            start: function(e, ui) {
                $(this).find('.point-actions').show();
                $(this).css('z-index', 20);
            },
            stop: function(e, ui) {
                var $point = $(this);
                var pointId = $point.data('point-id');
                var parentWidth = $container.width();
                var parentHeight = $container.height();
                var left = ui.position.left;
                var top = ui.position.top;
                var xPercent = (left / parentWidth) * 100;
                var yPercent = (top / parentHeight) * 100;
                
                // Ограничиваем значения
                xPercent = Math.max(0, Math.min(100, xPercent));
                yPercent = Math.max(0, Math.min(100, yPercent));
                
                // Обновляем позицию в стилях
                $point.css('left', xPercent + '%');
                $point.css('top', yPercent + '%');
                
                // Сохраняем позицию через AJAX
                var data = {
                    points: [{
                        id: pointId,
                        x: xPercent,
                        y: yPercent
                    }]
                };
                
                $.ajax({
                    url: '<?php echo URL::site("floorplan/savePositions"); ?>',
                    type: 'POST',
                    data: JSON.stringify(data),
                    contentType: 'application/json',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            console.log('Position saved for point ' + pointId);
                            // Обновляем таблицу (перезагружаем страницу или обновляем данные)
                        } else {
                            console.error('Error saving position:', response.error);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', error);
                    }
                });
            }
        });

        // Показываем действия при наведении
        $points.hover(
            function() {
                $(this).find('.point-actions').show();
            },
            function() {
                $(this).find('.point-actions').hide();
            }
        );
    }

    // Удаление точки
    $('.delete-point').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (!confirm('Удалить точку?')) return;
        
        var pointId = $(this).data('point-id');
        var $point = $('.floorplan-point[data-point-id="' + pointId + '"]');
        
        $.ajax({
            url: '<?php echo URL::site("floorplan/deletePointAjax"); ?>',
            type: 'POST',
            data: { point_id: pointId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $point.fadeOut(300, function() {
                        $(this).remove();
                        // Перезагружаем страницу для обновления таблицы
                        location.reload();
                    });
                } else {
                    alert('Ошибка при удалении точки: ' + (response.error || 'Неизвестная ошибка'));
                }
            },
            error: function(xhr, status, error) {
                alert('Ошибка при удалении точки: ' + error);
            }
        });
    });
});
</script>
<?php endif; ?>

<style>
.floorplan-container {
    background: #fafafa;
    min-height: 600px;
    position: relative;
}

.floorplan-point {
    z-index: 10;
    transition: all 0.2s ease;
    user-select: none;
}

.floorplan-point:hover {
    z-index: 20;
}

.floorplan-point .point-icon {
    text-shadow: 0 0 5px rgba(255,255,255,0.8);
    cursor: grab;
}

.floorplan-point .point-icon:active {
    cursor: grabbing;
}

.floorplan-point.status-online .point-icon {
    opacity: 1;
}

.floorplan-point.status-offline .point-icon {
    opacity: 0.4;
}

.floorplan-point.draggable {
    cursor: grab;
}

.floorplan-point.draggable:active {
    cursor: grabbing;
}

.floorplan-point.draggable:hover .point-actions {
    display: block !important;
}

.point-actions {
    display: none;
    z-index: 30;
}

.text-success {
    color: #5cb85c;
}

.text-danger {
    color: #d9534f;
}
</style>