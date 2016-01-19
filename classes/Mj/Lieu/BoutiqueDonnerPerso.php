<?php
/** Transfert d'item(s) vers un personnage
*
* @package Mj
*/

class Mj_Lieu_BoutiqueDonnerPerso
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		
		if(!isset($_POST['persoId']))
			return fctErrorMSG('Vous devez sélectionner un perso.', '?mj=Lieu_Boutique&id=' . $_GET['id'],null,false);
		
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'perso'
				. ' WHERE id=:persoId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId', $_POST['persoId'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		//Créer l'objet perso (à qui donner l'item)
		$perso = new Member_Perso($arr); //Et charger son inventaire
		
		foreach ($_POST['invId'] as $itemId)
		{
			
			
			//Si l'item ne supporte pas la gestion de quantité, simplement considérer sa quantité comme étant de 1.
			if(!isset($_POST['inv' . $itemId]))
				$_POST['inv' . $itemId] = 1;
			
			//Créer l'objet item à transférer
			$item = Member_ItemFactory::createFromInvId($itemId);
			
			
			//Donner l'item
			$item->transfererVersPerso($perso, $_POST['inv' . $itemId]);
		}
		
		
		
		//Retourner le template complété/rempli
		$tpl->set('PAGE', 'Lieu_Boutique&id=' . (int)$_GET['id']);
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/redirect.htm',__FILE__,__LINE__);
	}
}

