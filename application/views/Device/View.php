<?php
	//echo Debug::vars('2', $device_data, $devtypeList, $id_dev);
	//echo Debug::vars('3', new Device($id_dev));
	$dev = new Device($id_dev);
?>
<script type="text/javascript">
$(function() {		
  		$("#table1").tablesorter({sortList:[[0,0]], headers: { }});
  	});
	
</script> 	
<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title"><?php echo __('device_data_desc');?></h3>
  </div>
 
  <div class="panel-body">
  

  <div class="form-group">
  	<?php
		
	
	echo Form::open('device/update');
	?>
  
	<table id="table1" class=" table table-striped table-hover table-condensed tablesorter">
	<thead>
		<tr>
			<th><?echo __('SER_NUM');?></th>
			<th><?echo __('device_data_desc');?></th>
			<th><?echo __('VALUE');?></th>
			
						
		</tr>
	</thead>
	<tbody>

		<? 
		$nnum=1;
		echo '<tr>
				<td>'.$nnum++.'</td>'.
				'<td>'.__('id_dev').'</td>'.
				'<td>'.$dev->id.'</td>'.
				
			'</tr>';
			
		echo '<tr>
				<td>'.$nnum++.'</td>'.
				'<td>'.__('DEVICE_NAME').'</td>'.
				'<td>'.Form::input('name', iconv('windows-1251','UTF-8',$dev->name)).'</td>'.
		//КД.-1.3.1.6		
			'</tr>';
			
		echo '<tr>
				<td>'.$nnum++.'</td>'.
				'<td>'.__('id_devtype').'</td>'.
				
				'<td>'.Form::hidden('id_dev', $dev->id).
						Form::select('type', $devtypeList, $dev->type).
						'</td>'.
			'</tr>';
			
		echo '<tr>
				<td>'.$nnum++.'</td>'.
				'<td>'.__('dev_ip').'</td>'.
				
				'<td>'.Form::hidden('id_dev', Arr::get($device_data, 'device_id')).
						Form::input('connectionString', $dev->connectionString).
						'</td>'.
			'</tr>';
			
		

		?>

	</tbody>
	</table>
	<?php 
	echo Form::button('todo', __('devtype_edit2'), array('value'=>'devtype_edit','class'=>'btn btn-success', 'type' => 'submit'));	
			
	
	echo Form::close(); ?>				

</div>	
	
  
</div>
</div>