<?php
/**
 * Génère le contenu de l'onglet PPA Fermé
 * @package Mj
 * @subpackage Ajax
 */

class Mj_TabClosedPPA
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		
	
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		
		//Trouver les PPA associé au MJ
		$query = 'SELECT m.*, p.nom as perso'
				. ' FROM ' . DB_PREFIX . 'ppa as m'
				. ' LEFT JOIN ' . DB_PREFIX . 'perso as p ON (p.id = m.persoid)'
				. ' WHERE	m.mjid=:mjId'
					. ' AND m.statut="ferme"'
				. ' ORDER BY m.`date` DESC;';
		$prep = $db->prepare($query);
		$prep->bindValue(':mjId',			$mj->getId(),			PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
			
		$CLOSED_PPA_MJ = array();
		if(count($arrAll)>0)
		{
			
			foreach($arrAll as &$arr)
			{
				$arr['date'] = fctToGameTime($arr['date']);
				$CLOSED_PPA_MJ[] = $arr;
			}
		}
		$tpl->set('CLOSED_PPA_MJ', $CLOSED_PPA_MJ);
		
		
		
		echo $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/tabClosedPPA.htm');
		die();
	}
}


