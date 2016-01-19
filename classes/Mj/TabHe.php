<?php
/**
 * Génère le contenu de l'onglet He
 * @package Mj
 * @subpackage Ajax
 */

class Mj_TabHe
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		
	
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		
		//Trouver les messages du HE du MJ courrant
		$query = 'SELECT *'
					. ' FROM ' . DB_PREFIX . 'mj_he'
					. ' WHERE mjId=:mjId'
					. ' ORDER BY `date` DESC'
					. ' LIMIT 100;';
		$prep = $db->prepare($query);
		$prep->bindValue(':mjId',			$mj->getId(),			PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
			
		
		$HE_ITEM = array();
		if(count($arrAll)>0)
		{
			foreach($arrAll as &$arr)
			{
				$arr['type'] = $arr['concernant_type'];
				$arr['msg'] = nl2br(stripslashes($arr['msg']));
				$arr['date'] = date('d/m/Y \à H \h i', $arr['date'] );
				$HE_ITEM[] = $arr;
			}
		}
		$tpl->set('HE',$HE_ITEM);
		
		
		echo $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/tabHe.htm');
		die();
	}
}

