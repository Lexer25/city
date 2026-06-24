<?php
/**
 * Страница редактирования транспортного сервера
 * @var array $server - данные сервера
 * @var array $types - список типов серверов
 * @var bool $is_logged_in - флаг авторизации
 */
?>
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <?php echo View::factory('ts/_form_edit_server', array(
            'server' => $server,
            'types' => $types
        )); ?>
    </div>
</div>