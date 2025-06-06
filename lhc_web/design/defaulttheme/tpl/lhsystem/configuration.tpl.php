<div class="row">
    <div class="col-md-6" id="header-system-configuration">
        <h1><?php include(erLhcoreClassDesign::designtpl('lhsystem/configuration_titles/configuration_title.tpl.php'));?></h1>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <div class="input-group">
                <span class="input-group-text"><i class="material-icons me-0">search</i></span>
                <input type="text" id="configuration-search" class="form-control form-control-sm" placeholder="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','Search for configuration options...');?>">
            </div>
        </div>
    </div>
</div>


<?php $currentUser = erLhcoreClassUser::instance(); ?>



<div role="tabpanel" id="system-tabs">

	<ul class="nav nav-tabs mb-3" role="tablist">
		<li role="presentation" class="nav-item"><a href="#system" class="nav-link active" aria-controls="system" role="tab" data-bs-toggle="tab"><?php include(erLhcoreClassDesign::designtpl('lhsystem/configuration_titles/system_title.tpl.php'));?></a></li>
        
        <?php include(erLhcoreClassDesign::designtpl('lhsystem/configuration_tabs/generate_js.tpl.php'));?>
        
        <?php include(erLhcoreClassDesign::designtpl('lhsystem/configuration_tabs/chat.tpl.php'));?>
         
        <?php include(erLhcoreClassDesign::designtpl('lhsystem/configuration_tabs/speech.tpl.php'));?>

        <?php include(erLhcoreClassDesign::designtpl('lhsystem/configuration_tabs/mailconv.tpl.php'));?>

        <?php include(erLhcoreClassDesign::designtpl('lhsystem/configuration_tabs/tab_multiinclude.tpl.php'));?>
	</ul>

	<div class="tab-content">
		<div role="tabpanel" class="tab-pane active" id="system">

			<div class="row">
				<div class="col-md-6">

					<h5><?php include(erLhcoreClassDesign::designtpl('lhsystem/configuration_titles/system_title.tpl.php'));?></h5>

					<ul>
        	      		<?php if ($currentUser->hasAccessTo('lhsystem','timezone')) : ?>
        			    <li><a href="<?php echo erLhcoreClassDesign::baseurl('system/timezone')?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','Time zone settings');?></a></li>
        			    <?php endif; ?>
        			    
        			    <?php include(erLhcoreClassDesign::designtpl('lhsystem/configuration_links/performupdate.tpl.php'));?>
        			    
        			    <?php include(erLhcoreClassDesign::designtpl('lhsystem/configuration_links/configuresmtp.tpl.php'));?>

                        <?php include(erLhcoreClassDesign::designtpl('lhsystem/configuration_links/administrategeoconfig.tpl.php'));?>

                        <?php include(erLhcoreClassDesign::designtpl('lhsystem/configuration_links/syncandsoundesetting.tpl.php'));?>

        			    <?php if ($currentUser->hasAccessTo('lhabstract','use')) : ?>		    
        				    <?php if ($currentUser->hasAccessTo('lhsystem','changetemplates')) : ?>
        				    <li><a href="<?php echo erLhcoreClassDesign::baseurl('abstract/list')?>/EmailTemplate"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','E-mail templates');?></a></li>
        				    <?php endif; ?>			    
        			    <?php endif;?>
        		        
        		        <?php include(erLhcoreClassDesign::designtpl('lhsystem/configuration_links/languages.tpl.php'));?>

        		        <?php include(erLhcoreClassDesign::designtpl('lhsystem/configuration_links/recaptcha.tpl.php'));?>

        		        <?php include(erLhcoreClassDesign::designtpl('lhsystem/configuration_links/notice.tpl.php'));?>

        		        <?php include(erLhcoreClassDesign::designtpl('lhsystem/configuration_links/expirecache.tpl.php'));?>
        			</ul>

                    <?php if ($currentUser->hasAccessTo('lhabstract','use') && $currentUser->hasAccessTo('lhsystem','auditlog')) : ?>
                            <h5>Audit</h5>
                            <ul>
                                <?php include(erLhcoreClassDesign::designtpl('lhsystem/configuration_links/audit_log.tpl.php'));?>
                            </ul>
                    <?php endif; ?>

				</div>

                <div class="col-md-6">
                    <?php include(erLhcoreClassDesign::designtpl('lhsystem/configuration_links/mobile.tpl.php'));?>

                    <?php include(erLhcoreClassDesign::designtpl('lhsystem/configuration_links/users_section.tpl.php'));?>
                </div>

			</div>

		</div>
    
    <?php include(erLhcoreClassDesign::designtpl('lhsystem/configuration_tabs_content/chat.tpl.php'));?>
    
    <?php include(erLhcoreClassDesign::designtpl('lhsystem/configuration_tabs_content/chat_embed_js.tpl.php'));?>
    
    <?php include(erLhcoreClassDesign::designtpl('lhsystem/configuration_tabs_content/speech.tpl.php'));?>

    <?php include(erLhcoreClassDesign::designtpl('lhsystem/configuration_tabs_content/mailconv.tpl.php'));?>

    <?php include(erLhcoreClassDesign::designtpl('lhsystem/configuration_tabs_content/tab_content_multiinclude.tpl.php'));?>
     
    </div>
</div>
<script>

</script>
