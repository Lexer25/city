<?php if (!empty($flash_success)): ?>
<div class="alert alert-success">
    <?php echo HTML::chars($flash_success); ?>
</div>
<?php endif; ?>
<?php if (!empty($flash_error)): ?>
<div class="alert alert-danger">
    <?php echo HTML::chars($flash_error); ?>
</div>
<?php endif; ?>
<?php if (!empty($validation_errors)): ?>
<div class="alert alert-danger">
    <strong>Ошибки валидации:</strong>
    <ul>
        <?php foreach ($validation_errors as $field => $error): ?>
        <li><?php echo HTML::chars($error); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<h1>Редактирование типа события #<?php echo $eventtype['ID_EVENTTYPE']; ?></h1>

<form action="<?php echo URL::site('eventConfig/save'); ?>" method="post" class="form-horizontal">
    <input type="hidden" name="ID_EVENTTYPE" value="<?php echo $eventtype['ID_EVENTTYPE']; ?>">
    
    <div class="form-group">
        <label for="name" class="col-sm-2 control-label">Название:</label>
        <div class="col-sm-10">
            <input type="text" id="name" name="NAME" value="<?php echo HTML::chars($eventtype['NAME']); ?>" class="form-control" required>
        </div>
    </div>
    
    <div class="form-group">
        <label for="flag" class="col-sm-2 control-label">Флаг:</label>
        <div class="col-sm-10">
            <input type="number" id="flag" name="FLAG" value="<?php echo $eventtype['FLAG']; ?>" min="0" class="form-control">
        </div>
    </div>
    
    <div class="form-group">
        <label for="color" class="col-sm-2 control-label">Цвет (десятичный):</label>
        <div class="col-sm-10">
            <div class="input-group">
                <input type="number" id="color" name="COLOR" value="<?php echo $eventtype['COLOR']; ?>" min="0" max="16777215" class="form-control" oninput="updateColorPreview()">
                <span class="input-group-addon">
                    <div id="colorPreview" class="color-preview" style="display: inline-block; width: 30px; height: 30px; border: 1px solid #000;"></div>
                </span>
            </div>
            <small class="help-block">Диапазон 0-16777215 (0xFFFFFF)</small>
        </div>
    </div>
    
    <div class="form-group">
        <label for="sound" class="col-sm-2 control-label">Звук (путь к файлу):</label>
        <div class="col-sm-10">
            <input type="text" id="sound" name="SOUND" value="<?php echo HTML::chars($eventtype['SOUND']); ?>" placeholder="например, sound/alert.mp3" class="form-control">
        </div>
    </div>
    
    <div class="form-group">
        <label for="active" class="col-sm-2 control-label">Активен:</label>
        <div class="col-sm-10">
            <select id="active" name="ACTIVE" class="form-control">
                <option value="1" <?php echo $eventtype['ACTIVE'] ? 'selected' : ''; ?>>Да</option>
                <option value="0" <?php echo !$eventtype['ACTIVE'] ? 'selected' : ''; ?>>Нет</option>
            </select>
        </div>
    </div>
    
    <div class="form-group">
        <label for="id_parent" class="col-sm-2 control-label">Родительский тип:</label>
        <div class="col-sm-10">
            <select id="id_parent" name="ID_PARENT" class="form-control">
                <option value="">-- Без родителя --</option>
                <?php foreach ($parents as $parent): ?>
                <option value="<?php echo $parent['ID_EVENTTYPE']; ?>" <?php echo $parent['ID_EVENTTYPE'] == $eventtype['ID_PARENT'] ? 'selected' : ''; ?>>
                    <?php echo HTML::chars($parent['NAME']); ?> (ID: <?php echo $parent['ID_EVENTTYPE']; ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
            <a href="<?php echo URL::site('eventConfig'); ?>" class="btn btn-default">Отмена</a>
        </div>
    </div>
</form>

<script>
function updateColorPreview() {
    var colorVal = document.getElementById('color').value;
    var hex = '#' + ('000000' + parseInt(colorVal).toString(16)).slice(-6);
    document.getElementById('colorPreview').style.backgroundColor = hex;
}
window.onload = updateColorPreview;
</script>