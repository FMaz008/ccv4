<?php
/** Gestion des statistiques du jeu
*
* @package Mj
*/

class Mj_Perso_GestionStat
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		

		//Établir la liste des compétences
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'stat'
				. ' ORDER BY nom;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		$cat = array();
		$i =0 ;
		foreach($arrAll as &$arr)
		{
			$cat[$i++] = $arr;
			$cat[$i-1]['nom'] = stripslashes($cat[$i-1]['nom']);
			$cat[$i-1]['desc'] = stripslashes($cat[$i-1]['description']);
		}
		$tpl->set('STAT', $cat);
		
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Perso/GestionStat.htm',__FILE__,__LINE__);
	}
}
