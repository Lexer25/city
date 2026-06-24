<?php
/**
 * Страница редактирования типа транспортного сервера
 * @var array $type - данные типа
 * @var bool $is_logged_in - флаг авторизации
 */
?>
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <?php include Kohana::find_file('views', 'alert_line'); ?>
        
        <div class="panel panel-warning">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <?php echo __('Редактирование типа'); ?>
                    <small><?php echo HTML::chars($type['NAME']); ?></small>
                </h3>
            </div>
            <div class="panel-body">
                <?php echo Form::open('ts/control_types'); ?>
                
                <?php echo Form::hidden('id', $type['ID']); ?>
                <?php echo Form::hidden('todo', 'update_type'); ?>
                
                <div class="form-group">
                    <?php echo Form::label('name', __('Название типа')); ?>
                    <?php echo Form::input('name', HTML::chars($type['NAME']), array(
                        'class' => 'form-control',
                        'required' => 'required'
                    )); ?>
                </div>
                
                <div class="form-group">
                    <?php echo Form::label('description', __('Описание')); ?>
                    <?php echo Form::textarea('description', HTML::chars($type['DESCRIPTION']), array(
                        'class' => 'form-control',
                        'rows' => 3,
                        'required' => 'required'
                    )); ?>
                </div>
                
                <div class="form-group">
                    <div class="checkbox">
                        <label>
                            <?php echo Form::checkbox('is_enabled', 1, (bool)$type['IS_ENABLED']); ?>
                            <?php echo __('Активен'); ?>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <span class="glyphicon glyphicon-save"></span> <?php echo __('Сохранить'); ?>
                    </button>
                    <a href="<?php echo URL::site('ts/types'); ?>" class="btn btn-default">
                        <span class="glyphicon glyphicon-remove"></span> <?php echo __('Отмена'); ?>
                    </a>
                </div>
                
                <?php echo Form::close(); ?>
            </div>
        </div>
    </div>
</div>