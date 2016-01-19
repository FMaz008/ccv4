<?php
/** Gestion du transfert d'un item du perso vers le casier 
*
* @package Member_Action
*/
class Member_Action_Lieu_CasierPersoVersCasier
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		$errorUrl = '?popup=1&amp;m=Action_Lieu_CasiersListe';
		$errorUrlInv = '?popup=1&amp;m=Action_Lieu_CasierInv';
		
		
		//Valider si le casier est défini
		if(!isset($_POST['id_casier']))
			return fctErrorMSG('Aucun casier sélectionné.', $errorUrl);
		
		
		//Valider si le casier appartient au lieu du perso
		$i=0;
		$found=false;
		while( $casier = $perso->getLieu()->getCasiers($i++))
		{
			if($casier->getId()==$_POST['id_casier'])
			{
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
		
		
		
		//LISTER L'INVENTAIRE DU CASIER une première fois pour valider les PR
		$i=0;
		$itemsPr=0;
		$nbrItems=0;
		$arr = array();
		while($item = $perso->getInventaire($i++))
		{
			if(isset($_POST['qte_' . $item->getInvId()]) && $_POST['qte_' . $item->getInvId()]>0)
			{
				$itemsPr+=$item->getPr();
				$nbrItems+=$_POST['qte_' . $item->getInvId()];
			}
		}
		
		if($casier->getPr()+$itemsPr > $casier->getCapacite())
			return fctErrorMSG('Le casier n\'a pas assez de PR de disponible pour transférer la sélection.', $errorUrlInv, array('id_casier'=>$_POST['id_casier']));
		
		if($nbrItems==0)
			return fctErrorMSG('Rien à transférer.', $errorUrlInv, array('id_casier'=>$_POST['id_casier']));
		
		
		//Une seconde fois pour transférer
		$i=0; $e=0;
		$msg = 'Vous placez les items suivants dans le casier \'' . $casier->getNom() . '\': ';
		while($item = $perso->getInventaire($i++))
		{
			if(isset($_POST['qte_' . $item->getInvId()]) && $_POST['qte_' . $item->getInvId()]>0){
				if ($e>0)
					$msg .= ', ';
				$msg .= $item->getNom();
				
				$item->transfererVersCasier($casier, $_POST['qte_' . $item->getInvId()]);
			}
		}
		$msg .= '.';
		
		
		Member_He::add(NULL, $perso->getId(), 'casier', $msg);
		
		//Rafraichir le HE
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}

