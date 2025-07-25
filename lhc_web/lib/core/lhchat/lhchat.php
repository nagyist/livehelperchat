<?php

/**
 * Status -
 * 0 - Pending
 * 1 - Active
 * 2 - Closed
 * 3 - Blocked
 * */

class erLhcoreClassChat {

	public static $chatListIgnoreField = array(
			'remarks',			
			'unread_messages_informed',			
			'reinform_timeout',			
			'user_typing_txt',
			'hash',
			'ip',
			'cls_us',
			//'user_status',
			'email',
			'support_informed',
			'phone',
			'user_typing',
			'operator_typing',
			//'has_unread_messages',
			'operation',			
			'operation_admin',			
			'screenshot_id',			
			'last_msg_id',
			'mail_send',
			'lat',
			'lon',
			'city',
			//'additional_data',
			'session_referrer',
			'wait_time',
			'chat_duration',
			'priority',
			//'online_user_id',
			'transfer_if_na',
			'transfer_timeout_ts',
			'transfer_timeout_ac',

			'na_cb_executed',
			'nc_cb_executed',
			'fbst',
			'operator_typing_id',
			'chat_initiator',
			//'chat_variables',
			// Angular remake
			'referrer',
			//'last_op_msg_time',
			'has_unread_op_messages',
			'unread_op_messages_informed',
			'tslasign',
			'user_closed_ts',
			'usaccept',
			'auto_responder_id',
			'chat_locale',
			'anonymized',
			'uagent',
			'user_tz_identifier',
			'invitation_id',
			'theme_id',
	);

	public static $limitMessages = 50;

    /**
     * Gets pending chats
     */
    public static function getPendingChats($limit = 50, $offset = 0, $filterAdditional = array(), $filterAdditionalMainAttr = array(), $limitationDepartment = array())
    {
    	$limitation = self::getDepartmentLimitation('lh_chat', $limitationDepartment);

    	// Does not have any assigned department
    	if ($limitation === false) { return array(); }

    	$filter = array();
    	$filter['filter'] = array('status' => 0);
        $filter['use_index'] = 'status';

    	if ($limitation !== true) {
    		$filter['customfilter'][] = $limitation;
    	}

    	$filter['limit'] = $limit;
    	$filter['offset'] = $offset;
    	$filter['smart_select'] = true;
    	$filter['sort'] = isset($filterAdditionalMainAttr['sort']) ? $filterAdditionalMainAttr['sort'] : 'priority DESC, id DESC';

    	if (!empty($filterAdditional)) {
    		$filter = array_merge_recursive($filter,$filterAdditional);
    	}

    	return self::getList($filter);
    }

    public static function getMyMails($limit = 50, $offset = 0, $filterAdditional = array(), $filterAdditionalMainAttr = array(), $limitationDepartment = array())
    {
        $limitation = self::getDepartmentLimitation('lhc_mailconv_conversation',$limitationDepartment);

        // Does not have any assigned department
        if ($limitation === false) { return array(); }

        $filter = array();
        $filter['filterin'] = array('status' => array(0,1));

        if ($limitation !== true) {
            $filter['customfilter'][] = $limitation;
        }

        $filter['limit'] = $limit;
        $filter['offset'] = $offset;
        $filter['smart_select'] = true;
        $filter['sort'] = 'status DESC, priority DESC';

        if (!empty($filterAdditional)) {
            $filter = array_merge_recursive($filter,$filterAdditional);
        }

        return erLhcoreClassModelMailconvConversation::getList($filter);
    }

    public static function getPendingMails($limit = 50, $offset = 0, $filterAdditional = array(), $filterAdditionalMainAttr = array(), $limitationDepartment = array())
    {
    	$limitation = self::getDepartmentLimitation('lhc_mailconv_conversation',$limitationDepartment);

    	// Does not have any assigned department
    	if ($limitation === false) { return array(); }

    	$filter = array();
    	$filter['filter'] = array('status' => 0);
        //$filter['use_index'] = 'status_priority';

    	if ($limitation !== true) {
    		$filter['customfilter'][] = $limitation;
    	}

        $filter['customfilter'][] = '(id >= (SELECT MAX(id) FROM ( (SELECT id FROM `lhc_mailconv_conversation` WHERE status = 0 ORDER BY `id` DESC LIMIT 1000,1) UNION SELECT 0 ) AS max_id))';

    	$filter['limit'] = $limit;
    	$filter['offset'] = $offset;
    	$filter['smart_select'] = true;
    	$filter['sort'] = isset($filterAdditionalMainAttr['sort']) ? $filterAdditionalMainAttr['sort'] : 'priority DESC, id DESC';

    	if (!empty($filterAdditional)) {
    		$filter = array_merge_recursive($filter,$filterAdditional);
    	}

    	return erLhcoreClassModelMailconvConversation::getList($filter);
    }

    public static function getActiveMails($limit = 50, $offset = 0, $filterAdditional = array(), $filterAdditionalMainAttr = array(), $limitationDepartment = array())
    {
    	$limitation = self::getDepartmentLimitation('lhc_mailconv_conversation',$limitationDepartment);

    	// Does not have any assigned department
    	if ($limitation === false) { return array(); }

    	$filter = array();
    	$filter['filter'] = array('status' => 1);
        //$filter['use_index'] = 'status_priority';

    	if ($limitation !== true) {
    		$filter['customfilter'][] = $limitation;
    	}

    	$filter['limit'] = $limit;
    	$filter['offset'] = $offset;
    	$filter['smart_select'] = true;
    	$filter['sort'] = isset($filterAdditionalMainAttr['sort']) ? $filterAdditionalMainAttr['sort'] : 'priority DESC, id DESC';

    	if (!empty($filterAdditional)) {
    		$filter = array_merge_recursive($filter,$filterAdditional);
    	}

    	return erLhcoreClassModelMailconvConversation::getList($filter);
    }

    public static function getAlarmMails($limit = 50, $offset = 0, $filterAdditional = array(), $filterAdditionalMainAttr = array(), $limitationDepartment = array())
    {
        $limitation = self::getDepartmentLimitation('lhc_mailconv_conversation',$limitationDepartment);

        // Does not have any assigned department
        if ($limitation === false) { return array(); }

        $pendingAlert = (int)erLhcoreClassModelUserSetting::getSetting('malarm_p', -1);
        $pendingAlertResponse = (int)erLhcoreClassModelUserSetting::getSetting('malarm_pr', -1);
        $mailPastHour = (int)erLhcoreClassModelUserSetting::getSetting('malarm_h', -1);

        $filterOptions = [];
        if ($pendingAlert > 0) {
            $filterOptions[] = "(status = 0 AND user_id = 0 AND (UNIX_TIMESTAMP() - pnd_time) > {$pendingAlert})";
        }

        if ($pendingAlertResponse > 0) {
            $filterOptions[] = "((status = 1 OR (status = 0 AND user_id > 0)) AND lr_time = 0 && (UNIX_TIMESTAMP() - accept_time) > {$pendingAlertResponse})";
        }

        $filter = array();

        if ($mailPastHour > 0) {
            $filter['filtergt']['udate'] = time() - $mailPastHour;
        }

        if (!empty($filterOptions)){
            $filter['customfilter'] = array(' ( '.implode(' OR ',$filterOptions). ' ) ');
        }

        $filterString = '[]';
        $subjectIds = [];
        erLhcoreClassChatEventDispatcher::getInstance()->dispatch('subject.default_filter_mail', array('filter' => & $filterString, 'subject_id' => & $subjectIds));

        if (empty($subjectIds)) {
            $subjectIds = json_decode(erLhcoreClassModelUserSetting::getSetting('subject_mail_id', $filterString), true);
        }

        $filterSubject = '';
        if (!empty($subjectIds)) {
            erLhcoreClassChat::validateFilterIn($subjectIds);
            $filterSubject = ' WHERE `subject_id` IN (' . implode(',',$subjectIds) . ')';
        }

        if (!empty($filterSubject)){
            $filter['customfilter'][] = "(`lhc_mailconv_conversation`.`id` IN (SELECT `id` FROM (SELECT `conversation_id` AS `id` FROM `lhc_mailconv_msg_subject` {$filterSubject} ORDER BY `id` DESC LIMIT 150 ) AS `sq`))";
        }

        if (empty($filter)) {
            return [];
        }

        $filter['filter'] = array('status' => array(0,1));

        if ($limitation !== true) {
            $filter['customfilter'][] = $limitation;
        }

        $filter['limit'] = $limit;
        $filter['offset'] = $offset;
        $filter['smart_select'] = true;
        $filter['sort'] = 'udate DESC';

        if (!empty($filterAdditional)) {
            $filter = array_merge_recursive($filter,$filterAdditional);
        }

        return erLhcoreClassModelMailconvConversation::getList($filter);
    }

    /**
     * @desc returns chats list for my active chats
     * 
     * @param number $limit
     * @param number $offset
     * @param unknown $filterAdditional
     * @param unknown $filterAdditionalMainAttr
     * @param unknown $limitationDepartment
     * @return multitype:|array(object($class))
     */
    public static function getMyChats($limit = 50, $offset = 0, $filterAdditional = array(), $filterAdditionalMainAttr = array(), $limitationDepartment = array())
    {
        $limitation = self::getDepartmentLimitation('lh_chat',$limitationDepartment);
        
        // Does not have any assigned department
        if ($limitation === false) { return array(); }
        
        $filter = array();
        $filter['filterin'] = array('status' => array(0,1));

        if ($limitation !== true) {
            $filter['customfilter'][] = $limitation;
        }
        
        $filter['limit'] = $limit;
        $filter['offset'] = $offset;
        $filter['smart_select'] = true;
        $filter['sort'] = 'status ASC, id DESC';
        
        if (!empty($filterAdditional)) {
            $filter = array_merge_recursive($filter,$filterAdditional);
        }
                
        return self::getList($filter);
    }

    public static function getSubjectChats($limit, $offset = 0, $filterAdditional = array()) {

        $filterString = '[]';
        $subjectIds = [];
        erLhcoreClassChatEventDispatcher::getInstance()->dispatch('subject.default_filter', array('filter' => & $filterString, 'subject_id' => & $subjectIds));

        if (empty($subjectIds)) {
            $subjectIds = json_decode(erLhcoreClassModelUserSetting::getSetting('subject_id', $filterString), true);
        }

        $limitation = self::getDepartmentLimitation('lh_chat', ['check_list_permissions' => true]);

        // Does not have any assigned department
        if ($limitation === false) {
            return array();
        }

        $filter = array();
        $filter['filter'] = array('status' => array(0,1,5));
        $filter['use_index'] = 'status';

        $filterSubject = '';
        if (!empty($subjectIds)) {
            erLhcoreClassChat::validateFilterIn($subjectIds);
            $filterSubject = ' WHERE `subject_id` IN (' . implode(',',$subjectIds) . ')';
        }

        // Optimization - we get these stats only from last 200 chats
        $filter['customfilter'][] = "(`lh_chat`.`id` IN (SELECT `id` FROM (SELECT `chat_id` AS `id` FROM `lh_abstract_subject_chat` {$filterSubject} ORDER BY `id` DESC LIMIT 150) AS `sq`))";

        //echo "SELECT `id` FROM (SELECT `chat_id` AS `id` FROM `lh_abstract_subject_chat` {$filterSubject} ORDER BY `id` DESC LIMIT 150";

        if ($limitation !== true) {
            $filter['customfilter'][] = $limitation;
        }

        $filter['limit'] = $limit;
        $filter['offset'] = $offset;
        $filter['smart_select'] = true;

        if (!empty($filterAdditional)) {
            $filter = array_merge_recursive($filter,$filterAdditional);
        }

        $items = erLhcoreClassModelChat::getList($filter);

        return $items;
    }


    public static function getPendingChatsCount($filterAdditional = array())
    {
    	$limitation = self::getDepartmentLimitation();

    	// Does not have any assigned department
    	if ($limitation === false) { return 0; }

    	$filter = array();
    	$filter['filter'] = array('status' => 0);
        $filter['use_index'] = 'status';

    	if ($limitation !== true) {
    		$filter['customfilter'][] = $limitation;
    	}

    	if (!empty($filterAdditional)) {
    		$filter = array_merge_recursive($filter,$filterAdditional);
    	}

    	return self::getCount($filter);
    }

    public static function getPendingChatsCountPublic($department = false)
    {
    	$filter = array();
    	$filter['filter'] = array('status' => 0);
        $filter['use_index'] = 'status';

    	if ($department !== false && is_numeric($department)) {
    		$filter['filter']['dep_id'] = $department;
    	} elseif ($department !== false && is_array($department)) {
    		$filter['filterin']['dep_id'] = $department;
    	}

    	return self::getCount($filter);
    }

    public static function getList($paramsSearch = array(), $class = 'erLhcoreClassModelChat', $tableName = 'lh_chat')
    {
	       $paramsDefault = array('limit' => 32, 'offset' => 0);

	       $params = array_merge($paramsDefault,$paramsSearch);

	       $session = erLhcoreClassChat::getSession();
	       $q = $session->createFindQuery( $class, isset($params['ignore_fields']) ? $params['ignore_fields'] : array() );

	       $conditions = array();

	       if (!isset($paramsSearch['smart_select'])) {

                  if (isset($params['use_index'])) {
                       $q->useIndex( $params['use_index'] );
                  }

			      if (isset($params['filter']) && count($params['filter']) > 0)
			      {
			           foreach ($params['filter'] as $field => $fieldValue)
			           {
			               $conditions[] = $q->expr->eq( $field, $q->bindValue($fieldValue) );
			           }
			      }

			      if (isset($params['filterin']) && count($params['filterin']) > 0)
			      {
			           foreach ($params['filterin'] as $field => $fieldValue)
			           {
			               $conditions[] = $q->expr->in( $field, $fieldValue );
			           }
			      }

			      if (isset($params['filterlike']) && count($params['filterlike']) > 0)
			      {
			      	   foreach ($params['filterlike'] as $field => $fieldValue)
			      	   {
			      	   		$conditions[] = $q->expr->like( $field, $q->bindValue('%'.$fieldValue.'%') );
			      	   }
			      }

			      if (isset($params['filterlt']) && count($params['filterlt']) > 0)
			      {
			           foreach ($params['filterlt'] as $field => $fieldValue)
			           {
			               $conditions[] = $q->expr->lt( $field, $q->bindValue($fieldValue) );
			           }
			      }

			      if (isset($params['filtergt']) && count($params['filtergt']) > 0)
			      {
			           foreach ($params['filtergt'] as $field => $fieldValue)
			           {
			               $conditions[] = $q->expr->gt( $field,$q->bindValue( $fieldValue ));
			           }
			      }

			      if (isset($params['filterlte']) && count($params['filterlte']) > 0)
			      {
				       foreach ($params['filterlte'] as $field => $fieldValue)
				       {
				      		$conditions[] = $q->expr->lte( $field, $q->bindValue($fieldValue) );
				       }
			      }

			      if (isset($params['filtergte']) && count($params['filtergte']) > 0)
			      {
				      	foreach ($params['filtergte'] as $field => $fieldValue)
				      	{
				      		$conditions[] = $q->expr->gte( $field,$q->bindValue( $fieldValue ));
				      	}
				  }

			      if (isset($params['customfilter']) && count($params['customfilter']) > 0)
			      {
				      	foreach ($params['customfilter'] as $fieldValue)
				      	{
				      		$conditions[] = $fieldValue;
				      	}
			      }

                  if (isset($params['innerjoin']) && count($params['innerjoin']) > 0) {
                       foreach ($params['innerjoin'] as $table => $joinOn) {
                          $q->innerJoin($table, $q->expr->eq($joinOn[0], $joinOn[1]));
                       }
                  }

			      if (count($conditions) > 0)
			      {
			          $q->where(
			                     $conditions
			          );
			      }
               $q->limit($params['limit'],$params['offset']);

			      $q->orderBy(isset($params['sort']) ? $params['sort'] : 'id DESC' );
	      } else {

		      	$q2 = $q->subSelect();
		      	$q2->select( $tableName . '.id' )->from( $tableName );

                if (isset($params['use_index'])) {
                   $q2->useIndex( $params['use_index'] );
                }

		      	if (isset($params['filter']) && count($params['filter']) > 0)
		      	{
		      		foreach ($params['filter'] as $field => $fieldValue)
		      		{
		      			$conditions[] = $q2->expr->eq( $field, $q->bindValue($fieldValue) );
		      		}
		      	}

		      	if (isset($params['filterlike']) && count($params['filterlike']) > 0)
		      	{
		      		foreach ($params['filterlike'] as $field => $fieldValue)
		      		{
		      			$conditions[] = $q->expr->like( $field, $q->bindValue('%'.$fieldValue.'%') );
		      		}
		      	}

		      	if (isset($params['filterin']) && count($params['filterin']) > 0)
		      	{
		      		foreach ($params['filterin'] as $field => $fieldValue)
		      		{
		      			$conditions[] = $q2->expr->in( $field, $fieldValue );
		      		}
		      	}

		      	if (isset($params['filterlt']) && count($params['filterlt']) > 0)
		      	{
		      		foreach ($params['filterlt'] as $field => $fieldValue)
		      		{
		      			$conditions[] = $q2->expr->lt( $field, $q->bindValue($fieldValue) );
		      		}
		      	}

		      	if (isset($params['filterlte']) && count($params['filterlte']) > 0)
		      	{
		      		foreach ($params['filterlte'] as $field => $fieldValue)
		      		{
		      			$conditions[] = $q2->expr->lte( $field, $q->bindValue($fieldValue) );
		      		}
		      	}

		      	if (isset($params['filtergt']) && count($params['filtergt']) > 0)
		      	{
		      		foreach ($params['filtergt'] as $field => $fieldValue)
		      		{
		      			$conditions[] = $q2->expr->gt( $field,$q->bindValue( $fieldValue) );
		      		}
		      	}

		      	if (isset($params['filtergte']) && count($params['filtergte']) > 0)
		      	{
		      		foreach ($params['filtergte'] as $field => $fieldValue)
		      		{
		      			$conditions[] = $q2->expr->gte( $field,$q->bindValue( $fieldValue) );
		      		}
		      	}

		      	if (isset($params['customfilter']) && count($params['customfilter']) > 0)
		      	{
		      		foreach ($params['customfilter'] as $fieldValue)
		      		{
		      			$conditions[] = $fieldValue;
		      		}
		      	}

                if (isset($params['innerjoin']) && count($params['innerjoin']) > 0) {
                   foreach ($params['innerjoin'] as $table => $joinOn) {
                       $q2->innerJoin($table, $q->expr->eq($joinOn[0], $joinOn[1]));
                   }
                }

		      	if (count($conditions) > 0)
		      	{
		      		$q2->where(
		      				$conditions
		      		);
		      	}

		      	$q2->limit($params['limit'],$params['offset']);
		      	$q2->orderBy(isset($params['sort']) ? $params['sort'] : 'id DESC');

		      	$q->innerJoin( $q->alias( $q2, 'items' ), $tableName . '.id', 'items.id' );
		      	$q->orderBy(isset($params['sort']) ? $params['sort'] : 'id DESC' );
	      }

	      $objects = $session->find( $q );

	      return $objects;
    }

    public static function getCount($params = array(), $table = 'lh_chat', $operation = 'COUNT(id)')
    {
        if ($table == 'lh_chat' && $operation == 'COUNT(id)') {
            $operation = 'count(`lh_chat`.`id`)';
        }

    	$session = erLhcoreClassChat::getSession();
    	$q = $session->database->createSelectQuery();
    	$q->select( $operation )->from( $table );
    	$conditions = array();

    	if (isset($params['filter']) && count($params['filter']) > 0)
    	{
    		foreach ($params['filter'] as $field => $fieldValue)
    		{
                if (is_array($fieldValue)) {
                    if (!empty($fieldValue)) {
                        $conditions[] = $q->expr->in($field, $fieldValue);
                    }
                } else {
    			    $conditions[] = $q->expr->eq( $field, $q->bindValue($fieldValue) );
                }
    		}
    	}

    	if (isset($params['filterin']) && count($params['filterin']) > 0)
    	{
    		foreach ($params['filterin'] as $field => $fieldValue)
    		{
    			$conditions[] = $q->expr->in( $field, $fieldValue );
    		}
    	}

    	if (isset($params['filterlt']) && count($params['filterlt']) > 0)
    	{
    		foreach ($params['filterlt'] as $field => $fieldValue)
    		{
    			$conditions[] = $q->expr->lt( $field, $q->bindValue($fieldValue) );
    		}
    	}

    	if (isset($params['filtergt']) && count($params['filtergt']) > 0)
    	{
    		foreach ($params['filtergt'] as $field => $fieldValue)
    		{
    			$conditions[] = $q->expr->gt( $field,$q->bindValue( $fieldValue ));
    		}
    	}

    	if (isset($params['filterlte']) && count($params['filterlte']) > 0)
    	{
    		foreach ($params['filterlte'] as $field => $fieldValue)
    		{
    			$conditions[] = $q->expr->lte( $field, $q->bindValue($fieldValue) );
    		}
    	}

    	if (isset($params['filtergte']) && count($params['filtergte']) > 0)
    	{
    		foreach ($params['filtergte'] as $field => $fieldValue)
    		{
    			$conditions[] = $q->expr->gte( $field,$q->bindValue( $fieldValue ));
    		}
    	}

    	if (isset($params['filterlike']) && count($params['filterlike']) > 0)
    	{
    		foreach ($params['filterlike'] as $field => $fieldValue)
    		{
    			$conditions[] = $q->expr->like( $field, $q->bindValue('%'.$fieldValue.'%') );
    		}
    	}

        if (isset($params['filternot']) && count($params['filternot']) > 0)
        {
            foreach ($params['filternot'] as $field => $fieldValue) {
                if (is_array($fieldValue)) {
                    if (!empty($fieldValue)) {
                        $conditions[] = $q->expr->not($q->expr->in($field, $fieldValue));
                    }
                } else {
                    $conditions[] = $q->expr->neq($field, $q->bindValue($fieldValue));
                }
            }
        }

    	if (isset($params['customfilter']) && count($params['customfilter']) > 0)
    	{
    		foreach ($params['customfilter'] as $fieldValue)
    		{
    			$conditions[] = $fieldValue;
    		}
    	}

    	if (isset($params['leftjoin']) && count($params['leftjoin']) > 0) {
    	    foreach ($params['leftjoin'] as $table => $joinOn) {
    	        $q->leftJoin($table, $q->expr->eq($joinOn[0], $joinOn[1]));
    	    }
    	}
    	
    	if (isset($params['innerjoin']) && count($params['innerjoin']) > 0) {
    	    foreach ($params['innerjoin'] as $table => $joinOn) {
    	        $q->innerJoin($table, $q->expr->eq($joinOn[0], $joinOn[1]));
    	    }
    	}
    	
    	if ( count($conditions) > 0 )
    	{
	    	$q->where( $conditions );
    	}

    	
    	if (isset($params['use_index'])) {
    		$q->useIndex( $params['use_index'] );
    	}

    	$stmt = $q->prepare();
    	$stmt->execute();
    	$result = $stmt->fetchColumn();

    	return $result;
    }

    public static function getDepartmentLimitation($tableName = 'lh_chat', $params = array()) {

    	if (!isset($params['user'])) {
        	$currentUser = erLhcoreClassUser::instance();
        	$userData = $currentUser->getUserData(true);
        	$userId = $currentUser->getUserID();
    	} else {
    	    $userData = $params['user'];
    	    $userId = $userData->id;
    	}

        $limitationPermission = true;

        if (isset($params['check_list_permissions'])) {
            
            $scope = 'chats';
            $module = 'lhchat';

            if (isset($params['check_list_scope']) && $params['check_list_scope'] == 'mails'){
                $scope = 'mails';
                $module = 'lhmailconv';
            }

            if ((isset($params['rest_api']) && !erLhcoreClassRestAPIHandler::hasAccessTo($module,'list_all_'.$scope)) || (!isset($params['rest_api']) && !erLhcoreClassUser::instance()->hasAccessTo($module,'list_all_'.$scope))) {

                $limitationPermission = false;

                if ((isset($params['rest_api']) && !erLhcoreClassRestAPIHandler::hasAccessTo($module,'list_my_'.$scope)) || (!isset($params['rest_api']) && erLhcoreClassUser::instance()->hasAccessTo($module,'list_my_'.$scope))) {

                    $limitationPermission = '(`user_id` = ' . (isset($params['rest_api']) ? erLhcoreClassRestAPIHandler::getUserId() : (int)erLhcoreClassUser::instance()->getUserID()) . ')';

                    if ((isset($params['rest_api']) && !erLhcoreClassRestAPIHandler::hasAccessTo($module,'list_pending_'.$scope)) || (!isset($params['rest_api']) && erLhcoreClassUser::instance()->hasAccessTo($module,'list_pending_'.$scope))) {
                        $limitationPermission = '(`user_id` = ' . (isset($params['rest_api']) ? erLhcoreClassRestAPIHandler::getUserId() : (int)erLhcoreClassUser::instance()->getUserID()) . ' OR (`user_id` = 0 AND `status` = 0))';
                    }
                }
            }
        }

    	if ( $userData->all_departments == 0 )
    	{
    		$userDepartaments = erLhcoreClassUserDep::getUserDepartaments($userId, $userData->cache_version);

            if (isset($params['explicit']) && $params['explicit'] === true && in_array(-1,$userDepartaments)) {
                unset($userDepartaments[array_search(-1, $userDepartaments)]);
            }

    		if (count($userDepartaments) == 0) return false;

    		$LimitationDepartament = '('.$tableName.'.dep_id IN ('.implode(',',$userDepartaments).'))';

            if ($limitationPermission === false) {
                return false;
            } elseif ($limitationPermission !== true) {
                $LimitationDepartament = '(' . $LimitationDepartament . ' AND ' . $limitationPermission . ')';
            }

    		return $LimitationDepartament;

    	} elseif ($limitationPermission !== true) {
            return $limitationPermission;
        }

    	return true;
    }

    // Get's unread messages from users
    public static function getUnreadMessagesChats($limit = 10, $offset = 0, $filterAdditional = array()) {

    	$limitation = self::getDepartmentLimitation('lh_chat', ['check_list_permissions' => true]);

    	// Does not have any assigned department
    	if ($limitation === false) {
    		return array();
    	}

    	$filter = array();

    	$filter['filter'] = array('has_unread_messages' => 1);

    	if ($limitation !== true) {
    		$filter['customfilter'][] = $limitation;
    	}
    	
    	// Give 5 seconds to operator to sync a chat and avoid annoying him
    	$filter['filterlt']['last_user_msg_time'] = time()-5;
    	
    	$filter['limit'] = $limit;
    	$filter['offset'] = $offset;
    	$filter['smart_select'] = true;

    	if (!empty($filterAdditional)) {
    		$filter = array_merge_recursive($filter,$filterAdditional);
    	}

    	// Optimization - we get these stats only from last 50 chats
        $filter['customfilter'][] = '`lh_chat`.`id` IN (SELECT `id` FROM (SELECT `id` FROM `lh_chat` ORDER BY `id` DESC LIMIT 50) AS `sq`)';

    	return self::getList($filter);
    }

    // Get's unread messages from users | COUNT
    public static function getUnreadMessagesChatsCount($filterAdditional = array()) {

    	$limitation = self::getDepartmentLimitation();

    	// Does not have any assigned department
    	if ($limitation === false) {
    		return 0;
    	}

    	$filter = array();

    	$filter['filter'] = array('has_unread_messages' => 1);

    	if ($limitation !== true) {
    		$filter['customfilter'][] = $limitation;
    	}

    	if (!empty($filterAdditional)) {
    		$filter = array_merge_recursive($filter,$filterAdditional);
    	}

    	return self::getCount($filter);
    }

    public static function getActiveChats($limit = 50, $offset = 0, $filterAdditional = array())
    {
    	$limitation = self::getDepartmentLimitation('lh_chat', ['check_list_permissions' => true]);

    	// Does not have any assigned department
    	if ($limitation === false) { return array(); }

    	$filter = array();
    	$filter['filter'] = array('status' => 1);
        $filter['use_index'] = 'status';

    	if ($limitation !== true) {
    		$filter['customfilter'][] = $limitation;
    	}

    	$filter['limit'] = $limit;
    	$filter['offset'] = $offset;
    	$filter['smart_select'] = true;

    	if (!empty($filterAdditional)) {
    		$filter = array_merge_recursive($filter,$filterAdditional);
    	}

    	return self::getList($filter);
    }

    public static function getActiveChatsCount($filterAdditional = array())
    {
    	$limitation = self::getDepartmentLimitation('lh_chat', ['check_list_permissions' => true]);

    	// Does not have any assigned department
    	if ($limitation === false) { return 0; }

    	$filter = array();
    	$filter['filter'] = array('status' => 1);
        $filter['use_index'] = 'status';

    	if ($limitation !== true) {
    		$filter['customfilter'][] = $limitation;
    	}

    	if (!empty($filterAdditional)) {
    		$filter = array_merge_recursive($filter,$filterAdditional);
    	}

    	return self::getCount($filter);
    }

    public static function getClosedChats($limit = 50, $offset = 0, $filterAdditional = array())
    {
    	$limitation = self::getDepartmentLimitation('lh_chat', ['check_list_permissions' => true]);

    	// Does not have any assigned department
    	if ($limitation === false) { return array(); }

    	$filter = array();
    	$filter['filter'] = array('status' => 2);
        $filter['use_index'] = 'status';

    	if ($limitation !== true) {
    		$filter['customfilter'][] = $limitation;
    	}

    	$filter['limit'] = $limit;
    	$filter['offset'] = $offset;
    	$filter['smart_select'] = true;

    	if (!empty($filterAdditional)) {
    		$filter = array_merge_recursive($filter,$filterAdditional);
    	}

    	// Optimization - we get these stats only from last 50 chats
        $filter['customfilter'][] = '`lh_chat`.`id` IN (SELECT `id` FROM (SELECT `id` FROM `lh_chat` ORDER BY `id` DESC LIMIT ' . (int)$limit . ') AS `sq`)';

    	return self::getList($filter);
    }

    public static function getBotChats($limit = 50, $offset = 0, $filterAdditional = array())
    {
    	$limitation = self::getDepartmentLimitation();

    	// Does not have any assigned department
    	if ($limitation === false) { return array(); }

    	$filter = array();
    	$filter['filter'] = array('status' => 5);
        $filter['use_index'] = 'status';

    	if ($limitation !== true) {
    		$filter['customfilter'][] = $limitation;
    	}

    	$filter['limit'] = $limit;
    	$filter['offset'] = $offset;
    	$filter['smart_select'] = true;

    	if (!empty($filterAdditional)) {
    		$filter = array_merge_recursive($filter,$filterAdditional);
    	}

    	return self::getList($filter);
    }

    public static function getClosedChatsCount($filterAdditional = array())
    {
    	$limitation = self::getDepartmentLimitation();

    	// Does not have any assigned department
    	if ($limitation === false) { return 0; }

    	$filter = array();
    	$filter['filter'] = array('status' => 2);
        $filter['use_index'] = 'status';

    	if ($limitation !== true) {
    		$filter['customfilter'][] = $limitation;
    	}

    	if (!empty($filterAdditional)) {
    		$filter = array_merge_recursive($filter,$filterAdditional);
    	}

    	return self::getCount($filter);
    }

    public static function getOperatorsChats($limit = 50, $offset = 0, $filterAdditional = array())
    {
    	$limitation = self::getDepartmentLimitation();

    	// Does not have any assigned department
    	if ($limitation === false) { return array(); }

    	$filter = array();
    	$filter['filter'] = array('status' => 4);
        $filter['use_index'] = 'status';

    	if ($limitation !== true) {
    		$filter['customfilter'][] = $limitation;
    	}

    	$filter['limit'] = $limit;
    	$filter['offset'] = $offset;
    	$filter['smart_select'] = true;

    	if (!empty($filterAdditional)) {
    		$filter = array_merge_recursive($filter, $filterAdditional);
    	}

    	return self::getList($filter);
    }

    public static function getOperatorsChatsCount($filterAdditional = array())
    {
    	$limitation = self::getDepartmentLimitation();

    	// Does not have any assigned department
    	if ($limitation === false) { return 0; }

    	$filter = array();
    	$filter['filter'] = array('status' => 4);
        $filter['use_index'] = 'status';

    	if ($limitation !== true) {
    		$filter['customfilter'][] = $limitation;
    	}

    	if (!empty($filterAdditional)) {
    		$filter = array_merge_recursive($filter, $filterAdditional);
    	}

    	return self::getCount($filter);
    }

    public static $botOnlyOnline = null;

    public static function isOnline($dep_id = false, $exclipic = false, $params = array())
    {
       $isOnlineUser = isset($params['online_timeout']) ? $params['online_timeout'] : (int)erLhcoreClassModelChatConfig::fetch('sync_sound_settings')->data['online_timeout'];
       $ignoreUserStatus = (isset($params['ignore_user_status']) && $params['ignore_user_status'] == 1) ? true : false;

       // Redis Cache support
       $enableCache = false;

        if (!(isset($params['disable_cache']) && $params['disable_cache'] === true) && class_exists('erLhcoreClassRedis')) {

            $cacheKeyStatus = 'lhc_online_cache_key';

            if (class_exists('erLhcoreClassInstance', false) && is_object(erLhcoreClassInstance::$instanceChat)) {
                $cacheKeyStatus .= erLhcoreClassInstance::$instanceChat->id;
            }

            $cacheKey = 'is_online_' . $cacheKeyStatus . '_' . $exclipic . '_' . (new class {
                    use erLhcoreClassDBTrait;
                })::multi_implode('_', $dep_id) . '_' . md5((new class {
                    use erLhcoreClassDBTrait;
                })::multi_implode('_', $params));

            $contentCache = erLhcoreClassRedis::instance()->get($cacheKey);
            if ($contentCache !== false) {
                $parts = explode('_', $contentCache);
                if (isset($parts[1]) && $parts[1] == 1) {
                    self::$botOnlyOnline = true;
                }
                return (int)$parts[0] > 0;
            }
            $enableCache = true;
        }

       $db = ezcDbInstance::get();
	   $rowsNumber = 0;
       $userFilter = (isset($params['user_id']) && is_numeric($params['user_id'])) ? ' AND `lh_userdep`.`user_id` = '.(int)$params['user_id'] : '';

       if ($dep_id !== false && $dep_id !== '') {
       		$exclipicFilter = ($exclipic == false) ? ' OR dep_id = 0' : '';

       		if ($ignoreUserStatus === false) {

				if (is_numeric($dep_id)) {
		           $stmt = $db->prepare("SELECT COUNT(lh_userdep.id) AS found FROM lh_userdep INNER JOIN lh_departament ON lh_departament.id = :dep_id_dest WHERE `lh_departament`.`disabled` = 0 AND `lh_departament`.`dep_offline` = 0 " . ((!isset($params['include_users']) || $params['include_users'] === false) ? 'AND `lh_departament`.`ignore_op_status` = 0' : '') . " AND (lh_departament.pending_group_max = 0 OR lh_departament.pending_group_max > lh_departament.pending_chats_counter) AND (lh_departament.pending_max = 0 OR lh_departament.pending_max > lh_departament.pending_chats_counter) AND ((last_activity > :last_activity OR `lh_userdep`.`always_on` = 1) AND hide_online = 0 AND ro = 0) AND (dep_id = :dep_id {$exclipicFilter}) {$userFilter}");
		           $stmt->bindValue(':dep_id',$dep_id,PDO::PARAM_INT);
		           $stmt->bindValue(':dep_id_dest',$dep_id,PDO::PARAM_INT);
		           $stmt->bindValue(':last_activity',(time()-$isOnlineUser),PDO::PARAM_INT);
				} elseif ( is_array($dep_id) ) {
				    $dep_id_filter = $dep_id;
				    $sqlDepartment = '`lh_departament`.`id` IN ('. implode(',', $dep_id_filter) .') AND';
					if (empty($dep_id_filter)) {
                        $dep_id_filter = array(-1);
                        $sqlDepartment = '';
					}
					$stmt = $db->prepare('SELECT COUNT(lh_userdep.id) AS found FROM lh_userdep, lh_departament WHERE ' . $sqlDepartment . ' `lh_departament`.`disabled` = 0 ' . ((!isset($params['include_users']) || $params['include_users'] === false) ? 'AND `lh_departament`.`ignore_op_status` = 0' : '') . ' AND `lh_departament`.`dep_offline` = 0 AND (lh_departament.pending_group_max = 0 OR lh_departament.pending_group_max > lh_departament.pending_chats_counter) AND (lh_departament.pending_max = 0 OR lh_departament.pending_max > lh_departament.pending_chats_counter) AND ((last_activity > :last_activity OR `lh_userdep`.`always_on` = 1) AND hide_online = 0 AND ro = 0) AND (dep_id IN ('. implode(',', $dep_id_filter) .") {$exclipicFilter}) {$userFilter}");
					$stmt->bindValue(':last_activity',(time()-$isOnlineUser),PDO::PARAM_INT);
				}
				$stmt->execute();
				$rowsNumber = $stmt->fetchColumn();
       		}

			if ($rowsNumber == 0 && (!isset($params['exclude_online_hours']) || $params['exclude_online_hours'] == false)) { // Perhaps auto active is turned on for some of departments
                if (is_numeric($dep_id)) {
                    $stmt = $db->prepare("SELECT `lh_departament_custom_work_hours`.`start_hour`, `lh_departament_custom_work_hours`.`end_hour` FROM `lh_departament_custom_work_hours` INNER JOIN `lh_departament` ON `lh_departament`.`id` = `lh_departament_custom_work_hours`.`dep_id` WHERE `lh_departament`.`disabled` = 0 AND `lh_departament`.`dep_offline` = 0 AND (`lh_departament`.`pending_group_max` = 0 OR `lh_departament`.`pending_group_max` > `lh_departament`.`pending_chats_counter`) AND (`lh_departament`.`pending_max` = 0 OR `lh_departament`.`pending_max` > `lh_departament`.`pending_chats_counter`) AND `online_hours_active` = 1 AND ((`date_from` <= :date_from AND date_to >= :date_to AND `repetitiveness` = 0) OR (`date_from` = :date_week AND `repetitiveness` = 1)) AND `dep_id` = :dep_id");
                    $stmt->bindValue(':dep_id',$dep_id);
                } elseif (is_array($dep_id)) {
                    $sqlDepartment = '';
                    if (!empty($dep_id)) {
                        $sqlDepartment = "AND `dep_id` IN (". implode(',', $dep_id) .")";
                    }
                    $stmt = $db->prepare("SELECT `lh_departament_custom_work_hours`.`start_hour`, `lh_departament_custom_work_hours`.`end_hour` FROM `lh_departament_custom_work_hours` INNER JOIN `lh_departament` ON `lh_departament`.`id` = `lh_departament_custom_work_hours`.`dep_id` WHERE `lh_departament`.`disabled` = 0 AND `lh_departament`.`dep_offline` = 0 AND (`lh_departament`.`pending_group_max` = 0 OR `lh_departament`.`pending_group_max` > `lh_departament`.`pending_chats_counter`) AND (`lh_departament`.`pending_max` = 0 OR `lh_departament`.`pending_max` > `lh_departament`.`pending_chats_counter`) AND `online_hours_active` = 1 AND ((`date_from` <= :date_from AND `date_to` >= :date_to AND `repetitiveness` = 0) OR (`date_from` = :date_week AND `repetitiveness` = 1)) {$sqlDepartment}");
                }

                $stmt->bindValue(':date_from',strtotime(date('Y-m-d')),PDO::PARAM_INT);
                $stmt->bindValue(':date_to',strtotime(date('Y-m-d')),PDO::PARAM_INT);
                $stmt->bindValue(':date_week',date('N'),PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if(!empty($result)) {
                    foreach ($result as $item) {
                        if ($item['start_hour'] <= (int)(date('G') . date('i')) && $item['end_hour'] >= (int)(date('G') . date('i'))){
                            $rowsNumber++;
                        }
                    }
                } else {
                    $daysColumns = array('mod','tud','wed','thd','frd','sad','sud');
                    $column = date('N') - 1;
                    $startHoursColumnName = $daysColumns[$column].'_start_hour';
                    $endHoursColumnName = $daysColumns[$column].'_end_hour';

                    if (is_numeric($dep_id)) {
                        $stmt = $db->prepare("SELECT COUNT(id) AS found FROM lh_departament WHERE `lh_departament`.`disabled` = 0 AND `lh_departament`.`dep_offline` = 0 AND (lh_departament.pending_group_max = 0 OR lh_departament.pending_group_max > lh_departament.pending_chats_counter) AND (lh_departament.pending_max = 0 OR lh_departament.pending_max > lh_departament.pending_chats_counter) AND online_hours_active = 1 AND {$startHoursColumnName} <= :start_hour AND {$endHoursColumnName} >= :end_hour AND {$startHoursColumnName} != -1 AND {$endHoursColumnName} != -1 AND id = :dep_id");
                        $stmt->bindValue(':dep_id', $dep_id);
                    } elseif (is_array($dep_id)) {
                        $sqlDepartment = '';
                        if (!empty($dep_id)) {
                            $sqlDepartment = "AND id IN (". implode(',', $dep_id) .")";
                        }

                        $stmt = $db->prepare("SELECT COUNT(id) AS found FROM lh_departament WHERE `lh_departament`.`disabled` = 0 AND `lh_departament`.`dep_offline` = 0 AND (lh_departament.pending_group_max = 0 OR lh_departament.pending_group_max > lh_departament.pending_chats_counter) AND (lh_departament.pending_max = 0 OR lh_departament.pending_max > lh_departament.pending_chats_counter) AND online_hours_active = 1 AND {$startHoursColumnName} <= :start_hour AND {$endHoursColumnName} >= :end_hour AND {$startHoursColumnName} != -1 AND {$endHoursColumnName} != -1 {$sqlDepartment}");
                     }
                    
                    $stmt->bindValue(':start_hour', date('G') . date('i'), PDO::PARAM_INT);
                    $stmt->bindValue(':end_hour', date('G') . date('i'), PDO::PARAM_INT);
                    $stmt->execute();
                    $rowsNumber = $stmt->fetchColumn();
                }
			}					

			// Check is bot enabled for department
			if ($rowsNumber == 0 && (!isset($params['exclude_bot']) || $params['exclude_bot'] == false)) {
                if (is_numeric($dep_id)) {
                    $stmt = $db->prepare("SELECT bot_configuration FROM `lh_departament` WHERE id = :dep_id AND `lh_departament`.`dep_offline` = 0 AND `lh_departament`.`disabled` = 0");
                    $stmt->bindValue(':dep_id', $dep_id);
                    $stmt->execute();
                    $resultItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } elseif (is_array($dep_id)) {
                    $sqlDepartment = '';
                    if (!empty($dep_id)) {
                        $sqlDepartment = "WHERE id IN (". implode(',', $dep_id) .") AND `lh_departament`.`dep_offline` = 0";
                    }
                    $stmt = $db->prepare("SELECT bot_configuration FROM lh_departament {$sqlDepartment}");
                    $stmt->execute();
                    $resultItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
                if (is_array($resultItems)) {
                    foreach ($resultItems as $result) {
                        if (isset($result['bot_configuration']) && !empty($result['bot_configuration'])) {
                            $botData = json_decode($result['bot_configuration'], true);
                            if (isset($botData['bot_id']) && $botData['bot_id'] > 0 && (!isset($botData['bot_foh']) || $botData['bot_foh'] == false)) {
                                $rowsNumber = 1;
                                self::$botOnlyOnline = true;
                            }
                        }
                    }
                }
            }

       } else {

            if ($ignoreUserStatus === false) {
                $stmt = $db->prepare('SELECT COUNT(`lh_departament`.`id`) AS found FROM `lh_departament` WHERE `lh_departament`.`ignore_op_status` = 0 AND `lh_departament`.`dep_offline` = 0 AND (`lh_departament`.`pending_group_max` = 0 OR `lh_departament`.`pending_group_max` > `lh_departament`.`pending_chats_counter`) AND (`lh_departament`.`pending_max` = 0 OR `lh_departament`.`pending_max` > `lh_departament`.`pending_chats_counter`) AND `lh_departament`.`hidden` = 0 AND `lh_departament`.`disabled` = 0');
                $stmt->execute();
                $rowsNumberDep = $stmt->fetchColumn();
                if ($rowsNumberDep > 0) {
                    $stmt = $db->prepare('SELECT COUNT(`lh_userdep`.`id`) AS found FROM `lh_userdep` LEFT JOIN `lh_departament` ON `lh_departament`.`id` = `lh_userdep`.`dep_id` WHERE (`lh_departament`.`ignore_op_status` IS NULL OR `lh_departament`.`ignore_op_status` = 0) AND (`lh_departament`.`dep_offline` IS NULL OR `lh_departament`.`dep_offline` = 0) AND (`lh_departament`.`pending_group_max` IS NULL OR lh_departament.pending_group_max = 0 OR lh_departament.pending_group_max > lh_departament.pending_chats_counter) AND (lh_departament.pending_max IS NULL OR lh_departament.pending_max = 0 OR lh_departament.pending_max > lh_departament.pending_chats_counter) AND (lh_departament.hidden IS NULL OR lh_departament.hidden = 0) AND (last_activity > :last_activity OR `lh_userdep`.`always_on` = 1) AND ro = 0 AND hide_online = 0 AND (lh_departament.disabled IS NULL OR lh_departament.disabled = 0) '.$userFilter);
                    $stmt->bindValue(':last_activity',(time()-$isOnlineUser),PDO::PARAM_INT);
                    $stmt->execute();
                    $rowsNumber = $stmt->fetchColumn();
                }
            }

           if ($rowsNumber == 0) { // Perhaps auto active is turned on for some of departments
               $stmt = $db->prepare("SELECT `lh_departament_custom_work_hours`.`start_hour`, `lh_departament_custom_work_hours`.`end_hour` FROM `lh_departament_custom_work_hours` INNER JOIN `lh_departament` ON `lh_departament`.`id` = `lh_departament_custom_work_hours`.`dep_id` WHERE `lh_departament`.`dep_offline` = 0 AND (`lh_departament`.`pending_group_max` = 0 OR `lh_departament`.`pending_group_max` > `lh_departament`.`pending_chats_counter`) AND (`lh_departament`.`pending_max` = 0 OR `lh_departament`.`pending_max` > `lh_departament`.`pending_chats_counter`) AND `online_hours_active` = 1 AND `lh_departament`.`hidden` = 0 AND `lh_departament`.`disabled` = 0 AND ((`date_from` <= :date_from AND `date_to` >= :date_to AND `repetitiveness` = 0) OR (`date_from` = :date_week AND `repetitiveness` = 1))");
               $stmt->bindValue(':date_from',strtotime(date('Y-m-d')),PDO::PARAM_INT);
               $stmt->bindValue(':date_to',strtotime(date('Y-m-d')),PDO::PARAM_INT);
               $stmt->bindValue(':date_week',date('N'),PDO::PARAM_INT);
               $stmt->execute();
               $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

               if(!empty($result)) {
                   foreach ($result as $item) {
                       if ($item['start_hour'] <= (int)(date('G') . date('i')) && $item['end_hour'] >= (int)(date('G') . date('i'))) {
                           $rowsNumber++;
                       }
                   }



               } else {                   
                   $daysColumns = array('mod','tud','wed','thd','frd','sad','sud');
                   $column = date('N') - 1;
                   $startHoursColumnName = $daysColumns[$column].'_start_hour';
                   $endHoursColumnName = $daysColumns[$column].'_end_hour';

                   $stmt = $db->prepare("SELECT COUNT(id) AS found FROM lh_departament WHERE `lh_departament`.`hidden` = 0 AND `lh_departament`.`dep_offline` = 0 AND `lh_departament`.`disabled` = 0 AND (lh_departament.pending_group_max = 0 OR lh_departament.pending_group_max > lh_departament.pending_chats_counter) AND (lh_departament.pending_max = 0 OR lh_departament.pending_max > lh_departament.pending_chats_counter) AND `online_hours_active` = 1 AND {$startHoursColumnName} <= :start_hour AND {$endHoursColumnName} >= :end_hour AND {$startHoursColumnName} != -1 AND {$endHoursColumnName} != -1");
                   $stmt->bindValue(':start_hour', date('G') . date('i'), PDO::PARAM_INT);
                   $stmt->bindValue(':end_hour', date('G') . date('i'), PDO::PARAM_INT);
                   $stmt->execute();
                  
                   $rowsNumber = $stmt->fetchColumn();
               }
           }

           // Check is bot enabled for department
           if ($rowsNumber == 0 && (!isset($params['exclude_bot']) || $params['exclude_bot'] == false)) {
               $stmt = $db->prepare("SELECT bot_configuration FROM `lh_departament` WHERE `lh_departament`.`hidden` = 0 AND `lh_departament`.`dep_offline` = 0 AND `lh_departament`.`disabled` = 0 AND (lh_departament.pending_group_max = 0 OR lh_departament.pending_group_max > lh_departament.pending_chats_counter) AND (lh_departament.pending_max = 0 OR lh_departament.pending_max > lh_departament.pending_chats_counter)");
               $stmt->execute();
               $resultItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
               if (is_array($resultItems)) {
                   foreach ($resultItems as $result) {
                       if (isset($result['bot_configuration']) && !empty($result['bot_configuration'])) {
                           $botData = json_decode($result['bot_configuration'], true);
                           if (isset($botData['bot_id']) && $botData['bot_id'] > 0 && (!isset($botData['bot_foh']) || $botData['bot_foh'] == false)) {
                               $rowsNumber = 1;
                               self::$botOnlyOnline = true;
                           }
                       }
                   }
               }
           }

       }

       if ($enableCache === true) {
           erLhcoreClassRedis::instance()->setex($cacheKey, 60, $rowsNumber . '_' . (self::$botOnlyOnline === true ? 1 : 0));
       }

       return $rowsNumber >= 1;
    }

    public static function isOnlyBotOnline($department) {

        if ( self::$botOnlyOnline == null) {
            self::isOnline($department, false, array('ignore_user_status'=> (int)erLhcoreClassModelChatConfig::fetch('ignore_user_status')->current_value, 'online_timeout' => (int)erLhcoreClassModelChatConfig::fetch('sync_sound_settings')->data['online_timeout']));
        }

        $onlyBotOnline = false;

        if ((is_numeric($department) && $department > 0) || (is_array($department) && count($department) == 1)) {
            $onlyBotOnline = self::$botOnlyOnline;

            // Check does chat is started with bot
            if ($onlyBotOnline == false) {
                $departmentObject = erLhcoreClassModelDepartament::fetch($department);
                if ($departmentObject instanceof erLhcoreClassModelDepartament) {
                    if ((!isset($departmentObject->bot_configuration_array['bot_only_offline']) || $departmentObject->bot_configuration_array['bot_only_offline'] == 0) && isset($departmentObject->bot_configuration_array['bot_id']) && $departmentObject->bot_configuration_array['bot_id'] > 0) {
                        $onlyBotOnline = true;
                    }
                }
            }
        }

        return $onlyBotOnline;
    }

    /**
     * Returns departments with atleast one logged 
     */
    public static function getLoggedDepartmentsIds($departmentsIds, $exclipic = false)
    {
        if (empty($departmentsIds))
        {
            return array();
        }

        $isOnlineUser = (int)erLhcoreClassModelChatConfig::fetch('sync_sound_settings')->data['online_timeout'];

        $db = ezcDbInstance::get();

        if ($exclipic == true)
        {
            $stmt = $db->prepare("SELECT dep_id AS found FROM lh_userdep WHERE ((last_activity > :last_activity OR `lh_userdep`.`always_on` = 1) AND hide_online = 0) AND dep_id IN (" . implode(',', $departmentsIds) . ")");
            $stmt->bindValue(':last_activity',(time()-$isOnlineUser),PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_COLUMN);

        } else {
            
            $stmt = $db->prepare("SELECT count(id) AS found FROM lh_userdep WHERE ((last_activity > :last_activity OR `lh_userdep`.`always_on` = 1) AND hide_online = 0) AND (dep_id = 0 OR dep_id IN (" . implode(',', $departmentsIds) . "))");
            $stmt->bindValue(':last_activity',(time()-$isOnlineUser),PDO::PARAM_INT);
            $stmt->execute();
            
            $rowsNumber = $stmt->fetchColumn();
            
            // Return same departments because one of operators are online and has assigned all departments
            if ($rowsNumber > 0) {
                return $departmentsIds;
            } else {
                return array();
            }
        }
    }

    public static function getRandomOnlineUserID($params = array()) {
    	$isOnlineUser = isset($params['online_timeout']) ? $params['online_timeout'] : (int)erLhcoreClassModelChatConfig::fetch('sync_sound_settings')->data['online_timeout'];
    	
    	$db = ezcDbInstance::get();
		$agoTime = time()-$isOnlineUser;

		$filterOperators = '';
		if ( isset($params['operators']) && !empty($params['operators']) ) {
			$operators = array();
			foreach ($params['operators'] as $operatorID) {
				if ((int)$operatorID > 0){
					$operators[] = (int)$operatorID;
				}
			}
						
			if (!empty($operators)){
				$filterOperators = ' AND lh_users.id IN ('.implode(',',$operators).')';
			}
		}
		
    	$SQL = 'SELECT count(*) FROM (SELECT count(`lh_users`.`id`) FROM `lh_users` INNER JOIN `lh_userdep` ON `lh_userdep`.`user_id` = `lh_users`.`id` WHERE (`lh_userdep`.`last_activity` > :last_activity OR `lh_userdep`.`always_on` = 1) AND `lh_userdep`.`hide_online` = 0 ' . $filterOperators . ' GROUP BY `lh_users`.`id`) as `online_users`';
    	$stmt = $db->prepare($SQL);
    	$stmt->bindValue(':last_activity',$agoTime,PDO::PARAM_INT);
    	$stmt->execute();
    	$count = $stmt->fetchColumn();

    	if ($count > 0){
	    	$offsetRandom = rand(0, $count-1);

	    	$SQL = "SELECT `lh_users`.`id` FROM `lh_users` INNER JOIN `lh_userdep` ON `lh_userdep`.`user_id` = `lh_users`.`id` WHERE (`lh_userdep`.`last_activity` > :last_activity OR `lh_userdep`.`always_on` = 1) AND `lh_userdep`.`hide_online` = 0 {$filterOperators} GROUP BY `lh_users`.`id` LIMIT 1 OFFSET {$offsetRandom}";
	    	$stmt = $db->prepare($SQL);
	    	$stmt->bindValue(':last_activity',$agoTime,PDO::PARAM_INT);
	    	$stmt->execute();

	    	return $stmt->fetchColumn();
    	}

    	return 0;
    }

    public static function getOnlineUsers($UserID = array(), $params = array())
    {     
       $isOnlineUser = isset($params['online_timeout']) ? $params['online_timeout'] : (int)erLhcoreClassModelChatConfig::fetch('sync_sound_settings')->data['online_timeout'];
       $onlyOnline = isset($params['hide_online']) ? ' AND lh_userdep.hide_online = :hide_online' : false;
       $sameDepartment = isset($params['same_dep']) ? ' AND (lh_userdep.dep_id = 0 OR lh_userdep.dep_id = :dep_id)' : false;

        $db = ezcDbInstance::get();
       $NotUser = '';

       if (count($UserID) > 0)
       {
           $NotUser = ' AND lh_users.id NOT IN ('.implode(',',$UserID).')';
       }

       $limitationSQL = '';

       if (!erLhcoreClassUser::instance()->hasAccessTo('lhchat','allowtransfertoanyuser')){
	       // User can see online only their department's users
	       $limitation = self::getDepartmentLimitation('lh_userdep', array('explicit' => true));

	       // Does not have any assigned department
	       if ($limitation === false) { return array(); }

	       if ($limitation !== true) {
	       		$limitationSQL = ' AND '.$limitation;
	       }
       } else {
           $limitation = erLhcoreClassUser::instance()->hasAccessTo('lhchat','allowtransfertoanyuser', true);
           if ($limitation !== true) {
               $limitationParams = json_decode($limitation, true);
               if (isset($limitationParams['group'])) {
                   erLhcoreClassChat::validateFilterIn($limitationParams['group']);
                   $userIDValid = erLhcoreClassModelGroupUser::getCount(['filterin' => ['group_id' => $limitationParams['group']]],false,'user_id', 'user_id', false, true, true);
                   if (!empty($userIDValid)) {
                       $NotUser .= " AND `lh_users`.`id` IN(" . implode(',',$userIDValid) . ')';
                   } else {
                       return [];
                   }
               }
           }
       }

       $SQL = 'SELECT lh_users.* FROM lh_users INNER JOIN lh_userdep ON lh_userdep.user_id = lh_users.id WHERE (`lh_userdep`.`last_activity` > :last_activity OR `lh_userdep`.`always_on` = 1) '.$NotUser.$limitationSQL.$onlyOnline.$sameDepartment.' GROUP BY `lh_users`.`id`';
       $stmt = $db->prepare($SQL);
       $stmt->bindValue(':last_activity',(time()-$isOnlineUser),PDO::PARAM_INT);

       if ($onlyOnline !== false) {
           $stmt->bindValue(':hide_online',0,PDO::PARAM_INT);
       }

       if ($sameDepartment !== false) {
           $stmt->bindValue(':dep_id',$params['same_dep'],PDO::PARAM_INT);
       }

       $stmt->execute();
       $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
       return $rows;
    }

    public static function isOnlineUser($user_id, $params = array()) {    	
    	$isOnlineUser = isset($params['online_timeout']) ? $params['online_timeout'] : (int)erLhcoreClassModelChatConfig::fetch('sync_sound_settings')->data['online_timeout'];
    	    	
    	$db = ezcDbInstance::get();

    	$stmt = $db->prepare('SELECT count(lh_users.id) FROM lh_users INNER JOIN lh_userdep ON lh_userdep.user_id = lh_users.id WHERE (`lh_userdep`.`last_activity` > :last_activity OR `lh_userdep`.`always_on` = 1) AND `lh_users`.`hide_online` = 0 AND `lh_users`.`id` = :user_id');
    	$stmt->bindValue(':last_activity',(time()-$isOnlineUser),PDO::PARAM_INT);
    	$stmt->bindValue(':user_id',$user_id,PDO::PARAM_INT);
    	$stmt->execute();

    	$rows = $stmt->fetchColumn();

    	return $rows > 0;
    }


   /**
    * All messages, which should get administrator/user
    *
    * */
   public static function getPendingMessages($chat_id,$message_id, $excludeSystem = false)
   {

       $excludeFilter = '';

       if ($excludeSystem == true) {
           $excludeFilter = ' AND user_id != -1'; // It's a system message
       }

       $db = ezcDbInstance::get();
       $stmt = $db->prepare('SELECT lh_msg.* FROM lh_msg INNER JOIN (SELECT id FROM lh_msg WHERE chat_id = :chat_id AND id > :message_id ' . $excludeFilter . ' ORDER BY id ASC) AS items ON lh_msg.id = items.id');
       $stmt->bindValue( ':chat_id',$chat_id,PDO::PARAM_INT);
       $stmt->bindValue( ':message_id',$message_id,PDO::PARAM_INT);
       $stmt->setFetchMode(PDO::FETCH_ASSOC);
       $stmt->execute();
       $rows = $stmt->fetchAll();

       return $rows;
   }


   /**
    * All messages, which should get administrator/user for chatbox
    *
    * */
   public static function getPendingMessagesChatbox($chat_id,$message_id)
   {
       $db = ezcDbInstance::get();
       $stmt = $db->prepare('SELECT lh_msg.* FROM lh_msg INNER JOIN ( SELECT id FROM lh_msg WHERE chat_id = :chat_id AND id >= :message_id ORDER BY id ASC) AS items ON lh_msg.id = items.id');
       $stmt->bindValue( ':chat_id',$chat_id,PDO::PARAM_INT);
       $stmt->bindValue( ':message_id',$message_id,PDO::PARAM_INT);
       $stmt->setFetchMode(PDO::FETCH_ASSOC);
       $stmt->execute();
       $rows = $stmt->fetchAll();

       return $rows;
   }
   
   /**
    * Get last message for chatbox
    *
    * */
   public static function getGetLastChatMessage($chat_id)
   {
       $db = ezcDbInstance::get();
       $stmt = $db->prepare('SELECT lh_msg.* FROM lh_msg INNER JOIN ( SELECT id FROM lh_msg WHERE chat_id = :chat_id ORDER BY id DESC LIMIT 1 OFFSET 0) AS items ON lh_msg.id = items.id');
       $stmt->bindValue( ':chat_id',$chat_id,PDO::PARAM_INT);
       $stmt->setFetchMode(PDO::FETCH_ASSOC);
       $stmt->execute();
       $row = $stmt->fetch();

       return $row;
   }
   
   
   /**
    * Get last message for chat editing admin last message
    *
    * */
   public static function getGetLastChatMessageEdit($chat_id, $user_id)
   {
       $db = ezcDbInstance::get();
       $stmt = $db->prepare('SELECT lh_msg.* FROM lh_msg INNER JOIN ( SELECT id FROM lh_msg WHERE chat_id = :chat_id AND user_id = :user_id ORDER BY id DESC LIMIT 1 OFFSET 0) AS items ON lh_msg.id = items.id');
       $stmt->bindValue( ':chat_id',$chat_id,PDO::PARAM_INT);
       $stmt->bindValue( ':user_id',$user_id,PDO::PARAM_INT);
       $stmt->setFetchMode(PDO::FETCH_ASSOC);
       $stmt->execute();
       $row = $stmt->fetch();

       return $row;
   }
   
   
   
   /**
    * Get last message for browser notification
    *
    * */
   public static function getGetLastChatMessagePending($chat_id, $visitorMessages = false, $limit = 3, $implode = "\n")
   {
       $filter = '';
       if ($visitorMessages == true) {
           $filter = ' AND user_id = 0';
       }

       $db = ezcDbInstance::get();
       $stmt = $db->prepare("SELECT lh_msg.msg FROM lh_msg INNER JOIN ( SELECT id FROM lh_msg WHERE chat_id = :chat_id {$filter} ORDER BY id DESC LIMIT {$limit} OFFSET 0) AS items ON lh_msg.id = items.id");
       $stmt->bindValue( ':chat_id',$chat_id,PDO::PARAM_INT);
       $stmt->setFetchMode(PDO::FETCH_ASSOC);
       $stmt->execute();
       $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

       $plain = erLhcoreClassBBCodePlain::make_clickable(implode($implode, array_reverse($rows)), array('download_policy' => 0, 'operator_render' => true, 'sender' => 0));

       $text = mb_substr($plain,-200);
       
       return $text;
   }

   /**
    * Gets chats messages, used to review chat etc.
    * */
   public static function getChatMessages($chat_id, $limit = 1000, $lastMessageId = 0)
   {
       if ($lastMessageId == 0) {
           $db = ezcDbInstance::get();
           $stmt = $db->prepare('SELECT lh_msg.* FROM lh_msg INNER JOIN ( SELECT id FROM lh_msg WHERE chat_id = :chat_id ORDER BY id DESC LIMIT :limit) AS items ON lh_msg.id = items.id ORDER BY lh_msg.id ASC');
           $stmt->bindValue( ':chat_id',$chat_id,PDO::PARAM_INT);
           $stmt->bindValue( ':limit',$limit,PDO::PARAM_INT);
           $stmt->setFetchMode(PDO::FETCH_ASSOC);
           $stmt->execute();
           $rows = $stmt->fetchAll();
       } else {
           $db = ezcDbInstance::get();
           $stmt = $db->prepare('SELECT lh_msg.* FROM lh_msg INNER JOIN ( SELECT id FROM lh_msg WHERE chat_id = :chat_id AND lh_msg.id < :message_id ORDER BY id DESC LIMIT :limit) AS items ON lh_msg.id = items.id ORDER BY lh_msg.id ASC');
           $stmt->bindValue( ':chat_id',$chat_id,PDO::PARAM_INT);
           $stmt->bindValue( ':limit',$limit,PDO::PARAM_INT);
           $stmt->bindValue( ':message_id',$lastMessageId,PDO::PARAM_INT);
           $stmt->setFetchMode(PDO::FETCH_ASSOC);
           $stmt->execute();
           $rows = $stmt->fetchAll();
       }

       return $rows;
   }

   /**
    * Get first user mesasge for prefilling chat
    * */
   public static function getFirstUserMessage($chat_id)
   {
	   	$db = ezcDbInstance::get();
	   	$stmt = $db->prepare('SELECT lh_msg.msg,lh_msg.user_id FROM lh_msg INNER JOIN ( SELECT id FROM lh_msg WHERE chat_id = :chat_id AND (user_id = 0 OR user_id = -2) ORDER BY id ASC LIMIT 10) AS items ON lh_msg.id = items.id');
	   	$stmt->bindValue( ':chat_id',$chat_id,PDO::PARAM_INT);
	   	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	   	$stmt->execute();

	   	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

	   	$responseRows = [];
	   	foreach ($rows as $row) {
            $responseRows[] = ($row['user_id'] == 0 ? erTranslationClassLhTranslation::getInstance()->getTranslation('chat/startchat','You') : erTranslationClassLhTranslation::getInstance()->getTranslation('chat/startchat','Us')) . ': ' . $row['msg'];
        }

	   	if (empty($responseRows)) {
	   	    return '';
        }

	   	return erTranslationClassLhTranslation::getInstance()->getTranslation('chat/startchat','Summary') . ":\n".implode("\n",$responseRows);
   }

   public static function hasAccessToWrite($chat)
   {
        if ($chat->user_id > 0 && ($currentUser = erLhcoreClassUser::instance()) && $currentUser->isLogged() && $currentUser->getUserID() == $chat->user_id) {
            return true;
        }

        $dep = erLhcoreClassUserDep::getUserReadDepartments();
        return !in_array($chat->dep_id, $dep);
   }

   public static function hasAccessToRead($chat, $params = [])
   {
       $currentUser = erLhcoreClassUser::instance();

       $userData = $currentUser->getUserData(true);

       if ( $userData->all_departments == 0 && $chat->dep_id != 0) {
            /*
             * --From now permission is strictly by assigned department, not by chat owner
             *
             * Finally decided to keep this check, it allows more advance permissions configuration
             * */
            if ((!isset($params['scope']) || $params['scope'] != 'dep') && !$currentUser->hasAccessTo('lhchat','allowopenclosedchats') && $chat->status == erLhcoreClassModelChat::STATUS_CLOSED_CHAT) {
                return false;
            }

       		if ((!isset($params['scope']) || $params['scope'] != 'dep') && $chat->user_id == $currentUser->getUserID()) return true;

            $userDepartaments = erLhcoreClassUserDep::getUserDepartaments($currentUser->getUserID(), $userData->cache_version);

            if (count($userDepartaments) == 0) return false;

            if (in_array($chat->dep_id,$userDepartaments)) {
            	if ((isset($params['scope']) && $params['scope'] == 'dep') || ((!isset($params['scope']) || $params['scope'] != 'dep') && ($currentUser->hasAccessTo('lhchat','allowopenremotechat') == true || $chat->status == erLhcoreClassModelChat::STATUS_OPERATORS_CHAT))){
            		return true;
            	} elseif ((!isset($params['scope']) || $params['scope'] != 'dep') && ($chat->user_id == 0 || $chat->user_id == $currentUser->getUserID())) {
            		return true;
            	}
            	return false;
            }

            return false;

       } elseif ((!isset($params['scope']) || $params['scope'] != 'dep') && $userData->all_departments != 0 && $chat->user_id != 0 && $chat->user_id != $currentUser->getUserID() && !$currentUser->hasAccessTo('lhchat','allowopenremotechat')) {
           return false;
       }

       if ((!isset($params['scope']) || $params['scope'] != 'dep') && !$currentUser->hasAccessTo('lhchat','allowopenclosedchats') && $chat->status == erLhcoreClassModelChat::STATUS_CLOSED_CHAT) {
           return false;
       }

       return true;
   }

   public static function formatSeconds($seconds, $biggestReturn = false) {

	    $y = floor($seconds / (86400*365.25));
	    $d = floor(($seconds - ($y*(86400*365.25))) / 86400);
	    $h = gmdate('H', (int)$seconds);
	    $m = gmdate('i', (int)$seconds);
	    $s = gmdate('s', (int)$seconds);

	    $parts = array();
        $hasYears = false;
        $hasDays = false;
        $hasHours = false;

	    if ($y > 0)
	    {
	    	$parts[] = $y . ' .y';
            $hasYears = true;
	    }

	    if ($d > 0)
	    {
	    	$parts[] = $d . ' d.';
            $hasDays = true;
	    }

	    if ($h > 0 && $hasYears == false)
	    {
	    	$parts[] = $h . ' h.';
            $hasHours = true;
	    }

	    if ($m > 0 && $hasDays == false && $hasYears == false)
	    {
	    	$parts[] = $m . ' m.';
	    }

	    if ($s > 0 && $hasHours == false && $hasDays == false && $hasYears == false)
	    {
	    	$parts[] = $s . ' s.';
	    }

	    if ($biggestReturn == true) {
	        return array_shift($parts);
        }

	    return implode(' ', $parts);

   }

   /**
    * Is chat activated and user can send messages.
    *
    * */
   public static function isChatActive($chat_id,$hash)
   {
       $db = ezcDbInstance::get();
       $stmt = $db->prepare('SELECT COUNT(id) AS found FROM lh_chat WHERE id = :chat_id AND hash = :hash AND status = 1');
       $stmt->bindValue( ':chat_id',$chat_id,PDO::PARAM_INT);
       $stmt->bindValue( ':hash',$hash);

       $stmt->execute();
       $rows = $stmt->fetchAll();
       return $rows[0]['found'] == 1;
   }

   public static function generateHash($length = 40)
   {
       $string = '';

       while (($len = strlen($string)) < $length) {
           $size = $length - $len;

           $bytes = '';

           if (function_exists('random_bytes')) {
               try {
                   $bytes = random_bytes($size);
               } catch (\Exception $e) {
                   //Do nothing
               }
           } elseif (function_exists('openssl_random_pseudo_bytes')) {
               /** @noinspection CryptographicallySecureRandomnessInspection */
               $bytes = openssl_random_pseudo_bytes($size);
           }

           if ($bytes === '') {
               //We failed to produce a proper random string, so make do.
               //Use a hash to force the length to the same as the other methods
               $bytes = hash('sha256', uniqid((string) mt_rand(), true), true);
           }

           $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
       }

       return $string;;
   }
   
   public static function setTimeZoneByChat($chat)
   {
   		if ($chat->user_tz_identifier != '') {
   			erLhcoreClassModule::$defaultTimeZone = $chat->user_tz_identifier;
   			date_default_timezone_set(erLhcoreClassModule::$defaultTimeZone);   			
   		} 
   }
   
   public static function getSession()
   {
        if ( !isset( self::$persistentSession ) )
        {
            self::$persistentSession = new ezcPersistentSession(
                ezcDbInstance::get(),
                new ezcPersistentCodeManager( './pos/lhchat' )
            );
        }
        return self::$persistentSession;
   }

   public static function formatDate($ts) {
	   	if (date('Ymd') == date('Ymd',$ts)) {
	   		return date(erLhcoreClassModule::$dateHourFormat,$ts);
	   	} else {
	   		return date(erLhcoreClassModule::$dateDateHourFormat,$ts);
	   	}	  
   }
   
   public static function closeChatCallback($chat, $operator = false) {
        erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.close',array('chat' => & $chat, 'user_data' => $operator));

        $dep = $chat->department;

        if ( $dep !== false) {
            self::updateDepartmentStats($dep);
        }

        if ( $dep !== false && ($dep->inform_close == 1 || $dep->inform_close_all == 1)) {
            erLhcoreClassChatMail::informChatClosed($chat, $operator);
        }

        $checkEmpty = erLhcoreClassModelChatConfig::fetch('del_on_close_no_msg')->current_value;

        if ($checkEmpty == 1) {
           if (erLhcoreClassModelmsg::getCount(['filter' => ['user_id' => 0, 'chat_id' => $chat->id]]) === 0) {
               erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.delete', array('chat' => & $chat, 'user_data' => $operator));
               $chat->removeThis();
           }
        }
   }

   /**
    * Update department main statistic for frontend
    * This can be calculated in background as it does not influence anything except statistic
    * */
   public static function updateDepartmentStats($dep) {
       try {

           if (erLhcoreClassSystem::instance()->backgroundMode == false && class_exists('erLhcoreClassExtensionLhcphpresque')) {
               $inst_id = class_exists('erLhcoreClassInstance') ? erLhcoreClassInstance::$instanceChat->id : 0;
               erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->enqueue('lhc_stats_resque', 'erLhcoreClassChatStatsResque', array('inst_id' => $inst_id,'type' => 'dep', 'id' => $dep->id));
               return;
           }

           erLhcoreClassChatStatsResque::updateStats($dep);

       } catch (Exception $e) {
           //Fail silently as it's just statistic update operation
       }
   }

    /**
     * @desc returns departments by department groups
     *
     * @param array $group_ids
     *
     * @return mixed
     */
   public static function getDepartmentsByDepGroup($group_ids) {
       static $group_id_by_group = array();
       $key = implode('_',$group_ids);

       if (!key_exists($key, $group_id_by_group))
       {
           $db = ezcDbInstance::get();
           $stmt = $db->prepare('SELECT dep_id FROM lh_departament_group_member WHERE dep_group_id IN (' . implode(',', $group_ids) . ')');
           $stmt->execute();
           $depIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

           $group_id_by_group[$key] = $depIds;
       }

       if (empty($group_id_by_group[$key])) {
           return [];
       }
       
       return $group_id_by_group[$key];
   }

    /**
     * @desc returns users id by users groups
     *
     * @param array $group_ids
     *
     * @return mixed
     */
   public static function getUserIDByGroup($group_ids) {
        static $user_id_by_group = array();
        $key = implode('_',$group_ids);

        if (!key_exists($key, $user_id_by_group))
        {
            $db = ezcDbInstance::get();
            $stmt = $db->prepare('SELECT user_id FROM lh_groupuser WHERE group_id IN ('. implode(',', $group_ids) . ')');
            $stmt->execute();
            $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $user_id_by_group[$key] = $userIds;
        }

        return $user_id_by_group[$key];
   }

   public static function canReopen(erLhcoreClassModelChat $chat, $skipStatusCheck = false) {
   		if ( ($chat->status == erLhcoreClassModelChat::STATUS_CLOSED_CHAT || $skipStatusCheck == true)) {
			if (($chat->status_sub != erLhcoreClassModelChat::STATUS_SUB_USER_CLOSED_CHAT || $skipStatusCheck == true) && ($chat->last_user_msg_time > time()-600 || $chat->last_user_msg_time == 0)) {
				return true;
			} else {
				return false;
			}
   		}
   		return false;
   }

   public static function canReopenDirectly($params = array()) {
	   	if (($chatPart = CSCacheAPC::getMem()->getSession('chat_hash_widget_resume',true)) !== false) {
	   		try {
		   		$parts = explode('_', $chatPart);
		   		$chat = erLhcoreClassModelChat::fetch($parts[0]);
		   		
		   		if ($chat instanceof erLhcoreClassModelChat && ($chat->status_sub != erLhcoreClassModelChat::STATUS_SUB_USER_CLOSED_CHAT) && ($chat->last_user_msg_time > time()-600 || $chat->last_user_msg_time == 0) && (!isset($params['reopen_closed']) || $params['reopen_closed'] == 1 || ($params['reopen_closed'] == 0 && $chat->status != erLhcoreClassModelChat::STATUS_CLOSED_CHAT))) {
		   			return array('id' => $parts[0],'hash' => $parts[1]);
		   		} else {
					return false;
				}

	   		} catch (Exception $e) {
	   			return false;
	   		}
	   	}

	   	return false;
   }

   public static function reopenChatWidgetV2($onlineUser, $chat, $params) {
        if ($onlineUser->chat_id > 0) {
            $chatOld = erLhcoreClassModelChat::fetch($onlineUser->chat_id);

            // Old chat was not found
            if (!($chatOld instanceof erLhcoreClassModelChat)) {
                return;
            }

            if ($chatOld->status == erLhcoreClassModelChat::STATUS_ACTIVE_CHAT ||
                $chatOld->status == erLhcoreClassModelChat::STATUS_PENDING_CHAT ||
                $chatOld->status == erLhcoreClassModelChat::STATUS_BOT_CHAT
                || ($params['reopen_closed'] == true && $chatOld->status == erLhcoreClassModelChat::STATUS_CLOSED_CHAT && ($chat->last_user_msg_time == 0 || $chat->last_op_msg_time > time() - (int)$params['open_closed_chat_timeout']))
            ) {
                // Just switch chat ID, that's it.
                // The rest will be done automatically.
                $chat->id = $chatOld->id;
                $chat->remarks = $chatOld->remarks;
                $chat->old_last_msg_id = $chatOld->last_msg_id;
            }
        }
   }

   /**
    * Is there any better way to initialize __get variables?
    * */
   public static function prefillGetAttributes(& $objects, $attrs = array(),$attrRemove = array(), $params = array()) {   		
   		foreach ($objects as & $object) {
   			foreach ($attrs as $attr) {
   				$object->{$attr};
   			};

            if (isset($params['additional_columns']) && is_array($params['additional_columns']) && !empty($params['additional_columns'])) {

                foreach ($params['additional_columns'] as $column) {

                    // Translatable title
                    if (strpos($column->column_name,'{args.') !== false) {
                        $object->{'cc_' . $column->id . '_tt'} = erLhcoreClassGenericBotWorkflow::translateMessage($column->column_name, array('chat' => $object, 'args' => ['chat' => $object]));
                    }

                    if (strpos($column->variable,'additional_data.') !== false) {
                        $additionalDataArray = $object->additional_data_array;
                        if (is_array($additionalDataArray)) {
                            foreach ($additionalDataArray as $additionalItem) {

                                $valueCompare = false;

                                if (isset($additionalItem['identifier'])) {
                                    $valueCompare = $additionalItem['identifier'];
                                } elseif (isset($additionalItem['key'])) {
                                    $valueCompare = $additionalItem['key'];
                                }

                                if ($valueCompare !== false && $valueCompare == str_replace('additional_data.','',$column->variable)) {
                                    $object->{'cc_'.$column->id} = $additionalItem['value'];
                                    break;
                                }
                            }
                        }
                    } elseif (strpos($column->variable,'chat_variable.') !== false) {
                        $additionalDataArray = $object->chat_variables_array;
                        if (is_array($additionalDataArray)) {
                            $variableNames = str_replace('chat_variable.','', $column->variable);
                            foreach (explode('||',$variableNames) as $variableName) {
                                if (isset($object->chat_variables_array[$variableName]) && $object->chat_variables_array[$variableName] != '') {
                                    $object->{'cc_' . $column->id} = $object->chat_variables_array[$variableName];
                                    break;
                                } elseif (strpos($variableName,'.') !== false) {
                                    $valueAttribute = erLhcoreClassGenericBotActionRestapi::extractAttribute($object->chat_variables_array, $variableName, '.');
                                    if ($valueAttribute['found'] == true && !is_array($valueAttribute['value']) && !is_object($valueAttribute['value'])) {
                                        $object->{'cc_' . $column->id} = $valueAttribute['value'];
                                        break;
                                    }
                                }
                            }
                        }
                    } elseif (strpos($column->variable,'lhc.') !== false) {
                        $variableName = str_replace('lhc.','', $column->variable);
                        $variableValue = $object->{$variableName};
                        if (isset($variableValue) && $variableValue != '') {
                            $object->{'cc_'.$column->id} = $variableValue;
                        }
                    } elseif (strpos($column->variable,'{args.') !== false) {
                        foreach (explode('||',$column->variable) as $variableValue) {
                            $variableValueReplaced = erLhcoreClassGenericBotWorkflow::translateMessage($variableValue, array('chat' => $object, 'args' => ['chat' => $object]));
                            $object->{'cc_' . $column->id} = $variableValueReplaced;
                            if ($variableValueReplaced != '') {
                                break;
                            }
                        }

                    }
                }
            }

   			foreach ($attrRemove as $attr) {
   				$object->{$attr} = null;
   				if (isset($params['clean_ignore'])) {
   				    unset($object->{$attr});
                }
   			}
   			
   			if (isset($params['remove_all']) && $params['remove_all'] == true) {
   			    foreach ($object as $attr => $value) {
   			        if (!in_array($attr, $attrs)) {
   			            $object->$attr = null;
   			        }
   			    }
   			}

   			if (!isset($params['do_not_clean'])){
   			    if (isset($params['filter_function'])){
                    $object = (object)array_filter((array)$object,function ($value) {
                        return is_array($value) || (!is_null($value) && strlen($value) > 0);
                    });
                } else {
                    $object = (object)array_filter((array)$object);
                }
            }
   		}
   }

   /**
    * Is there any better way to initialize __get variables?
    * */
   public static function prefillGetAttributesObject(& $object, $attrs = array(),$attrRemove = array(), $params = array()) {   		
   	
   			foreach ($attrs as $attr) {
   				$object->{$attr};
   			};
   			
   			foreach ($attrRemove as $attr) {
   				$object->{$attr} = null;
   			};
   			
   			if (!isset($params['do_not_clean']))
   			$object = (object)array_filter((array)$object);   		
   }
   
   public static function validateFilterIn(& $params) {
   		foreach ($params as & $param) {
   			$param = (int)$param;
   		}
   }

   public static function validateFilterInString(& $params) {
   		foreach ($params as & $param) {
   			$param =  preg_replace('/[^a-zA-Z0-9]/', '', $param );
   		}
   }
   
   /*
    * Example of call
    * This method can prefill first and second level objects without
    * requirement for each object to be fetched separately
    * Increases performance drastically
   erLhcoreClassModuleFunctions::prefillObjects($items, array(
       array(
           'order_id',
           'order',
           'dommyClass::getList'
       ),      
       array(
           'status_id',
           'status',
           'dommyClass::getList'
       ),
       array(
           array(
               'order',
               'registration_id'
           ),
           array(
               'order',
               'registration'
           ),
           'dommyClass::getList',
           'id'
       )
   ));
   */
   public static function prefillObjects(& $objects, $attrs = array(), $params = array())
   {
       $cache = CSCacheAPC::getMem();
   
       foreach ($attrs as $attr) {
           $ids = array();
           foreach ($objects as $object) {
               if (is_array($attr[0])) {
                   if (is_object($object->{$attr[0][0]}) && $object->{$attr[0][0]}->{$attr[0][1]} > 0) {
                       $ids[] = $object->{$attr[0][0]}->{$attr[0][1]};
                   }
               } else {
                   if ($object->{$attr[0]} > 0) {
                       $ids[] = $object->{$attr[0]};
                   }
               }
           }
   
           $ids = array_unique($ids);
   
           if (! empty($ids)) {
   
               // First try to fetch from memory
               if (isset($params['use_cache'])) {
                   list ($class) = explode('::', $attr[2]);
                   $class = strtolower($class);
   
                   $cacheKeyPrefix = $cache->cacheGlobalKey . 'object_' . $class . '_';
                   $cacheKeyPrefixStore = 'object_' . $class . '_';
   
                   $cacheKeys = array();
                   foreach ($ids as $id) {
                       $cacheKeys[] = $cacheKeyPrefix . $id;
                   }
   
                   $cachedObjects = $cache->restoreMulti($cacheKeys);
   
                   if (! empty($cachedObjects)) {
                       foreach ($objects as & $item) {
                           if (is_array($attr[0])) {
                               if (isset($cachedObjects[$cacheKeyPrefix . $item->{$attr[0][0]}->{$attr[0][1]}]) && $cachedObjects[$cacheKeyPrefix . $item->{$attr[0][0]}->{$attr[0][1]}] !== false) {
                                   $item->{$attr[1][0]}->{$attr[1][1]} = $cachedObjects[$cacheKeyPrefix . $item->{$attr[0][0]}->{$attr[0][1]}];
                                   $key = array_search($item->{$attr[0][0]}->{$attr[0][1]}, $ids);
                                   if ($key !== false) {
                                       unset($ids[$key]);
                                   }
                               }
                           } else {
                               if (isset($cachedObjects[$cacheKeyPrefix . $item->{$attr[0]}]) && $cachedObjects[$cacheKeyPrefix . $item->{$attr[0]}] !== false) {
                                   $item->{$attr[1]} = $cachedObjects[$cacheKeyPrefix . $item->{$attr[0]}];
                                   $key = array_search($item->{$attr[0]}, $ids);
                                   if ($key !== false) {
                                       unset($ids[$key]);
                                   }
                               }
                           }
                       }
                   }
               }
   
               // Check again that ID's were not filled
               if (! empty($ids)) {
                   $filter_attr = 'id';
   
                   if (isset($attr[3]) && $attr[3]) {
                       $filter_attr = $attr[3];
                   }
   
                   $objectsPrefill = call_user_func($attr[2], array(
                       'limit' => false,
                       'filterin' => array(
                           $filter_attr => $ids
                       )
                   ));
   
                   if ($filter_attr != 'id') {
                       $objectsPrefillNew = array();
                       foreach ($objectsPrefill as $key => $value) {
                           $objectsPrefillNew[$value->$filter_attr] = $value;
                       }
                       $objectsPrefill = $objectsPrefillNew;
                   }
   
                   foreach ($objects as & $item) {
   
                       if (is_array($attr[0])) {
                           if (is_object($item->{$attr[0][0]}) && isset($objectsPrefill[$item->{$attr[0][0]}->{$attr[0][1]}])) {
                               $item->{$attr[1][0]}->{$attr[1][1]} = $objectsPrefill[$item->{$attr[0][0]}->{$attr[0][1]}];
   
                               if (isset($params['use_cache']) && $params['use_cache'] == true) {
                                   $cache->store($cacheKeyPrefixStore . $item->{$attr[0][0]}->{$attr[0][1]}, $objectsPrefill[$item->{$attr[0][0]}->{$attr[0][1]}]);
                               }
                           }
                       } else {
                           if (isset($objectsPrefill[$item->{$attr[0]}])) {
   
                               $item->{$attr[1]} = $objectsPrefill[$item->{$attr[0]}];
   
                               if (isset($params['fill_cache']) && $params['fill_cache'] == true) {
                                   $GLOBALS[get_class($objectsPrefill[$item->{$attr[0]}]) . '_' . $item->{$attr[0]}] = $item->{$attr[1]};
                               }
   
                               if (isset($params['use_cache']) && $params['use_cache'] == true) {
                                   $cache->store($cacheKeyPrefixStore . $item->{$attr[0]}, $objectsPrefill[$item->{$attr[0]}]);
                               }
                           }
                       }
                   }
               }
           }
       }
   }

   public static function updateActiveChats($user_id, $ignoreEvent = false)
   {
       if ($user_id == 0) {
           return true;
       }

       $db = ezcDbInstance::get();
       $stmt = $db->prepare('SELECT id FROM lh_userdep WHERE user_id = :user_id');
       $stmt->bindValue(':user_id', $user_id,PDO::PARAM_STR);
       $stmt->execute();

       $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

       $activeChats = null;
       $pendingChats = null;
       $inactiveChats = null;

       $activeMails = null;
       $pendingMails = null;
       $success = false;

       if (!empty($ids)) {

           // Try 3 times to update table
           for ($i = 0; $i < 3; $i++)
           {
               try {

                   if ($activeChats === null) {
                       $activeChats = erLhcoreClassChat::getCount(array('filter' => array('user_id' => $user_id, 'status' => erLhcoreClassModelChat::STATUS_ACTIVE_CHAT)));
                   }

                   if ($pendingChats === null) {
                       $pendingChats = erLhcoreClassChat::getCount(array('filter' => array('user_id' => $user_id, 'status' => erLhcoreClassModelChat::STATUS_PENDING_CHAT)));
                   }

                   if ($inactiveChats === null) {
                       $inactiveChats = erLhcoreClassChat::getCount(array('filterin' => array('status' => array(erLhcoreClassModelChat::STATUS_PENDING_CHAT, erLhcoreClassModelChat::STATUS_ACTIVE_CHAT), 'status_sub' => array(erLhcoreClassModelChat::STATUS_SUB_SURVEY_COMPLETED, erLhcoreClassModelChat::STATUS_SUB_USER_CLOSED_CHAT, erLhcoreClassModelChat::STATUS_SUB_SURVEY_SHOW)), 'filter' => array('user_id' => $user_id)));
                   }

                   if ($activeMails === null) {
                       $activeMails = erLhcoreClassModelMailconvConversation::getCount(array('filter' => array('user_id' => $user_id, 'status' => erLhcoreClassModelMailconvConversation::STATUS_ACTIVE)));
                   }

                   if ($pendingMails === null) {
                       $pendingMails = erLhcoreClassModelMailconvConversation::getCount(array('filter' => array('user_id' => $user_id, 'status' => erLhcoreClassModelMailconvConversation::STATUS_PENDING)));
                   }

                   $stmt = $db->prepare('UPDATE lh_userdep SET active_mails = :active_mails, pending_mails = :pending_mails, active_chats = :active_chats, pending_chats = :pending_chats, inactive_chats = :inactive_chats WHERE id IN (' . implode(',', $ids) . ');');
                   $stmt->bindValue(':active_chats',(int)$activeChats,PDO::PARAM_INT);
                   $stmt->bindValue(':pending_chats',(int)$pendingChats,PDO::PARAM_INT);
                   $stmt->bindValue(':inactive_chats',(int)$inactiveChats,PDO::PARAM_INT);
                   $stmt->bindValue(':active_mails',(int)$activeMails,PDO::PARAM_INT);
                   $stmt->bindValue(':pending_mails',(int)$pendingMails,PDO::PARAM_INT);
                   $stmt->execute();

                   $success = true;
                   // Finish cycle
                   break;

               } catch (Exception $e) {
                   if ($i == 2) { // It was last try
                       if ($ignoreEvent === false) {
                           erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.update_active_chats',array('user_id' => $user_id));
                       }

                       erLhcoreClassLog::write($e->getMessage() . "\n" . $e->getTraceAsString(),
                           ezcLog::SUCCESS_AUDIT,
                           array(
                               'source' => 'lhc',
                               'category' => 'update_active_chats',
                               'line' => __LINE__,
                               'file' => __FILE__,
                               'object_id' => $user_id
                           )
                       );

                       return $success;
                   } else {
                       // Just sleep for fraction of second and try again
                       usleep(150);
                   }
               }
           }

           if ($ignoreEvent === false) {
                erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.update_active_chats',array('user_id' => $user_id));
           }
       }

       return $success;
   }
   
   public static function getAdjustment($geo_adjustment, $onlineUserVid = '', $widgetMode = false, $onlineUserDefined = false){
   	
   		$responseStatus = array('status' => 'normal');
   		$onlineUser = false;
   		
	   	if (isset($geo_adjustment['use_geo_adjustment']) && $geo_adjustment['use_geo_adjustment'] == true){

	   		if ($widgetMode === true && $geo_adjustment['apply_widget'] == 0){
	   			return $responseStatus;
	   		}

	   		if (is_object($onlineUserDefined)){
	   			$onlineUser = $onlineUserDefined;
	   		} elseif (!empty($onlineUserVid)){
	   			$onlineUser = erLhcoreClassModelChatOnlineUser::fetchByVid($onlineUserVid);
	   		}

	   		if ($onlineUser === false) {	   		
		   		$onlineUser = new erLhcoreClassModelChatOnlineUser(); // Just to pass instance
		   		$onlineUser->ip = erLhcoreClassIPDetect::getIP();
		   		erLhcoreClassModelChatOnlineUser::detectLocation($onlineUser);
	   		}

	   		$countriesAvailableFor = array();
	   		if ($geo_adjustment['available_for'] != '') {
	   			$countriesAvailableFor = explode(',', $geo_adjustment['available_for']);
	   		}

	   		if (!in_array($onlineUser->user_country_code, $countriesAvailableFor)){
	   			if ($geo_adjustment['other_countries'] == 'all') {
	   				if (($geo_adjustment['other_status']) == 'offline'){	   				
	   					$responseStatus = array('status' => 'offline');
	   				} else {
	   					$responseStatus = array('status' => 'hidden');
	   				}
	   			} else {
	   				if ($geo_adjustment['hide_for'] != '') {
	   					$countrieshideFor = explode(',', $geo_adjustment['hide_for']);
	   					if (in_array($onlineUser->user_country_code, $countrieshideFor)){
	   						if (($geo_adjustment['other_status']) == 'offline'){
	   							$responseStatus = array('status' => 'offline');
	   						} else {
	   							$responseStatus = array('status' => 'hidden');
	   						}
	   					} else {
	   						if (($geo_adjustment['rest_status']) == 'offline'){
	   							$responseStatus = array('status' => 'offline');
	   						} elseif ($geo_adjustment['rest_status'] == 'normal') {
	   							$responseStatus = array('status' => 'normal');
	   						} else {
	   							$responseStatus = array('status' => 'hidden');
	   						}
	   					}
	   				} else {
	   					if (($geo_adjustment['rest_status']) == 'offline'){
   							$responseStatus = array('status' => 'offline');
	   					} elseif ($geo_adjustment['rest_status'] == 'normal') {
   							$responseStatus = array('status' => 'normal');
   						} else {
   							$responseStatus = array('status' => 'hidden');
   						}
	   				}
	   			}
	   		} // Normal status
	   	}

	   	return $responseStatus;
   }

   public static function lockDepartment($depId, $db)
   {
       $stmt = $db->prepare('SELECT id FROM lh_userdep WHERE dep_id = :dep_id');
       $stmt->bindValue(':dep_id',$depId);
       $stmt->execute();

       $recordIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

       if (!empty($recordIds)) {
           try {
               $stmt = $db->prepare('SELECT 1 FROM lh_userdep WHERE id IN (' . implode(',', $recordIds) . ') ORDER BY id ASC FOR UPDATE;');
               $stmt->execute();
           } catch (Exception $e) {
               try {
                   usleep(100);
                   $stmt = $db->prepare('SELECT 1 FROM lh_userdep WHERE id IN (' . implode(',', $recordIds) . ') ORDER BY id ASC FOR UPDATE;');
                   $stmt->execute();
               } catch (Exception $e) {
                   error_log($e->getMessage() . "\n" . $e->getTraceAsString());
               }
           }
       }
   }

   public static function lockOperatorsByDepartment($depId, $db)
   {
       $stmt = $db->prepare('SELECT `id` FROM lh_userdep WHERE `user_id` IN (SELECT `user_id` FROM lh_userdep WHERE `dep_id` = :dep_id AND hide_online = 0 AND ro = 0 AND last_activity > :last_activity)');
       $stmt->bindValue(':dep_id',$depId);
       $stmt->bindValue(':last_activity',time() - 600);
       $stmt->execute();

       $recordIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
       if (!empty($recordIds)) {
           try {
               $stmt = $db->prepare('SELECT 1 FROM lh_userdep WHERE id IN (' . implode(',', $recordIds) . ') ORDER BY id ASC FOR UPDATE;');
               $stmt->execute();
               return true;
           } catch (Exception $e) {
               try {
                   usleep(100);
                   $stmt = $db->prepare('SELECT 1 FROM lh_userdep WHERE id IN (' . implode(',', $recordIds) . ') ORDER BY id ASC FOR UPDATE;');
                   $stmt->execute();
                   return true;
               } catch (Exception $e) {
                   error_log($e->getMessage() . "\n" . $e->getTraceAsString());
               }
           }
       }
       return false;
   }

   /**
    * @see https://github.com/LiveHelperChat/livehelperchat/pull/809
    *
    * @param array $value
    * */
   public static function safe_json_encode($value) {
        
       $encoded = json_encode($value);
        
       switch (json_last_error()) {
           case JSON_ERROR_NONE:
               return $encoded;
           case JSON_ERROR_DEPTH:
               return 'Maximum stack depth exceeded'; // or trigger_error() or throw new Exception()
           case JSON_ERROR_STATE_MISMATCH:
               return 'Underflow or the modes mismatch'; // or trigger_error() or throw new Exception()
           case JSON_ERROR_CTRL_CHAR:
               return 'Unexpected control character found';
           case JSON_ERROR_SYNTAX:
               return 'Syntax error, malformed JSON'; // or trigger_error() or throw new Exception()
           case JSON_ERROR_UTF8:
               $clean = self::utf8ize($value);
               return self::safe_json_encode($clean);
           default:
               return 'Unknown error'; // or trigger_error() or throw new Exception()
                
       }
   }
    
   public static function getAgoFormat($ts) {
       
       $lastactivity_ago = '';
       
       if ( $ts > 0 ) {
       
           $periods         = array("s.", "m.", "h.", "d.", "w.", "M.", "y.", "dec.");
           $lengths         = array("60","60","24","7","4.35","12","10");
       
           $difference     = time() - $ts;
       
           for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
               $difference /= $lengths[$j];
           }
       
           $difference = round($difference);
       
           $lastactivity_ago = "$difference $periods[$j]";
       };
       
       return $lastactivity_ago;       
   }
   
   /**
    * Make conversion if required
    *
    * @param unknown $mixed
    *
    * @return string
    */
   public static function utf8ize($mixed) {
       if (is_array($mixed)) {
           foreach ($mixed as $key => $value) {
               $mixed[$key] = self::utf8ize($value);
           }
       } else if (is_string ($mixed)) {
           return utf8_encode($mixed);
       }
       return $mixed;
   }

   public static function array_flatten($array = null) {
        $result = array();

        if (!is_array($array)) {
            $array = func_get_args();
        }

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, self::array_flatten($value));
            } else {
                $result = array_merge($result, array($key => $value));
            }
        }

        return $result;
    }

    public static function cleanForDashboard($chatLists) {
       $attrsClean = array('online_user_id','uagent','user_status','last_user_msg_time','last_op_msg_time','lsync','gbot_id');
        foreach ($chatLists as & $chatList) {
            foreach ($chatList as & $chat) {
                foreach ($attrsClean as $attrClean) {
                    if (isset($chat->{$attrClean})) {
                        unset($chat->{$attrClean});
                    }
                }
            }
        }
   }

   public static function getChatSubjects($chats, $type)
   {
       $subjectsSelected = erLhAbstractModelSubjectChat::getList(array('filter' => array('chat_id' => array_keys($chats))));
       $subjectByChat = [];
       $subject_ids = [];
       foreach ($subjectsSelected as $subjectSelected) {
           $subject_ids[] = $subjectSelected->subject_id;
       }
       if (!empty($subject_ids)) {
           $subjectsMeta = erLhAbstractModelSubject::getList(array('customfilter' => ['`widgets` & ' . (int)$type],'filterin' => array('id' => array_unique($subject_ids))));
       }
       foreach ($subjectsSelected as $subjectSelected) {
           if (isset( $subjectsMeta[$subjectSelected->subject_id])) {
               $subjectByChat[$subjectSelected->chat_id][] = [
                   'n' => $subjectsMeta[$subjectSelected->subject_id]->name,
                   'c' => $subjectsMeta[$subjectSelected->subject_id]->color
               ];
           }
       }

       return $subjectByChat;
   }

   public static function extractDepartment($departments, $logInvalidRequest = true, $paramsExecution = []) {

       $hasInvalidDepartment = false;

       $output = ['argument' => [],'system' => []];
       foreach ($departments as $department) {
           if (is_numeric($department)) {
               $dep = erLhcoreClassModelDepartament::fetch((int)$department);
               if ($dep instanceof erLhcoreClassModelDepartament) {
                   $output['system'][] = (int)$department;
                   $output['argument'][] = $dep->alias == '' ? $dep->id : $dep->alias;
                   if ($dep->alias != '') {
                       $hasInvalidDepartment = true;
                   }
               } else {
                  $hasInvalidDepartment = true;
              }
           } else {
               $deps = erLhcoreClassModelDepartament::getList(['sort' => '`sort_priority` ASC, `id` ASC','filterlor' => ['alias' => [$department, erLhcoreClassSystem::instance()->SiteAccess . '-' . $department]]]);

               foreach ($deps as $depPotential) {
                   // We found department with exact alias and language code, so use it.
                   if ($depPotential->alias === erLhcoreClassSystem::instance()->SiteAccess . '-' . $department) {
                       $dep = $depPotential;
                       break;
                   }
               }

               if (!isset($dep) && !empty($deps)) {
                   $dep = array_shift($deps);
               }

               if (isset($dep) && $dep instanceof erLhcoreClassModelDepartament) {

                   if (isset($dep->bot_configuration_array['priority_check']) && $dep->bot_configuration_array['priority_check'] == 1) {

                       $chat = new erLhcoreClassModelChat();

                       // Init some main attributes
                       erLhcoreClassModelChat::detectLocation($chat, $_GET['vid'] ?? ($paramsExecution['vid'] ?? ''));
                       
                       // Detect user locale
                       $locale = erLhcoreClassChatValidator::getVisitorLocale();

                       if ($locale !== null) {
                           $chat->chat_locale = $locale;
                       }

                       // We set custom chat locale only if visitor is not using default siteaccss and default langauge is not english.
                       if (erConfigClassLhConfig::getInstance()->getSetting('site','default_site_access') != erLhcoreClassSystem::instance()->SiteAccess) {
                           $siteAccessOptions = erConfigClassLhConfig::getInstance()->getSetting('site_access_options', erLhcoreClassSystem::instance()->SiteAccess);
                           // Never override to en
                           if (isset($siteAccessOptions['content_language'])) {
                               $chat->chat_locale = $siteAccessOptions['content_language'];
                           }
                       }

                       $chat->referrer = urldecode($_GET['r'] ?? '');
                       $chat->session_referrer = urldecode($_GET['l'] ?? '');

                       if (empty($chat->referrer)) {
                           $chat->referrer = $chat->session_referrer;
                       }

                       if (empty($chat->referrer) && $chat->online_user_id > 0 && is_object($chat->online_user)) {
                           $chat->referrer = $chat->online_user->referrer;
                           $chat->session_referrer = $chat->online_user->current_page;
                           if (empty($chat->referrer)) {
                               $chat->referrer = $chat->session_referrer;
                           }
                       }

                       // Maybe we add in the future
                       // $chat->dep_id = $dep->id;

                       $priority = erLhcoreClassChatValidator::getPriorityByAdditionalData($chat, array('alias' => $department . ' OR ' . erLhcoreClassSystem::instance()->SiteAccess . '-' . $department, 'detailed' => true, 'log_if_needed' => true));

                       if ($priority !== false && $priority['dep_id'] > 0) {
                           $dep = erLhcoreClassModelDepartament::fetch($priority['dep_id']);
                       }
                   }

                   $output['system'][] = (int)$dep->id;
                   $output['argument'][] = $dep->alias == '' ? $dep->id : $dep->alias;
               } else {
                   $hasInvalidDepartment = true;
               }
           }
       }

       if ($hasInvalidDepartment == true && $logInvalidRequest == true) {
           $response = erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.extract_department', array('departments' => $departments));
           $referrer = print_r($departments, true) . "\n";
           $messageLog = $referrer . erLhcoreClassIPDetect::getIP();
           erLhcoreClassLog::write($messageLog.print_r($_SERVER, true),
               ezcLog::SUCCESS_AUDIT,
               array(
                   'source' => 'lhc',
                   'category' => 'extract_department',
                   'line' => 0,
                   'file' => '',
                   'object_id' => 0
               )
           );
           if ($response === false) {
               return ['argument' => [],'system' => []];
           }
       }

       erLhcoreClassChatValidator::$routingActions['alias_output'] = $output;

       return $output;
   }

   public static function extractTheme($themeId = null, $checkAlias = true) {

       $themeId = isset($_GET['theme']) && !empty($_GET['theme']) ? $_GET['theme'] : $themeId;

       if (!empty($themeId)) {
           if (is_numeric($themeId)) {
               $theme = erLhAbstractModelWidgetTheme::fetch($themeId);
               // Don't expose existing theme
               if ($checkAlias == true && $theme instanceof erLhAbstractModelWidgetTheme && $theme->alias != '') {
                   return false;
               }
           } else {
               $theme = erLhAbstractModelWidgetTheme::findOne(['filter' => ['alias' => $themeId]]);
           }
           if ($theme instanceof erLhAbstractModelWidgetTheme) {
               return $theme->id;
           }
       }

       return false;
   }

   // Static attribute for class
   public static $trackActivity = false;
   public static $trackTimeout = 0;
   public static $onlineCondition = 0;
   
   private static $persistentSession;
}

?>