<?php
/** Gestion de l'interface pour les items
*
* @package Member_Action
*/
class Mj_Item_Listall
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{	//BUT: Démarrer un template propre à cette page
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'SELECT `db_id`, `db_nom`, `db_type`, `db_soustype`, `db_valeur`, COUNT(`inv_id`) as `qte_circu`'
					. ' FROM `' . DB_PREFIX . 'item_db`'
					. ' LEFT JOIN `' . DB_PREFIX . 'item_inv` ON(`inv_dbid` = `db_id`)'
					. ' GROUP BY `db_id`'
					. ' ORDER BY `db_type`, `db_soustype`, `db_nom` ASC;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__,__LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		if(count($arrAll) > 0)
		{
			$tpl->set("ITEMS",$arrAll);
		}
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . '/html/Mj/Item/Listall.htm');
	}
}

