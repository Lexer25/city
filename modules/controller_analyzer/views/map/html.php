<?php
// modules/controller_analyzer/views/map/html.php
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title; ?></title>
    <style>
        .controller-map { font-family: monospace; }
        .controller { background: #f0f0f0; margin: 10px 0; padding: 10px; }
        .controller-name { font-weight: bold; color: #0066cc; }
        .action { margin-left: 20px; padding: 5px; }
        .action-name { color: #009900; }
        .view { color: #cc6600; }
        .method { color: #666666; font-style: italic; }
    </style>
</head>
<body>
    <div class="controller-map">
        <h1>Controller Map</h1>
        
        <?php foreach ($map as $controller_name => $controller_info): ?>
        <div class="controller">
            <div class="controller-name">
                <?php echo $controller_name; ?>
                <span class="class">(<?php echo $controller_info['class']; ?>)</span>
            </div>
            
            <?php foreach ($controller_info['actions'] as $action_name => $action_info): ?>
            <div class="action">
                <span class="action-name"><?php echo $action_name; ?>()</span>
                <?php if (!empty($action_info['view'])): ?>
                <span class="view">→ View: <?php echo $action_info['view']; ?></span>
                <?php endif; ?>
                <span class="method">[<?php echo $action_info['method']; ?>]</span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>