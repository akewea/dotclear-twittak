<?php
//@@licence@@

/**
 * Classe utilitaire pour Twittak.
 * 
 * @author akewea
 *
 */
class twittakUtils {
	
	/**
	 * Retourne les tweets du compte donné.
	 * 
	 * @param $twitterId le compte twitter
	 * @param $count le nombre de tweets à retourner
	 * @param $offset le décalage du premier tweet à retourner
	 * @param $cache_timeout la durée de mise en cache des tweets obtenus depuis le site twitter
	 * @return staticRecord la liste des tweets
	 */
	public static function getTweets($twitterId, $count=5, $offset=0, $cache_timeout=900){
				
		global $core;
		
		$cachedir = DC_TPL_CACHE."/twittak";
		$cachefile = $cachedir."/".$twitterId;
		
		// Création du répertoire de cache si besoin.
		if(!is_dir($cachedir)){
			try {
				mkdir($cachedir);
			} catch(Exception $e) { 
				throw "Unable to create TwittAk cache directory";					
			}
		}
		
		$xml = null;
		
		if(false && file_exists($cachefile) && @filemtime($cachefile) < (time() + $cache_timeout)){
			// Si fichier en cache et suffisamment récent : on utilise la version en cache
			$xml = self::getFromCache($cachefile);
		}else{
			// Sinon, on récupère la dernière versions et on la met en cache.
			$url = $core->blog->settings->twittak->twittak_api_base_url.'/statuses/user_timeline/'.$twitterId.'.xml';
						
			$content = self::getFromWeb($url);
			
			if ($xml = @simplexml_load_string($content))
			{
				if ($xml->error == '' && count($xml->status) > 0 && $fp = @fopen($cachefile, 'wb'))
				{
					fwrite($fp, $content);
					fclose($fp);
				}else{
					$xml = self::getFromCache($cachefile);
				}
			}else{
				$xml = self::getFromCache($cachefile);
			}
		}		
		
		// On renseigne le contexte
		if( $xml == null || count($xml->status) < 1 )
		{
			return staticRecord::newFromArray(array());
		}
		
		$tweets = array();
		$i = -1;
		foreach($xml->status as $elm) {
			
			$i++;
			
			if($i < $offset){
				continue;
			}
			
			if($i >= ($offset + $count)){
				break;
			}
			
			$tz = self::getTimeZone($elm->user->time_zone);
						
			$img = $elm->user->profile_image_url;
			$img_mini = preg_replace("@_normal.@i", "_mini.",$img);
			$time = ((int) strtotime($elm->created_at));
			$time += $tz->getOffset(new DateTime($elm->created_at, new DateTimeZone("UTC")));
			
			$tweets[] = array(	'id' => $elm->id,
								'name' => (string) $elm->user->name,
								'screen_name' => (string) $elm->user->screen_name,
								'location' => (string) $elm->user->location,
								'img' => $img,
								'img_mini' => $img_mini,
								'desc' => self::formatContent($elm->text),
								'time' => $time,
								'source' => $elm->source,
								'isReply' => ($elm->in_reply_to_status_id != ''),
								'replyId' => $elm->in_reply_to_status_id,
								'replyUser' => $elm->in_reply_to_screen_name
								);
								
		}
		
		return staticRecord::newFromArray($tweets);
	}	
	
	/**
	 * Retourne le flux XML des tweets depuis la version dans le cache.
	 * 
	 * @param $filepath string le chemin du fichier mis en cache
	 * @return object Représentation objet du flux XML
	 */
	private static function getFromCache($filepath){
		if(file_exists($filepath)){
			return @simplexml_load_string(file_get_contents($filepath));
		}		
	}
	
	/**
	 * Retourne le flux XML des tweets depuis le site twitter.
	 * 
	 * @param $url string L'URL du flux XML à télécharger
	 * @return object Représentation objet du flux XML
	 */
	private static function getFromWeb($url){
		
		$http = new netHttp('');
		//$http->setDebug(true);
		$http->readURL($url,$ssl,$host,$port,$path,$user,$pass);
		$http->setHost($host,$port);
		$http->useSSL($ssl);
		$http->setAuthorization($user,$pass);
		$http->setUserAgent("TwittAk plugin for Dotclear");
		
		$http->get($path);
		
		return $http->getContent();
	}
	
	/**
	 * Met en forme le contenu donné (URL en tant que lien, réponses, smiley, etc.)
	 * 
	 * @param $content string Le contenu à formatter
	 * @return string Le contenu formatté
	 */
	private static function formatContent($content){
		global $core;
		
		$res = $content;		
		// Remplacement des urls
		$res = preg_replace("/(http|mailto|news|ftp|https):\/\/(([-éa-z0-9\/\.\?_=#@:~])*)/i", "<a href=\"\\1://\\2\" target=\"_blank\">\\1://\\2</a>",$res);
		// Remplacement des réponses (@xxx) avec un lien vers le twitter en question
		$res = preg_replace("/^@([a-zA-Z0-9_^\s]+)/", "@<a href=\"".$core->blog->settings->twittak->twittak_base_url."/\\1\" target=\"_blank\">\\1</a>",$res);
		// Remplacement des smilies (en se basant sur ceux du thème).
		$res = self::smilies($res);
		
		return $res;
	}
	
	/**
	 * Retourne la timezone PHP correspondant à la timezone spécifié dans le tweet.
	 * @param $end_name string Le nom de la timezone telque spécifiée dans twitter.
	 * @return DateTimeZone UTC par défaut.
	 */
	private static function getTimeZone($end_name) {
		// TODO Ajouter une gestion de cache.
		foreach(DateTimeZone::listIdentifiers() as $tzName){
			if(substr($tzName, -strlen($end_name)) == $end_name){
				return new DateTimeZone($tzName);
			}
		}
		return new DateTimeZone("UTC");
	}	
	
	/**
	 * Ajoute les smilies au <code>$c</code>.
	 * 
	 * @param $c string Le contenu à modifier.
	 * @return string Le contenu modifié.
	 */
	private static function smilies($c)
	{
		global $core;
		if (!isset($GLOBALS['__smilies'])) {
			$GLOBALS['__smilies'] = context::getSmilies($core->blog);
		}
		return context::addSmilies($c);
	}
	
}
	
?>