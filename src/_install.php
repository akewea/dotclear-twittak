<?php
//@@licence@@

if (!defined('DC_CONTEXT_ADMIN')) { return; }
 
$m_version = $core->plugins->moduleInfo('twittak','version');
 
$i_version = $core->getVersion('twittak');
 
if (version_compare($i_version,$m_version,'>=')) {
	return;
}
 
# Création des setting (s'ils existent, ils ne seront pas écrasés)
$settings = new dcSettings($core,null);
$settings->addNameSpace('twittak');
$settings->twittak->put('twittak_base_url','http://twitter.com','string','Twitter base URL',false,true);
$settings->twittak->put('twittak_api_base_url','http://api.twitter.com/1','string','Twitter API base URL',false,true);
$settings->twittak->put('twittak_enabled',false,'boolean','Enable TwittAk tweets page',false,true);
$settings->twittak->put('twittak_show_twitter_link',true,'boolean','Show link to Twitter page',false,true);
$settings->twittak->put('twittak_account','','string','Twitter ID for TwittAk tweets page',false,true);
$settings->twittak->put('twittak_page_size',20,'integer','Page size for TwittAk tweets page',false,true);

$core->setVersion('twittak',$m_version);
?>