<?php
/** Gestion de la suppression d'un perso
*
* @package Mj
*/
class Mj_Perso_Del
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_GET['id']) || empty($_GET['id']))
			return fctErrorMSG('Vous devez sélectionner un personnage.');
		
		
		//suppression du perso si le MJ est sûr
		if (isset($_GET['action']) && $_GET['action'] == 'suppr')
		{
			
			self::delete($_GET['id'], $mj->getNom());
			
			die("<script type=\"text/javascript\">location.href='?mj=index';</script>");
		}
		
		//Trouver les infos sur le persos :
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'perso'
				. ' WHERE id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',		$_GET['id'],		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		//envoie des infos à l'autre page (tpl)
		$tpl->set('perso',$arr);
		
		
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . '/html/Mj/Perso/Del.htm');
	}
	
	
	
	
	
	/**
	 * Supprime entièrement un personnage.
	 *
	 * @param int $id Id du personnage.
	 * @param string $delBy Identification de qui ou quoi effectue la suppression.
	 * @return true
	 */
	public static function delete($id, $delBy='')
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante

		$db->beginTransaction();

		try
		{
			$id = (int)$id;
			
			//Trouver les infos sur le persos :
			$query = 'SELECT *'
						. ' FROM ' . DB_PREFIX . 'perso'
						. ' WHERE id=:id'
						. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':id',		$id,		PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$arr = $prep->fetch();
			$prep->closeCursor();
			$prep = NULL;
			


			
			//suppression des historiques de comptes bancaires,
			//chercher le compte correspondant à l'id du perso (voir si maintien)
			$query = 'SELECT compte_compte'
					. ' FROM ' . DB_PREFIX . 'banque_comptes'
					. ' WHERE compte_idperso=:persoId;';
			$prep = $db->prepare($query);
			$prep->bindValue(':persoId',		$id,	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$arrAll = $prep->fetchAll();
			$prep->closeCursor();
			$prep = NULL;
			
			$query = 'DELETE FROM ' . DB_PREFIX . 'banque_historique'
					. ' WHERE compte=:compte;';
			$prep = $db->prepare($query);

			foreach($arrAll as &$arrOne)
			{
				$prep->bindValue(':compte',	$arrOne['compte_compte'],	PDO::PARAM_STR);
				$prep->execute($db, __FILE__, __LINE__);
			}
			
			
			//suppression des logs de tel (voir si maintien)
			$query = 'DELETE FROM ' . DB_PREFIX . 'log_telephone'
					. ' WHERE from_persoid=:persoId1'
						. ' OR to_persoid=:persoId2;';
			$prep = $db->prepare($query);
			$prep->bindValue(':persoId1',		$id,	PDO::PARAM_INT);
			$prep->bindValue(':persoId2',		$id,	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			
			$query = 'INSERT INTO ' . DB_PREFIX . 'log_persosuppr'
					. ' (`timestamp`, `perso`, `mj`)'
					. ' VALUES('
						. ' UNIX_TIMESTAMP(), :nom, :delBy'
					. ' )';
			$prep = $db->prepare($query);
			$prep->bindValue(':nom',	$arr['nom'],	PDO::PARAM_STR);
			$prep->bindValue(':delBy',	$delBy,			PDO::PARAM_STR);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			
			//suppression des lieux bannis par le perso
			$query = 'DELETE'
						. ' FROM ' . DB_PREFIX . 'lieu_ban'
						. ' WHERE persoid=:persoId;';
			$prep = $db->prepare($query);
			$prep->bindValue(':persoId',		$id,	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			//suppression des connaissances
			$query = 'DELETE'
						. ' FROM ' . DB_PREFIX . 'perso_connu'
						. ' WHERE persoid=:persoId;';
			$prep = $db->prepare($query);
			$prep->bindValue(':persoId',		$id,	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			
			//suppression des caractéristiques
			$query = 'DELETE'
						. ' FROM ' . DB_PREFIX . 'perso_caract'
						. ' WHERE persoid=:persoId;';
			$prep = $db->prepare($query);
			$prep->bindValue(':persoId',		$id,	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			//suppression des compétences
			$query = 'DELETE'
						. ' FROM ' . DB_PREFIX . 'perso_competence'
						. ' WHERE persoid=:persoId;';
			$prep = $db->prepare($query);
			$prep->bindValue(':persoId',		$id,	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			//suppression des statistiques
			$query = 'DELETE'
						. ' FROM ' . DB_PREFIX . 'perso_stat'
						. ' WHERE persoid=:persoId;';
			$prep = $db->prepare($query);
			$prep->bindValue(':persoId',		$id,	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			
			//suppression des méssages du HE (mettre le `show`=0)
			$query = 'UPDATE ' . DB_PREFIX . 'he_fromto'
						. ' SET `show`="0"'
						. ' WHERE `fromto`="from" AND persoid=:persoId;';
			$prep = $db->prepare($query);
			$prep->bindValue(':persoId',		$id,	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			
			//Supprimer les messages effacés
			//Sera effectué lors de la suppression régulière d'un message pour l'ensemble des show==0
			
			//Supprimer les PPA
			$query = 'SELECT id'
						. ' FROM ' . DB_PREFIX . 'ppa'
						. ' WHERE persoid=:persoId;';
			$prep = $db->prepare($query);
			$prep->bindValue(':persoId',		$id,	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$arrAll = $prep->fetchAll();
			$prep->closeCursor();
			$prep = NULL;


			$query = 'DELETE'
					. ' FROM ' . DB_PREFIX . 'ppa_reponses'
					. ' WHERE sujetid=:ppaId;';
			$prep = $db->prepare($query);
			foreach($arrAll as &$ppa)
			{
				$prep->bindValue(':ppaId',	$ppa['id'],	PDO::PARAM_INT);
				$prep->execute($db, __FILE__, __LINE__);
			}
			$prep->closeCursor();
			$prep = NULL;

			$query = 'DELETE'
					. ' FROM `' . DB_PREFIX . 'ppa`'
					. ' WHERE `id` = :ppaId;';
			$prep = $db->prepare($query);
			foreach($arrAll as &$ppa)
			{
				$prep->bindValue(':ppaId',	$ppa['id'],	PDO::PARAM_INT);
				$prep->execute($db, __FILE__, __LINE__);
			}
			$prep->closeCursor();
			$prep = NULL;
			
			//Dropper les items de l'inventaire au sol.
			$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
						. ' SET inv_persoid=NULL,'
							. ' inv_equip="0",'
							. ' inv_lieutech=:lieu'
						. ' WHERE inv_persoid=:persoId;';
			$prep = $db->prepare($query);
			$prep->bindValue(':lieu',		$arr['lieu'],	PDO::PARAM_STR);
			$prep->bindValue(':persoId',		$id,			PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			
			//Calculer le total de tous les comptes du perso
			$query = 'SELECT *'
						. ' FROM ' . DB_PREFIX . 'banque_comptes'
						. ' WHERE compte_idperso=:persoId;';
			$prep = $db->prepare($query);
			$prep->bindValue(':persoId',		$id,	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$arrAll = $prep->fetchAll();
			$prep->closeCursor();
			$prep = NULL;
			
			
			$argent_compte=0;
			foreach($arrAll as &$arr2)
				$argent_compte += $arr2['compte_cash'];
			
			//Ajout du total dans le fond commun
			$query = 'UPDATE ' . DB_PREFIX . 'banque_comptes'
						. ' SET compte_cash=compte_cash+:cash'
						. ' WHERE compte_id=1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':cash',	$argent_compte,		PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			
			
			
			//Création d'un item "liasse de billet" dans l'endroit où le PJ est.
			$query = 'INSERT INTO ' . DB_PREFIX . 'item_inv '
						. ' (inv_dbid,inv_lieutech,inv_param)'
						. ' VALUES ('
							. ' 2, :lieu_tech, :cash'
						. ');';
			$prep = $db->prepare($query);
			$prep->bindValue(':lieu_tech',	$arr['lieu'],	PDO::PARAM_STR);
			$prep->bindValue(':cash',		$arr['cash'],	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			//suppression du compte bancaire
			$query = 'DELETE'
						. ' FROM ' . DB_PREFIX . 'banque_comptes'
						. ' WHERE compte_idperso=:persoId;';
			$prep = $db->prepare($query);
			$prep->bindValue(':persoId',		$id,	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			//suppression du perso
			$query = 'DELETE'
						. ' FROM ' . DB_PREFIX . 'perso'
						. ' WHERE id=:persoId'
						. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':persoId',		$id,	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;

			$db->commit();

			return true;
		}
		catch(Exception $e)
		{
			fctBugRepport('Erreur', $e->getMessage(), __FILE__, __LINE__);
		}
		
	}
}

