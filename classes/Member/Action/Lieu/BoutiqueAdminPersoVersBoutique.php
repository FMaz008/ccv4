<?php
/** Gestion d'une boutique par son propriétaire
*
* @package Member_Action
*/
class Member_Action_Lieu_BoutiqueAdminPersoVersBoutique
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
			
			
		//Valider si le personnage possède le droit d'administration de cette boutique
		if(!$perso->getLieu()->isGerant($perso))
			return fctErrorMSG('Vous devez être propriétaire du lieu pour pouvoir l\'administrer.', $errorUrl);
		
		
		//Valider si un item de l'inventaire du perso est sélectionné
		if (!isset($_POST['perso']))
			return fctErrorMSG('Vous devez sélectionner un item dans votre inventaire.', $errorUrl2);
		
		
		
		
		
		//Trouver les informations concernant l'item du perso
		$i=0;
		while( $item = $perso->getInventaire($i++))
		{
			if($item->getInvId() == $_POST['perso'])
			{
				$ITEM  = $item;
				break;
			}
		}
		
		//Valider si le perso possède l'item dans son inventaire
		if (!isset($ITEM))
			return fctErrorMSG('L\'item sélectionné n\'existe pas (ou ne vous appartiend pas).', $errorUrl2);
		
		
		//Valider si la quantité d'item à transférer est possible.
		if(!isset($_POST['psqte_' . $ITEM->getInvId()]))
			return fctErrorMSG('Quantité requise manquante.', $errorUrl2);
		
		
		$qte_a_transferer = $_POST['psqte_' . $ITEM->getInvId()];
		if($qte_a_transferer<0)
			return fctErrorMSG('La quantité doit être supérieure ou égale à zéro.', $errorUrl2);
		
		
		//Valider si la quantité d'item à transféré n'est pas supérieur à ce que le perso posède
		if ($qte_a_transferer > $ITEM->getQte())
			return fctErrorMSG('Vous ne pouvez pas transférer plus que le personnage possède.', $errorUrl2);
		
		
		
		//Transférer l'item dans la boutique
		$ITEM->transfererVersBoutique($perso->getLieu(), $qte_a_transferer);
		$perso->refreshInventaire();
		
		
		$tpl->set('PAGE', 'Action_Lieu_BoutiqueAdmin');
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/action_redirect.htm',__FILE__,__LINE__);
	}
}

