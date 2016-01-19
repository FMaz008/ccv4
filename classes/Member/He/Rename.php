<?php
/**
 * AJAX: Gestion des actions de renommage d'un personnage
 * 
 * Recoit une requête AJAX et la traite. Aucun retour.
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Member
 * @subpackage Ajax
 */
 


class Member_He_Rename
{
	function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_POST['persoId']))
			die ('ERREUR: Aucun Id de perso à renommer spécifié.');
		
		if (!isset($_POST['newName']))
			die ('ERREUR: Aucun nouveau nom spécifié.');
			
		$_POST['newName'] = trim($_POST['newName']);
		if(strtolower($_POST['newName']) == 'inconnu' || strtolower($_POST['newName']) == 'inconnue' || empty($_POST['newName']))
			$deleteNewName = true;
		
		
		//Vérifier si le personnage est déjà connu
		$query = 'SELECT id, nom'
				. ' FROM ' . DB_PREFIX . 'perso_connu'
				. ' WHERE	persoid=:fromId'
					. ' AND nomid=:whoId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':fromId',		$perso->getId(),		PDO::PARAM_INT);
		$prep->bindValue(':whoId',		$_POST['persoId'],		PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		
		if ($arr === false)
		{
			//Un total inconnu innexistant, le nommer pour une première fois
			
			if (!isset($deleteNewName))
			{
				$query = 'INSERT INTO ' . DB_PREFIX . 'perso_connu'
						. ' (`persoid`, `nomid`, `nom`)'
						. ' VALUES'
						. ' (:fromId,  :whoId,  :nom);';
				$prep = $db->prepare($query);
				$prep->bindValue(':fromId',		$perso->getId(),					PDO::PARAM_INT);
				$prep->bindValue(':whoId',		$_POST['persoId'],					PDO::PARAM_INT);
				$prep->bindValue(':nom',		rawurldecode($_POST['newName']),	PDO::PARAM_STR);
				$prep->execute($db, __FILE__,__LINE__);
			}
			
		}
		else
		{
			//Il est déjà connu; Vérifier si on doit renommer ou effacer
			
			if (isset($deleteNewName))
			{
			
				//Effacer la connaissance
				$query = 'DELETE FROM ' . DB_PREFIX . 'perso_connu'
						. ' WHERE	persoid=:fromId'
							. ' AND nomid=:whoId'
						. ' LIMIT 1;';
				$prep = $db->prepare($query);
				$prep->bindValue(':fromId',		$perso->getId(),	PDO::PARAM_INT);
				$prep->bindValue(':whoId',		$_POST['persoId'],	PDO::PARAM_INT);
				$prep->execute($db, __FILE__,__LINE__);
				
			}
			else
			{
			
				//Renommer
				$query = 'UPDATE ' . DB_PREFIX . 'perso_connu'
						. ' SET nom=:nom'
						. ' WHERE	persoid=:fromId'
							. ' AND nomid=:whoId'
						. ' LIMIT 1;';
				$prep = $db->prepare($query);
				$prep->bindValue(':fromId',		$perso->getId(),					PDO::PARAM_INT);
				$prep->bindValue(':whoId',		$_POST['persoId'],					PDO::PARAM_INT);
				$prep->bindValue(':nom',		rawurldecode($_POST['newName']),	PDO::PARAM_STR);
				$prep->execute($db, __FILE__,__LINE__);
			}
			
			
		}
		
		//Retourner le template complété/rempli
		die('OK');
	}
}

