<?php
//echo Debug::vars('2', $config_windows);//exit;
//echo Debug::vars('3', $list_windows1);//exit;
?>
<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title"><?php echo __('Панель управления')?></h3>
  </div>
  <div class="panel-body">

	<?php
		if(!(Arr::get($config_windows, 'windows1') 
			or Arr::get($config_windows, 'windows2')
			or Arr::get($config_windows, 'windows3')
			or Arr::get($config_windows, 'windows4')
			or Arr::get($config_windows, 'windows5')
		)) echo  'Вывод информации запрещен в настройках.'.HTML::anchor('setting', 'Настройки');
	?>

    <!-- Панель №1 - Информация по жильцам и картам -->
	<?php if(Arr::get($config_windows, 'windows1')) :?>
    <div class="panel panel-info col-md-3">
        <div class="panel-heading row"><?php echo __('data_people_and_card');?></div>
        <div class="panel-body">
        <?php if (!empty($list_windows1['card'])): ?>
            <?php 
            $card = $list_windows1['card'];
			
			//набор выводимых параметров и ссылок на эти параметры. Если ссылка указана, то она будет подствлеена в html
            $items = array(
                'key_count' => '',
                'key_people' => '',
                'key_people_delete' => '',
                'event_count_24' => '',
                'count_card_late_next_week' => 'people/find_card_late_next_week',
                'count_card_late' => 'people/find_card_late',
                'people_without_card' => 'people/people_without_card',
                'count_unactive_card' => 'people/find_unActiveCard'
            );
            
            foreach ($items as $key => $link):
                if (isset($card[$key])):
                    $value = Arr::get($card[$key], 'count');
                    $name = Arr::get($card[$key], 'name');
            ?>
                <?php echo $name . ' '; ?>
                <?php echo $link ? HTML::anchor($link, $value) : $value; ?>
                <br>
            <?php 
                endif;
            endforeach; 
            ?>
            
            <?php // RFID формат ?>
            <span class="label label-<?php echo $countErrKeyFormatRfid > 0 ? 'danger' : 'success'; ?>">
                <?php echo __('Неправильный формат RFID'); ?>
            </span>
            <?php echo HTML::anchor('dashboard/ErrKeyFormatRfid', $countErrKeyFormatRfid); ?>
            
        <?php else: ?>
            <?php echo __('windows_disable'); ?>
        <?php endif; ?>
        </div>
    </div>
	<?php endif;?>
	
	
    <!-- Панель №2 - Информация по оборудованию -->
	<?php if(Arr::get($config_windows, 'windows2')) :?>
    <div class="panel panel-warning col-md-3">
        <div class="panel-heading row"><?php echo __('data_device');?></div>
        <div class="panel-body">
        <?php if (!empty($list_windows2['device'])): ?>
            <?php foreach (Arr::get($list_windows2, 'device', array()) as $device): ?>
                <?php echo Arr::get($device, 'name') . ' ' . Arr::get($device, 'count'); ?><br>
            <?php endforeach; ?>
        <?php else: ?>
            <?php echo __('windows_disable'); ?>
        <?php endif; ?>
        </div>
    </div>
	<?php endif;?>
    
	<!-- Панель №3 - Очередь загрузок -->
	<?php if(Arr::get($config_windows, 'windows3')) :?>
    <div class="panel panel-success col-md-3">
        <div class="panel-heading row">
            <h3 class="panel-title"><?php echo __('data_cardindev');?></h3>
        </div>
        <div class="panel-body">
        <?php if (!empty($list_windows3['order'])): ?>
            <?php foreach ($list_windows3['order'] as $key => $value): ?>
                <?php echo Arr::get($value, 'name') . ' '; ?>
                <?php if ($key == 'card_for_not_active'): ?>
                    <?php echo HTML::anchor('device/'.$key, Arr::get($value, 'count')); ?>
                <?php else: ?>
                    <?php echo Arr::get($value, 'count'); ?>
                <?php endif; ?>
                <br>
            <?php endforeach; ?>
        <?php else: ?>
            <?php echo __('windows_disable'); ?>
        <?php endif; ?>
        </div>
    </div>
	<?php endif;?>
	
	
    <!-- Панель №4 - Системная информация -->
	<?php if(Arr::get($config_windows, 'windows4')) :?>
    <div class="panel panel-info col-md-3">
        <div class="panel-heading row">
            <h3 class="panel-title"><?php echo __('system_info');?></h3>
        </div>
        <div class="panel-body">
            <?php
            $system_info = array(
                'connectName' => array('Имя', 'CP1251'),
                'dsn' => array('Тип', 'CP1251'),
                'pathDB' => array('БД', 'cp866'),
                'Server' => array('IP', 'CP1251')
            );
            
            foreach ($system_info as $key => $params):
                $value = Arr::path($about, $key, '');
                if ($value):
                    $encoded = iconv($params[1], 'UTF-8//IGNORE', $value);
            ?>
                <?php echo $params[0] . ': ' . $encoded; ?><br>
            <?php 
                endif;
            endforeach; 
            ?>
            
            <?php 
            $events = Arr::path($about, 'countTable.EVENTS', 0);
            echo __('Событий') . ': ' . number_format($events, 0, '', ' ') . '<br>';
            echo __('Min_date') . ': ' . Arr::get($about, 'minEventDate', '');
            ?>
        </div>
    </div>
    
    <div class="clearfix hidden-xs hidden-sm"></div>
	
	<?php endif;?>

    <!-- Панель №5 - Аналитика -->
	<?php if(Arr::get($config_windows, 'windows5')) :?>
    <div class="panel panel-danger">
        <div class="panel-heading">
            <h3 class="panel-title">
                <?php 
                echo __('analyt_result', array(
                    'time_from' => Date::formatted_time('-1 days', "d.m.Y H:i:s"),
                    'time_to' => Date::formatted_time('now', "d.m.Y H:i:s")
                ));
                ?>
            </h3>
        </div>
        <div class="panel-body">
            <?php echo HTML::anchor('event/event_analyt', __('analyt_code_list')); ?>
            
            <?php if (!empty($analyt_result)): ?>
                <table class="table table-striped table-hover table-condensed">
                    <tr>
                        <th><?php echo __('ID_ANALIT');?></th>
                        <th><?php echo __('NAME_ANALYT');?></th>
                        <th><?php echo __('COUNT_EVENT_ANALYT');?></th>
                        <th><?php echo __('DETAIL');?></th>
                    </tr>
                    <?php 
                    $config_analyt_code = Kohana::$config->load('artonitcity_config')->analit_err;
                    foreach ($analyt_result as $data): 
                        $analit = Arr::get($data, 'ANALIT');
                        $class_text = in_array($analit, $config_analyt_code) ? 'text-danger' : 'text-success';
                    ?>
                        <tr class="<?php echo $class_text; ?>">
                            <td><?php echo $analit; ?></td>
                            <td><?php echo __($analit . 'a'); ?></td>
                            <td><?php echo Arr::get($data, 'COUNT'); ?></td>
                            <td><?php echo HTML::anchor('event/event_analyt/' . $analit, __('DETAIL')); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <?php echo __('no_data'); ?>
            <?php endif; ?>
        </div>
    </div>
	<?php endif;?>
    </div>
<?php
//вывод номера сборки
    echo 'app version ' . (defined('CITY_MODULE_VERSION') ? CITY_MODULE_VERSION : 'unknown');
?> 
</div>
