<?php
/** Gestion d'une boutique par son propriétaire
*
* @package Member_Action
*/
class Member_Action_Lieu_BoutiqueAdminBoutiqueVersPerso
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		$errorUrl = '?popup=1&amp;m=Action_Lieu_Boutique';
		$errorUrl2 = '?popup=1&amp;m=Action_Lieu_BoutiqueAdmin';
		
		
		//Valider si le lieu actuel est une boutique
		if(!$perso->getLieu()->isBoutique())
			return fctErrorMSG('Ce lieu n\'est pas une boutique.');
		
		
		//Définir les accès d'administration
		if(!$perso->getLieu()->isGerant($perso))
			return fctErrorMSG('Vous devez être propriétaire du lieu pour pouvoir l\'administrer.', $errorUrl);
		
		
		//Valider si un item de l'inventaire du perso est sélectionné
		if (!isset($_POST['boutique']))
			return fctErrorMSG('Vous devez sélectionner un item dans votre inventaire.', $errorUrl2);
		
		
		
		
		
		//Trouver les informations concernant l'item de la boutique
		$i=0;
		while( $item = $perso->getLieu()->getBoutiqueInventaire($i++))
		{
			if($item->getInvId() == $_POST['boutique'])
			{
				$ITEM_BOUTIQUE = $item;
				break;
			}
		}
		
		
		//Valider l'item a été trouvé dans l'inventaire de la boutique
		if (!isset($ITEM_BOUTIQUE))
			return fctErrorMSG('L\'item sélectionné n\'existe pas (ou n\'est pas en boutique).', $errorUrl2);
				
		//Valider si la quantité à transférer est définie
		if(!isset($_POST['btqte_' . $ITEM_BOUTIQUE->getInvId()]))
			return fctErrorMSG('Quantité requise manquante.', $errorUrl2);
		
		
		$qte_a_transferer = $_POST['btqte_' . $ITEM_BOUTIQUE->getInvId()];
		if($qte_a_transferer<0)
			return fctErrorMSG('La quantité doit être supérieure ou égale à zéro.', $errorUrl2);
		
		
		//Vérifier si la quantité d'item à transféré n'est pas supérieur à ce que la boutique posède
		if ($qte_a_transferer > $ITEM_BOUTIQUE->getQte())
			return fctErrorMSG('Vous ne pouvez pas transférer plus que la boutique contient.', $errorUrl2);
		
		//Valider les PR
		$prTotal = $qte_a_transferer * $item->getPr();
		if($prTotal > ($perso->getPrMax() - $perso->getPr()))
			return fctErrorMSG('Vous n\'avez pas assez de PR.', $errorUrl2);
		
		//Tranférer l'item de la boutique vers l'inventaire du perso
		$ITEM_BOUTIQUE->transfererVersPerso($perso, $qte_a_transferer);
		$perso->refreshInventaire();
		
		
		$tpl->set('PAGE', 'Action_Lieu_BoutiqueAdmin');
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/action_redirect.htm',__FILE__,__LINE__);
	}
}
