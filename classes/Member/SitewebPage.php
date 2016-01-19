<?php
/** Gestion des page de sites web. 
 * 
 * Exemple d'utilisation:
 * <code>
 * $query = 'SELECT *'
 * 			. ' FROM ' . DB_PREFIX . 'sitesweb_page'
 * 			. ' WHERE id=:pageId'
 * 			. ' LIMIT 1;';
 * $prep = $db->prepare($query);
 * $prep->bindValue(':pageId',	$this->first_page_id,	PDO::PARAM_INT);
 * $prep->execute($db, __FILE__, __LINE__);
 * $arr = $prep->fetch();
 * $prep->closeCursor();
 * $prep = NULL;
 *		
 * $this->first_page = new Member_SitewebPage($arr);
 * </code>
 *
 * @package Member
 * @subpackage SiteWeb
 */
class Member_SitewebPage
{
	
	/** Id de la page
	 * @var int
	 * @access private
	 */
	private $id;
	
	/** Id du site
	 * @var int
	 * @access private
	 */
	private $site_id;
	
	/** Contenu brute du site (BBCodes non formatés)
	 * @var string
	 * @access private
	 */
	private $contentRaw;
	
	/** Contenu HTML du site (BBCodes formatés)
	 * @var string
	 * @access private
	 */
	private $contentHTML;
	
	/** Titre du site
	 * @var string
	 * @access private
	 */
	private $titre;
	
	/** Si le site est accessible publiquement ou en privé ('pub', 'priv')
	 * @var string
	 * @access private
	 */
	private $acces;

	/** Si la page doit être affiché dans l'index
	 * @var bool
	 * @access private
	 */
	private $showIndex;
	
	/** Si la page est faite de BBCode (false) ou de HTML (true)
	 * @var bool
	 * @access private
	 */
	private $allowHTML;
	
	
	/** Charge un site en mémoire.
	 * 
	 * @param string &$arr Array des résultats de la requête SELECT *
	 */ 
	function __construct(&$arr)
	{
		//Charger les informations générale du site en mémoire
		$this->id 			= $arr['id'];
		$this->site_id 		= $arr['site_id'];
		$this->contentRaw 	= stripslashes($arr['content']);
		$this->titre		= stripslashes($arr['titre']);
		$this->acces		= $arr['acces'];
		$this->showIndex	= (isset($arr['showIndex']) && $arr['showIndex']=='1') ? true : false;
		$this->allowHTML	= (isset($arr['allowHTML']) && $arr['allowHTML']=='1') ? true : false;
		$this->contentHTML	= null;
	}
	
	
	/** Retourne le contenu HTML de la page (avec les BBCodes convertis en HTML).
	 * @return string
	 */
	public function getContentHTML(){
	
		if(!$this->allowHTML)
			$this->contentHTML = preg_replace(array('/</','/>/'), array('«','»'), $this->contentHTML);
		
		
		if(empty($this->contentHTML))
			$this->contentHTML = BBCodes($this->contentRaw, true);
			
		return $this->contentHTML;
	}
	
	/** Retourne l'Id de la page.
	 * @return int
	 */
	public function getId()			{	return $this->id; } 
	
	/** Retourne l'Id du site.
	 * @return int
	 */
	public function getSiteId()		{	return $this->site_id; } 
	
	/** Retourne le contenu brute de la page.
	 * @return string
	 */
	public function getContentRaw()	{	return $this->contentRaw; }
	
	/** Retourne le titre de la page.
	 * @return string
	 */
	public function getTitre()		{	return $this->titre; }
	
	/** Retourne si la page supporte le HTML.
	 * @return bool
	 */
	public function getAllowHTML()	{	return $this->allowHTML; }
	
	/** Retourne si la page doit être affiché dans l'index.
	 * @return string
	 */
	public function getShowIndex()	{	return $this->showIndex; }
	
	
	/** Retourne TRUE si la page est publique
	 * @return bool
	 */
	public function isPublic()		{	return (($this->acces=='pub') ? true : false); }
	
	
	
	/** Valide si un user/pass à accès ou non a la page
	 * <br > Fonctionne pour la page actuelle. Retournera TRUE ou FALSE
	 *
	 * Exemple d'utilisation
	 * <code>
	 * if (!$page->checkAcces($_POST['user'], $_POST['pass']))
	 * 	die ('ACCÈS REFUSÉ');
	 * </code>
	 * <br>
	 * 
	 * @param string $user le nom d'utilisateur
	 * @param string $pass le mot de passe
	 * @return int Retourne le tableau de l'access
	 */
	public function checkAcces($user, $pass)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		$query = 'SELECT pa.*'
				. ' FROM ' . DB_PREFIX . 'sitesweb_acces as a'
				. ' LEFT JOIN ' . DB_PREFIX . 'sitesweb_pages_acces as pa ON (pa.page_id=:pageId AND pa.user_id= a.id)'
				. ' WHERE	user =:user'
					. ' AND pass =:pass'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':pageId',		$this->id,		PDO::PARAM_INT);
		$prep->bindValue(':user',		$user,			PDO::PARAM_STR);
		$prep->bindValue(':pass',		$pass,			PDO::PARAM_STR);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

		
		if($arr===false)
			return false;
			
		if($arr['id']==null)
			return false;
		
		return $arr;
	}
	
}





