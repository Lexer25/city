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

<h1>Типы событий</h1>

<!-- Содержимое табов -->
<div class="tab-content" style="padding-top: 20px;">
    <!-- Закладка 1: Текущий список -->
    <div role="tabpanel" class="tab-pane active" id="tab-list">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Флаг</th>
                    <th>Цвет</th>
                    <th>Записывать в базу данных</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($eventtypes as $event): 
                $color = str_pad(dechex($event['COLOR']), 6, '0', STR_PAD_LEFT);
                ?>
                <tr>
                    <td><?php echo $event['ID_EVENTTYPE']; ?></td>
                    <td><?php echo HTML::chars($event['NAME']); ?></td>
                    <td><?php echo $event['FLAG']; ?></td>
                    <td>
                        <div class="color-box" style="display: inline-block; width: 20px; height: 20px; border: 1px solid #000; background-color: #<?php echo $color; ?>;" title="COLOR=<?php echo $event['COLOR']; ?> hex=#<?php echo $color; ?>"></div>
                        #<?php echo $color; ?>
                        <small class="text-muted">(<?php echo $event['COLOR']; ?>)</small>
                    </td>
                    <td class="<?php echo $event['ACTIVE'] ? 'text-success' : 'text-danger'; ?>">
                        <?php echo $event['ACTIVE'] ? 'Да' : 'Нет'; ?>
                    </td>
                    <td>
                        <button type="button" class="btn btn-primary btn-xs edit-event-btn" 
                                data-id="<?php echo $event['ID_EVENTTYPE']; ?>"
                                data-name="<?php echo htmlspecialchars($event['NAME'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-flag="<?php echo $event['FLAG']; ?>"
                                data-color="<?php echo $event['COLOR']; ?>"
                                data-sound="<?php echo htmlspecialchars($event['SOUND'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-active="<?php echo $event['ACTIVE']; ?>"
                                data-id_parent="<?php echo $event['ID_PARENT']; ?>">
                            Редактировать
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p><small>Всего записей: <?php echo count($eventtypes); ?></small></p>
    </div>

    <!-- Закладка 2: Добавить событие -->
    <div role="tabpanel" class="tab-pane" id="tab-add">
        <?php if (!empty($validation_errors) && empty($old_input['ID_EVENTTYPE'])): ?>
        <div class="alert alert-danger">
            <strong>Ошибки валидации:</strong>
            <ul>
                <?php foreach ($validation_errors as $field => $error): ?>
                <li><?php echo HTML::chars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <h2>Добавление нового типа события</h2>
        <form action="<?php echo URL::site('eventConfig/create'); ?>" method="post" class="form-horizontal">
            <div class="form-group">
                <label for="name" class="col-sm-2 control-label">Название:</label>
                <div class="col-sm-10">
                    <input type="text" id="name" name="NAME" class="form-control" required
                           value="<?php echo isset($old_input['NAME']) ? HTML::chars($old_input['NAME']) : ''; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="flag" class="col-sm-2 control-label">Флаг:</label>
                <div class="col-sm-10">
                    <input type="number" id="flag" name="FLAG" value="<?php echo isset($old_input['FLAG']) ? HTML::chars($old_input['FLAG']) : '0'; ?>" min="0" class="form-control">
                </div>
            </div>
            
            <div class="form-group">
                <label for="color" class="col-sm-2 control-label">Цвет:</label>
                <div class="col-sm-10">
                    <div class="row">
                        <div class="col-xs-7 col-sm-8">
                            <input type="number" id="color" name="COLOR" value="<?php echo isset($old_input['COLOR']) ? HTML::chars($old_input['COLOR']) : '16777215'; ?>" min="0" max="16777215" class="form-control" oninput="updateColorPreview()" placeholder="Десятичный код">
                        </div>
                        <div class="col-xs-5 col-sm-4">
                            <div class="input-group">
                                <input type="text" id="colorHex" class="form-control" placeholder="#FFFFFF" oninput="updateColorFromHex()" maxlength="7">
                                <span class="input-group-addon" style="padding: 0;">
                                    <div id="colorPreview" style="width: 34px; height: 34px; border: 1px solid #ccc; cursor: pointer;" title="Выбрать цвет"></div>
                                </span>
                            </div>
                        </div>
                    </div>
                    <small class="help-block">Введите десятичный код (0-16777215) или HEX (#000000 - #FFFFFF)</small>
                </div>
            </div>
            
            <div class="form-group">
                <label for="sound" class="col-sm-2 control-label">Звук (путь к файлу):</label>
                <div class="col-sm-10">
                    <input type="text" id="sound" name="SOUND" placeholder="например, sound/alert.mp3" class="form-control"
                           value="<?php echo isset($old_input['SOUND']) ? HTML::chars($old_input['SOUND']) : ''; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="active" class="col-sm-2 control-label">Активен:</label>
                <div class="col-sm-10">
                    <select id="active" name="ACTIVE" class="form-control">
                        <option value="1" <?php echo (isset($old_input['ACTIVE']) && $old_input['ACTIVE'] == '1') ? 'selected' : 'selected'; ?>>Да</option>
                        <option value="0" <?php echo (isset($old_input['ACTIVE']) && $old_input['ACTIVE'] == '0') ? 'selected' : ''; ?>>Нет</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="id_parent" class="col-sm-2 control-label">Родительский тип:</label>
                <div class="col-sm-10">
                    <select id="id_parent" name="ID_PARENT" class="form-control">
                        <option value="">-- Без родителя --</option>
                        <?php foreach ($parents as $parent): ?>
                        <option value="<?php echo $parent['ID_EVENTTYPE']; ?>"
                            <?php echo (isset($old_input['ID_PARENT']) && $old_input['ID_PARENT'] == $parent['ID_EVENTTYPE']) ? 'selected' : ''; ?>>
                            <?php echo HTML::chars($parent['NAME']); ?> (ID: <?php echo $parent['ID_EVENTTYPE']; ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" class="btn btn-primary">Создать</button>
                    <button type="reset" class="btn btn-default">Очистить</button>
                </div>
            </div>
        </form>
        
        <script>
        function decToHex(dec) {
            return '#' + ('000000' + parseInt(dec).toString(16)).slice(-6).toUpperCase();
        }
        
        function hexToDec(hex) {
            return parseInt(hex.substring(1), 16);
        }
        
        function updateColorPreview() {
            var colorVal = document.getElementById('color').value;
            if (colorVal === '' || isNaN(colorVal)) colorVal = 16777215;
            var hex = decToHex(colorVal);
            document.getElementById('colorPreview').style.backgroundColor = hex;
            document.getElementById('colorHex').value = hex;
        }
        
        function updateColorFromHex() {
            var hex = document.getElementById('colorHex').value;
            if (!hex.match(/^#[0-9A-Fa-f]{6}$/)) {
                if (hex.match(/^[0-9A-Fa-f]{6}$/)) {
                    hex = '#' + hex;
                } else {
                    return;
                }
            }
            var dec = hexToDec(hex);
            document.getElementById('color').value = dec;
            document.getElementById('colorPreview').style.backgroundColor = hex;
        }
        
        // Открываем цветовой круг через кнопку
        document.getElementById('colorPreview').addEventListener('click', function(e) {
            e.preventDefault();
            // Создаем временный input и сразу кликаем по нему
            var tempInput = document.createElement('input');
            tempInput.type = 'color';
            tempInput.style.position = 'fixed';
            tempInput.style.top = '50%';
            tempInput.style.left = '50%';
            tempInput.style.transform = 'translate(-50%, -50%)';
            tempInput.style.opacity = '0';
            tempInput.style.width = '0';
            tempInput.style.height = '0';
            tempInput.style.border = 'none';
            tempInput.style.padding = '0';
            tempInput.style.margin = '0';
            document.body.appendChild(tempInput);
            
            var currentHex = document.getElementById('colorHex').value;
            if (currentHex.match(/^#[0-9A-Fa-f]{6}$/)) {
                tempInput.value = currentHex;
            } else {
                tempInput.value = '#ffffff';
            }
            
            tempInput.addEventListener('change', function() {
                var hex = tempInput.value;
                var dec = hexToDec(hex);
                document.getElementById('color').value = dec;
                document.getElementById('colorHex').value = hex.toUpperCase();
                document.getElementById('colorPreview').style.backgroundColor = hex;
                document.body.removeChild(tempInput);
            });
            
            tempInput.addEventListener('blur', function() {
                if (document.body.contains(tempInput)) {
                    document.body.removeChild(tempInput);
                }
            });
            
            tempInput.click();
        });
        
        window.onload = function() {
            updateColorPreview();
        };
        
        <?php if (!empty($validation_errors) && empty($old_input['ID_EVENTTYPE'])): ?>
        $(document).ready(function() {
            $('a[href="#tab-add"]').tab('show');
        });
        <?php endif; ?>
        </script>
    </div>

    <!-- Закладка 3: История -->
    <div role="tabpanel" class="tab-pane" id="tab-history">
        <h2>История разработки модуля</h2>
        <p>Модуль "Конфигурация событий" был разработан для системы Artonit City с целью управления типами событий СКУД.</p>
        <p>Основные этапы разработки:</p>
        <ul>
            <li>Версия 1.0 (2023) — базовый функционал: CRUD для типов событий.</li>
            <li>Версия 1.1 (2024) — добавлена валидация, улучшен интерфейс.</li>
            <li>Версия 2.0 (2025) — интеграция с Bootstrap 3, добавлены цветовые предпросмотры.</li>
        </ul>
        <p>Текущая версия: 2.0 (Kohana 3.3, PHP 5.6, Bootstrap 3).</p>
    </div>
</div>

<!-- Стили для простого модального окна -->
<style>
#customModalOverlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    z-index: 10000;
}

#customModal {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 500px;
    max-width: 90%;
    background: #fff;
    border-radius: 5px;
    box-shadow: 0 0 20px rgba(0,0,0,0.3);
    z-index: 10001;
    font-family: Arial, sans-serif;
}

.custom-modal-header {
    padding: 15px;
    border-bottom: 1px solid #ddd;
    background: #f5f5f5;
    border-radius: 5px 5px 0 0;
    overflow: hidden;
}

.custom-modal-header h4 {
    margin: 0;
    float: left;
    font-size: 18px;
}

.custom-modal-header .close {
    float: right;
    font-size: 24px;
    font-weight: bold;
    line-height: 1;
    color: #000;
    opacity: 0.5;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
}

.custom-modal-header .close:hover {
    opacity: 0.8;
}

.custom-modal-body {
    padding: 20px;
    max-height: 70vh;
    overflow-y: auto;
}

.custom-modal-footer {
    padding: 15px;
    border-top: 1px solid #ddd;
    text-align: right;
    background: #f5f5f5;
    border-radius: 0 0 5px 5px;
}

.custom-modal-footer button {
    padding: 6px 12px;
    margin-left: 10px;
    font-size: 14px;
    border-radius: 4px;
    cursor: pointer;
}

.custom-modal-footer .btn-cancel {
    color: #333;
    background: #fff;
    border: 1px solid #ccc;
}

.custom-modal-footer .btn-save {
    color: #fff;
    background: #337ab7;
    border: 1px solid #2e6da4;
}

.custom-modal-body .form-group {
    margin-bottom: 15px;
}

.custom-modal-body label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.custom-modal-body input,
.custom-modal-body select {
    width: 100%;
    padding: 6px 12px;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
}

.custom-modal-body input[readonly] {
    background-color: #f5f5f5;
    cursor: not-allowed;
}

.custom-modal-body .row {
    margin: 0 -5px;
}

.custom-modal-body .col-xs-7,
.custom-modal-body .col-xs-5 {
    padding: 0 5px;
    float: left;
}

.custom-modal-body .col-xs-7 {
    width: 58.33333333%;
}

.custom-modal-body .col-xs-5 {
    width: 41.66666667%;
}

.custom-modal-body .input-group {
    display: flex;
}

.custom-modal-body .input-group input {
    flex: 1;
}

.custom-modal-body .input-group-addon {
    padding: 0;
    background: #eee;
    border: 1px solid #ccc;
    border-left: none;
}

.custom-modal-body .help-block {
    font-size: 12px;
    color: #737373;
    margin-top: 5px;
}

.custom-modal-body .alert {
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 4px;
}

.custom-modal-body .alert-danger {
    color: #a94442;
    background: #f2dede;
    border: 1px solid #ebccd1;
}

/* Стили для цветового круга */
.color-wheel-container {
    text-align: center;
    padding: 10px 0;
}

.color-wheel {
    width: 200px;
    height: 200px;
    border-radius: 50%;
    margin: 0 auto;
    cursor: pointer;
    border: 2px solid #ccc;
    box-shadow: 0 0 10px rgba(0,0,0,0.2);
    transition: transform 0.2s;
}

.color-wheel:hover {
    transform: scale(1.02);
}

.color-wheel canvas {
    border-radius: 50%;
}

.color-preview-large {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    margin: 10px auto;
    border: 2px solid #ddd;
    box-shadow: 0 0 5px rgba(0,0,0,0.2);
}
</style>

<div id="customModalOverlay"></div>
<div id="customModal">
    <div class="custom-modal-header">
        <button type="button" class="close" id="modalCloseBtn">&times;</button>
        <h4>Редактирование типа события</h4>
    </div>
    <form action="<?php echo URL::site('eventConfig/save'); ?>" method="post" id="customEditForm">
        <div class="custom-modal-body">
            <input type="hidden" name="ID_EVENTTYPE" id="custom_edit_id">
            
            <div class="form-group">
                <label for="custom_edit_id_display">Номер события (ID):</label>
                <input type="text" id="custom_edit_id_display" class="form-control" readonly>
            </div>
            
            <div class="form-group">
                <label for="custom_edit_name">Название:</label>
                <input type="text" id="custom_edit_name" name="NAME" required>
            </div>
            
            <div class="form-group">
                <label for="custom_edit_flag">Флаг:</label>
                <input type="number" id="custom_edit_flag" name="FLAG" min="0" value="0">
            </div>
            
            <div class="form-group">
                <label>Цвет:</label>
                <div class="row">
                    <div class="col-xs-7">
                        <input type="number" id="custom_edit_color" name="COLOR" min="0" max="16777215" class="form-control" oninput="updateCustomColorPreview()" placeholder="Десятичный код">
                    </div>
                    <div class="col-xs-5">
                        <div class="input-group">
                            <input type="text" id="custom_edit_hex" class="form-control" placeholder="#FFFFFF" oninput="updateCustomColorFromHex()" maxlength="7">
                            <span class="input-group-addon" style="padding: 0;">
                                <div id="customColorPreview" style="width: 34px; height: 34px; border: 1px solid #ccc; cursor: pointer;" title="Выбрать цвет"></div>
                            </span>
                        </div>
                    </div>
                </div>
                <small class="help-block">Введите десятичный код или HEX</small>
            </div>
            
            <!-- Цветовой круг -->
            <div class="color-wheel-container" id="colorWheelContainer" style="display: none;">
                <div id="colorWheel" class="color-wheel"></div>
                <div id="colorPreviewLarge" class="color-preview-large"></div>
                <button type="button" class="btn btn-sm btn-default" id="closeColorWheel" style="margin-top: 10px;">Закрыть</button>
            </div>
            
            <div class="form-group">
                <label for="custom_edit_sound">Звук (путь к файлу):</label>
                <input type="text" id="custom_edit_sound" name="SOUND" placeholder="например, sound/alert.mp3">
            </div>
            
            <div class="form-group">
                <label for="custom_edit_active">Активен:</label>
                <select id="custom_edit_active" name="ACTIVE">
                    <option value="1">Да</option>
                    <option value="0">Нет</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="custom_edit_id_parent">Родительский тип:</label>
                <select id="custom_edit_id_parent" name="ID_PARENT">
                    <option value="">-- Без родителя --</option>
                    <?php foreach ($parents as $parent): ?>
                    <option value="<?php echo $parent['ID_EVENTTYPE']; ?>">
                        <?php echo HTML::chars($parent['NAME']); ?> (ID: <?php echo $parent['ID_EVENTTYPE']; ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <?php if (!empty($validation_errors) && isset($old_input['ID_EVENTTYPE'])): ?>
            <div class="alert alert-danger">
                <strong>Ошибки валидации:</strong>
                <ul>
                    <?php foreach ($validation_errors as $field => $error): ?>
                    <li><?php echo HTML::chars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="btn-cancel" id="modalCancelBtn">Отмена</button>
            <button type="submit" class="btn-save">Сохранить изменения</button>
        </div>
    </form>
</div>

<!-- Подключаем библиотеку iro.js для цветового круга -->
<script src="https://cdn.jsdelivr.net/npm/@jaames/iro@5"></script>

<script>
var colorPicker = null;
var colorWheelVisible = false;

function decToHexModal(dec) {
    return '#' + ('000000' + parseInt(dec).toString(16)).slice(-6).toUpperCase();
}

function hexToDecModal(hex) {
    return parseInt(hex.substring(1), 16);
}

function updateCustomColorPreview() {
    var colorVal = document.getElementById('custom_edit_color').value;
    if (colorVal === '' || isNaN(colorVal)) colorVal = 16777215;
    var hex = decToHexModal(colorVal);
    document.getElementById('customColorPreview').style.backgroundColor = hex;
    document.getElementById('custom_edit_hex').value = hex;
    if (colorPicker) {
        colorPicker.color.hex = hex;
    }
    if (document.getElementById('colorPreviewLarge')) {
        document.getElementById('colorPreviewLarge').style.backgroundColor = hex;
    }
}

function updateCustomColorFromHex() {
    var hex = document.getElementById('custom_edit_hex').value;
    if (!hex.match(/^#[0-9A-Fa-f]{6}$/)) {
        if (hex.match(/^[0-9A-Fa-f]{6}$/)) {
            hex = '#' + hex;
        } else {
            return;
        }
    }
    var dec = hexToDecModal(hex);
    document.getElementById('custom_edit_color').value = dec;
    document.getElementById('customColorPreview').style.backgroundColor = hex;
    if (colorPicker) {
        colorPicker.color.hex = hex;
    }
    if (document.getElementById('colorPreviewLarge')) {
        document.getElementById('colorPreviewLarge').style.backgroundColor = hex;
    }
}

function showColorWheel() {
    var container = document.getElementById('colorWheelContainer');
    if (!container) return;
    
    container.style.display = 'block';
    colorWheelVisible = true;
    
    if (!colorPicker) {
        colorPicker = new iro.ColorPicker('#colorWheel', {
            width: 200,
            color: document.getElementById('custom_edit_hex').value || '#ffffff',
            borderWidth: 2,
            borderColor: '#ccc',
            layout: [
                {
                    component: iro.ui.Wheel,
                    options: {
                        wheelLightness: true,
                        wheelAngle: 0,
                        wheelDirection: 'clockwise'
                    }
                }
            ]
        });
        
        colorPicker.on('color:change', function(color) {
            var hex = color.hexString;
            var dec = hexToDecModal(hex);
            document.getElementById('custom_edit_color').value = dec;
            document.getElementById('custom_edit_hex').value = hex.toUpperCase();
            document.getElementById('customColorPreview').style.backgroundColor = hex;
            document.getElementById('colorPreviewLarge').style.backgroundColor = hex;
        });
    } else {
        colorPicker.color.hex = document.getElementById('custom_edit_hex').value;
    }
}

function hideColorWheel() {
    var container = document.getElementById('colorWheelContainer');
    if (container) {
        container.style.display = 'none';
    }
    colorWheelVisible = false;
}

function openCustomModal() {
    document.getElementById('customModalOverlay').style.display = 'block';
    document.getElementById('customModal').style.display = 'block';
}

function closeCustomModal() {
    document.getElementById('customModalOverlay').style.display = 'none';
    document.getElementById('customModal').style.display = 'none';
    hideColorWheel();
}

$(document).ready(function() {
    // Навешиваем обработчик на цветной квадратик
    var colorPreview = document.getElementById('customColorPreview');
    if (colorPreview) {
        colorPreview.addEventListener('click', function(e) {
            e.preventDefault();
            if (colorWheelVisible) {
                hideColorWheel();
            } else {
                showColorWheel();
            }
        });
    }
    
    // Закрытие цветового круга
    var closeWheel = document.getElementById('closeColorWheel');
    if (closeWheel) {
        closeWheel.addEventListener('click', function() {
            hideColorWheel();
        });
    }
    
    $('.edit-event-btn').on('click', function(e) {
        e.preventDefault();
        var btn = $(this);
        var id = btn.data('id');
        var color = btn.data('color');
        var hex = decToHexModal(color);
        
        $('#custom_edit_id').val(id);
        $('#custom_edit_id_display').val(id);
        $('#custom_edit_name').val(btn.data('name'));
        $('#custom_edit_flag').val(btn.data('flag'));
        $('#custom_edit_color').val(color);
        $('#custom_edit_hex').val(hex);
        $('#custom_edit_sound').val(btn.data('sound'));
        $('#custom_edit_active').val(btn.data('active'));
        $('#custom_edit_id_parent').val(btn.data('id_parent') ? btn.data('id_parent') : '');
        
        $('#customColorPreview').css('backgroundColor', hex);
        if (colorPicker) {
            colorPicker.color.hex = hex;
        }
        
        openCustomModal();
    });
    
    $('#modalCloseBtn, #modalCancelBtn, #customModalOverlay').on('click', closeCustomModal);
    
    $(document).on('keydown', function(e) {
        if (e.keyCode === 27) closeCustomModal();
    });
    
    <?php if (!empty($validation_errors) && isset($old_input['ID_EVENTTYPE'])): ?>
    var id = '<?php echo isset($old_input['ID_EVENTTYPE']) ? $old_input['ID_EVENTTYPE'] : ''; ?>';
    var color = '<?php echo isset($old_input['COLOR']) ? $old_input['COLOR'] : '16777215'; ?>';
    var hex = decToHexModal(color);
    
    $('#custom_edit_id').val(id);
    $('#custom_edit_id_display').val(id);
    $('#custom_edit_name').val('<?php echo isset($old_input['NAME']) ? addslashes($old_input['NAME']) : ''; ?>');
    $('#custom_edit_flag').val('<?php echo isset($old_input['FLAG']) ? $old_input['FLAG'] : ''; ?>');
    $('#custom_edit_color').val(color);
    $('#custom_edit_hex').val(hex);
    $('#custom_edit_sound').val('<?php echo isset($old_input['SOUND']) ? addslashes($old_input['SOUND']) : ''; ?>');
    $('#custom_edit_active').val('<?php echo isset($old_input['ACTIVE']) ? $old_input['ACTIVE'] : ''; ?>');
    $('#custom_edit_id_parent').val('<?php echo isset($old_input['ID_PARENT']) ? $old_input['ID_PARENT'] : ''; ?>');
    
    $('#customColorPreview').css('backgroundColor', hex);
    if (colorPicker) {
        colorPicker.color.hex = hex;
    }
    
    openCustomModal();
    <?php endif; ?>
});
</script>