<?php
/** Gestion des statistiques du jeu
*
* @package Mj
*/

class Mj_Perso_GestionStatDel
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_POST['id']))
			return fctErrorMSG('Vous devez sélectionner une statistique.', '?mj=Perso_GestionStat',null,false);
		
		
		
			
		//Retirer les modificateurs de comp
		$query = 'DELETE FROM ' . DB_PREFIX . 'competence_stat
					WHERE statid=:id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',	$_POST['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;

		
		
		//La retirer aux perso
		$query = 'DELETE FROM ' . DB_PREFIX . 'perso_stat
					WHERE statid=:id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',	$_POST['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;

		//Supprimer la caractéristique
		$query = 'DELETE FROM ' . DB_PREFIX . 'stat
					WHERE id=' . mysql_real_escape_string($_POST['id']) . '
					LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',	$_POST['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
	
		//Retourner le template complété/rempli
		$tpl->set('PAGE', 'Perso_GestionStat');
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/redirect.htm',__FILE__,__LINE__);
	}
	

}

