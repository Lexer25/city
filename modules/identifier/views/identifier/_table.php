<?php
// identifier/views/identifier/_table.php
/**
 * Общий шаблон для отображения таблицы карт
 * Доступные переменные:
 * - $list: массив данных для отображения
 * - $headers: массив заголовков таблицы (ключ => отображаемое имя)
 * - $total_row_count: общее количество записей
 * - $rows_per_page: количество отображаемых строк
 * - $type: тип отчета (для экспорта)
 * - $arg: аргументы запроса (для передачи в форму экспорта)
 * - $show_actions: показывать ли панель действий (по умолчанию true)
 * - $custom_info: дополнительная информация над таблицей (опционально)
 */
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">
            <?php echo isset($title) ? htmlspecialchars($title) : htmlspecialchars(__('Список карт')); ?>
        </h3>
    </div>
    
    <div class="panel-body">
        <!-- Информационная панель -->
        <div class="alert alert-info">
            <?php 
            echo __('Всего найдено записей') . ' ' . (isset($total_row_count) ? $total_row_count : '0');
            echo '<br>';
            
            $show_row = 0;
            $show_row = isset($rows_per_page) ? $rows_per_page : '0';
            if (isset($total_row_count) && $total_row_count < $show_row) {
                $show_row = $total_row_count;
            }
            echo __('Из них показаны') . ' ' . $show_row;
            echo '<br>';
            echo __('Для получения всего списка сохраните список в файл. В файле будет полный набор данных.');
            ?>
        </div>
        
        <!-- Кнопка экспорта -->
        <div class="mb-3" style="margin-bottom: 15px;">
            <?php 
            echo Form::open('identifier/save_csv', array('class' => 'form-inline'));
            echo Form::button('todo', __('Сохранить список в файл'), array(
                'value' => isset($type) ? $type : '',
                'class' => 'btn btn-primary',
                'type' => 'submit'
            ));
            
            if (isset($arg)) {
                echo Form::hidden('arg', htmlspecialchars(json_encode($arg)));
            }
            echo Form::close();
            ?>
        </div>
        
        <!-- Дополнительная информация (если передана) -->
        <?php if (isset($custom_info) && !empty($custom_info)): ?>
            <div class="custom-info mb-3" style="margin-bottom: 15px;">
                <?php echo $custom_info; ?>
            </div>
        <?php endif; ?>
        
        <!-- Основная форма с таблицей -->
        <?php echo Form::open('identifier/control', array('class' => 'form-inline', 'id' => 'cards-form')); ?>
        
        <?php if (isset($list) && !empty($list)): ?>
            <div class="table-responsive">
                <table id="tablesorter" class="table table-striped table-hover table-condensed tablesorter table-bordered">
                    <thead>
                        <tr>
                            <th width="50">№</th>
                            <th width="80">
                                <div class="text-center">
                                    <label class="d-block">
                                        Выделить все
                                        <input type="checkbox" id="check_all" class="form-check-input">
                                    </label>
                                </div>
                            </th>
                            <?php foreach ($headers as $header_key => $header_title): ?>
                                <th><?php echo htmlspecialchars($header_title); ?></th>
                            <?php endforeach; ?>
                        </th>
                    </thead>
                    
                    <tbody>
                        <?php 
                        $sn = 0;
                        foreach ($list as $index => $row): 
                            $cardId = isset($row['ID_CARD']) ? $row['ID_CARD'] : '';
                            $safeCardId = htmlspecialchars($cardId, ENT_QUOTES, 'UTF-8');
                        ?>
                            <tr>
                                <td class="text-center"><?php echo ++$sn; ?></td>
                                <td class="text-center">
                                    <label>
                                        <?php echo Form::checkbox('identifier[]', $safeCardId, false, array(
                                            'class' => 'checkbox form-check-input',
                                            'data-card-id' => $safeCardId
                                        )); ?>
                                    </label>
                                </td>
                                <?php foreach (array_keys($headers) as $field): ?>
                                    <td>
                                        <?php
                                        $value = isset($row[$field]) ? $row[$field] : '';
                                        // Преобразование кодировки только если нужно
                                        if (!mb_check_encoding($value, 'UTF-8')) {
                                            $value = iconv('windows-1251', 'UTF-8', $value);
                                        }
                                        echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                                        ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <?php echo htmlspecialchars(__('Нет данных для отображения')); ?>
            </div>
        <?php endif; ?>
        
        <!-- Панель действий -->
        <?php if (isset($show_actions) ? $show_actions : true): ?>
            <div class="card mt-3" style="margin-top: 20px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <?php if (Auth::instance()->logged_in()): ?>
                            <div>
                                <button type="submit" 
                                        class="btn btn-success" 
                                        name="todo"  
                                        value="unactive"
                                        onclick="return confirm('<?php echo htmlspecialchars(addslashes(__('people_unactive_alert'))); ?>')">
                                    <?php echo htmlspecialchars(__('people_unactive')); ?>
                                </button>
                                
                                <button type="submit" 
                                        class="btn btn-danger" 
                                        name="todo"  
                                        value="delete"
                                        disabled
                                        onclick="return confirm('<?php echo htmlspecialchars(addslashes(__('people_delete_alert'))); ?>')">
                                    <?php echo htmlspecialchars(__('card_delete')); ?>
                                </button>
                            </div>
                            
                            <div class="text-muted">
                                <small><?php echo __('Выбрано карт'); ?>: <span id="selected-count">0</span></small>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger w-100">
                                <?php echo htmlspecialchars(__('Для выполнения действий необходимо авторизоваться')); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php echo Form::close(); ?>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    // Проверяем, загрузился ли tablesorter
    console.log('Tablesorter доступен:', typeof $.fn.tablesorter);
    
    // Инициализация tablesorter с фильтрами
    if ($.fn.tablesorter) {
        $("#tablesorter").tablesorter({
            theme: 'blue',
            widthFixed: true,
            widgets: ['filter']
        });
        console.log('Tablesorter инициализирован');
    } else {
        console.log('Tablesorter не загружен');
    }
    
    // ========== РАБОТА С ЧЕКБОКСАМИ С УЧЁТОМ ФИЛЬТРАЦИИ ==========
    
    // Функция получения видимых чекбоксов (только в видимых строках)
    function getVisibleCheckboxes() {
        return $(".checkbox").filter(function() {
            return $(this).closest("tr").is(":visible");
        });
    }
    
    // Обновление состояния главного чекбокса и текста кнопок
    function updateMasterCheckbox() {
        var $visible = getVisibleCheckboxes();
        var total = $visible.length;
        var checked = $visible.filter(":checked").length;
        
        // Обновляем главный чекбокс
        var $masterCheck = $("#check_all");
        $masterCheck.prop("checked", total > 0 && total === checked);
        
        // Добавляем indeterminate состояние (когда выбрана часть)
        if (checked > 0 && checked < total) {
            $masterCheck.prop("indeterminate", true);
        } else {
            $masterCheck.prop("indeterminate", false);
        }
        
        // Обновляем текст кнопки "Сделать неактивными"
        var $btnUnactive = $("button[name='todo'][value='unactive']");
        if ($btnUnactive.length) {
            if (checked > 0) {
                $btnUnactive.html("<?php echo __('people_unactive'); ?> (" + checked + ")");
            } else {
                $btnUnactive.html("<?php echo __('people_unactive'); ?>");
            }
        }
        
        // Обновляем текст кнопки "Удалить карты"
        var $btnDelete = $("button[name='todo'][value='delete']");
        if ($btnDelete.length) {
            if (checked > 0) {
                $btnDelete.html("<?php echo __('card_delete'); ?> (" + checked + ")");
            } else {
                $btnDelete.html("<?php echo __('card_delete'); ?>");
            }
        }
        
        console.log('Обновлено: видимых чекбоксов ' + total + ', выбрано ' + checked);
    }
    
    // Обработчик главного чекбокса
    $("#check_all").off('click').on('click', function() {
        var $visible = getVisibleCheckboxes();
        $visible.prop("checked", $(this).prop("checked"));
        updateMasterCheckbox();
    });
    
    // Обработчик дочерних чекбоксов
    $(document).on('change', '.checkbox', function() {
        updateMasterCheckbox();
    });
    
    // Следим за событиями фильтрации tablesorter
    $("#tablesorter").on('filterEnd', function() {
        setTimeout(function() {
            updateMasterCheckbox();
        }, 50);
    });
    
    // Также обновляем при сортировке
    $("#tablesorter").on('sortEnd', function() {
        setTimeout(function() {
            updateMasterCheckbox();
        }, 50);
    });
    
    // Перехват отправки формы
    $("#cards-form").on('submit', function(e) {
        var $visibleChecked = getVisibleCheckboxes().filter(":checked");
        
        if ($visibleChecked.length === 0) {
            e.preventDefault();
            alert("<?php echo __('Не выбрано ни одной видимой карты!'); ?>");
            return false;
        }
        
        // Для кнопки "Удалить карты" дополнительное подтверждение
        var $clickedButton = $(document.activeElement);
        if ($clickedButton.val() === 'delete') {
            var confirmMsg = "<?php echo __('Будет удалено'); ?> " + $visibleChecked.length + " <?php echo __('карт (только видимые в текущем фильтре). Подтверждаете удаление?'); ?>";
            if (!confirm(confirmMsg)) {
                e.preventDefault();
                return false;
            }
        } else if ($clickedButton.val() === 'unactive') {
            var confirmMsg = "<?php echo __('Будет деактивировано'); ?> " + $visibleChecked.length + " <?php echo __('карт (только видимые в текущем фильтре). Подтверждаете операцию?'); ?>";
            if (!confirm(confirmMsg)) {
                e.preventDefault();
                return false;
            }
        }
        
        // Отключаем невидимые чекбоксы, чтобы они не отправились на сервер
        $(".checkbox").each(function() {
            var $checkbox = $(this);
            if (!$checkbox.closest("tr").is(":visible")) {
                $checkbox.prop('disabled', true);
            }
        });
        
        return true;
    });
    
    // Начальная инициализация
    setTimeout(function() {
        updateMasterCheckbox();
        console.log('Чекбоксы инициализированы с учётом фильтрации');
    }, 100);
    
    // Подсчет выбранных элементов (для обратной совместимости)
    $(document).on('change', '.checkbox', function() {
        updateSelectedCount();
    });
    
    function updateSelectedCount() {
        var selectedCount = $('.checkbox:checked').length;
        $('#selected-count').text(selectedCount);
        
        var hasSelection = selectedCount > 0;
        $('button[name="todo"][value="delete"]').prop('disabled', !hasSelection);
    }
    
    // Инициализация счетчика
    updateSelectedCount();
});
</script>

<?php if (isset($exec_time)): ?>
<!-- Информация о времени генерации -->
<span id="time-bottom" style="display:none;">
    <?php echo __('Страница подготовлена за :time сек.', array(':time' => number_format($exec_time, 3))); ?>
</span>
<?php endif; ?>