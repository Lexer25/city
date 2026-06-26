<?php
/**
 * Страница редактирования транспортного сервера
 * @var array $server - данные сервера
 * @var bool $is_logged_in - флаг авторизации
 */
?>
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <?php include Kohana::find_file('views', 'alert_line'); ?>
        
        <div class="panel panel-warning">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <?php echo __('Редактирование транспортного сервера'); ?>
                    <small><?php echo HTML::chars($server['NAME']); ?></small>
                </h3>
            </div>
            <div class="panel-body">
                <?php echo Form::open('ts/control_servers'); ?>
                
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
                    <?php echo Form::input('ip', Model::factory('Stat')->IntToIP($server['IP']), array(
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
                    <a href="<?php echo URL::site('ts/servers'); ?>" class="btn btn-default">
                        <span class="glyphicon glyphicon-remove"></span> <?php echo __('Отмена'); ?>
                    </a>
                </div>
                
                <?php echo Form::close(); ?>
            </div>
        </div>
    </div>
</div>