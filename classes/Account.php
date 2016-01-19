<?php
/** Gestion du compte.
* <br> (cette classe est instanciée pour toutes les pages du site.
* Dans le cas ou l'utilisateur n'est pas authentifié, les options par défaut restent chargés et ne sont pas remplacé par les options personnelles chargée par loadAccount())
* 
* @package CyberCity_2034
*/
class Account
{
	private $user;
	private $userId;
	private $mjId;
	private $email;
	private $remoteSkin; //Nom du skin/Nom du dossier sur le serveur
	private $localSkin;  //Chemin local vers les images et les trucs lourd
	private $msgPerPage; //Nombre d'items par page
	private $memberLevel; //Niveau d'abonnement
	private $memberRemain; //Jours restants à l'abonnement
	private $sexe;
	private $logged; //Si le compte est authentifié
	
	
	/** Charge une base de compte en mémoire (options générales).
	* <br> Si la session est authentifiée, la classe Member_Account -qui est toujours déjà instanciée- sera chargée/rempli, et ce pour toutes les pages du site.
	* <br> (en ce qui concerne la classe Member_Perso, elle sera instanciée uniquement pour les pages la nécéssitant)
	*
	*/
	function __construct()
	{
		$this->userId		= NULL; //Par défaut null=visiteur
		$this->mjId			= NULL;
		$this->localSkin	= NULL; //Skin installé
		$this->remoteSkin	= SITE_DEFAULT_SKIN; //Skin sur le site
		
		if(!self::doSkinExist($this->remoteSkin))
		{
			echo fctErrorMSG(
				'Impossible d\'accéder au skin par défaut ( ' .
				SITE_PHYSICAL_PATH . 'tpl/' . $this->remoteSkin . '/ )' .
				'<br />Vérifiez SITE_DEFAULT_SKIN et SITE_PHYSICAL_PATH.'
			);
			die;
		}
	}
	
	/** Charger les options personnelles du compte en mémoire (écraser les options générales).
	* <br> Cette méthode est uniquement appelée si la session est authentifiée
	* 
	* @param int $userId Id du compte utilisateur à charger
	* @param string $errFile En cas d'erreur, permet d'afficher le fichier qui procède à l'appel
	* @param string $errLine en cas d'erreur, permet d'afficher la ligne en cause du fichier d'appel
	*/ 
	public function loadAccount($userId, $logged, $errFile, $errLine)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'SELECT a.*, mj.id as mjid'
					. ' FROM ' . DB_PREFIX . 'account as a'
					. ' LEFT JOIN ' . DB_PREFIX . 'mj as mj ON (mj.userId=a.id)'
					. ' WHERE a.id=' . (int)$userId . ';';
		$arr = $db->query($query,__FILE__,__LINE__)->fetch();
		
		
		$this->user			= $arr['user'];
		$this->userId		= (int)$arr['id'];
		$this->mjId			= $arr['mjid']; //Sera null si innexistant
		$this->email		= $arr['email'];
		$this->remoteSkin	= $arr['skin'];
		$this->localSkin	= $arr['skin_localpath'];
		$this->msgPerPage	= (int)$arr['heitems'];
		$this->memberLevel	= (int)$arr['mp'];
		$this->memberRemain	= (int)$arr['mp_expiration'];
		$this->sexe			= $arr['sexe'];
		$this->logged		= $logged===true;
		
		
		//Valider si le skin existe
		if(!self::doSkinExist($this->remoteSkin))
		{
			//Le skin essayé est introuvable, appliquer le skin par défaut
			echo fctErrorMSG('Le skin spécifié est invalide, le skin par défaut sera employé.
								<br /><a href="?v=Main">OK</a>');
			
			//Changer le skin pour celui par défaut
			$query = 'UPDATE ' . DB_PREFIX . 'account'
						. ' SET `skin`="' . SITE_DEFAULT_SKIN . '"'
						. ' WHERE id=' . (int)$this->getId()
						. ' LIMIT 1;';
			$db->query($query,__FILE__,__LINE__);
		}
		
	}
	
	
	//#########################
	// ## GESTION DU COMPTE
	//#########################
	
	
	/** Retourne l'Id du compte
	* @return int
	*/
	public function getId()
	{
		return $this->userId;
	}
	
	
	/** Retourne si l'utilisateur est authentifié ou non.
	* @return bool
	*/
	public function isLogged()
	{
		return $this->logged;
	}
	
	/** Retourne le nom d'utilisateur du compte.
	* @return string
	*/
	public function getUser()
	{
		return $this->user;
	} 
	
	/** Retourne l'adresse email du compte.
	* @return string
	*/
	public function getEmail()
	{
		return $this->email;
	} 
	
	/** Retourne l'ID du MJ (dans le cas ou le compte n'à aucun accès MJ, retournera NULL)
	* @return int
	*/	
	public function getMjId()
	{
		return $this->mjId;
	}
	
	/** Retourne le chemin virtuel (accessible par le navigateur) vers le skin utilisé.
	* Principalement utilisé pour les chemins d'accès aux images et au CSS
	* @return string
	*/
	public function getSkinRemoteVirtualPath()
	{
		return SITE_VIRTUAL_PATH . 'tpl/' . $this->remoteSkin . '/';
	}
	
	
	/**
	 * Retourne le chemin physique sur le serveur(accessible par le système du serveur) vers le skin utilisé.
	 * Principalement utilisé par la méthode Template::Fetch()
	 * @return string
	 */
	public function getSkinRemotePhysicalPath()
	{
		return SITE_PHYSICAL_PATH . 'tpl/' . $this->remoteSkin . '/';
	}	
	
	
	/**
	 * Retourne le chemin physique(accessible par le système de l'usagé) vers le skin utilisé.
	 * Principalement utilisé par les Skin installés sur les ordinateurs des membres.
	 * @return string
	 */
	public function getSkinLocalPhysicalPath()
	{
		return 'file:///' . $this->localSkin . '/';
	}
	
	
	
	/**
	 * Valider si un skin existe.
	 *
	 * @param string $skinName Nom du skin (et de son dossier dans tpl/)
	 * @return bool true si le skin existe
	 */
	private static function doSkinExist($skinName)
	{
		//Vérifier si la page principale sur le skin existe (si le skin en soit existe)
		return file_exists(SITE_PHYSICAL_PATH . 'tpl/' . $skinName . '/');
	}
	
	/**
	 * Valide si un skin est le skin par défaut.
	 *
	 * @param string $skinName Nom du skin (et de son dossier dans tpl/)
	 * @return bool true si le skin est celui par défaut
	 */
	private static function isDefaultSkin($skinName)
	{
		return SITE_DEFAULT_SKIN===$skinName;
	}
	

	
	
	/** Retourne le niveau d'abonnement du membre actuel.
	*
	* Exemple d'utilisation - Afficher le type d'abonnement "numérique" du membre actuel
	* <code>
	* //Voir getMemberLevelTxt() pour une méthode simplifiée
	* switch($account->getMemberLevel()){
	*	case 0: echo 'membre gratuit';  break;
	*	case 1: echo 'niveau 1';  break;
	*	case 2: echo 'niveau 2'; break;
	*	case 3: echo 'niveau 3'; break;
	* }
	* </code>
	*
	* @return int
	*/
	public function getMemberLevel()
	{
		return $this->memberLevel;
	}
	

	/** Retourne le temps restant à l'abonnement actuel en jour(s).
	* <br />Retournera &infin; si Membre gratuit sans expiration.
	*
	* Exemple d'utilisation - Afficher le temps restant
	* <code>
	* echo $account->getMemberRestant() . " jour(s)";
	* </code>
	*
	* @return string
	*/
	public function getMemberRestant()
	{
		if ($this->getMemberLevel()===0)
			return '&infin;';
		else
			return (string)floor(($this->memberRemain - time())/(60*60*24));
	}
	
	
	/** Retourne le niveau d'abonnement "texte" du membre actuel.
	*
	* Exemple d'utilisation - Afficher le type d'abonnement du membre actuel
	* <code>
	* echo $account->getMemberLevelTxt();
	* </code>
	*
	* @return string
	*/
	public function getMemberLevelTxt()
	{
		return self::convMemberLevelTxt($this->getMemberLevel());
	}
	
	/** Converti le niveau d'abonnement en format "texte"
	*
	* Exemple d'utilisation - Afficher le texte d'un abonnement MP-1
	* <code>
	* echo self::convMemberLevelTxt(1);
	* </code>
	*
	* @return string
	*/
	public static function convMemberLevelTxt($lvl)
	{
		switch($lvl)
		{
			case 0: return 'Membre gratuit';  break;
			case 1: return 'M+ Niveau 1';  break;
			case 2: return 'M+ Niveau 2'; break;
			case 3: return 'M+ Niveau 3'; break;
			default:
				throw new Exception('Niveau d\'abonnement non-reconnu. (' . $lvl . ')');
				return 'Membre gratuit';
				break;
		}
	}
	
	/** Retourne si le skin utilisé est en ligne (online) ou installé (offline).
	*
	* Exemple d'utilisation
	* <code>
	* echo $account->skinOnlineOffline();
	* </code>
	*
	* @return string
	*/
	public function skinOnlineOffline()
	{
		if(empty($this->localSkin))
			return 'online';
		return 'offline';
	}
	
	
	/** Retourne le sexe du joueur (et non du perso)
	* 'f' pour meuf, 'm' pour fornicateur
	*
	* Exemple d'utilisation
	* <code>
	* echo $account->getSexe();
	* </code>
	*
	* @return string
	*/
	public function getSexe()
	{
		return $this->sexe;
	}
	
	
	
	/**
	 * Retourne le nombre de message à afficher par page du HE
	 * @return int
	 */
	public function getMsgPerPage()
	{
		return $this->msgPerPage;
	}
	
}


