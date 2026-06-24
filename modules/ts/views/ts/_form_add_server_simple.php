<?php
/**
 * Форма добавления нового сервера (без типа)
 */
echo Form::open('ts/control_servers');
?>
<div class="panel panel-success">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Добавить транспортный сервер'); ?></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <?php echo Form::label('name', __('Название')); ?>
                    <?php echo Form::input('name', null, array(
                        'class' => 'form-control',
                        'placeholder' => __('Введите название'),
                        'required' => 'required'
                    )); ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <?php echo Form::label('ip', __('IP-адрес')); ?>
                    <?php echo Form::input('ip', null, array(
                        'class' => 'form-control',
                        'placeholder' => '192.168.1.1',
                        'required' => 'required'
                    )); ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <?php echo Form::label('port', __('Порт')); ?>
                    <?php echo Form::input('port', null, array(
                        'class' => 'form-control',
                        'placeholder' => '8080',
                        'required' => 'required'
                    )); ?>
                </div>
            </div>
        </div>
        
        <?php echo Form::button('todo', __('Добавить'), array(
            'value' => 'add_server',
            'class' => 'btn btn-success',
            'type' => 'submit'
        )); ?>
    </div>
</div>
<?php echo Form::close(); ?>