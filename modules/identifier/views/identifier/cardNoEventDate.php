<?php
//echo Debug::vars('2', count($list), $type);exit;
//считаю количество лет, месяцев, дней от текущей даты.
$eventDate = Arr::get($arg, 'event_date');
$diff = Date::span(strtotime($eventDate), time(), 'years,months,days');

$diffText = '';
if ($diff['years'] > 0) {
    $diffText .= $diff['years'] . 'г. ';
}
if ($diff['months'] > 0) {
    $diffText .= $diff['months'] . 'мес. ';
}
if ($diff['days'] > 0 || empty($diffText)) {
    $diffText .= $diff['days'] . 'дн.';
}
?>
<br>
<br>
<br>
<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title"><?php echo __('Список карт, не имеющих отметки о проходе до указанной даты :date (:diff)', array(
        ':date' => $eventDate,
        ':diff' => trim($diffText)
    )); ?></h3>
  </div>

  

 <?php
/* 	$title=array('ID_CARD'
    ,'TIMESTART'
    ,'TIMEEND'
    ,'"ACTIVE"'
    ,'ID_CARDTYPE'
    ,'IDTYPE'
    ,'CREATEDAT'
    ,'ID_PEP'
    ,'FIO'
    ,'ID_ORG'
    ,'ORGNAME'
    ,'ID_PARENT'
    ,'ORGPARENTNAME'); */
	
	$title=array('ID_CARD'
    ,'TIMESTART'
    ,'TIMEEND'
	,'"ACTIVE"'
    ,'IDTYPE'
    ,'CREATEDAT'
    ,'ID_PEP'
    ,'FIO'
    ,'ID_ORG'
    ,'ORGNAME'
    ,'ID_PARENT'
    ,'ORGPARENTNAME'
	,'lastevent');
	
	//$title=array_keys(reset($list));
	
?>	
  <div class="panel-body">
  
	<?	echo __('Всего найдено записей').' ';
		echo isset($total_row_count)? $total_row_count : '0';
	
	echo '<br>';
		
	$show_row=0;
	$show_row=isset($rows_per_page)? $rows_per_page : '0';
	if($total_row_count<$show_row) $show_row=$total_row_count;
	echo __('Из них показаны ').' ';
		echo $show_row;
		
		
	echo '<br>';
		echo __('Для получения всего списка сохраните список в файл. В файле будет полный набор данных.');?>	
	
	<?echo Form::open('identifier/save_csv');
			echo Form::button('todo', __('Сохранить список в файл'), array('value'=>$type,'class'=>'btn btn-primary', 'type' => 'submit'));
			echo Form::hidden('arg', json_encode($arg));//сохраняю параметры выборки для передачи в POST
		
		
		echo Form::close();
		?>
	



	<?echo Form::open('identifier/control', array('class'=>'form-inline', 'id'=>'identifier_control_form'));?>
	
			
		
		 <table id="tablesorter" class="table table-striped table-hover table-condensed tablesorter table-bordered">
		<thead allign="center">
			<tr>
			<th>№ п/п</th>
			<th>
				Выделить<br><label><input type="checkbox" name="identifier" id="check_all"></label>
			</th>
			<?php
	
				foreach($title as $key)
								{
									echo '<th>';
									
										echo $key;
									echo '</th>';
								}
			
		
			?>
			
			</th>
		</thead>
		
		<tbody>
			<?php
			$sn=0;
			foreach($list as $key=>$value)
			{
				//echo Debug::vars('110', $value);exit;
				echo '<tr>';
					echo '<td>';
						echo ++$sn;
					echo '</td>';
				echo '<td>
					<label>'.Form::checkbox('identifier[]', '\''.Arr::get($value, 'ID_CARD').'\'', FALSE, array('class'=>'checkbox')).'</label>
					</td>';
						echo '<td>';
							echo iconv('windows-1251','UTF-8', Arr::get($value, 'ID_CARD'));
						echo '</td>';
					echo '<td>';
							echo iconv('windows-1251','UTF-8', Arr::get($value, 'TIMESTART'));
						echo '</td>';
					echo '<td>';
							echo iconv('windows-1251','UTF-8', Arr::get($value, 'TIMEEND'));
						echo '</td>';
					echo '<td>';
							echo Arr::get($value, 'ACTIVE');
						echo '</td>';
					echo '<td>';
							echo iconv('windows-1251','UTF-8', Arr::get($value, 'IDTYPE'));
						echo '</td>';
					
					echo '<td>';
							echo iconv('windows-1251','UTF-8', Arr::get($value, 'CREATEDAT'));
						echo '</td>';
					
					echo '<td>';
							echo iconv('windows-1251','UTF-8', Arr::get($value, 'ID_PEP'));
						echo '</td>';
					
					echo '<td>';
							echo iconv('windows-1251','UTF-8', Arr::get($value, 'FIO'));
						echo '</td>';
					
					echo '<td>';
							echo iconv('windows-1251','UTF-8', Arr::get($value, 'ID_ORG'));
						echo '</td>';
					
					echo '<td>';
							echo iconv('windows-1251','UTF-8', Arr::get($value, 'ORGNAME'));
						echo '</td>';
					
					echo '<td>';
							echo iconv('windows-1251','UTF-8', Arr::get($value, 'ID_PARENT'));
						echo '</td>';
					
					echo '<td>';
							echo iconv('windows-1251','UTF-8', Arr::get($value, 'ORGPARENTNAME'));
						echo '</td>';
					echo '<td>';
							echo iconv('windows-1251','UTF-8', Arr::get($value, 'lastevent'));
						echo '</td>';
					
				echo '</tr>';
				
				
			}
			
			?>
		
		</tbody>

		
		 </tr>
		</table>
 
	  <nav class="navbar navbar-default navbar-fixed-bottom disable" role="navigation">
  <div class="container">
  <div class="row">



	<button 
		  	type="submit" 
		  	class="btn btn-success" 
		  	name="todo"  
		  	value="unactive" 
		  	<?php if(!Auth::instance()->logged_in()) echo 'disabled'?>
		  	onclick="return confirm('<?echo __('people_unactive_alert')?>') ? true : false;"><?echo __('people_unactive')?>
	</button>
  	  
  	<button type="submit" 
			class="btn btn-danger pull-right" 
			name="todo"  
			value="delete" 
			
			<?php if(!Auth::instance()->logged_in()) echo 'disabled'?> onclick="return confirm('<?echo __('people_delete_alert')?>') ? true : false;"><?echo __('card_delete')?>
	</button>
	
	</div>
	</div>
</nav>						  
							
	</div>
</div>

<script>
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
    
    // Инициализация datetimepicker (если есть на странице)
    if ($("#datetimepicker").length) {
        var dateBegin = new Date();
        dateBegin.setHours(22, 0, 0, 0);
        dateBegin.setMonth(dateBegin.getMonth() + 2);
        $("#datetimepicker").datetimepicker({
            language: 'ru', 
            showToday: true,
            sideBySide: true,
            defaultDate: dateBegin
        });
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
    
    // Обработчик главного чекбокса (выделить всё/снять всё)
    $("#check_all").off('click').on('click', function() {
        var $visible = getVisibleCheckboxes();
        $visible.prop("checked", $(this).prop("checked"));
        updateMasterCheckbox();
    });
    
    // Обработчик дочерних чекбоксов (делегирование для динамических элементов)
    $(document).on('click', '.checkbox', function() {
        updateMasterCheckbox();
    });
    
    // Следим за событиями фильтрации tablesorter
    $("#tablesorter").on('filterEnd', function() {
        setTimeout(function() {
            updateMasterCheckbox();
        }, 50);
    });
    
    // Также обновляем при сортировке (на всякий случай)
    $("#tablesorter").on('sortEnd', function() {
        setTimeout(function() {
            updateMasterCheckbox();
        }, 50);
    });
    
    // Перехват отправки формы - проверяем, что выбраны видимые карты
    $("#identifier_control_form").on('submit', function(e) {
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
        
        // Опционально: отключаем невидимые чекбоксы, чтобы они не отправились на сервер
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
});
</script>


<!-- Информация о времени генерации (скрытая) -->
<span id="time-bottom" style="display:none;">
    <?php 
    $time = isset($exec_time) ? number_format($exec_time, 3) : '0.000';
    echo __('Страница подготовлена за :time сек.', array(':time' => $time)); 
    ?>
</span>