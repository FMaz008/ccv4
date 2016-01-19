<?php
/** Gestion de l'interface d'une boutique
*
* @package Member_Action
*/
class Member_Action_Perso_Donneritem2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		$errorUrl = '?popup=1&amp;m=Action_Perso_Donneritem';
		
		
		//Vérifier l'état du perso
		if(!$perso->isConscient())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.', $errorUrl);
		
		//Si aucun perso n'a été choisi	
		if(!isset($_POST['toPersoId']))
			return fctErrorMSG('Aucun personnage n\'a été choisi.', $errorUrl);	
		
		
		
		//Déclaration des variables pour cette action
		$totalPr = 0;
		$totalPa = 0;
		$paCostPerItem = 2;
		
		
		//Trouver les informations concernant les items dans l'inventaire
		$i=0;
		while( $item = $perso->getInventaire($i++))
		{
			
			if(isset($_POST[$item->getInvId() . '_qte'])
			&& is_numeric($_POST[$item->getInvId() . '_qte'])
			&& $_POST[$item->getInvId() . '_qte'] > 0){
			
				$totalPr += $item->getPr() * $_POST[$item->getInvId() . '_qte'];
				$totalPa += $paCostPerItem;
				
			}
			
			if ($item->getQte() < $_POST[$item->getInvId() . '_qte'])
				return fctErrorMSG('Vous ne pouvez pas transférer plus que vous possèdez.', $errorUrl);
		}
		
		
		//Valider si le perso possède assez de PA pour effectuer le transfert
		if($perso->getPa() <= $totalPa)
			return fctErrorMSG('Vous n\'avez pas assez de PA.', $errorUrl);
		
		
		
		//Vérifier si le perso à qui donner l'item est présent dans le bon lieu
		$found = false;
		$i=0;
		while($toPerso = $perso->getLieu()->getPerso($perso, $i++))
		{
			if($toPerso->getId() == $_POST['toPersoId'])
			{
				$found=true;
				break;
			}
		}
		if(!$found)
			return fctErrorMSG('Ce personnage n\'est pas dans le lieu ou vous vous trouvez actuellement.', $errorUrl);
		
		if(($toPerso->getPrMax() - $toPerso->getPr()) < $totalPr)
			return fctErrorMSG('Ce personnage n\'a pas suffisamment de PR de libre pour accepter cette transaction.', $errorUrl);
		
		
		//Effectuer les transferts
		$i=0; $itemsList = '';
		while( $item = $perso->getInventaire($i++))
		{
			if(isset($_POST[$item->getInvId() . '_qte']))
			{
				$qte = $_POST[$item->getInvId() . '_qte'];
				
				if(is_numeric($qte) && $qte > 0)
				{
				
					$itemsList .= (empty($itemsList) ? '' : ', ') . '[i]' . $item->getNom() . '[/i] x' . $qte;
					$item->transfererVersPerso($toPerso, $qte);
				}
			}
		}
		
		
		$perso->refreshInventaire();
		$perso->changePa('-', $totalPa);
		$perso->setPa();
		
		
		if (!empty($itemsList))
			Member_He::add($perso->getId(), $toPerso->getId(), 'donner', "Objet(s) donné(s):\n" . $itemsList);
		
		
		
		//Rafraichir le HE
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}

