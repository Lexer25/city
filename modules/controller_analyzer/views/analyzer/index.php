<?php
// modules/controller_analyzer/views/analyzer/index.php
?>
<div class="controller-analyzer">
    <h1>Controller Analyzer</h1>
    
    <div class="stats">
        <h2>Statistics</h2>
        <ul>
            <li>Total Controllers: <?php echo $stats['total_controllers']; ?></li>
            <li>Total Actions: <?php echo $stats['total_actions']; ?></li>
            <li>Controllers with Views: <?php echo $stats['controllers_with_views']; ?></li>
            <li>Total Views: <?php echo $stats['views_count']; ?></li>
            <li>Unique Views: <?php echo $stats['unique_views_count']; ?></li> <!-- Изменили здесь -->
        </ul>
        
        <?php if (!empty($stats['unique_views'])): ?>
        <h3>View Usage:</h3>
        <ul>
            <?php foreach ($stats['unique_views'] as $view => $count): ?>
            <li><?php echo $view; ?>: <?php echo $count; ?> usage(s)</li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
    
    <div class="actions">
        <h2>Actions</h2>
        <a href="<?php echo URL::site('controller-analyzer/map'); ?>" class="btn">View Complete Map</a>
        <form method="POST" action="<?php echo URL::site('controller-analyzer/scan'); ?>" style="display: inline;">
            <button type="submit" class="btn btn-warning">Rescan Controllers</button>
        </form>
        <a href="<?php echo URL::site('controller-analyzer/map?format=json'); ?>" class="btn">JSON Export</a>
        <a href="<?php echo URL::site('controller-analyzer/map?format=xml'); ?>" class="btn">XML Export</a>
    </div>
    
    <?php if (!empty($scan_result)): ?>
    <div class="alert alert-success">
        Scan completed successfully! Found <?php echo count($scan_result); ?> controllers.
    </div>
    <?php endif; ?>
</div>