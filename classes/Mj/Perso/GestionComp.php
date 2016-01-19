<?php
/** Gestion des compétences du jeu
*
* @package Mj
*/

class Mj_Perso_GestionComp
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		

		//Établir la liste des compétences
		$query = 'SELECT c.*, cs.stat_multi, s.abbr as stat_abbr'
				. ' FROM ' . DB_PREFIX . 'competence as c'
				. ' LEFT JOIN ' . DB_PREFIX . 'competence_stat as cs'
					. ' ON (cs.compid = c.id)'
				. ' LEFT JOIN ' . DB_PREFIX . 'stat as s ON (s.id = cs.statid)'
				. ' ORDER BY c.nom;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		
		$result = $db->query($query, __FILE__,__LINE__);
		$cat = array();
		$lastId=0;
		$i =0 ;
		foreach($arrAll as &$arr)
		{
			$compId = $arr['id'];
			
			//S'il s'agit d'une nouvelle compétence
			if($compId != $lastId)
			{
				$i++;
				$cat[$i] = $arr;
				$cat[$i]['nom'] = stripslashes($cat[$i]['nom']);
				$cat[$i]['desc'] = stripslashes($cat[$i]['description']);
				$cat[$i]['stats'] = '';
			}
			
			if(!empty($arr['stat_multi']))
				$cat[$i]['stats'] .= $arr['stat_multi'] . 'x' . strtoupper($arr['stat_abbr']) . ' ';
			
			if($compId != $lastId)
				$lastId = $compId;
		}
		$tpl->set('COMP', $cat);
		
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Perso/GestionComp.htm',__FILE__,__LINE__);
	}
}
