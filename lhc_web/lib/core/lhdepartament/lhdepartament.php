<?php

class erLhcoreClassDepartament{

   public static function getDepartaments()
   {
         $db = ezcDbInstance::get();

         $stmt = $db->prepare('SELECT * FROM lh_departament ORDER BY id ASC');
         $stmt->execute();
         $rows = $stmt->fetchAll();

         return $rows;
   }

   public static function sortByStatus($departments) {

	   	$onlineDep = array();
	   	$offlineDep = array();

	   	foreach ($departments as $dep) {
	   		if ($dep->is_online === true){
	   			$onlineDep[] = $dep;
	   		} else {
	   			$offlineDep[] = $dep;
	   		}
	   	}

	   	return array_merge($onlineDep,$offlineDep);
   }

   public static function validateDepartment(erLhcoreClassModelDepartament & $department, $additionalParams = array()) {
   	
	   	$definition = array(
	   			'Name' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'unsafe_raw'
	   			),
                'Alias' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'unsafe_raw'
	   			),
	   			'Email' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'unsafe_raw'
	   			),
	   			'XMPPRecipients' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'unsafe_raw'
	   			),
	   			'XMPPRecipientsGroup' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'unsafe_raw'
	   			),
	   			'Identifier' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'string'
	   			),
	   			'Priority' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'int'
	   			),
	   			'SortPriority' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'int'
	   			),
	   			'TansferDepartmentID' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'int', array('min_range' => 1)
	   			),
	   			'TransferTimeout' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'int', array('min_range' => 5)
	   			),	   			
	   			'delay_lm' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'int', array('min_range' => 5)
	   			),
	   			'hide_survey_bot' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
	   			),
                'OnlineHoursActive' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
	   			),
	   			'Disabled' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
	   			),
	   			'Hidden' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
	   			),
	   			'inform_close' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
	   			),
	   			'inform_unread' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
	   			),
	   			'off_op_exec' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
	   			),
                'no_transfer_no_operators' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
	   			),
                'off_op_work_hours' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
	   			),
                'ru_on_transfer' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
	   			),
                'nc_cb_execute' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
	   			),
	   			'na_cb_execute' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
	   			),
                'off_if_online' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
	   			),
	   			'AutoAssignActive' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
	   			),
                'active_mail_balancing' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
	   			),
	   			'VisibleIfOnline' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
	   			),
                'ExcludeInactiveChats' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
	   			),
                'priority_check' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
	   			),
                'AutoAssignLowerLimit' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
	   			),
	   			'MaxNumberActiveChats' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'int'
	   			),
                'max_ac_dep_mails' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'int'
	   			),
                'max_active_mails' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'int'
	   			),
	   			'MaxWaitTimeoutSeconds' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'int'
	   			),
	   			'max_timeout_seconds_mail' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'int'
	   			)
                ,'MaxNumberActiveDepChats' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'int'
	   			),
	   			'pending_max' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'int'
	   			),
                'delay_before_assign' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'int'
	   			),
                'delay_before_assign_mail' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'int'
	   			),
	   			'inform_unread_delay' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'int',array('min_range' => 5)
	   			),
	   			'inform_delay' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'int',array('min_range' => 0)
	   			),
                'mailbox_id' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'int',array('min_range' => 0)
	   			),
                'transfer_min_priority' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'int'
	   			),
	   			'inform_options' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'string', null, FILTER_REQUIRE_ARRAY
	   			),
	   			'inform_close_all' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
	   			),
	   			'inform_close_all_email' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'string'
	   			),
	   			'DepartamentProducts' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'int', null, FILTER_REQUIRE_ARRAY
	   			),
	   			'products_enabled' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
	   			),
	   			'products_required' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
	   			),
                'assign_same_language' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
	   			),
                'hide_send_email' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
	   			),
                'dep_offline' => new ezcInputFormDefinitionElement(
	   					ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
	   			),
                // Bot attributes
                'bot_id' => new ezcInputFormDefinitionElement(
                        ezcInputFormDefinitionElement::OPTIONAL, 'int', array('min_range' => 1)
                ),
                'bot_tr_id' => new ezcInputFormDefinitionElement(
                        ezcInputFormDefinitionElement::OPTIONAL, 'int', array('min_range' => 1)
                ),
                'theme_ind' => new ezcInputFormDefinitionElement(
                        ezcInputFormDefinitionElement::OPTIONAL, 'int', array('min_range' => 1), FILTER_REQUIRE_ARRAY
                ),
                'bot_only_offline' => new ezcInputFormDefinitionElement(
                        ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
                ),
                'bot_foh' => new ezcInputFormDefinitionElement(
                        ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
                ),
                'archive' => new ezcInputFormDefinitionElement(
                        ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
                ),
                'auto_delay_timeout' => new ezcInputFormDefinitionElement(
                        ezcInputFormDefinitionElement::OPTIONAL, 'int', array('min_range' => 1)
                ),
                'auto_delay_var' => new ezcInputFormDefinitionElement(
                        ezcInputFormDefinitionElement::OPTIONAL, 'unsafe_raw'
                ),
                'bot_debug' => new ezcInputFormDefinitionElement(
                        ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
                ),
                'survey_id' => new ezcInputFormDefinitionElement(
                    ezcInputFormDefinitionElement::OPTIONAL, 'int', array('min_range' => 1)
                ),
                'attr_int_1' => new ezcInputFormDefinitionElement(
                    ezcInputFormDefinitionElement::OPTIONAL, 'int', array('min_range' => 0)
                ),
                'attr_int_2' => new ezcInputFormDefinitionElement(
                    ezcInputFormDefinitionElement::OPTIONAL, 'int', array('min_range' => 0)
                ),
                'attr_int_3' => new ezcInputFormDefinitionElement(
                    ezcInputFormDefinitionElement::OPTIONAL, 'int', array('min_range' => 0)
                ),
                'active_prioritized_assignment' => new ezcInputFormDefinitionElement(
                    ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
                ),
                'assign_by_priority' => new ezcInputFormDefinitionElement(
                    ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
                ),
                'assign_by_priority_chat' => new ezcInputFormDefinitionElement(
                    ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
                ),
                'min_agent_priority' => new ezcInputFormDefinitionElement(
                    ezcInputFormDefinitionElement::OPTIONAL, 'int'
                ),
                'min_chat_priority' => new ezcInputFormDefinitionElement(
                    ezcInputFormDefinitionElement::OPTIONAL, 'int'
                ),
                'max_chat_priority' => new ezcInputFormDefinitionElement(
                    ezcInputFormDefinitionElement::OPTIONAL, 'int'
                ),

        );

        foreach (self::getWeekDays() as $dayShort => $dayLong) {
            $definition[$dayShort] = new ezcInputFormDefinitionElement(
                ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
            );

            $key = 'StartHour'.ucfirst($dayShort);
            $definition[$key] = new ezcInputFormDefinitionElement(
                ezcInputFormDefinitionElement::OPTIONAL, 'int',array('min_range' => 0, 'mx_range' => 23)
            );

            $key = 'StartMinutes'.ucfirst($dayShort);
            $definition[$key] = new ezcInputFormDefinitionElement(
                ezcInputFormDefinitionElement::OPTIONAL, 'int',array('min_range' => 0, 'mx_range' => 59)
            );

            $key = 'EndHour'.ucfirst($dayShort);
            $definition[$key] = new ezcInputFormDefinitionElement(
                ezcInputFormDefinitionElement::OPTIONAL, 'int',array('min_range' => 0, 'mx_range' => 23)
            );

            $key = 'EndMinutes'.ucfirst($dayShort);
            $definition[$key] = new ezcInputFormDefinitionElement(
                ezcInputFormDefinitionElement::OPTIONAL, 'int',array('min_range' => 0, 'mx_range' => 59)
            );
        }

	   	 if (isset($additionalParams['payload_data'])) {
            $form = new erLhcoreClassInputForm(INPUT_GET, $definition, null, $additionalParams['payload_data']);
        } else {
	   	    $form = new ezcInputForm( INPUT_POST, $definition );
	   	 }

	   	$Errors = array();
	   	
	   	if ( !$form->hasValidData( 'Name' ) || $form->Name == '' )
	   	{
	   		$Errors[] =  erTranslationClassLhTranslation::getInstance()->getTranslation('departament/edit','Please enter a department name');
	   	} else {
	   		$department->name = $form->Name;
	   	}

        if (erLhcoreClassUser::instance()->hasAccessTo('lhdepartment','managealias') == true) {
            if ( $form->hasValidData( 'Alias' )  )
            {
                $department->alias = trim($form->Alias);
                if (is_numeric($department->alias)) {
                    $Errors[] = erTranslationClassLhTranslation::getInstance()->getTranslation('departament/edit','Alias should be not a plain number!');
                }
            }
        }

       $botConfiguration = $department->bot_configuration_array;

	   	if ((isset($additionalParams['payload_data']) && erLhcoreClassRestAPIHandler::hasAccessTo('lhdepartment', 'actautoassignment')) || erLhcoreClassUser::instance()->hasAccessTo('lhdepartment','actautoassignment') ) {
		   	if ( $form->hasValidData( 'AutoAssignActive' ) && $form->AutoAssignActive == true )	{
		   		$department->active_balancing = 1;
		   	} else {
		   		$department->active_balancing = 0;
		   	}

            if ( $form->hasValidData( 'active_mail_balancing' ) && $form->active_mail_balancing == true )	{
		   		$department->active_mail_balancing = 1;
		   	} else {
		   		$department->active_mail_balancing = 0;
		   	}

		   	if ( $form->hasValidData( 'assign_same_language' ) && $form->assign_same_language == true )	{
		   		$department->assign_same_language = 1;
		   	} else {
		   		$department->assign_same_language = 0;
		   	}
		   	
		   	if ( $form->hasValidData( 'MaxNumberActiveChats' ) )	{
		   		$department->max_active_chats = $form->MaxNumberActiveChats;
		   	} else {
		   		$department->max_active_chats = 0;
		   	}

            if ( $form->hasValidData( 'max_active_mails' ) )	{
		   		$department->max_active_mails = $form->max_active_mails;
		   	} else {
		   		$department->max_active_mails = 0;
		   	}
		   	
		   	if ( $form->hasValidData( 'MaxWaitTimeoutSeconds' ) )	{
		   		$department->max_timeout_seconds = $form->MaxWaitTimeoutSeconds;
		   	} else {
		   		$department->max_timeout_seconds = 0;
		   	}

		   	if ( $form->hasValidData( 'max_timeout_seconds_mail' ) )	{
		   		$department->max_timeout_seconds_mail = $form->max_timeout_seconds_mail;
		   	} else {
		   		$department->max_timeout_seconds_mail = 0;
		   	}

		   	if ( $form->hasValidData( 'delay_before_assign' ) )	{
		   		$department->delay_before_assign = $form->delay_before_assign;
		   	} else {
		   		$department->delay_before_assign = 0;
		   	}

		   	if ( $form->hasValidData( 'delay_before_assign_mail' ) )	{
		   		$department->delay_before_assign_mail = $form->delay_before_assign_mail;
		   	} else {
		   		$department->delay_before_assign_mail = 0;
		   	}

		   	if ( $form->hasValidData( 'ExcludeInactiveChats' ) )	{
		   		$department->exclude_inactive_chats = $form->ExcludeInactiveChats;
		   	} else {
		   		$department->exclude_inactive_chats = 0;
		   	}

		   	if ( $form->hasValidData( 'AutoAssignLowerLimit' ) )	{
                $botConfiguration['auto_lower_limit'] = $form->AutoAssignLowerLimit;
		   	} else {
                $botConfiguration['auto_lower_limit'] = 0;
		   	}

		   	if ( $form->hasValidData( 'MaxNumberActiveDepChats' ) )	{
		   		$department->max_ac_dep_chats = $form->MaxNumberActiveDepChats;
		   	} else {
		   		$department->max_ac_dep_chats = 0;
		   	}


		   	if ( $form->hasValidData( 'max_ac_dep_mails' ) )	{
		   		$department->max_ac_dep_mails = $form->max_ac_dep_mails;
		   	} else {
		   		$department->max_ac_dep_mails = 0;
		   	}

            if ( $form->hasValidData( 'assign_by_priority' ) && $form->assign_by_priority == true )	{
                $botConfiguration['assign_by_priority'] = 1;
            } else {
                $botConfiguration['assign_by_priority'] = 0;
            }

            if ( $form->hasValidData( 'active_prioritized_assignment' ) && $form->active_prioritized_assignment == true )	{
                $botConfiguration['active_prioritized_assignment'] = 1;
            } else {
                $botConfiguration['active_prioritized_assignment'] = 0;
            }

            if ( $form->hasValidData( 'assign_by_priority_chat' ) && $form->assign_by_priority_chat == true ) {
                $botConfiguration['assign_by_priority_chat'] = 1;
            } else {
                $botConfiguration['assign_by_priority_chat'] = 0;
            }

            if ( $form->hasValidData( 'min_agent_priority' ) ) {
                $botConfiguration['min_agent_priority'] = $form->min_agent_priority;
            } else {
                $botConfiguration['min_agent_priority'] = 0;
            }

            if ($form->hasValidData( 'priority_check' ) && $form->priority_check == true) {
                $botConfiguration['priority_check'] = 1;
            } else {
                $botConfiguration['priority_check'] = 0;
            }

            if ( $form->hasValidData( 'min_chat_priority' ) ) {
                $botConfiguration['min_chat_priority'] = $form->min_chat_priority;
            } else {
                $botConfiguration['min_chat_priority'] = 0;
            }

            if ( $form->hasValidData( 'max_chat_priority' ) ) {
                $botConfiguration['max_chat_priority'] = $form->max_chat_priority;
            } else {
                $botConfiguration['max_chat_priority'] = 0;
            }

	   	}

	   	if ((isset($additionalParams['payload_data']) && erLhcoreClassRestAPIHandler::hasAccessTo('lhdepartment', 'actworkflow')) || erLhcoreClassUser::instance()->hasAccessTo('lhdepartment','actworkflow') ) {
		   	if ( $form->hasValidData( 'TansferDepartmentID' ) )
		   	{
		   		$department->department_transfer_id = $form->TansferDepartmentID;
		   	} else {
		   		$department->department_transfer_id = 0;
		   	}
		   	
		   	if ( $form->hasValidData( 'TransferTimeout' ) )
		   	{
		   		$department->transfer_timeout = $form->TransferTimeout;
		   	} else {
		   		$department->transfer_timeout = 0;
		   	}
		   			   	
		   	if ( $form->hasValidData( 'nc_cb_execute' ) && $form->nc_cb_execute == true )
		   	{
		   		$department->nc_cb_execute = 1;
		   	} else {
		   		$department->nc_cb_execute = 0;
		   	}		   	

		   	if ( $form->hasValidData( 'off_op_exec' ) && $form->off_op_exec == true )
		   	{
		   		$botConfiguration['off_op_exec'] = 1;
		   	} else {
                $botConfiguration['off_op_exec'] = 0;
		   	}

		   	if ( $form->hasValidData( 'no_transfer_no_operators' ) && $form->no_transfer_no_operators == true )
		   	{
		   		$botConfiguration['no_transfer_no_operators'] = 1;
		   	} else {
                $botConfiguration['no_transfer_no_operators'] = 0;
		   	}

		   	if ( $form->hasValidData( 'off_op_work_hours' ) && $form->off_op_work_hours == true) {
		   		$botConfiguration['off_op_work_hours'] = 1;
		   	} else {
                $botConfiguration['off_op_work_hours'] = 0;
		   	}

		   	if ( $form->hasValidData( 'ru_on_transfer' ) && $form->ru_on_transfer == true )
		   	{
		   		$botConfiguration['ru_on_transfer'] = 1;
		   	} else {
                $botConfiguration['ru_on_transfer'] = 0;
		   	}

		   	if ( $form->hasValidData( 'na_cb_execute' ) && $form->na_cb_execute == true )
		   	{
		   		$department->na_cb_execute = 1;
		   	} else {
		   		$department->na_cb_execute = 0;
		   	}

		   	if ( $form->hasValidData( 'off_if_online' ) && $form->off_if_online == true )	{
                $botConfiguration['off_if_online'] = 1;
		   	} else {
		   		$botConfiguration['off_if_online'] = 0;
		   	}
	   	}
	   	
	   	if ( $form->hasValidData( 'Identifier' ) )
	   	{
	   		$department->identifier = $form->Identifier;
	   	}
	   	
	   	if ( $form->hasValidData( 'delay_lm' ) )
	   	{
	   		$department->delay_lm = $form->delay_lm;
	   	} else {
	   		$department->delay_lm = 0;
	   	}

	   	if ( $form->hasValidData( 'dep_offline' ) ) {
	   		$department->dep_offline = 1;
	   	} else {
	   		$department->dep_offline = 0;
	   	}

	   	if ( $form->hasValidData( 'pending_max' ) )
	   	{
	   		$department->pending_max = $form->pending_max;
	   	} else {
	   		$department->pending_max = 0;
	   	}

	   	if ( $form->hasValidData( 'attr_int_1' ) )
	   	{
	   		$department->attr_int_1 = $form->attr_int_1;
	   	}

	   	if ( $form->hasValidData( 'attr_int_2' ) )
	   	{
	   		$department->attr_int_2 = $form->attr_int_2;
	   	}

	   	if ( $form->hasValidData( 'attr_int_3' ) )
	   	{
	   		$department->attr_int_3 = $form->attr_int_3;
	   	}
	   	
	   	if ( $form->hasValidData( 'Email' ) ) {	   	
	   		$partsEmail = explode(',', $form->Email);
	   		$validatedEmail = array();
	   		foreach ($partsEmail as $email){
	   			if (filter_var(trim($email), FILTER_VALIDATE_EMAIL)){
	   				$validatedEmail[] = trim($email);
	   			}
	   		}	   	
	   		$department->email = implode(',', $validatedEmail);	   	
	   	} else {
	   		$department->email = '';
	   	}
	   	
	   	if ( $form->hasValidData( 'XMPPRecipients' ) ) {	   	
	   		$department->xmpp_recipients = $form->XMPPRecipients;	   			   	
	   	} else {
	   		$department->xmpp_recipients = '';
	   	}
	   	
	   	if ( $form->hasValidData( 'XMPPRecipientsGroup' ) ) {	   	
	   		$department->xmpp_group_recipients = $form->XMPPRecipientsGroup;	   			   	
	   	} else {
	   		$department->xmpp_group_recipients = '';
	   	}
	   	
	   	if ( $form->hasValidData( 'Priority' ) ) {
	   		$department->priority = $form->Priority;
	   	} else {
	   		$department->priority = 0;
	   	}
	   	
	   	if ( $form->hasValidData( 'SortPriority' ) ) {
	   		$department->sort_priority = $form->SortPriority;
	   	} else {
	   		$department->sort_priority = 0;
	   	}
	   		   	
	   	if ( $form->hasValidData( 'inform_close' ) && $form->inform_close === true ) {
	   		$department->inform_close = 1;
	   	} else {
	   		$department->inform_close = 0;
	   	}
	   		   	
	   	if ( $form->hasValidData( 'inform_close_all' ) && $form->inform_close_all === true ) {
	   		$department->inform_close_all = 1;
	   	} else {
	   		$department->inform_close_all = 0;
	   	}

	   	if ( $form->hasValidData( 'inform_close_all_email' ) ) {
	   		$department->inform_close_all_email = $form->inform_close_all_email;
	   	} else {
	   		$department->inform_close_all_email = '';
	   	}

	   	if ( $form->hasValidData( 'inform_unread' ) && $form->inform_unread === true ) {
	   		$department->inform_unread = 1;
	   	} else {
	   		$department->inform_unread = 0;
	   	}
	   	
	   	if ( $form->hasValidData( 'archive' ) && $form->archive === true ) {
	   		$department->archive = 1;
	   	} else {
	   		$department->archive = 0;
	   	}
	   	
	   	if ( $form->hasValidData( 'VisibleIfOnline' ) && $form->VisibleIfOnline === true ) {
	   		$department->visible_if_online = 1;
	   	} else {
	   		$department->visible_if_online = 0;
	   	}
	   		   	
	   	if ($form->hasValidData( 'inform_unread_delay' )) {
	   		$department->inform_unread_delay = $form->inform_unread_delay;
	   	} elseif ($department->inform_unread == 1) {
	   		$Errors[] = erTranslationClassLhTranslation::getInstance()->getTranslation('departament/edit','Minimum 5 seconds');
	   	} else {
	   		$department->inform_unread_delay = 0;
	   	}
	   		   	
	   	if ( $form->hasValidData( 'Disabled' ) && $form->Disabled === true ) {
	   		$department->disabled = 1;
	   	} else {
	   		$department->disabled = 0;
	   	}
	   		   	
	   	if ( $form->hasValidData( 'Hidden' ) && $form->Hidden === true ) {
	   		$department->hidden = 1;
	   	} else {
	   		$department->hidden = 0;
	   	}
	   		   	
	   	if ( $form->hasValidData( 'OnlineHoursActive' ) && $form->OnlineHoursActive === true ) {
	   		$department->online_hours_active = 1;
	   	} else {
	   		$department->online_hours_active = 0;
	   	}

	   	$productsConfiguration = $department->product_configuration_array;
	   	
	   	if ( $form->hasValidData( 'products_enabled' ) && $form->products_enabled === true ) {
	   		$productsConfiguration['products_enabled'] = 1;
	   	} else {
	   		$productsConfiguration['products_enabled'] = 0;
	   	}
	   	
	   	if ( $form->hasValidData( 'products_required' ) && $form->products_required === true ) {
	   		$productsConfiguration['products_required'] = 1;
	   	} else {
	   		$productsConfiguration['products_required'] = 0;
	   	}
	   	
	   	$department->product_configuration_array = $productsConfiguration;
	   	$department->product_configuration = json_encode($productsConfiguration);
	   	
	   	if ( $form->hasValidData( 'inform_options' ) ) {
	   		$department->inform_options = serialize($form->inform_options);
	   		$department->inform_options_array = $form->inform_options;
	   	} else {
	   		$department->inform_options = serialize(array());
	   	}

	   	if ( $form->hasValidData( 'inform_delay' )  ) {
	   		$department->inform_delay = $form->inform_delay;
	   	} else {
	   		$department->inform_delay = 0;
	   	}
	   	
	   	if ($department->id > 0 && $department->department_transfer_id == $department->id) {
	   		$Errors[] =  erTranslationClassLhTranslation::getInstance()->getTranslation('departament/edit','Transfer department has to be different one than self');
	   	}

       foreach (self::getWeekDays() as $dayShort => $dayLong) {
           if($form->hasValidData( $dayShort ) && $form->$dayShort === true) {
               $key = 'StartHour' . ucfirst($dayShort);
               if ($form->hasValidData($key)) {
                   $startHour = $form->$key;
               } else {
                   $startHour = 0;
               }

               $key = 'EndHour' . ucfirst($dayShort);
               if ($form->hasValidData($key)) {
                   $endHour = $form->$key;
               } else {
                   $endHour = 0;
               }

               $key = 'StartMinutes' . ucfirst($dayShort);
               if ($form->hasValidData($key)) {
                   $StartMinutes = str_pad($form->$key, 2, '0', STR_PAD_LEFT);
               } else {
                   $StartMinutes = '00';
               }

               $key = 'EndMinutes' . ucfirst($dayShort);
               if ($form->hasValidData($key)) {
                   $endHourMinutes = str_pad($form->$key, 2, '0', STR_PAD_LEFT);
               } else {
                   $endHourMinutes = '00';
               }

               $key = $dayShort . '_start_hour';
               $department->$key = $startHour . $StartMinutes;

               $key = $dayShort . '_end_hour';
               $department->$key = $endHour . $endHourMinutes;
           } else {
               $key = $dayShort . '_start_hour';
               $department->$key = -1;

               $key = $dayShort . '_end_hour';
               $department->$key = -1;
           }
       }
       
       if ( $form->hasValidData( 'DepartamentProducts' ) && !empty($form->DepartamentProducts)) {
           $department->departament_products_id = $form->DepartamentProducts;
       } else {
           $department->departament_products_id = array();
       }

       if ( $form->hasValidData( 'bot_id' ) )
       {
           $botConfiguration['bot_id'] = $form->bot_id;
       } else {
           $botConfiguration['bot_id'] = 0;
       }

       if ( $form->hasValidData( 'mailbox_id' ) )
       {
           $botConfiguration['mailbox_id'] = $form->mailbox_id;
       } else {
           $botConfiguration['mailbox_id'] = 0;
       }

       if ( $form->hasValidData( 'transfer_min_priority' ) ) {
           $botConfiguration['transfer_min_priority'] = $form->transfer_min_priority;
       } else {
           $botConfiguration['transfer_min_priority'] = '';
       }

       if ( $form->hasValidData( 'bot_tr_id' ) )
       {
           $botConfiguration['bot_tr_id'] = $form->bot_tr_id;
       } else {
           $botConfiguration['bot_tr_id'] = 0;
       }

       if ( $form->hasValidData( 'theme_ind' ) )
       {
           $botConfiguration['theme_ind'] = implode(',',$form->theme_ind);
       } else {
           $botConfiguration['theme_ind'] = 0;
       }

       if ((isset($additionalParams['payload_data']) && erLhcoreClassRestAPIHandler::hasAccessTo('lhdepartment', 'managesurvey')) || erLhcoreClassUser::instance()->hasAccessTo('lhdepartment', 'managesurvey')) {
           if ($form->hasValidData('survey_id')) {
               $botConfiguration['survey_id'] = $form->survey_id;
           } else {
               $botConfiguration['survey_id'] = 0;
           }
       }

       if ( $form->hasValidData( 'bot_only_offline' ) ) {
           $botConfiguration['bot_only_offline'] = true;
       } else {
           $botConfiguration['bot_only_offline'] = false;
       }

       if ( $form->hasValidData( 'hide_survey_bot' ) ) {
           $botConfiguration['hide_survey_bot'] = true;
       } else {
           $botConfiguration['hide_survey_bot'] = false;
       }

       if ( $form->hasValidData( 'bot_foh' ) ) {
           $botConfiguration['bot_foh'] = true;
       } else {
           $botConfiguration['bot_foh'] = false;
       }

       if ( $form->hasValidData( 'bot_debug' ) ) {
           $botConfiguration['bot_debug'] = true;
       } elseif (isset($botConfiguration['bot_debug'])) {
           unset($botConfiguration['bot_debug']);
       }

       if ( $form->hasValidData( 'auto_delay_timeout' ) ) {
           $botConfiguration['auto_delay_timeout'] = $form->auto_delay_timeout;
       } else {
           $botConfiguration['auto_delay_timeout'] = 0;
       }

       if ( $form->hasValidData( 'auto_delay_var' ) ) {
           $botConfiguration['auto_delay_var'] = $form->auto_delay_var;
       } else {
           $botConfiguration['auto_delay_var'] = '';
       }

       if ( $form->hasValidData( 'hide_send_email' ) && $form->hide_send_email === true ) {
           $botConfiguration['hide_send_email'] = true;
       } else {
           $botConfiguration['hide_send_email'] = false;
       }

       $department->bot_configuration_array = $botConfiguration;
       $department->bot_configuration = json_encode($botConfiguration);

	   return $Errors;   	
   }

   public static function validateDepartmentProducts(erLhcoreClassModelDepartament $departament)
   {
       /**
        * Remove old
        */
       $db = ezcDbInstance::get();
       $stmt = $db->prepare('DELETE FROM lh_abstract_product_departament WHERE departament_id = :departament_id');
       $stmt->bindValue(':departament_id',$departament->id,PDO::PARAM_INT);
       $stmt->execute();
       
       if (is_array($departament->departament_products_id)) {
           foreach ($departament->departament_products_id as $id) {
               $item = new erLhAbstractModelProductDepartament();
               $item->product_id = $id;
               $item->departament_id = $departament->id;
               $item->saveThis();
           }
       }       
   }

    /**
     * validate and saves/removes department custom work hours, and return result of current custom work hours
     *
     * @param erLhcoreClassModelDepartament $departament
     * @param erLhcoreClassModelDepartamentCustomWorkHours[] $departamentCustomWorkHours
     * @return erLhcoreClassModelDepartamentCustomWorkHours[]
     */
   public static function validateDepartmentCustomWorkHours(erLhcoreClassModelDepartament $departament, $departamentCustomWorkHours = array())
   {
       $availableCustomWorkHours = array();

       $definition = array(
           'customPeriodId' => new ezcInputFormDefinitionElement(
               ezcInputFormDefinitionElement::OPTIONAL, 'unsafe_raw', null, FILTER_REQUIRE_ARRAY
           ),
           'customPeriodDateFrom' => new ezcInputFormDefinitionElement(
               ezcInputFormDefinitionElement::OPTIONAL, 'unsafe_raw', null, FILTER_REQUIRE_ARRAY
           ),
           'customPeriodDateTo' => new ezcInputFormDefinitionElement(
               ezcInputFormDefinitionElement::OPTIONAL, 'unsafe_raw', null, FILTER_REQUIRE_ARRAY
           ),
           'customPeriodStartHour' => new ezcInputFormDefinitionElement(
               ezcInputFormDefinitionElement::OPTIONAL, 'unsafe_raw', null, FILTER_REQUIRE_ARRAY
           ),
           'customPeriodStartHourMin' => new ezcInputFormDefinitionElement(
               ezcInputFormDefinitionElement::OPTIONAL, 'unsafe_raw', null, FILTER_REQUIRE_ARRAY
           ),
           'customPeriodEndHour' => new ezcInputFormDefinitionElement(
               ezcInputFormDefinitionElement::OPTIONAL, 'unsafe_raw', null, FILTER_REQUIRE_ARRAY
           ),
           'customPeriodEndHourMin' => new ezcInputFormDefinitionElement(
               ezcInputFormDefinitionElement::OPTIONAL, 'unsafe_raw', null, FILTER_REQUIRE_ARRAY
           ),
           'customPeriodRepetitiveness' => new ezcInputFormDefinitionElement(
               ezcInputFormDefinitionElement::OPTIONAL, 'int', null, FILTER_REQUIRE_ARRAY
           ),
           'customPeriodDayOfWeek' => new ezcInputFormDefinitionElement(
               ezcInputFormDefinitionElement::OPTIONAL, 'int', null, FILTER_REQUIRE_ARRAY
           ),
       );

       $form = new ezcInputForm( INPUT_POST, $definition );

       if ( $form->hasValidData( 'customPeriodId' ) && !empty($form->customPeriodId)) {
           foreach ($form->customPeriodId as $key => $customPeriodId) {
               if (!$customPeriodId) {
                   // if id is not defined save new custom departament work hours
                   $newDepartamentCustomWorkHours = new erLhcoreClassModelDepartamentCustomWorkHours();
                   $newDepartamentCustomWorkHours->setState(array(
                       'dep_id'         => $departament->id,
                       'date_from'      => ($form->customPeriodRepetitiveness[$key] == 0 ? strtotime($form->customPeriodDateFrom[$key]) : $form->customPeriodDayOfWeek[$key]),
                       'date_to'        => strtotime($form->customPeriodDateTo[$key]),
                       'start_hour'     => $form->customPeriodStartHour[$key] . (($form->customPeriodStartHourMin[$key] > 0) ? str_pad($form->customPeriodStartHourMin[$key], 2, '0', STR_PAD_LEFT) : '00'),
                       'end_hour'       => $form->customPeriodEndHour[$key] . (($form->customPeriodEndHourMin[$key] > 0) ? str_pad($form->customPeriodEndHourMin[$key], 2, '0', STR_PAD_LEFT) : '00'),
                       'repetitiveness' => $form->customPeriodRepetitiveness[$key]
                   ));

                   erLhcoreClassDepartament::getSession()->save($newDepartamentCustomWorkHours);

                   $availableCustomWorkHours[$key]              = $newDepartamentCustomWorkHours;
                   unset($departamentCustomWorkHours[$customPeriodId]);
               } elseif($customPeriodId && !empty($departamentCustomWorkHours) && isset($departamentCustomWorkHours[$customPeriodId])) {
                   // if id isset, unset from provided array
                   $availableCustomWorkHours[$key] = $departamentCustomWorkHours[$customPeriodId];
                   unset($departamentCustomWorkHours[$customPeriodId]);
               }
           }
       }

       // if there are left elements, remove them from DB
       if(!empty($departamentCustomWorkHours)) {
           foreach ($departamentCustomWorkHours as $departamentCustomWorkHour) {
               erLhcoreClassDepartament::getSession()->delete($departamentCustomWorkHour);
           }
       }

       return $availableCustomWorkHours;
   }

   /**
    * Validates department group submit
    * 
    * @param erLhcoreClassModelDepartamentGroup $departamentGroup
    */
   public static function validateDepartmentGroup(erLhcoreClassModelDepartamentGroup $departamentGroup)
   {
       $definition = array(
           'Name' => new ezcInputFormDefinitionElement(
               ezcInputFormDefinitionElement::OPTIONAL, 'unsafe_raw'
           )
       );
       
       $form = new ezcInputForm( INPUT_POST, $definition );
       $Errors = array();
        
       if ( !$form->hasValidData( 'Name' ) || $form->Name == '' ) {
           $Errors[] =  erTranslationClassLhTranslation::getInstance()->getTranslation('departament/editgroup','Please enter a department group name');
       } else {
           $departamentGroup->name = $form->Name;
       }
       
       return $Errors;
   }

   public static function validateDepartmentBrand(\LiveHelperChat\Models\Brand\Brand $brand, & $members = [])
   {
       $definition = array(
           'Name' => new ezcInputFormDefinitionElement(
               ezcInputFormDefinitionElement::OPTIONAL, 'unsafe_raw'
           ),
           'department' => new ezcInputFormDefinitionElement(
               ezcInputFormDefinitionElement::OPTIONAL, 'int', null, FILTER_REQUIRE_ARRAY
           ),
           'role' => new ezcInputFormDefinitionElement(
               ezcInputFormDefinitionElement::OPTIONAL, 'unsafe_raw', null, FILTER_REQUIRE_ARRAY
           )
       );

       $form = new ezcInputForm( INPUT_POST, $definition );
       $Errors = array();

       if ( !$form->hasValidData( 'Name' ) || $form->Name == '' ) {
           $Errors[] =  erTranslationClassLhTranslation::getInstance()->getTranslation('departament/editgroup','Please enter a brand name');
       } else {
           $brand->name = $form->Name;
       }

       if ( $form->hasValidData( 'department' ) && !empty($form->department) ) {
           foreach ($form->department as $departmentId) {
               $members[] = [
                   'dep_id' => $departmentId,
                   'role' => $form->role[$departmentId]
               ];
           }
       }

       return $Errors;
   }


   
   /**
    * Validates department group submit
    * 
    * @param erLhcoreClassModelDepartamentGroup $departamentGroup
    */
   public static function validateDepartmentLimitGroup(erLhcoreClassModelDepartamentLimitGroup $departamentGroup)
   {
       $availableCustomWorkHours = array();
       
       $definition = array(
           'Name' => new ezcInputFormDefinitionElement(
               ezcInputFormDefinitionElement::OPTIONAL, 'unsafe_raw'
           ),
           'PendingMax' => new ezcInputFormDefinitionElement(
               ezcInputFormDefinitionElement::OPTIONAL, 'int'
           )
       );
       
       $form = new ezcInputForm( INPUT_POST, $definition );
       $Errors = array();
        
       if ( !$form->hasValidData( 'Name' ) || $form->Name == '' ) {
           $Errors[] =  erTranslationClassLhTranslation::getInstance()->getTranslation('departament/editgroup','Please enter a department group name');
       } else {
           $departamentGroup->name = $form->Name;
       }
       
       if ( $form->hasValidData( 'PendingMax' )) {
           $departamentGroup->pending_max = $form->PendingMax;
       } else {
           $departamentGroup->pending_max = 0;
       }
       
       return $Errors;
   }
   
   /**
    * Validates department group submit
    * 
    * @param erLhcoreClassModelDepartamentGroup $departamentGroup
    * 
    */
   public static function validateDepartmentGroupDepartments(erLhcoreClassModelDepartamentGroup $departamentGroup)
   {
       $definition = array(
           'departaments' => new ezcInputFormDefinitionElement(
               ezcInputFormDefinitionElement::OPTIONAL, 'unsafe_raw', null, FILTER_REQUIRE_ARRAY
           ));
       
       $form = new ezcInputForm( INPUT_POST, $definition );
       $Errors = array();
       
       if ( $form->hasValidData( 'departaments' ) && !empty($form->departaments)) {
           // Remove old departaments
           self::assignDepartmentsToGroup($departamentGroup, $form->departaments);
       } else {
           // Remove old departaments
           self::assignDepartmentsToGroup($departamentGroup, array());
       }
   }
   
   /**
    * Validates department group submit
    * 
    * @param erLhcoreClassModelDepartamentLimitGroup $departamentGroup
    * 
    */
   public static function validateDepartmentGroupLimitDepartments(erLhcoreClassModelDepartamentLimitGroup $departamentGroup)
   {
       $definition = array(
           'departaments' => new ezcInputFormDefinitionElement(
               ezcInputFormDefinitionElement::OPTIONAL, 'unsafe_raw', null, FILTER_REQUIRE_ARRAY
           ));
       
       $form = new ezcInputForm( INPUT_POST, $definition );
       $Errors = array();
       
       if ( $form->hasValidData( 'departaments' ) && !empty($form->departaments)) {
           // Remove old departaments
           self::assignDepartmentsToLimitGroup($departamentGroup, $form->departaments);
       } else {
           // Remove old departaments
           self::assignDepartmentsToLimitGroup($departamentGroup, array());
       }
   }
   
   public static function assignDepartmentsToLimitGroup(erLhcoreClassModelDepartamentLimitGroup $departamentGroup, $ids)
   {
       $members = erLhcoreClassModelDepartamentLimitGroupMember::getList(array('limit' => false,'filter' => array('dep_limit_group_id' => $departamentGroup->id)));
       
       $newMembers = array();
       $removeMembers = array();       
       $oldMembers = array();
       
       // Remove old members
       foreach ($members as $member) {
           if (!in_array($member->dep_id, $ids)) {
               $member->removeThis();
           } else {
               $oldMembers[] = $member->dep_id;
           }
       }
       
       // Store new members
       foreach ($ids as $id) {
           if (!in_array($id, $oldMembers)) {
               $member = new erLhcoreClassModelDepartamentLimitGroupMember();
               $member->dep_id = $id;
               $member->dep_limit_group_id = $departamentGroup->id;
               $member->saveThis();
           }
       }
   }
   
   
   public static function assignDepartmentsToGroup(erLhcoreClassModelDepartamentGroup $departamentGroup, $ids)
   {
       $members = erLhcoreClassModelDepartamentGroupMember::getList(array('limit' => false,'filter' => array('dep_group_id' => $departamentGroup->id)));
       
       $newMembers = array();
       $removeMembers = array();       
       $oldMembers = array();
       
       // Remove old members
       foreach ($members as $member) {
           if (!in_array($member->dep_id, $ids)) {
               $member->removeThis();
           } else {
               $oldMembers[] = $member->dep_id;
           }
       }
       
       // Store new members
       foreach ($ids as $id) {
           if (!in_array($id, $oldMembers)) {
               $member = new erLhcoreClassModelDepartamentGroupMember();
               $member->dep_id = $id;
               $member->dep_group_id = $departamentGroup->id;
               $member->saveThis();
           }
       }
   }
   
    /**
     * Convert departament custom work hours to template data
     *
     * @param erLhcoreClassModelDepartamentCustomWorkHours[] $departamentCustomWorkHours
     * @return array
     */
   public static function getDepartamentCustomWorkHoursData($departamentCustomWorkHours = array())
   {
       $data = array();

       foreach ($departamentCustomWorkHours as $departamentCustomWorkHour) {
           $data[] = array(
               'dep_id'         => $departamentCustomWorkHour->dep_id,
               'date_from'      => date('Y-m-d', $departamentCustomWorkHour->date_from),
               'date_to'        => date('Y-m-d', $departamentCustomWorkHour->date_to),
               'start_hour'     => $departamentCustomWorkHour->start_hour_front,
               'start_hour_min' => $departamentCustomWorkHour->start_minutes_front,
               'end_hour'       => $departamentCustomWorkHour->end_hour_front,
               'end_hour_min'   => $departamentCustomWorkHour->end_minutes_front,
               'repetitiveness' => $departamentCustomWorkHour->repetitiveness,
               'day_of_week'    => $departamentCustomWorkHour->date_from
           );
       }

       usort($data, function ($a, $b) {
            if ($a['day_of_week'] > $b['day_of_week'] || ($a['day_of_week'] == $b['day_of_week'] && $a['start_hour'] > $b['start_hour'])) {
                return 1;
            } else {
                return -1;
            }
       });

       return $data;
   }

   public static function getWeekDays()
   {        
       return array(
           'mod' => erTranslationClassLhTranslation::getInstance()->getTranslation('department/edit','Monday'),
           'tud' => erTranslationClassLhTranslation::getInstance()->getTranslation('department/edit','Tuesday'),
           'wed' => erTranslationClassLhTranslation::getInstance()->getTranslation('department/edit','Wednesday'),
           'thd' => erTranslationClassLhTranslation::getInstance()->getTranslation('department/edit','Thursday'),
           'frd' => erTranslationClassLhTranslation::getInstance()->getTranslation('department/edit','Friday'),
           'sad' => erTranslationClassLhTranslation::getInstance()->getTranslation('department/edit','Saturday'),
           'sud' => erTranslationClassLhTranslation::getInstance()->getTranslation('department/edit','Sunday')
       );
   }
         
   public static function getSession()
   {
        if ( !isset( self::$persistentSession ) )
        {
            self::$persistentSession = new ezcPersistentSession(
                ezcDbInstance::get(),
                new ezcPersistentCodeManager( './pos/lhdepartament' )
            );
        }
        return self::$persistentSession;
   }

   private static $persistentSession;

}


?>
