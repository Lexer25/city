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
		  <div class="input-group date" id="datetimepicker1">
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
 
 console.log('Мой скрипт загружен');
 
      $(function () {
		var dateBegin = new Date();
		dateBegin.setHours(22, 0, 0, 0);
		dateBegin.setMonth(dateBegin.getMonth()+2);
	    //Инициализация datetimepicker1
        $("#datetimepicker1").datetimepicker(
		{language: 'ru', 
		showToday: true,
		sideBySide: true,
		defaultDate: dateBegin
		}
		);
      });

		
  	

$(function () {
    // Инициализация datetimepicker
    var dateBegin = new Date();
    dateBegin.setHours(22, 0, 0, 0);
    dateBegin.setMonth(dateBegin.getMonth()+2);
    $("#datetimepicker1").datetimepicker({
        language: 'ru', 
        showToday: true,
        sideBySide: true,
        defaultDate: dateBegin
    });
});

//переключатель Выделить все
$(document).ready(function() {
    var $masterCheck = $("#check_all");
    var $slaveChecks = $("input[name='id_pep[]']");
    
    // Выделить все
    $masterCheck.off('click').on('click', function() {
        $slaveChecks.prop("checked", $(this).prop("checked"));
    });
    
    // Обновление состояния главного чекбокса
    $slaveChecks.off('click').on('click', function() {
        var total = $slaveChecks.length;
        var checked = $slaveChecks.filter(":checked").length;
        $masterCheck.prop("checked", total === checked);
    });
    
    // Инициализация начального состояния
    var total = $slaveChecks.length;
    var checked = $slaveChecks.filter(":checked").length;
    $masterCheck.prop("checked", total === checked);
});
</script>
		
		
// Проверить, что находит селектор
console.log($(".checkbox").length);          // Сколько элементов с классом checkbox
console.log($("input[name='id_pep[]']").length); // Сколько реальных чекбоксов

// Проверить, что именно попадает под .checkbox
console.log($(".checkbox").get(0).tagName);  // Может быть INPUT или LABEL
</script>
 
    
