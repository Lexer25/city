<?php
/**
 * Рекурсивный рендеринг дерева организаций
 */
function render_org_node($org, $level = 0) {
    $indent = str_repeat('    ', $level);
    $has_children = isset($org['CHILDREN']) && !empty($org['CHILDREN']);
    $icon = $has_children ? 'fa-folder-open-o' : 'fa-folder-o';
    
    $html = $indent . '<li>' . "\n";
    $html .= $indent . '    <div class="org-node" data-org-id="' . $org['ID_ORG'] . '">' . "\n";
    $html .= $indent . '        <i class="fa ' . $icon . '"></i>' . "\n";
    $html .= $indent . '        <span class="org-name">' . htmlspecialchars($org['NAME']) . '</span>' . "\n";
    $html .= $indent . '        <span class="badge" id="org-count-' . $org['ID_ORG'] . '">' . ($org['PEOPLE_COUNT'] ?? 0) . '</span>' . "\n";
    $html .= $indent . '        <div class="org-actions pull-right">' . "\n";
    $html .= $indent . '            <button class="btn btn-xs btn-info btn-add-child" title="' . __('Добавить подразделение') . '">' . "\n";
    $html .= $indent . '                <i class="fa fa-plus"></i>' . "\n";
    $html .= $indent . '            </button>' . "\n";
    
    if ($org['ID_ORG'] != 1) {
        $html .= $indent . '            <button class="btn btn-xs btn-danger btn-delete-org" title="' . __('Удалить организацию') . '">' . "\n";
        $html .= $indent . '                <i class="fa fa-trash-o"></i>' . "\n";
        $html .= $indent . '            </button>' . "\n";
    }
    
    $html .= $indent . '        </div>' . "\n";
    $html .= $indent . '    </div>' . "\n";
    
    if ($has_children) {
        $html .= $indent . '    <ul class="list-unstyled org-children" data-parent="' . $org['ID_ORG'] . '">' . "\n";
        foreach ($org['CHILDREN'] as $child) {
            $html .= render_org_node($child, $level + 2);
        }
        $html .= $indent . '    </ul>' . "\n";
    }
    
    $html .= $indent . '</li>' . "\n";
    return $html;
}
?>