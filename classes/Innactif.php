<?php
/**
 * Gestion automatisée des innactifs
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package CyberCity_2034
 */
 
class Innactif
{
	
	public static function go(&$account)
	{		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$nextnow = mktime (date("H")-INNACTIVITE_TELEPORT_DELAY, date("i"), date("s"), date("m"), date("d"), date("Y"));
		$delExpir = mktime (date("H"), date("i"), date("s"), date("m"), date("d")-INNACTIVITE_DELETE_DELAY, date("Y"));
		

		try
		{
			
			//Trouver les innactifs
			$query = 'SELECT p.id'
						. ' FROM ' . DB_PREFIX . 'perso as p, ' . DB_PREFIX . 'account as a'
						. ' WHERE a.last_conn<:expiration'
							. ' AND p.userId = a.id'
							. ' AND p.lieu!=:lieu'
							. ' AND p.lieu!=:lieuVac'
						. ' LIMIT 5;';
			$prep = $db->prepare($query);
			$prep->bindValue('expiration',		$nextnow,		PDO::PARAM_INT);
			$prep->bindValue('lieu',			INNACTIVITE_TELEPORT_LOCATION,		PDO::PARAM_STR);
			$prep->bindValue('lieuVac', 		INNACTIVITE_VOLUNTARY_LOCATION, 	PDO::PARAM_STR);
			$prep->executePlus($db, __FILE__, __LINE__);
			$arrPerso = $prep->fetchAll();
			$prep->closeCursor();
			$prep = NULL;

			
			if(count($arrPerso)==0)
				return; //Ne pas essayer de supprimer des perso, on le fera au prochain innactif


			$query = 'UPDATE ' . DB_PREFIX . 'perso'
					. ' SET lieu=:lieu'
					. ' WHERE id=:persoId;';
			$prep = $db->prepare($query);
			foreach($arrPerso as &$id)
			{
				$id= (int)$id[0];
				
				//Téléporter les innactifs
				
				$prep->bindValue('lieu',	INNACTIVITE_TELEPORT_LOCATION,		PDO::PARAM_STR);
				$prep->bindValue('persoId',	$id,								PDO::PARAM_INT);
				$prep->executePlus($db, __FILE__, __LINE__);
				
				Member_He::add('System', $id, 'innact', 'Votre personnage a été téléporté pour inactivité.');
				
			}
			$prep->closeCursor();
			$prep = NULL;
		}
		catch(Exception $e)
		{
			fctBugReport('Erreur', $e->getMessage(), __FILE__, __LINE__);
		}


		try
		{
			//Trouver les trop innactifs (les supprimer)
			$query = 'SELECT p.id, p.nom, a.email'
						. ' FROM ' . DB_PREFIX . 'perso as p, ' . DB_PREFIX . 'account as a'
						. ' WHERE a.last_conn<:expiration'
							. ' AND p.userId = a.id'
							. ' AND p.lieu != :lieuVac'
						. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue('expiration',		$delExpir,		PDO::PARAM_INT);
			$prep->bindValue('lieuVac', 		INNACTIVITE_VOLUNTARY_LOCATION, 	PDO::PARAM_STR);
			$prep->executePlus($db, __FILE__, __LINE__);
			$arrPerso = $prep->fetchAll();
			$prep->closeCursor();
			$prep = NULL;

			
			if (count($arrPerso)==0)
				return; //Ne pas essayer de supprimer des perso, on le fera au prochain innactif
			
			foreach($arrPerso as &$arr)
			{
				//Apeller la fonction qui gère la suppression de perso.
				Mj_Perso_Del::delete($arr['id'], 'system');
				
				$tpl = new Template($account);
				
				//Envoyer un email de bye bye
				$tpl->set('PERSO_NOM', stripslashes($arr['nom']));
				$MSG = $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Visitor/innactivite_email.htm',__FILE__,__LINE__);
				$ret= @mail(
						$arr['email'],
						"Cybercity 2034 - Suppression",
						$MSG,
						"From: robot@cybercity2034.com\n"
						. "MIME-Version: 1.0\n"
						. "Content-type: text/html; charset=utf-8\n"
						);
			}

		}
		catch(Exception $e)
		{
			fctBugReport('Erreur', $e->getMessage(), __FILE__, __LINE__);
		}
	}
}

