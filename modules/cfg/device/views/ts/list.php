<? //http://itchief.ru/lessons/bootstrap-3/30-bootstrap-3-tables;
// страница отображения данных по транспортынм серверам
//echo Debug::vars('3', $listTS);
//echo Debug::vars('4', $id_parent);

echo Form::open('ts/control');
$title=array('ID_SERVER', 'NAME', 'IP', 'PORT', 'ACTIVE', 'NAMETYPE');
?>
<div class="panel panel-primary">
			<div class="panel-heading">
				<h3 class="panel-title"><?php echo 'Список зарегистрированных транспортных серверов';?></h3>
			</div>
			<div class="panel-body">
			
				<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title"><?php echo 'Список зарегистрированных транспортных серверов';?></h3>
				</div>
				<div class="panel-body">

				<table class="table table-striped table-hover table-condensed">
					<tr>
						<th><?echo __('№ п/п');?></th>
						<?php
							foreach($title as $key=>$value)
								{
									echo '<td>';
										echo iconv('windows-1251','UTF-8', $value);
									echo '</td>';
								}
						?>
					</tr>
					<?php 
					$i=0;
					$checked='no';
					foreach($listTS as $key=>$value)
					{
						
						echo '<tr>';
							echo '<td>';
								//echo ++$i;
							//	echo Debug::vars('42', $value);exit;
							if($i==0) echo Form::radio('id', Arr::get($value,'ID_SERVER'), FALSE, array('checked'=>$checked));
							if($i>0) echo Form::radio('id', Arr::get($value,'ID_SERVER'), FALSE);
							//echo $i++;
							echo '</td>';
							
							foreach($title as $key2=>$value2)
							{
								echo '<td>';
									//echo iconv('windows-1251','UTF-8', Arr::get($value, $value2));
									echo ($value2 == 'IP')? Model::Factory('Stat')->IntToIP(Arr::get($value, $value2)) : iconv('windows-1251','UTF-8', Arr::get($value, $value2));
								echo '</td>';
							}
						echo '</tr>';
					}
				?>

				</table>	
			<?php if(Auth::Instance()->logged_in())
					{
						echo Form::button('todo', 'Изменить', array('value'=>'edit','class'=>'btn btn-success  btn-xs', 'type' => 'submit'));	
						echo Form::button('todo', 'Удалить', array('value'=>'del','class'=>'btn btn-danger  btn-xs', 'type' => 'submit', 'onclick'=>'return confirm(\''.__('delete').'?\') ? true : false;'));
					} else {
						echo Form::button('todo', 'Изменить', array('value'=>'edit_rubic','class'=>'btn btn-success', 'type' => 'submit'));	
					}?>				
				</div>			
			</div>		
			<?php 

			if(Auth::Instance()->logged_in())
						{?>
					<div class="panel panel-primary">
					  <div class="panel-heading">
						<h3 class="panel-title"><?echo __('Добавить транспортный сервер')?></h3>
					  </div>
					  <div class="panel-body">
					  
						<?
					
						echo __('Название транспортного сервера');
						//echo Form::hidden('id_parent',$id_parent);
						echo Form::input('name',null , array('placeholder'=>'Новый ТС'));
						echo '<br>';
						echo Form::button('todo', 'Добавить', array('value'=>'add','class'=>'btn btn-success', 'type' => 'submit'));	
						?>	

					  </div>

					</div>


				<?php 
					} 
				?>
	</div>			
</div>		

<?php
		echo Form::close();
		echo Form::open('ts/control');
		?>

<div class="panel panel-primary">
	<div class="panel-heading">
		<h3 class="panel-title"><?php echo 'Типы транспортных серверов';?></h3>
	</div>
	<div class="panel-body">
				
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title"><?php echo 'Список зарегистрированных типов транспортных серверов';?></h3>
				</div>
				<div class="panel-body">
			<?php
					$title=array('ID', 'NAME', 'IS_ENABLED', 'DESCRIPTION', 'DATECREATED', 'DATECHANGE');
			?>
			<table class="table table-striped table-hover table-condensed">
					<tr>
						<th><?echo __('№ п/п');?></th>
						<?php
							foreach($title as $key=>$value)
								{
									echo '<td>';
										echo iconv('windows-1251','UTF-8', $value);
									echo '</td>';
								}
						?>
					</tr>
					<?php 
					$n=0;

					foreach($listTsType as $key=>$value)
					{
						
						echo '<tr>';
							echo '<td>';
								echo ++$n;
								
							echo '</td>';
							
							foreach($title as $key2=>$value2)
							{
								echo '<td>';
									echo iconv('windows-1251','UTF-8', Arr::get($value, $value2));
								echo '</td>';
							}
						echo '</tr>';
					}
				?>

				</table>		
						
					
					<?php if(Auth::Instance()->logged_in())
					{
						echo Form::button('todo', Kohana::message('rubic','rp_edit'), array('value'=>'edit','class'=>'btn btn-success  btn-xs', 'type' => 'submit'));	
						echo Form::button('todo', Kohana::message('rubic','rp_del'), array('value'=>'del','class'=>'btn btn-danger  btn-xs', 'type' => 'submit', 'onclick'=>'return confirm(\''.__('delete').'?\') ? true : false;'));
					} else {
						echo Form::button('todo', Kohana::message('rubic','to_view'), array('value'=>'edit_rubic','class'=>'btn btn-success', 'type' => 'submit'));	
					}?>
					
					
				</div>
			</div>
			<?php 

			if(Auth::Instance()->logged_in())
						{?>
					<div class="panel panel-primary">
					  <div class="panel-heading">
						<h3 class="panel-title"><?echo __('Добавить тип')?></h3>
					  </div>
					  <div class="panel-body">
					  
						<?
					
						echo __('Название типа транспортного сервера');
						//echo Form::hidden('id_parent',$id_parent);
						echo Form::input('name',null , array('placeholder'=>'Новый тип'));
						echo '<br>';
						echo Form::button('todo', 'Добавить', array('value'=>'add','class'=>'btn btn-success', 'type' => 'submit', 'disabled'=>'disabled'));	
						
						
						
						?>	

					  </div>

					</div>


				<?php 
					} 
				?>
				
	</div>
</div>

<?php
	echo Form::close();
?>

  