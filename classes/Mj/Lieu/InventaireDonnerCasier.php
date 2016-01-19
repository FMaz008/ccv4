<?php
/** Transfert d'item(s) vers un casier
*
* @package Mj
*/

class Mj_Lieu_InventaireDonnerCasier
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_POST['casierId']))
			return fctErrorMSG('Vous devez sélectionner un casier.', '?mj=Lieu_Inventaire&id=' . $_GET['id'] . '&cid=' . $_POST['casier'],null,false);
			
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'lieu_casier'
				. ' WHERE id=:casierId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':casierId',	$_POST['casierId'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		//Créer l'objet Casier (à qui donner l'item)
		$casier = new Member_Casier($arr);
		
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
			
			$item->transfererVersCasier($casier, $_POST['inv' . $itemId]);
			
		}
		
		
		//Retourner le template complété/rempli
		$tpl->set('PAGE', 'Lieu_Inventaire&id=' . $_GET['id']);
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/redirect.htm',__FILE__,__LINE__);
	}
}

