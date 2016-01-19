<?php
/** Ramasser un objet présent dans un lieu
*
* @package Member_Action
*/
class Member_Action_Perso_FouillerLieu2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		$errorUrl = '?popup=1&amp;m=Action_Perso_FouillerLieu';
		
		
		//Vérifier l'état du perso
		if(!$perso->isAutonome())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.', $errorUrl);
		
		
		//Déclaration des variables pour cette action
		$totalPr = 0;
		$totalPa = 0;
		$paCostPerItem = 2;
		
		//Trouver les informations concernant les items dans le lieu
		$i=0;
		while( $item = $perso->getLieu()->getItems($i++))
		{
			
			if(isset($_POST[$item->getInvId() . '_qte']))
			{
				if(	is_numeric($_POST[$item->getInvId() . '_qte'])
					&& $_POST[$item->getInvId() . '_qte'] > 0)
				{
			
					$totalPr += $item->getPr() * $_POST[$item->getInvId() . '_qte'];
					$totalPa += $paCostPerItem;
				
				}
			
				//Valider si la quantité que le lieu possède la quantité que l'on veux transférer
				if ($item->getQte() < $_POST[$item->getInvId() . '_qte'])
					return fctErrorMSG('Vous ne pouvez pas transférer plus que le lieu contient.', $errorUrl);
			}
		}
		
		
		//Valider que l'on possède assez de PA
		if($perso->getPa() <= $totalPa)
			return fctErrorMSG('Vous n\'avez pas assez de PA.', $errorUrl);
		
		
		//Valider que l'on possède assez de PR disponible
		if (($perso->getPrMax() - $perso->getPr()) < $totalPr)
			return fctErrorMSG('Vous ne disposez pas d\'assez de Pr pour effectuer cette action.', $errorUrl);
		
		
		
		
		$i=0;
		$itemsList = '';
		while( $item = $perso->getLieu()->getItems($i++))
		{
			if(isset($_POST[$item->getInvId() . '_qte']))
			{
				$qte = $_POST[$item->getInvId() . '_qte'];
				
				if(is_numeric($qte) && $qte > 0)
				{
					$itemsList .= (empty($itemsList) ? '' : ', ') . '[i]' . $item->getNom() . '[/i] x' . $qte;
					$item->transfererVersPerso($perso, $qte);
				}
			}
		}
		
		
		$perso->refreshInventaire();
		$perso->changePa('-', $totalPa);
		$perso->setPa();
		
		
		if (!empty($itemsList))
			Member_He::add('System', $perso->getId(), 'donner', "Objet(s) ramassé(s) dans le lieu [i]" . $perso->getLieu()->getNom() . "[/i]: \n" . $itemsList);
		
		
		
		//Rafraichir le HE
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}

