<?php
//@@licence@@

if (!defined('DC_RC_PATH')) { return; }

require dirname(__FILE__).'/_widgets.php';

// Enregistrement des behaviors pour l'admin.
$core->addBehavior('adminBlogPreferencesForm',array('twittakAdminBehaviors','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('twittakAdminBehaviors','adminBeforeBlogSettingsUpdate'));

/**
 * Classe pour la gestion de Behaviors de l'administation.
 * Ajoute le formulaire "twittak" à la page des préférence du blog.
 *
 * @author akewea
 *
 */
class twittakAdminBehaviors
{

	/**
	 * Affichage du formulaire "twittak" sur la page des préférences du blog.
	 *
	 * @param $core
	 * @param $settings
	 */
	public static function adminBlogPreferencesForm($core,$settings)
	{
		echo
		'<fieldset><legend>TwittAk</legend>'.
		'<div class="two-cols">'.
		'<div class="col">'.

		'<p><label class="classic">'.
		form::checkbox('twittak_enabled','1',$settings->twittak->twittak_enabled).
		__('Enable TwittAk tweets page').'</label></p>'.

		'<p><label class="classic">'.
		form::checkbox('twittak_show_twitter_link','1',$settings->twittak->twittak_show_twitter_link).
		__('Show link to Twitter page').'</label></p>'.

		'</div>'.

		'<div class="col">'.

		'<p><label class="classic">'.__('Twitter ID for TwittAk tweets page').' '.
		form::field('twittak_account', 10, 256, $settings->twittak->twittak_account).
		'</label></p>'.

		'<p><label class="classic">'.__('Page size for TwittAk tweets page').' '.
		form::field('twittak_page_size', 3, 3, $settings->twittak->twittak_page_size).
		'</label></p>'.

		'</fieldset>';
	}

	/**
	 * Enregistrement des settings correspondants au formulaire "twittak" sur la page des préférences du blog.
	 *
	 * @param $settings
	 */
	public static function adminBeforeBlogSettingsUpdate($settings)
	{
		$settings->addNameSpace('twittak');
		$settings->twittak->put('twittak_enabled',!empty($_POST['twittak_enabled']),'boolean');
		$settings->twittak->put('twittak_account',$_POST['twittak_account']);
		$settings->twittak->put('twittak_show_twitter_link',!empty($_POST['twittak_show_twitter_link']),'boolean');
		$settings->twittak->put('twittak_page_size',(integer)$_POST['twittak_page_size']);
	}
}
?>