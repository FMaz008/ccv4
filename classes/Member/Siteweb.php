<?php
/** Gestion des sites web. 
 * 
 * Exemple d'utilisation:
 * <code>
 * $site = Member_Siteweb::loadSite("monsite.com");
 * echo "Bienvenu sur le site: " . $site->getTitre();
 * </code>
 *
 * @package Member
 * @subpackage SiteWeb
 */
class Member_Siteweb
{

	/** Id du site
	 * @var int
	 * @access private
	 */
	private $id;
	
	/** URL (adresse) du site
	 * @var string
	 * @access private
	 */
	private $url;
	
	/** Titre du site
	 * @var string
	 * @access private
	 */
	private $titre;
	
	/** Page à afficher en premier (avec/en plus de l'index)
	 * @var object
	 * @access private
	 */
	private $first_page;
	
	/** Si le site est accessible publiquement ou en privé ('pub', 'priv')
	 * @var string
	 * @access private
	 */
	private $acces;
	
	/** Array d'objet des pages contenu dans le site
	 * @var array
	 * @access private
	 */
	private $arrPages;

	/** Tableau des accès utilisateurs
	 * @var array
	 * @access private
	 */
	private $arrAcces;
	
	
	
	/** Charge un site en mémoire.
	 * <br> UTILISER LA MÉTHODE {@link loadSite()} QUI VALIDE L'URL.
	 * 
	 * @param string &$arr Array des résultats de la requête SELECT *
	 */ 
	function __construct(&$arr)
	{
		//Charger les informations générale du site en mémoire
		$this->id 			= $arr['id'];
		$this->url 			= $arr['url'];
		$this->titre		= stripslashes($arr['titre']);
		$this->first_page_id= $arr['first_page'];
		$this->acces		= $arr['acces'];
		$this->first_page	= null; //Sera chargé sur demande seulement
		$this->arrPages		= null; //Sera chargé sur demande seulement
	}
	
	/** Valide si l'url existe, si oui, charge le lieu.
	 * <br> Retournera l'objet du site si existant, sinon FALSE.
	 * 
	 * @param string $url URL du site à charger
	 * @return object
	 */ 
	public static function loadSite($url)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'SELECT * '
				. ' FROM ' . DB_PREFIX . 'sitesweb'
				. ' WHERE `url`=:url'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':url',		$url,		PDO::PARAM_STR);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

		
		//Si innexistant, retourner false
		if($arr === false)
			return false;
		
		//Si l'URL est existante, créer puis retourner l'objet lieu
		return new Member_Siteweb($arr);
	}
	
	
	
	/** Retourne l'objet la première page à afficher avec l'index s'il y en a une. Sinon FALSE.
	 * @return object
	 */
	public function getFirstPage()
	{
		
		if ($this->first_page_id == 0)
			return false;
		
		if (empty($this->first_page))
		{
			//Charger la page
			$i=0;
			while( $page = $this->getPage($i++))
				if($page->getId() == $this->first_page_id)
					$this->first_page = $page;
		}
		
		return $this->first_page;
	}
	

	/** Charge et retourne les pages du site.
	 * <br > Fonctionne pour le site actuel. Retournera les informations sur les pages.
	 *
	 * Exemple d'utilisation - Afficher les pages d'un site
	 * <code>
	 * $i=0; $e=0;
	 * while( $page = $site->getPage($i++))
	 *	$arrPages[$e++] = $page;
	 * </code>
	 * <br>
	 * 
	 * @param int $id Indexe de la page du site(dans le tableau) à retourner
	 * @return object Retourne un objet de type Member_SitewebPage
	 */
	public function getPage($id)
	{ 
		
		//Vérifier si les pages sont chargés
		if (empty($this->arrPages))
		{
			$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
			$db = $dbMgr->getConn('game'); //Demander la connexion existante
			
			//Lister les pages du site
			$query = 'SELECT *'
					. ' FROM ' . DB_PREFIX . 'sitesweb_pages'
					. ' WHERE site_id=:siteId'
					. ' ORDER BY `titre`;';
			$prep = $db->prepare($query);
			$prep->bindValue(':siteId',		$this->id,	PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$arrAll = $prep->fetchAll();
			$prep->closeCursor();
			$prep = NULL;
			
			
			
			foreach($arrAll as &$arr)
				$this->arrPages[] = new Member_SitewebPage($arr);
			
		}
		
		//Vérifier si l'index de la page demandé est hors de la liste existante
		if (!is_numeric($id) || $id>=count($this->arrPages))
			return false;
		
		return $this->arrPages[$id];
	}
	
	
	
	/** Vide le tableau des accès, permet de rafraichir les accès si fait juste avant un getAcces()
	 */
	public function clearAcces()
	{
		$this->arrAcces = null;
	}
	
	
	/** Charge et retourne les accès au site
	 * <br > Fonctionne pour le site actuel. Retournera les informations sur l'accès
	 *
	 * Exemple d'utilisation - Afficher les accès d'un site
	 * <code>
	 * $i=0;
	 * while( $ax = $site->getAcces($i++))
	 *	$arrAcces[] = $ax;
	 * </code>
	 * <br>
	 * 
	 * @param int $id Indexe de l'acces du site(dans le tableau) à retourner
	 * @return object Retourne un objet de type Member_SitewebAcces
	 */
	public function getAcces($id)
	{
		
		//Vérifier si les items sont chargés
		if (empty($this->arrAcces))
		{
			$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
			$db = $dbMgr->getConn('game'); //Demander la connexion existante
			
			//Lister les page du site
			$query = 'SELECT *'
					. ' FROM ' . DB_PREFIX . 'sitesweb_acces'
					. ' WHERE	site_id=:siteId;';
			$prep = $db->prepare($query);
			$prep->bindValue(':siteId',		$this->id,	PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$arrAll = $prep->fetchAll();
			$prep->closeCursor();
			$prep = NULL;

			
			foreach($arrAll as &$arr)
				$this->arrAcces[] = new Member_SitewebAcces($arr);
				
		}
		
		//Vérifier si l'index de l'item demandé est hors de la liste existante
		if (!is_numeric($id) || $id>=count($this->arrAcces) || $id<0)
			return false;
		
		return $this->arrAcces[$id];
	}
	
	
	
	/** Valide si un user/pass à accès ou non au site
	 * <br > Fonctionne pour le site actuel. Retournera TRUE ou FALSE
	 *
	 * Exemple d'utilisation - Afficher les pages d'un site
	 * <code>
	 * if (!$site->checkAcces($_POST['user'], $_POST['pass']))
	 * 	die ('ACCÈS REFUSÉ');
	 * </code>
	 * <br>
	 * 
	 * @param string $user le nom d'utilisateur
	 * @param string $pass le mot de passe
	 * @return object Retourne un objet de type Member_SitewebAcces ou FALSE si l'accès n'existe pas
	 */
	public function checkAcces($user, $pass)
	{
		$i=0; $e=0;
		while( $ax = $this->getAcces($i++))
			if($ax->getUser() == $user && $ax->getPass() == $pass)
				return $ax;
		
		return false;
	}
	
	
	
	
	/** Retourne l'Id du site.
	 * @return int
	 */
	public function getId()		{	return $this->id; } 
	
	/** Retourne l'URL du site.
	 * @return string
	 */
	public function getUrl()	{	return $this->url; }
	
	/** Retourne le titre du site.
	 * @return string
	 */
	public function getTitre()	{	return $this->titre; }
	
	/** Retourne l'Id de la première page à afficher avec l'index.
	 * @return int
	 */
	public function getFirstPageId()	{	return $this->first_page_id; }
	
	/** Retourne TRUE si le site est publique
	 * @return bool
	 */
	public function isPublic()	{	return (($this->acces=='pub') ? true : false); }
	
	
}

