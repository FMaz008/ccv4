<?php
/** Gestion d'un casier (page de modification)
*
* @package Mj
*/

class Mj_Lieu_CasierMod
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		if(!isset($_POST['id_casier']))
			return fctErrorMSG('Données requises manquantes.');
		
		
		//Si le lieu est spécifié, le passer au template pour créer un lien de retour
		if(isset($_POST['LIEU_ID']))
			$tpl->set('LIEU_ID', $_POST['LIEU_ID']);
		
		
		
		
		//## Sauvegarder les modifications ?
		if(isset($_POST['save']))
		{
			self::save();
		}
		
		
		
		//## Afficher les informations dans le formulaire de modification
		
		//Fetcher les informations sur le casier à modifier
		$query = 'SELECT c.*, l.nom_technique as lieuTech'
				. ' FROM ' . DB_PREFIX . 'lieu_casier as c'
				. ' LEFT JOIN ' . DB_PREFIX . 'lieu as l'
					. ' ON (l.id = c.lieuId)'
				. ' WHERE id_casier=:idCasier'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':idCasier',	$_POST['id_casier'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		
		if($arr === false)
			return fctErrorMSG('Le casier #' . $_POST['id_casier'] . ' n\'existe pas.');
		
		if(empty($arr['protection_casier']))
			$arr['protection_casier'] = '0';
			
		if(empty($arr['pass_casier']))
			$arr['pass_casier'] = '';
			
		$tpl->set('CASIER', $arr);
		
		$tpl->set('SHOWID', true);
		$tpl->set('ACTIONTYPETXT', 'Modifier');
		$tpl->set('SUBMITNAME', 'Mod');
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/CasierAddmod.htm',__FILE__,__LINE__);
	}
	
	
	
	private static function save()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		$query = 'UPDATE ' . DB_PREFIX . 'lieu_casier'
				. ' SET'
					. ' nom_casier			= :nom,'
					. ' lieuId				= :lieuId,'
					. ' resistance_casier	= :resistance,'
					. ' capacite_casier		= :capacite,'
					. ' protection_casier	= :protection,'
					. ' pass_casier			= :pass'
				. ' WHERE id_casier=:casierId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':casierId',	$_POST['id_casier'],	PDO::PARAM_INT);
		$prep->bindValue(':nom',		$_POST['nom_casier'],	PDO::PARAM_STR);
		$prep->bindValue(':lieuId',		$_POST['lieu_id'],		PDO::PARAM_INT);
		$prep->bindValue(':resistance',	$_POST['resistance_casier'],	PDO::PARAM_INT);
		$prep->bindValue(':capacite',	$_POST['capacite_casier'],	PDO::PARAM_INT);
		$prep->bindValue(':protection',	$_POST['protection_casier'],	PDO::PARAM_STR);
		
		if(isset($_POST['pass_casier']))
			$prep->bindValue(':pass',	$_POST['pass_casier'],	PDO::PARAM_STR);
		else
			$prep->bindValue(':pass',	NULL,	PDO::PARAM_NULL);
		
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		if(isset($_POST['LIEU_ID']))
			die("<script type=\"text/javascript\">location.href='?mj=Lieu_Casiers&id=" . $_POST['LIEU_ID'] . "';</script>");
		else
			die("Sauvegardé.");
	}
}
