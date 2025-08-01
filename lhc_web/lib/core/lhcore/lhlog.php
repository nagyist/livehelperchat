<?php

class erLhcoreClassLog implements ezcBaseConfigurationInitializer {

	// to write to the log put a write statement in the code with the content to log
	//
    // Basic example, where de level=WARNING, category=default and source=default : 
    // erLhcoreClassLog::write(print_r($_POST,true));
	// or: erLhcoreClassLog::write("Log entry");
    // 
    // Output Basic example to default.log file:
    // Jan 05 12:54:10 [Warning] [default] [default] Log entry 
    //
    // Custom level, category and source example: 
    // erLhcoreClassLog::write("Log entry custom level,category and source",
    //                          ezcLog::DEBUG,
    //                          array( "category" => "category debug message",
    //                                  "source" => "source debug message"
    //                               )
    //                       );
    // 
    // Output custom level, category and source example to default.log file
    // Jan 05 13:07:41 [Debug] [source debug message] [category debug message] Log entry custom level,category and show_source
    //
    //
    //
    // Custom Audit level, category, source, line and file example: 
    // $currentUser = erLhcoreClassUser::instance();
    // $userData = $currentUser->getUserData();
    //
    // erLhcoreClassLog::write("user_id: " . $userData->id . " UserName: " . $userData->username . " Objeto Modificado :" . var_export($state, true),
    //                          ezcLog::SUCCESS_AUDIT,
    //                          array(
    //                                  'source' => $class,
    //                                  'category' => 'updateInternal',
    //                                  'line' => __LINE__,
    //                                  'file' => __FILE__
    //                              )
    //                      );
    //
    // Output custom Audit level, category, source, line and file to table 'audits' in DB: 
    //mysql> select * from audits\G;
    //*************************** 1. row ***************************
    //category: updateInternal
    //file: lhc_web/ezcomponents/PersistentObject/src/handlers/save_handler.php
    //id: 1
    //line: 408
    //message: id_usuario: 1 UserName: lhc Object Updated: array (
    //  'id' => 36,
    //  'question' => 'teet',
    //  'answer' => ' es respuesta del bot',
    //  'context_id' => 1,
    //  'was_used' => 0,
    //  'confirmed' => 1,
    //  'answer_id' => '5c2392e17c4093455b4d780f',
    //  'name' => 'tetst feedback',
    //)
    //severity: Success audit
    //source: erLhcoreClassModelLHCChatBotQuestion
    //time: 2019-01-05 12:17:15
    //1 row in set (0.00 sec)
	static function write($msg, $level=ezcLog::WARNING, $attributes = array()) {
		// Use log
        static $logFileWriterAdded = false;

		$log = ezcLog::getInstance ();

        if (!in_array($level,[ezcLog::FAILED_AUDIT,ezcLog::SUCCESS_AUDIT]) && $logFileWriterAdded === false) {
            $logFileWriterAdded = true;
            self::addLogFileWrite($log);
        }

		$log->log ( $msg, $level, $attributes);
	}

    public static function addLogFileWrite($log) {
        #Rule for log write to a file
        #For INFO,NOTICE,WARNING,ERROR,FATAL and DEBUG severities;
        $filter = new ezcLogFilter();
        $filter->severity = ezcLog::INFO | ezcLog::NOTICE | ezcLog::WARNING | ezcLog::ERROR | ezcLog::FATAL | ezcLog::DEBUG;

        $cfg = erConfigClassLhConfig::getInstance();
        $defaultGroup = $cfg->getSetting( 'site', 'default_group', false );
        $defaultUser = $cfg->getSetting( 'site', 'default_user', false );

        $writeAll = new ezcLogUnixFileWriter ( "cache", "default.log",204800, 5, $defaultUser, $defaultGroup);

        $log->getMapper ()->appendRule ( new ezcLogFilterRule ( $filter, $writeAll, true ) );
    }

    //Set de write destination in relation to the level of message.
    // For INFO,NOTICE,WARNING,ERROR,FATAL and DEBUG severitis the 
    // default log location is CACHE/DEFAULT.LOG since it is writable Change below.
    //
    // For SUCCESS_AUDIT and FAILED_AUDIT the write destination is "audits" table in DB.
    // The "audits" table must be create before.
	public static function configureObject($log) {
		#Rule for log write to a table in db
		#For SUCCESS_AUDIT and FAILED_AUDIT severities;
		$filter_audit = new ezcLogFilter();
 		$filter_audit->severity = ezcLog::SUCCESS_AUDIT | ezcLog::FAILED_AUDIT;
		$db = ezcDbInstance::get();
		$log->getMapper()->appendRule( new ezcLogFilterRule( $filter_audit, new ezcLogDatabaseWriter( $db, "lh_audits" ), true ) );
	}

	public static function logObjectChange($params)
    {
        $className = str_replace(array('erLhcoreClassModel','erLhAbstractModel'),'',get_class($params['object'])) . ($params['action_class'] ?? '');

        if (isset($params['check_log']) && $params['check_log'] == true) {
            $auditOptions = erLhcoreClassModelChatConfig::fetch('audit_configuration');
            $data = (array)$auditOptions->data;
            if (!(isset($data['log_objects']) && is_array($data['log_objects']) && in_array($className,$data['log_objects']))){
                return;
            }
        }

        if (isset($params['action'])) {
            $className .= $params['action'];
        }

        $details = array(
            'source' => 'lhc',
            'category' => $className,
            'line' => __LINE__,
            'file' => __FILE__,
            'object_id' => (isset($params['object_id']) ? $params['object_id'] : $params['object']->id)
        );

        if (isset($params['user_id'])) {
            $details['user_id'] = $params['user_id'];
        }

        erLhcoreClassLog::write((is_string($params['msg']) || is_numeric($params['msg']) ? $params['msg'] : json_encode($params['msg'],JSON_PRETTY_PRINT)),
            ezcLog::SUCCESS_AUDIT,
            $details
        );
    }
}

ezcBaseInit::setCallback ( 'ezcInitLog', 'erLhcoreClassLog' );

?>
