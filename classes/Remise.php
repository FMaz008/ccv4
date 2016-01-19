<?php
/**
 * Traitement de la remise aux joueurs.
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package CyberCity_2034
 */
 
class Remise
{
	public static $debugInfo;
	
	public static function doRemise(&$account, $maxRemise)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante


		try
		{
			$db->beginTransaction();
		
			//Tagger tout les comptes qui doivent recevoir une remise
			$query = 'SELECT id'
						. ' FROM ' . DB_PREFIX . 'account'
						. ' WHERE remise<UNIX_TIMESTAMP()'
						. ' LIMIT :max;';
			$prep = $db->prepare($query);
			$prep->bindValue('max',		$maxRemise,		PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$arrAccount = $prep->fetchAll();
			$prep->closeCursor();
			$prep = NULL;
			
		
			//Scan tout les accounts qui doivent avoir une remise
			foreach($arrAccount as &$arr)
				self::remiseAccount($arr['id']);
			
			//Sauvegarder les modifications
			$db->commit();
		}
		catch(exception $e)
		{
			$db->rollback();
			fctBugReport('Erreur', $e->getMessage(), __FILE__, __LINE__);
		}
	
		
		
		//Supprimer les portes tenues innutilisées
		$query = 'DELETE FROM ' . DB_PREFIX . 'lieu_tenirporte'
					. ' WHERE expiration<UNIX_TIMESTAMP();';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
			
		//Supprimer tous les banissements achevés
		$query = 'DELETE FROM ' . DB_PREFIX . 'lieu_ban'
					. ' WHERE remiseleft<1;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		//Rendre les comptes M+ expiré Niveau 0
		$query = 'UPDATE ' . DB_PREFIX . 'account'
					. ' SET mp="0", mp_expiration=0'
					. ' WHERE mp_expiration<UNIX_TIMESTAMP()'
						. ' AND mp!="0";';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		//Traiter les vieux médias
		self::deleteOldMedias();
		
		//Traiter les transactions automatiques
		self::doTransactions();
		
		//Traiter les comptes innactifs
		Innactif::go($account);
	}
	
	private static function doTransactions()
	{
		$limitTransaction = 5;
		//On récupère les transactions qui doivent être effectuées
		$transactions = Member_BanqueTransactionAuto::getTransactionsReady($limitTransaction);
		
		if(count($transactions) > 0)
		{
			foreach($transactions as $transaction)
				try
				{
					$transaction->effectuerTransaction();
				}
				catch(Exception $e)
				{
					//S'il y a un problème, on signal l'erreur mais on continue l'exécution de la remise
					fctBugReport('Erreur lors d\'une transaction automatique de banque',
								array(
									'error' => $e->getMessage()), 
									__FILE__, __LINE__, __FUNCTION__, __CLASS__, __METHOD__, false, false, false);
				}
		}
	}
	
	private static function deleteOldMedias()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		//Liste des émission dans l'ordre décroissant de date
		$query = 'SELECT DISTINCT `mediaType`, `canalId`, `titre`'
				. ' FROM (SELECT * FROM `' . DB_PREFIX . 'media` ORDER BY `date` DESC) as temp1;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		if(count($arrAll) == 0)
			return;
			
		//On traite seulement l'émission la plus ancienne
		$emission = $arrAll[count($arrAll) - 1];
		
		//On récupère la date du message le plus récent de l'émission
		$query = 'SELECT `date` FROM `' . DB_PREFIX . 'media`'
				. ' WHERE `titre` = :titre'
				. ' AND `canalId` = :canalId'
				. ' AND `mediaType` = :mediaType' 
				. ' ORDER BY `date` DESC LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':titre', $emission['titre'], PDO::PARAM_STR);
		$prep->bindValue(':canalId', $emission['canalId'], PDO::PARAM_INT);
		$prep->bindValue(':mediaType', $emission['mediaType'], PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		$limitTimeMedia = 60;
		$limit = mktime (date("H"), date("i"), date("s"), date("m"), date("d") - $limitTimeMedia, date("Y"));
		
		if($limit > $arr['date'])
		{
			$query = 'DELETE FROM `' . DB_PREFIX . 'media`'
					. ' WHERE `titre` = :titre'
					. ' AND `canalId` = :canalId'
					. ' AND `mediaType` = :mediaType;';
			$prep = $db->prepare($query);
			$prep->bindValue(':titre', $emission['titre'], PDO::PARAM_STR);
			$prep->bindValue(':canalId', $emission['canalId'], PDO::PARAM_INT);
			$prep->bindValue(':mediaType', $emission['mediaType'], PDO::PARAM_STR);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
		}
	}
	
	private static function remiseAccount($accountId)
	{
	
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		if(DEBUG_MODE)
			self::$debugInfo .=  'a' . $accountId;
		
		$query = 'SELECT *'
					. ' FROM ' . DB_PREFIX . 'perso'
					. ' WHERE userId=:userId'
						. ' AND inscription_valide="1";';
		$prep = $db->prepare($query);
		$prep->bindValue(':userId',		$accountId,		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrPerso = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
			
		
		//Passe tout les perso d'un compte
		foreach($arrPerso as $arr)
		{
			//$perso->getLieu() peut lever une exception si le lieu n'existe pas
			// Dans ce cas, on zap la remise
			try
			{
				$perso = new Member_Perso($arr);
				if(!in_array(
						$perso->getLieu()->getNomTech(),
						array(
							INNACTIVITE_VOLUNTARY_LOCATION,
							INNACTIVITE_TELEPORT_LOCATION
						)
					)
				){
					self::remisePerso($perso);
				}
			}
			catch(exception $e)
			{
				//On fait rien --> on zap la remise du perso sans bloquer les autres remises
			}
		}
		
		//Détagguer le compte & définir la prochaine remise
		$query = 'UPDATE ' . DB_PREFIX . 'account'
					. ' SET remise=remise+86400'
					. ' WHERE id=:userId'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':userId',		$accountId,		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		if(DEBUG_MODE)
			self::$debugInfo .=  'a2';
	}
	
	
	
	
	private static function remisePerso(&$perso)
	{

		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		if(DEBUG_MODE)
			self::$debugInfo .=  $perso->getNom();

		//Update Bannissement pour vol
		$query = 'UPDATE ' . DB_PREFIX . 'lieu_ban'
					. ' SET remiseleft=remiseleft-1'
					. ' WHERE persoid=:persoId;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',		$perso->getId(),		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		//Remettre le perso soignable
		$query = 'UPDATE ' . DB_PREFIX . 'perso'
					. ' SET soin=0'
					. ' WHERE id=:persoId'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',		$perso->getId(),		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		
		
		//Supprimer toutes les drogues épuissées
		self::remisePersoDrogue($perso);
		
		//Les demande de confirmation expire après 3 jours
		$expir = mktime (date("H"), date("i"), date("s"), date("m"), date("d")-3, date("Y"));
		
		//Vérifier l'expiration des menottages
		self::remisePersoMenotte($perso, $expir);
		
		//Vérifier l'expiration des autorisation de fouille
		self::remisePersoFouille($perso, $expir);
		
		//Update PA
		$paMax = $perso->getPaMax() - $perso->getPa();
		$paRemis = PA_PAR_REMISE > $paMax ? $paMax : PA_PAR_REMISE;
		$perso->changePa('+', $paRemis);
		
		//Update PN
		$pnRetire = 1 + round($paRemis/25);
		$perso->changePn('-', $pnRetire);
		
		//Update PV
		if($perso->isNormal())
			$perso->changePv('+', 2);
		elseif($perso->isAutonome())
			$perso->changePv('+', 1);
		elseif($perso->isConscient())
			$perso->changePv('-', 1);
		elseif($perso->isVivant())
			$perso->changePv('-', 2);
		
		
		if($perso->isRassasie())
			$perso->changePv('+', 1);
			
		if($perso->isFaim())
			$perso->changePv('-', 4);
		
		
		//Spécificités PNJ & ROBOT
		if($perso->getType() == 'pnj' || $perso->getType() == 'robot')
		{
			$perso->setPn($perso->getPnMax());
		}
		
		//Spécificité MANNEQUIN & OBJET
		if($perso->getType() == 'mannequin' || $perso->getType() == 'objet')
		{
			$perso->setPv('System', 'remise', $perso->getPvMax());
			$perso->setPa($perso->getPaMax());
			$perso->setPn($perso->getPnMax());
		}
		
		//Spécificité ANIMAL
		if($perso->getType() == 'animal')
		{
			$perso->changePv('+', 3);
		}
		
		
		//Appliquer les modifications
		$perso->setPa();
		$perso->setPn();
		$perso->setPv('Remise', 'Remise');
		Member_He::add('System', $perso->getId(), 'remise', 'Remise de PA/PV/PN effectuée.');
		
		
		
	}
	
	
	
	
	private static function remisePersoDrogue(&$perso)
	{
	
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		if(DEBUG_MODE)
			self::$debugInfo .=  "d";
		//Update Drogues (Retirer une remise si la drogue est consommée)
		$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
					. ' LEFT JOIN ' . DB_PREFIX . 'item_db ON (db_id=inv_dbid)'
					. ' SET inv_remiseleft=inv_remiseleft-1'
					. ' WHERE db_type="drogue"'
						. ' AND inv_remiseleft IS NOT NULL'
						. ' AND inv_equip="1"'
						. ' AND inv_persoid=:persoId;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',		$perso->getId(),		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		
		//SHOCK EFFECT de la drogue (effect secondaire)
		$query = 'SELECT *'
					. ' FROM ' . DB_PREFIX . 'item_inv'
					. ' LEFT JOIN ' . DB_PREFIX . 'item_db ON (db_id=inv_dbid)'
					. ' WHERE inv_remiseleft=0'
						. ' AND db_type="drogue"'
						. ' AND inv_equip="1"'
						. ' AND inv_persoid=:persoId;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',		$perso->getId(),		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrItem = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		

		$query = 'DELETE FROM ' . DB_PREFIX . 'item_inv'
					. ' WHERE inv_id=:invId'
					. ' LIMIT 1;';
		$prepDel = $db->prepare($query);

		$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
					. ' SET inv_remiseleft = NULL'
					. ' WHERE inv_id=:invId'
					. ' LIMIT 1;';
		$prepUpd = $db->prepare($query);
		
		foreach($arrItem as $arr)
		{
			$perso->changePa('-', $arr['db_shock_pa']);
			$perso->changePv('-', $arr['db_shock_pv']);
			
			//Effacer cette drogue si elle est expirée
			if($arr['inv_qte']==0)
			{
				$prepDel->bindValue(':invId',		$arr['inv_id'],		PDO::PARAM_INT);
				$prepDel->execute($db, __FILE__, __LINE__);
			}
			else
			{
				
				$prepUpd->bindValue(':invId',		$arr['inv_id'],		PDO::PARAM_INT);
				$prepUpd->execute($db, __FILE__, __LINE__);
			}
		}
		$prepDel->closeCursor();
		$prepDel = NULL;
		$prepUpd->closeCursor();
		$prepUpd = NULL;
		
		//Remettre le remiseleft à null si il reste des quantités non-consomé (dans le cas ou la drogue est pas effacée)
		$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
					. ' LEFT JOIN ' . DB_PREFIX . 'item_db ON (db_id=inv_dbid)'
					. ' SET inv_remiseleft=null'
					. ' WHERE inv_qte>0'
						. ' AND inv_remiseleft=0'
						. ' AND db_type="drogue";';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		if(DEBUG_MODE)
			self::$debugInfo .=  "d2";
	}
	
	
	
	
	
	
	
	private static function remisePersoMenotte(&$perso, $expir)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		if(DEBUG_MODE)
			self::$debugInfo .=  "m";
			
		//Refuser d'être menotté pour les demandes expirées
		$query = 'SELECT m.*, i.inv_persoid'
					. ' FROM ' . DB_PREFIX . 'perso_menotte as m'
					. ' LEFT JOIN ' . DB_PREFIX . 'item_inv as i'
						. ' ON (i.inv_id = m.inv_id)'
					. ' WHERE to_id=:persoId'
						. ' AND expiration<:expir;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',		$perso->getId(),		PDO::PARAM_INT);
		$prep->bindValue(':expir',			$expir,					PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrMenotte = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		$query = 'DELETE'
					. ' FROM ' . DB_PREFIX . 'perso_menotte'
					. ' WHERE to_id=:persoId'
						. ' AND inv_id=:itemId;';
		$prepDel = $db->prepare($query);
		
		foreach($arrMenotte as &$arr)
		{
			
			//Envoyer un message informant le menotteur que le délais est expiré
			Member_He::add('System', $arr['to_id'], 'menotte', '[HJ: Vous n\'avez pas répondu en temps à une demande de menottage, elle est donc considérée comme refusée. Merci d\'essayer d\'éviter le plus possible de laisser des RP trainer en longueur, car le rythme de jeu s\'en ressent.]');

			if(!empty($arr['inv_persoid']))
				Member_He::add('System', $arr['inv_persoid'], 'menotte', '[HJ: Une demande de menottage que vous avez faite à expirée, elle est considérée comme refusée.]');
			
			//Supprimer le menottage
			$prepDel->bindValue(':persoId',		$arr['to_id'],		PDO::PARAM_INT);
			$prepDel->bindValue(':itemId',		$arr['inv_id'],		PDO::PARAM_INT);
			$prepDel->execute($db, __FILE__, __LINE__);
			
		}

		$prepDel->closeCursor();
		$prepDel = NULL;

		
		if(DEBUG_MODE)
			self::$debugInfo .=  "m2";
	}
	
	private static function remisePersoFouille(&$perso, $expir)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		if(DEBUG_MODE)
			self::$debugInfo .=  "f";
			
		//Refuser d'être menotté pour les demandes expirées
		$query = 'SELECT *'
					. ' FROM ' . DB_PREFIX . 'perso_fouille'
					. ' WHERE toid=:persoId'
						. ' AND expiration<:expir;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',		$perso->getId(),		PDO::PARAM_INT);
		$prep->bindValue(':expir',			$expir,					PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrFouille = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		
		$query = 'DELETE'
					. ' FROM ' . DB_PREFIX . 'perso_fouille'
					. ' WHERE toid=:toId'
						. ' AND fromid=:fromId;';
		$prepDel = $db->prepare($query);
		
		foreach($arrFouille as $arr)
		{
			
			//Envoyer un message informant le menotteur que le délais est expiré
			Member_He::add('System', $arr['toid'], 'fouille', '[HJ: Vous n\'avez pas répondu en temps à une demande de fouille, elle est donc considérée comme refusée. Merci d\'essayer d\'éviter le plus possible de laisser des RP trainer en longueur, car le rythme de jeu s\'en ressent.]');
			Member_He::add('System', $arr['fromid'], 'fouille', '[HJ: Une demande de fouille que vous avez faite à expirée, elle est considérée comme refusée.]');
			
			//Supprimer le menottage
			$prepDel->bindValue(':toId',		$arr['toid'],		PDO::PARAM_INT);
			$prepDel->bindValue(':fromId',		$arr['fromid'],		PDO::PARAM_INT);
			$prepDel->execute($db, __FILE__, __LINE__);
		}

		$prepDel->closeCursor();
		$prepDel = NULL;

		
		if(DEBUG_MODE)
			self::$debugInfo .=  "f2";
	}
}

