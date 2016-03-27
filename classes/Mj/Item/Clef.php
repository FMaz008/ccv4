<?php
/** Gestion de l'interface pour les items de type clé
*
* @package Mj
*/
class Mj_Item_Clef
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		//Options de tri
		$arrTriable = array(
						'db_id','db_nom',
						'db_valeur','db_pr'
					);
		
		if(isset($_GET['tri']) && isset($_GET['par']))
		{
			if(!in_array($_GET['tri'], array('ASC', 'DESC'), true))
				return fctErrorMSG('Paramètre de tri invalide(1).');
			
			if(!in_array('db_' . $_GET['par'], $arrTriable, true))
				return fctErrorMSG('Paramètre de tri invalide(2).');
				
			$field = 'db_' . $_GET['par'];
			$sort = $_GET['tri'];
		}
		else
		{
			$field = 'db_soustype, db_nom';
			$sort = 'ASC';
		}
		
		$query = 'SELECT ' . implode(',',$arrTriable) . ','
					. ' COUNT(`inv_id`) as `qte_circu`'
					. ' FROM `' . DB_PREFIX . 'item_db`'
					. ' LEFT JOIN `' . DB_PREFIX . 'item_inv` ON(`inv_dbid` = `db_id`)'
					. ' WHERE `db_type` = "clef"'
					. ' GROUP BY `db_id`'
					. ' ORDER BY ' . $field . ' ' . $sort . ';';
		$prep = $db->prepare($query);
		$prep->executePlus($db, __FILE__,__LINE__);
		$result = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		if (count($result)>0)
		{
			$tpl->set("ITEMS",$result);
		}
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . '/html/Mj/Item/Clef.htm');
	}
}

