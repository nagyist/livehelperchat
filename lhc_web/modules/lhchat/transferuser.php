<?php

if (is_numeric($Params['user_parameters']['chat_id']) && is_numeric($Params['user_parameters']['item_id']))
{
    $db = ezcDbInstance::get();
    $db->beginTransaction();
    try {

        $transferScope = isset($_POST['obj']) && $_POST['obj'] == 'mail' ? erLhcoreClassModelTransfer::SCOPE_MAIL : erLhcoreClassModelTransfer::SCOPE_CHAT;

        if ($transferScope == erLhcoreClassModelTransfer::SCOPE_CHAT) {
            $Chat = erLhcoreClassModelChat::fetch($Params['user_parameters']['chat_id']);
        } else {
            $Chat = erLhcoreClassModelMailconvConversation::fetch($Params['user_parameters']['chat_id']);
        }

        $errors = array();

        erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.before_chat_transfered', array('chat' => & $Chat, 'errors' => & $errors, 'scope' => $transferScope));

        if ( erLhcoreClassChat::hasAccessToRead($Chat) && empty($errors) )
        {
            $currentUser = erLhcoreClassUser::instance();

            if ( isset($_POST['type']) && $_POST['type'] == 'change_dep' ) {

                if (
                    ($currentUser->hasAccessTo('lhchat','changedepartment') && $transferScope == erLhcoreClassModelTransfer::SCOPE_CHAT) ||
                    ($currentUser->hasAccessTo('lhmailconv','changedepartment') && $transferScope == erLhcoreClassModelTransfer::SCOPE_MAIL)
                ) {

                    $dep = erLhcoreClassModelDepartament::fetch($Params['user_parameters']['item_id']);
                    $departmentFromParent = erLhcoreClassModelDepartament::fetch($Chat->dep_id);
                    $Chat->dep_id = $dep->id;

                    $msg =  ($transferScope == erLhcoreClassModelTransfer::SCOPE_CHAT) ? (new erLhcoreClassModelmsg()) : (new erLhcoreClassModelMailconvMessageInternal());
                    $msg->chat_id = $Chat->id;
                    $msg->user_id = -1;
                    $msg->time = time();
                    $msg->name_support = (string)$currentUser->getUserData()->name_support;

                    \LiveHelperChat\Models\Departments\UserDepAlias::getAlias(array('scope' => 'msg', 'msg' => & $msg, 'chat' => & $Chat, 'user_id' => $currentUser->getUserID()));
                    erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.before_msg_admin_saved', array('msg' => & $msg, 'chat' => & $Chat, 'user_id' => $currentUser->getUserID()));
                    $msg->msg = $msg->name_support . ' ' . erTranslationClassLhTranslation::getInstance()->getTranslation('chat/transferuser', 'has changed department to') . ' ' . $dep . ' '. erTranslationClassLhTranslation::getInstance()->getTranslation('chat/transferuser', 'from') . ' ' . $departmentFromParent;

                    $msg->meta_msg_array = ['content' => ['change_dep_action' => ['user_id' => $currentUser->getUserID(), 'source' => $msg->name_support, 'destination' => (string)$dep, 'source' => $departmentFromParent]]];
                    $msg->meta_msg = json_encode($msg->meta_msg_array);

                    $msg->saveThis();

                    $Chat->last_msg_id = $msg->id;
                    $Chat->last_user_msg_time = time();
                    $Chat->status_sub = erLhcoreClassModelChat::STATUS_SUB_OWNER_CHANGED;
                    $Chat->transfer_uid = $currentUser->getUserID();
                    $Chat->updateThis();

                    $tpl = erLhcoreClassTemplate::getInstance('lhkernel/alert_success.tpl.php');
                    $tpl->set('msg', erTranslationClassLhTranslation::getInstance()->getTranslation('chat/transferuser', 'Chat department was changed to') . ' ' . htmlspecialchars((string)$dep));

                    erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.chat_owner_changed', array('chat' => & $Chat, 'user' => $currentUser->getUserData()));

                    echo json_encode(['error' => 'false', 'result' => $tpl->fetch(), 'chat_id' => $Params['user_parameters']['chat_id']]);
                } else {
                    throw new Exception('You do not have permission to change department!');
                }

            } else if ( isset($_POST['type']) && $_POST['type'] == 'change_owner' ) {

                if (
                    ($currentUser->hasAccessTo('lhchat','changeowner') && $transferScope == erLhcoreClassModelTransfer::SCOPE_CHAT) ||
                    ($currentUser->hasAccessTo('lhmailconv','changeowner') && $transferScope == erLhcoreClassModelTransfer::SCOPE_MAIL)
                ) {

                    $user = erLhcoreClassModelUser::fetch($Params['user_parameters']['item_id']);

                    if ($user instanceof erLhcoreClassModelUser)
                    {

                        $msg = ($transferScope == erLhcoreClassModelTransfer::SCOPE_CHAT) ? (new erLhcoreClassModelmsg()) : (new erLhcoreClassModelMailconvMessageInternal());
                        $msg->chat_id = $Chat->id;
                        $msg->user_id = -1;
                        $msg->time = time();

                        $msg->name_support = (string)$user->name_support;
                        \LiveHelperChat\Models\Departments\UserDepAlias::getAlias(array('scope' => 'msg', 'msg' => & $msg, 'chat' => & $Chat, 'user_id' => $user->id));
                        erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.before_msg_admin_saved', array('msg' => & $msg, 'chat' => & $Chat, 'user_id' => $user->id));
                        $nickTo = $msg->name_support;

                        $msg->name_support = (string)$currentUser->getUserData()->name_support;
                        \LiveHelperChat\Models\Departments\UserDepAlias::getAlias(array('scope' => 'msg', 'msg' => & $msg, 'chat' => & $Chat, 'user_id' => $currentUser->getUserID()));
                        erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.before_msg_admin_saved', array('msg' => & $msg, 'chat' => & $Chat, 'user_id' => $currentUser->getUserID()));
                        $msg->msg = (string)$msg->name_support . ' ' . erTranslationClassLhTranslation::getInstance()->getTranslation('chat/transferuser', 'has changed owner to') . ' ' . $nickTo;

                        $msg->meta_msg_array = ['content' => ['change_owner_action' => ['source_user_id' => $currentUser->getUserID(), 'destination_user_id' => $user->id, 'source' => $msg->name_support, 'destination' => (string)$nickTo]]];
                        $msg->meta_msg = json_encode($msg->meta_msg_array);

                        $msg->saveThis();
                        $Chat->last_msg_id = $msg->id;

                        $oldUserId = 0;

                        if ($Chat->user_id > 0) {
                            $oldUserId = $Chat->user_id;
                        }

                        $Chat->last_msg_id = $msg->id;

                        $Chat->last_user_msg_time = time();

                        $Chat->user_id = $user->id;
                        $Chat->status_sub = erLhcoreClassModelChat::STATUS_SUB_OWNER_CHANGED;
                        $Chat->transfer_uid = $currentUser->getUserID();
                        $Chat->saveThis();

                        erLhcoreClassChat::updateActiveChats($Chat->user_id);

                        if ($oldUserId > 0) {
                            erLhcoreClassChat::updateActiveChats($oldUserId);
                        }

                        $tpl = erLhcoreClassTemplate::getInstance('lhkernel/alert_success.tpl.php');
                        $tpl->set('msg', erTranslationClassLhTranslation::getInstance()->getTranslation('chat/transferuser', 'Chat owner was changed to') . ' ' . htmlspecialchars($user->name_support));

                        erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.chat_owner_changed', array('chat' => & $Chat, 'user' => $user));

                        echo json_encode(['error' => 'false', 'result' => $tpl->fetch(), 'chat_id' => $Params['user_parameters']['chat_id']]);
                    } else {
                        throw new Exception('User could not be found!');
                    }

                } else {
                    throw new Exception('You do not have permission to change owner!');
                }

            } else {

                // Delete any existing transfer for this chat already underway
                $transferLegacy = erLhcoreClassTransfer::getTransferByChat($Params['user_parameters']['chat_id'], $transferScope);

                if (is_array($transferLegacy)) {
                    $chatTransfer = erLhcoreClassTransfer::getSession()->load('erLhcoreClassModelTransfer', $transferLegacy['id']);
                    erLhcoreClassTransfer::getSession()->delete($chatTransfer);
                }

                $Transfer = new erLhcoreClassModelTransfer();
                $Transfer->chat_id = $Chat->id;
                $Transfer->ctime = time();
                $Transfer->transfer_scope = $transferScope;

                $msg = ($transferScope == erLhcoreClassModelTransfer::SCOPE_CHAT) ? (new erLhcoreClassModelmsg()) : (new erLhcoreClassModelMailconvMessageInternal());
                $msg->chat_id = $Chat->id;
                $msg->user_id = -1;

                if (isset($_POST['type']) && $_POST['type'] == 'dep') {
                    $transferConfiguration = erLhcoreClassModelChatConfig::fetch('transfer_configuration')->data;
                    $dep = erLhcoreClassModelDepartament::fetch($Params['user_parameters']['item_id']);

                    $Transfer->dep_id = $dep->id; // Transfer was made to department
                    $departmentFromParent = erLhcoreClassModelDepartament::fetch($Chat->dep_id);
                    
                    if (isset($transferConfiguration['change_department']) && $transferConfiguration['change_department'] == true) {
                        $departmentFrom = $departmentFromParent;
                        $Chat->dep_id = $Transfer->dep_id;

                        // Our new department has transfer rule
                        if ($dep->department_transfer !== false) {
                            $Chat->transfer_if_na = 1;
                            $Chat->transfer_timeout_ac = $dep->transfer_timeout;
                            $Chat->transfer_timeout_ts = time();
                        }
                    }

                    if (isset($transferConfiguration['make_pending']) && $transferConfiguration['make_pending'] == true) {
                        if ($transferScope == erLhcoreClassModelTransfer::SCOPE_CHAT) {
                            $Chat->status = erLhcoreClassModelChat::STATUS_PENDING_CHAT;
                            $Chat->pnd_time = time();
                            $Chat->wait_time = 0;
                        } else {
                            if ($Chat->status != erLhcoreClassModelMailconvConversation::STATUS_CLOSED) {
                                $Chat->status = erLhcoreClassModelMailconvConversation::STATUS_PENDING;
                            }
                        }
                    }

                    $recalculateLoad = 0;
                    if (isset($transferConfiguration['make_unassigned']) && $transferConfiguration['make_unassigned'] == true) {
                        $recalculateLoad = $Chat->user_id;
                        if ($transferScope == erLhcoreClassModelTransfer::SCOPE_CHAT) {
                            $Chat->user_id = 0;
                        } elseif ($Chat->status != erLhcoreClassModelMailconvConversation::STATUS_CLOSED) { // Mail is not closed reset owner
                            $Chat->user_id = 0;
                        }
                    }

                    $msg->name_support = (string)$currentUser->getUserData()->name_support;

                    \LiveHelperChat\Models\Departments\UserDepAlias::getAlias(array('scope' => 'msg', 'msg' => & $msg, 'chat' => & $Chat, 'user_id' => $currentUser->getUserID()));
                    erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.before_msg_admin_saved', array('msg' => & $msg, 'chat' => & $Chat, 'user_id' => $currentUser->getUserID()));

                    $msg->msg = $msg->name_support . ' ' . erTranslationClassLhTranslation::getInstance()->getTranslation('chat/transferuser', 'has transferred chat to') . ' ' . (string)$dep . ' ' . erTranslationClassLhTranslation::getInstance()->getTranslation('chat/transferuser', 'from'). ' ' . $departmentFromParent;

                    $msg->meta_msg_array = ['content' => ['transfer_action_dep' => ['user_id' => $currentUser->getUserID(), 'destination' => (string)$dep, 'source' => $msg->name_support, 'source_dep' => $msg->name_support]]];
                    $msg->meta_msg = json_encode($msg->meta_msg_array);

                } else {
                    $Transfer->transfer_to_user_id = $Params['user_parameters']['item_id']; // Transfer was made to user

                    $userTo = erLhcoreClassModelUser::fetch($Transfer->transfer_to_user_id);
                    $msg->name_support = $userTo->name_support;

                    \LiveHelperChat\Models\Departments\UserDepAlias::getAlias(array('scope' => 'msg', 'msg' => & $msg, 'chat' => & $Chat, 'user_id' => $userTo->id));
                    erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.before_msg_admin_saved', array('msg' => & $msg, 'chat' => & $Chat, 'user_id' => $userTo->id));
                    $userToNick = $msg->name_support;

                    $msg->name_support = (string)$currentUser->getUserData()->name_support;

                    \LiveHelperChat\Models\Departments\UserDepAlias::getAlias(array('scope' => 'msg', 'msg' => & $msg, 'chat' => & $Chat, 'user_id' => $currentUser->getUserID()));
                    erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.before_msg_admin_saved', array('msg' => & $msg, 'chat' => & $Chat, 'user_id' => $currentUser->getUserID()));
                    $msg->msg = $msg->name_support . ' ' . erTranslationClassLhTranslation::getInstance()->getTranslation('chat/transferuser', 'has transferred chat to') . ' ' . (string)$userToNick;

                    $msg->meta_msg_array = ['content' => ['transfer_action_user' => ['user_id' => $currentUser->getUserID(), 'destination' => (string)$userToNick, 'source' => $msg->name_support]]];
                    $msg->meta_msg = json_encode($msg->meta_msg_array);
                }

                $Chat->last_user_msg_time = $msg->time = time();

                // Original department id
                $Transfer->from_dep_id = $Chat->dep_id;

                // User which is transferring
                $Transfer->transfer_user_id = $currentUser->getUserID();

                if (
                    !($transferScope == erLhcoreClassModelTransfer::SCOPE_CHAT && $Chat->user_id == 0 && $Chat->status == erLhcoreClassModelChat::STATUS_PENDING_CHAT) ||
                    !($transferScope == erLhcoreClassModelTransfer::SCOPE_MAIL && $Chat->user_id == 0 && $Chat->status == erLhcoreClassModelMailconvConversation::STATUS_PENDING)
                ) {
                   erLhcoreClassTransfer::getSession()->save($Transfer);
                }

                $tpl = erLhcoreClassTemplate::getInstance('lhkernel/alert_success.tpl.php');
                if (isset($_POST['type']) && $_POST['type'] == 'dep') {
                    $tpl->set('msg', erTranslationClassLhTranslation::getInstance()->getTranslation('chat/transferuser', 'Chat was assigned to selected department'));
                } else {
                    $tpl->set('msg', erTranslationClassLhTranslation::getInstance()->getTranslation('chat/transferuser', 'Chat was assigned to selected user'));
                }

                // Save message
                $msg->saveThis();

                // User who transferred chat
                $Chat->last_msg_id = $msg->id;
                $Chat->transfer_uid = $currentUser->getUserID();
                $Chat->saveThis();

                if (isset($recalculateLoad) && $recalculateLoad > 0) {
                    // Update user who transferred chat statistic
                    erLhcoreClassChat::updateActiveChats($recalculateLoad);
                }

                if (isset($departmentFrom) && $departmentFrom instanceof $departmentFrom) {
                    // Update from department statistic
                    erLhcoreClassChat::updateDepartmentStats($departmentFrom);

                    // Update to department statistic
                    erLhcoreClassChat::updateDepartmentStats($dep);
                }

                erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.chat_transfered', array('scope' => $transferScope, 'chat' => & $Chat, 'transfer' => $Transfer));

                echo json_encode(['error' => 'false', 'result' => $tpl->fetch(), 'chat_id' => $Params['user_parameters']['chat_id']]);
            }

        } elseif (!empty($errors)) {
            $tpl = erLhcoreClassTemplate::getInstance('lhkernel/validation_error.tpl.php');
            $tpl->set('errors', $errors);
            echo json_encode(['error' => 'false', 'result' => $tpl->fetch(), 'chat_id' => $Params['user_parameters']['chat_id']]);
        }
        $db->commit();
    } catch (Exception $e) {
        $db->rollback();

        $tpl = erLhcoreClassTemplate::getInstance('lhkernel/validation_error.tpl.php');
        $tpl->set('errors', array($e->getMessage()));
        echo json_encode(['error' => 'false', 'result' => $tpl->fetch(), 'chat_id' => $Params['user_parameters']['chat_id']]);
    }
}
exit;
?>
