<?php
/**
 * Рекурсивный рендеринг узла дерева с отображением карт сотрудников
 * @var array $org - данные организации
 * @var int $level - уровень вложенности
 */
?>
<li class="tree-node" data-org-id="<?php echo $org['ID_ORG']; ?>" data-type="org" data-expanded="false">
    <div class="tree-item tree-item-org">
        <span class="tree-toggle">
            <?php if (isset($org['CHILDREN']) && !empty($org['CHILDREN'])): ?>
                <i class="fa fa-folder-o"></i>
            <?php else: ?>
                <i class="fa fa-folder-o" style="color: #999;"></i>
            <?php endif; ?>
        </span>
        <span class="item-name org-name"><?php echo htmlspecialchars($org['NAME']); ?></span>
        <?php 
        $people_count = 0;
        if (isset($org['PEOPLE_COUNT']) && $org['PEOPLE_COUNT'] > 0) {
            $people_count = $org['PEOPLE_COUNT'];
        }
        ?>
        <?php if ($people_count > 0): ?>
            <span class="badge" style="margin-left: 5px; font-size: 10px; background-color: #337ab7;">
                <i class="fa fa-users"></i> <?php echo $people_count; ?>
            </span>
        <?php endif; ?>
        <?php if (isset($org['CHILDREN']) && !empty($org['CHILDREN'])): ?>
            <span class="badge" style="margin-left: 3px; font-size: 10px; background-color: #5bc0de;">
                <i class="fa fa-folder"></i> <?php echo count($org['CHILDREN']); ?>
            </span>
        <?php endif; ?>
        <div class="org-actions pull-right">
            <button class="btn btn-xs btn-info btn-add-child" title="<?php echo __('Добавить подразделение'); ?>">
                <i class="fa fa-plus"></i>
            </button>
            <button class="btn btn-xs btn-warning btn-rename-org" title="<?php echo __('Переименовать'); ?>">
                <i class="fa fa-pencil"></i>
            </button>
            <?php if ($org['ID_ORG'] != 1): ?>
                <button class="btn btn-xs btn-danger btn-delete-org" title="<?php echo __('Удалить организацию'); ?>">
                    <i class="fa fa-trash-o"></i>
                </button>
            <?php endif; ?>
        </div>
        <span class="id-tooltip">ID_ORG: <?php echo $org['ID_ORG']; ?></span>
    </div>
    
    <?php if (isset($org['CHILDREN']) && !empty($org['CHILDREN'])): ?>
        <ul class="tree-children" style="display: none;">
            <?php foreach ($org['CHILDREN'] as $child): ?>
                <?php echo View::factory('mancard/tree_node_with_cards', array(
                    'org' => $child,
                    'level' => $level + 1
                )); ?>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <ul class="tree-children" style="display: none;"></ul>
    <?php endif; ?>
</li>