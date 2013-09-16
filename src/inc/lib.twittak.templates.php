<?php
//@@licence@@

/**
 * Classe pour le traitement des balises de template.
 * 
 * @author akewea
 *
 */
class twittakTemplates
{
	
	// ##################### TEMPLATE ##########################
	
	/**
	 * Affiche l'URL de Twitter (telle que configurée pour le blog).
	 * @param $attr 
	 * @return string
	 */
	public static function TwitterBaseURL($attr){
		global $core;
		$f = $core->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f, '$core->blog->settings->twittak->twittak_base_url').'; ?>';
	}
	
	/**
	 * Affiche le nom du compte Twitter (dépend du contexte).
	 * 
	 * @param $attr
	 * @return string
	 */
	public static function TwitterAccount($attr){
		global $_ctx;
		global $core;
		$f = $core->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f, '$_ctx->twittakAccount').'; ?>';
	}
	
	/**
	 * Affiche l'adresse du flux RSS du compte Twitter.
	 * 
	 * @param $attr
	 * @return string
	 */
	public static function TwitterFeedURL($attr){
		global $_ctx;
		global $core;
		$f = $core->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f, '$core->blog->settings->twittak->twittak_api_base_url."/statuses/user_timeline/".$_ctx->twittakAccount.".rss"').'; ?>';
	}
	
	/**
	 * Retourne le titre du widget TwittAk (widget uniquement).
	 * 
	 * @param $attr
	 * @return string
	 */
	public static function TwitterTitle($attr){
		global $_ctx;
		global $core;
		$f = $core->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f, '$_ctx->twittakWidgetTitle').'; ?>';
	}
	
	/**
	 * Boucle sur les tweets dans le contexte.
	 * 
	 * @param $attr
	 * @param $content
	 * @return string
	 */
	public static function Tweets($attr,$content)
	{
		return 
		'<?php while ($_ctx->tweets->fetch()) : ?>'.$content.'<?php endwhile; '.
		'$_ctx->tweets = null; ?>';
	}
	
	/**
	 * Affiche le contenu de la balise unquement pour le premier tweet de la liste.
	 * 
	 * @param $attr
	 * @param $content
	 * @return string
	 */
	public static function TweetsHeader($attr,$content)
	{
		return '<?php if ($_ctx->tweets->isStart()) : ?>'.$content.'<?php endif; ?>';
	}
	
	/**
	 * Affiche le contenu de la balise unquement pour le dernier tweet de la liste.
	 * 
	 * @param $attr
	 * @param $content
	 * @return string
	 */
	public static function TweetsFooter($attr,$content)
	{
		return '<?php if ($_ctx->tweets->isEnd()) : ?>'.$content.'<?php endif; ?>';
	}
	
	/**
	 * Affiche le contenu en fonction du ou des tests demandés se rapportant à la liste des tweets dans le contexte.
	 *  
	 * @param $attr
	 * @param $content
	 * @return string
	 */
	public static function TweetsIf($attr,$content)
	{
		$if = array();
		
		$operator = isset($attr['operator']) ? self::getOperator($attr['operator']) : '&&';
		
		if (isset($attr['has_tweets'])) {
			$sign = (boolean) $attr['has_tweets'] ? '' : '!';
			$if[] = $sign.'($_ctx->tweets->count() > 0)';
		}
		
		if (isset($attr['show_twitter_link'])) {
			$sign = (boolean) $attr['show_twitter_link'] ? '' : '!';
			$if[] = $sign.'($_ctx->twittakShowTwitterLink)';
		}
		
		if (isset($attr['mine_only'])) {
			$sign = (boolean) $attr['mine_only'] ? '' : '!';
			// TODO Ajouter un paramètre de config pour permettre d'afficher les 2 types de tweets
			$if[] = $sign.'(true)';
		}
		
		if (isset($attr['show_tweets_page_link'])) {
			$sign = (boolean) $attr['show_tweets_page_link'] ? '' : '!';
			$if[] = $sign.'($_ctx->twittakShowTweetsPageLink)';
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	/**
	 * Affiche le contenu en fonction du ou des tests demandés, se rapportant à un tweet.
	 * 
	 * @param $attr
	 * @param $content
	 * @return string
	 */
	public static function TweetIf($attr,$content)
	{
		$if = array();
		
		$operator = isset($attr['operator']) ? self::getOperator($attr['operator']) : '&&';
		
		if (isset($attr['account'])) {
			$account = addslashes(trim($attr['account']));
			if (substr($account,0,1) == '!') {
				$account = substr($account,1);
				$if[] = '($_ctx->tweets->name != "'.$account.'")';
			} else {
				$if[] = '($_ctx->tweets->name == "'.$account.'")';
			}
		}
		
		if (isset($attr['is_mine'])) {
			$sign = (boolean) $attr['is_mine'] ? '' : '!';
			$if[] = $sign.'($_ctx->tweets->name == $_ctx->twittakAccount)';
		}
		
		if (isset($attr['reply'])) {
			$sign = (boolean) $attr['reply'] ? '' : '!';
			$if[] = $sign.'($_ctx->tweets->isReply)';
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	/**
	 * Affiche une valeur si le tweet correspond au compte twitter à partir duquel le tweet a été obtenu.
	 * 
	 * @param $attr
	 * @return string
	 */
	public static function TweetIfMe($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'me';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->tweets->name == $_ctx->twittakAccount) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	
	/**
	 * Affiche une valeur si le tweet est le premier de la liste.
	 * 
	 * @param $attr
	 * @return string
	 */
	public static function TweetIfFirst($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'first';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->tweets->index() == 0) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	/**
	 * Affiche une valeur si le rang du tweet dans la liste est impair.
	 * 
	 * @param $attr
	 * @return string
	 */
	public static function TweetIfOdd($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'odd';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if (($_ctx->tweets->index()+1)%2 == 1) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	/**
	 * Affiche le numéro (ID) du tweet.
	 * 
	 * @param $attr
	 * @return string
	 */
	public static function TweetId($attr){
		global $_ctx;
		global $core;
		$f = $core->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f, '$_ctx->tweet->id').'; ?>';
	}
	
	/**
	 * Affiche le nom du compte de l'auteur du tweet.
	 * 
	 * @param $attr
	 * @return string
	 */
	public static function TweetName($attr){
		global $_ctx;
		global $core;
		$f = $core->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f, '$_ctx->tweets->name').'; ?>';
	}
	
	/**
	 * Affiche le nom d'affichage de l'auteur du tweet.
	 * 
	 * @param $attr
	 * @return string
	 */
	public static function TweetScreenName($attr){
		global $_ctx;
		global $core;
		$f = $core->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f, '$_ctx->tweets->screen_name').'; ?>';
	}
	
	/**
	 * Affiche la location de l'auteur du tweet.
	 * 
	 * @param $attr
	 * @return string
	 */
	public static function TweetLocation($attr){
		global $_ctx;
		global $core;
		$f = $core->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f, '$_ctx->tweets->location').'; ?>';
	}
	
	/**
	 * Affiche le contenu du tweet.
	 * 
	 * @param $attr
	 * @return string
	 */
	public static function TweetContent($attr){
		global $_ctx;
		global $core;
		$f = $core->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f, '$_ctx->tweets->desc').'; ?>';
	}
	
	/**
	 * Affiche l'URL de l'image (avatar) du compte de l'auteur du tweet.
	 * 
	 * @param $attr
	 * @return string
	 */
	public static function TweetImage($attr){
		global $_ctx;
		global $core;
		
		$mini = !empty($attr['mini']) ? $attr['mini'] == true : false;
		
		$f = $core->tpl->getFilters($attr);
		if ($mini) {
			return '<?php echo '.sprintf($f, '$_ctx->tweets->img_mini').'; ?>';
		} else {
			return '<?php echo '.sprintf($f, '$_ctx->tweets->img').'; ?>';
		}
	}
	
	/**
	 * Affiche la source (le nom de l'application) qui a servi à poster le tweet.
	 * 
	 * @param $attr
	 * @return string
	 */
	public static function TweetSource($attr){
		global $_ctx;
		global $core;
		$f = $core->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f, '$_ctx->tweets->source').'; ?>';
	}
	
	/**
	 * Affiche la date du tweet.
	 * 
	 * @param $attr
	 * @return string
	 */
	public static function TweetDate($attr){
		global $core;
		$f = $core->tpl->getFilters($attr);
				
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}
		
		$iso8601 = !empty($attr['iso8601']);
		$rfc822 = !empty($attr['rfc822']);
		
		$f = $core->tpl->getFilters($attr);
		
		if ($rfc822) {
			return '<?php echo '.sprintf($f,"dt::rfc822(\$_ctx->tweets->time)").'; ?>';
		} elseif ($iso8601) {
			return '<?php echo '.sprintf($f,"dt::iso8601(\$_ctx->tweets->time)").'; ?>';
		} else if($format) {
			return '<?php echo '.sprintf($f,"dt::str('".$format."', \$_ctx->tweets->time)").'; ?>';
		} else {
			return '<?php echo '.sprintf($f,"dt::str(\$core->blog->settings->system->date_format, \$_ctx->tweets->time)").'; ?>';
		}
	}
	
	/**
	 * Affiche le nom du compte twitter auquel le tweet est une réponse.
	 * 
	 * @param $attr
	 * @return string
	 */
	public static function TweetReplyName($attr){
		global $_ctx;
		global $core;
		$f = $core->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f, '$_ctx->tweets->replyUser').'; ?>';
	}
	
	/**
	 * Affiche le numéro du tweet (ID) auquel le tweet courant est une réponse.
	 * 
	 * @param $attr
	 * @return string
	 */
	public static function TweetReplyId($attr){
		global $_ctx;
		global $core;
		$f = $core->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f, '$_ctx->tweets->replyId').'; ?>';
	}
	
	// ##################### PRIVATE ##########################
	
	/**
	 * Convertit un opérateur de template en opérateur PHP.
	 * 
	 * @param $op
	 * @return string '11' ou '||'
	 */
	private static function getOperator($op)
	{
		switch (strtolower($op))
		{
			case 'or':
			case '||':
				return '||';
			case 'and':
			case '&&':
			default:
				return '&&';
		}
	}
	
}
?>