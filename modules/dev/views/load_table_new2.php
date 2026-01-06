<?php $t1=microtime(true);?>
   <!-- <table class="table table-striped table-hover table-condensed">  -->
   <table id="tablesorter" class="table table-striped table-hover table-condensed tablesorter">
   <thead allign="center">

		
		<tr>
			<th>
				Выделить<br><label><input type="checkbox" name="id_dev" id="check_all3"></label>
			</th>
			<?php
			echo '<th>'.__('SERVER_NAME').'</th>'; //2
			echo '<th>'.__('DEVICE_NAME').'</th>'; //21
			echo '<th>'.__('DEVICE_IsActive').'</th>'; //22
			echo '<th>'.__('DEVICE_TYPE').'</th>'; //5
			echo '<th>'.__('IP').'</th>'; //5
			echo '<th>'.__('isOnLine').'</th>'; //5
			echo '<th>'.__('isWp').'</th>'; //50
			echo '<th>'.__('isTest').'</th>'; //52
			echo '<th>'.__('DOOR_NAME').'</th>'; //6
			echo '<th>'.__('DEVICE_VERSION').'</th>'; //8
			echo '<th>'.__('SCUD_MODE').'</th>'; //81
			echo '<th>'.__('BASE_COUNT').'</th>'; //9 количество карт по базе данных
			echo '<th>'.__('DEVICE_COUNT').'</th>'; //90 количество карт в контроллере
			echo '<th>'.__('delta_count').'</th>'; //91
			echo '<th>'.__('DOORSTATE_MODE').'</th>'; //11
			echo '<th>'.__('isBlocked').'</th>'; //12
			echo '<th>'.__('isAlarm').'</th>'; //13
			echo '<th>'.__('time').'</th>'; //14
			echo '<th>'.__('timestamp', array('title'=>'Дата получения информации')).'</th>'; //15
			echo '<th  class="filter-false sorter-false" >'.__('collectAlarm').'</th>'; //15
			?>
			
		</tr>
	
		</thead>
		<tbody>
		<? 
		$tr_class='success';
			
		//	if($deltacard<0) $tr_class='danger';
		//	if($deltacard>0) $tr_class='warning';
			
		foreach ($list as $key => $value)//для каждой точки прохода набираю данные
		{
			$deviceInfo=new DeviceInfo(Arr::get($value, 'ID_DEV'), Arr::get($value, 'FACTS'));
			//echo Debug::vars('47', Arr::get($value, 'FACTS'));//exit;
			//echo Debug::vars('48', $deviceInfo);
			echo '<tr class="'.$tr_class.'">';
				//echo '<td>'.Debug::vars('47', $value, $deviceInfo).'</td>';
				echo '<td><label>'.Form::checkbox('id_dev['.$key.']', $key, FALSE, array('class'=>'checkbox')).'</label></td>'; //1
				echo '<td>2/'.iconv('CP1251', 'UTF-8', Arr::get($value, 'SERVERNAME')).'</td>';
				echo '<td>3/'.iconv('CP1251', 'UTF-8', Arr::get($value, 'DEVNAME')).'</td>';
				echo '<td>4/'.$deviceInfo->ip.'</td>';
				echo '<td>5/'.$deviceInfo->isWP .'</td>';
				echo '<td>6/'.$deviceInfo->ip .'</td>';
				echo '<td>7/'.$deviceInfo->isWP .'</td>';
				echo '<td>8/'.$deviceInfo->isTest .'</td>';
				echo '<td>9/'.$deviceInfo->isWP .'</td>';
				echo '<td>'.iconv('CP1251', 'UTF-8', Arr::get($value, 'DOORNAME')).'</td>';
				echo '<td>10/'.$deviceInfo->softVersion  .'</td>';
				echo '<td>11/'.$deviceInfo->isWP .'</td>';
				echo '<td>12/'.$deviceInfo->isWP .'</td>';
				echo '<td>13/'.$deviceInfo->isWP .'</td>';
				echo '<td>14/'.$deviceInfo->isWP .'</td>';
				echo '<td>15/'.$deviceInfo->isWP .'</td>';
				echo '<td>16/'.$deviceInfo->isWP .'</td>';
				echo '<td>17/'.$deviceInfo->isWP .'</td>';
				echo '<td>18/'.$deviceInfo->isWP .'</td>';
				echo '<td>19/'.$deviceInfo->isWP .'</td>';
				echo '<td>20/'.$deviceInfo->isWP .'</td>';
							
			echo '</tr>';
			

			//exit;
		}
		?>
		</tbody>
	</table>
<?php
echo Debug::vars('162',(microtime(true)-$t1));//exit;
?>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<nav class="navbar navbar-default navbar-fixed-bottom disable" role="navigation">
  <div class="container">
  	<button type="submit" class="btn btn-primary sm" name="synctime" value="1" title = "Синхронизация времени в контроллерах"><?php echo __('synctime_dev')?></button>
	<button type="submit" class="btn btn-primary sm" name="settz"  value="1" title = "Установить временные зоны для выбранных контроллеров"><?php echo __('settz')?></button>
	<button type="submit" class="btn btn-danger sm" name="clear_device"  value="1" title = "Удалить карты из выбранных точек прохода"><?php echo __('clear_device')?></button>
	<button type="submit" class="btn btn-danger sm" name="load_card"  value="1" title = "Загрузить карты в выбранные точки прохода"><?php echo __('load_card')?></button>
	<!--<button type="submit" class="btn btn-info" name="checkStatusOnLine"  value="1" title = "Чтение текущего состояния контроллера он-лайн." disabled="disabled"><?php echo __('checkStatusOnLine')?></button>-->
	<button type="submit" class="btn btn-success  sm" name="checkStatus"  value="1" title = "Чтение состояния и запись данных в базу данных."><?php echo __('checkStatus')?></button>
	<button type="submit" class="btn btn-warning sm" name="readkey"  value="1" title = "Вычитка карт из точки прохода и запись в файл"><?php echo __('Comparekey')?></button>
	<button type="submit" class="btn btn-warning sm" name="cardidx_refresh"  value="1" title = "cardidx_refresh"><?php echo __('cardidx_refresh')?></button>
	
	<?php 
		echo Form::button('control_door', 'Разблокировать', array('value'=>'unlockdoor','class'=>'btn btn-warning', 'type' => 'submit'));
		echo Form::button('control_door', 'Открыть 1 раз', array('value'=>'opendoor','class'=>'btn btn-warning', 'type' => 'submit'));
		echo Form::button('control_door', 'Открыть навсегда', array('value'=>'opendooralways','class'=>'btn btn-warning', 'type' => 'submit'));
		echo Form::button('control_door', 'Закрыть навсегда', array('value'=>'lockdoor','class'=>'btn btn-warning', 'type' => 'submit'));
		
		//echo Form::button('checkStateDoor', 'checkDoorState', array('value'=>'fixDoorState','class'=>'btn btn-success', 'type' => 'submit'));

	?>
	
	</div>
</nav>

<?php echo Form::close();?>		
  </div>
  
 
							  
							
</div>
