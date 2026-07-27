<?php
/**
 * Главная страница модуля mancard - файловый менеджер с категориями доступа
 */
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<style>
/* Стили для отображения карт */
.card-badge {
    display: inline-block;
    padding: 1px 6px;
    margin: 1px 2px;
    font-size: 9px;
    font-weight: bold;
    border-radius: 3px;
    line-height: 1.2;
    white-space: nowrap;
}
.card-badge-rfid {
    background-color: #d9edf7;
    color: #31708f;
    border: 1px solid #bce8f1;
}
.card-badge-fp {
    background-color: #fcf8e3;
    color: #8a6d3b;
    border: 1px solid #faebcc;
}
.card-badge-barcode {
    background-color: #dff0d8;
    color: #3c763d;
    border: 1px solid #d6e9c6;
}
.card-badge-grz {
    background-color: #f5f5f5;
    color: #333;
    border: 1px solid #ddd;
}
.card-badge-faceid {
    background-color: #e8d5f5;
    color: #6f2c8a;
    border: 1px solid #d4b8e8;
}
.card-badge-default {
    background-color: #eee;
    color: #666;
    border: 1px solid #ddd;
}
.card-badge-inactive {
    opacity: 0.5;
    text-decoration: line-through;
}
.card-badge .fa {
    margin-right: 2px;
    font-size: 8px;
}
.tree-item-person .cards-container {
    display: inline-block;
    margin-left: 5px;
}
.tree-item-person .cards-container .card-badge {
    font-size: 8px;
    padding: 1px 4px;
}
.tree-item-person .person-name {
    font-weight: 500;
}
.tree-item-person .person-post {
    color: #999;
    font-size: 11px;
    margin-left: 5px;
}
.tree-item-person .person-status-inactive {
    opacity: 0.6;
}

/* Стили для файлового дерева */
.file-tree {
    list-style: none;
    padding-left: 0;
    margin: 0;
}
.file-tree ul {
    list-style: none;
    padding-left: 20px;
    margin: 0;
}
.tree-node {
    margin: 1px 0;
}
.tree-item {
    padding: 3px 8px;
    border-radius: 3px;
    display: flex;
    align-items: center;
    cursor: default;
    transition: background 0.15s;
    position: relative;
}
.tree-item:hover {
    background: #f0f0f0;
}
.tree-item .tree-toggle {
    width: 20px;
    cursor: pointer;
    text-align: center;
    flex-shrink: 0;
}
.tree-item .tree-toggle .fa {
    font-size: 14px;
}
.tree-item .item-name {
    margin-left: 3px;
    flex: 1;
}
.tree-item .org-actions {
    display: none;
}
.tree-item:hover .org-actions {
    display: block;
}
.tree-item .org-actions .btn {
    padding: 0 3px;
    font-size: 11px;
}
.tree-children {
    padding-left: 20px;
}
.tree-node .tree-children {
    border-left: 1px dashed #ddd;
    margin-left: 8px;
    padding-left: 12px;
}

/* Tooltip для отображения ID */
.tree-item .id-tooltip {
    display: none;
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: #2c3e50;
    color: white;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 10px;
    font-family: monospace;
    white-space: nowrap;
    z-index: 100;
    opacity: 0.9;
}
.tree-item:hover .id-tooltip {
    display: block;
}

/* Стили для панелей */
.main-container {
    min-height: 500px;
}
.org-panel {
    height: 100%;
}
.org-panel .panel-body {
    height: 100%;
}
.move-list {
    border: 1px solid #e7e7e7;
    border-radius: 4px;
    background: #fafafa;
}

/* Панель информации о сотруднике */
.info-panel {
    min-height: 300px;
}
.info-panel .panel-body {
    padding: 15px;
}
.info-item {
    padding: 5px 0;
    border-bottom: 1px solid #f0f0f0;
}
.info-item:last-child {
    border-bottom: none;
}
.info-item .label {
    display: inline-block;
    width: 120px;
    font-weight: 600;
    color: #555;
}
.info-item .value {
    color: #333;
}

/* Панель категорий доступа */
.access-panel {
    min-height: 300px;
}
.access-panel .panel-body {
    padding: 15px;
    max-height: 500px;
    overflow-y: auto;
}
.access-item {
    padding: 3px 0;
    border-bottom: 1px solid #f5f5f5;
}
.access-item:last-child {
    border-bottom: none;
}
.access-item .checkbox {
    margin: 0;
}
.access-item .checkbox label {
    font-weight: normal;
    cursor: pointer;
    width: 100%;
}
.access-item .checkbox input[type="checkbox"] {
    margin-right: 8px;
}
.access-item.different {
    background-color: #fff3cd;
    border-left: 3px solid #ffc107;
    padding-left: 5px;
}
.access-item.different .checkbox label {
    color: #856404;
}
.access-item .badge-org {
    background-color: #5bc0de;
    color: white;
    font-size: 9px;
    padding: 1px 6px;
    border-radius: 10px;
    margin-left: 5px;
}
.access-item .badge-person {
    background-color: #5cb85c;
    color: white;
    font-size: 9px;
    padding: 1px 6px;
    border-radius: 10px;
    margin-left: 5px;
}
.access-header {
    padding: 8px 12px;
    background: #f9f9f9;
    border-bottom: 2px solid #e7e7e7;
    font-weight: 600;
    font-size: 12px;
    color: #555;
}

/* Footer с ID информацией */
.org-footer {
    padding: 5px 10px;
    background: #f8f9fa;
    border-top: 1px solid #e7e7e7;
    font-size: 11px;
}
.org-footer .id-info {
    color: #2c3e50;
}
.org-footer .id-info strong {
    color: #337ab7;
}
.org-footer .id-info .label-id {
    display: inline-block;
    background: #e7e7e7;
    padding: 0 6px;
    border-radius: 2px;
    font-family: monospace;
    font-size: 11px;
    color: #555;
}

/* Анимация */
.tree-children {
    transition: all 0.3s ease;
}

/* Стили для поиска */
#org-search, #person-search {
    font-size: 12px;
    height: 28px;
}
#org-search:focus, #person-search:focus {
    border-color: #66afe9;
    box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102, 175, 233, .6);
}
.input-group-addon {
    padding: 0 8px;
    font-size: 12px;
}
#person-search.searching {
    background-image: url('data:image/gif;base64,R0lGODlhEAAQAPIAAP///wAAAMLCwkJCQgAAAGJiYoKCgpKSkiH/C05FVFNDQVBFMi4wAwEAAAAh/hpDcmVhdGVkIHdpdGggYWpheGxvYWQuaW5mbwAh+QQJCgAAACwAAAAAEAAQAAADMwi63P4wyklrE2MIOggZnAdOmGYJRbExwroUmcG2LmDEwnHQLVsYOd2mBzkYDAdKa+dIAAAh+QQJCgAAACwAAAAAEAAQAAADNAi63P5OjCEgG4QMu7DmikRxQlFUYDEZIGBMRVsaqHwctXXf7WEYB4Ag1xjihkMZsiUkKhIAIfkECQoAAAAsAAAAABAAEAAAAzYIujIjK8pByJDMlFYvBoVjHA70GU7xSUJhmKtwHPAKzLO9HMaoKwJZ7Rf8AYPDDzKpZBqfvwQAIfkECQoAAAAsAAAAABAAEAAAAzMIumIlK8oyhpHsnFZfhYumCYUhDAQxRIdhHBGqRoKw0R8DYlJd8z0fMDgsGo/IpHI5TAAAIfkECQoAAAAsAAAAABAAEAAAAzIIunInK0rnZBTwGPNMgQwmdsNgXGJUlIWEuR5oWUIpz8pAEAMe6TwfwyYsGo/IpFKSAAAh+QQJCgAAACwAAAAAEAAQAAADMwi6IMKQORfjdOe82p4wGccc4CEuQradylesojEMBgsUc2G7sDX3lQGBMLAJibufbSlKAAAh+QQJCgAAACwAAAAAEAAQAAADMgi63P7wCRHZnFVdmgHu2nFwlWVs3XjUEUrgXGcUU0zwOMADhlSgH1R4H2g7E1xTp0kRlgAAIfkECQoAAAAsAAAAABAAEAAAAzIIunInK0rnZBTwGPNMgQwmdsNgXGJUlIWEuR5oWUIpz8pAEAMe6TwfwyYsGo/IpFKSAAAh+QQJCgAAACwAAAAAEAAQAAADMwi6IMKQORfjdOe82p4wGccc4CEuQradylesojEMBgsUc2G7sDX3lQGBMLAJibufbSlKAAA7');
    background-repeat: no-repeat;
    background-position: right 8px center;
    background-size: 16px;
}

/* Адаптивность */
@media (max-width: 992px) {
    .org-panel {
        margin-bottom: 15px;
    }
    .info-panel {
        margin-top: 15px;
    }
}

/* Стили для результатов поиска */
#search-results {
    display: none;
}
#search-results-list li {
    padding: 4px 8px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    font-size: 13px;
}
#search-results-list li:hover {
    background: #e8f4fd;
}
#search-results-list li .result-org {
    color: #999;
    font-size: 11px;
    margin-left: 8px;
}
#search-results-list li .result-id {
    color: #999;
    font-size: 10px;
    margin-left: 5px;
    font-family: monospace;
}


/* Стили для результатов поиска */
#search-results-list li .result-card {
    color: #5bc0de;
    font-size: 11px;
    margin-left: 8px;
}
#search-results-list li .result-card .fa {
    margin-right: 2px;
}

</style>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">
            <i class="fa fa-sitemap"></i>
            <?php echo __('Управление организациями и сотрудниками'); ?>
            <div class="pull-right">
                <button class="btn btn-xs btn-default" id="btn-switch-to-move">
                    <i class="fa fa-exchange"></i> <?php echo __('Массовое перемещение'); ?>
                </button>
                <button class="btn btn-xs btn-success" id="btn-save-access" style="display:none;">
                    <i class="fa fa-save"></i> <?php echo __('Сохранить категории'); ?>
                </button>
            </div>
        </h3>
    </div>
    <div class="panel-body">
        
        <div class="row main-container">
            <!-- Левая панель - дерево организаций -->
            <div class="col-md-4">
                <div class="panel panel-default org-panel">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <i class="fa fa-building-o"></i>
                            <?php echo __('Организации'); ?>
                            <span class="pull-right">
                                <button class="btn btn-xs btn-success" id="btn-add-org" title="<?php echo __('Новая организация'); ?>">
                                    <i class="fa fa-plus"></i>
                                </button>
                                <button class="btn btn-xs btn-info" id="btn-expand-all" title="<?php echo __('Развернуть всё'); ?>">
                                    <i class="fa fa-plus-square-o"></i>
                                </button>
                                <button class="btn btn-xs btn-warning" id="btn-collapse-all" title="<?php echo __('Свернуть всё'); ?>">
                                    <i class="fa fa-minus-square-o"></i>
                                </button>
                            </span>
                        </h4>
                    </div>
                    <div class="panel-body" style="padding: 5px;">
                       
                        <!-- Строки поиска -->
<!-- Строки поиска -->
<!-- Строки поиска -->
<div class="form-group" style="margin-bottom: 5px;">
    <div class="input-group input-group-sm" style="margin-bottom: 3px;">
        <span class="input-group-addon"><i class="fa fa-building-o"></i></span>
        <input type="text" class="form-control" id="org-search" placeholder="<?php echo __('Поиск организаций...'); ?>">
    </div>
    <div class="input-group input-group-sm" style="margin-bottom: 3px;">
        <span class="input-group-addon"><i class="fa fa-users"></i></span>
        <input type="text" class="form-control" id="person-search" placeholder="<?php echo __('Поиск сотрудников...'); ?>">
    </div>
    <div class="input-group input-group-sm">
        <span class="input-group-addon"><i class="fa fa-id-card-o"></i></span>
        <input type="text" class="form-control" id="card-search" placeholder="<?php echo __('Поиск по идентификатору...'); ?>">
    </div>
</div>

<!-- === НОВЫЙ КОНТЕЙНЕР ДЛЯ РЕЗУЛЬТАТОВ ПОИСКА === -->
<div id="search-results" style="display: none; margin-bottom: 10px; max-height: 200px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; background: #fafafa;">
    <div class="panel-heading" style="background: #f5f5f5; padding: 5px 10px; font-size: 12px; border-bottom: 1px solid #ddd;">
        <span class="badge" id="search-results-count">0</span> <?php echo __('найдено'); ?>
        <button class="btn btn-xs btn-default pull-right" id="clear-search-results"><i class="fa fa-times"></i></button>
    </div>
    <ul class="list-unstyled" id="search-results-list" style="margin: 0; padding: 5px;">
        <!-- Результаты будут добавляться сюда -->
    </ul>
</div>
                        
                        <!-- Дерево -->
                        <div class="move-list" id="org-tree-container" style="max-height: 450px; overflow-y: auto; overflow-x: auto; padding: 5px;">
                            <ul class="file-tree" id="file-tree-root">
                                <li class="tree-node" data-org-id="1" data-type="org" data-expanded="false">
                                    <div class="tree-item tree-item-org">
                                        <span class="tree-toggle">
                                            <i class="fa fa-folder-open-o"></i>
                                        </span>
                                        <span class="item-name org-name"><strong><?php echo __('Корень'); ?></strong></span>
                                        <span class="badge" style="margin-left: 5px; font-size: 10px; background-color: #337ab7;" id="root-people-count">
                                            <i class="fa fa-users"></i> 0
                                        </span>
                                        <div class="org-actions pull-right">
                                            <button class="btn btn-xs btn-info btn-add-child" title="<?php echo __('Добавить подразделение'); ?>">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                        <span class="id-tooltip">ID_ORG: 1</span>
                                    </div>
                                    <ul class="tree-children" id="root-children">
                                        <?php if (isset($org_tree) && !empty($org_tree)): ?>
                                            <?php foreach ($org_tree as $org): ?>
                                                <?php if ($org['ID_PARENT'] == 1): ?>
                                                    <?php echo View::factory('mancard/tree_node_with_cards', array(
                                                        'org' => $org,
                                                        'level' => 0
                                                    )); ?>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Footer с ID информацией -->
                        <div class="org-footer">
                            <div class="row">
                                <div class="col-xs-6">
                                    <span class="id-info">
                                        <i class="fa fa-database"></i>
                                        <?php echo __('Всего организаций'); ?>: <strong id="total-orgs">0</strong>
                                    </span>
                                </div>
                                <div class="col-xs-6 text-right">
                                    <span class="id-info" id="hover-id-info">
                                        <i class="fa fa-info-circle"></i>
                                        <span class="text-muted"><?php echo __('Наведите на элемент'); ?></span>
                                    </span>
                                </div>
                            </div>
                            <div class="row" style="margin-top: 2px;">
                                <div class="col-xs-12">
                                    <span class="id-info" id="selected-id-info">
                                        <i class="fa fa-check-circle-o"></i>
                                        <span class="text-muted"><?php echo __('Выберите элемент'); ?></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Центральная панель - информация о сотруднике -->
            <div class="col-md-4">
                <div class="panel panel-info info-panel">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <i class="fa fa-user"></i>
                            <?php echo __('Информация о сотруднике'); ?>
                            <span class="pull-right">
                                <button class="btn btn-xs btn-success" id="btn-add-person" title="<?php echo __('Новый сотрудник'); ?>">
                                    <i class="fa fa-user-plus"></i>
                                </button>
                            </span>
                        </h4>
                    </div>
                    <div class="panel-body" id="person-info-container">
                        <div class="text-center text-muted" style="padding: 40px 0;">
                            <i class="fa fa-info-circle fa-3x"></i>
                            <p style="margin-top: 10px;"><?php echo __('Выберите сотрудника в дереве слева'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Правая панель - категории доступа -->
            <div class="col-md-4">
                <div class="panel panel-warning access-panel">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <i class="fa fa-lock"></i>
                            <?php echo __('Категории доступа'); ?>
                            <span class="pull-right">
                                <span class="badge" id="access-count">0</span>
                                <button class="btn btn-xs btn-primary" id="btn-select-all-access" title="<?php echo __('Выбрать все'); ?>">
                                    <i class="fa fa-check-square-o"></i>
                                </button>
                                <button class="btn btn-xs btn-default" id="btn-deselect-all-access" title="<?php echo __('Снять все'); ?>">
                                    <i class="fa fa-square-o"></i>
                                </button>
                            </span>
                        </h4>
                    </div>
                    <div class="panel-body" id="access-container">
                        <div class="text-center text-muted" style="padding: 40px 0;">
                            <i class="fa fa-info-circle fa-3x"></i>
                            <p style="margin-top: 10px;"><?php echo __('Выберите организацию или сотрудника'); ?></p>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i>
                            <?php echo __('Зеленые галочки - у сотрудника, синие - у организации'); ?>
                            <span class="label label-warning" style="margin-left: 5px;"><?php echo __('Желтый фон - отличие'); ?></span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>

<!-- Модальные окна -->
<?php echo View::factory('mancard/edit_person'); ?>

<script>
$(document).ready(function() {
    var currentEntityType = null;
    var currentEntityId = null;
    var allAccessNames = [];
    var orgAccessIds = [];
    var personAccessIds = [];
    var isDirty = false;
    var searchTimeout = null;
    
    // ===== Обновление ID информации при наведении =====
    function updateHoverInfo(type, id, orgId) {
        var $info = $('#hover-id-info');
        var html = '<i class="fa fa-info-circle"></i> ';
        
        if (type === 'org') {
            html += '<span class="label-id">ID_ORG: ' + id + '</span>';
        } else if (type === 'person') {
            html += '<span class="label-id">ID_PEP: ' + id + '</span>';
            if (orgId) {
                html += ' <span class="label-id" style="background: #d9edf7;">ID_ORG: ' + orgId + '</span>';
            }
        } else {
            html += '<span class="text-muted">' + '<?php echo __('Наведите на элемент'); ?>' + '</span>';
        }
        
        $info.html(html);
    }
    
    // ===== Обновление информации о выбранном элементе =====
    function updateSelectedInfo(type, id, orgId) {
        var $info = $('#selected-id-info');
        var html = '<i class="fa fa-check-circle-o"></i> ';
        
        if (type === 'org') {
            html += '<span class="label label-primary" style="font-size: 10px;">Выбрана организация</span> ';
            html += '<span class="label-id">ID_ORG: ' + id + '</span>';
        } else if (type === 'person') {
            html += '<span class="label label-success" style="font-size: 10px;">Выбран сотрудник</span> ';
            html += '<span class="label-id">ID_PEP: ' + id + '</span>';
            if (orgId) {
                html += ' <span class="label-id" style="background: #d9edf7;">ID_ORG: ' + orgId + '</span>';
            }
        } else {
            html += '<span class="text-muted">' + '<?php echo __('Выберите элемент'); ?>' + '</span>';
        }
        
        $info.html(html);
    }
    
    // ===== Обработчики наведения на элементы дерева =====
    $(document).on('mouseenter', '.tree-item-org', function() {
        var $node = $(this).closest('.tree-node');
        var orgId = $node.data('org-id');
        updateHoverInfo('org', orgId);
    });
    
    $(document).on('mouseenter', '.tree-item-person', function() {
        var personId = $(this).data('person-id');
        var orgId = $(this).data('org-id');
        updateHoverInfo('person', personId, orgId);
    });
    
    $(document).on('mouseleave', '.tree-item', function() {
        if (!currentEntityId) {
            updateHoverInfo(null, null);
        } else {
            if (currentEntityType === 'org') {
                updateHoverInfo('org', currentEntityId);
            } else if (currentEntityType === 'person') {
                updateHoverInfo('person', currentEntityId);
            }
        }
    });
    
    // ===== Функция получения CSS класса для типа карты =====
    function getCardClass(cardType) {
        var typeMap = {
            'Карта EM-marine': 'card-badge-rfid',
            'RFID': 'card-badge-rfid',
            'FP': 'card-badge-fp',
            'Отпечаток пальца': 'card-badge-fp',
            'ШК': 'card-badge-barcode',
            'Штрих-код': 'card-badge-barcode',
            'BAR-code': 'card-badge-barcode',
            'ГРЗ': 'card-badge-grz',
            'grz': 'card-badge-grz',
            'FaceID': 'card-badge-faceid',
            'Распознавание лица': 'card-badge-faceid'
        };
        return typeMap[cardType] || 'card-badge-default';
    }
    
    // ===== Функция получения иконки для типа карты =====
    function getCardIcon(cardType) {
        var iconMap = {
            'Карта EM-marine': 'fa-credit-card',
            'RFID': 'fa-credit-card',
            'FP': 'fa-hand-o-up',
            'Отпечаток пальца': 'fa-hand-o-up',
            'ШК': 'fa-barcode',
            'Штрих-код': 'fa-barcode',
            'BAR-code': 'fa-barcode',
            'ГРЗ': 'fa-car',
            'grz': 'fa-car',
            'FaceID': 'fa-user-circle',
            'Распознавание лица': 'fa-user-circle'
        };
        return iconMap[cardType] || 'fa-id-card-o';
    }
    
    // ===== Функция рендеринга сотрудника с картами =====
    function renderPersonItem(person) {
        var fullName = person.SURNAME + ' ' + person.NAME + ' ' + person.PATRONYMIC;
        var statusClass = person.ACTIVE == 1 ? '' : 'person-status-inactive';
        var statusIcon = person.ACTIVE == 1 ? 'fa-user' : 'fa-user-o';
        var statusColor = person.ACTIVE == 1 ? '' : 'text-muted';
        
        var cardsHtml = '';
        if (person.CARDS && person.CARDS.length > 0) {
            cardsHtml = '<span class="cards-container">';
            $.each(person.CARDS, function(idx, card) {
                var cardClass = getCardClass(card.CARDTYPE_NAME);
                var cardIcon = getCardIcon(card.CARDTYPE_NAME);
                var inactiveClass = card.ACTIVE == 1 ? '' : 'card-badge-inactive';
                var displayName = card.CARDTYPE_SMALLNAME || card.CARDTYPE_NAME;
                
                cardsHtml += '<span class="card-badge ' + cardClass + ' ' + inactiveClass + '" title="' + 
                    card.CARDTYPE_NAME + (card.ACTIVE == 0 ? ' (неактивна)' : '') + '">' +
                    '<i class="fa ' + cardIcon + '"></i> ' +
                    displayName + ': ' + card.ID_CARD +
                    '</span>';
            });
            cardsHtml += '</span>';
        } else {
            cardsHtml = '<span class="text-muted" style="font-size: 10px; margin-left: 5px;">' +
                '<i class="fa fa-info-circle"></i> нет идентификаторов</span>';
        }
        
        var orgId = person.ID_ORG || '';
        
        return '<div class="tree-item tree-item-person ' + statusClass + '" data-person-id="' + person.ID_PEP + '" data-org-id="' + orgId + '">' +
            '<span class="tree-toggle">' +
            '<i class="fa ' + statusIcon + ' ' + statusColor + '"></i>' +
            '</span>' +
            '<span class="item-name person-name ' + statusColor + '">' + fullName + '</span>' +
            (person.POST ? ' <span class="person-post">(' + person.POST + ')</span>' : '') +
            cardsHtml +
            '<span class="id-tooltip">ID_PEP: ' + person.ID_PEP + ' | ID_ORG: ' + (orgId || '?') + '</span>' +
            '</div>';
    }
    
    // ===== Переключение узла =====
    $(document).on('click', '.tree-toggle', function(e) {
        e.stopPropagation();
        var $node = $(this).closest('.tree-node');
        var $children = $node.children('.tree-children');
        var $icon = $(this).find('.fa');
        
        if ($node.data('type') === 'org') {
            if ($children.is(':visible')) {
                $children.slideUp();
                $icon.removeClass('fa-folder-open-o').addClass('fa-folder-o');
                $node.data('expanded', false);
            } else {
                if ($children.children().length === 0) {
                    loadNodeContent($node);
                } else {
                    $children.slideDown();
                    $icon.removeClass('fa-folder-o').addClass('fa-folder-open-o');
                    $node.data('expanded', true);
                }
            }
        }
    });
    
    // ===== Загрузка содержимого узла =====
// ===== Загрузка содержимого узла =====
function loadNodeContent($node) {
    var orgId = $node.data('org-id');
    var $children = $node.children('.tree-children');
    var $icon = $node.find('.tree-toggle .fa');
    
    $.ajax({
        url: '<?php echo URL::site('mancard/get_org_structure_cards'); ?>/' + orgId,
        type: 'GET',
        dataType: 'json',
        beforeSend: function() {
            $children.html('<div class="text-center text-muted" style="padding: 10px;"><i class="fa fa-spinner fa-spin"></i> <?php echo __('Загрузка...'); ?></div>');
        },
        success: function(response) {
            if (response.success && response.data) {
                renderNodeChildren($children, response.data);
                $children.slideDown();
                $icon.removeClass('fa-folder-o').addClass('fa-folder-open-o');
                $node.data('expanded', true);
            } else {
                $children.html('<div class="text-muted" style="padding: 10px;"><i class="fa fa-info-circle"></i> <?php echo __('Нет данных'); ?></div>');
            }
        },
        error: function() {
            $children.html('<div class="text-danger" style="padding: 10px;"><i class="fa fa-exclamation-circle"></i> <?php echo __('Ошибка загрузки'); ?></div>');
        }
    });
}
    
    // ===== Рендеринг детей узла с картами =====
    function renderNodeChildren($container, data) {
        $container.empty();
        
        if (data.CHILDREN && data.CHILDREN.length > 0) {
            $.each(data.CHILDREN, function(index, child) {
                var hasChildren = child.HAS_CHILDREN || false;
                var iconClass = hasChildren ? 'fa-folder-o' : 'fa-folder-o';
                var iconStyle = hasChildren ? '' : 'style="color: #999;"';
                
                var $li = $('<li class="tree-node" data-org-id="' + child.ID_ORG + '" data-type="org" data-expanded="false">');
                var $div = $('<div class="tree-item tree-item-org">');
                
                var $toggle = $('<span class="tree-toggle">');
                $toggle.append('<i class="fa ' + iconClass + '" ' + iconStyle + '></i>');
                $div.append($toggle);
                
                $div.append('<span class="item-name org-name">' + child.NAME + '</span>');
                
                var actionsHtml = '<div class="org-actions pull-right">' +
                    '<button class="btn btn-xs btn-info btn-add-child" title="<?php echo __('Добавить подразделение'); ?>">' +
                    '<i class="fa fa-plus"></i>' +
                    '</button>' +
                    '<button class="btn btn-xs btn-warning btn-rename-org" title="<?php echo __('Переименовать'); ?>">' +
                    '<i class="fa fa-pencil"></i>' +
                    '</button>';
                if (child.ID_ORG != 1) {
                    actionsHtml += '<button class="btn btn-xs btn-danger btn-delete-org" title="<?php echo __('Удалить организацию'); ?>">' +
                        '<i class="fa fa-trash-o"></i>' +
                        '</button>';
                }
                actionsHtml += '</div>';
                $div.append(actionsHtml);
                
                $div.append('<span class="id-tooltip">ID_ORG: ' + child.ID_ORG + '</span>');
                
                $li.append($div);
                
                var $childrenUl = $('<ul class="tree-children" style="display:none;">');
                $li.append($childrenUl);
                
                $container.append($li);
            });
        }
        
        if (data.PEOPLE && data.PEOPLE.length > 0) {
            $.each(data.PEOPLE, function(index, person) {
                var $li = $('<li class="tree-node" data-person-id="' + person.ID_PEP + '" data-type="person">');
                var html = renderPersonItem(person);
                $li.append(html);
                $container.append($li);
            });
        }
        
        if ($container.children().length === 0) {
            $container.html('<div class="text-muted" style="padding: 10px;"><i class="fa fa-info-circle"></i> <?php echo __('Пусто'); ?></div>');
        }
        
        updateTotalOrgs();
    }
    
    // ===== Обновление счетчиков =====
    function updateRootCount() {
        var count = $('#root-children .tree-node[data-type="person"]').length;
        $('#root-people-count').text(count);
    }
    
    function updateTotalOrgs() {
        var count = $('.tree-node[data-type="org"]').length - 1;
        $('#total-orgs').text(count);
    }
    
    // ===== Клик по организации или сотруднику (через делегирование) =====
    $(document).on('click', '.tree-item-org', function(e) {
        e.stopPropagation();
        var $node = $(this).closest('.tree-node');
        var orgId = $node.data('org-id');
        
        if (orgId) {
            currentEntityType = 'org';
            currentEntityId = orgId;
            updateSelectedInfo('org', orgId);
            updateHoverInfo('org', orgId);
            loadAccessForOrg(orgId);
        }
    });
    
    $(document).on('click', '.tree-item-person', function(e) {
        e.stopPropagation();
        var personId = $(this).data('person-id');
        var orgId = $(this).data('org-id');
        
        if (personId) {
            currentEntityType = 'person';
            currentEntityId = personId;
            updateSelectedInfo('person', personId, orgId);
            updateHoverInfo('person', personId, orgId);
            loadPersonInfo(personId);
            loadAccessForPerson(personId);
        }
    });
    
    // ===== Загрузка категорий доступа для организации =====
    function loadAccessForOrg(orgId) {
        currentEntityType = 'org';
        currentEntityId = orgId;
        
        if (allAccessNames.length === 0) {
            loadAllAccessNames();
            setTimeout(function() {
                loadAccessForOrg(orgId);
            }, 500);
            return;
        }
        
        $.ajax({
            url: '<?php echo URL::site('mancard/get_entity_access'); ?>/org/' + orgId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    orgAccessIds = response.data || [];
                    personAccessIds = [];
                    renderAccessList();
                    $('#btn-save-access').show();
                    isDirty = false;
                } else {
                    $('#access-container').html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function(xhr, status, error) {
                $('#access-container').html('<div class="alert alert-danger">Ошибка загрузки: ' + status + '</div>');
            }
        });
    }
    
    // ===== Загрузка категорий доступа для сотрудника =====
    function loadAccessForPerson(personId) {
        currentEntityType = 'person';
        currentEntityId = personId;
        
        if (allAccessNames.length === 0) {
            loadAllAccessNames();
            setTimeout(function() {
                loadAccessForPerson(personId);
            }, 500);
            return;
        }
        
        $.ajax({
            url: '<?php echo URL::site('mancard/get_entity_access'); ?>/person/' + personId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    personAccessIds = response.data || [];
                    orgAccessIds = response.org_access || [];
                    renderAccessList();
                    $('#btn-save-access').show();
                    isDirty = false;
                } else {
                    console.error('Error loading person access:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
            }
        });
    }
    
    // ===== Загрузка всех категорий доступа =====
    function loadAllAccessNames() {
        if (allAccessNames.length > 0) return;
        
        $.ajax({
            url: '<?php echo URL::site('mancard/get_access_names'); ?>',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    allAccessNames = response.data;
                    renderAccessList();
                }
            }
        });
    }
    
    // ===== Рендеринг списка категорий доступа =====
    function renderAccessList() {
        var $container = $('#access-container');
        $container.empty();
        
        if (allAccessNames.length === 0) {
            $container.html('<div class="text-center text-muted" style="padding: 40px 0;"><i class="fa fa-spinner fa-spin fa-3x"></i><p style="margin-top: 10px;"><?php echo __('Загрузка...'); ?></p></div>');
            return;
        }
        
        // ===== ПРИНУДИТЕЛЬНОЕ ПРЕОБРАЗОВАНИЕ В ЧИСЛА =====
        var personIds = personAccessIds.map(Number);
        var orgIds = orgAccessIds.map(Number);
        
        var html = '<div class="access-list">';
        var count = 0;
        
        $.each(allAccessNames, function(index, access) {
            var id = Number(access.ID_ACCESSNAME);
            var isOrg = orgIds.indexOf(id) !== -1;
            var isPerson = personIds.indexOf(id) !== -1;
            
            var checkedAttr = '';
            var isDifferent = false;
            var displayBadges = false;
            
            if (currentEntityType === 'person') {
                checkedAttr = isPerson ? 'checked' : '';
                isDifferent = isPerson !== isOrg;
                displayBadges = true;
            } else if (currentEntityType === 'org') {
                checkedAttr = isOrg ? 'checked' : '';
            }
            
            var differentClass = isDifferent ? 'different' : '';
            
            html += '<div class="access-item ' + differentClass + '">';
            html += '<div class="checkbox">';
            html += '<label>';
            html += '<input type="checkbox" class="access-checkbox" value="' + id + '" ' + checkedAttr + '>';
            html += access.NAME;
            
            if (displayBadges) {
                if (isOrg && isPerson) {
                    html += ' <span class="badge-org"><i class="fa fa-building-o"></i> есть в организации</span>';
                    html += ' <span class="badge-person"><i class="fa fa-user"></i> у сотрудника</span>';
                } else if (isOrg && !isPerson) {
                    html += ' <span class="badge-org"><i class="fa fa-building-o"></i> есть в организации</span>';
                } else if (!isOrg && isPerson) {
                    html += ' <span class="badge-person"><i class="fa fa-user"></i> у сотрудника</span>';
                }
            }
            
            html += '</label>';
            html += '</div>';
            html += '</div>';
            
            if (checkedAttr === 'checked') count++;
        });
        
        html += '</div>';
        
        $container.html(html);
        $('#access-count').text(count);
        
        $('.access-checkbox').on('change', function() {
            isDirty = true;
            $('#btn-save-access').addClass('btn-warning').removeClass('btn-success');
            updateAccessCount();
        });
    }
    
    // ===== Подсветка отличий =====
    function highlightDifferences() {
        $('.access-item').each(function() {
            var $item = $(this);
            var $checkbox = $item.find('.access-checkbox');
            var id = parseInt($checkbox.val());
            var isOrg = orgAccessIds.indexOf(id) !== -1;
            var isPerson = personAccessIds.indexOf(id) !== -1;
            
            if (isPerson !== isOrg) {
                $item.addClass('different');
            } else {
                $item.removeClass('different');
            }
        });
    }
    
    // ===== Обновление счетчика =====
    function updateAccessCount() {
        var count = $('.access-checkbox:checked').length;
        $('#access-count').text(count);
    }
    
    // ===== Выбрать все =====
    $('#btn-select-all-access').on('click', function() {
        $('.access-checkbox').prop('checked', true);
        isDirty = true;
        $('#btn-save-access').addClass('btn-warning').removeClass('btn-success');
        updateAccessCount();
        if (currentEntityType === 'person') {
            highlightDifferences();
        }
    });
    
    // ===== Снять все =====
    $('#btn-deselect-all-access').on('click', function() {
        $('.access-checkbox').prop('checked', false);
        isDirty = true;
        $('#btn-save-access').addClass('btn-warning').removeClass('btn-success');
        updateAccessCount();
        if (currentEntityType === 'person') {
            highlightDifferences();
        }
    });
    
    // ===== Сохранить категории доступа =====
    $('#btn-save-access').on('click', function() {
        if (!isDirty) {
            alert('<?php echo __('Нет изменений для сохранения'); ?>');
            return;
        }
        
        var accessIds = [];
        $('.access-checkbox:checked').each(function() {
            accessIds.push($(this).val());
        });
        
        var data = {
            type: currentEntityType,
            id: currentEntityId,
            access_ids: accessIds
        };
        
        $.ajax({
            url: '<?php echo URL::site('mancard/update_access'); ?>',
            type: 'POST',
            data: data,
            dataType: 'json',
            beforeSend: function() {
                $('#btn-save-access').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo __('Сохранение...'); ?>');
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    isDirty = false;
                    $('#btn-save-access').removeClass('btn-warning').addClass('btn-success').html('<i class="fa fa-save"></i> <?php echo __('Сохранено'); ?>');
                    setTimeout(function() {
                        $('#btn-save-access').html('<i class="fa fa-save"></i> <?php echo __('Сохранить категории'); ?>');
                        $('#btn-save-access').prop('disabled', false);
                    }, 2000);
                } else {
                    alert('Ошибка: ' + response.message);
                    $('#btn-save-access').prop('disabled', false).html('<i class="fa fa-save"></i> <?php echo __('Сохранить категории'); ?>');
                }
            },
            error: function() {
                alert('<?php echo __('Ошибка при сохранении'); ?>');
                $('#btn-save-access').prop('disabled', false).html('<i class="fa fa-save"></i> <?php echo __('Сохранить категории'); ?>');
            }
        });
    });
    
    // ===== Загрузка информации о сотруднике =====
    function loadPersonInfo(personId) {
        $.ajax({
            url: '<?php echo URL::site('mancard/get_person'); ?>/' + personId,
            type: 'GET',
            dataType: 'json',
            beforeSend: function() {
                $('#person-info-container').html('<div class="text-center text-muted" style="padding: 40px 0;"><i class="fa fa-spinner fa-spin fa-2x"></i><p style="margin-top: 10px;"><?php echo __('Загрузка...'); ?></p></div>');
            },
            success: function(response) {
                if (response.success && response.data) {
                    $.ajax({
                        url: '<?php echo URL::site('mancard/get_person_cards'); ?>/' + personId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(cardsResponse) {
                            if (cardsResponse.success) {
                                response.data.CARDS = cardsResponse.data;
                            }
                            displayPersonInfo(response.data);
                        },
                        error: function() {
                            response.data.CARDS = [];
                            displayPersonInfo(response.data);
                        }
                    });
                } else {
                    $('#person-info-container').html('<div class="alert alert-danger">' + (response.message || '<?php echo __('Ошибка загрузки'); ?>') + '</div>');
                }
            },
            error: function() {
                $('#person-info-container').html('<div class="alert alert-danger"><?php echo __('Ошибка загрузки'); ?></div>');
            }
        });
    }
    
    // ===== Отображение информации о сотруднике =====
    function displayPersonInfo(person) {
        var fullName = person.SURNAME + ' ' + person.NAME + ' ' + person.PATRONYMIC;
        var statusText = person.ACTIVE == 1 ? '<?php echo __('Активен'); ?>' : '<?php echo __('Неактивен'); ?>';
        var statusClass = person.ACTIVE == 1 ? 'success' : 'default';
        
        var cardsHtml = '';
        if (person.CARDS && person.CARDS.length > 0) {
            cardsHtml = '<div class="info-item"><span class="label"><?php echo __('Идентификаторы'); ?>:</span><span class="value">';
            $.each(person.CARDS, function(idx, card) {
                var cardClass = getCardClass(card.CARDTYPE_NAME);
                var cardIcon = getCardIcon(card.CARDTYPE_NAME);
                var inactiveClass = card.ACTIVE == 1 ? '' : 'card-badge-inactive';
                var displayName = card.CARDTYPE_SMALLNAME || card.CARDTYPE_NAME;
                
                cardsHtml += '<span class="card-badge ' + cardClass + ' ' + inactiveClass + '" title="' + 
                    card.CARDTYPE_NAME + (card.ACTIVE == 0 ? ' (неактивна)' : '') + '" style="font-size: 11px; padding: 2px 8px; margin: 2px;">' +
                    '<i class="fa ' + cardIcon + '"></i> ' +
                    displayName + ': ' + card.ID_CARD +
                    '</span>';
            });
            cardsHtml += '</span></div>';
        } else {
            cardsHtml = '<div class="info-item"><span class="label"><?php echo __('Идентификаторы'); ?>:</span><span class="value text-muted"><?php echo __('Нет идентификаторов'); ?></span></div>';
        }
        
        var html = '<div class="row">' +
            '<div class="col-md-12">' +
            '<div class="info-item"><span class="label"><?php echo __('ФИО'); ?>:</span><span class="value"><strong>' + fullName + '</strong></span></div>' +
            '<div class="info-item"><span class="label"><?php echo __('Должность'); ?>:</span><span class="value">' + (person.POST || '—') + '</span></div>' +
            '<div class="info-item"><span class="label"><?php echo __('Табельный номер'); ?>:</span><span class="value">' + (person.TABNUM || '—') + '</span></div>' +
            '<div class="info-item"><span class="label"><?php echo __('Логин'); ?>:</span><span class="value">' + (person.LOGIN || '—') + '</span></div>' +
            '<div class="info-item"><span class="label"><?php echo __('Организация'); ?>:</span><span class="value">' + (person.ORG_NAME || '—') + '</span></div>' +
            '<div class="info-item"><span class="label"><?php echo __('Статус'); ?>:</span><span class="value"><span class="label label-' + statusClass + '">' + statusText + '</span></span></div>' +
            '<div class="info-item"><span class="label"><?php echo __('Телефон'); ?>:</span><span class="value">' + (person.PHONEWORK || '—') + '</span></div>' +
            '<div class="info-item"><span class="label"><?php echo __('Мобильный'); ?>:</span><span class="value">' + (person.PHONECELLULAR || '—') + '</span></div>' +
            cardsHtml +
            '<div class="info-item"><span class="label"><?php echo __('Примечание'); ?>:</span><span class="value">' + (person.NOTE || '—') + '</span></div>' +
            '</div>' +
            '</div>';
        
        $('#person-info-container').html(html);
    }
    
    // ===== Добавление организации =====
    $('#btn-add-org').on('click', function() {
        var parentId = 1;
        var name = prompt('<?php echo __('Введите новое название'); ?>', '');
        
        if (name && name.trim()) {
            $.ajax({
                url: '<?php echo URL::site('mancard/add_organization'); ?>',
                type: 'POST',
                data: {
                    name: name.trim(),
                    parent_id: parentId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                }
            });
        }
    });
    
    // ===== Добавление подразделения =====
    $(document).on('click', '.btn-add-child', function(e) {
        e.stopPropagation();
        var $node = $(this).closest('.tree-item').closest('.tree-node');
        var parentId = $node.data('org-id');
        var name = prompt('<?php echo __('Введите новое название'); ?>', '');
        
        if (name && name.trim()) {
            $.ajax({
                url: '<?php echo URL::site('mancard/add_organization'); ?>',
                type: 'POST',
                data: {
                    name: name.trim(),
                    parent_id: parentId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                }
            });
        }
    });
    
    // ===== Переименование организации =====
    $(document).on('click', '.btn-rename-org', function(e) {
        e.stopPropagation();
        var $node = $(this).closest('.tree-item').closest('.tree-node');
        var orgId = $node.data('org-id');
        var currentName = $node.find('.org-name').text();
        
        if (orgId == 1) {
            alert('<?php echo __('Нельзя переименовать корневую организацию'); ?>');
            return;
        }
        
        var newName = prompt('<?php echo __('Введите новое название'); ?>', currentName);
        if (newName && newName.trim() && newName.trim() != currentName) {
            $.ajax({
                url: '<?php echo URL::site('mancard/rename_organization'); ?>',
                type: 'POST',
                data: {
                    id: orgId,
                    name: newName.trim()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                }
            });
        }
    });
    
    // ===== Удаление организации =====
    $(document).on('click', '.btn-delete-org', function(e) {
        e.stopPropagation();
        var $node = $(this).closest('.tree-item').closest('.tree-node');
        var orgId = $node.data('org-id');
        var orgName = $node.find('.org-name').text();
        
        if (orgId == 1) {
            alert('<?php echo __('Нельзя удалить корневую организацию'); ?>');
            return;
        }
        
        if (confirm('<?php echo __('Вы уверены, что хотите удалить'); ?> "' + orgName + '" ?')) {
            $.ajax({
                url: '<?php echo URL::site('mancard/delete_organization'); ?>/' + orgId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                }
            });
        }
    });
    
    // ===== Добавление сотрудника =====
    $('#btn-add-person').on('click', function() {
        var orgId = 1;
        var $activeOrg = $('.tree-item-org').first();
        if ($activeOrg.length) {
            orgId = $activeOrg.closest('.tree-node').data('org-id') || 1;
        }
        openEditPersonDialog(0, orgId);
    });
    
    // ===== Поиск организаций (локальный) =====
    $('#org-search').on('keyup', function() {
        var query = $(this).val().toLowerCase().trim();
        
        // Сначала показываем все узлы
        $('.tree-node').show();
        
        if (query.length < 2) {
            return;
        }
        
        // Скрываем все узлы
        $('.tree-node').hide();
        
        // Ищем организации
        $('.tree-item-org').each(function() {
            var $org = $(this);
            var orgName = $org.find('.org-name').text().toLowerCase();
            
            if (orgName.indexOf(query) > -1) {
                var $node = $org.closest('.tree-node');
                $node.show();
                $node.parents('.tree-node').each(function() {
                    var $parent = $(this);
                    $parent.show();
                    var $children = $parent.children('.tree-children');
                    var $icon = $parent.find('.tree-toggle .fa');
                    if ($children.length > 0) {
                        $children.show();
                        $icon.removeClass('fa-folder-o').addClass('fa-folder-open-o');
                        $parent.data('expanded', true);
                    }
                });
            }
        });
    });
    
    // ===== Поиск сотрудников (через AJAX) =====
// ===== Поиск сотрудников =====
$('#person-search').on('keyup', function() {
    var query = $(this).val().toLowerCase().trim();
    
    clearTimeout(searchTimeout);
    
    if (query.length < 2) {
        $('#search-results').hide();
        $('#search-results-list').empty();
        $('.tree-node').show();
        $('#person-search').removeClass('searching');
        return;
    }
    
    $('#person-search').addClass('searching');
    
    searchTimeout = setTimeout(function() {
        $.ajax({
            url: '<?php echo URL::site('mancard/search_people'); ?>',
            type: 'POST',
            data: { query: query },
            dataType: 'json',
            success: function(response) {
                $('#person-search').removeClass('searching');
                console.log('Search response:', response);
                
                if (response.success && response.data && response.data.length > 0) {
                    // Показываем результаты поиска
                    showSearchResults(response.data);
                } else {
                    // Если ничего не найдено
                    $('#search-results').show();
                    $('#search-results-list').html('<li class="text-muted text-center" style="padding: 10px;">Ничего не найдено</li>');
                    $('#search-results-count').text('0');
                }
            },
            error: function(xhr, status, error) {
                $('#person-search').removeClass('searching');
                console.error('AJAX search error:', status, error);
            }
        });
    }, 300);
});

// ===== Отображение результатов поиска =====
function showSearchResults(people) {
    var $list = $('#search-results-list');
    var $container = $('#search-results');
    
    $list.empty();
    
    people.forEach(function(person) {
        var fullName = person.SURNAME + ' ' + person.NAME + ' ' + person.PATRONYMIC;
        var orgName = person.ORG_NAME || 'Без организации';
        
        var $li = $('<li>')
            .html('<span class="result-name">' + fullName + '</span>' +
                  '<span class="result-org"><i class="fa fa-building-o"></i> ' + orgName + '</span>' +
                  '<span class="result-id">ID: ' + person.ID_PEP + '</span>')
            .data('person-id', person.ID_PEP)
            .data('org-id', person.ID_ORG)
            .on('click', function() {
                var personId = $(this).data('person-id');
                var orgId = $(this).data('org-id');
                console.log('Clicked on person:', personId, 'org:', orgId);
                
                // Раскрываем дерево к сотруднику
                revealAndHighlightPerson(personId, orgId);
                
                // Скрываем результаты поиска
                $('#search-results').hide();
                $('#person-search').val('');
            });
        
        $list.append($li);
    });
    
    $('#search-results-count').text(people.length);
    $container.show();
}

// ===== Очистка результатов поиска =====
$('#clear-search-results').on('click', function() {
    $('#search-results').hide();
    $('#search-results-list').empty();
    $('#person-search').val('');
    $('.tree-node').show();
});
    
    // ===== Развернуть всё =====
    $('#btn-expand-all').on('click', function() {
        $('.tree-node[data-type="org"]').each(function() {
            var $this = $(this);
            var $children = $this.children('.tree-children');
            var $icon = $this.find('.tree-toggle .fa');
            
            if (!$this.data('expanded') && $children.length > 0) {
                if ($children.children().length === 0) {
                    loadNodeContent($this);
                } else {
                    $children.slideDown();
                    $icon.removeClass('fa-folder-o').addClass('fa-folder-open-o');
                    $this.data('expanded', true);
                }
            }
        });
    });
    
    // ===== Свернуть всё =====
    $('#btn-collapse-all').on('click', function() {
        $('.tree-children').slideUp();
        $('.tree-node[data-type="org"]').data('expanded', false);
        $('.tree-node[data-type="org"] .tree-toggle .fa').removeClass('fa-folder-open-o').addClass('fa-folder-o');
    });
    
    // ===== Переключение на страницу перемещения =====
    $('#btn-switch-to-move').on('click', function() {
        window.location.href = '<?php echo URL::site('mancard/move'); ?>';
    });
    
    // ===== Инициализация =====
    $('#file-tree-root > .tree-node .tree-toggle .fa').removeClass('fa-folder-o').addClass('fa-folder-open-o');
    $('#file-tree-root > .tree-node').data('expanded', true);
    $('#file-tree-root .tree-children').show();
    
    updateRootCount();
    updateTotalOrgs();
    
    if ($('#root-children').children().length === 0) {
        loadNodeContent($('#file-tree-root > .tree-node'));
    }
    
    loadAllAccessNames();




// ===== Раскрытие дерева к сотруднику и подсветка =====
function revealAndHighlightPerson(personId, orgId) {
    console.log('Reveal and highlight person:', personId, 'in org:', orgId);
    
    // Находим узел организации
    var $orgNode = $('.tree-node[data-org-id="' + orgId + '"]');
    
    if ($orgNode.length === 0) {
        console.log('Organization not found in DOM:', orgId);
        alert('Организация не найдена в дереве');
        return;
    }
    
    // === 1. Раскрываем организацию и всех родителей ===
    function expandNode($node) {
        var $children = $node.children('.tree-children');
        var $icon = $node.find('.tree-toggle .fa');
        
        // Если узел еще не раскрыт
        if (!$node.data('expanded')) {
            // Если нет детей - загружаем через AJAX
            if ($children.children().length === 0) {
                console.log('Loading content for org:', $node.data('org-id'));
                loadNodeContent($node);
            }
            // Раскрываем
            $children.show();
            if ($icon.length > 0) {
                $icon.removeClass('fa-folder-o').addClass('fa-folder-open-o');
            }
            $node.data('expanded', true);
            console.log('Expanded node:', $node.data('org-id'));
        }
        
        // Раскрываем всех родителей
        $node.parents('.tree-node').each(function() {
            var $parent = $(this);
            var $pChildren = $parent.children('.tree-children');
            var $pIcon = $parent.find('.tree-toggle .fa');
            
            if ($pChildren.length > 0 && !$parent.data('expanded')) {
                $pChildren.show();
                if ($pIcon.length > 0) {
                    $pIcon.removeClass('fa-folder-o').addClass('fa-folder-open-o');
                }
                $parent.data('expanded', true);
            }
        });
    }
    
    // Раскрываем организацию
    expandNode($orgNode);
    
    // === 2. Ждем загрузки сотрудников и подсвечиваем нужного ===
    var attempts = 0;
    var maxAttempts = 15; // 15 попыток по 300ms = 4.5 секунды
    
    var checkInterval = setInterval(function() {
        attempts++;
        console.log('Attempt', attempts, 'to find person:', personId);
        
        var $person = $('.tree-item-person[data-person-id="' + personId + '"]');
        
        if ($person.length > 0) {
            // Нашли сотрудника!
            clearInterval(checkInterval);
            console.log('Found person in DOM!');
            
            // Показываем узел
            var $node = $person.closest('.tree-node');
            $node.show();
            
            // Раскрываем всех родителей еще раз (на всякий случай)
            $node.parents('.tree-node').each(function() {
                var $parent = $(this);
                $parent.show();
                var $pChildren = $parent.children('.tree-children');
                var $pIcon = $parent.find('.tree-toggle .fa');
                if ($pChildren.length > 0 && !$parent.data('expanded')) {
                    $pChildren.show();
                    $pIcon.removeClass('fa-folder-o').addClass('fa-folder-open-o');
                    $parent.data('expanded', true);
                }
            });
            
            // Подсветка
            $person.css({
                'background-color': '#ffff99',
                'border-left': '4px solid #ff6600',
                'border-radius': '3px',
                'padding-left': '4px'
            });
            
            // Прокручиваем к сотруднику
            var $container = $('#org-tree-container');
            var offset = $person.offset().top - $container.offset().top + $container.scrollTop() - 80;
            $container.animate({ scrollTop: offset }, 400);
            
            // Убираем подсветку через 5 секунд
            setTimeout(function() {
                $person.css({
                    'background-color': '',
                    'border-left': '',
                    'border-radius': '',
                    'padding-left': ''
                });
            }, 5000);
            
            // Обновляем информацию в правой панели
            $person.trigger('click');
            
        } else if (attempts >= maxAttempts) {
            clearInterval(checkInterval);
            console.log('Timeout: Person not found');
            
            // Показываем сообщение с предложением раскрыть вручную
            var orgName = $orgNode.find('.org-name').text();
            if (confirm('Сотрудник найден в организации "' + orgName + '", но еще не загружен в дерево.\n\nРаскрыть организацию вручную?')) {
                // Раскрываем организацию еще раз с принудительной загрузкой
                var $children = $orgNode.children('.tree-children');
                if ($children.children().length === 0) {
                    loadNodeContent($orgNode);
                }
                $orgNode.children('.tree-children').show();
                $orgNode.data('expanded', true);
                
                // Повторяем поиск через секунду
                setTimeout(function() {
                    revealAndHighlightPerson(personId, orgId);
                }, 1000);
            }
        }
    }, 300);
}

// ===== Поиск по идентификатору (карте) =====
$('#card-search').on('keyup', function() {
    var query = $(this).val().toLowerCase().trim();
    
    clearTimeout(searchTimeout);
    
    if (query.length < 2) {
        $('#search-results').hide();
        $('#search-results-list').empty();
        $('.tree-node').show();
        $('#card-search').removeClass('searching');
        return;
    }
    
    $('#card-search').addClass('searching');
    
    searchTimeout = setTimeout(function() {
        $.ajax({
            url: '<?php echo URL::site('mancard/search_card'); ?>',
            type: 'POST',
            data: { query: query },
            dataType: 'json',
            success: function(response) {
                $('#card-search').removeClass('searching');
                console.log('Card search response:', response);
                
                if (response.success && response.data && response.data.length > 0) {
                    // Показываем результаты поиска
                    showCardSearchResults(response.data);
                } else {
                    // Если ничего не найдено
                    $('#search-results').show();
                    $('#search-results-list').html('<li class="text-muted text-center" style="padding: 10px;">По идентификатору ничего не найдено</li>');
                    $('#search-results-count').text('0');
                }
            },
            error: function(xhr, status, error) {
                $('#card-search').removeClass('searching');
                console.error('AJAX card search error:', status, error);
            }
        });
    }, 300);
});

// ===== Отображение результатов поиска по идентификатору =====
function showCardSearchResults(people) {
    var $list = $('#search-results-list');
    var $container = $('#search-results');
    
    $list.empty();
    
    people.forEach(function(person) {
        var fullName = person.SURNAME + ' ' + person.NAME + ' ' + person.PATRONYMIC;
        var orgName = person.ORG_NAME || 'Без организации';
        var cardInfo = person.ID_CARD || 'Нет карты';
        var cardType = person.CARDTYPE_NAME || 'Неизвестный тип';
        
        var $li = $('<li>')
            .html('<span class="result-name">' + fullName + '</span>' +
                  '<span class="result-org"><i class="fa fa-building-o"></i> ' + orgName + '</span>' +
                  '<span class="result-card"><i class="fa fa-id-card-o"></i> ' + cardType + ': ' + cardInfo + '</span>' +
                  '<span class="result-id">ID: ' + person.ID_PEP + '</span>')
            .data('person-id', person.ID_PEP)
            .data('org-id', person.ID_ORG)
            .on('click', function() {
                var personId = $(this).data('person-id');
                var orgId = $(this).data('org-id');
                console.log('Clicked on person from card search:', personId, 'org:', orgId);
                
                // Раскрываем дерево к сотруднику
                revealAndHighlightPerson(personId, orgId);
                
                // Скрываем результаты поиска
                $('#search-results').hide();
                $('#card-search').val('');
            });
        
        $list.append($li);
    });
    
    $('#search-results-count').text(people.length);
    $container.show();
}

});
</script>