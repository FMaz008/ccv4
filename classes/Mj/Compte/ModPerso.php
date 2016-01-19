<?php
/** Afficher les personnages lié aux compte
*
* @package Mj
*/

class Mj_Compte_ModPerso
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		$query = 'SELECT p.*'
				. ' FROM ' . DB_PREFIX . 'account as a'
				. ' LEFT JOIN ' . DB_PREFIX . 'perso as p ON (p.userId = a.id)'
				. ' WHERE a.id=:id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',	$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$PERSO = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		if (count($PERSO)>0)
			$tpl->set('PERSO',$PERSO);
		
		
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Compte/ModPerso.htm');
	}
}

