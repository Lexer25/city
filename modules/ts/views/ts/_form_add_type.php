<?php
/**
 * Форма добавления нового типа ТС
 */
echo Form::open('ts/control_types');
?>
<div class="panel panel-success">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Добавить тип ТС'); ?></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <?php echo Form::label('name', __('Название типа')); ?>
                    <?php echo Form::input('name', null, array(
                        'class' => 'form-control',
                        'placeholder' => __('Введите название типа'),
                        'required' => 'required'
                    )); ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <?php echo Form::label('description', __('Описание')); ?>
                    <?php echo Form::input('description', null, array(
                        'class' => 'form-control',
                        'placeholder' => __('Введите описание типа')
                    )); ?>
                </div>
            </div>
        </div>
        
        <?php echo Form::button('todo', __('Добавить'), array(
            'value' => 'add_type',
            'class' => 'btn btn-success',
            'type' => 'submit'
        )); ?>
    </div>
</div>
<?php echo Form::close(); ?>