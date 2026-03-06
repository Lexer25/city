<?php 
// echo Debug::vars('11', $list);
?>

<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title"><?php echo __('people_panel_title'); ?></h3>
  </div>
  <div class="panel-body">
    
    <?php if (empty($list)): ?>
      <div class="alert alert-info">
        <?php echo __('no_records_found'); ?>
      </div>
    <?php else: ?>
      
      <table class="table table-striped table-hover table-condensed table-bordered">
        <tr>
          <th><?php echo __('pep_id'); ?></th>
          <th><?php echo __('name'); ?></th>
          <th><?php echo __('org_name'); ?></th>
          <th><?php echo __('card'); ?></th>
          <th><?php echo __('card_type'); ?></th>
          <th><?php echo __('about_pep_authmode'); ?></th>
          <th><?php echo __('last_event'); ?></th>
        </tr>
        
        <?php foreach ($list as $key => $contact): ?>
          <tr>
            <td><?php echo Arr::get($contact, 'ID_PEP'); ?></td>
            
            <td>
              <?php echo HTML::anchor(
                'people/peopleInfo/' . Arr::get($contact, 'ID_PEP') . '/' . Arr::get($contact, 'ID_CARD', __('No_card')),
                Arr::get($contact, 'SURNAME') . ' ' . Arr::get($contact, 'NAME') . ' ' . Arr::get($contact, 'PATRONYMIC')
              ); ?>
            </td>
            
            <td><?php echo Arr::get($contact, 'ORG_NAME', __('No_organization')); ?></td>
            
            <td><?php echo Arr::get($contact, 'ID_CARD', __('No_card')); ?></td>
            
            <td><?php echo Arr::get($contact, 'CARDTYPENAME', __('No_cardtype')); ?></td>
            
            <td><?php echo Model::factory('stat')->Authmode(Arr::get($contact, 'AUTHMODE', 0)); ?></td>
            
            <td><?php echo Arr::get($contact, 'MAX', __('No_event')); ?></td>
          </tr>
        <?php endforeach; ?>
        
      </table>
      
    <?php endif; ?>
    
  </div>  
</div>