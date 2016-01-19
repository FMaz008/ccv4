<?php
/** Transfert d'item(s) vers un item
*
* @package Mj
*/

class Mj_Lieu_InventaireDonnerItem
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_POST['itemId']))
			return fctErrorMSG('Vous devez sélectionner un item.', '?mj=Lieu_Inventaire&id=' . $_GET['id'],null,false);
			
		//Créer l'objet item vers/dans lequel transférer les items.
		$itemTo = Member_ItemFactory::createFromInvId($_POST['itemId']);
		
		foreach ($_POST['invId'] as $itemId)
		{
			
			
			//Si l'item ne supporte pas la gestion de quantité, simplement considérer sa quantité comme étant de 1.
			if(!isset($_POST['inv' . $itemId]))
				$_POST['inv' . $itemId] = 1;
			
			//Créer l'objet item à transférer
			$item = Member_ItemFactory::createFromInvId($itemId);

			
			//Si l'item est caché, le dé-cacher
			if($item->iscache())
				$item->decacherObjet();
			
			$item->transfererVersItem($itemTo, $_POST['inv' . $itemId]);
			
			
		}
		
		
		//Retourner le template complété/rempli
		$tpl->set('PAGE', 'Lieu_Inventaire&id=' . $_GET['id']);
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/redirect.htm',__FILE__,__LINE__);
	}
}

