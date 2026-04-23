<?php
echo '<div class="alert alert-info alert-dismissible fade in" role="alert" style="margin-bottom: 20px;">';
echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
echo '<strong>Версия модуля:</strong> ' . EVENTCONFIG_VERSION;
echo '</div>';

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'tab1';
?>

<!-- Закладки Bootstrap 3 -->
<ul class="nav nav-tabs" role="tablist" style="margin-bottom: 25px;">
    <li role="presentation" class="<?php echo $tab == 'tab1' ? 'active' : ''; ?>">
        <a href="?tab=tab1">
            <span class="glyphicon glyphicon-list"></span> Список событий
        </a>
    </li>
    <li role="presentation" class="<?php echo $tab == 'tab2' ? 'active' : ''; ?>">
        <a href="?tab=tab2">
            <span class="glyphicon glyphicon-plus"></span> Добавить событие
        </a>
    </li>
</ul>

<div class="content-wrapper">
    <?php
    // Подключаем соответствующий файл
    switch($tab) {
        case 'tab1':
            include 'eventList.php';
            break;
        case 'tab2':
            include 'add.php';
            break;
        default:
            include 'tab1.php';
    }
    ?>
</div>

<script>
// Сохраняем активную закладку в localStorage
$(document).ready(function() {
    $('.nav-tabs a').on('click', function(e) {
        var activeTab = $(this).attr('href').split('=')[1];
        localStorage.setItem('activeEventConfigTab', activeTab);
    });
    
    // Восстанавливаем последнюю активную закладку
    var savedTab = localStorage.getItem('activeEventConfigTab');
    if (savedTab && savedTab !== '<?php echo $tab; ?>') {
        window.location.href = '?tab=' + savedTab;
    }
});
</script>