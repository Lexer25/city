<?php
/**
 * Форма добавления связи сервер-тип
 * @var array $listServers - список серверов
 * @var array $listTypes - список типов
 */
echo Form::open('ts/control_links');
?>
<div class="panel panel-success">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Добавить привязку'); ?></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <?php echo Form::label('id_server', __('Выберите сервер')); ?>
                    <?php 
                    $server_options = array();
                    foreach ($listServers as $server) {
                        $server_options[$server['ID_SERVER']] = HTML::chars($server['NAME']);
                    }
                    echo Form::select('id_server', $server_options, null, array(
                        'class' => 'form-control',
                        'required' => 'required'
                    ));
                    ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <?php echo Form::label('id_type', __('Выберите тип')); ?>
                    <?php 
                    $type_options = array();
                    foreach ($listTypes as $type) {
                        $type_options[$type['ID']] = HTML::chars($type['NAME']);
                    }
                    echo Form::select('id_type', $type_options, null, array(
                        'class' => 'form-control',
                        'required' => 'required'
                    ));
                    ?>
                </div>
            </div>
        </div>
        
        <?php echo Form::button('todo', __('Добавить связь'), array(
            'value' => 'add_link',
            'class' => 'btn btn-success',
            'type' => 'submit'
        )); ?>
    </div>
</div>
<?php echo Form::close(); ?>