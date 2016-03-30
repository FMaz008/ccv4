<?php
/** Gestion d'un Historique des Évènements.
 * 
 * Exemple d'utilisation:
 * <code>
 * $he = new Member_He();
 * </code>
 *
 * @package Member
 * @subpackage HE
 */
define ('HE_UNIQUEMENT_MOI',	2);
define ('HE_TOUS',	1);
define ('HE_AUCUN',	0);
class Member_He
{
	/*
	private $nbrMsg;
	private $maxMsg;
	
	function __construct(&$account, &$perso)
	{
		$this->nbrMsg = $perso->getHeMsgCount());
		$this->maxMsg = self::spacePerMembership($account->getMemberLevel());
	}

	public function getNbrMsg(){		return $this->nbrMsg; }
	public function getMaxMsg(){		return $this->maxMsg; }
	
	*/
	
	
	
	public static function listMessages(&$perso, $from, $nbr, $parseBBCode=true)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query= 'SELECT he.msg, he.date, he.id AS hid, he.type,'
					. ' pc.nom, ft.fromto, ft.persoid, ft.name_complement,'
					. ' p.sexe, p.imgurl, ft.`show`'
				. ' FROM ('
					. 'SELECT msgid, `show`'
					. ' FROM ' . DB_PREFIX . 'he_fromto'
					. ' WHERE `show`!=0'
						. ' AND persoid = :persoId1'
					. ' ORDER BY `msgid` DESC'
					. ' LIMIT :from, :nbr'
				. ' ) as sq'
				. ' INNER JOIN ' . DB_PREFIX . 'he AS he ON (he.id=sq.msgid)'
				. ' INNER JOIN ' . DB_PREFIX . 'he_fromto AS ft ON ( ft.msgid = he.id )'
				. ' LEFT JOIN '.DB_PREFIX.'perso_connu AS pc'
					. ' ON (pc.persoid = :persoId2'
						. ' AND pc.nomid = ft.persoid )'
				. ' LEFT JOIN '.DB_PREFIX.'perso AS p'
					. ' ON ( p.id = ft.persoid )'
				. ' ORDER BY he.`date` DESC, hid ASC, ft.fromto ASC , pc.nom ASC';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId1',   $perso->getId(),        PDO::PARAM_INT);
		$prep->bindValue(':persoId2',   $perso->getId(),        PDO::PARAM_INT);
		$prep->bindValue(':from',       $from,                  PDO::PARAM_INT);
		$prep->bindValue(':nbr',        $nbr,                   PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__, __LINE__);
		
		$heMsg = array();
		$i=-1;
		$lastMsgId = -1; //Id du dernier message
		$lastPersoId = -1; //Id du dernier perso From/To
		//Créer le message et la liste de perso de/a.
		while($arr = $prep->fetch())
		{
			if ($arr['hid']!=$lastMsgId) //Il s'agit d'un nouveau message
			{
				$heMsg[++$i] = new Member_HeMessage($perso, $arr, $parseBBCode);
				$lastMsgId = $arr['hid'];
				$lastPersoId = $arr['persoid'];
			}
			else
			{
				if ($lastPersoId != $arr['persoid'])
				{
					$heMsg[$i]->addFromTo($perso, $arr);
					$lastPersoId = $arr['persoid'];
				}
			}
		}
		
		$prep->closeCursor();
        $prep = NULL;
		return $heMsg;
	}
	
	
	
	
	
	
	
	
	
	/** Ajoute un message dans un ou des HE.
	 *
	 * Exemple d'utilisation - Ajouter un message Système au personnage #801:
	 * <code>
	 * $he::add('System', 801, 'remise', 'Remise de PA effectué.');
	 * </code>
	 * <br>
	 * Exemple d'utilisation - Envoyer un message du personnage #801 à 3 personnages:
	 * <code>
	 * $arrToPerso = array{545, 656, 701};
	 * Member_He::add(801, $arrToPerso, 'msg', 'Salut les gars !');
	 * </code>
	 * <br>
	 * Exemple d'utilisation - Envoyer un message du personnage joué à tous les personnages du lieu actuel:
	 * <code>
	 * while( $arrPerso = $perso->getLieu()->getPerso($perso, $i++))
	 *	$arrIdPerso = $arrPerso->getId()
	 *	
	 * Member_He::add($perso->getId(), $arrIdPerso , 'msg', 'Salut à tlm !');
	 * </code>
	 * <br>
	 * <br>
	 * <br>Valeurs possibles pour $fromshow et $toshow:
	 * - HE_AUCUN : Ne pas afficher le message aux gens de cette liste.
	 * - HE_TOUS : Afficher le message aux gens de cette liste.
	 * - HE_UNIQUEMENT_MOI : Afficher le message aux gens de cette liste, mais ne pas leurs afficher la liste, excepté eux-même.
	 * 
	 * @param int $from Id ou nom du ou des envoyeur(s) du message. Peut être un int ou un tableau d'int
	 * @param int $to Id ou nom du ou des récepteur(s) du message. Peut être un int ou un tableau d'int
	 * @param string $type Type du message (techniquement utile pour la suppression des déplacement ou le tri par type de message)
	 * @param string $msg Message
	 * @param bool $fromshow Afficher le message à l'/les envoyeur(s) (par défaut à HE_TOUS)
	 * @param bool $toshow Afficher le message à l'/les récepteur(s) (par défaut à HE_TOUS)
	 * @param bool $msgProtect Protège le message en supprimant les balises HTML(par défaut à true)
	 * @return int Retourne l'ID du message ajouté
	 */ 
	public static function add($from=null, $to=null, $type='', $msg='', $fromshow=HE_TOUS, $toshow=HE_TOUS, $msgProtect=true)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		if (!is_numeric($fromshow))
		{
			throw new Exception('Valeur du paramètre fromshow non-numérique. Corrigez l\'appel à Member_He::add');
			return false;
		}
		if (!is_numeric($toshow))
		{
			throw new Exception('Valeur du paramètre toshow non-numérique. Corrigez l\'appel à Member_He::add');
			return false;
		}
		

		
		//Préparer les requêtes FromTo
		//##############################################################

		$query = 'INSERT INTO `' . DB_PREFIX . 'he_fromto`'
					. ' (`msgid`, `fromto`, `persoid`, `lieuid`, `masque`, `show`, `id_description`, `name_complement`)'
					. ' VALUES'
					. ' (:msgId,  :fromTo,  :persoId,  :lieuId,  :masque,  :show, :idDescription,     :nameComplement);';
		$prepAddFromTo = $db->prepare($query);
		
		//##############################################################




		
		//NULL = Message systeme (0)
		if ($from===NULL)
			$from = array(0);
		
		if ($to===NULL)
			$to = array(0);
		
		//Si les paramètres ne sont pas un tableau, les placer dans un tableau
		if (!is_array($from))
			$from = array($from);
			
		if (!is_array($to))
			$to = array($to);
		
		//Valider si on essaie d'envoyer un message à au moins 1 joueur (un ID)
		$fromCheck	= is_array($from) && count($from)>0 && is_numeric($from[0]);
		$toCheck	= is_array($to) && count($to)>0 && is_numeric($to[0]);
		
		if(!$fromCheck && !$toCheck)
		{
			throw new Exception('Aucune correspondance tangible au message. Ajoutez un From ou un To. Corrigez l\'appel à Member_He::add');
			return false;
		}
		
		
		
		
		//### Créer le message
		if($msgProtect)
			$msg = fctScriptProtect($msg);
		

		try
		{
			$db->beginTransaction();
			
		
			$query = 'INSERT INTO `' . DB_PREFIX . 'he`'
						. ' (`date`, `type`, `msg`)'
						. ' VALUES ('
							. ' UNIX_TIMESTAMP(),'
							. ' :type,'
							. ' :msg'
						. ' );';
			$prep = $db->prepare($query);
			$prep->bindValue(':type',	$type,	PDO::PARAM_STR);
			$prep->bindValue(':msg',	$msg,	PDO::PARAM_STR);
			$prep->executePlus($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			$msgId = $db->lastInsertId(); //Récupérer l'ID du message inséré.

			
			//Créer les lien FROMTO (Insérer "from")
			self::addFromTo($msgId,	'from',	$from,	$fromshow,	$type, $prepAddFromTo);
			
			//Créer les lien FROMTO (Insérer "to")
			self::addFromTo($msgId,	'to',	$to,	$toshow,	$type, $prepAddFromTo);
		
			
			//Sauvegarder les modifications
			$db->commit();
			return $msgId;
		}
		catch(exception $e)
		{
			$db->rollback();
			throw $e;
			return false;
		}
	}
	

	
	
	/**
	 * Cette fonction ajoute les destinataire et envoyeur d'un message.
	 *
	 * <br>Valeurs possibles pour $show:
	 * - HE_AUCUN : Ne pas afficher le message aux gens de cette liste.
	 * - HE_TOUS : Afficher le message aux gens de cette liste.
	 * - HE_UNIQUEMENT_MOI : Afficher le message aux gens de cette liste, mais ne pas leurs afficher la liste, excepté eux-même.
	 * 
	 * @param int $msgId Id du message du HE pour lequel on ajoute des destinataires
	 * @param string $fromTo 'to' si on ajoute des destinataire, 'from' pour des expéditeurs
	 * @param array $arrId Tableau des Id ou Nom système des destinataires.
	 * @param int $show Constante d'affichage
	 */
	private static function addFromTo($msgId, $fromTo, $arrId, $show, $type=null, &$prepAddFromTo)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		if(!is_numeric($msgId))
		{
			throw new Exception('msgId doit être numérique.');
			return false;
		}
		if(!in_array($fromTo, array('from', 'to')))
		{
			throw new Exception('fromTo doit être from ou to.');
			return false;
		}
		if(!is_array($arrId))
		{
			throw new Exception('arrId doit être un array.');
			return false;
		}

		if(count($arrId)==0)
			return; // Aucun ajout à faire

		
		//Ramasser les descriptions & masque de tous les joueurs
		$arrInfo = array(); //Les données sur les perso y seront stockés

		//Regrouper les ID de perso
		$arrNumericId = array();
		foreach($arrId as $value)
			if($value > 0)
				$arrNumericId[] = $value;

		$arrNumericIdCount = count($arrNumericId);

		try
		{
			if($arrNumericIdCount!=0)
			{
				$queryIn = implode(',', $arrNumericId);
				
				//Cette requête ne peut pas être préparé à l'avance car elle utilise un IN
				$query = 'SELECT p.id, p.heQte, a.mp, p.description, l.id as lieu_id, i.inv_id as masque_id'
						. ' FROM ' . DB_PREFIX . 'perso as p'
						. ' INNER JOIN ' . DB_PREFIX . 'account as a ON (a.id = p.userId)'
						. ' LEFT JOIN ' . DB_PREFIX . 'lieu as l'
							. ' ON (l.nom_technique = p.lieu)'
						. ' LEFT JOIN ('
								. ' SELECT i.inv_persoId, MIN(i.inv_id) as inv_id'
								. ' FROM ' . DB_PREFIX . 'item_inv as i, cc_item_db as d'
								. ' WHERE i.inv_persoId IN (' . $queryIn . ')'
									. ' AND i.inv_equip="1"'
									. ' AND d.db_id = i.inv_dbid'
									. ' AND d.db_masque="1"'
								. ' GROUP BY i.inv_persoId'
								. ' LIMIT :max1'
						. ' ) as i ON (i.inv_persoId = p.id)'
						. ' WHERE p.id IN (' . $queryIn . ')'
						. ' LIMIT :max2;';
				$prep = $db->prepare($query);
				$prep->bindValue(':max1',	$arrNumericIdCount,	PDO::PARAM_INT);
				$prep->bindValue(':max2',	$arrNumericIdCount,	PDO::PARAM_INT);
				$prep->executePlus($db, __FILE__, __LINE__);
				$arrData = $prep->fetchAll();
				$prep->closeCursor();
				$prep = NULL;
				
				foreach($arrData as &$arr)
					$arrInfo[$arr['id']] = $arr;
				
			}

			
			foreach ($arrId as $persoId)
			{
				
				//Message d'un joueur
				if ($persoId > 0)
				{
					
					//Vider le HE si de l'espace suplémentaire est nécésaire
					if($show != HE_AUCUN)
						self::freeSpace($persoId, $arrInfo[$persoId]['heQte'], $arrInfo[$persoId]['mp']);

					if($fromTo=='to' && $type=='move')
					{
						//Déplacement de type MOVE, ne pas stocker de description
						// sur la personne.
						$desc = '';
					}
					else
					{
						//Ramasser la description
						//Si le perso à été supprimé dans l'interval, passer au suivant
						if(!isset($arrInfo[$persoId]['description']))
							continue;
						
						$desc = stripslashes($arrInfo[$persoId]['description']);
					}

					//Récupérer l'id de la description dans la table des description
					$descId = self::getIdDescription($desc);
					
					//Mettre à jour les stats du HE.
					if($show!=HE_AUCUN && is_numeric($persoId) && $persoId!=0)
						self::changeStat($persoId, 1);
					
					
					//Vérifier si un masque est équipé (Si le perso est masquer, placer la valeur à 1)
					$masque = isset($arrInfo[$persoId]['masque_id']) ? 1 : 0;
					$lieuId	= isset($arrInfo[$persoId]['lieu_id']) ? $arrInfo[$persoId]['lieu_id'] : 0;
					

					//Utiliser la requête préparée précédemment
					$prepAddFromTo->bindValue(':msgId',			 $msgId);
					$prepAddFromTo->bindValue(':fromTo',		 $fromTo);
					$prepAddFromTo->bindValue(':persoId',		 $persoId);
					$prepAddFromTo->bindValue(':lieuId',		 $lieuId);
					$prepAddFromTo->bindValue(':idDescription',	 $descId);
					$prepAddFromTo->bindValue(':masque',		 $masque);
					$prepAddFromTo->bindValue(':show',			 $show);
					$prepAddFromTo->bindValue(':nameComplement', '');
				}
				else //Message Système.
				{
					//Récupérer l'id de la description dans la table des description
					$descId = self::getIdDescription('');

					//Dans le cas ou le From est personnalisé, il peut-etre une string (par un MJ par exemple)
					$prepAddFromTo->bindValue(':msgId',			$msgId);
					$prepAddFromTo->bindValue(':fromTo',		$fromTo);
					$prepAddFromTo->bindValue(':persoId',		0);
					$prepAddFromTo->bindValue(':lieuId',		0);
					$prepAddFromTo->bindValue(':idDescription',	$descId);
					$prepAddFromTo->bindValue(':masque',		0);
					$prepAddFromTo->bindValue(':show',			0);
					$prepAddFromTo->bindValue(':nameComplement', $persoId);
				}
				$prepAddFromTo->executePlus($db, __FILE__, __LINE__);
			}
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}

	/** Renvoi l'id de la description se trouvant dans la table des descriptions
	 *
	 * Si la description n'existe pas, elle est ajoutée dans la table
	 *
	 * @param string $description la description à trouver
	 * @return int l'id de la description
	 */
	private static function getIdDescription($description)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante

		$queryGet =		'SELECT `id` '
						. ' FROM ' . DB_PREFIX . 'he_description'
						. ' WHERE `description` = :description'
						. ' LIMIT 1;';
		$prep = $db->prepare($queryGet);
		$prep->bindValue(':description', $description, PDO::PARAM_STR);
		$prep->executePlus($db, __FILE__, __LINE__);

		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

		$idDescription;

		//Si pas de réponse -> insérer la description
		if(empty($arr))
		{
			$queryAdd =	'INSERT INTO `' . DB_PREFIX . 'he_description`'
						. ' (`description`, `msg_who_use`)'
						. ' VALUES'
						. ' (:description,  :msgWhoUse);';
			$prep = $db->prepare($queryAdd);
			$prep->bindValue(':description',	$description,	PDO::PARAM_STR);
			$prep->bindValue(':msgWhoUse',		1,				PDO::PARAM_INT);
			$prep->executePlus($db, __FILE__, __LINE__);

			$idDescription = $db->lastInsertId(); //Récupérer l'ID de la description insérée.
			$prep->closeCursor();
			$prep = NULL;
		}
		else	//La description existe -> on récupère son id et on incrémente le compteur
		{
			$idDescription = $arr['id'];

			$queryUp =	'UPDATE `' . DB_PREFIX . 'he_description`'
						. ' SET `msg_who_use` = `msg_who_use` + 1'
						. ' WHERE `id` = :id'
						. ' LIMIT 1;';
			$prep = $db->prepare($queryUp);
			$prep->bindValue(':id',	$idDescription,	PDO::PARAM_INT);
			$prep->executePlus($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
		}

		return $idDescription;
	}
	
	/** Supprime les message excédant la taille maximale permise.
	 * Libère l'espace nécésaire pour ajouter un message.
	 * 
	 * Exemple d'utilisation (requiert 0 requêtes)
	 * <code>
	 * self::freeSpace($perso->getId(), $arr['heQte'], $arr['mp']);
	 * </code>
	 * 
	 * Exemple d'utilisation (requiert 2 requêtes)
	 * <code>
	 * self::freeSpace($perso->getId());
	 * </code>
	 * @param int $pid Id du ou perso dont on veux trunker le HE.
	 * @param int $heQte Quantité de message dans le HE (champ perso.heQte).
	 * @param string $mp Status du M+ (champ account.mp).
	 */ 
	private static function freeSpace($pid, $heQte=null, $mp=null)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Vérifier si il reste suffisament d'espace disponible dans le HE pour ajouter le nouvel item.
		if(empty($htQte))
			$heQte   = Member_He::getMsgCount($pid); //WARNING: 1 query.

		if(empty($mp))
			$maxSpace = Member_He::calculateHeMaxSize($pid); //WARNING: 1 query.
		else
			$maxSpace = self::spacePerMembership($mp);
		
		//echo "<!-- DEBUG: HE_SIZE ($pid): $hesize / $maxspace -->";
		
		//Vérifier si l'espace restant dans le HE est correcte
		if ($heQte >= $maxSpace && $maxSpace > 0)
		{
			$qteDeleted = 0;
			$query = 'UPDATE `' . DB_PREFIX . 'he_fromto`'
					. ' SET `show`="0"'
					. ' WHERE `persoid`= :persoId'
					. ' ORDER BY `msgid` ASC'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':persoId', $pid);
			
			while ($heQte - $qteDeleted >= $maxSpace
					&& $qteDeleted<10) //Éviter une boucle infinie.
			{
				
				// Présomption: l'ID gère l'ordre temporel.
				$prep->executePlus();
				
				$qteDeleted++;
			}
			
			//Mettre à jour les stats du HE.
			self::changeStat($pid, $qteDeleted, true, array(__FUNCTION__, $query));
		}
	}
	
	/** 
	 * Calcule le nombre de message dans le HE.
	 * 
	 * Note: Cette Méthode est à éviter car elle est très lente.
	 * Favoriser Member_He::getMsgCount(), ou si votre classe perso est instanciée, Member_Perso::getHeMsgCount().
	 *
	 * @param int $id Id du perso à qui appartient le HE.
	 * @return int Nombre de message dans le HE.
	 */
	private static function calculateHeSize($id)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Dimplodeemander la connexion existante
		
		
		$query = 'SELECT COUNT(msgid)'
					. ' FROM `' . DB_PREFIX . 'he_fromto`'
					. ' WHERE persoid=:persoId'
						. ' AND `show`!=0;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$id,	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		return $arr[0];
	}

	/** 
	 * Recoit le nombre pré-calculé de message dans le HE.
	 * 
	 * Note: Cette Méthode est à éviter car elle requiert une requête.
	 * Favoriser Member_Perso::getHeMsgCount() si votre classe perso est instanciée.
	 *
	 * @param int $id Id du perso à qui appartient le HE.
	 * @return int Nombre de message dans le HE.
	 */
	private static function getMsgCount($id)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Dimplodeemander la connexion existante
		
		
		$query = 'SELECT heQte'
					. ' FROM `' . DB_PREFIX . 'perso`'
					. ' WHERE `id`=:persoId'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$id,	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		return $arr['heQte'];
	}

	
	
	/**
	 * Quel est le nombre de message maximum que peux contenir le HE d'une personne ?
	 *
	 * Favoriser Member_He::spacePerMembership() car la présente méthode requiert une requête
	 * pour trouver le niveau d'abonnement d'un perso.
	 */
	private static function calculateHeMaxSize($id)
	{ 
		return self::spacePerMembership(Member_Perso::memberLevel($id));
	}
		
	/** 
	 * Retourne l'espace de chaque niveau de membre
	 */
	public static function spacePerMembership($level)
	{
		switch($level)
		{
			case '0': return 500; 	break;
			case '1': return 1000;  break;
			case '2': return 5000;	break;
			case '3': return 12000; break;
			default:
				fctBugReport('Le statut de membre diverge des options possibles (' . $level . ')', $level, __FILE__, __LINE__,__FUNCTION__,__CLASS__,__METHOD__, true);
				break;
		}
	}
	
	
	/** 
	 * Supprime des messages du point de vue du joueurs
	 *
	 * @param int $persoId Id du perso à qui appartient le HE.
	 * @param array $arr Tableau des Id des messages à masquer.
	 */
	public static function deleteMessages($persoId, $arr)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		$arrQ = array();
		for($i=1; $i<=count($arr); $i++)
			$arrQ[] = '?';
		$strQ = implode(',', $arrQ);
		
		
		$query = 'UPDATE `' . DB_PREFIX . 'he_fromto`'
					. ' SET `show`=' . HE_AUCUN
					. ' WHERE msgid IN (' . $strQ . ')'
						. ' AND persoid=? ;';
		$prep = $db->prepare($query);
		
		for($i=1; $i<=count($arr); $i++)
			$prep->bindValue($i,	$arr[$i-1],	PDO::PARAM_INT);
		
		// $i == 2
		$prep->bindValue($i,	$persoId,	PDO::PARAM_INT); 	
		
		$prep->executePlus($db, __FILE__, __LINE__);
		$affRow = $prep->rowCount();
		$prep->closeCursor();
		$prep = NULL;
		
		
		//Mettre à jour les stats du HE.
		self::changeStat($persoId, $affRow, true, array(__FUNCTION__, $query));

		//Lancer la routine de purge réelle des messages effacés.
		self::deleteErasedMessages();
	}
	
	/** 
	 * Masque tous les messages d'un certain type
	 *
	 * @param int $persoId Id du perso concerné
	 * @param string $type Type de message à effacer
	 */
	public static function deleteType($persoId, $type)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante

		$db->beginTransaction();
		
		//Trouver les items à supprimer
		$query = 'SELECT ft.msgid'
				. ' FROM ' . DB_PREFIX . 'he_fromto as ft'
				. ' INNER JOIN ' . DB_PREFIX . 'he as h ON (h.id=ft.msgid)'
				. ' WHERE h.type=:type'
					. ' AND ft.persoid=:persoId'
					. ' AND ft.`show`!=' . HE_AUCUN . ';';
		$prep = $db->prepare($query);
		$prep->bindValue(':type',		$type,		PDO::PARAM_STR);	
		$prep->bindValue(':persoId',	$persoId,	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

        if(empty($arrAll))
            return;
        
		foreach($arrAll as &$arr)
			$arrMsgId[] = $arr['msgid'];

		unset($arrAll);
		$strMsgId = implode(',',$arrMsgId);
		
		
		$query = 'UPDATE ' . DB_PREFIX . 'he_fromto'
				. ' SET `show`=' . HE_AUCUN
				. ' WHERE msgid IN (' . $strMsgId . ')'
					. ' AND persoid=:persoId;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$persoId,	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__, __LINE__);
		$affRow = $prep->rowCount();
		$prep->closeCursor();
		$prep = NULL;

		$db->commit();
		
		//Mettre à jour les stats du HE.
		self::changeStat($persoId, $affRow, true, array(__FUNCTION__, $query));

		//Lancer la routine de purge réelle des messages effacés.
		self::deleteErasedMessages();
	}


	/** 
	 * Supprime tous les messages du HE et force la remise du compteur heQte à 0.
	 *
	 * @param int $persoId Id du perso à qui appartient le HE.
	 */
	public static function deleteAll($persoId)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		$query = 'UPDATE `' . DB_PREFIX . 'he_fromto`'
					. ' SET `show`=' . HE_AUCUN
					. ' WHERE persoid=:persoId ;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$persoId,	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__, __LINE__);
		$affRow = $prep->rowCount(); //Messages effacés
		$prep->closeCursor();
		$prep = NULL;
		
		
		//Mettre à jour les stats du HE.
		$query = 'UPDATE `' . DB_PREFIX . 'perso`'
					. ' SET `heQte`=0'
					. ' WHERE `id`=:persoId'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$persoId,	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		//Lancer la routine de purge réelle des messages effacés.
		self::deleteErasedMessages();
	}

	
	/** 
	 * Supprime physiquement tout les message virtuellement effacé.
	 *
	 * @return int Retourne le nombre de message effacé.
	 */
	private static function deleteErasedMessages()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Effacer en permanence les items du HE qui sont effacé et expiré de X jours
		$expiration = mktime (date('H'), date('i'), date('s'), date('m'), date('d')-ENGINE_HE_EXPIRE, date('Y'));
		
		//Lister tous les messages qui sont effacés par tous les joueurs ET qui sont expirés
		$query = 'SELECT h.`id`, d.`id_description`'
					. ' FROM ' . DB_PREFIX . 'he as h'
					. ' LEFT JOIN ' . DB_PREFIX . 'he_fromto as d'
						. ' ON (d.`msgid` = h.`id`)'
					. ' WHERE h.`date` < :date'
					. ' GROUP BY h.`id` HAVING SUM( d.`show` )=0;';
		$prep = $db->prepare($query);
		$prep->bindValue(':date',	$expiration,	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__, __LINE__);
		$arrMsg = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		if( count($arrMsg) == 0)
			return 0;
		
		$arr = array();
		foreach($arrMsg as $msg)
			$arr[] = (int)$msg[0];
		
		$into = implode(',', $arr);
		
		//Effacer le message
		$query = 'DELETE FROM `' . DB_PREFIX . 'he`'
				. ' WHERE `id` IN (' . $into . ');';
		$prep = $db->prepare($query);
		$prep->executePlus($db, __FILE__, __LINE__);
		$deleted = $prep->rowCount();
		$prep->closeCursor();
		$prep = NULL;
		
		
		
		//Effacer les destinataires
		$query = 'DELETE FROM `' . DB_PREFIX . 'he_fromto`'
				. ' WHERE msgid IN (' . $into . ');';
		$prep = $db->prepare($query);
		$prep->executePlus($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		//Décrémenter le compteur pour la table des descriptions pour les messages
		$queryDec =	'UPDATE `' . DB_PREFIX . 'he_description`'
						. ' SET `msg_who_use` = `msg_who_use` - 1'
						. ' WHERE `id` = :id'
						. ' LIMIT 1;';
		$prep = $db->prepare($queryDec);
		foreach($arrMsg as $msg)
		{
			$prep->bindValue(':id',	$msg['id_description'],	PDO::PARAM_INT);
			$prep->executePlus($db, __FILE__, __LINE__);
		}
		$prep->closeCursor();
		$prep = NULL;

		//Supprimer les descriptions qui ne sont plus utilisées
		$query = 'DELETE FROM `' . DB_PREFIX . 'he_description`'
				. ' WHERE `msg_who_use` <= 0;';
		$prep = $db->prepare($query);
		$prep->executePlus($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		return $deleted;
	}
	

	/**
	 * Cette fonction s'occupe de mettre à jours le compte des messages
	 * dans le HE ainsi que sa taille totale.
	 *
	 * @param int $persoId Id du perso à qui le HE appartient.
	 * @param int $qte Quantité de message ajoutés ou supprimés.
	 * @param bool $deleted S'il sagit de la suppression d'un message.
	 */
	
	private static function changeStat($persoId, $qte=1, $deleted=false, $extraInfo=null)
	{
		if($qte == 0)
			return;
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante

		//Modifier les statistiques sur les message dans le HE.
		if($deleted)
		{
			// le AND préviennent les 0-1 = VALEUR_MAX_DU_CHAMP
			$query = 'UPDATE `' . DB_PREFIX . 'perso`'
					. ' SET `heQte`=`heQte`-:qte1'
					. ' WHERE `id`=:persoId'
						. ' AND `heQte` >= :qte2'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':qte1',		$qte,		PDO::PARAM_INT);
			$prep->bindValue(':qte2',		$qte,		PDO::PARAM_INT);
			$prep->bindValue(':persoId',	$persoId,	PDO::PARAM_INT);
		}
		else
		{
			$query = 'UPDATE `' . DB_PREFIX . 'perso`'
					. ' SET `heQte`=`heQte`+:qte'
					. ' WHERE `id`=:persoId'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':qte',		$qte,		PDO::PARAM_INT);
			$prep->bindValue(':persoId',	$persoId,	PDO::PARAM_INT);
		}
		$prep->executePlus($db, __FILE__, __LINE__);
		$affRow = $prep->rowCount();
		$prep->closeCursor();
		$prep = NULL;
		
		
		//Mettre à jour les stats du HE.
		
		if($affRow==0)
		{
			//Remettre le compteur à zéro.
			$query = 'UPDATE `' . DB_PREFIX . 'perso`'
						. ' SET `heQte`=0'
						. ' WHERE `id`=:persoId'
						. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':persoId',	$persoId,	PDO::PARAM_INT);
			$prep->executePlus($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			fctBugReport(
						'Incohérence heQte détectée.',
						array($query,$extraInfo),
						__FILE__, __LINE__,
						__FUNCTION__, __CLASS__, __METHOD__,
						true, false, false);
		}
	}
}

