<?php
/** Gestion des page de sites web. 
 * 
 * Exemple d'utilisation:
 * <code>
 * $query = 'SELECT *' 
 * 			. ' FROM ' . DB_PREFIX . 'sitesweb_acces'
 * 			. ' WHERE id=:pageId'
 * 			. ' LIMIT 1;';
 * 	$prep = $db->prepare($query);
 *	$prep->bindValue(':pageId',	$this->first_page_id,	PDO::PARAM_INT);
 *	$prep->execute($db, __FILE__, __LINE__);
 *	$arr = $prep->fetch();
 *	$prep->closeCursor();
 *	$prep = NULL;
 *
 * $this->acces = new Member_SitewebAcces($arr);
 * </code>
 *
 * @package Member
 * @subpackage SiteWeb
 */
class Member_SitewebAcces
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
	
	/** User de l'accès
	 * @var string
	 * @access private
	 */
	private $user;
	
	/** Pass de l'accès (non crypté)
	 * @var string
	 * @access private
	 */
	private $pass;
	
	/** Droit accéder
	 * @var bool
	 * @access private
	 */
	private $accede;
	
	/** Droit de poster (nouvelle page)
	 * @var bool
	 * @access private
	 */
	private $poste;

	/** Droit en modification
	 * @var bool
	 * @access private
	 */
	private $modifier;
	
	/** Droit administrateur
	 * @var bool
	 * @access private
	 */
	private $admin;
	
	
	/** Charge un site en mémoire.
	 * 
	 * @param string &$arr Array des résultats de la requête SELECT *
	 */ 
	function __construct(&$arr)
	{
		//Charger les informations générale du site en mémoire
		$this->id 			= $arr['id'];
		$this->site_id 		= $arr['site_id'];
		$this->user		 	= stripslashes($arr['user']);
		$this->pass			= stripslashes($arr['pass']);
		$this->accede		= ($arr['accede']=='1') ? true : false;
		$this->poste		= ($arr['poste']=='1') ? true : false;
		$this->modifier		= ($arr['modifier']=='1') ? true : false;
		$this->admin		= ($arr['admin']=='1') ? true : false;
	}
	
	

	
	
	
	/** Retourne l'Id de la page.
	 * @return int
	 */
	public function getId()		{	return $this->id; } 
	
	/** Retourne l'Id du site.
	 * @return int
	 */
	public function getSiteId()	{	return $this->site_id; } 
	
	/** Retourne le user
	 * @return string
	 */
	public function getUser()	{	return $this->user; }
	
	/** Retourne le pass (non crypté)
	 * @return string
	 */
	public function getPass()	{	return $this->pass; }
	
	/** Retourne TRUE si l'accès à les droits d'accéder au site
	 * @return bool
	 */
	public function canAccede()	{	return $this->accede; }
	
	/** Retourne TRUE si l'accès à les droits de poster
	 * @return string
	 */
	public function canPoste()	{	return $this->poste; }
	
	/** Retourne TRUE si l'accès à les droits de modifier
	 * @return bool
	 */
	public function canModifier()	{	return $this->modifier; }
	
	/** Retourne TRUE si l'accès à les droits admin
	 * @return bool
	 */
	public function isAdmin()	{	return $this->admin; }
	
	
	
	
	
}

