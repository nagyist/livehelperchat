<?php if (isset($errors)) : ?>
	<?php include(erLhcoreClassDesign::designtpl('lhkernel/validation_error.tpl.php'));?>
<?php endif; ?>

<?php if (isset($updated) && $updated == true) : $msg = erTranslationClassLhTranslation::getInstance()->getTranslation('abstract/abstract_form','Updated!'); ?>
	<?php include(erLhcoreClassDesign::designtpl('lhkernel/alert_success.tpl.php'));?>
<?php endif; ?>

<?php
    $fields = $object->getFields();
    $object->languages_ignore; // Just to init
?>

<script>
    <?php if (!empty($object->languages_ignore)) : ?>
        var autoResponderIgnore<?php echo ($object->id > 0 ? $object->id : 0)?> = <?php echo json_encode([['languages' => $object->languages_ignore]]); ?>;
    <?php endif; ?>

    <?php if ($object->languages != '') : ?>
    var autoResponder<?php echo ($object->id > 0 ? $object->id : 0)?> = <?php echo json_encode(json_decode($object->languages,true),JSON_HEX_APOS)?>;
    <?php endif; ?>

    var languageDialects = <?php echo json_encode(array_values(erLhcoreClassModelSpeechLanguageDialect::getDialectsGrouped()))?>;
    window.replaceDepartments = <?php $items = []; foreach (erLhcoreClassModelDepartament::getList(['limit' => false]) as $itemDepartment) { $items[$itemDepartment->id] = $itemDepartment->name; }; echo json_encode($items) ?>;
</script>

<div ng-controller="AutoResponderCtrl as cmsg" class="ng-cloak" ng-cloak ng-init='cmsg.setDialects();<?php if (!empty($object->languages_ignore)) : ?>cmsg.setIgnoreLanguages();<?php endif; ?><?php if ($object->languages != '') : ?>cmsg.setLanguages();<?php endif;?>'>

<div class="form-group">
<label><?php echo $fields['name']['trans'];?></label>
<?php echo erLhcoreClassAbstract::renderInput('name', $fields['name'], $object)?>
</div>

<div class="form-group">
    <label><?php echo erLhcoreClassAbstract::renderInput('disabled', $fields['disabled'], $object)?> <?php echo $fields['disabled']['trans'];?></label>
</div>

<div class="form-group">
<label><?php echo $fields['siteaccess']['trans'];?></label>
<?php echo erLhcoreClassAbstract::renderInput('siteaccess', $fields['siteaccess'], $object)?>
</div>

<div class="form-group"><label><?php echo $fields['position']['trans'];?> <a class="live-help-tooltip" data-placement="top" title="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('abstract/widgettheme','Auto responders with lowest values will be applied first');?>" data-bs-toggle="tooltip"><i class="material-icons">help</i></a></label>
<?php echo erLhcoreClassAbstract::renderInput('position', $fields['position'], $object)?>
</div>

<?php /*
<div class="form-group">
<label><?php echo $fields['dep_id']['trans'];?></label>
<?php echo erLhcoreClassAbstract::renderInput('dep_id', $fields['dep_id'], $object)?>
</div>*/ ?>

<?php include(erLhcoreClassDesign::designtpl('lhabstract/custom/responder/department.tpl.php')); ?>

<div class="row">
    <div class="col-6">
        <div class="form-group">
            <label><?php echo $fields['mint_reset']['trans'];?></label>
            <?php echo erLhcoreClassAbstract::renderInput('mint_reset', $fields['mint_reset'], $object, 70)?>
        </div>
    </div>
    <div class="col-6">
        <div class="form-group">
            <label><?php echo $fields['maxt_reset']['trans'];?></label>
            <?php echo erLhcoreClassAbstract::renderInput('maxt_reset', $fields['maxt_reset'], $object, 120)?>
        </div>
    </div>
</div>

<div class="form-group">
    <label><?php echo erLhcoreClassAbstract::renderInput('dreset_survey', $fields['dreset_survey'], $object)?> <?php echo $fields['dreset_survey']['trans'];?></label>
</div>

<div role="tabpanel">
    <!-- Nav tabs -->
    <ul class="nav nav-tabs mb-2" role="tablist">
        <li role="presentation" class="nav-item"><a class="nav-link active" href="#main-wait-content" aria-controls="main-wait-content" role="tab" data-bs-toggle="tab"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('abstract/widgettheme','Welcome message');?></a></li>
        <li role="presentation" class="nav-item"><a class="nav-link" href="#main-offline-content" aria-controls="main-offline-content" role="tab" data-bs-toggle="tab"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('abstract/widgettheme','Offline message');?></a></li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="main-wait-content">
            <div class="form-group">
                <label><?php echo $fields['wait_message']['trans'];?> <a href="#" onclick="lhc.revealModal({'url':WWW_DIR_JAVASCRIPT+'genericbot/help/cannedreplacerules'});" class="material-icons text-muted">help</a></label>
                <?php $bbcodeOptions = array('selector' => 'textarea[name=AbstractInput_wait_message]'); ?>
                <?php include(erLhcoreClassDesign::designtpl('lhbbcode/toolbar.tpl.php')); ?>
                <?php echo erLhcoreClassAbstract::renderInput('wait_message', $fields['wait_message'], $object)?>
            </div>
            <p><small><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('abstract/proactivechatinvitation','If department is online and visitor starts a chat and is waiting for some to accept chat. This will be initial message they will get.')?></small></p>
        </div>
        <div role="tabpanel" class="tab-pane" id="main-offline-content">
            <div class="form-group">
                <label><?php echo $fields['offline_message']['trans'];?> <a href="#" onclick="lhc.revealModal({'url':WWW_DIR_JAVASCRIPT+'genericbot/help/cannedreplacerules'});" class="material-icons text-muted">help</a></label>
                <?php $bbcodeOptions = array('selector' => 'textarea[name=AbstractInput_offline_message]'); ?>
                <?php include(erLhcoreClassDesign::designtpl('lhbbcode/toolbar.tpl.php')); ?>
                <?php echo erLhcoreClassAbstract::renderInput('offline_message', $fields['offline_message'], $object)?>
                <p><small><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('abstract/proactivechatinvitation','If department is offline and visitor starts a chat this message will be send instaed of default welcome message. If this message is empty - welcome message will be send.')?></small></p>
            </div>
        </div>
    </div>
</div>

    <hr class="border">

<div class="form-group">
<label><?php echo $fields['operator']['trans'];?></label>
<?php echo erLhcoreClassAbstract::renderInput('operator', $fields['operator'], $object)?>
</div>

<div class="form-group">
    <label><?php echo erLhcoreClassAbstract::renderInput('only_proactive', $fields['only_proactive'], $object)?> <?php echo $fields['only_proactive']['trans'];?></label>
</div>

<div role="tabpanel">
    	<!-- Nav tabs -->
    	<ul class="nav nav-tabs mb-2" role="tablist" id="autoresponder-tabs">
    		<li role="presentation" class="nav-item"><a class="nav-link active" href="#pending" aria-controls="pending" role="tab" data-bs-toggle="tab"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('abstract/widgettheme','Pending chat messaging');?></a></li>
    		<li role="presentation" class="nav-item"><a class="nav-link" href="#active" aria-controls="active" role="tab" data-bs-toggle="tab"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('abstract/widgettheme','Visitor not replying messaging');?></a></li>
    		<li role="presentation" class="nav-item"><a class="nav-link" href="#operatornotreply" aria-controls="active" role="tab" data-bs-toggle="tab"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('abstract/widgettheme','Operator not replying messaging');?></a></li>
    		<li role="presentation" class="nav-item"><a class="nav-link" href="#onhold" aria-controls="onhold" role="tab" data-bs-toggle="tab"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('abstract/widgettheme','On-hold chat messaging');?></a></li>
            <li role="presentation" class="nav-item"><a class="nav-link" href="#closeaction" aria-controls="closeaction" role="tab" data-bs-toggle="tab"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('abstract/widgettheme','Close messaging');?></a></li>
    		<li role="presentation" class="nav-item"><a class="nav-link" href="#survey" aria-controls="survey" role="tab" data-bs-toggle="tab"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('abstract/widgettheme','Survey');?></a></li>
    		<li role="presentation" class="nav-item"><a class="nav-link" href="#multilanguage-chat" aria-controls="multilanguage-chat" role="tab" data-bs-toggle="tab"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('abstract/widgettheme','Multi-language chat');?></a></li>

            <lhc-multilanguage-tab identifier="autoResponder" <?php if ($object->languages != '') : ?>init_langauges="<?php echo ($object->id > 0 ? $object->id : 0)?>"<?php endif;?>></lhc-multilanguage-tab>

        </ul>
    
    	<!-- Tab panes -->
    	<div class="tab-content">
    		<div role="tabpanel" class="tab-pane active" id="pending">
    		  <?php include(erLhcoreClassDesign::designtpl('lhabstract/custom/responder/pending.tpl.php'));?>
    		</div>
    		<div role="tabpanel" class="tab-pane" id="active">
    		  <?php include(erLhcoreClassDesign::designtpl('lhabstract/custom/responder/active.tpl.php'));?>
    		</div>
            <div role="tabpanel" class="tab-pane" id="operatornotreply">
    		  <?php include(erLhcoreClassDesign::designtpl('lhabstract/custom/responder/operatornotreply.tpl.php'));?>
    		</div>
            <div role="tabpanel" class="tab-pane" id="onhold">
    		  <?php include(erLhcoreClassDesign::designtpl('lhabstract/custom/responder/onhold.tpl.php'));?>
    		</div>
            <div role="tabpanel" class="tab-pane" id="closeaction">
    		  <?php include(erLhcoreClassDesign::designtpl('lhabstract/custom/responder/closeaction.tpl.php'));?>
    		</div>
            <div role="tabpanel" class="tab-pane" id="survey">
    		  <?php include(erLhcoreClassDesign::designtpl('lhabstract/custom/responder/survey.tpl.php'));?>
    		</div>
            <div role="tabpanel" class="tab-pane" id="multilanguage-chat">
    		  <?php include(erLhcoreClassDesign::designtpl('lhabstract/custom/responder/multilanguage-chat.tpl.php'));?>
    		</div>

            <?php include(erLhcoreClassDesign::designtpl('lhabstract/custom/responder/svelte_languages.tpl.php'));?>

            <lhc-multilanguage-tab-content enable_department="true" identifier="autoResponder" <?php if ($object->languages != '') : ?>init_langauges="<?php echo ($object->id > 0 ? $object->id : 0)?>"<?php endif;?>></lhc-multilanguage-tab-content>

            <?php //include(erLhcoreClassDesign::designtpl('lhabstract/custom/responder/languages.tpl.php'));?>

		</div>
</div>

<div class="btn-group" role="group" aria-label="...">
	<input type="submit" class="btn btn-secondary" name="SaveClient" value="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/buttons','Save');?>"/>
	<input type="submit" class="btn btn-secondary" name="UpdateClient" value="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/buttons','Update');?>"/>
	<input type="submit" class="btn btn-secondary" name="CancelAction" value="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/buttons','Cancel');?>"/>
</div>
    
    <?php include(erLhcoreClassDesign::designtpl('lhabstract/parts/after_form.tpl.php'));?>

</div>

<script>
$('select[name*="AbstractInput_pending_op_bot_id"],select[name*="AbstractInput_nreply_op_bot_id"],select[name="AbstractInput_nreply_bot_id"],select[name="AbstractInput_onhold_bot_id"],select[name*="AbstractInput_nreply_vis_bot_id"]').change(function(){
    var identifier = $(this).attr('name').replace(/AbstractInput_|_bot_id/g,"");
    $.get(WWW_DIR_JAVASCRIPT + 'genericbot/triggersbybot/' + $(this).val() + '/0/(preview)/1/(element)/'+identifier+'_trigger_id', { }, function(data) {
        $('#'+identifier+'-trigger-list-id').html(data);
        renderPreview($('select[name="AbstractInput_'+identifier+'_trigger_id"]'));
    }).fail(function() {

    });
});

var responderItems = [{'id':'nreply_bot_id','val':<?php echo (isset($object->bot_configuration_array['nreply_trigger_id'])) ? $object->bot_configuration_array['nreply_trigger_id'] : 0 ?>},{'id':'onhold_bot_id','val': <?php echo (isset($object->bot_configuration_array['onhold_trigger_id'])) ? $object->bot_configuration_array['onhold_trigger_id'] : 0 ?>}];

<?php for ($i = 1; $i <= 5; $i++)  : ?>
responderItems.push({'id':'nreply_op_bot_id_<?php echo $i?>','val' : <?php echo (isset($object->bot_configuration_array['nreply_op_' . $i .'_trigger_id'])) ? $object->bot_configuration_array['nreply_op_' . $i .'_trigger_id'] : 0 ?>});
responderItems.push({'id':'pending_op_bot_id_<?php echo $i?>','val' : <?php echo (isset($object->bot_configuration_array['pending_op_' . $i .'_trigger_id'])) ? $object->bot_configuration_array['pending_op_' . $i .'_trigger_id'] : 0 ?>});
responderItems.push({'id':'nreply_vis_bot_id_<?php echo $i?>','val' : <?php echo (isset($object->bot_configuration_array['nreply_vis_' . $i .'_trigger_id'])) ? $object->bot_configuration_array['nreply_vis_' . $i .'_trigger_id'] : 0 ?>});
<?php endfor; ?>

$.each(responderItems, function( index, value ) {
    var identifier = value.id.replace(/AbstractInput_|_bot_id/g,"");
    $.get(WWW_DIR_JAVASCRIPT + 'genericbot/triggersbybot/' + $('select[name="AbstractInput_'+value.id+'"]').val() + '/'+value.val+'/(preview)/1/(element)/'+identifier+'_trigger_id', { }, function(data) {
        $('#' + identifier +'-trigger-list-id').html(data);
        if (parseInt(value.val) > 0){
            renderPreview($('select[name="AbstractInput_' + identifier +'_trigger_id"]'));
        }
    }).fail(function() {

    });
});

function renderPreview(inst) {
    if (inst.length == 0) {
        return;
    }
    var identifier = inst.attr('name').replace(/AbstractInput_|_trigger_id/g,"");
    $.get(WWW_DIR_JAVASCRIPT + 'theme/renderpreview/' + inst.val(), { }, function(data) {
        $('#'+identifier+'-trigger-preview-window').html(data);
    }).fail(function() {
        $('#'+identifier+'-trigger-preview-window').html('');
    });
}
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
</script>