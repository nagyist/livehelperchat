<?php

try {
    erLhcoreClassRestAPIHandler::validateRequest();

    if (!erLhcoreClassRestAPIHandler::hasAccessTo('lhchat', 'use')) {
        throw new Exception('You do not have permission. `lhchat`, `use` is required.');
    }

    $definition = array(
        'chat_id' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::REQUIRED, 'int'
        ),
        'user_id' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL, 'int', array('min_range' => 1)
        ),
        'msg' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::REQUIRED, 'unsafe_raw'
        ),
        'meta_msg' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL, 'unsafe_raw'
        ),
        'operator_name' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL, 'unsafe_raw'
        ),
        'sender' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL, 'string'
        ),
        'status' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL, 'int', array('min_range' => 0)
        )
    );

    $form = new ezcInputForm( INPUT_POST, $definition );

    if (trim($form->msg) != '')
    {
        $db = ezcDbInstance::get();

        try {
            $db->beginTransaction();

            $Chat = erLhcoreClassChat::getSession()->load( 'erLhcoreClassModelChat', $form->chat_id);

            if (!($Chat instanceof erLhcoreClassModelChat)) {
                throw new Exception('Chat could not be found!');
            }

            // Has access to read, chat
            //FIXME create permission to add message...
            if ( erLhcoreClassRestAPIHandler::hasAccessToWrite($Chat) )
            {
                $userData = erLhcoreClassRestAPIHandler::getUser();

                if ($form->sender == 'system') {
                    $messageUserId = -1;
                } else if ($form->sender != 'bot') {
                    $messageUserId = $userData->id;
                } else {
                    $messageUserId = -2;
                }

                if ($form->hasValidData('user_id')) {
                    $messageUserId = $form->user_id;
                    $userData = erLhcoreClassModelUser::fetch($messageUserId);

                    if (!($userData instanceof erLhcoreClassModelUser)) {
                        throw new Exception('Operator with provided user_id could not be found!');
                    }
                }

                $msgText = trim($form->msg);
                $ignoreMessage = false;
                $returnBody = '';
                $customArgs = array();
                $msg = new erLhcoreClassModelmsg();

                if (strpos($msgText, '!') === 0) {
                    $statusCommand = erLhcoreClassChatCommand::processCommand(array('user' => $userData, 'msg' => $msgText, 'chat' => & $Chat));
                    if ($statusCommand['processed'] === true) {
                        $messageUserId = -1; // Message was processed set as internal message

                        $rawMessage = !isset($statusCommand['raw_message']) ? $msgText : $statusCommand['raw_message'];

                        $msgText = trim('[b]'.$userData->name_support.'[/b]: '.$rawMessage .' '. ($statusCommand['process_status'] != '' ? '|| '.$statusCommand['process_status'] : ''));

                        if (isset($statusCommand['ignore']) && $statusCommand['ignore'] == true) {
                            $ignoreMessage = true;
                        }

                        if (isset($statusCommand['info'])) {
                            $tpl = erLhcoreClassTemplate::getInstance('lhchat/lists/assistance_message.tpl.php');
                            $tpl->set('msg',array('msg' =>  $statusCommand['info'], 'time' => time()));
                            $returnBody = $tpl->fetch();
                        }

                        if (isset($statusCommand['custom_args'])) {
                            $customArgs = $statusCommand['custom_args'];
                        }
                    };
                }

                if ($ignoreMessage == false) {
                    $msg->msg = $msgText;
                    $msg->chat_id = $Chat->id;
                    $msg->user_id = $messageUserId;
                    $msg->time = time();

                    if (strpos($msg->msg,'[html]') !== false && !erLhcoreClassRestAPIHandler::hasAccessTo('lhchat','htmlbbcodeenabled')) {
                        $msg->msg = '[html] is disabled for you!';
                        $msg->user_id = -1;
                    }

                    if ($form->hasValidData('meta_msg') && $form->meta_msg != '' && erLhcoreClassRestAPIHandler::hasAccessTo('lhchat','metamsgenabled')) {
                        $metaParts = json_decode($form->meta_msg,true);
                        // Parse meta message as it was bot message and store it within message
                        // We cannot store directly meta message content because it may contain callbacks which can be internal functions
                        // It would be huge security flaw in automated hosting environment
                        if ($metaParts !== null) {
                            $trigger = new erLhcoreClassModelGenericBotTrigger();
                            $trigger->actions_front = $metaParts;
                            $trigger->actions = $form->meta_msg;

                            // Combine all meta messages to single one
                            $messages = erLhcoreClassGenericBotWorkflow::processTriggerPreview($Chat, $trigger, array('args' => array('do_not_save' => true)));

                            $metaData = array();
                            foreach ($messages as $metaMessage) {
                                $metaData = array_merge_recursive($metaData, $metaMessage->meta_msg_array);
                            }

                            $metaData = array_filter($metaData);

                            if (!empty($metaData)) {
                                $msg->meta_msg = json_encode($metaData);
                            }
                        }
                    }

                    if ($form->hasValidData('operator_name') && $form->operator_name != '') {
                        $msg->name_support = $form->operator_name;
                    } elseif ($form->sender == 'bot') {
                        $msg->name_support = erLhcoreClassGenericBotWorkflow::getDefaultNick($Chat);
                    } else {
                        $msg->name_support = $userData->name_support;
                    }

                    if ($messageUserId != -1 && $Chat->chat_locale != '' && $Chat->chat_locale_to != '') {
                        erLhcoreClassTranslate::translateChatMsgOperator($Chat, $msg);
                    }

                    // We want alias only if it's not a bot message
                    if ($msg->user_id != -2) {
                        \LiveHelperChat\Models\Departments\UserDepAlias::getAlias(array('scope' => 'msg', 'msg' => & $msg, 'chat' => & $Chat));
                    }

                    erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.before_msg_admin_saved',array('msg' => & $msg,'chat' => & $Chat));

                    erLhcoreClassChat::getSession()->save($msg);

                    $whisper = false;

                    // Set last message ID
                    if ($Chat->last_msg_id < $msg->id) {

                            $updateFields = array();

                            if (!$whisper && $Chat->status_sub == erLhcoreClassModelChat::STATUS_SUB_ON_HOLD && $messageUserId !== -2 && $messageUserId !== -1 && !isset($Chat->chat_variables_array['lhc_hldu'])) {
                                $updateFields[] = 'status_sub';
                                $updateFields[] = 'last_user_msg_time';
                                $Chat->status_sub = erLhcoreClassModelChat::STATUS_SUB_DEFAULT;
                                $tpl = erLhcoreClassTemplate::getInstance('lhchat/lists/assistance_message.tpl.php');
                                $tpl->set('msg', array('msg' => erTranslationClassLhTranslation::getInstance()->getTranslation('chat/adminchat', 'Hold removed!'), 'time' => time()));
                                $returnBody .= $tpl->fetch();
                                $customArgs['hold_removed'] = true;

                                if ($Chat->auto_responder !== false) {
                                    $Chat->auto_responder->active_send_status = 0;
                                    $Chat->auto_responder->saveThis();
                                }
                            }

                            // Reset active counter if operator send new message and it's sync request and there was new message from operator
                            if (!$whisper && $messageUserId !== -2 && $Chat->status_sub != erLhcoreClassModelChat::STATUS_SUB_ON_HOLD && $Chat->auto_responder !== false) {
                                if ($Chat->auto_responder->active_send_status != 0) {
                                    $Chat->auto_responder->active_send_status = 0;
                                    $Chat->auto_responder->saveThis();
                                }
                            }

                            if (!$whisper) {
                                $Chat->last_op_msg_time = time();
                                $updateFields[] = 'last_op_msg_time';
                            }

                            $Chat->last_msg_id = $msg->id;
                            $updateFields[] = 'last_msg_id';

                            if (!$whisper && $Chat->status != erLhcoreClassModelChat::STATUS_CLOSED_CHAT) {
                                $Chat->has_unread_op_messages = 1;
                                $updateFields[] = 'has_unread_op_messages';
                                if ($Chat->status_sub_sub == erLhcoreClassModelChat::STATUS_SUB_SUB_MSG_DELIVERED) {
                                    $Chat->status_sub_sub = erLhcoreClassModelChat::STATUS_SUB_SUB_DEFAULT;
                                    $updateFields[] = 'status_sub_sub';
                                }
                            }

                            if (!$whisper && $Chat->unread_op_messages_informed != 0) {
                                $Chat->unread_op_messages_informed = 0;
                                $updateFields[] = 'unread_op_messages_informed';
                            }

                            if (!$whisper && $userData->invisible_mode == 0 && $messageUserId > 0) { // Change status only if it's not internal command
                                if ($Chat->status == erLhcoreClassModelChat::STATUS_PENDING_CHAT) {
                                    $Chat->status = erLhcoreClassModelChat::STATUS_ACTIVE_CHAT;
                                    $Chat->status_sub = erLhcoreClassModelChat::STATUS_SUB_OWNER_CHANGED;
                                    $Chat->user_id = $messageUserId;
                                    $updateFields[] = 'status';
                                    $updateFields[] = 'status_sub';
                                    $updateFields[] = 'user_id';
                                }
                            }

                            // Chat can be reopened only if user did not ended chat explictly
                            if (!$whisper && $Chat->user_status == erLhcoreClassModelChat::USER_STATUS_CLOSED_CHAT && $Chat->status_sub != erLhcoreClassModelChat::STATUS_SUB_USER_CLOSED_CHAT) {
                                $Chat->user_status = erLhcoreClassModelChat::USER_STATUS_PENDING_REOPEN;
                                $updateFields[] = 'user_status';
                                if ( ($onlineuser = $Chat->online_user) !== false) {
                                    $onlineuser->reopen_chat = 1;
                                    $onlineuser->saveThis();
                                }
                            }

                            if (!$whisper && $Chat->wait_time == 0 && $messageUserId > 0) {
                                $Chat->wait_time = time() - ($Chat->pnd_time > 0 ? $Chat->pnd_time : $Chat->time);
                                $updateFields[] = 'wait_time';
                            }

                            $Chat->updateThis(array('update' => $updateFields));
                    }

                    // If chat is in bot mode and operators writes a message, accept a chat as operator.
                    if ($form->sender == 'operator' && $Chat->status == erLhcoreClassModelChat::STATUS_BOT_CHAT && $messageUserId != -1) {

                        if (!$whisper && $Chat->status == erLhcoreClassModelChat::STATUS_BOT_CHAT && $messageUserId != -1) {

                            if ($userData->invisible_mode == 0 && erLhcoreClassChat::hasAccessToWrite($Chat)) {
                                $Chat->status = erLhcoreClassModelChat::STATUS_ACTIVE_CHAT;

                                $Chat->pnd_time = time() - 2;
                                $Chat->wait_time = 1;

                                $Chat->user_id = $messageUserId;

                                // If operator takes over and task is not finished we want to unlock text field for visitor
                                if (isset($Chat->chat_variables_array['bot_lock_msg'])) {
                                    $chatVariables = $Chat->chat_variables_array;
                                    unset($chatVariables['bot_lock_msg']);
                                    $Chat->chat_variables_array = $chatVariables;
                                    $Chat->chat_variables = json_encode($chatVariables);
                                }

                                // User status in event of chat acceptance
                                $Chat->usaccept = $userData->hide_online;
                                $Chat->operation_admin .= "lhinst.updateVoteStatus(".$Chat->id.");";
                                $Chat->saveThis();

                                // If chat is transferred to pending state we don't want to process any old events
                                erLhcoreClassGenericBotWorkflow::removePreviousEvents($Chat->id);

                                erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.data_changed',array('chat' => & $Chat, 'user_data' => $userData));

                                erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.accept',array('chat' => & $Chat, 'user_data' => $userData));
                                erLhcoreClassChat::updateActiveChats($Chat->user_id);

                                if ($Chat->department !== false) {
                                    erLhcoreClassChat::updateDepartmentStats($Chat->department);
                                }

                                $options = $Chat->department->inform_options_array;
                                erLhcoreClassChatWorkflow::chatAcceptedWorkflow(array('department' => $Chat->department, 'options' => $options),$Chat);
                            }
                        }
                    }
                }

                if ($Chat->status == erLhcoreClassModelChat::STATUS_OPERATORS_CHAT) {

                    $transfer = erLhcoreClassModelTransfer::findOne(array('filter' => array('transfer_user_id' => $userData->id, 'transfer_to_user_id' => ($Chat->user_id == $userData->id ? $Chat->sender_user_id : $Chat->user_id))));

                    if ($transfer === false) {
                        $transfer = new erLhcoreClassModelTransfer();

                        $transfer->chat_id = $Chat->id;

                        $transfer->from_dep_id = $Chat->dep_id;

                        // User which is transfering
                        $transfer->transfer_user_id = $userData->id;

                        // To what user
                        $transfer->transfer_to_user_id = $Chat->user_id == $userData->id ? $Chat->sender_user_id : $Chat->user_id;
                        $transfer->saveThis();
                    }
                }

                // Chat status change part
                $validStatus = array(
                    erLhcoreClassModelChat::STATUS_PENDING_CHAT,
                    erLhcoreClassModelChat::STATUS_ACTIVE_CHAT,
                    erLhcoreClassModelChat::STATUS_CLOSED_CHAT,
                    erLhcoreClassModelChat::STATUS_CHATBOX_CHAT,
                    erLhcoreClassModelChat::STATUS_OPERATORS_CHAT,
                    erLhcoreClassModelChat::STATUS_BOT_CHAT,
                );

                if ($form->hasValidData('status') && in_array($form->status, $validStatus)) {

                    erLhcoreClassChatHelper::changeStatus(array(
                        'user' => $userData,
                        'chat' => $Chat,
                        'status' => $form->status,
                        'allow_close_remote' => erLhcoreClassRestAPIHandler::hasAccessTo('lhchat', 'allowcloseremote')
                    ));

                    erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.data_changed',array('chat' => & $Chat, 'user_data' => $userData));
                }

                echo erLhcoreClassChat::safe_json_encode(array('error' => false, 'r' => $returnBody, 'msg' => $msg->getState()) + $customArgs);

                erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.web_add_msg_admin', array('msg' => & $msg,'chat' => & $Chat));

            } else {
                throw new Exception('You cannot read this chat!');
            }

            $db->commit();

        } catch (Exception $e) {
            http_response_code(400);
            echo erLhcoreClassRestAPIHandler::outputResponse(array(
                'error' => true,
                'r' => $e->getMessage()
            ));
            $db->rollback();
        }

    } else {
        http_response_code(400);
        echo erLhcoreClassRestAPIHandler::outputResponse(array(
            'error' => true,
            'r' => "Please enter a message!"
        ));
    }
} catch (Exception $e) {
    http_response_code(400);
    echo erLhcoreClassRestAPIHandler::outputResponse(array(
        'error' => true,
        'r' => $e->getMessage()
    ));
}

exit;

?>