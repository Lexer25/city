<?php
/**
 * Главная страница модуля mancard
 */
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">
            <i class="fa fa-sitemap"></i>
            <?php echo __('Управление организациями и сотрудниками'); ?>
        </h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <!-- Левая панель: дерево организаций -->
            <div class="col-md-4 col-lg-3">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <i class="fa fa-building-o"></i>
                            <?php echo __('Организации'); ?>
                            <button class="btn btn-xs btn-primary pull-right" id="btn-add-org" title="<?php echo __('Новая организация'); ?>">
                                <i class="fa fa-plus"></i>
                            </button>
                        </h4>
                    </div>
                    <div class="panel-body" style="padding: 5px;">
                        <div id="org-tree" style="max-height: 500px; overflow-y: auto;">
                            <ul class="list-unstyled org-tree" data-org-id="1">
                                <li>
                                    <div class="org-node" data-org-id="1">
                                        <i class="fa fa-folder-open-o"></i>
                                        <span class="org-name"><?php echo __('Корень'); ?></span>
                                        <span class="badge" id="org-count-1">0</span>
                                        <div class="org-actions pull-right">
                                            <button class="btn btn-xs btn-info btn-add-child" title="<?php echo __('Добавить подразделение'); ?>">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <ul class="list-unstyled org-children" data-parent="1">
                                        <?php if (isset($org_tree) && !empty($org_tree)): ?>
                                            <?php foreach ($org_tree as $org): ?>
                                                <?php if ($org['ID_PARENT'] == 1): ?>
                                                    <?php echo View::factory('mancard/org_node', array('org' => $org)); ?>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i>
                            <?php echo __('Перетащите организацию для перемещения'); ?>
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- Правая панель: список сотрудников -->
            <div class="col-md-8 col-lg-9">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <i class="fa fa-users"></i>
                            <?php echo __('Сотрудники'); ?>
                            <span id="current-org-name"><?php echo isset($current_org) ? $current_org['NAME'] : ''; ?></span>
                            <div class="pull-right">
                                <button class="btn btn-xs btn-success" id="btn-add-person" title="<?php echo __('Новый сотрудник'); ?>">
                                    <i class="fa fa-user-plus"></i>
                                </button>
                                <button class="btn btn-xs btn-primary" id="btn-move-people" title="<?php echo __('Переместить выбранных'); ?>">
                                    <i class="fa fa-arrows"></i>
                                </button>
                                <button class="btn btn-xs btn-default" id="btn-refresh" title="<?php echo __('Обновить'); ?>">
                                    <i class="fa fa-refresh"></i>
                                </button>
                            </div>
                        </h4>
                    </div>
                    <div class="panel-body" style="padding: 5px;">
                        <div id="people-list" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-striped table-hover table-condensed" id="people-table">
                                <thead>
                                    <tr>
                                        <th width="30">
                                            <input type="checkbox" id="select-all-people" title="<?php echo __('Выбрать все'); ?>">
                                        </th>
                                        <th><?php echo __('ФИО'); ?></th>
                                        <th><?php echo __('Должность'); ?></th>
                                        <th><?php echo __('Телефон'); ?></th>
                                        <th><?php echo __('Статус'); ?></th>
                                        <th width="100"><?php echo __('Действия'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($people_list) && !empty($people_list)): ?>
                                        <?php foreach ($people_list as $person): ?>
                                            <tr data-person-id="<?php echo $person['ID_PEP']; ?>">
                                                <td>
                                                    <input type="checkbox" class="person-checkbox" value="<?php echo $person['ID_PEP']; ?>">
                                                </td>
                                                <td>
                                                    <strong><?php echo $person['SURNAME'] . ' ' . $person['NAME'] . ' ' . $person['PATRONYMIC']; ?></strong>
                                                </td>
                                                <td><?php echo $person['POST']; ?></td>
                                                <td><?php echo $person['PHONEWORK']; ?></td>
                                                <td>
                                                    <?php if ($person['ACTIVE'] == 1): ?>
                                                        <span class="label label-success"><?php echo __('Активен'); ?></span>
                                                    <?php else: ?>
                                                        <span class="label label-default"><?php echo __('Неактивен'); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-xs btn-info btn-edit-person" title="<?php echo __('Редактировать'); ?>">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-xs btn-danger btn-delete-person" title="<?php echo __('Удалить'); ?>">
                                                        <i class="fa fa-trash-o"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                <i class="fa fa-info-circle"></i>
                                                <?php echo __('Нет данных'); ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <span class="text-muted">
                            <i class="fa fa-tag"></i>
                            <?php echo __('Выбрано'); ?>: <span id="selected-count">0</span> <?php echo __('Записей'); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальные окна -->
<?php echo View::factory('mancard/move_dialog', array('organizations' => isset($org_tree) ? $org_tree : array())); ?>
<?php echo View::factory('mancard/edit_person'); ?>

<script>
$(document).ready(function() {
    var selectedPeople = [];
    
    // ===== Дерево организаций =====
    function initOrgTree() {
        // Toggle children
        $('.org-node .fa.fa-folder-o, .org-node .fa.fa-folder-open-o').on('click', function(e) {
            e.stopPropagation();
            var $node = $(this).closest('.org-node');
            var $children = $node.next('.org-children');
            var $icon = $(this);
            
            if ($children.is(':visible')) {
                $children.slideUp();
                $icon.removeClass('fa-folder-open-o').addClass('fa-folder-o');
            } else {
                $children.slideDown();
                $icon.removeClass('fa-folder-o').addClass('fa-folder-open-o');
            }
        });
        
        // Select organization
        $('.org-node').on('click', function() {
            var orgId = $(this).data('org-id');
            loadPeople(orgId);
            
            $('.org-node').removeClass('active');
            $(this).addClass('active');
        });
    }
    
    // ===== Загрузка сотрудников =====
    function loadPeople(orgId) {
        $.ajax({
            url: '<?php echo URL::site('mancard/get_people'); ?>/' + orgId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updatePeopleTable(response.data);
                    $('#current-org-name').text($('.org-node.active .org-name').text());
                    selectedPeople = [];
                    updateSelectedCount();
                }
            }
        });
    }
    
    function updatePeopleTable(people) {
        var $tbody = $('#people-table tbody');
        $tbody.empty();
        
        if (!people || people.length === 0) {
            $tbody.append('<tr><td colspan="6" class="text-center text-muted"><i class="fa fa-info-circle"></i> <?php echo __('Нет данных'); ?></td></tr>');
            return;
        }
        
        $.each(people, function(index, person) {
            var statusHtml = person.ACTIVE == 1 ? 
                '<span class="label label-success"><?php echo __('Активен'); ?></span>' : 
                '<span class="label label-default"><?php echo __('Неактивен'); ?></span>';
            
            var row = '<tr data-person-id="' + person.ID_PEP + '">' +
                '<td><input type="checkbox" class="person-checkbox" value="' + person.ID_PEP + '"></td>' +
                '<td><strong>' + person.SURNAME + ' ' + person.NAME + ' ' + person.PATRONYMIC + '</strong></td>' +
                '<td>' + person.POST + '</td>' +
                '<td>' + person.PHONEWORK + '</td>' +
                '<td>' + statusHtml + '</td>' +
                '<td>' +
                '<button class="btn btn-xs btn-info btn-edit-person" title="<?php echo __('Редактировать'); ?>"><i class="fa fa-edit"></i></button> ' +
                '<button class="btn btn-xs btn-danger btn-delete-person" title="<?php echo __('Удалить'); ?>"><i class="fa fa-trash-o"></i></button>' +
                '</td>' +
                '</tr>';
            $tbody.append(row);
        });
        
        // Обновляем счетчик
        var orgId = $('.org-node.active').data('org-id');
        if (orgId) {
            $('#org-count-' + orgId).text(people.length);
        }
    }
    
    // ===== Выделение сотрудников =====
    function updateSelectedCount() {
        var count = $('.person-checkbox:checked').length;
        $('#selected-count').text(count);
        $('#move-selected-count').text(count);
    }
    
    $(document).on('change', '.person-checkbox', function() {
        updateSelectedCount();
    });
    
    $('#select-all-people').on('change', function() {
        $('.person-checkbox').prop('checked', $(this).is(':checked'));
        updateSelectedCount();
    });
    
    // ===== Добавление организации =====
    $('#btn-add-org').on('click', function() {
        var parentId = $('.org-node.active').data('org-id') || 1;
        var parentName = $('.org-node.active .org-name').text() || '<?php echo __('Корень'); ?>';
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
        var $node = $(this).closest('.org-node');
        var parentId = $node.data('org-id');
        var parentName = $node.find('.org-name').text();
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
    $(document).on('dblclick', '.org-node', function() {
        var orgId = $(this).data('org-id');
        var currentName = $(this).find('.org-name').text();
        
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
        var $node = $(this).closest('.org-node');
        var orgId = $node.data('org-id');
        var orgName = $node.find('.org-name').text();
        
        if (orgId == 1) {
            alert('<?php echo __('Нельзя удалить корневую организацию'); ?>');
            return;
        }
        
        if (confirm('<?php echo __('Вы уверены, что хотите удалить'); ?> "' + orgName + '"?')) {
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
        var orgId = $('.org-node.active').data('org-id') || 1;
        openEditPersonDialog(0, orgId);
    });
    
    // ===== Редактирование сотрудника =====
    $(document).on('click', '.btn-edit-person', function() {
        var personId = $(this).closest('tr').data('person-id');
        openEditPersonDialog(personId);
    });
    
    // ===== Удаление сотрудника =====
    $(document).on('click', '.btn-delete-person', function() {
        var personId = $(this).closest('tr').data('person-id');
        if (confirm('<?php echo __('Вы уверены, что хотите удалить'); ?>?')) {
            $.ajax({
                url: '<?php echo URL::site('mancard/delete_person'); ?>/' + personId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        loadPeople($('.org-node.active').data('org-id'));
                    } else {
                        alert(response.message);
                    }
                }
            });
        }
    });
    
    // ===== Перемещение сотрудников =====
    $('#btn-move-people').on('click', function() {
        var selected = $('.person-checkbox:checked');
        if (selected.length === 0) {
            alert('<?php echo __('Не выбраны сотрудники'); ?>');
            return;
        }
        updateSelectedCount();
        $('#move-dialog').modal('show');
    });
    
    // ===== Кнопка обновления =====
    $('#btn-refresh').on('click', function() {
        loadPeople($('.org-node.active').data('org-id') || 1);
    });
    
    // ===== Инициализация =====
    initOrgTree();
    
    // Выделяем первую организацию
    if ($('.org-node').length > 0) {
        $('.org-node:first').addClass('active');
        // Загружаем сотрудников для корня
        loadPeople(1);
    }
    updateSelectedCount();
});
</script>

<style>
.org-tree {
    margin: 0;
    padding-left: 0;
}
.org-tree ul {
    padding-left: 20px;
}
.org-node {
    padding: 4px 8px;
    cursor: pointer;
    border-radius: 3px;
    border-left: 3px solid transparent;
    display: flex;
    align-items: center;
}
.org-node:hover {
    background: #f0f0f0;
}
.org-node.active {
    background: #e8f4fd;
    border-left-color: #337ab7;
}
.org-node .org-name {
    flex: 1;
    margin-left: 5px;
}
.org-node .org-actions {
    display: none;
}
.org-node:hover .org-actions {
    display: block;
}
.org-actions .btn {
    padding: 0 3px;
}
#people-table tbody tr {
    cursor: default;
}
#people-table tbody tr:hover {
    background: #f5f5f5;
}
</style>