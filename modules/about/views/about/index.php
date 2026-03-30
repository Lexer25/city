<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">Информация о программе</h3>
    </div>
    <div class="panel-body">
        
        <table class="table table-striped">
            <tr>
                <th>Название:</th>
                <td><?php echo htmlspecialchars($developer['name']); ?></td>
            </tr>
            <tr>
                <th>Компания:</th>
                <td><?php echo htmlspecialchars($developer['company']); ?></td>
            </tr>
            <tr>
                <th>Email:</th>
                <td><a href="mailto:<?php echo htmlspecialchars($developer['email']); ?>"><?php echo htmlspecialchars($developer['email']); ?></a></td>
            </tr>
            <tr>
                <th>Веб-сайт:</th>
                <td><a href="<?php echo htmlspecialchars($developer['website_1']); ?>" target="_blank"><?php echo htmlspecialchars($developer['website_1']).'<br>'.htmlspecialchars($developer['website_2']); ?></a></td>
            </tr>
        </table>

        <h4>Текущая версия</h4>
        <p class="lead">Версия: <strong><?php echo htmlspecialchars($current_version); ?></strong></p>

        <h4>История версий</h4>
        <?php if (empty($version_history)): ?>
            <p>История версий пока не доступна.</p>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($version_history as $version_info): ?>
                    <div class="list-group-item">
                        <h5 class="list-group-item-heading">
                            Версия <?php echo htmlspecialchars($version_info['version']); ?>
                            <small class="text-muted"> (от <?php echo htmlspecialchars($version_info['date']); ?>)</small>
                        </h5>
                        <div class="list-group-item-text">
                            <pre><?php echo htmlspecialchars($version_info['changes']); ?></pre>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>