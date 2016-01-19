<?php
/** Gestion de l'interface de gestion des casino
*
* @package Mj
*/

class Mj_Lieu_Casino
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'casino;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		if(count($arrAll)>0)
		{
			//Lister toutes les banques du jeu
			$arrCasino = array();
			foreach($arrAll as &$arr)
			{
				$arrCasino[] = new Member_Casino($arr);
			}
			$tpl->set('CASINOS',$arrCasino);
		}
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/Casino.htm',__FILE__,__LINE__);
	}
}

