<?php
/**
 * Таблица типов транспортных серверов
 * @var array $listTsType
 * @var bool $is_logged_in
 */
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Типы транспортных серверов'); ?></h3>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-condensed">
                <thead>
                    <tr>
                        <th><?php echo __('№ п/п'); ?></th>
                        <th><?php echo __('ID'); ?></th>
                        <th><?php echo __('TS_NAME'); ?></th>
                        <th><?php echo __('IS_ENABLED'); ?></th>
                        <th><?php echo __('DESCRIPTION'); ?></th>
                        <th><?php echo __('DATECREATED'); ?></th>
                        <th><?php echo __('DATECHANGE'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
					foreach ($listTsType as $index => $type): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo HTML::chars($type['ID']); ?></td>
                            <td><?php echo HTML::chars($type['NAME']);
									
							?></td>
                            <td>
                                <?php if (!empty($type['IS_ENABLED'])): ?>
                                    <span class="label label-success"><?php echo __('Да'); ?></span>
                                <?php else: ?>
                                    <span class="label label-default"><?php echo __('Нет'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo HTML::chars($type['DESCRIPTION']); ?></td>
                            <td><?php echo HTML::chars($type['DATECREATED']); ?></td>
                            <td><?php echo HTML::chars($type['DATECHANGE']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($is_logged_in): ?>
            <div class="alert alert-info">
                <?php echo __('Для управления типами обратитесь к администратору'); ?>
            </div>
        <?php endif; ?>
    </div>
</div>