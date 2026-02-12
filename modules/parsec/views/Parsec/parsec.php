<? //http://itchief.ru/lessons/bootstrap-3/30-bootstrap-3-tables;
// страница отображения данных по парковочной системе
//echo Debug::vars('3', $task_list);
echo Form::open('parsec/parsec_control', array('method' => 'post', 'id' => 'parsec-form'));
?>
<fieldset>
    <legend><?php echo __('parsec_about'); ?></legend>
    <?php echo __('parsec_legend'); ?>
</fieldset>

<?php
$e_mess = Validation::Factory(Session::instance()->as_array())
    ->rule('e_mess', 'is_array')
    ->rule('e_mess', 'not_empty');

if ($e_mess->check()) {
    $param = 'Yes message<br>';
    foreach (Arr::get($e_mess, 'e_mess') as $key => $value) {
        $param .= $value . '<br>';
    }
    ?>
    <div id="my-alert" class="alert alert-danger alert-dismissible" role="alert">
        <?php echo $param; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php
}
Session::instance()->delete('e_mess');
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">
            <?php echo __('Список задач интегратора парсек :count', array(':count' => count($task_list))); ?>
        </h3>
    </div>
    <div class="panel-body">

        <?php
        // Массив операций
        $operatiion_name = array(
            '1' => 'add_card',
            '2' => 'del_card',
            '3' => 'add_people',
            '4' => 'del_card',
            '5' => 'add_org',
            '6' => 'del_org',
            '7' => 'add_access',
            '8' => 'del_access',
        );

        // Исходные данные (преобразованные для JS)
        $raw_data = array();
        if (isset($task_list) && is_array($task_list)) {
            foreach ($task_list as $item) {
                $raw_data[] = array(
                    'id'          => Arr::get($item, 'ID', ''),
                    'id_card'     => Arr::get($item, 'ID_CARD', ''),
                    'id_pep'      => Arr::get($item, 'ID_PEP', ''),
                    'operation'   => Arr::get($item, 'OPERATION', ''),
                    'operation_name' => Arr::get($operatiion_name, Arr::get($item, 'OPERATION', ''), 'unknown'),
                    'org_name'    => iconv('windows-1251', 'UTF-8', Arr::get($item, 'ORG_NAME', '')),
                    'attempts'    => Arr::get($item, 'ATTEMPTS', ''),
                    'dest'        => iconv('windows-1251', 'UTF-8', Arr::get($item, 'DEST', '')),
                    'timestamp'   => Arr::get($item, 'TIME_STAMP', ''),
                    'hex'         => (Arr::get($item, 'OPERATION', '') == 2) ? ' (0x' . dechex(Arr::get($item, 'ID_CARD', 0)) . ')' : ''
                );
            }
        }
        ?>

        <?php if (isset($task_list) && is_array($task_list) && !empty($task_list)): ?>

        <!-- Кнопка сброса фильтров и счётчик -->
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-12">
                <button type="button" class="btn btn-sm btn-warning" id="reset-filters">
                    <span class="glyphicon glyphicon-remove-circle"></span> <?php echo __('Сбросить все фильтры'); ?>
                </button>
                <span class="text-muted" style="margin-left: 10px;">
                    <span id="filter-counter">
                        <?php echo __('Показано записей: :count из :total', array(':count' => count($task_list), ':total' => count($task_list))); ?>
                    </span>
                </span>
            </div>
        </div>

        <!-- Таблица с фильтрами в заголовках -->
        <div class="table-responsive">
            <table class="table table-striped table-hover table-condensed" id="parsec-table">
                <thead>
                    <tr class="active">
                        <th><?php echo __('id_cardindev'); ?></th>
                        <th><?php echo __('GUID'); ?></th>
                        <th><?php echo __('id_pep'); ?></th>
                        <th><?php echo __('operation'); ?></th>
                        <th><?php echo __('С кем связана операция'); ?></th>
                        <th><?php echo __('attempt'); ?></th>
                        <th><?php echo __('для кого'); ?></th>
                        <th><?php echo __('timestamp'); ?></th>
                        <th><?php echo __('todo'); ?></th>
                    </tr>
                    <!-- Строка с полями фильтрации -->
                    <tr class="filters-row">
                        <th><input type="text" class="form-control input-sm column-filter" data-column="0" placeholder="ID"></th>
                        <th><input type="text" class="form-control input-sm column-filter" data-column="1" placeholder="GUID"></th>
                        <th><input type="text" class="form-control input-sm column-filter" data-column="2" placeholder="ID_PEP"></th>
                        <th>
                            <select class="form-control input-sm column-filter" data-column="3" data-type="select">
                                <option value=""><?php echo __('все'); ?></option>
                                <?php foreach ($operatiion_name as $op_id => $op_name): ?>
                                <option value="<?php echo $op_id; ?>"><?php echo $op_name; ?> (<?php echo $op_id; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </th>
                        <th><input type="text" class="form-control input-sm column-filter" data-column="4" placeholder="<?php echo __('организация'); ?>"></th>
                        <th><input type="text" class="form-control input-sm column-filter" data-column="5" placeholder="<?php echo __('попытки'); ?>"></th>
                        <th><input type="text" class="form-control input-sm column-filter" data-column="6" placeholder="<?php echo __('получатель'); ?>"></th>
                        <th><input type="text" class="form-control input-sm column-filter" data-column="7" placeholder="YYYY-MM-DD"></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <?php foreach ($raw_data as $row): ?>
                    <tr>
                        <td><?php echo Form::hidden('id_cardindev[' . $row['id'] . ']', $row['id']); ?><?php echo $row['id']; ?></td>
                        <td><?php echo $row['id_card'] . $row['hex']; ?></td>
                        <td><?php echo $row['id_pep']; ?></td>
                        <td><?php echo $row['operation_name'] . ' (' . $row['operation'] . ')'; ?></td>
                        <td><?php echo htmlspecialchars($row['org_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo $row['attempts']; ?></td>
                        <td><?php echo htmlspecialchars($row['dest'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo $row['timestamp']; ?></td>
                        <td>
                            <a href="parsec/repeat/<?php echo $row['id']; ?>" class="btn btn-xs btn-success">Repeat</a>
                            <a href="parsec/delete/<?php echo $row['id']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('<?php echo __('Вы уверены?'); ?>') ? true : false;">delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php else: ?>
            <div class="alert alert-info"><?php echo __('Список задач пуст.'); ?></div>
        <?php endif; ?>

        <div class="form-group" style="margin-top: 15px;">
            <?php
            echo Form::button('todo', 'RESTART ALL TASK', array(
                'value' => 'set_attempt',
                'class' => 'btn btn-warning',
                'type' => 'submit',
                'onclick' => 'return confirm(\'' . __('restart_all_task_parsec') . '\') ? true : false;'
            ));
            echo Form::button('todo', 'DELETE ALL TASKS', array(
                'value' => 'delAllTasks',
                'class' => 'btn btn-danger',
                'type' => 'submit',
                'style' => 'margin-left: 10px;',
                'onclick' => 'return confirm(\'' . __('delete_all_task_parsec') . '\') ? true : false;'
            ));
            ?>
        </div>
    </div>
</div>

<?php echo Form::close(); ?>

<!-- JavaScript для мгновенной фильтрации -->
<script>
    (function() {
        'use strict';

        // Функция фильтрации таблицы
        function filterTable() {
            var filters = [];
            var filterInputs = document.querySelectorAll('.column-filter');
            
            // Собираем значения фильтров
            filterInputs.forEach(function(input, index) {
                var value = '';
                if (input.tagName === 'SELECT') {
                    value = input.options[input.selectedIndex] ? input.options[input.selectedIndex].value : '';
                } else {
                    value = input.value.toLowerCase().trim();
                }
                filters[index] = value;
            });

            var rows = document.querySelectorAll('#table-body tr');
            var visibleCount = 0;

            rows.forEach(function(row) {
                var show = true;
                var cells = row.cells;

                // Проверяем каждый фильтр
                for (var i = 0; i < filters.length; i++) {
                    var filterValue = filters[i];
                    if (filterValue === '') continue;

                    var cellValue = '';
                    if (cells[i]) {
                        cellValue = cells[i].textContent.toLowerCase() || cells[i].innerText.toLowerCase() || '';
                    }

                    if (i === 3) { // Колонка operation
                        var opMatch = cellValue.match(/\((\d+)\)/);
                        var opCode = opMatch ? opMatch[1] : '';
                        if (filterValue !== opCode) {
                            show = false;
                            break;
                        }
                    } else {
                        if (cellValue.indexOf(filterValue) === -1) {
                            show = false;
                            break;
                        }
                    }
                }

                row.style.display = show ? '' : 'none';
                if (show) visibleCount++;
            });

            // Обновляем счётчик
            var counter = document.getElementById('filter-counter');
            if (counter) {
                var total = rows.length;
                counter.innerHTML = '<?php echo __("Показано записей: :count из :total"); ?>'
                    .replace(':count', visibleCount)
                    .replace(':total', total);
            }
        }

        // Назначаем обработчики после загрузки страницы
        document.addEventListener('DOMContentLoaded', function() {
            var filterInputs = document.querySelectorAll('.column-filter');
            
            filterInputs.forEach(function(input) {
                input.addEventListener('keyup', function(e) {
                    filterTable();
                });
                
                input.addEventListener('change', function(e) {
                    filterTable();
                });
                
                if (input.tagName === 'SELECT') {
                    input.addEventListener('change', function(e) {
                        filterTable();
                    });
                }
            });

            // Кнопка сброса фильтров
            var resetBtn = document.getElementById('reset-filters');
            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    filterInputs.forEach(function(input) {
                        if (input.tagName === 'SELECT') {
                            input.selectedIndex = 0;
                        } else {
                            input.value = '';
                        }
                    });
                    filterTable();
                });
            }
        });

    })();
</script>

<style>
    /* Стили для фильтров в заголовках */
    .filters-row th {
        vertical-align: top !important;
        background-color: #f9f9f9 !important;
        padding: 8px 5px !important;
    }
    
    .filters-row .form-control {
        width: 100%;
        font-size: 12px;
        padding: 5px 8px;
        height: 30px;
        border-radius: 3px;
        border: 1px solid #ccc;
        transition: all 0.3s ease;
    }
    
    .filters-row .form-control:focus {
        border-color: #66afe9;
        outline: 0;
        box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102, 175, 233, 0.6);
        background-color: #fff;
    }
    
    .filters-row .form-control:hover {
        border-color: #337ab7;
    }
    
    .filters-row select.form-control {
        padding: 4px 6px;
    }
    
    /* Подсветка активного фильтра */
    .filters-row .form-control:not(:placeholder-shown) {
        background-color: #fff3cd;
        border-color: #ffc107;
    }
    
    /* Стили таблицы */
    .table th {
        background-color: #f5f5f5;
        border-bottom: 2px solid #ddd !important;
        white-space: nowrap;
    }
    
    .table td {
        vertical-align: middle !important;
    }
    
    .btn-xs {
        margin-right: 3px;
    }
    
    /* Bootstrap 3 совместимость */
    .table-responsive {
        border: none;
    }
    
    /* Анимация */
    #table-body tr {
        transition: background-color 0.2s ease;
    }
    
    /* Авто-закрытие алерта */
    #my-alert {
        transition: opacity 0.5s ease;
    }
</style>

<script>
    // Автоматическое закрытие алерта через 5 секунд
    $(function(){
        if($('#my-alert').length) {
            window.setTimeout(function(){
                $('#my-alert').alert('close');
            },5000);
        }
    });
</script>