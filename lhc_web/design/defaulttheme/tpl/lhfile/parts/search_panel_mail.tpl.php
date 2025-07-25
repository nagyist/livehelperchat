<form action="<?php echo $input->form_action?>" method="get" id="mailfile-search-form" ng-non-bindable>

    <input type="hidden" name="doSearch" value="1">

    <div class="row">
        <div class="col-6">
            <div class="row">
                <div class="col-3">
                    <div class="form-group">
                        <input type="text" id="mailfile_conversation_id" placeholder="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Conversation ID')?>" class="form-control" name="conversation_id" value="<?php echo htmlspecialchars((string)$input->conversation_id)?>" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <input type="text" id="mailfile_message_id" placeholder="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Message ID')?>" class="form-control" name="message_id" value="<?php echo htmlspecialchars((string)$input->message_id)?>" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <input type="text" id="mailfile_name" placeholder="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','File name')?>" class="form-control" name="name" value="<?php echo htmlspecialchars((string)$input->name)?>" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <input type="text" id="mailfile_id" placeholder="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','File id')?>" class="form-control" name="file_id" value="<?php echo htmlspecialchars((string)$input->file_id)?>" />
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6">
            <div class="row">
                <div class="col-6">
                    <input type="submit" name="doSearch" class="btn btn-secondary" value="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Search');?>" />
                </div>
            </div>
        </div>
    </div>

</form>
