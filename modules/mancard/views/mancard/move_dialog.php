<div class="modal fade" id="move-dialog" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="fa fa-arrows"></i>
                    <?php echo __('Переместить сотрудников'); ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i>
                    <?php echo __('Выбрано сотрудников'); ?>: <span id="move-selected-count">0</span>
                </div>
                
                <div class="form-group">
                    <label for="target-org"><?php echo __('Родительская организация'); ?></label>
                    <select class="form-control" id="target-org">
                        <option value="1"><?php echo __('Корень'); ?></option>
                        <?php if (isset($organizations) && !empty($organizations)): ?>
                            <?php foreach ($organizations as $org): ?>
                                <?php if ($org['ID_ORG'] != 1): ?>
                                    <?php 
                                    // Для PHP 5.6 используем isset вместо ??
                                    $level = isset($org['LEVEL']) ? $org['LEVEL'] : 0;
                                    ?>
                                    <option value="<?php echo $org['ID_ORG']; ?>">
                                        <?php echo str_repeat('—', $level) . ' ' . htmlspecialchars($org['NAME']); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> <?php echo __('Отмена'); ?>
                </button>
                <button type="button" class="btn btn-primary" id="btn-move-confirm">
                    <i class="fa fa-arrows"></i> <?php echo __('Переместить'); ?>
                </button>
            </div>
        </div>
    </div>
</div>