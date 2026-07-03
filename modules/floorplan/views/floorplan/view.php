<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">
            <span class="glyphicon glyphicon-eye-open"></span>
            Просмотр плана: <?php echo htmlspecialchars($floorplan['name']); ?>
        </h3>
    </div>
    <div class="panel-body">

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
                <span class="pull-right text-muted">
                    <span class="glyphicon glyphicon-info-sign"></span>
                    <?php echo count($points); ?> точек на плане
                </span>
            </div>
        </div>

        <!-- Контейнер для плана -->
        <div class="floorplan-container" style="position: relative; border: 1px solid #ddd; border-radius: 4px; overflow: auto; background: #fafafa; min-height: 400px;">
            <div style="position: relative; width: <?php echo $floorplan['width']; ?>px; height: <?php echo $floorplan['height']; ?>px; margin: 0 auto;">
                <!-- Изображение плана -->
                <img src="<?php echo URL::base() . $floorplan['image']; ?>" 
                     style="width: 100%; height: 100%; display: block;"
                     alt="<?php echo htmlspecialchars($floorplan['name']); ?>">

                <!-- Точки на плане -->
                <?php foreach ($points as $point): 
                    $status = isset($deviceStatuses[$point['id_dev']]) ? $deviceStatuses[$point['id_dev']]['status'] : 'unknown';
                    $statusClass = $status == 'online' ? 'status-online' : 'status-offline';
                ?>
                    <div class="floorplan-point <?php echo $statusClass; ?>" 
                         data-point-id="<?php echo $point['id_point']; ?>"
                         data-device-id="<?php echo $point['id_dev']; ?>"
                         style="position: absolute; left: <?php echo $point['x_pos']; ?>%; top: <?php echo $point['y_pos']; ?>%; transform: translate(-50%, -50%);">
                        
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
                            <div class="point-label" style="position: absolute; bottom: -24px; left: 50%; transform: translateX(-50%); font-size: 10px; white-space: nowrap; background: rgba(255,255,255,0.9); padding: 1px 6px; border-radius: 3px; border: 1px solid #ddd;">
                                <?php echo htmlspecialchars($point['label']); ?>
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
                                        <th>#</th>
                                        <th>Тип</th>
                                        <th>Устройство</th>
                                        <th>Метка</th>
                                        <th>Позиция</th>
                                        <th>Статус</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($points)): ?>
                                        <?php foreach ($points as $index => $point): 
                                            $status = isset($deviceStatuses[$point['id_dev']]) ? $deviceStatuses[$point['id_dev']]['status'] : 'unknown';
                                        ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo $point['point_type']; ?></td>
                                                <td><?php echo htmlspecialchars($point['device_name'] ?: 'Не привязано'); ?></td>
                                                <td><?php echo htmlspecialchars($point['label']); ?></td>
                                                <td>X: <?php echo round($point['x_pos'], 1); ?>% Y: <?php echo round($point['y_pos'], 1); ?>%</td>
                                                <td>
                                                    <span class="label label-<?php echo $status == 'online' ? 'success' : 'danger'; ?>">
                                                        <?php echo $status == 'online' ? 'Online' : 'Offline'; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Нет точек на плане</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.floorplan-container {
    background: #fafafa;
    min-height: 400px;
}

.floorplan-point {
    z-index: 10;
    transition: all 0.2s ease;
}

.floorplan-point:hover {
    z-index: 20;
    transform: translate(-50%, -50%) scale(1.1) !important;
}

.floorplan-point .point-icon {
    text-shadow: 0 0 5px rgba(255,255,255,0.8);
}

.floorplan-point.status-online .point-icon {
    opacity: 1;
}

.floorplan-point.status-offline .point-icon {
    opacity: 0.4;
}

.text-success {
    color: #5cb85c;
}

.text-danger {
    color: #d9534f;
}
</style>