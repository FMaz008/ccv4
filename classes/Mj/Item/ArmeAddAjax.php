<?php
/** AJAX: Liste des armes à feu
*
* @package Mj
* @subpackage Ajax
*/
class Mj_Item_ArmeAddAjax
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{	//BUT: Démarrer un template propre à cette page
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		//Générer la liste des armes associables à ces munitions
		$query = 'SELECT `db_id`, `db_nom`'
					. ' FROM `' . DB_PREFIX . 'item_db`'
					. ' WHERE `db_soustype` = "arme_feu"'
					. ' AND `db_type` = "arme";';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__,__LINE__);
		$result = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		$ARMES = array();
		foreach($result as $arr)
			$ARME[] = $arr;
		
		$tpl->set('ARMES',$ARMES);
		$tpl->set('ARMES_IDX',$_POST['divid']);
			
		$source = $tpl->fetch($account->getSkinRemotePhysicalPath() . '/html/Mj/Item/Arme_Addmod_Ajax.htm');
		die($source);
	}
}
?>
