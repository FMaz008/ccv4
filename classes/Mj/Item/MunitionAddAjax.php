<?php
/** Gestion de l'interface pour les items
*
* @package Mj
*/
class Mj_Item_MunitionAddAjax
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		//Générer la liste des armes associables à ces munitions
		$query = 'SELECT `db_id`, `db_nom`'
					. ' FROM `' . DB_PREFIX . 'item_db`'
					. ' WHERE `db_soustype` = "aucun" AND `db_type` = "munition";';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__,__LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		
		$tpl->set('MUNITIONS',$arrAll);
		$tpl->set('MUNITIONS_IDX',$_POST['divid']);
			
		$source = $tpl->fetch($account->getSkinRemotePhysicalPath() . '/html/Mj/Item/Munition_Addmod_Ajax.htm');
		die($source);
	}
}
