<script type="text/javascript">
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

      $(document).ready(function() {
    	    $("#check_all3").click(function () {
    	         if (!$("#check_all3").is(":checked"))
    	            $(".checkbox").prop("checked",false);
    	        else
    	            $(".checkbox").prop("checked",true);
    	    });
    	});


 
 
 
 
  	$(function() {		
  		$("#tablesorter").tablesorter({sortList:[[0,0]], headers: { 0:{sorter: false}}});
  	});	
	
</script> 
<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title"><?echo __('device_panel_title').' '.date('Y-m-d H:i:s')?></h3>
  </div>
  

  
  <div class="panel-body">
  
     <div class="panel panel-danger">

  <div class="panel-body">
    <?php echo __('device_panel_title_desc', array('date_from'=>$date_stat['min'], 'date_to'=>$date_stat['max']));?>
  </div>
  </div>
  
  <?echo Form::open('Dashboard/load');

		?>

	<button type="submit" class="btn btn-primary" name="refresh"  value="1"><?echo __('refresh')?></button>
<?echo Form::close();	

echo __('load_table', array('count_door'=>count($list)));

echo Form::open('Dashboard/device_control');?>	

   <!-- <table class="table table-striped table-hover table-condensed">  -->
   <table id="tablesorter" class="table table-striped table-hover table-condensed tablesorter">
   <thead allign="center">

		
		<tr>
			<th>
				Выделить<br><label><input type="checkbox" name="id_dev" id="check_all3"></label>
			</th>
			<?php
			echo '<th>'.__('SERVER_NAME').'</th>'; //2
			echo '<th>'.__('DEVICE_NAME').'</th>'; //5
			echo '<th>'.__('DEVICE_TYPE').'</th>'; //5
			echo '<th>'.__('isWP').'</th>'; //51
			echo '<th>'.__('isTest').'</th>'; //52
			echo '<th>'.__('DOOR_NAME').'</th>'; //6
			echo '<th>'.__('DEVICE_VERSION').'</th>'; //8
			echo '<th>'.__('DEVICE_COUNT').'</th>'; //9
			echo '<th>'.__('delta_count').'</th>'; //90
			echo '<th>'.__('DOORSTATE').'</th>'; //11
			echo '<th>'.__('isBlocked').'</th>'; //12
			echo '<th>'.__('isAlarm').'</th>'; //13
			?>
			
		</tr>
		
		<tr align="center">
		<?php
			echo '<td>1</td>';
			echo '<td>2</td>';
			echo '<td>21</td>';
			echo '<td>5</td>';
			echo '<td>51</td>';
			echo '<td>52</td>';
			echo '<td>6</td>';
			echo '<td>8</td>';
			echo '<td>9</td>';
			echo '<td>90</td>';
			echo '<td>11</td>';
			echo '<td>12</td>';
			echo '<td>13</td>';
		?>
			
		</tr>
		
		</thead>
		<tbody>
		<? 
		//echo Debug::vars('26', $_SESSION, $list);
		//echo Debug::vars('26', $list); exit;
		foreach ($list as $key => $value)//для каждой точки прохода набираю данные
		{
			$deviceInfo=new DeviceInfo(Arr::get($value, 'DEVICEINFO'));

			// формирование данных для выдеделния неактивных дверей. Дверь считается неактивной, если у контроллера есть метка Единый список и door=1. В этом случае строка должна быть серой, выбор заперещен
			$tr_class=Arr::get($value, 'TR_COLOR', 'active');
			
			
			if ((Arr::get($value, 'DB_COMMON_LIST') == 1) and ( Arr::get($value, 'ID_READER') == 1 ))// если в БД установлена метка Единый список и door=0, то это точкой прохода управлять нельзя
			{	
				$tr_class = 'active';
				$str_select = '<u title="'.__('title_not_select_in_load_table').'">'.__('off_rus').'</u>';
						
			} else {
						$delta=(Arr::get($value, 'BASE_COUNT_AT_TIME') - Arr::get($value, 'DEVICE_COUNT'));
						$str_select= Form::checkbox('id_dev['.Arr::get($value, 'ID_DOOR').']', Arr::get($value, 'ID_DOOR'), FALSE, array('class'=>'checkbox'));
					};
			
			echo '<tr class="'.$tr_class.'">';
				echo '<td>'.$str_select.'</td>';// 1
				echo '<td>'.__('title_server', array(
					'SERVER_IP'=>Arr::get($value, 'SERVER_IP', 'No data'),
					'SERVER_PORT'=>Arr::get($value, 'SERVER_PORT', 'No data'),
					'SERVER_NAME'=>Arr::get($value, 'SERVER_NAME', 'No data')
					)).'</td>'; //2
				
				echo '<td>';
				echo HTML::anchor('device/deviceInfo/'. Arr::get($value, 'DEVICE_ID'),  Arr::get($value, 'DEVICE_NAME', 'No data'));
				echo '</td>';
				
				echo '<td>';
				
				echo HTML::anchor('device/deviceInfo/'. Arr::get($value, 'DEVICE_ID', 'no'),
					Arr::get($devtypeList,Arr::get($value, 'ID_DEVTYPE')).
					' ('.Arr::get($value, 'ID_DEVTYPE', 'no').')'
					);
					if($deviceInfo->onLine == 1){
						
							
							echo '<br>'
							.HTML::anchor('http://'.$deviceInfo->ip, $deviceInfo->ip, array('target' => '_blank'))
							.'<br>'
							.$deviceInfo->mac;
					} else {
						echo '<br>----';
					}
							
				echo '</td>'; //5
				//echo '<td>'.$deviceInfo->isWP.'</td>';			
				echo '<td>';
					if($deviceInfo->onLine ==1 ) echo Form::checkbox('isWP', 1, $deviceInfo->isWP == 1, array('disabled'=>'disabled'));
				echo '</td>';			
				echo '<td>';
					if($deviceInfo->onLine == 1) echo Form::checkbox('isTest', 1, $deviceInfo->isTest == 1, array('disabled'=>'disabled'));
				echo '</td>';			
				echo '<td>'.HTML::anchor('door/doorInfo/'.Arr::get($value, 'ID_DOOR'), Arr::get($value, 'DOOR_NAME', 'No data')).'</td>'; //6
				//echo '<td>'.Arr::get($value, 'DEVICE_VERSION', 'No data').' '.$deviceInfo->softVersion.'</td>';
				echo '<td>'.$deviceInfo->softVersion.'</td>';
				echo '<td>'.__('count_for_laod_table',
					array('BASE_COUNT_AT_TIME'=>Arr::get($value, 'BASE_COUNT_AT_TIME', 'No data'),
					'DEVICE_COUNT'=>Arr::get($value, 'DEVICE_COUNT', 'No data'),
					'KEYCOUNTTIME'=>Arr::get($value, 'KEYCOUNTTIME', 'No data'),
					'DBKEYCOUNTTIME'=>Arr::get($value, 'DBKEYCOUNTTIME', 'No data')
					)).'</td>';
				
				//90
				//$delta=0;
				if (is_numeric(Arr::get($value, 'BASE_COUNT_AT_TIME')) and is_numeric(Arr::get($value, 'DEVICE_COUNT') ))
				{					
					
					echo '<td>'.$delta.'</td>';
				} else {
					echo '<td>---</td>';
				}
			
				//91 вывод данных о едином списке
				$db_common_list = __('n/a');
				$read_common_list = __('n/a');
				if(Arr::get($value, 'DB_COMMON_LIST') == 1) $db_common_list = __('on_rus');
				if(Arr::get($value, 'DB_COMMON_LIST') == 0) $db_common_list = __('off_rus');
				if (Arr::get($value, 'READ_COMMON_LIST') == 1) $read_common_list = __('on_rus');
				if (Arr::get($value, 'READ_COMMON_LIST') == 0) $read_common_list = __('off_rus');
				$common_list=__('commont_list', array('DB_COMMON_LIST'=>$db_common_list,'READ_COMMON_LIST'=>$read_common_list ));
				$print_common_list = '<span class="label label-danger">'.$common_list.'</span>';//колонка 91
				if(Arr::get($value, 'DB_COMMON_LIST') == Arr::get($value, 'READ_COMMON_LIST')) $print_common_list = '<span class="label label-success">'.$common_list.'</span>';//колонка 91 
				//echo '<td>'.$print_common_list.'</td>';
				//echo '<td>'.Arr::get($value, 'FIXOVERTIMEKEYONDB', 0).'</td>';
				
				$status_device = 'n/a';
				if(Arr::get($value, 'TEST_MODE') == 'TEST_OFF')$status_device = '<span class="label label-success">'.__('test_mode_is_off').'</span>';//колонка 10 
				if(Arr::get($value, 'TEST_MODE') == 'TEST_ON') $status_device =  '<span class="label label-danger">'.__('test_mode_is_on').'</span>';//колонка 10
				
				//10
				//echo '<td>'.$status_device.'</td>';	
				
				echo '<td>';
						//echo Arr::get($value, 'DOORSTATE');
							switch(Arr::get($value, 'DOORSTATE')){
								case 'Fire':
									echo __('Откр всегда').' '. HTML::image("static/images/replace2.png", array('height' => 20, 'alt' => 'Откр всегда'));
								break;
								
								case 'Blocked':
									echo __('Закр всегда').' '. HTML::image("static/images/replace2.png", array('height' => 20, 'alt' => 'Закр всегда'));
								break;
								
								case 'devNotConnect':
									echo __('Нет связи');
								break;
								
								case 'Closed':
									echo __('Рабочий режим');
									//echo HTML::image("static/images/green-check.png", array('height' => 20, 'alt' => 'Рабочий режим'));
								break;
								
								case 'Open':
									echo __('Рабочий режим').' '. HTML::image("static/images/green-check.png", array('height' => 20, 'alt' => 'Рабочий режим'));
								break;
								
								case 'Alarm':
									echo __('Взлом').' '. HTML::image("static/images/docs-point-big2.png", array('height' => 20, 'alt' => 'Взлом'));
								break;
								
								case 'Disabled':
									echo __('Отключен');
								break;
								
								default: //не определено
									echo __('Не определен').' '. HTML::image("static/images/man-says.png", array('height' => 20, 'alt' => 'Не определен'));
								break;
								
				
								
							};
					
				echo '</td>';		
				echo '<td>';
				
				if($deviceInfo->onLine){
					
						if(Arr::get($value, 'ID_READER')==0){
							$block=Arr::get($deviceInfo->inputPortState, 2);
							//$alarm=Arr::get($deviceInfo->inputPortState, 3);
						}
						if(Arr::get($value, 'ID_READER')==1){
							$block=Arr::get($deviceInfo->inputPortState, 6);
							//$alarm=Arr::get($deviceInfo->inputPortState, 7);
						}
						//echo Debug::vars('256', $deviceInfo->inputPortState);
						//exit;
						/* if($block == 0){
							echo HTML::image("static/images/ball.red.png");
						} else {
							echo HTML::image("static/images/ball.green.png");
						} */
						echo Form::checkbox('block', 1, $block==0, array('disabled'=>'disabled'));
						if($block == 0) HTML::image("static/images/attention.png");
					/* 	
						if($alarm == 0){
							echo HTML::image("static/images/dot_red_h.png");
						} else {
							echo HTML::image("static/images/dot_green_n.png");
						} */
					} else {
						// echo HTML::image("static/images/ball.gray.png");
						
					}
					 
				echo '</td>'; 
				echo '<td>';
				
				if($deviceInfo->onLine){
					
						if(Arr::get($value, 'ID_READER')==0){
							//$block=Arr::get($deviceInfo->inputPortState, 2);
							$alarm=Arr::get($deviceInfo->inputPortState, 3);
						}
						if(Arr::get($value, 'ID_READER')==1){
							//$block=Arr::get($deviceInfo->inputPortState, 6);
							$alarm=Arr::get($deviceInfo->inputPortState, 7);
						}
						//echo Debug::vars('256', $deviceInfo->inputPortState);
						//exit;
						/* if($block == 0){
							echo HTML::image("static/images/dot_red_h.png");
						} else {
							echo HTML::image("static/images/dot_green_n.png");
						}
						 */
						/* if($alarm == 0){
							echo HTML::image("static/images/ball.red.png");
						} else {
							echo HTML::image("static/images/ball.green.png");
						} */
						echo Form::checkbox('block', 1, $alarm==0, array('disabled'=>'disabled'));
						if($alarm == 0) HTML::image("static/images/attention.png");
				} else {
						// echo HTML::image("static/images/ball.gray.png");
						
					}	
					
				echo '</td>'; 
				
				
						
				HTML::image("images/nophoto.png", array('height' => 100, 'alt' => 'photo'));
			echo '</tr>';
			

			
		}
		?>
		</tbody>
	</table>

<nav class="navbar navbar-default navbar-fixed-bottom disable" role="navigation">
  <div class="container">
  	<button type="submit" class="btn btn-primary sm" name="synctime" value="1" title = "Синхронизация времени в контроллерах"><?echo __('synctime')?></button>
	<button type="submit" class="btn btn-primary sm" name="settz"  value="1" title = "Установить временные зоны для выбранных контроллеров"><?echo __('settz')?></button>
	<button type="submit" class="btn btn-danger sm" name="clear_device"  value="1" title = "Удалить карты из выбранных точек прохода"><?echo __('clear_device')?></button>
	<button type="submit" class="btn btn-danger sm" name="load_card"  value="1" title = "Загрузить карты в выбранные точки прохода"><?echo __('load_card')?></button>
	<!--<button type="submit" class="btn btn-info" name="checkStatusOnLine"  value="1" title = "Чтение текущего состояния контроллера он-лайн." disabled="disabled"><?echo __('checkStatusOnLine')?></button>-->
	<button type="submit" class="btn btn-success  sm" name="checkStatus"  value="1" title = "Чтение состояния и запись данных в базу данных."><?echo __('checkStatus')?></button>
	<button type="submit" class="btn btn-warning sm" name="readkey"  value="1" title = "Вычитка карт из точки прохода и запись в файл"><?echo __('Comparekey')?></button>
	<button type="submit" class="btn btn-warning sm" name="cardidx_refresh"  value="1" title = "cardidx_refresh"><?echo __('cardidx_refresh')?></button>
	
	<?php 
		echo Form::button('control_door', 'Разблокировать', array('value'=>'unlockdoor','class'=>'btn btn-warning', 'type' => 'submit'));
		echo Form::button('control_door', 'Открыть 1 раз', array('value'=>'opendoor','class'=>'btn btn-warning', 'type' => 'submit'));
		echo Form::button('control_door', 'Открыть навсегда', array('value'=>'opendooralways','class'=>'btn btn-warning', 'type' => 'submit'));
		echo Form::button('control_door', 'Закрыть навсегда', array('value'=>'lockdoor','class'=>'btn btn-warning', 'type' => 'submit'));
		
		echo Form::button('checkStateDoor', 'checkDoorState', array('value'=>'fixDoorState','class'=>'btn btn-success', 'type' => 'submit'));

	?>
	
	</div>
</nav>

<?echo Form::close();?>		
  </div>
</div>
