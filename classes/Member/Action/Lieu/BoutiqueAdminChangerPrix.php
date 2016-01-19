<?php
/** Gestion d'une boutique par son propriétaire
*
* @package Member_Action
*/
class Member_Action_Lieu_BoutiqueAdminChangerPrix
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		$errorUrl = '?popup=1&amp;m=Action_Lieu_Boutique';
		
		
		//Définir les accès d'administration
		if(!$perso->getLieu()->isGerant($perso))
			return fctErrorMSG('Vous devez être propriétaire du lieu pour pouvoir l\'administrer.', $errorUrl, array('perso' => $perso, 'lieu' => $lieu));
		
		
		//Valider si le lieu actuel est une boutique
		if(!$perso->getLieu()->isBoutique())
			return fctErrorMSG('Ce lieu n\'est pas une boutique.');
			
		
		
		//LISTER TOUT LES ITEMS EN VENTE DANS LA BOUTIQUE
		$i=0;
		$query = 'UPDATE `' . DB_PREFIX . 'item_inv`'
				. ' SET `inv_boutiquePrixVente`=:prixVente,'
					. ' `inv_boutiquePrixAchat`=:prixAchat'
				. ' WHERE `inv_id`=:invId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		while( $item = $perso->getLieu()->getBoutiqueInventaire($i++))
		{
			$btvnt = str_replace(",",".",$_POST['btvnt_' . $item->getInvId()]);
			$btach = str_replace(",",".",$_POST['btach_' . $item->getInvId()]);
			$btvnt = (is_numeric($btvnt)) ? $btvnt : -1;
			$btach = (is_numeric($btach)) ? $btach : -1;
			
			$prep->bindValue(':prixVente',	$btvnt,				PDO::PARAM_INT);
			$prep->bindValue(':prixAchat',	$btach,				PDO::PARAM_INT);
			$prep->bindValue(':invId',		$item->getInvId(),	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
		}
		$prep->closeCursor();
		$prep = NULL;
		
		$tpl->set('PAGE', 'Action_Lieu_BoutiqueAdmin');
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/action_redirect.htm',__FILE__,__LINE__);
	}
}
