<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('setting.backups_title'); ?></h3>
    </div>
    <div class="panel-body">
        
        <p><a href="<?php echo URL::site('setting'); ?>" class="btn btn-default">← <?php echo __('setting.cancel'); ?></a></p>
        
        <?php if (empty($backups)): ?>
            <div class="alert alert-info"><?php echo __('setting.no_backups'); ?></div>
        <?php else: ?>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Имя файла</th>
                        <th><?php echo __('setting.backup_date'); ?></th>
                        <th><?php echo __('setting.backup_size'); ?></th>
                        <th><?php echo __('setting.backup_actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($backups as $backup): ?>
                    <tr>
                        <td><code><?php echo HTML::chars($backup['filename']); ?></code></td>
                        <td><?php echo $backup['date']; ?></td>
                        <td><?php echo $backup['readable_size']; ?></td>
                        <td>
                            <a href="<?php echo URL::site('setting/restore?file=' . urlencode($backup['file'])); ?>" 
                               class="btn btn-warning btn-sm"
                               onclick="return confirm('<?php echo __('setting.confirm_restore'); ?>')">
                                <span class="glyphicon glyphicon-repeat"></span> <?php echo __('setting.restore'); ?>
                            </a>
                            <a href="<?php echo URL::site('setting/download_backup?file=' . urlencode($backup['file'])); ?>" 
                               class="btn btn-info btn-sm">
                                <span class="glyphicon glyphicon-download"></span> <?php echo __('setting.download'); ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <p class="text-muted">
                <small>Бэкапы хранятся в папке: <code><?php echo realpath(dirname($backups[0]['file'])); ?></code></small>
            </p>
            
        <?php endif; ?>
        
    </div>
</div>