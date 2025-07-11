<?php

if (!isset($dashboardOrder)) {
    $dashboardOrder = json_decode(erLhcoreClassModelUserSetting::getSetting('dwo',''),true);
    if (!is_array($dashboardOrder)) {
        $dashboardOrder = json_decode(erLhcoreClassModelChatConfig::fetch('dashboard_order')->current_value,true);
    }
}

if (!is_array($dashboardOrder)) {
    $dashboardOrder = [
        [
            'pending_chats'
        ],
        [
            'active_chats'
        ]
    ];
}

$columnsTotal = count($dashboardOrder);
$columnSize = 12 / $columnsTotal;

?>
<div class="row" id="dashboard-body">

    <?php foreach ($dashboardOrder as $widgets) : ?>
        <div class="col-md-<?php echo $columnSize+2?> col-lg-<?php echo $columnSize?> sortable-column-dashboard">
            <?php foreach ($widgets as $wiget) : ?>
                <?php if ($wiget == 'online_operators') : ?>
                 
                     <?php if ($canListOnlineUsers == true || $canListOnlineUsersAll == true) : ?>
                        <?php include(erLhcoreClassDesign::designtpl('lhfront/dashboard/panels/online_operators.tpl.php'));?>
                     <?php endif;?>
                 
                <?php elseif ($wiget == 'active_chats') : ?>
                
                     <?php if ($activeTabEnabled == true && $online_chat_enabled_pre == true) : ?>
                        <?php include(erLhcoreClassDesign::designtpl('lhfront/dashboard/panels/active_chats.tpl.php'));?>
                     <?php endif;?>
                     
                <?php elseif ($wiget == 'online_visitors') : ?>
                
                     <?php if ($online_visitors_enabled_pre == true && $currentUser->hasAccessTo('lhchat', 'use_onlineusers') == true) : ?>
                        <?php include(erLhcoreClassDesign::designtpl('lhfront/dashboard/panels/online_visitors.tpl.php'));?>
                     <?php endif;?>
                    
                <?php elseif ($wiget == 'departments_stats') : ?>
                
                    <?php if ($online_chat_enabled_pre == true && $canseedepartmentstats == true) : ?>
                        <?php include(erLhcoreClassDesign::designtpl('lhfront/dashboard/panels/departments_stats.tpl.php'));?>
                    <?php endif;?>
                    
                <?php elseif ($wiget == 'pending_chats') : ?>
                
                    <?php if ($pendingTabEnabled == true && $online_chat_enabled_pre == true) : ?>
                        <?php include(erLhcoreClassDesign::designtpl('lhfront/dashboard/panels/pending_chats.tpl.php'));?>
                    <?php endif;?>

                <?php elseif ($wiget == 'bot_chats') : $idPanelElementSet = true;?>

                    <?php if ($botTabEnabled == true) : ?>
                            <?php include(erLhcoreClassDesign::designtpl('lhfront/dashboard/panels/bot_chats.tpl.php'));?>
                    <?php endif;?>

                <?php elseif ($wiget == 'subject_chats' && $currentUser->hasAccessTo('lhchat', 'subject_chats') == true) : ?>

                        <?php include(erLhcoreClassDesign::designtpl('lhfront/dashboard/panels/subject_chats.tpl.php'));?>

                <?php elseif ($wiget == 'group_chats') : ?>

                        <?php include(erLhcoreClassDesign::designtpl('lhfront/dashboard/panels/group_chat.tpl.php'));?>

                <?php elseif ($wiget == 'unread_chats') : ?>
                
                    <?php if ($unreadTabEnabled == true && $online_chat_enabled_pre == true) : ?>
                        <?php include(erLhcoreClassDesign::designtpl('lhfront/dashboard/panels/unread_chats.tpl.php'));?>
                    <?php endif;?>
                    
                <?php elseif ($wiget == 'transfered_chats') : ?>
                
                    <?php include(erLhcoreClassDesign::designtpl('lhchat/lists_panels/transfer_panel_container_pre.tpl.php'));?>
            
                    <?php if ($transfer_panel_container_pre_enabled == true) : ?>
                            <?php include(erLhcoreClassDesign::designtpl('lhfront/dashboard/panels/transfered_chats.tpl.php'));?>
                    <?php endif;?>
                    
                <?php elseif ($wiget == 'my_chats') : ?>
                  
                    <?php if ($mchatsTabEnabled == true) : $idPanelElementSet = true;?>
                        <?php include(erLhcoreClassDesign::designtpl('lhfront/dashboard/panels/my_chats.tpl.php'));?>
                    <?php endif;?>

                <?php elseif ($wiget == 'my_mails') : ?>
                        <?php include(erLhcoreClassDesign::designtpl('lhfront/dashboard/panels/my_mails.tpl.php'));?>
                <?php elseif ($wiget == 'pmails' && erLhcoreClassUser::instance()->hasAccessTo('lhmailconv', 'use_pmailsw')) : ?>
                        <?php include(erLhcoreClassDesign::designtpl('lhfront/dashboard/panels/pmails.tpl.php'));?>
                <?php elseif ($wiget == 'amails') : ?>
                        <?php include(erLhcoreClassDesign::designtpl('lhfront/dashboard/panels/amails.tpl.php'));?>
                <?php elseif ($wiget == 'malarms') : ?>
                        <?php include(erLhcoreClassDesign::designtpl('lhfront/dashboard/panels/malarms.tpl.php'));?>
                <?php else : ?>
                    <?php include(erLhcoreClassDesign::designtpl('lhfront/dashboard/panels/extension_panel_multiinclude.tpl.php'));?>
                <?php endif;?>
            <?php endforeach;?>           
            
        </div>
     <?php endforeach;?>
</div>
<?php $popoverInitialized = true; ?>
