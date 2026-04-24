<?php
// content1.php
// Здесь может быть любая ваша логика
$title = "Контент первой вкладки";
$items = ['Элемент 1', 'Элемент 2', 'Элемент 3'];
?>

<div style="font-family: Arial, sans-serif;">
    <h2><?php echo $title; ?></h2>
    
    <?php if(isset($_GET['action'])): ?>
        <div class="alert">
            Выполнено действие: <?php echo htmlspecialchars($_GET['action']); ?>
        </div>
    <?php endif; ?>
    
    <ul>
        <?php foreach($items as $item): ?>
            <li><?php echo $item; ?></li>
        <?php endforeach; ?>
    </ul>
    
    <form method="GET" action="">
        <input type="text" name="action" placeholder="Введите действие">
        <button type="submit">Отправить</button>
    </form>
    
    <p>Это содержимое из content1.php</p>
</div>

<style>
    .alert {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        padding: 10px;
        margin: 10px 0;
        border-radius: 4px;
    }
</style>