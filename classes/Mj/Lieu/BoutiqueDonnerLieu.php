<?php
/** Transfert d'item(s) vers un lieu
*
* @package Mj
*/

class Mj_Lieu_BoutiqueDonnerLieu
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_POST['lieuTech']))
			return fctErrorMSG('Vous devez sélectionner un lieu.', '?mj=Lieu_Boutique&id=' . $_GET['id'],null,false);
			
		//Créer l'objet Lieu (à qui donner l'item)
		$lieu = Member_LieuFactory::createFromNomTech($_POST['lieuTech']);
		
		foreach ($_POST['invId'] as $itemId)
		{
			
			
			//Si l'item ne supporte pas la gestion de quantité, simplement considérer sa quantité comme étant de 1.
			if(!isset($_POST['inv' . $itemId]))
				$_POST['inv' . $itemId] = 1;
			
			//Créer l'objet item à transférer
			$item = Member_ItemFactory::createFromInvId($itemId);
			
			
			$item->transfererVersLieu($lieu, $_POST['inv' . $itemId]);
			
		}
		
		
		//Retourner le template complété/rempli
		$tpl->set('PAGE', 'Lieu_Boutique&id=' . (int)$_GET['id']);
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/redirect.htm',__FILE__,__LINE__);
	}
}

