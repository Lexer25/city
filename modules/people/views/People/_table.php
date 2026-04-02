<?php
// people/views/People/_table.php
/**
 * Общий шаблон для отображения таблицы (people: просроченные / скоро истекающие карты).
 * - $people_table_variant: 'card_late' | 'card_late_next_week' (по умолчанию card_late)
 * - $list, $title, $custom_info — см. ниже
 */
ini_set('memory_limit', '256M');
if (!isset($people_table_variant)) {
    $people_table_variant = 'card_late';
}
if (!isset($total_row_count) && isset($list) && is_array($list)) {
    $total_row_count = count($list);
}
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
            echo __('Для получения всего списка сохраните список в файл. В файле будет полный набор данных.');
            ?>
        </div>
        
        <!-- Кнопка экспорта -->
        <div class="mb-3" style="margin-bottom: 15px;">
            <?php 
            echo Form::open('people/save_csv', array('class' => 'form-inline'));
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
        <?php if (isset($custom_info) && !empty($custom_info)) { ?>
            <div class="custom-info mb-3" style="margin-bottom: 15px;">
                <?php echo $custom_info; ?>
            </div>
        <?php } ?>
        
        <!-- Основная форма с таблицей -->
        <?php echo Form::open('identifier/control', array('class' => 'form-inline', 'id' => 'cards-form')); ?>
        
        <?php if (isset($list) && !empty($list)) { ?>
            
            <!-- Пагинация НАД таблицей -->
            <div id="pager-top" class="pager" style="margin-bottom: 15px;">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-default first"><i class="glyphicon glyphicon-step-backward"></i> Первая</button>
                            <button type="button" class="btn btn-sm btn-default prev"><i class="glyphicon glyphicon-backward"></i> Назад</button>
                            <button type="button" class="btn btn-sm btn-default next">Вперед <i class="glyphicon glyphicon-forward"></i></button>
                            <button type="button" class="btn btn-sm btn-default last">Последняя <i class="glyphicon glyphicon-step-forward"></i></button>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-sm-12 text-right">
                        <div class="pagination-info" style="display: inline-block; margin-right: 15px;">
                            <span class="pagedisplay"></span>
                        </div>
                        
                        <div class="pagination-size" style="display: inline-block;">
                            <label style="margin-right: 5px; font-weight: normal;">Показывать:</label>
                            <select class="pagesize form-control input-sm" style="width: auto; display: inline-block;">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="30">30</option>
                                <option value="50" selected>50</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                                <option value="500">500</option>
                            </select>
                        </div>
                        
                        <div class="pagination-goto" style="display: inline-block; margin-left: 15px;">
                            <label style="margin-right: 5px; font-weight: normal;">Страница:</label>
                            <input type="text" class="pagenum form-control input-sm" size="4" style="width: 60px; display: inline-block;">
                        </div>
                    </div>
                </div>
            </div>
           

            <!-- Таблица -->
            <div class="table-responsive">
                 <table id="tablesorter" class="table table-striped table-hover table-super-condensed tablesorter table-bordered">
		
		
		<thead>
		<tr>
			<th class="filter-false" ><?php echo __('pp'); ?></th>
			<th class="filter-false sorter-false" ><label><input type="checkbox" name="id_pep" id="check_all"> </label></th>
			<th><?php echo __('pep_id');?></th>
			<th class="filter-true sorter-true"><?php echo __('name');?></th>
			<th><?php echo __('org_name');?></th>
			
			<th><?php echo __('card');?></th>
			<th><?php echo __('card_date_end');?></th>
			<?php if ($people_table_variant === 'card_late_next_week') { ?>
			<th><?php echo __('overlong');?></th>
			<?php } else { ?>
			<th><?php echo __('overlate');?></th>
			<th><?php echo __('isactive');?></th>
			<?php } ?>
			
			
			</tr>
		</thead>
		<tbody>
		<?php
		$pp=0;
		foreach ($list as $key=>$contact) {
			$timeend = Arr::get($contact, 'TIMEEND');

			if ($people_table_variant === 'card_late_next_week') {
				if ($timeend && $timeend != __('No_card')) {
					$date_end_display = date("d.m.Y H:i", strtotime($timeend));
					$days_left = (int) round((strtotime($timeend) - time()) / 86400);
				} else {
					$date_end_display = __('No_card');
					$days_left = 0;
				}
				echo '<tr>';
				echo '<td>'.$pp++.'</td>';
				echo '<td><label>'.Form::checkbox('identifier[]', Arr::get($contact, 'ID_CARD'), FALSE, array('class'=>'checkbox')).'</label></td>';
				echo '<td>'.Arr::get($contact, 'ID_PEP').'</td>';
				echo '<td>'.HTML::anchor('people/peopleInfo/'.Arr::get($contact, 'ID_PEP'), Arr::get($contact,'SURNAME').' '.Arr::get($contact, 'NAME').' '.Arr::get($contact,'PATRONYMIC')).'</td>';
				echo '<td>'.Arr::get($contact, 'ORG_PARENT', __('No_card')).'</td>';
				echo '<td>'.Arr::get($contact, 'ID_CARD', __('No_card')).'</td>';
				echo '<td>'.$date_end_display.'</td>';
				echo '<td>'.$days_left.' дн.</td>';
				echo '</tr>';
			} else {
				if ($timeend && $timeend != __('No_card')) {
					$overlate = Date::span(strtotime($timeend), time(), 'months,days');
					$months = Arr::get($overlate, 'months', 0);
					$days = Arr::get($overlate, 'days', 0);
					$date_end_display = date("d.m.Y", strtotime($timeend));
				} else {
					$months = 0;
					$days = 0;
					$date_end_display = __('No_card');
				}
				echo '<tr>';
				echo '<td>'.$pp++.'</td>';
				echo '<td><label>'.Form::checkbox('identifier[]', Arr::get($contact, 'ID_CARD'), FALSE, array('class'=>'checkbox')).'</label></td>';
				echo '<td>'.Arr::get($contact, 'ID_PEP').'</td>';
				echo '<td>'.HTML::anchor('people/peopleInfo/'.Arr::get($contact, 'ID_PEP'), Arr::get($contact,'SURNAME').' '.Arr::get($contact, 'NAME').' '.Arr::get($contact,'PATRONYMIC')).'</td>';
				echo '<td>'.Arr::get($contact, 'ORG_PARENT', __('No_card')).'</td>';
				echo '<td>'.Arr::get($contact, 'ID_CARD', __('No_card')).'</td>';
				echo '<td>'.$date_end_display.'</td>';
				echo '<td>'.$months.' мес. '.$days.' дн.</td>';
				echo '<td>'.Arr::get($contact, 'ISACTIVE', 0).'</td>';
				echo '</tr>';
			}
		}
		?>
		</tbody>
	</table>
            </div>
            
            <!-- Пагинация ПОД таблицей -->
            <div id="pager-bottom" class="pager" style="margin-top: 15px;">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-default first"><i class="glyphicon glyphicon-step-backward"></i> Первая</button>
                            <button type="button" class="btn btn-sm btn-default prev"><i class="glyphicon glyphicon-backward"></i> Назад</button>
                            <button type="button" class="btn btn-sm btn-default next">Вперед <i class="glyphicon glyphicon-forward"></i></button>
                            <button type="button" class="btn btn-sm btn-default last">Последняя <i class="glyphicon glyphicon-step-forward"></i></button>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-sm-12 text-right">
                        <div class="pagination-info" style="display: inline-block; margin-right: 15px;">
                            <span class="pagedisplay"></span>
                        </div>
                        
                        <div class="pagination-size" style="display: inline-block;">
                            <label style="margin-right: 5px; font-weight: normal;">Показывать:</label>
                            <select class="pagesize form-control input-sm" style="width: auto; display: inline-block;">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="30">30</option>
                                <option value="50" selected>50</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                                <option value="500">500</option>
                            </select>
                        </div>
                        
                        <div class="pagination-goto" style="display: inline-block; margin-left: 15px;">
                            <label style="margin-right: 5px; font-weight: normal;">Страница:</label>
                            <input type="text" class="pagenum form-control input-sm" size="4" style="width: 60px; display: inline-block;">
                        </div>
                    </div>
                </div>
            </div>
            
        <?php } else { ?>
            <div class="alert alert-warning">
                <?php echo htmlspecialchars(__('Нет данных для отображения')); ?>
            </div>
        <?php } ?>
        
<!-- Панель действий -->
<?php if (isset($show_actions) ? $show_actions : true) { ?>
    <div class="card mt-3" style="margin-top: 20px;">
        <div class="card-body">
            <!-- Блок с датой и кнопками в одном ряду -->
			<?php if (Auth::instance()->logged_in()) { ?>
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="form-inline" style="display: flex; align-items: flex-end; gap: 15px; flex-wrap: wrap;">
                        <!-- Календарь -->
                        <div class="form-group" style="flex: 1; min-width: 200px;">
                            <label for="prolong_date" class="control-label" style="display: block; margin-bottom: 5px;">
                                <?php echo __('Дата'); ?>:
                            </label>
                            <div class="input-group" style="width: 100%;">
                                <?php
                                // Значение по умолчанию - текущая дата + 3 месяца
                                $default_date = date('Y-m-d', strtotime('+3 months'));
                                $prolong_date = isset($prolong_date) ? $prolong_date : $default_date;
                                
                                echo Form::input('prolong_date', $prolong_date, [
                                    'type' => 'date',
                                    'class' => 'form-control date-picker',
                                    'id' => 'prolong_date',
                                    'min' => date('Y-m-d'), // Минимум - сегодня
                                    'max' => date('Y-m-d', strtotime('+5 years')), // Максимум - через 5 лет
                                    'title' => __('Выберите дату')
                                ]);
                                ?>
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                        
                       <!-- Кнопка Продлить -->
						<div class="form-group">
							<button type="submit" 
									class="btn btn-warning" 
									name="todo"  
									value="prolong"
									onclick="return confirmExtend();">
								<span class="glyphicon glyphicon-calendar"></span>
								<?php echo __('Продлить до указанной даты'); ?>
							</button>
						</div>
                        
                        <!-- Кнопка Сделать неактивными -->
                        <div class="form-group">
                            <button type="submit" 
                                    class="btn btn-success" 
                                    name="todo"  
                                    value="unactive"
                                    onclick="return confirmDeactivation();">
                                <span class="glyphicon glyphicon-ok"></span>
                                <?php echo htmlspecialchars(__('people_unactive')); ?>
                            </button>
                        </div>
                        
                        <!-- Кнопка Удалить -->
                        <div class="form-group">
                            <button type="submit" 
                                    class="btn btn-danger" 
                                    name="todo"  
                                    value="delete"
                                    disabled
                                    onclick="return confirmDelete();">
                                <span class="glyphicon glyphicon-trash"></span>
                                <?php echo htmlspecialchars(__('card_delete')); ?>
                            </button>
                        </div>
                        
                        <!-- Счетчик выбранных карт -->
                        <div class="form-group text-muted" style="margin-left: auto;">
                            <small><?php echo __('Выбрано карт'); ?>: <span id="selected-count">0</span></small>
                        </div>
                    </div>
                    <small class="text-muted" style="display: block; margin-top: 5px;">
                        <?php echo __('Рекомендуемая дата: текущая + 3 месяца (' . date('d.m.Y', strtotime('+3 months')) . ')'); ?>
                    </small>
                </div>
            </div>
			
			 <?php } else { ?>
                            <div class="alert alert-danger w-100">
                                <?php echo htmlspecialchars(__('Для выполнения действий необходимо авторизоваться')); ?>
                            </div>
							<?php } ?>
        </div>
    </div>
<?php } ?>
        
        <?php echo Form::close(); ?>
    </div>
</div>

<style type="text/css">
/* Стили для пагинации */
.pager {
    margin: 10px 0;
    padding: 8px;
    background: #f9f9f9;
    border-radius: 4px;
    border: 1px solid #e3e3e3;
}

.pager .btn-group {
    margin-bottom: 5px;
}

.pager .btn-sm {
    padding: 5px 10px;
    font-size: 12px;
    line-height: 1.5;
}

.pager .btn-default {
    color: #333;
    background-color: #fff;
    border-color: #ccc;
}

.pager .btn-default:hover:not(:disabled) {
    background-color: #e6e6e6;
    border-color: #adadad;
}

.pager .btn-default:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

.pager .pagedisplay {
    font-weight: bold;
    margin: 0 10px;
}

.pager select.form-control,
.pager input.form-control {
    margin-left: 5px;
}

.pager label {
    margin-bottom: 0;
    font-weight: normal;
}

/* Стили для таблицы */
.table-responsive {
    overflow-x: auto;
}

.table-bordered {
    border: 1px solid #ddd;
}

.table-bordered > thead > tr > th,
.table-bordered > tbody > tr > td {
    border: 1px solid #ddd;
}

.table-striped > tbody > tr:nth-child(odd) {
    background-color: #f9f9f9;
}

.table-hover > tbody > tr:hover {
    background-color: #f5f5f5;
}

.table-condensed > thead > tr > th,
.table-condensed > tbody > tr > td {
    padding: 5px;
}

/* Стили для чекбоксов */
.form-check-input {
    margin: 0;
    cursor: pointer;
}

.text-center {
    text-align: center;
}
</style>

<script type="text/javascript">
$(document).ready(function() {
    var $table = $("#tablesorter");
    
    // Инициализация tablesorter с пагинацией
    if ($.fn.tablesorter && $.fn.tablesorterPager) {
        $table.tablesorter({
            theme: 'blue',
            widgets: ['zebra', 'filter'],
            widgetOptions: {
                filter_reset: '.reset-filter',
                filter_searchDelay: 300,
                filter_placeholder: { search: 'Поиск...' }
            }
        });
        
        $table.tablesorterPager({
            container: $(".pager"),
            cssGoto: '.pagenum',
            cssPageDisplay: '.pagedisplay',
            cssPageSize: '.pagesize',
            cssFirst: '.first',
            cssPrev: '.prev',
            cssNext: '.next',
            cssLast: '.last',
            output: 'Показано {startRow} - {endRow} из {totalRows} записей',
            page: 0,
            size: 50,
            updateArrows: true
        });
        
        console.log('Пагинация инициализирована');
    }
    
    // ========== РАБОТА С ЧЕКБОКСАМИ ==========
    
    // Функция получения только видимых чекбоксов (с учетом пагинации и фильтрации)
    function getVisibleCheckboxes() {
        return $(".checkbox").filter(function() {
            var $row = $(this).closest("tr");
            return $row.is(":visible");
        });
    }
    
    // Обновление состояния главного чекбокса
   // Обновление состояния главного чекбокса
function updateMasterCheckbox() {
    var $visible = getVisibleCheckboxes();
    var total = $visible.length;
    var checked = $visible.filter(":checked").length;
    
    var $masterCheck = $("#check_all");
    
    if (total === 0) {
        $masterCheck.prop("checked", false);
        $masterCheck.prop("disabled", true);
    } else {
        $masterCheck.prop("disabled", false);
        $masterCheck.prop("checked", total === checked);
    }
    
    if (checked > 0 && checked < total) {
        $masterCheck.prop("indeterminate", true);
    } else {
        $masterCheck.prop("indeterminate", false);
    }
    
    $('#selected-count').text(checked);
    
    // Обновляем текст кнопок
    var $btnUnactive = $("button[name='todo'][value='unactive']");
    var $btnDelete = $("button[name='todo'][value='delete']");
    var $btnExtend = $("button[name='todo'][value='prolong']");
    
    // Кнопка "Сделать неактивными"
    if ($btnUnactive.length) {
        if (checked > 0) {
            $btnUnactive.html("<?php echo htmlspecialchars(__('people_unactive')); ?> (" + checked + ")");
            $btnUnactive.prop('disabled', false);
        } else {
            $btnUnactive.html("<?php echo htmlspecialchars(__('people_unactive')); ?>");
            $btnUnactive.prop('disabled', true);
        }
    }
    
    // Кнопка "Продлить до указанной даты"
    if ($btnExtend.length) {
        if (checked > 0) {
            $btnExtend.html("Продлить до указанной даты (" + checked + ")");
            $btnExtend.prop('disabled', false);
        } else {
            $btnExtend.html("Продлить до указанной даты");
            $btnExtend.prop('disabled', true);
        }
    }
    
    // Кнопка "Удалить карты"
    if ($btnDelete.length) {
        if (checked > 0) {
            $btnDelete.html("<?php echo htmlspecialchars(__('card_delete')); ?> (" + checked + ")");
            $btnDelete.prop('disabled', false);
        } else {
            $btnDelete.html("<?php echo htmlspecialchars(__('card_delete')); ?>");
            $btnDelete.prop('disabled', true);
        }
    }
}
    
    // Переключение всех видимых чекбоксов
    function toggleAllVisibleCheckboxes() {
        var $visible = getVisibleCheckboxes();
        var shouldCheck = $("#check_all").prop("checked");
        $visible.prop("checked", shouldCheck);
        updateMasterCheckbox();
    }
    
    // Обработчик главного чекбокса
    $("#check_all").off('change').on('change', function() {
        toggleAllVisibleCheckboxes();
    });
    
    // Обработчик всех чекбоксов
    $(document).off('change', '.checkbox').on('change', '.checkbox', function() {
        updateMasterCheckbox();
    });
    
    // Обновляем чекбоксы при изменении страницы, фильтрации или сортировке
    $table.on('pagerComplete filterEnd sortEnd', function() {
        setTimeout(function() {
            // Сбрасываем главный чекбокс
            $("#check_all").prop("checked", false);
            $("#check_all").prop("indeterminate", false);
            updateMasterCheckbox();
        }, 50);
    });
    
    // Перехват отправки формы - отправляем только видимые выбранные карты
    $("#cards-form").off('submit').on('submit', function(e) {
        var $visibleChecked = getVisibleCheckboxes().filter(":checked");
        
        if ($visibleChecked.length === 0) {
            e.preventDefault();
            alert("Не выбрано ни одной видимой карты!");
            return false;
        }
        
        var $clickedButton = $(document.activeElement);
        
        if ($clickedButton.val() === 'delete') {
            var confirmMsg = "Будет удалено " + $visibleChecked.length + 
                           " карт (только видимые в текущем фильтре). Подтверждаете удаление?";
            if (!confirm(confirmMsg)) {
                e.preventDefault();
                return false;
            }
        } else if ($clickedButton.val() === 'unactive') {
            var confirmMsg = "Будет деактивировано " + $visibleChecked.length + 
                           " карт (только видимые в текущем фильтре). Подтверждаете операцию?";
            if (!confirm(confirmMsg)) {
                e.preventDefault();
                return false;
            }
        }
        
        // Отключаем все невидимые чекбоксы, чтобы они не отправились на сервер
        $(".checkbox").each(function() {
            var $checkbox = $(this);
            var $row = $checkbox.closest("tr");
            if (!$row.is(":visible")) {
                $checkbox.prop('disabled', true);
            } else {
                $checkbox.prop('disabled', false);
            }
        });
        
        // Снимаем выделение со всех скрытых чекбоксов
        $(".checkbox").filter(function() {
            var $row = $(this).closest("tr");
            return !$row.is(":visible");
        }).prop("checked", false);
        
        return true;
    });
    
    // Начальная инициализация
    setTimeout(function() {
        updateMasterCheckbox();
        console.log('Чекбоксы инициализированы');
    }, 200);
});
</script>

<?php if (isset($exec_time)) { ?>
<!-- Информация о времени генерации -->
<span id="time-bottom" style="display:none;">
    <?php echo __('Страница подготовлена за :time сек.', array(':time' => number_format($exec_time, 3))); ?>
</span>
<?php } ?>