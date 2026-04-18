<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавление типа события</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: inline-block; width: 150px; font-weight: bold; }
        input[type="text"], input[type="number"], select { width: 300px; padding: 5px; }
        .color-preview { display: inline-block; width: 30px; height: 30px; border: 1px solid #000; vertical-align: middle; margin-left: 10px; }
        .buttons { margin-top: 20px; }
        .btn { padding: 8px 15px; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
        .btn-save { background: #4CAF50; color: white; }
        .btn-cancel { background: #ccc; color: black; margin-left: 10px; }
    </style>
    <script>
        function updateColorPreview() {
            var colorVal = document.getElementById('color').value;
            var hex = '#' + ('000000' + parseInt(colorVal || 16777215).toString(16)).slice(-6);
            document.getElementById('colorPreview').style.backgroundColor = hex;
        }
        window.onload = updateColorPreview;
    </script>
</head>
<body>
    <h1>Добавление нового типа события</h1>
    
    <form action="<?php echo URL::site('eventConfig/create'); ?>" method="post">
        <div class="form-group">
            <label for="name">Название:</label>
            <input type="text" id="name" name="NAME" required>
        </div>
        
        <div class="form-group">
            <label for="flag">Флаг:</label>
            <input type="number" id="flag" name="FLAG" value="0" min="0">
        </div>
        
        <div class="form-group">
            <label for="color">Цвет (десятичный):</label>
            <input type="number" id="color" name="COLOR" value="16777215" min="0" max="16777215" oninput="updateColorPreview()">
            <div id="colorPreview" class="color-preview"></div>
            <small>Диапазон 0-16777215 (0xFFFFFF), по умолчанию белый</small>
        </div>
        
        <div class="form-group">
            <label for="sound">Звук (путь к файлу):</label>
            <input type="text" id="sound" name="SOUND" placeholder="например, sound/alert.mp3">
        </div>
        
        <div class="form-group">
            <label for="active">Активен:</label>
            <select id="active" name="ACTIVE">
                <option value="1" selected>Да</option>
                <option value="0">Нет</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="id_parent">Родительский тип:</label>
            <select id="id_parent" name="ID_PARENT">
                <option value="">-- Без родителя --</option>
                <?php foreach ($parents as $parent): ?>
                <option value="<?php echo $parent['ID_EVENTTYPE']; ?>">
                    <?php echo HTML::chars($parent['NAME']); ?> (ID: <?php echo $parent['ID_EVENTTYPE']; ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="buttons">
            <button type="submit" class="btn btn-save">Создать</button>
            <a href="<?php echo URL::site('eventConfig'); ?>" class="btn btn-cancel">Отмена</a>
        </div>
    </form>
</body>
</html>