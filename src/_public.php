<?php
//@@licence@@

if (!defined('DC_RC_PATH')) { return; }

require dirname(__FILE__).'/inc/lib.twittak.utils.php';
require dirname(__FILE__).'/inc/lib.twittak.templates.php';

// Pour utilisation avec la balise "tpl:Widget"
require dirname(__FILE__).'/_widgets.php';

if($core->blog->settings->twittak->twittak_enabled && $core->blog->settings->twittak->twittak_account != ''){
	$core->url->register('TwittAk','twittak','^tweets/(.*)$',array('twittakPublic','twittakUrl'));
}

// Enregistrement des nouvelles balises de template
$core->tpl->addValue('TwitterBaseURL',array('twittakTemplates','TwitterBaseURL'));
$core->tpl->addValue('TwitterAccount',array('twittakTemplates','TwitterAccount'));
$core->tpl->addValue('TwitterTitle',array('twittakTemplates','TwitterTitle'));
$core->tpl->addValue('TwitterFeedURL',array('twittakTemplates','TwitterFeedURL'));
$core->tpl->addValue('TweetId',array('twittakTemplates','TweetId'));
$core->tpl->addValue('TweetName',array('twittakTemplates','TweetName'));
$core->tpl->addValue('TweetScreenName',array('twittakTemplates','TweetScreenName'));
$core->tpl->addValue('TweetLocation',array('twittakTemplates','TweetLocation'));
$core->tpl->addValue('TweetContent',array('twittakTemplates','TweetContent'));
$core->tpl->addValue('TweetDate',array('twittakTemplates','TweetDate'));
$core->tpl->addValue('TweetImage',array('twittakTemplates','TweetImage'));
$core->tpl->addValue('TweetSource',array('twittakTemplates','TweetSource'));
$core->tpl->addValue('TweetReplyName',array('twittakTemplates','TweetReplyName'));
$core->tpl->addValue('TweetReplyId',array('twittakTemplates','TweetReplyId'));
$core->tpl->addBlock('Tweets',array('twittakTemplates','Tweets'));
$core->tpl->addBlock('TweetsHeader',array('twittakTemplates','TweetsHeader'));
$core->tpl->addBlock('TweetsFooter',array('twittakTemplates','TweetsFooter'));
$core->tpl->addBlock('TweetsIf',array('twittakTemplates','TweetsIf'));
$core->tpl->addBlock('TweetIf',array('twittakTemplates','TweetIf'));
$core->tpl->addValue('TweetIfMe',array('twittakTemplates','TweetIfMe'));
$core->tpl->addValue('TweetIfFirst',array('twittakTemplates','TweetIfFirst'));
$core->tpl->addValue('TweetIfOdd',array('twittakTemplates','TweetIfOdd'));

/**
 * Classe pour la gestion des éléments publiques du plugin (url, widget).
 * 
 * @author akewea
 *
 */
class twittakPublic extends dcUrlHandlers
{

	// ##################### PAGE ##########################
	
	/**
	 * Traitement de la page des tweets;
	 * 
	 * @param $argss
	 */
	public static function twittakUrl($args)
	{
		global $_ctx;
		global $core;
		
		$page_number = 0;
		$page_size = $core->blog->settings->twittak->twittak_page_size;
		
		$account = $core->blog->settings->twittak->twittak_account;
		
		$_ctx->tweets = twittakUtils::getTweets($account, $page_size, $page_size * $page_number);
		
		$_ctx->twittakAccount = $account;
		$_ctx->twittakShowTwitterLink = $core->blog->settings->twittak->twittak_show_twitter_link;
				
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
		self::serveDocument('tweets.html');
	}

	// ##################### WIDGET ##########################
	
	/**
	 * Traitement du widget TwittAk.
	 * 
	 * @param $w les paramètres du widget.
	 */
	public static function twittakWidget($w) {
	
		global $core;
		global $_ctx;
		
		//Affichage page d'accueil seulement
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		$account = (isset($w->account) && strlen($w->account) > 0) ? $w->account : $core->blog->settings->twittak->twittak_account;
		
		if ($account == ''){
			return;
		}
		
		if (isset($w->twitterlink)) {
			$p .= "\$params['post_type'] = '".addslashes($attr['post_type'])."';\n";
		}		
		
		$_ctx->tweets = twittakUtils::getTweets($account, $w->limit);
		
		$_ctx->twittakAccount = $account;
		$_ctx->twittakWidgetTitle = $w->title;
		$_ctx->twittakShowTwitterLink = $w->twitterlink;
		$_ctx->twittakShowTweetsPageLink = $w->tweetslink && $core->blog->settings->twittak->twittak_enabled;
		
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
		self::serveDocument('tweets-widget.html');
	}
	
}
?>