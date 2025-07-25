<?php

erLhcoreClassRestAPIHandler::setHeaders();

if (!empty($_GET) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $requestPayload = $_GET;
} else {
    $requestPayload = json_decode(file_get_contents('php://input'),true);
}

$db = ezcDbInstance::get();
$db->beginTransaction();

try {
    if (isset($requestPayload['chat_id'])) {
        $chat = erLhcoreClassModelChat::fetchAndLock($requestPayload['chat_id']);
    } else {
        $chat = false;
    }
} catch (Exception $e) {
    $chat = false;
}

$content = '';
$ott = '';
$LastMessageID = 0;
$firstOperatorMessageId = 0;
$firstVisitorMessageId = 0;
$userOwner = true;
$saveChat = false;
$operation = '';
$operatorId = 0;
$visitorTotalMessages = 0;
$operatorTotalMessages = 0;

$responseArray = array('status' => erLhcoreClassModelChat::STATUS_CLOSED_CHAT, 'status_sub' => erLhcoreClassModelChat::STATUS_SUB_DEFAULT);

if (is_object($chat) && $chat->hash === $requestPayload['hash'])
{
    erLhcoreClassChat::setTimeZoneByChat($chat);
    $chat->updateIgnoreColumns = array('last_msg_id');

    $responseArray['status_sub'] = $chat->status_sub;
    $responseArray['status'] = $chat->status;

	try {

		    if ($chat->auto_responder !== false) {
		        $chat->auto_responder->chat = $chat;
		        $chat->auto_responder->process();
		    }

			if ($chat->reinform_timeout > 0 && $chat->unread_messages_informed == 0 && $chat->has_unread_messages == 1 && (time()-$chat->last_user_msg_time) > $chat->reinform_timeout) {
				$department = $chat->department;
				if ($department !== false) {
					$options = $department->inform_options_array;
					erLhcoreClassChatWorkflow::unreadInformWorkflow(array('department' => $department,'options' => $options),$chat);
				}
			}

            $validStatuses = array(
                erLhcoreClassModelChat::STATUS_PENDING_CHAT,
                erLhcoreClassModelChat::STATUS_ACTIVE_CHAT,
                erLhcoreClassModelChat::STATUS_BOT_CHAT,
            );

            erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.validstatus_chat',array('chat' => & $chat, 'valid_statuses' => & $validStatuses));

            // Sync only if chat is pending or active
			if (in_array($chat->status,$validStatuses) || ($chat->status == erLhcoreClassModelChat::STATUS_CLOSED_CHAT && ($chat->last_op_msg_time == 0 || $chat->last_op_msg_time > time() - (int)erLhcoreClassModelChatConfig::fetch('open_closed_chat_timeout')->current_value))) {
				// Check for new messages only if chat last message id is greater than user last message id
				if (!isset($requestPayload['lmgsid']) || (int)$requestPayload['lmgsid'] < $chat->last_msg_id) {
				    $Messages = erLhcoreClassChat::getPendingMessages((int)$requestPayload['chat_id'], (isset($requestPayload['lmgsid']) ? (int)$requestPayload['lmgsid'] : 0), true);
				    if (count($Messages) > 0)
				    {
                        if ($chat->user_id > 0 && \LiveHelperChat\Models\LHCAbstract\ChatMessagesGhosting::shouldMask($chat->user_id)) {
                             \LiveHelperChat\Models\LHCAbstract\ChatMessagesGhosting::maskVisitorMessages($Messages);
                        }

				        $tpl = erLhcoreClassTemplate::getInstance( 'lhchat/syncuser.tpl.php');
				        $tpl->set('messages',$Messages);
				        $tpl->set('chat',$chat);
				        $tpl->set('sync_mode','');

				        if ($requestPayload['lmgsid'] == 0) {
                            if (isset($requestPayload['new_chat']) && $requestPayload['new_chat'] == true) {
                                $tpl->set('chat_started_now',true);
                                if (isset($requestPayload['old_msg_id']) && is_numeric($requestPayload['old_msg_id']) && $requestPayload['old_msg_id'] > 0) {
                                    $tpl->set('old_msg_id',(int)$requestPayload['old_msg_id']);
                                }
                            }
                            $tpl->set('init_sync',true);
                        } else {
                            $tpl->set('async_call',true);
                        }

				        if (isset($requestPayload['theme']) && ($themeId = erLhcoreClassChat::extractTheme($requestPayload['theme'])) !== false) {
                            $tpl->set('theme',erLhAbstractModelWidgetTheme::fetch($requestPayload['theme']));
                        }

                        $tpl->set('react',true);

                        $content = $tpl->fetch();

                        $operatorId = null;
                        $unreadSince = null;

				        foreach ($Messages as $indexMessage => $msg) {

                            if (($firstOperatorMessageId == 0 || (isset($requestPayload['lmgsid']) && (int)$requestPayload['lmgsid'] == 0 && isset($requestPayload['new_chat']) && $requestPayload['new_chat'] == false)) && ($msg['user_id'] > 0 || $msg['user_id'] == -2) && strpos($content,'id="msg-'.$msg['id'].'"') !== false) {
                                $firstOperatorMessageId = $msg['id'];
                            }

				        	if ($indexMessage === 0 && ($msg['user_id'] > 0 || $msg['user_id'] == -2 && $userOwner === true)) {
				        		$userOwner = false;
				        	}

				        	if ($msg['user_id'] != -1 && $operatorId === null) {
                                $operatorId = (int)$msg['user_id'];
                            }

				        	if ($msg['user_id'] == 0) {
                                $visitorTotalMessages++;

                                if ($firstVisitorMessageId == 0) {
                                    $firstVisitorMessageId = $msg['id'];
                                }

                            } else {
                                $operatorTotalMessages++;
                            }

                            $operatorIdLast = (int)$msg['user_id'];

                            $LastMessageID = $msg['id'];

                            if ($unreadSince === null && ($msg['user_id'] > 0 || $msg['user_id'] == -2) && $msg['del_st'] != 3) {
                                $unreadSince = $msg['id'];
                            }

				        }
				    }
				}

				if ( $chat->is_operator_typing == true /*&& $Params['user_parameters_unordered']['ot'] != 't'*/ ) {
				    erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.syncuser.operator_typing',array('chat' => & $chat));

                    if ($chat->operator_typing_user !== false) {
                        \LiveHelperChat\Models\Departments\UserDepAlias::getAlias(array('scope' => 'typing', 'chat' => $chat));
                    }

					$ott = ($chat->operator_typing_user !== false) ? $chat->operator_typing_user->name_support . ' ' . htmlspecialchars_decode(erTranslationClassLhTranslation::getInstance()->getTranslation('chat/chat','is typing now...'),ENT_QUOTES) : htmlspecialchars_decode(erTranslationClassLhTranslation::getInstance()->getTranslation('chat/chat','Operator is typing now...'),ENT_QUOTES);
				}  elseif (/*$Params['user_parameters_unordered']['ot'] == 't' &&*/ $chat->is_operator_typing == false) {
					$ott = false;
				}
			}

		    // Closed
		    if ($chat->status == erLhcoreClassModelChat::STATUS_CLOSED_CHAT || $chat->status_sub == erLhcoreClassModelChat::STATUS_SUB_USER_CLOSED_CHAT) {
		    	$responseArray['closed'] = true;
		    }

		    $updateFields = array('lsync');
		    if ($chat->status_sub == erLhcoreClassModelChat::STATUS_SUB_OWNER_CHANGED) {
		    	$chat->status_sub = erLhcoreClassModelChat::STATUS_SUB_DEFAULT;
                $updateFields[] = 'status_sub';
		    	$saveChat = true;
		    }

		    if ($chat->status_sub == erLhcoreClassModelChat::STATUS_SUB_SURVEY_SHOW) {
		    	$responseArray['closed'] = true;
		    	if ($chat->status_sub_arg != '') {
		    	    $args = json_decode($chat->status_sub_arg,true);
		    	    $responseArray['closed_arg'] = $args;
		    	}
		    }

		    if ($chat->operation != '') {
		    	$operation = explode("\n", trim($chat->operation));
		    	$chat->operation = '';
                $updateFields[] = 'operation';
		    	$saveChat = true;
		    }

		    if ($chat->user_status != 0) {
		    	$chat->user_status = 0;
                $updateFields[] = 'user_status';
		    	$saveChat = true;
		    }

            if (($chat->has_unread_op_messages == 1 && isset($requestPayload['active_widget']) && $requestPayload['active_widget'] === true) || (isset($requestPayload['lmgsid']) && isset($Messages) && count($Messages) > 0)) {
                if (isset($requestPayload['active_widget']) && $requestPayload['active_widget'] === true) {

                    // Sometimes lock happens. We can ignore those. As this is not a major thing.
                    try {
                        $db->query('UPDATE `lh_msg` SET `del_st` = 3 WHERE `chat_id` = ' . (int)$chat->id . ' AND `del_st` IN (0,1,2) AND (`user_id` > 0 OR `user_id` = -2)');
                    } catch (Exception $e) {

                    }

                    $unreadSince = null;
                    
                    if ($chat->status_sub_sub == erLhcoreClassModelChat::STATUS_SUB_SUB_MSG_DELIVERED) {
                        $chat->status_sub_sub = erLhcoreClassModelChat::STATUS_SUB_SUB_DEFAULT;
                        $updateFields[] = 'status_sub_sub';
                        $saveChat = true;
                    }
                    erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.messages_read',array('chat' => & $chat));
                } else {

                    // Sometimes lock happens. We can ignore those. As this is not a major thing.
                    try {
                        $db->query('UPDATE `lh_msg` SET `del_st` = 2 WHERE `chat_id` = ' . (int)$chat->id . ' AND `del_st` IN (0,1) AND (`user_id` > 0 OR `user_id` = -2)');
                    } catch (Exception $e) {

                    }

                    if ($chat->status_sub_sub == erLhcoreClassModelChat::STATUS_SUB_SUB_DEFAULT) {
                        $chat->status_sub_sub = erLhcoreClassModelChat::STATUS_SUB_SUB_MSG_DELIVERED;
                        $updateFields[] = 'status_sub_sub';
                        $saveChat = true;
                    }
                    erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.messages_delivered',array('chat' => & $chat));
                }
            }

		    if ($chat->has_unread_op_messages == 1 && isset($requestPayload['active_widget']) && $requestPayload['active_widget'] === true)
		    {
		    	$chat->unread_op_messages_informed = 0;
		    	$chat->has_unread_op_messages = 0;
                $chat->unanswered_chat = 0;
                $updateFields[] = 'unread_op_messages_informed';
                $updateFields[] = 'has_unread_op_messages';
                $updateFields[] = 'unanswered_chat';
		    	$saveChat = true;
		    }

		    if (isset($responseArray['closed']) && $responseArray['closed'] == true) {
                $chatVariables = $chat->chat_variables_array;
                if (isset($chatVariables['lhc_ds']) && (int)$chatVariables['lhc_ds'] == 0) {
                    $responseArray['disable_survey'] = true;
                }
            }

            $lockTextArea = isset($chat->chat_variables_array['bot_lock_msg']);

            if ($lockTextArea === true && isset($operatorIdLast) && ($operatorIdLast == -2 || $operatorIdLast > 0) && $chat->chat_variables_array['bot_lock_msg'] < $LastMessageID) {
                $lockTextArea = false;
                $chatVariables = $chat->chat_variables_array;
                unset($chatVariables['bot_lock_msg']);
                $chat->chat_variables_array = $chatVariables;
                $chat->chat_variables = json_encode($chatVariables);
                $updateFields[] = 'chat_variables';
                $saveChat = true;
            }

		    if ($saveChat === true || $chat->lsync < time()-30) {
		        $chat->lsync = time();
		    	$chat->updateThis(array('update' => $updateFields));
		    }

		    erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.syncuser',array('chat' => & $chat, 'response' => & $responseArray));

		$db->commit();

	} catch (Exception $e) {
	    $db->rollback();

        // Store log
        erLhcoreClassLog::write($e->getMessage() . ' - ' . $e->getTraceAsString(),
            ezcLog::SUCCESS_AUDIT,
            array(
                'source' => 'lhc',
                'category' => 'store',
                'line' => $e->getLine(),
                'file' => 'fetchmessages.php',
                'object_id' => $requestPayload['chat_id']
            )
        );
	}

} else {
    $db->rollback();
    $responseArray['closed'] = true;
}

$responseArray['op'] = $operation;
$responseArray['uw'] = $userOwner;
$responseArray['msop'] = $operatorId;
if (isset($operatorIdLast) && $operatorIdLast != $operatorId) {
    $responseArray['lmsop'] = $operatorIdLast;
}
$responseArray['ott'] = $ott;

// Append how many of messages ones are visitor ones
if ($visitorTotalMessages > 0) {
    $responseArray['vtm'] = $visitorTotalMessages;
}

// Append how many of messages ones are visitor ones
if ($operatorTotalMessages > 0) {
    $responseArray['otm'] = $operatorTotalMessages;
}

$responseArray['message_id'] = (int)$LastMessageID;
$responseArray['f_msg_id'] = (isset($requestPayload['lmgsid']) ? (int)$requestPayload['lmgsid'] : 0);

if (isset($requestPayload['lfmsgid']) && (int)$requestPayload['lfmsgid'] > 0) {
    $responseArray['message_id_first'] = max($firstVisitorMessageId,$requestPayload['lfmsgid']); // We want to scroll to first visitor message
} else {
    $responseArray['message_id_first'] = (isset($unreadSince) && is_numeric($unreadSince)) ? $unreadSince : (isset($operatorIdLast) && $operatorIdLast == 0 ? 0 : (int)$firstOperatorMessageId);
}

if (isset($lockTextArea) && $lockTextArea === true) {
    $responseArray['lock_send'] = true;
}

$responseArray['messages'] = trim($content);

echo erLhcoreClassChat::safe_json_encode($responseArray);
exit;

?>