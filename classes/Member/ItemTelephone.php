<?Php
/** Gestion des Téléphones Portables
 *
 * @package Member
 * @subpackage Item
 */

class Member_ItemTelephone extends Member_Item
{

	private $notel;
	private $nobanque;
	private $nocompte;
	private $nip;
	private $typeAffichage;
	private $anonymeCapable;
	private $prixAppel;
	private $memory;	
	private $memorySize;
	
	
	function __construct (&$arr)
	{
		parent::__construct($arr);

		
		$this->nobanque			= $arr['inv_nobanque'];
		$this->nocompte			= $arr['inv_nocompte'];
		$this->nip				= $arr['inv_nip'];
		$this->typeAffichage	= $arr['db_afficheur'];
		$this->anonymeCapable	= $arr['db_anonyme']=='1' ? true : false;
		$this->prixAppel		= $arr['db_param'];		
		$this->memory			= $arr['inv_memoiretext'];
		$this->memorySize		= $arr['db_memoire'];
		
		// Pour éviter les doublons de numéro
		if($this->qte == 1)
		{
			if(empty($arr['inv_notel']))
			{
				$this->genNoTel();
			}
			else
			{
				$this->notel	= $arr['inv_notel'];
			}
		}
	}
	
	/** Retourne le contenu brute de la mémoire
	 * @return string
	 */	
	public function getMemory()			{ return $this->memory; }			/** Retourne la possibilité qu'a le téléphone de pouvoir passer des appels		*<br />0 = pas possible		*<br />1 = possible		*@return int		*/		public function getCanCall()		{ 		if(($this->getNocompte() == null) && ($this->getNobanque() == null))	{		return 0;	}	else	{		return 1;	}		}
	
	/** Retourne la taille utilisée de la mémoire (en octet/chr)
	 * @return int
	 */	
	public function getMemorySize()
	{
		$arr = explode(',', $this->memory);
		$c=0;
		foreach($arr as $entry)
			if(!empty($entry))
				$c++;	
		return $c;
	}
	
	/** Retourne la taille maximale de la mémoire (en octet/chr)
	 * @return int
	 */	
	public function getMemorySizeMax()		{ return $this->memorySize; }
	
	/** Retourne le numéro de téléphone au format XXX-XXXX
	 * @return string
	 */
	public function getNotel()			{ return $this->notel; }
	
	/** Retourne le # de la banque qui possède le compte auquel le téléphone est associé
	 * @return int
	 */
	public function getNobanque()		{ return $this->nobanque; }
	
	/** Retourne le # du compte auquel le téléphone est associé au format XXXX-XXXX-XXXX
	 * @return string
	 */
	public function getNocompte()		{ return $this->nocompte; }
	
	/** Retourne le NIP/code associé au compte bancaire
	 * @return int
	 */ 
	public function getNip()			{ return $this->nip; }
	
	/** Retourne le type d'affichage
	 * - 0 = aucun affiche de numéro de téléphone
	 * - 1 = affichage du numéro (si le téléphone n'a pas caché le numéro)
	 * - 2 = affichage du numéro dans tous les cas même si le numéro a été caché
	 * @return int
	 */
	public function getTypeAffichage()	{ return $this->typeAffichage; } 	
	
	/** Retourne TRUE si le téléphone peut cacher son numéro
	 * @return bool
	 */	
	public function getAnonymeCapable()	{ return $this->anonymeCapable; }
	
	/** Retourne le cout de l'appel (Un double/int stocké dans db_param... qui est un champ text !?)
	 * @return string
	 */ 	
	public function getPrixAppel()		{ return $this->prixAppel; } 
	

	/** Retourne le groupe affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getGroup()			{ return 'Électronique'; }
	
	/** Retourne le groupe (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getGroupTech()		{ return 'electronique'; }
	
	/** Retourne le type affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getType()			{ return 'Téléphone'; }
	
	/** Retourne le type (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getTypeTech()		{ return 'telephone'; }
	
	
	/** Envoie un message à un téléphone
	 * @param string $callTo Numéro auquel le coup de téléphone est passé au format XXX-XXXX
	 * @param string $message Message à envoyer
	 * @param bool $anonyme Si l'appel est envoyé de facon anonyme ou non
	 */
	public function envoyerMessage ($callTo, $message, $anonyme)
	{ 
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		//Récup des info du téléphone du destinataire
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'item_inv'
				. ' LEFT JOIN ' . DB_PREFIX . 'item_db ON (db_id = inv_dbid)'
				. ' WHERE inv_notel=:noTel'
					. ' AND db_type="telephone"'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':noTel',	$callTo,				PDO::PARAM_STR);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		
		//Valider si le téléphone existe
		if($arr === false)
			throw new GameException('Le numéro que vous avez composé n\'est pas en service.');
		
		
		//Instancier le téléphone
		$tel_distant = new Member_ItemTelephone($arr);
		
		
		
		//Récup des info sur le compte en banque de l'appeleur		
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'banque_comptes'
				. ' WHERE compte_banque=:banque'
					. ' AND compte_compte=:compte'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':banque',	$this->getNobanque(),	PDO::PARAM_STR);
		$prep->bindValue(':compte',	$this->getNocompte(),	PDO::PARAM_STR);
		$prep->execute($db, __FILE__,__LINE__);
		$arrBanque = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		//Valider si le compte bancaire existe
		if($arrBanque === false)
			throw new GameException('Le numéro que vous avez composé n\'est pas en service (compte introuvable)');
		
		
		//Instancier le compte bancaire
		$compteAppeleur = new Member_BanqueCompte ($arrBanque);		
		
		
		
		//Valider si le mode anonyme est demandé & possible
		if($anonyme && $tel_distant->getAnonymeCapable())
			throw new GameException('Mode anonyme impossible avec cet appareil.');
		
		
		
		//destinataire n'a pas d'afficheur
		if($tel_distant->getTypeAffichage() == 0)
			$entete_message_dest = 'Votre téléphone ne possède pas d\'afficheur. Appel reçu sur le téléphone #[i]' . $callTo . '[/i]';
		
		//Appel en mode anonyme et destinataire téléphone normal ou afficheur simple donc numéro caché
		elseif($tel_distant->getTypeAffichage() != 2 && $this->getAnonymeCapable())
			$entete_message_dest = 'Le numéro de votre interlocuteur est caché. Appel reçu sur le téléphone #[i]' . $callTo . '[/i]';
		
		//Appel en mode anonyme mais le destinataire à un afficheur priviligé donc affichage du numéro
		elseif($tel_distant->getTypeAffichage() == 2 && $this->getAnonymeCapable())
			$entete_message_dest = 'Appel provenant du #[i]' . $this->getNotel() . '[/i]. Appel reçu sur le téléphone #[i]' . $callTo . '[/i]';
		
		//appel non anonyme et le destinataire à 1 des 2 afficheurs donc numéro affiché
		elseif($tel_distant->getTypeAffichage() != 0 && !$this->getAnonymeCapable())
			$entete_message_dest = 'Appel provenant du #[i]' .  $this->getNotel() . '[/i]. Appel reçu sur le téléphone #[i]' . $callTo . '[/i]';
		
		
		
		
		//Si le compte de l'expéditeur contient moins que le cout de l'appel ou si aucun compte n'est associé  au téléphone
		if($compteAppeleur->getcash() < $this->getPrixAppel() || $this->getNotel() == 0)
			throw new GameException('Vous n\'avez pas assez de crédit, ou alors aucun compte n\'est associé à ce téléphone.');
		
		//Si le téléphone du destinataire est dans un sac et/ou n'est pas possédé par un personnage
		$telDistantProprioId = $tel_distant->getIdProprio(); //Contournement d'un bug de Can't write in this context.
		if(empty($telDistantProprioId))
		{
			if(!empty($arr['inv_itemid']))
			{
				$query = 'SELECT inv_persoid'
						. ' FROM ' . DB_PREFIX . 'item_inv'
						. ' WHERE inv_id=:sacid'
						. ' LIMIT 1;';
				$prep = $db->prepare($query);
				$prep->bindValue(':sacid',	$arr['inv_itemid'],	PDO::PARAM_INT);
				$prep->execute($db, __FILE__,__LINE__);
				$arrSac = $prep->fetch();
				$prep->closeCursor();
				$prep = NULL;
				if($arrSac != NULL && !empty($arrSac['inv_persoid']))
					Member_He::add('System', $arrSac['inv_persoid'], 'Telephone', 'Un téléphone a sonné dans votre sac, vous n\'avez pas eu le temps de répondre.');
			}
			throw new GameException('Aucune réponse, réessayer plus tard.');
		}
		
		//Retrait de l'argent du compte téléphonique
		$compteAppeleur->changeCash('-', $this->getPrixAppel());
		$compteAppeleur->setCash();
		
		//On le cout d'appel met dans le fond commun
		$query = 'UPDATE `' . DB_PREFIX . 'banque_comptes`'
				. ' SET `compte_cash`=`compte_cash`+:cash'
				. ' WHERE `compte_id`=1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':cash', $this->getPrixAppel(), PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		//mise du message dans les HE des personnes impliquées
		$destMsgId= Member_He::add('System', $telDistantProprioId, 'Telephone', $entete_message_dest. '<hr />' . $message);
		$expMsgId = Member_He::add('System', $this->getIdProprio(), 'Telephone', $message);
		
		
		//log MJ: on enregistre le message qu'a reçu (et reçu dans son HE) le destinataire
		$query = 'INSERT INTO ' . DB_PREFIX . 'log_telephone'
				. ' ('
					. '`id_he_exp`, `id_he_dest`, `date`, `from_tel`, `from_persoid`, `to_tel`, `to_persoid`'
				. ' )'
				. ' VALUES'
				. ' ('
					. ' :expId, :destId, :date, :fromTel, :fromId, :toTel, :toId'
				. ' );';
		$prep = $db->prepare($query);
		$prep->bindValue(':expId',		$expMsgId,					PDO::PARAM_INT);
		$prep->bindValue(':destId',		$destMsgId,					PDO::PARAM_INT);
		$prep->bindValue(':date',		time(),						PDO::PARAM_INT);
		$prep->bindValue(':fromTel',	$this->getNotel(),			PDO::PARAM_STR);
		$prep->bindValue(':fromId',		$this->getIdProprio(),		PDO::PARAM_INT);
		$prep->bindValue(':toTel',		$callTo,					PDO::PARAM_STR);
		$prep->bindValue(':toId',		$telDistantProprioId,		PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
	}
	


	
	
	/**Met à jour le répertoire
	 * @param string $mem Chaine complétée et déjà protégée des données à sauvegarder
	 */
	public function majRepertoire ($mem)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'UPDATE  ' . DB_PREFIX . 'item_inv'
				. ' SET inv_memoiretext=:mem'
				. ' WHERE inv_id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':mem',	$mem,				PDO::PARAM_STR);
		$prep->bindValue(':id',		$this->getInvId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}
	


	
	
	/**Met à jour le compte et nip
	 * @param string $compte Numéro complet du nouveau compte
	 * @param string $nip Nouveau nip
	 */
	public function majCompte ($compte, $nip)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(empty($compte))
		{
			//MAJ numero de compte et nip -> vider les informations.
			$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
					. ' SET inv_nocompte="",'
						. ' inv_nobanque="",'
						. ' inv_nip=""'
					. ' WHERE inv_id=:id'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':id',		$this->getInvId(),	PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			return true;
		}
		
		
		
		//Valider le découpage du numéro
		$occ = preg_match('/([0-9]{4})-([0-9]{4})-([0-9]{4})-([0-9]{4})/', $compte);
		if($occ==0)
		{
			throw new Exception ('Le # de compte doit être au format XXXX-XXXX-XXXX-XXXX.');
			return false;
		}
		
		$compte_exp = explode( "-",$compte);
		$nobanque = $compte_exp[0];
		$nocompte = $compte_exp[1].'-'.$compte_exp[2].'-'.$compte_exp[3];
		
		//recup des info sur le compte du téléphone et sur le contenu de la partie compte du téléphone
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'banque_comptes'
				. ' LEFT JOIN ' . DB_PREFIX . 'banque ON (banque_no=compte_banque)'
				. ' WHERE compte_compte=:compte'
					. ' AND compte_banque=:banque'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':compte',		$nocompte,	PDO::PARAM_STR);
		$prep->bindValue(':banque',		$nobanque,	PDO::PARAM_STR);
		$prep->execute($db, __FILE__,__LINE__);
		$arrCompteTel = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		$compteTel	= new Member_BanqueCompte($arrCompteTel);	
		$banque 	= new Member_Banque($arrCompteTel);
		
		
		
		
		
		if($banque->getSupportTelephone () != 1)
		{
			throw new Exception ('Ce compte n\'est pas celui d\'une agence téléphonique.');
			return false;
		}
		
		
		if(empty($nip) && $this->getNip()!=NULL) // Mise a jour du numéro de compte car le champ nip n'a pas été modif
		{
		
			
			if($compteTel->getNip() != $this->getNip())
			{
				throw new Exception ('Le nip précédemment sauvegardé ne correspond pas.');
				return false;
			}
				
			$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
					. ' SET inv_nocompte=:compte,'
						. ' inv_nobanque=:banque'
					. ' WHERE inv_id=:id'
					. ' LIMIT 1;'; 
			$prep = $db->prepare($query);
			$prep->bindValue(':compte',		$nocompte,			PDO::PARAM_STR);
			$prep->bindValue(':banque',		$nobanque,			PDO::PARAM_STR);
			$prep->bindValue(':id',			$this->getInvId(),	PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$prep->closeCursor();
			$prep = NULL;
		}
		else //Si on désire mettre à jour le NIP
		{
			if(!is_numeric($nip))
			{
				throw new Exception ('Ce NIP n\'est pas numérique.');
				return false;
			}
			
			if($compteTel->getNip() != $nip )
			{
				throw new Exception ('Le nip ne correspond pas.');
				return false;
			}
			
			//MAJ numero de compte et nip
			$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
					. ' SET inv_nocompte=:compte,'
						. ' inv_nobanque=:banque,'
						. ' inv_nip=:nip'
					. ' WHERE inv_id=:id'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':compte',		$nocompte,			PDO::PARAM_STR);
			$prep->bindValue(':banque',		$nobanque,			PDO::PARAM_STR);
			$prep->bindValue(':nip',		$nip,				PDO::PARAM_INT);
			$prep->bindValue(':id',			$this->getInvId(),	PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$prep->closeCursor();
			$prep = NULL;
		}

		return true;
	}
	



	private function genNoTel()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante

		//Générer un nouveau # unique
		do
		{
			$noTel = rand(1,9) . rand(0,9) . rand(0,9) . '-' . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9);
			$query = 'SELECT COUNT(inv_id)'
						. ' FROM ' . DB_PREFIX . 'item_inv'
						. ' WHERE `inv_notel`=:noTel'
						. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':noTel',		$noTel,			PDO::PARAM_STR);
			$prep->execute($db, __FILE__,__LINE__);
			$arr = $prep->fetch();
			$prep->closeCursor();
			$prep = NULL;
			
		}while($arr[0] > 0);
		
		$this->notel = $noTel;
		
		//Enregistrer le #
		$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
					. ' SET `inv_notel`=:noTel'
					. ' WHERE `inv_id`=:id'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':noTel',		$this->notel,		PDO::PARAM_STR);
		$prep->bindValue(':id',			$this->getInvId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}
}

