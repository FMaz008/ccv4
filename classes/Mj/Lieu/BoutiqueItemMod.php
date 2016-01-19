<?php
/** Gestion de l'inventaire du personnage
*
* @package Mj
*/

class Mj_Lieu_BoutiqueItemMod
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		//Instancier le lieu
		try
		{
			$lieu = Member_LieuFactory::createFromId((int)$_GET['id']);
		}
		catch(Exception $e)
		{
			return fctErrorMSG($e->getMessage());
		}
		
		
			
			
		//Lister l'inventaire
		$i=0;
		$arrItem = array();
		while( $item = $lieu->getBoutiqueInventaire($i++))
		{
			
			//Sauvegarder la quantité
			if(isset($_POST['inv' . $item->getInvId()]) && is_numeric($_POST['inv' . $item->getInvId()]) && $_POST['inv' . $item->getInvId()] != $item->getQte())
				$item->setQte((int)$_POST['inv' . $item->getInvId()]);
			
			
			//Sauvegarder le prix de vente
			$prix_vente = $item->getBoutiquePrixVente();
			$prix_achat = $item->getBoutiquePrixAchat();
			
			if(isset($_POST['prixvente_' . $item->getInvId()]))
				$prix_vente = $_POST['prixvente_' . $item->getInvId()];
			
			if(isset($_POST['prixachat_' . $item->getInvId()]))
				$prix_achat =  $_POST['prixachat_' . $item->getInvId()];
			
			if($item->getBoutiquePrixVente() != $prix_vente || $item->getBoutiquePrixAchat() != $prix_achat)
			{
				$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
						. ' SET inv_boutiquePrixVente=:prixVente,'
							. ' inv_boutiquePrixAchat=:prixAchat'
						. ' WHERE inv_id=:invId'
						. ' LIMIT 1;';
				$prep = $db->prepare($query);
				$prep->bindValue(':prixVente',	$prix_vente,	PDO::PARAM_INT);
				$prep->bindValue(':prixAchat',	$prix_achat,	PDO::PARAM_INT);
				$prep->bindValue(':invId',		$item->getInvId(),	PDO::PARAM_INT);
				$prep->execute($db, __FILE__, __LINE__);
				$prep->closeCursor();
				$prep = NULL;
			}
		}
		
		//Retourner le template complété/rempli
		$tpl->set('PAGE', 'Lieu_Boutique&id=' . (int)$_GET['id'] );
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/redirect.htm',__FILE__,__LINE__);
	}
}
