<?php
/** Gestion de l'interface de l'inventaire du personnage
*
* @package Mj
*/

class Mj_Lieu_BoutiqueItemDel
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_POST['invId']))
			return fctErrorMSG('Vous devez sélectionner un item.');
		
		
		
		
		foreach ($_POST['invId'] as $itemId)
		{
			$query = 'DELETE FROM ' . DB_PREFIX . 'item_inv'
					. ' WHERE inv_id=:invId'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':invId',	$itemId,	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
		}
		
		
		//Retourner le template complété/rempli
		$tpl->set('PAGE', 'Lieu_Boutique&id=' . (int)$_GET['id']);
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/redirect.htm',__FILE__,__LINE__);
	}
}

