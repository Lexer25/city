<?php
// modules/controller_analyzer/views/template.php
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo isset($title) ? $title : 'Controller Analyzer'; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .controller-analyzer { max-width: 800px; margin: 0 auto; }
        .stats ul { list-style: none; padding: 0; }
        .stats li { padding: 5px 0; border-bottom: 1px solid #eee; }
        .btn { display: inline-block; padding: 8px 15px; margin: 5px; 
               background: #007bff; color: white; text-decoration: none; 
               border-radius: 4px; }
        .btn-warning { background: #ffc107; }
        .alert { padding: 15px; background: #d4edda; color: #155724; 
                 border-radius: 4px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="controller-analyzer">
        <?php echo $content; ?>
    </div>
</body>
</html>