<?php
/** Gestion du transfert d'un items du casier vers le perso
*
* @package Member_Action
*/
class Member_Action_Lieu_CasierCasierVersPerso
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		$errorUrl = '?popup=1&amp;m=Action_Lieu_CasiersListe';
		
		if(!isset($_POST['id_casier']))
			return fctErrorMSG('Aucun casier sélectionné.', $errorUrl);
		
		
		//TROUVER LE CASIER
		$i=0;
		$found=false;
		while( $casier = $perso->getLieu()->getCasiers($i++))
		{
			if($casier->getId()==$_POST['id_casier']){
				$found = true;
				break;
			}
		}
		
		if(!$found)
			return fctErrorMSG('Le casier #' . $_POST['id_casier'] . ' est introuvable.', $errorUrl);
		
		
		
		
		//PROTECTION: vérifier la protection du casier
		if($casier->getProtection() != NULL)
		{
		
			//Protection par digipass
			if($casier->getProtection() == 'pass')
			{
				
				if(!isset($_POST['pass']) || $_POST['pass'] != $casier->getPass())
					return fctErrorMSG('Accès par mot de passe invalide. (Tentative de hack 1)', $errorUrl);
			
			//Protection par clef
			}elseif($casier->getProtection() == 'clef')
			{
				$accesOk = false;
				
				//Trouver la clé
				$i=0; $e=0;
				$arrClefs = array();
				while( $item = $perso->getInventaire($i++))
				{
					if($item instanceof Member_ItemClef)
					{
						$arrClefs[$e++] = $item;
						
						if(isset($_POST['clef']) && $item->getInvId() == $_POST['clef'])
						{
							if($item->getCode() == $casier->getPass())
							{
								$accesOk = true;
								break;
							}
						}
					}
				}
					
				if(!$accesOk)
					return fctErrorMSG('Accès par mot de passe invalide. (Tentative de hack 2)', $errorUrl);
				
				
			
			}
			else
			{
				 fctBugReport('Une protection d\'un casier n\'est pas prise en charge par le système.', array('CasierId:' . $casier->getId(), 'Protection:' . $casier->getProtection()), __FILE__, __LINE__);
			}
		}
		
		
		
		//LISTER L'INVENTAIRE DU CASIER une première fois pour valider les PR & calculer le nbr d'items à transférer
		$i=0;
		$itemsPr=0;
		$nbrItems=0;
		$arr = array();
		while($item = $casier->getInventaire($i++))
		{
			if(isset($_POST['qte_' . $item->getInvId()]))
			{
				$qte = $_POST['qte_' . $item->getInvId()];
				
				//Valider si la quantité est possible
				if(!is_numeric($qte) || $qte<0)
					return fctErrorMSG('Vous devez choisir une quantité à transférer numérique et supérieure à 0.', $errorUrl, array('id_casier'=>$_POST['id_casier']));
				
				if($qte>0)
				{
				
					if($qte>$item->getQte())
						return fctErrorMSG('Vous ne pouvez transférer plus que vous possèdez.', $errorUrl, array('id_casier'=>$_POST['id_casier']));
					
					$itemsPr += $item->getPr();
					$nbrItems += $qte;
				}
			}
		}
		
		//Valider si le perso a assez de PR disponible pour transférer les items
		if($perso->getPr()+$itemsPr > $perso->getPrMax())
			return fctErrorMSG('Vous n\'avez pas assez de PR de disponible pour transférer la sélection.', $errorUrl, array('id_casier'=>$_POST['id_casier']));
		
		
		//Valider si on transfert quelque chose
		if($nbrItems==0)
			return fctErrorMSG('Rien à transférer.', $errorUrl, array('id_casier'=>$_POST['id_casier']));
		
		
		//Une seconde fois pour transférer
		$i=0; $e=0;
		$itemsList = '';
		while($item = $casier->getInventaire($i++))
		{
			if(isset($_POST['qte_' . $item->getInvId()]))
			{
				$qte = $_POST['qte_' . $item->getInvId()];
				if($qte>0)
				{
					//Ajouter le nom de l'item dans la liste
					$itemsList .= (($e++>0) ? ', ' : '') . $qte . 'x[i]' . $item->getNom() . '[/i]';
				
					//Transférer l'item
					$item->transfererVersPerso($perso, $qte);
				}
			}
		}
		
		$msg = 'Vous ramassez les items suivants dans le casier \'' . $casier->getNom() . '\': ' . $itemsList . '.';
		Member_He::add(NULL, $perso->getId(), 'casier', $msg);
		
		//Rafraichir le HE
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}

