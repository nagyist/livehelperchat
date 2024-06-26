<?php
$modalHeaderClass = 'pt-1 pb-1 ps-2 pe-2';
$modalHeaderTitle = erTranslationClassLhTranslation::getInstance()->getTranslation('lhsystem/singlesetting','Settings');
$modalSize = 'md';
$modalBodyClass = 'p-1'
?>
<?php include(erLhcoreClassDesign::designtpl('lhkernel/modal_header.tpl.php'));?>

    <form action="<?php echo erLhcoreClassDesign::baseurl('department/edit')?>/<?php echo $department->id?>/(action)/onlinehours" method="post" onsubmit="return lhinst.submitModalForm($(this))">
        <?php include(erLhcoreClassDesign::designtpl('lhkernel/csfr_token.tpl.php'));?>
        <?php if (isset($updated)) : $msg = erTranslationClassLhTranslation::getInstance()->getTranslation('user/account','Updated');?>
            <?php include(erLhcoreClassDesign::designtpl('lhkernel/alert_success.tpl.php'));?>
        <?php endif; ?>
        <div class="modal-body">

            <p><?php echo  erTranslationClassLhTranslation::getInstance()->getTranslation('lhsystem/singlesetting','Ignore operators online statuses and use departments online hours.')?></p>

            <?php $systemconfig = erLhcoreClassModelChatConfig::fetch($attribute);?>
            <div class="form-group">
                <label><input type="checkbox" name="ignore_user_statusValueParam" value="1" <?php if ($systemconfig->value == 1) : ?>checked="checked"<?php endif;?> /> <?php echo  erTranslationClassLhTranslation::getInstance()->getTranslation('lhsystem/singlesetting','For all departments.')?></label>
            </div>

            <div class="form-group">
                <label><input type="checkbox" name="ignore_user_status_dep" value="1" <?php if ($department->ignore_op_status == 1): ?>checked="checked"<?php endif;?> > <?php echo  erTranslationClassLhTranslation::getInstance()->getTranslation('lhsystem/singlesetting','Only for this department.')?></label>
            </div>

        </div>
        <input type="hidden" name="export_action" value="doExport">
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary btn-sm"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Save')?></button>
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/buttons','Close')?></button>
        </div>
    </form>

<?php include(erLhcoreClassDesign::designtpl('lhkernel/modal_footer.tpl.php'));?>