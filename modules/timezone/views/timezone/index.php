<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Временные зоны'); ?></h3>
    </div>
    <div class="panel-body">
        
        <!-- Отображение сообщений -->
        <?php 
        $message = Session::instance()->get_once('message');
        $message_type = Session::instance()->get_once('message_type', 'info');
        if ($message): 
        ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <!-- Информация о количестве -->
        <div class="alert alert-info">
            <span class="glyphicon glyphicon-info-sign"></span>
            <?php echo __('Всего временных зон'); ?>: <strong><?php echo $currentCount; ?></strong>
            <?php echo __('из'); ?> <strong><?php echo $maxTimezones; ?></strong>
            <?php if ($currentCount >= $maxTimezones): ?>
                <span class="label label-danger"><?php echo __('Достигнут максимум'); ?></span>
            <?php endif; ?>
        </div>
        
        <!-- Верхняя панель с кнопкой добавления -->
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-xs-12">
                <a href="<?php echo URL::site('timezone/add'); ?>" class="btn btn-success <?php echo ($currentCount >= $maxTimezones) ? 'disabled' : ''; ?>">
                    <span class="glyphicon glyphicon-plus"></span> <?php echo __('Добавить временную зону'); ?>
                </a>
            </div>
        </div>
        
        <?php if(isset($timezones) && count($timezones) > 0): ?>
            
            <div class="table-responsive">
                <table id="timezonesTable" class="table table-striped table-hover table-condensed table-bordered">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="30%"><?php echo __('Название'); ?></th>
                            <th width="15%"><?php echo __('Время начала'); ?></th>
                            <th width="15%"><?php echo __('Время окончания'); ?></th>
                            <th width="25%"><?php echo __('Дни недели'); ?></th>
                            <th width="10%"><?php echo __('Действия'); ?></th>
                        </tr>
                        <tr class="active">
                            <th>
                                <input type="text" id="filterId" class="form-control input-sm" placeholder="<?php echo __('Поиск по ID...'); ?>">
                            </th>
                            <th>
                                <input type="text" id="filterName" class="form-control input-sm" placeholder="<?php echo __('Поиск по названию...'); ?>">
                            </th>
                            <th>
                                <input type="text" id="filterTimeStart" class="form-control input-sm" placeholder="<?php echo __('Поиск по времени...'); ?>">
                            </th>
                            <th>
                                <input type="text" id="filterTimeEnd" class="form-control input-sm" placeholder="<?php echo __('Поиск по времени...'); ?>">
                            </th>
                            <th>
                                <input type="text" id="filterDays" class="form-control input-sm" placeholder="<?php echo __('Поиск по дням...'); ?>">
                            </th>
                            <th>
                                <button type="button" id="resetFilters" class="btn btn-default btn-sm btn-block" title="<?php echo __('Сбросить фильтры'); ?>">
                                    <span class="glyphicon glyphicon-refresh"></span>
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($timezones as $timezone): 
                            $flagsInfo = Model::factory('Timezone')->parseFlags(Arr::get($timezone, 'flag', 0));
                        ?>
                            <tr data-id="<?php echo htmlspecialchars(Arr::get($timezone, 'id_timezone')); ?>"
                                data-name="<?php echo htmlspecialchars(Arr::get($timezone, 'name')); ?>"
                                data-time-start="<?php echo htmlspecialchars(Arr::get($timezone, 'timestart')); ?>"
                                data-time-end="<?php echo htmlspecialchars(Arr::get($timezone, 'timeend')); ?>"
                                data-days="<?php echo htmlspecialchars($flagsInfo['days']); ?>">
                                <td><?php echo htmlspecialchars(Arr::get($timezone, 'id_timezone')); ?></td>
                                <td><?php echo htmlspecialchars(Arr::get($timezone, 'name')); ?></td>
                                <td><?php echo substr(htmlspecialchars(Arr::get($timezone, 'timestart')), 0, 5); ?></td>
                                <td><?php echo substr(htmlspecialchars(Arr::get($timezone, 'timeend')), 0, 5); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($flagsInfo['days']); ?>
                                    <?php if (!empty($flagsInfo['special'])): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($flagsInfo['special']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-xs">
                                        <a href="<?php echo URL::site('timezone/edit/' . Arr::get($timezone, 'id_timezone')); ?>" class="btn btn-primary" title="<?php echo __('Редактировать'); ?>">
                                            <span class="glyphicon glyphicon-edit"></span>
                                        </a>
                                        <a href="<?php echo URL::site('timezone/delete/' . Arr::get($timezone, 'id_timezone')); ?>" class="btn btn-danger" title="<?php echo __('Удалить'); ?>" onclick="return confirm('<?php echo __('Вы уверены, что хотите удалить эту временную зону?'); ?>')">
                                            <span class="glyphicon glyphicon-trash"></span>
                                        </a>
                                    </div>
                                  </td>
                              </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="active">
                            <td colspan="6">
                                <small class="text-muted">
                                    <span class="glyphicon glyphicon-stats"></span> 
                                    <?php echo __('Всего зон'); ?>: <span id="totalCount"><?php echo count($timezones); ?></span>
                                    <span id="filterInfo" style="display: none;">
                                        , <?php echo __('Показано'); ?>: <span id="filteredCount">0</span>
                                    </span>
                                </small>
                             </td>
                          </tr>
                    </tfoot>
                  </table>
            </div>
            
            <!-- Нижняя панель с кнопкой добавления -->
            <div class="row" style="margin-top: 15px;">
                <div class="col-xs-12">
                    <a href="<?php echo URL::site('timezone/add'); ?>" class="btn btn-success <?php echo ($currentCount >= $maxTimezones) ? 'disabled' : ''; ?>">
                        <span class="glyphicon glyphicon-plus"></span> <?php echo __('Добавить временную зону'); ?>
                    </a>
                </div>
            </div>
            
        <?php else: ?>
            <div class="alert alert-info text-center">
                <span class="glyphicon glyphicon-info-sign"></span> <?php echo __('Нет доступных временных зон'); ?>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        function applyFilters() {
            var idFilter = $("#filterId").val().toLowerCase().trim();
            var nameFilter = $("#filterName").val().toLowerCase().trim();
            var timeStartFilter = $("#filterTimeStart").val().toLowerCase().trim();
            var timeEndFilter = $("#filterTimeEnd").val().toLowerCase().trim();
            var daysFilter = $("#filterDays").val().toLowerCase().trim();
            
            var visibleCount = 0;
            
            $("#timezonesTable tbody tr").each(function() {
                var $row = $(this);
                var id = $row.attr("data-id").toLowerCase();
                var name = $row.attr("data-name").toLowerCase();
                var timeStart = $row.attr("data-time-start").toLowerCase();
                var timeEnd = $row.attr("data-time-end").toLowerCase();
                var days = $row.attr("data-days").toLowerCase();
                
                var showRow = true;
                
                if (idFilter && id.indexOf(idFilter) === -1) showRow = false;
                if (nameFilter && name.indexOf(nameFilter) === -1) showRow = false;
                if (timeStartFilter && timeStart.indexOf(timeStartFilter) === -1) showRow = false;
                if (timeEndFilter && timeEnd.indexOf(timeEndFilter) === -1) showRow = false;
                if (daysFilter && days.indexOf(daysFilter) === -1) showRow = false;
                
                if (showRow) {
                    $row.show();
                    visibleCount++;
                } else {
                    $row.hide();
                }
            });
            
            var total = $("#timezonesTable tbody tr").length;
            if (idFilter || nameFilter || timeStartFilter || timeEndFilter || daysFilter) {
                $("#filterInfo").show();
                $("#filteredCount").text(visibleCount);
            } else {
                $("#filterInfo").hide();
            }
            
            if (visibleCount === 0 && total > 0) {
                if ($("#noDataMessage").length === 0) {
                    $("#timezonesTable tbody").append('<tr id="noDataMessage"><td colspan="6" class="text-center text-muted" style="padding: 30px;"><span class="glyphicon glyphicon-search"></span> <?php echo __('Ничего не найдено'); ?></td></td>');
                }
            } else {
                $("#noDataMessage").remove();
            }
        }
        
        var debounceTimer;
        function debounceApplyFilters() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(applyFilters, 300);
        }
        
        $("#filterId, #filterName, #filterTimeStart, #filterTimeEnd, #filterDays").on("keyup", debounceApplyFilters);
        
        $("#resetFilters").on("click", function() {
            $("#filterId, #filterName, #filterTimeStart, #filterTimeEnd, #filterDays").val("");
            applyFilters();
        });
        
        // Сортировка таблицы
        var sortOrder = {};
        $("#timezonesTable thead tr:first th").on("click", function() {
            var index = $(this).index();
            if (index === 5) return;
            
            var $table = $("#timezonesTable");
            var rows = $table.find("tbody tr:visible").get();
            var currentOrder = sortOrder[index] || 'asc';
            
            rows.sort(function(a, b) {
                var aVal, bVal;
                
                if (index === 0) {
                    aVal = parseInt($(a).find("td:eq(0)").text()) || 0;
                    bVal = parseInt($(b).find("td:eq(0)").text()) || 0;
                    return currentOrder === 'asc' ? aVal - bVal : bVal - aVal;
                } else if (index === 2 || index === 3) {
                    aVal = $(a).find("td:eq(" + index + ")").text();
                    bVal = $(b).find("td:eq(" + index + ")").text();
                } else {
                    aVal = $(a).find("td:eq(" + index + ")").text().toLowerCase();
                    bVal = $(b).find("td:eq(" + index + ")").text().toLowerCase();
                }
                
                if (currentOrder === 'asc') {
                    if (aVal < bVal) return -1;
                    if (aVal > bVal) return 1;
                    return 0;
                } else {
                    if (aVal > bVal) return -1;
                    if (aVal < bVal) return 1;
                    return 0;
                }
            });
            
            sortOrder[index] = currentOrder === 'asc' ? 'desc' : 'asc';
            
            $.each(rows, function(i, row) {
                $table.children("tbody").append(row);
            });
            
            $("#timezonesTable thead tr:first th").removeClass("active");
            $(this).addClass("active");
        });
    });
</script>

<style>
    #timezonesTable th {
        cursor: pointer;
        user-select: none;
    }
    
    #timezonesTable th.active {
        background-color: #d9edf7;
    }
    
    .disabled {
        pointer-events: none;
        opacity: 0.6;
    }
</style>