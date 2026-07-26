<?php
/**
 * Страница массового перемещения сотрудников и организаций
 * Две панели с деревьями и стрелками между ними
 */
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<style>
/* Стили для отображения карт */
.card-badge {
    display: inline-block;
    padding: 1px 6px;
    margin: 1px 2px;
    font-size: 10px;
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
    font-size: 9px;
}
.tree-item-person .cards-container {
    display: inline-block;
    margin-left: 5px;
}
.tree-item-person .cards-container .card-badge {
    font-size: 9px;
    padding: 1px 5px;
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
</style>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">
            <i class="fa fa-exchange"></i>
            <?php echo __('Массовое перемещение'); ?>
            <div class="pull-right">
                <button class="btn btn-xs btn-default" id="btn-switch-view">
                    <i class="fa fa-list"></i> <?php echo __('Переключить вид'); ?>
                </button>
            </div>
        </h3>
    </div>
    <div class="panel-body">
        
        <!-- Информационная панель -->
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            <?php echo __('Выберите элементы в левой панели (сотрудников или организаций), затем выберите целевую организацию в правой панели и нажмите стрелку для перемещения.'); ?>
            <br>
            <small><i class="fa fa-id-card-o"></i> <?php echo __('У сотрудников отображаются все выданные идентификаторы (карты, отпечатки пальцев, FaceID и т.д.)'); ?></small>
        </div>
        
        <!-- Основные панели -->
        <div class="row move-container">
            <!-- Левая панель - источник -->
            <div class="col-md-5">
                <div class="panel panel-default move-panel">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <i class="fa fa-arrow-right"></i>
                            <?php echo __('Источник'); ?>
                            <span class="pull-right">
                                <button class="btn btn-xs btn-success" id="btn-select-all-left" title="<?php echo __('Выбрать все'); ?>">
                                    <i class="fa fa-check-square-o"></i>
                                </button>
                                <button class="btn btn-xs btn-default" id="btn-deselect-all-left" title="<?php echo __('Снять выделение'); ?>">
                                    <i class="fa fa-square-o"></i>
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
                        <!-- Строка поиска -->
                        <div class="form-group" style="margin-bottom: 5px;">
                            <div class="input-group input-group-sm">
                                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                <input type="text" class="form-control" id="left-search" placeholder="<?php echo __('Поиск по названию или ФИО...'); ?>">
                            </div>
                        </div>
                        
                        <!-- Дерево-файловый менеджер -->
                        <div class="move-list" id="left-tree" style="max-height: 400px; overflow-y: auto; overflow-x: auto; padding: 5px;">
                            <ul class="file-tree" id="file-tree-root">
                                <?php if (isset($org_tree) && !empty($org_tree)): ?>
                                    <?php foreach ($org_tree as $org): ?>
                                        <?php if ($org['ID_PARENT'] == 1): ?>
                                            <?php echo View::factory('mancard/move_tree_node_with_cards', array(
                                                'org' => $org,
                                                'level' => 0
                                            )); ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                        
                        <div class="panel-footer" style="padding: 5px 10px;">
                            <small class="text-muted">
                                <i class="fa fa-tag"></i>
                                <?php echo __('Выбрано'); ?>: <span id="left-selected-count">0</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Центр - кнопки перемещения -->
            <div class="col-md-2 text-center move-buttons">
                <div style="display: flex; flex-direction: column; justify-content: center; height: 100%; padding: 20px 0;">
                    <button class="btn btn-primary btn-lg" id="btn-move-right" style="margin-bottom: 20px;" title="<?php echo __('Переместить выбранное вправо'); ?>">
                        <i class="fa fa-arrow-right fa-2x"></i>
                        <br>
                        <small><?php echo __('Переместить →'); ?></small>
                    </button>
                    
                    <button class="btn btn-warning btn-lg" id="btn-move-left" title="<?php echo __('Переместить выбранное влево'); ?>">
                        <i class="fa fa-arrow-left fa-2x"></i>
                        <br>
                        <small><?php echo __('← Переместить'); ?></small>
                    </button>
                </div>
            </div>
            
            <!-- Правая панель - назначение -->
            <div class="col-md-5">
                <div class="panel panel-success move-panel">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <i class="fa fa-arrow-left"></i>
                            <?php echo __('Назначение'); ?>
                            <span class="pull-right">
                                <button class="btn btn-xs btn-info" id="btn-refresh-right" title="<?php echo __('Обновить'); ?>">
                                    <i class="fa fa-refresh"></i>
                                </button>
                            </span>
                        </h4>
                    </div>
                    <div class="panel-body" style="padding: 5px;">
                        <!-- Выбор целевой организации -->
                        <div class="form-group">
                            <label for="target-org-select"><?php echo __('Целевая организация'); ?></label>
                            <select class="form-control" id="target-org-select">
                                <option value="1"><?php echo __('Корень'); ?></option>
                                <?php if (isset($organizations) && !empty($organizations)): ?>
                                    <?php foreach ($organizations as $org): ?>
                                        <?php if ($org['ID_ORG'] != 1): ?>
                                            <?php $level = isset($org['LEVEL']) ? $org['LEVEL'] : 0; ?>
                                            <option value="<?php echo $org['ID_ORG']; ?>">
                                                <?php echo str_repeat('—', $level) . ' ' . htmlspecialchars($org['NAME']); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <!-- Информация о выбранной организации -->
                        <div class="well well-sm" id="target-org-info">
                            <strong><?php echo __('Выбрана'); ?>:</strong>
                            <span id="target-org-name"><?php echo __('Корень'); ?></span>
                            <span class="pull-right">
                                <span class="label label-info" id="target-org-people-count">0</span>
                            </span>
                        </div>
                        
                        <!-- Список элементов в целевой организации -->
                        <div class="move-list" id="right-items-list" style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-striped table-hover table-condensed">
                                <thead>
                                    <tr>
                                        <th><?php echo __('Тип'); ?></th>
                                        <th><?php echo __('Название / ФИО'); ?></th>
                                        <th><?php echo __('Идентификаторы'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">
                                            <i class="fa fa-info-circle"></i> <?php echo __('Выберите целевую организацию'); ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="panel-footer" style="padding: 5px 10px;">
                            <small class="text-muted">
                                <i class="fa fa-database"></i>
                                <?php echo __('Всего элементов'); ?>: <span id="right-items-count">0</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Кнопка выполнения перемещения -->
        <div class="row" style="margin-top: 15px;">
            <div class="col-md-12 text-center">
                <button class="btn btn-success btn-lg" id="btn-execute-move" disabled>
                    <i class="fa fa-exchange"></i>
                    <?php echo __('Выполнить перемещение'); ?>
                </button>
                <span id="move-status" class="text-muted" style="margin-left: 15px;"></span>
            </div>
        </div>
        
    </div>
</div>

<script>
$(document).ready(function() {
    var selectedItems = [];
    var targetOrgId = 1;
    
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
        
        return '<div class="tree-item tree-item-person ' + statusClass + '">' +
            '<input type="checkbox" class="item-checkbox" value="person_' + person.ID_PEP + '" ' +
            'data-type="person" data-id="' + person.ID_PEP + '" data-name="' + fullName + '">' +
            '<span class="tree-toggle">' +
            '<i class="fa ' + statusIcon + ' ' + statusColor + '"></i>' +
            '</span>' +
            '<span class="item-name person-name ' + statusColor + '">' + fullName + '</span>' +
            (person.POST ? ' <span class="person-post">(' + person.POST + ')</span>' : '') +
            cardsHtml +
            '</div>';
    }
    
    // ===== Переключение узла =====
    $(document).on('click', '.tree-toggle', function(e) {
        e.stopPropagation();
        var $node = $(this).closest('.tree-node');
        var $children = $node.children('.tree-children');
        var $icon = $(this).find('.fa');
        
        // Проверяем, является ли узел организацией
        if ($node.data('type') === 'org') {
            if ($children.is(':visible')) {
                $children.slideUp();
                $icon.removeClass('fa-folder-open-o').addClass('fa-folder-o');
                $node.data('expanded', false);
            } else {
                // Загружаем содержимое, если его нет
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
    function loadNodeContent($node) {
        var orgId = $node.data('org-id');
        var $children = $node.children('.tree-children');
        var $icon = $node.find('.tree-toggle .fa');
        
        $.ajax({
            url: '<?php echo URL::site('mancard/get_org_structure_cards'); ?>/' + orgId,
            type: 'GET',
            dataType: 'json',
            beforeSend: function() {
                $children.html('<div class="text-center text-muted"><i class="fa fa-spinner fa-spin"></i> <?php echo __('Загрузка...'); ?></div>');
            },
            success: function(response) {
                if (response.success && response.data) {
                    renderNodeChildrenWithCards($children, response.data);
                    $children.slideDown();
                    $icon.removeClass('fa-folder-o').addClass('fa-folder-open-o');
                    $node.data('expanded', true);
                } else {
                    $children.html('<div class="text-muted"><i class="fa fa-info-circle"></i> <?php echo __('Нет данных'); ?></div>');
                }
            },
            error: function() {
                $children.html('<div class="text-danger"><i class="fa fa-exclamation-circle"></i> <?php echo __('Ошибка загрузки'); ?></div>');
            }
        });
    }
    
    // ===== Рендеринг детей узла с картами =====
    function renderNodeChildrenWithCards($container, data) {
        $container.empty();
        
        // Сначала выводим подорганизации
        if (data.CHILDREN && data.CHILDREN.length > 0) {
            $.each(data.CHILDREN, function(index, child) {
                var hasChildren = child.HAS_CHILDREN || false;
                var iconClass = hasChildren ? 'fa-folder-o' : 'fa-folder-o';
                var iconStyle = hasChildren ? '' : 'style="color: #999;"';
                
                var $li = $('<li class="tree-node" data-org-id="' + child.ID_ORG + '" data-type="org" data-expanded="false">');
                var $div = $('<div class="tree-item tree-item-org">');
                
                // Чекбокс
                $div.append('<input type="checkbox" class="item-checkbox" value="org_' + child.ID_ORG + '" data-type="org" data-id="' + child.ID_ORG + '" data-name="' + child.NAME + '">');
                
                // Иконка переключения
                var $toggle = $('<span class="tree-toggle">');
                $toggle.append('<i class="fa ' + iconClass + '" ' + iconStyle + '></i>');
                $div.append($toggle);
                
                // Название
                $div.append('<span class="item-name">' + child.NAME + '</span>');
                
                $li.append($div);
                
                // Контейнер для детей
                var $childrenUl = $('<ul class="tree-children" style="display:none;">');
                $li.append($childrenUl);
                
                $container.append($li);
            });
        }
        
        // Затем выводим сотрудников с картами
        if (data.PEOPLE && data.PEOPLE.length > 0) {
            $.each(data.PEOPLE, function(index, person) {
                var $li = $('<li class="tree-node" data-person-id="' + person.ID_PEP + '" data-type="person">');
                var html = renderPersonItem(person);
                $li.append(html);
                $container.append($li);
            });
        }
        
        // Если ничего нет
        if ($container.children().length === 0) {
            $container.html('<div class="text-muted"><i class="fa fa-info-circle"></i> <?php echo __('Пусто'); ?></div>');
        }
        
        // Обновляем счетчики
        updateSelectedCount();
    }
    
    // ===== Выделение элементов =====
    $(document).on('change', '.item-checkbox', function() {
        updateSelectedCount();
    });
    
    function updateSelectedCount() {
        var count = $('.item-checkbox:checked').length;
        $('#left-selected-count').text(count);
        
        if (count > 0) {
            $('#btn-execute-move').prop('disabled', false);
            $('#move-status').text('Выбрано: ' + count + ' элементов');
        } else {
            $('#btn-execute-move').prop('disabled', true);
            $('#move-status').text('');
        }
    }
    
    // ===== Выбрать все видимые =====
    $('#btn-select-all-left').on('click', function() {
        $('.tree-item:visible .item-checkbox').prop('checked', true);
        updateSelectedCount();
    });
    
    // ===== Снять выделение =====
    $('#btn-deselect-all-left').on('click', function() {
        $('.item-checkbox').prop('checked', false);
        updateSelectedCount();
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
    
    // ===== Поиск =====
    $('#left-search').on('keyup', function() {
        var query = $(this).val().toLowerCase();
        
        if (query.length < 2) {
            $('.tree-node').show();
            return;
        }
        
        $('.tree-node').each(function() {
            var text = $(this).find('.item-name').text().toLowerCase();
            if (text.indexOf(query) > -1) {
                $(this).show();
                // Показываем родителей
                $(this).parents('.tree-node').show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // ===== Выбор целевой организации =====
    $('#target-org-select').on('change', function() {
        targetOrgId = parseInt($(this).val());
        loadRightPanel(targetOrgId);
    });
    
    // ===== Загрузка правой панели =====
    function loadRightPanel(orgId) {
        $.ajax({
            url: '<?php echo URL::site('mancard/get_move_people'); ?>/' + orgId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateRightPanel(response.data, orgId);
                }
            }
        });
    }
    
    function updateRightPanel(people, orgId) {
        var $tbody = $('#right-items-list tbody');
        $tbody.empty();
        
        var orgName = $('#target-org-select option:selected').text();
        $('#target-org-name').text(orgName);
        
        if (!people || people.length === 0) {
            $tbody.append('<tr><td colspan="3" class="text-center text-muted"><i class="fa fa-info-circle"></i> <?php echo __('Нет сотрудников'); ?></td></tr>');
            $('#right-items-count').text(0);
            $('#target-org-people-count').text(0);
            return;
        }
        
        var count = 0;
        $.each(people, function(index, person) {
            count++;
            var statusIcon = person.ACTIVE == 1 ? 'fa-user' : 'fa-user-o text-muted';
            var cardsHtml = '';
            if (person.CARDS && person.CARDS.length > 0) {
                var cardBadges = [];
                $.each(person.CARDS, function(idx, card) {
                    var cardClass = getCardClass(card.CARDTYPE_NAME);
                    var cardIcon = getCardIcon(card.CARDTYPE_NAME);
                    var inactiveClass = card.ACTIVE == 1 ? '' : 'card-badge-inactive';
                    var displayName = card.CARDTYPE_SMALLNAME || card.CARDTYPE_NAME;
                    cardBadges.push('<span class="card-badge ' + cardClass + ' ' + inactiveClass + '" title="' + 
                        card.CARDTYPE_NAME + (card.ACTIVE == 0 ? ' (неактивна)' : '') + '">' +
                        '<i class="fa ' + cardIcon + '"></i> ' +
                        displayName + ': ' + card.ID_CARD +
                        '</span>');
                });
                cardsHtml = cardBadges.join(' ');
            } else {
                cardsHtml = '<span class="text-muted" style="font-size: 10px;"><i class="fa fa-info-circle"></i> нет</span>';
            }
            
            var row = '<tr>' +
                '<td><i class="fa ' + statusIcon + '"></i></td>' +
                '<td>' + person.SURNAME + ' ' + person.NAME + ' ' + person.PATRONYMIC + 
                (person.POST ? ' <small class="text-muted">(' + person.POST + ')</small>' : '') +
                '</td>' +
                '<td style="max-width: 200px;">' + cardsHtml + '</td>' +
                '</tr>';
            $tbody.append(row);
        });
        
        $('#right-items-count').text(count);
        $('#target-org-people-count').text(count);
    }
    
    // ===== Перемещение вправо =====
    $('#btn-move-right').on('click', function() {
        var selected = $('.item-checkbox:checked');
        if (selected.length === 0) {
            alert('<?php echo __('Не выбраны элементы для перемещения'); ?>');
            return;
        }
        
        var targetOrgId = parseInt($('#target-org-select').val());
        if (targetOrgId === 0) {
            alert('<?php echo __('Выберите целевую организацию'); ?>');
            return;
        }
        
        var peopleIds = [];
        var orgIds = [];
        
        selected.each(function() {
            var type = $(this).data('type');
            var id = parseInt($(this).data('id'));
            if (type === 'person') {
                peopleIds.push(id);
            } else if (type === 'org') {
                if (id === 1) {
                    alert('<?php echo __('Нельзя перемещать корневую организацию'); ?>');
                    return;
                }
                orgIds.push(id);
            }
        });
        
        var total = peopleIds.length + orgIds.length;
        if (total === 0) {
            alert('<?php echo __('Не выбраны элементы для перемещения'); ?>');
            return;
        }
        
        if (!confirm('Переместить ' + total + ' элементов?')) {
            return;
        }
        
        $.ajax({
            url: '<?php echo URL::site('mancard/move_items'); ?>',
            type: 'POST',
            data: {
                source_org_id: 0,
                target_org_id: targetOrgId,
                move_people: peopleIds,
                move_orgs: orgIds
            },
            dataType: 'json',
            beforeSend: function() {
                $('#btn-move-right').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Ошибка: ' + response.message);
                    $('#btn-move-right').prop('disabled', false).html('<i class="fa fa-arrow-right fa-2x"></i><br><small><?php echo __('Переместить →'); ?></small>');
                }
            },
            error: function() {
                alert('Ошибка при выполнении запроса');
                $('#btn-move-right').prop('disabled', false).html('<i class="fa fa-arrow-right fa-2x"></i><br><small><?php echo __('Переместить →'); ?></small>');
            }
        });
    });
    
    // ===== Перемещение влево =====
    $('#btn-move-left').on('click', function() {
        alert('<?php echo __('Функция перемещения справа налево будет реализована в следующей версии'); ?>');
    });
    
    // ===== Выполнить перемещение =====
    $('#btn-execute-move').on('click', function() {
        $('#btn-move-right').click();
    });
    
    // ===== Переключение вида =====
    $('#btn-switch-view').on('click', function() {
        window.location.href = '<?php echo URL::site('mancard/index'); ?>';
    });
    
    // ===== Обновление правой панели =====
    $('#btn-refresh-right').on('click', function() {
        loadRightPanel(targetOrgId);
    });
    
    // ===== Инициализация =====
    targetOrgId = parseInt($('#target-org-select').val());
    loadRightPanel(targetOrgId);
    updateSelectedCount();
});
</script>

<style>
/* Стили для отображения карт */
.card-badge {
    display: inline-block;
    padding: 1px 6px;
    margin: 1px 2px;
    font-size: 10px;
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
    font-size: 9px;
}
.tree-item-person .cards-container {
    display: inline-block;
    margin-left: 5px;
}
.tree-item-person .cards-container .card-badge {
    font-size: 9px;
    padding: 1px 5px;
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

/* Остальные стили */
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
    padding: 2px 5px;
    border-radius: 3px;
    display: flex;
    align-items: center;
    cursor: default;
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
.tree-item .item-checkbox {
    margin-right: 5px;
    flex-shrink: 0;
}
.tree-item .item-name {
    margin-left: 3px;
    flex: 1;
}
.tree-children {
    padding-left: 20px;
}
.tree-node .tree-children {
    border-left: 1px dashed #ddd;
    margin-left: 8px;
    padding-left: 12px;
}

/* Стили для панелей */
.move-container {
    min-height: 500px;
}
.move-panel {
    height: 100%;
}
.move-panel .panel-body {
    height: 100%;
}
.move-list {
    border: 1px solid #e7e7e7;
    border-radius: 4px;
    background: #fafafa;
}
.move-list .table {
    margin-bottom: 0;
}
.move-list .table thead th {
    background: #f5f5f5;
    border-bottom: 2px solid #ddd;
}
.move-buttons {
    display: flex;
    align-items: center;
    justify-content: center;
}
.move-buttons .btn {
    width: 100%;
    padding: 20px 10px;
}
.move-buttons .btn .fa {
    display: block;
}
#target-org-info {
    margin-top: 10px;
    margin-bottom: 10px;
}
#target-org-info .label {
    font-size: 12px;
    padding: 4px 8px;
}

/* Анимация */
.tree-children {
    transition: all 0.3s ease;
}

/* Поиск */
#left-search:focus {
    border-color: #66afe9;
    box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102, 175, 233, .6);
}

/* Адаптивность */
@media (max-width: 768px) {
    .move-buttons {
        padding: 15px 0;
    }
    .move-buttons .btn {
        width: auto;
        padding: 10px 20px;
        display: inline-block;
    }
    .move-buttons .btn .fa {
        display: inline-block;
        margin-right: 5px;
    }
    .move-buttons .btn .fa-2x {
        font-size: 1.5em;
    }
}
</style>