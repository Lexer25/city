<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Список праздников'); ?></h3>
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
        
        <!-- Верхняя панель с кнопкой добавления -->
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-xs-12">
                <?php if ($is_admin): ?>
                    <a href="<?php echo URL::site('holiday/add'); ?>" class="btn btn-success">
                        <span class="glyphicon glyphicon-plus"></span> <?php echo __('Добавить праздник'); ?>
                    </a>
                <?php else: ?>
                    <span class="btn btn-success disabled" title="<?php echo __('Доступно только администраторам'); ?>">
                        <span class="glyphicon glyphicon-plus"></span> <?php echo __('Добавить праздник'); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if(isset($holidays) && count($holidays) > 0): ?>
            
            <div class="table-responsive">
                <table id="holidaysTable" class="table table-striped table-hover table-condensed table-bordered">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="50%"><?php echo __('Название праздника'); ?></th>
                            <th width="25%"><?php echo __('Дата'); ?></th>
                            <th width="20%"><?php echo __('Действия'); ?></th>
                        </tr>
                        <tr class="active">
                            <th>
                                <input type="text" id="filterId" class="form-control input-sm" placeholder="<?php echo __('Поиск по ID...'); ?>">
                            </th>
                            <th>
                                <input type="text" id="filterName" class="form-control input-sm" placeholder="<?php echo __('Поиск по названию...'); ?>">
                            </th>
                            <th>
                                <input type="text" id="filterDate" class="form-control input-sm" placeholder="<?php echo __('Поиск по дате...'); ?>">
                            </th>
                            <th>
                                <button type="button" id="resetFilters" class="btn btn-default btn-sm btn-block" title="<?php echo __('Сбросить фильтры'); ?>">
                                    <span class="glyphicon glyphicon-refresh"></span>
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($holidays as $holiday): ?>
                            <tr data-id="<?php echo htmlspecialchars(Arr::get($holiday, 'id_holiday')); ?>"
                                data-name="<?php echo htmlspecialchars(Arr::get($holiday, 'name')); ?>"
                                data-date="<?php echo htmlspecialchars(Arr::get($holiday, 'date')); ?>">
                                <td><?php echo htmlspecialchars(Arr::get($holiday, 'id_holiday')); ?></td>
                                <td><?php echo htmlspecialchars(Arr::get($holiday, 'name')); ?></td>
                                <td><?php echo date('d.m.Y', strtotime(Arr::get($holiday, 'date'))); ?></td>
                                <td>
                                    <div class="btn-group btn-group-xs">
                                        <?php if ($is_admin): ?>
                                            <a href="<?php echo URL::site('holiday/edit/' . Arr::get($holiday, 'id_holiday')); ?>" class="btn btn-primary" title="<?php echo __('Редактировать'); ?>">
                                                <span class="glyphicon glyphicon-edit"></span>
                                            </a>
                                            <a href="<?php echo URL::site('holiday/delete/' . Arr::get($holiday, 'id_holiday')); ?>" class="btn btn-danger" title="<?php echo __('Удалить'); ?>" onclick="return confirm('<?php echo __('Вы уверены, что хотите удалить этот праздник?'); ?>')">
                                                <span class="glyphicon glyphicon-trash"></span>
                                            </a>
                                        <?php else: ?>
                                            <span class="btn btn-primary disabled" title="<?php echo __('Доступно только администраторам'); ?>">
                                                <span class="glyphicon glyphicon-edit"></span>
                                            </span>
                                            <span class="btn btn-danger disabled" title="<?php echo __('Доступно только администраторам'); ?>">
                                                <span class="glyphicon glyphicon-trash"></span>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                  </td>
                              </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="active">
                            <td colspan="4">
                                <small class="text-muted">
                                    <span class="glyphicon glyphicon-stats"></span> 
                                    <?php echo __('Всего праздников'); ?>: <span id="totalCount"><?php echo count($holidays); ?></span>
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
                    <?php if ($is_admin): ?>
                        <a href="<?php echo URL::site('holiday/add'); ?>" class="btn btn-success">
                            <span class="glyphicon glyphicon-plus"></span> <?php echo __('Добавить праздник'); ?>
                        </a>
                    <?php else: ?>
                        <span class="btn btn-success disabled" title="<?php echo __('Доступно только администраторам'); ?>">
                            <span class="glyphicon glyphicon-plus"></span> <?php echo __('Добавить праздник'); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
        <?php else: ?>
            <div class="alert alert-info text-center">
                <span class="glyphicon glyphicon-info-sign"></span> <?php echo __('Нет доступных праздников'); ?>
            </div>
            
            <div class="form-group" style="margin-top: 15px;">
                <?php if ($is_admin): ?>
                    <a href="<?php echo URL::site('holiday/add'); ?>" class="btn btn-success">
                        <span class="glyphicon glyphicon-plus"></span> <?php echo __('Добавить праздник'); ?>
                    </a>
                <?php else: ?>
                    <span class="btn btn-success disabled" title="<?php echo __('Доступно только администраторам'); ?>">
                        <span class="glyphicon glyphicon-plus"></span> <?php echo __('Добавить праздник'); ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$is_admin && count($holidays) > 0): ?>
            <div class="alert alert-info text-center" style="margin-top: 15px;">
                <span class="glyphicon glyphicon-lock"></span> 
                <?php echo __('Режим только для просмотра. Для редактирования праздников необходимы права администратора.'); ?>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        // Функция фильтрации
        function applyFilters() {
            var idFilter = $("#filterId").val().toLowerCase().trim();
            var nameFilter = $("#filterName").val().toLowerCase().trim();
            var dateFilter = $("#filterDate").val().toLowerCase().trim();
            
            var visibleCount = 0;
            
            $("#holidaysTable tbody tr").each(function() {
                var $row = $(this);
                var id = $row.attr("data-id").toLowerCase();
                var name = $row.attr("data-name").toLowerCase();
                var date = $row.attr("data-date").toLowerCase();
                
                var showRow = true;
                
                if (idFilter && id.indexOf(idFilter) === -1) showRow = false;
                if (nameFilter && name.indexOf(nameFilter) === -1) showRow = false;
                if (dateFilter && date.indexOf(dateFilter) === -1) showRow = false;
                
                if (showRow) {
                    $row.show();
                    visibleCount++;
                } else {
                    $row.hide();
                }
            });
            
            var total = $("#holidaysTable tbody tr").length;
            if (idFilter || nameFilter || dateFilter) {
                $("#filterInfo").show();
                $("#filteredCount").text(visibleCount);
            } else {
                $("#filterInfo").hide();
            }
            
            if (visibleCount === 0 && total > 0) {
                if ($("#noDataMessage").length === 0) {
                    $("#holidaysTable tbody").append('<tr id="noDataMessage"><td colspan="4" class="text-center text-muted" style="padding: 30px;"><span class="glyphicon glyphicon-search"></span> <?php echo __('Ничего не найдено'); ?></td></tr>');
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
        
        $("#filterId, #filterName, #filterDate").on("keyup", debounceApplyFilters);
        
        $("#resetFilters").on("click", function() {
            $("#filterId, #filterName, #filterDate").val("");
            applyFilters();
        });
        
        // Сортировка таблицы
        var sortOrder = {};
        $("#holidaysTable thead tr:first th").on("click", function() {
            var index = $(this).index();
            if (index === 3) return;
            
            var $table = $("#holidaysTable");
            var rows = $table.find("tbody tr:visible").get();
            var currentOrder = sortOrder[index] || 'asc';
            
            rows.sort(function(a, b) {
                var aVal, bVal;
                
                if (index === 0) {
                    aVal = parseInt($(a).find("td:eq(0)").text()) || 0;
                    bVal = parseInt($(b).find("td:eq(0)").text()) || 0;
                    return currentOrder === 'asc' ? aVal - bVal : bVal - aVal;
                } else {
                    aVal = $(a).find("td:eq(" + index + ")").text().toLowerCase();
                    bVal = $(b).find("td:eq(" + index + ")").text().toLowerCase();
                    if (currentOrder === 'asc') {
                        return aVal.localeCompare(bVal);
                    } else {
                        return bVal.localeCompare(aVal);
                    }
                }
            });
            
            sortOrder[index] = currentOrder === 'asc' ? 'desc' : 'asc';
            
            $.each(rows, function(i, row) {
                $table.children("tbody").append(row);
            });
            
            $("#holidaysTable thead tr:first th").removeClass("active");
            $(this).addClass("active");
        });
    });
</script>

<style>
    #holidaysTable th {
        cursor: pointer;
        user-select: none;
    }
    
    #holidaysTable th.active {
        background-color: #d9edf7;
    }
    
    .disabled {
        pointer-events: none;
        opacity: 0.6;
        cursor: not-allowed;
    }
</style>