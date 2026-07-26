<div class="modal fade" id="edit-person-dialog" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="fa fa-user"></i>
                    <span id="edit-person-title"><?php echo __('Новый сотрудник'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <form id="edit-person-form">
                    <input type="hidden" name="id_pep" id="edit-id-pep" value="0">
                    <input type="hidden" name="id_org" id="edit-id-org" value="1">
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo __('Фамилия'); ?> *</label>
                                <input type="text" class="form-control" name="surname" id="edit-surname" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo __('Имя'); ?> *</label>
                                <input type="text" class="form-control" name="name" id="edit-name" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo __('Отчество'); ?></label>
                                <input type="text" class="form-control" name="patronymic" id="edit-patronymic">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo __('Табельный номер'); ?></label>
                                <input type="text" class="form-control" name="tabnum" id="edit-tabnum">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo __('Логин'); ?></label>
                                <input type="text" class="form-control" name="login" id="edit-login">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo __('Должность'); ?></label>
                                <input type="text" class="form-control" name="post" id="edit-post">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo __('Телефон'); ?></label>
                                <input type="text" class="form-control" name="phonework" id="edit-phonework">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo __('Мобильный телефон'); ?></label>
                                <input type="text" class="form-control" name="phonecellular" id="edit-phonecellular">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo __('Домашний телефон'); ?></label>
                                <input type="text" class="form-control" name="phonehome" id="edit-phonehome">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo __('Дата рождения'); ?></label>
                                <input type="date" class="form-control" name="datebirth" id="edit-datebirth">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label><?php echo __('Место рождения'); ?></label>
                                <input type="text" class="form-control" name="placebirth" id="edit-placebirth">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo __('Адрес проживания'); ?></label>
                                <input type="text" class="form-control" name="placelife" id="edit-placelife">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo __('Адрес регистрации'); ?></label>
                                <input type="text" class="form-control" name="placereg" id="edit-placereg">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo __('Паспорт'); ?></label>
                                <input type="text" class="form-control" name="numdoc" id="edit-numdoc">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo __('Дата выдачи'); ?></label>
                                <input type="date" class="form-control" name="datedoc" id="edit-datedoc">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo __('Кем выдан'); ?></label>
                                <input type="text" class="form-control" name="placedoc" id="edit-placedoc">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo __('Статус'); ?></label>
                                <select class="form-control" name="active" id="edit-active">
                                    <option value="1"><?php echo __('Активен'); ?></option>
                                    <option value="0"><?php echo __('Неактивен'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label><?php echo __('Примечание'); ?></label>
                                <input type="text" class="form-control" name="note" id="edit-note">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><?php echo __('Служебные записи'); ?></label>
                        <textarea class="form-control" name="sysnote" id="edit-sysnote" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> <?php echo __('Отмена'); ?>
                </button>
                <button type="button" class="btn btn-primary" id="btn-save-person">
                    <i class="fa fa-save"></i> <?php echo __('Сохранить'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openEditPersonDialog(personId, orgId) {
    if (personId > 0) {
        // Редактирование существующего
        $('#edit-person-title').text('<?php echo __('Редактировать сотрудника'); ?>');
        
        $.ajax({
            url: '<?php echo URL::site('mancard/get_person'); ?>/' + personId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    $('#edit-id-pep').val(data.ID_PEP);
                    $('#edit-id-org').val(data.ID_ORG);
                    $('#edit-surname').val(data.SURNAME);
                    $('#edit-name').val(data.NAME);
                    $('#edit-patronymic').val(data.PATRONYMIC);
                    $('#edit-tabnum').val(data.TABNUM);
                    $('#edit-login').val(data.LOGIN);
                    $('#edit-post').val(data.POST);
                    $('#edit-phonework').val(data.PHONEWORK);
                    $('#edit-phonecellular').val(data.PHONECELLULAR);
                    $('#edit-phonehome').val(data.PHONEHOME);
                    $('#edit-datebirth').val(data.DATEBIRTH);
                    $('#edit-placebirth').val(data.PLACEBIRTH);
                    $('#edit-placelife').val(data.PLACELIFE);
                    $('#edit-placereg').val(data.PLACEREG);
                    $('#edit-numdoc').val(data.NUMDOC);
                    $('#edit-datedoc').val(data.DATEDOC);
                    $('#edit-placedoc').val(data.PLACEDOC);
                    $('#edit-active').val(data.ACTIVE);
                    $('#edit-note').val(data.NOTE);
                    $('#edit-sysnote').val(data.SYSNOTE);
                    $('#edit-person-dialog').modal('show');
                } else {
                    alert(response.message);
                }
            }
        });
    } else {
        // Новый сотрудник
        $('#edit-person-title').text('<?php echo __('Новый сотрудник'); ?>');
        $('#edit-id-pep').val(0);
        $('#edit-id-org').val(orgId || 1);
        $('#edit-surname').val('');
        $('#edit-name').val('');
        $('#edit-patronymic').val('');
        $('#edit-tabnum').val('');
        $('#edit-login').val('');
        $('#edit-post').val('');
        $('#edit-phonework').val('');
        $('#edit-phonecellular').val('');
        $('#edit-phonehome').val('');
        $('#edit-datebirth').val('');
        $('#edit-placebirth').val('');
        $('#edit-placelife').val('');
        $('#edit-placereg').val('');
        $('#edit-numdoc').val('');
        $('#edit-datedoc').val('');
        $('#edit-placedoc').val('');
        $('#edit-active').val(1);
        $('#edit-note').val('');
        $('#edit-sysnote').val('');
        $('#edit-person-dialog').modal('show');
    }
}

$('#btn-save-person').on('click', function() {
    var form = $('#edit-person-form');
    var data = form.serializeArray();
    var postData = {};
    
    $.each(data, function(i, field) {
        postData[field.name] = field.value;
    });
    
    var personId = $('#edit-id-pep').val();
    var url = personId > 0 ? 
        '<?php echo URL::site('mancard/update_person'); ?>' : 
        '<?php echo URL::site('mancard/add_person'); ?>';
    
    $.ajax({
        url: url,
        type: 'POST',
        data: postData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(response.message);
                $('#edit-person-dialog').modal('hide');
                loadPeople($('.org-node.active').data('org-id') || 1);
            } else {
                alert(response.message);
            }
        }
    });
});

$('#btn-move-confirm').on('click', function() {
    var targetOrgId = $('#target-org').val();
    var personIds = [];
    $('.person-checkbox:checked').each(function() {
        personIds.push($(this).val());
    });
    
    if (personIds.length === 0) {
        alert('<?php echo __('Не выбраны сотрудники'); ?>');
        return;
    }
    
    $.ajax({
        url: '<?php echo URL::site('mancard/move_people'); ?>',
        type: 'POST',
        data: {
            person_ids: personIds,
            target_org_id: targetOrgId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(response.message);
                $('#move-dialog').modal('hide');
                loadPeople($('.org-node.active').data('org-id') || 1);
            } else {
                alert(response.message);
            }
        }
    });
});
</script>