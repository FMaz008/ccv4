<?php
/** Transfert d'item(s) vers un lieu
*
* @package Mj
*/

class Mj_Perso_InventaireDonnerLieu
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{	//BUT: Démarrer un template propre à cette page
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_POST['lieuTech']))
			return fctErrorMSG('Vous devez sélectionner un lieu.', '?mj=Perso_Inventaire&id=' . $_GET['id'],null,false);
			
		if(!isset($_POST['invId']))
			return fctErrorMSG('Vous devez sélectionner au moins un objet.', '?mj=Perso_Inventaire&id=' . $_GET['id'],null,false);

		//Instancier le lieu
		try
		{
			$lieu = Member_LieuFactory::createFromNomTech($_POST['lieuTech']);
		}
		catch(Exception $e)
		{
			return fctErrorMSG($e->getMessage());
		}
		
		
		foreach ($_POST['invId'] as $itemId)
		{
			
			
			//Si l'item ne supporte pas la gestion de quantité, simplement considérer sa quantité comme étant de 1.
			if(!isset($_POST['inv' . $itemId]))
				$_POST['inv' . $itemId] = 1;
			
			//Créer l'objet item
			$item = Member_ItemFactory::createFromInvId($itemId);
			
			//Transférer l'item
			$item->transfererVersLieu($lieu, $_POST['inv' . $itemId]);
			
		}
		
		
		//Retourner le template complété/rempli
		$tpl->set('PAGE', 'Perso_Inventaire&id=' . $_GET['id']);
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/redirect.htm',__FILE__,__LINE__);
	}
}

