<?php
/** Gestion de l'interface de gestion des PPA d'un joueur
*
* @package Mj
*/
class Mj_Perso_Listppa
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_GET['id']))
			return fctErrorMSG('Données requises manquantes.');
		
		
		//Lister les sujets
		$query = 'SELECT p.*, mj.nom as mjnom'
				. ' FROM ' . DB_PREFIX . 'ppa as p'
				. ' LEFT JOIN ' . DB_PREFIX . 'mj as mj ON (mj.id = mjid)'
				. ' WHERE	p.persoid=:persoId'
				. ' ORDER BY p.`statut` DESC, p.`date` DESC;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',		$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		$i=0;
		$arrPpa = array();
		$ouvertCount = 0;
		
		if(count($arrAll)>0)
		{
			foreach($arrAll as &$arr)
			{
				$arrPpa[$i] = $arr;
				$arrPpa[$i]['date'] = fctToGameTime($arr['date']);
				
				if ($arr['mjid'] == 0)
					$arrPpa[$i]['mjnom'] = 'aucun';
				else
					$arrPpa[$i]['mjnom'] = $arr['mjnom'];
				
				if($arr['statut']=='ouvert')
					$ouvertCount++;
				
				$i++;
			}
		}
		
		
		
		if(count($arrPpa) > 0)
			$tpl->set('PPA', $arrPpa);
		
		
		
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . '/html/Mj/Perso/Listppa.htm');
	}
}

