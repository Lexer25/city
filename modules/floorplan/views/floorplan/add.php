<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">Добавление плана объекта</h3>
    </div>
    <div class="panel-body">

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo URL::site('floorplan/add'); ?>" enctype="multipart/form-data">
            <div class="form-group <?php echo isset($errors['name']) ? 'has-error' : ''; ?>">
                <label for="name">Название плана *</label>
                <input type="text" class="form-control" id="name" name="name" 
                       value="<?php echo isset($post['name']) ? htmlspecialchars($post['name']) : ''; ?>" 
                       required>
                <?php if (isset($errors['name'])): ?>
                    <span class="help-block"><?php echo $errors['name']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="description">Описание</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?php echo isset($post['description']) ? htmlspecialchars($post['description']) : ''; ?></textarea>
            </div>

            <div class="form-group <?php echo isset($errors['image']) ? 'has-error' : ''; ?>">
                <label for="image">Изображение плана *</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                <small class="text-muted">Поддерживаются форматы: JPG, PNG, GIF</small>
                <?php if (isset($errors['image'])): ?>
                    <span class="help-block"><?php echo $errors['image']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Добавить</button>
                <a href="<?php echo URL::site('floorplan'); ?>" class="btn btn-default">Отмена</a>
            </div>
        </form>

    </div>
</div>