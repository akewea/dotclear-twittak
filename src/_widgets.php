<?php
//@@licence@@

if (!defined('DC_RC_PATH')) { return; }

// Enregistrement des behaviors pour l'initialisation du widget.
$core->addBehavior('initWidgets',array('TwittAkWidgets','initWidgets'));

/**
 * Classe pour l'initialisation du widget.
 * 
 * @author akewea
 *
 */
class TwittAkWidgets
{
	
	/**
	 * Initialisation du widget (paramètres à saisir et valeurs par défaut).
	 * 
	 * @param $w
	 */
	public static function initWidgets($w)
	{
		global $core;
		
		$w->create('twittak',__('TwittAk'),array('twittakPublic','twittakWidget'));
		$w->twittak->setting('account', __('Account ID:'), $core->blog->settings->twittak->twittak_account);
		$w->twittak->setting('title', __('Title:'), __('My last tweets'));
		$w->twittak->setting('limit', __('Limit (empty means no limit):'), 5);
		$w->twittak->setting('homeonly', __('Home page only'), 0, 'check');
		$w->twittak->setting('twitterlink', __('Link to Twitter page'), $core->blog->settings->twittak->twittak_show_twitter_link, 'check');
		$w->twittak->setting('tweetslink', __('Link to internal tweets page'), $core->blog->settings->twittak->twittak_enabled, 'check');
	}
}




?>