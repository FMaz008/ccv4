<?php
/** Gestion d'un casier (page d'ajout)
*
* @package Mj
*/

class Mj_Lieu_CasierAdd
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{	//BUT: Démarrer un template propre à cette page
		
		
		
		
		//Si le lieu est spécifié, le passer au template pour créer un lien de retour
		if(isset($_POST['lieu_id']))
			$tpl->set('LIEU_ID', $_POST['lieu_id']);
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		//## Sauvegarder les modifications ?
		if(isset($_POST['save']))
		{
			self::save();
		}
		
		
		
		//## Afficher les informations dans le formulaire de modification
		
		if(isset($_POST['LIEU_ID']))
		{
			//Trouver le nom technique de l'ID de lieu
			$query = 'SELECT nom_technique'
					. ' FROM ' . DB_PREFIX . 'lieu'
					. ' WHERE id=:idLieu'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':idLieu',	$_POST['LIEU_ID'],	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$arr = $prep->fetch();
			$prep->closeCursor();
			$prep = NULL;
			
			if ($arr === false)
				return fctErrorMSG('Ce lieu n\'existe pas.');
			
			$lieuTech = $arr['nom_technique'];
			$arr['lieuTech'] = $lieuTech;
			$arr['lieuId'] = (int)$_POST['LIEU_ID'];
		}
		else
		{
			$arr['lieuTech'] = 'aucun';
		}
		$arr['nom_casier']='';
		$arr['resistance_casier']='50';
		$arr['capacite_casier']='15';
		$arr['protection_casier']='0';
		$arr['pass_casier']='';
		$tpl->set('CASIER', $arr);
		
		$tpl->set('SHOWID', false);
		$tpl->set('ACTIONTYPETXT', 'Ajouter');
		$tpl->set('SUBMITNAME', 'Add');
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/CasierAddmod.htm',__FILE__,__LINE__);
	}
	
	
	
	private static function save()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		$query = 'INSERT INTO ' . DB_PREFIX . 'lieu_casier'
				. ' (nom_casier, lieuId, resistance_casier, capacite_casier, protection_casier, pass_casier)'
				. ' VALUES'
				. ' (:nom, :lieuId, :resistance, :capacite, :protection, :pass);';
		$prep = $db->prepare($query);
		$prep->bindValue(':nom',	$_POST['nom_casier'],	PDO::PARAM_STR);
		$prep->bindValue(':lieuId',	$_POST['lieu_id'],		PDO::PARAM_INT);
		$prep->bindValue(':resistance',	$_POST['resistance_casier'],		PDO::PARAM_INT);
		$prep->bindValue(':capacite',	$_POST['capacite_casier'],		PDO::PARAM_INT);
		$prep->bindValue(':protection',	$_POST['protection_casier'],		PDO::PARAM_STR);
		
		if(isset($_POST['pass_casier']))
			$prep->bindValue(':pass',	$_POST['pass_casier'],		PDO::PARAM_STR);
		else
			$prep->bindValue(':pass',	NULL,		PDO::PARAM_NULL);
		
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		if(isset($_POST['LIEU_ID']))
			die("<script type=\"text/javascript\">location.href='?mj=Lieu_Casiers&id=" . $_POST['LIEU_ID'] . "';</script>");
		else
			die("Sauvegardé.");
	}
}