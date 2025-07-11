<?php if ( $chat->department !== false ) : ?>
    <tr>
        <td colspan="2" >
            <h6 class="fw-bold">
                <i class="material-icons">chat</i>
                <?php if ($chat->chat_initiator == erLhcoreClassModelChat::CHAT_INITIATOR_PROACTIVE) : ?>
                    <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/adminchat','Proactive chat')?>
                <?php else : ?>
                    <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/adminchat','Chat')?>
                <?php endif; ?>
                <span class="fs11 ms-2 text-muted fw-normal badge bg-light">
                    <?php if ($chat->status == erLhcoreClassModelChat::STATUS_BOT_CHAT) : ?><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/adminchat','Bot')?>
                    <?php elseif ($chat->status == erLhcoreClassModelChat::STATUS_ACTIVE_CHAT) : ?><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/adminchat','Active')?>
                    <?php elseif ($chat->status == erLhcoreClassModelChat::STATUS_OPERATORS_CHAT) : ?><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/adminchat','Operators')?>
                    <?php elseif ($chat->status == erLhcoreClassModelChat::STATUS_CLOSED_CHAT) : ?><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/adminchat','Closed')?>
                    <?php elseif ($chat->status == erLhcoreClassModelChat::STATUS_PENDING_CHAT) : ?><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/adminchat','Pending')?>
                    <?php endif; ?>
                </span>

                <?php include(erLhcoreClassDesign::designtpl('lhchat/chat_tabs/actions/close_chat.tpl.php'));?>

                <div class="float-end text-muted">
                    <?php include(erLhcoreClassDesign::designtpl('lhchat/chat_tabs/information/thumbs.tpl.php'));?>
                    <i id="chat-id-<?php echo $chat->id?>-mds" data-chat-status="<?php echo $chat->status?>" data-chat-user="<?php echo $chat->user_id?>" class="material-icons<?php if ($chat->has_unread_op_messages == 1) : ?> chat-unread<<?php endif;?>">chat</i>
                    <?php if (isset($canEditChat) && $canEditChat == true && (!isset($hideActionBlock) || $hideActionBlock == false)) : ?>
                        <span class="float-end <?php if (erLhcoreClassUser::instance()->hasAccessTo('lhchat','canchangechatstatus')) : ?> action-image<?php endif?>" id="chat-status-text-<?php echo $chat->id?>" <?php if (erLhcoreClassUser::instance()->hasAccessTo('lhchat','canchangechatstatus')) : ?>title="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/adminchat','Click to change chat status')?>" onclick="return lhc.revealModal({'url':WWW_DIR_JAVASCRIPT +'chat/changestatus/<?php echo $chat->id?>'})"<?php endif;?>>
                            <i class="material-icons me-0" title="<?php if ($chat->status == erLhcoreClassModelChat::STATUS_PENDING_CHAT) : ?><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/adminchat','Pending chat')?><?php elseif ($chat->status == erLhcoreClassModelChat::STATUS_ACTIVE_CHAT) : ?><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/adminchat','Active chat')?><?php elseif ($chat->status == erLhcoreClassModelChat::STATUS_CLOSED_CHAT) : ?><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/adminchat','Closed chat')?><?php elseif ($chat->status == erLhcoreClassModelChat::STATUS_CHATBOX_CHAT) : ?><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/adminchat','Chatbox chat')?><?php elseif ($chat->status == erLhcoreClassModelChat::STATUS_OPERATORS_CHAT) : ?><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/adminchat','Operators chat')?><?php elseif ($chat->status == erLhcoreClassModelChat::STATUS_BOT_CHAT) : ?><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/adminchat','Bot chat')?><?php endif;?>">info_outline</i>
                        </span>
                    <?php endif; ?>
                </div>
            </h6>

            <div class="row text-muted">

                <?php include(erLhcoreClassDesign::designtpl('lhchat/chat_tabs/information_rows/department.tpl.php'));?>

                <?php include(erLhcoreClassDesign::designtpl('lhchat/chat_tabs/information_rows/theme.tpl.php'));?>

                <?php include(erLhcoreClassDesign::designtpl('lhchat/chat_tabs/information_rows/bot.tpl.php'));?>

                <?php if (isset($canEditChat) && $canEditChat == true) : ?>
                    <?php include(erLhcoreClassDesign::designtpl('lhchat/chat_tabs/actions/edit_chat.tpl.php'));?>

                    <?php include(erLhcoreClassDesign::designtpl('lhchat/chat_tabs/actions/delete_chat.tpl.php'));?>

                    <?php include(erLhcoreClassDesign::designtpl('lhchat/chat_tabs/actions/speech.tpl.php'));?>

                    <?php include(erLhcoreClassDesign::designtpl('lhchat/chat_tabs/actions/cobrowse.tpl.php'));?>

                    <?php include(erLhcoreClassDesign::designtpl('lhchat/chat_tabs/actions/escalations.tpl.php'));?>

                <?php endif; ?>

                <?php include(erLhcoreClassDesign::designtpl('lhchat/chat_tabs/chat_translation_tab_pre.tpl.php')); ?>
                <?php if ($chat_translation_tab_enabled == true && erLhcoreClassUser::instance()->hasAccessTo('lhtranslation','use')) : ?>
                    <?php include(erLhcoreClassDesign::designtpl('lhchat/part/translation_action_data.tpl.php')); ?>
                    <?php if ($dataChatTranslation['enable_translations'] && $dataChatTranslation['enable_translations'] == true) : ?>
                        <div class="col-6 pb-1">
                            <a class="text-muted" onclick="lhc.revealModal({'url':WWW_DIR_JAVASCRIPT+'chat/singleaction/<?php echo $chat->id?>/translation'})"><span class="material-icons">language</span><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/translation','Automatic translation')?></a>
                        </div>
                    <?php endif;?>
                <?php endif;?>

                <?php include(erLhcoreClassDesign::designtpl('lhchat/chat_tabs/actions/open_new_window.tpl.php'));?>

                <?php include(erLhcoreClassDesign::designtpl('lhchat/chat_tabs/actions/print.tpl.php'));?>

                <?php include(erLhcoreClassDesign::designtpl('lhchat/chat_tabs/actions/copy_messages.tpl.php'));?>

                <?php include(erLhcoreClassDesign::designtpl('lhchat/chat_tabs/information_rows/id.tpl.php'));?>

                <?php include(erLhcoreClassDesign::designtpl('lhchat/chat_tabs/information_rows/external_chat.tpl.php'));?>

            </div>
        </td>
    </tr>
<?php endif;?>