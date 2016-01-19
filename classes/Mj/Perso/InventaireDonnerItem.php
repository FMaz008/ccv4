<?php
/** Transfert d'item(s) vers un item
*
* @package Mj
*/

class Mj_Perso_InventaireDonnerItem
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_POST['itemId']))
			return fctErrorMSG('Vous devez sélectionner un item.', '?mj=Perso_Inventaire&id=' . $_GET['id'],null,false);
			
		if(!isset($_POST['invId']))
			return fctErrorMSG('Vous devez sélectionner au moins un objet.', '?mj=Perso_Inventaire&id=' . $_GET['id'],null,false);

		//Créer l'objet item (celui à transférer)
		$itemTo = Member_ItemFactory::createFromInvId($itemId);
		
		foreach ($_POST['invId'] as $itemId)
		{
			
			
			//Si l'item ne supporte pas la gestion de quantité, simplement considérer sa quantité comme étant de 1.
			if(!isset($_POST['inv' . $itemId]))
				$_POST['inv' . $itemId] = 1;
			
			//Créer l'objet item à transférer
			$item = Member_ItemFactory::createFromInvId($itemId);
			
			
			$item->transfererVersItem($itemTo, $_POST['inv' . $itemId]);
			
			
		}
		
		
		//Retourner le template complété/rempli
		$tpl->set('PAGE', 'Perso_Inventaire&id=' . $_GET['id']);
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/redirect.htm',__FILE__,__LINE__);
	}
}

