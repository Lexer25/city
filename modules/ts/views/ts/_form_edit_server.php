<?php
/**
 * Форма редактирования транспортного сервера
 * @var array $server - данные сервера
 * @var array $types - список типов серверов
 */
?>
<div class="panel panel-warning">
    <div class="panel-heading">
        <h3 class="panel-title">
            <?php echo __('Редактирование транспортного сервера'); ?>
            <small><?php echo HTML::chars($server['NAME']); ?></small>
        </h3>
    </div>
    <div class="panel-body">
        <?php echo Form::open('ts/control'); ?>
        
        <?php echo Form::hidden('id', $server['ID_SERVER']); ?>
        <?php echo Form::hidden('todo', 'update_server'); ?>
        
        <div class="form-group">
            <?php echo Form::label('name', __('Название транспортного сервера')); ?>
            <?php echo Form::input('name', HTML::chars($server['NAME']), array(
                'class' => 'form-control',
                'required' => 'required'
            )); ?>
        </div>
        
        <div class="form-group">
            <?php echo Form::label('ip', __('IP-адрес')); ?>
            <?php echo Form::input('ip', $server['IP'], array(
                'class' => 'form-control',
                'placeholder' => '192.168.1.1',
                'required' => 'required'
            )); ?>
            <small class="text-muted"><?php echo __('Введите IP-адрес сервера'); ?></small>
        </div>
        
        <div class="form-group">
            <?php echo Form::label('port', __('Порт')); ?>
            <?php echo Form::input('port', $server['PORT'], array(
                'class' => 'form-control',
                'placeholder' => '8080',
                'required' => 'required'
            )); ?>
            <small class="text-muted"><?php echo __('Введите порт для подключения'); ?></small>
        </div>
        
        <div class="form-group">
            <?php echo Form::label('id_type', __('Тип сервера')); ?>
            <?php 
            // Формируем список типов для выпадающего списка
            $type_options = array();
            foreach ($types as $type) {
                $type_options[$type['ID']] = HTML::chars($type['NAME']);
            }
            echo Form::select('id_type', $type_options, $server['ID_TYPE'], array(
                'class' => 'form-control',
                'required' => 'required'
            )); 
            ?>
            <small class="text-muted"><?php echo __('Выберите тип транспортного сервера'); ?></small>
        </div>
        
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <?php echo Form::checkbox('is_active', 1, (bool)$server['ACTIVE']); ?>
                    <?php echo __('Активен'); ?>
                </label>
            </div>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <span class="glyphicon glyphicon-save"></span> <?php echo __('Сохранить'); ?>
            </button>
            <a href="<?php echo URL::site('ts'); ?>" class="btn btn-default">
                <span class="glyphicon glyphicon-remove"></span> <?php echo __('Отмена'); ?>
            </a>
        </div>
        
        <?php echo Form::close(); ?>
    </div>
</div>