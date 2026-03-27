<div class="panel panel-primary  ">
  <div class="panel-heading">
    <h3 class="panel-title"><?echo __($title)?></h3>
  </div>
  <div class="panel-body">
	
	<?echo __('total_count').' ';
		echo isset($list)? count($list) : '0';?>	
	
	<?echo Form::open('people/card_late_save_to_file');?>
		<button type="submit" class="btn btn-primary" name="card_late_save_to_file"  value="1"><?echo __('card_late_save_to_file')?></button>
	<?echo Form::close();?>
	
	
	
	<?echo Form::open('people/people_delete', array('class'=>'form-inline'));?>
		
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
			
	<!-- таблица -->	
		 <table id="tablesorter" class="table table-striped table-hover table-super-condensed tablesorter table-bordered">
		
		
		<thead>
		<tr>
			<th class="filter-false" ><?echo __('pp');?></th>
			<th class="filter-false sorter-false" ><label><input type="checkbox" name="id_pep" id="check_all"> </label></th>
			<th><?php echo __('pep_id');?></th>
			<th class="filter-true sorter-true"><?php echo __('name');?></th>
			<th><?php echo __('org_name');?></th>
			
			<th><?php echo __('card');?></th>
			<th><?php echo __('card_date_end');?></th>
			<th><?php echo __('overlate');?></th>
			<th><?php echo __('isactive');?></th>
			
			
		</tr>
		</thead>
		<tbody>
		<?
		$pp=0;
		foreach ($list as $key=>$contact)
		{
			
			 // Получаем значение TIMEEND
                    $timeend = Arr::get($contact, 'TIMEEND');
                    
                    // Безопасный расчет просрочки
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
			echo '<td><label>'.Form::checkbox('id_pep[]', '\''.Arr::get($contact, 'ID_CARD').'\'', FALSE, array('class'=>'checkbox')).'</label></td>';
			echo '<td>'.Arr::get($contact, 'ID_PEP').'</td>';
			echo '<td>'.HTML::anchor('people/peopleInfo/'.Arr::get($contact, 'ID_PEP'),  Arr::get($contact,'SURNAME').' '.Arr::get($contact, 'NAME').' '.Arr::get($contact,'PATRONYMIC')).'</td>';
			
			echo '<td>'.Arr::get($contact, 'ORG_PARENT', __('No_card')).'</td>';
			echo '<td>'.Arr::get($contact, 'ID_CARD', __('No_card')).'</td>';
			//echo '<td>'.date("d.m.Y", strtotime(Arr::get($contact, 'TIMEEND', __('No_card')))).'</td>';
			echo '<td>'.$date_end_display.'</td>';
			echo '<td>' . $months . ' мес. ' . $days . ' дн.</td>';
			echo '<td>'. Arr::get($contact, 'ISACTIVE',0).'</td>';
			
		echo '</tr>';
					
			}
				?>
		</tbody>
	</table>
	
	<!-- Навигация -->
<nav class="navbar navbar-default navbar-fixed-bottom disable" role="navigation">
  <div class="container">
  <div class="row">
  
	<!-- Инициализация виджета "Bootstrap datetimepicker" --> 
		
		<div class="form-group">
		  <div class="input-group date" id="datetimepicker">
			<input type="text" class="form-control" name="timeTo" >
			<span class="input-group-addon">
			  <span class="glyphicon glyphicon-calendar"></span>
			</span>
		  </div>
		</div>


	<button 
	  	type="submit" 
	  	class="btn btn-warning" 
	  	name="people_long"  
	  	value="1" 
	  	<?php if(!Auth::instance()->logged_in()) echo 'disabled'?> onclick="return confirm('<?echo __('people_long_alert')?>') ? true : false;"><?echo __('people_long')?>
	 </button>


	<button 
		  	type="submit" 
		  	class="btn btn-success" 
		  	name="people_unactive"  
		  	value="1" 
		  	<?php if(!Auth::instance()->logged_in()) echo 'disabled'?>
		  	onclick="return confirm('<?echo __('people_unactive_alert')?>') ? true : false;"><?echo __('people_unactive')?>
	</button>
  	  
  	<button type="submit" 
			class="btn btn-danger pull-right" 
			name="card_delete"  
			value="1" 
			<?php if(!Auth::instance()->logged_in()) echo 'disabled'?> onclick="return confirm('<?echo __('people_delete_alert')?>') ? true : false;"><?echo __('card_delete')?>
	</button>
	
	</div>
	</div>
</nav>	
		
<?echo Form::close();?>	
</div>	
</div>
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
        
        if ($btnUnactive.length) {
            if (checked > 0) {
                $btnUnactive.html("Сделать неактивными (" + checked + ")");
                $btnUnactive.prop('disabled', false);
            } else {
                $btnUnactive.html("Сделать неактивными");
                $btnUnactive.prop('disabled', true);
            }
        }
        
        if ($btnDelete.length) {
            if (checked > 0) {
                $btnDelete.html("Удалить карты (" + checked + ")");
                $btnDelete.prop('disabled', false);
            } else {
                $btnDelete.html("Удалить карты");
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
		

 
    
