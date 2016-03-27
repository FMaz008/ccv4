<?php
/** Recyclage des Items, but: vendre l'item et donner de l'argent au proprio
*
* @package Member_Action
*/
class Member_Action_Lieu_Recycler2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl = '?popup=1&amp;m=Action_Lieu_Recycler';
		
		
		$coutPa=5; //Cout de l'action

		if(isset($_POST['march']))
			$coutPa+=10; //Cout du marchandage
		
		$remiseTotale=0.60; // Pourcentage de retour sur la valeur de l'objet. 60% = 60$ pour un objet de 100$.
		
		
		if($perso->getPa() <= $coutPa)
			return fctErrorMSG('Vous n\'avez pas assez de PA pour effectuer cette action.', $errorUrl);
		
		
		
		//Vérifier que le lieu dans lequel est le perso permet bien de recycler les items
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'lieu_menu`'
				. ' WHERE lieutech=:lieuTech'
					. ' AND url="Recycler"'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuTech',		$perso->getLieu()->getNomTech(),			PDO::PARAM_STR);
		$prep->executePlus($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

		if($arr === false)
			return fctErrorMSG('Vous n\'êtes pas dans un lieu permettant ce type d\'action.', $errorUrl);	
		
		
		
		
		
		
		$vendre=true;
		$vendu = 0;
		$totalProfit = 0;
		$message='';
		
		
		//Essayer de négocier le taux de remise
		if(isset($_POST['march']))
		{
			$de = round(rand(1, 100));	
			$reussite =  $perso->getChancesReussite("MRCH");
	
			$message = "(négo) ";
			if($de < $reussite) //On réussit le marchandage/négociation
			{
				//Calcul du nouveau % de remise		
				$percReussite =	$reussite/100;
				$remiseTotale += (10*$percReussite)/100;	
			}
			else
			{
				Member_He::add(NULL, $perso->getId(), 'Recyclage',"La négociation a échoué." ,HE_AUCUN, HE_UNIQUEMENT_MOI);
				$vendre=false;
			}	
	
		}
		
		
		
		
		if($vendre) //En cas de négociation échouée, on ne continue pas la transaction.
		{
			$message .= "Vous vendez:";
			
			//On récupère dans l'inventaire, les objets que l'utilisateur a sélectionné
			$i=0;
			$objets=array();
			while( $item = $perso->getInventaire($i++))
			{
				//Vérifier si l'item fait parti de ceux que l'on désire transférer
				if(isset($_POST[$item->getInvId() . '_qte']))
				{
					$qte = $_POST[$item->getInvId() . '_qte'];
					if(is_numeric($qte) && $qte > 0)
					{
						//Valider si la quantité à tranférer ne dépasse pas la quantité possédée
						if ($item->getQte() < $qte)
							return fctErrorMSG('Vous ne pouvez pas transférer plus que vous possèdez.', $errorUrl);
		
						//Valider si l'item est non-équipée
						if($item->isEquip())
							return fctErrorMSG('Un item (' . $item->getNom() . ') dans votre sac a dos apparait comme équipé. Veuillez contacter un MJ par PPA.', $errorUrl);
				
						
						//Définir des variables simplifiées
						$prixOriginal = $item->getDbPrix();
						$qteInv = $item->getQte();
						$qteVente = $qte;
						
						
						//Calculer le profit sur cet item(s)
						$prixVente = round(($prixOriginal * $remiseTotale) * $qteVente);
						$perso->changeCash("+",$prixVente);
						$totalProfit+=$prixVente;
						
						//Retirer définitivement les items de la circulation (du jeu)
						$qteRestante = $qteInv - $qteVente;
						if($qteRestante == 0)
						{
							$query = 'DELETE FROM ' . DB_PREFIX . 'item_inv'
									. ' WHERE inv_id=:itemId'
									. ' LIMIT 1';
							$prep = $db->prepare($query);
							$prep->bindValue(':itemId',		$item->getInvId(),			PDO::PARAM_INT);
							$prep->executePlus($db, __FILE__, __LINE__);
							$prep->closeCursor();
							$prep = NULL;

						}
						else
						{
							$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
									. ' SET inv_qte=:qte'
									. ' WHERE inv_id=:itemId'
									. ' LIMIT 1';
							$prep = $db->prepare($query);
							$prep->bindValue(':qte',		$qteRestante,			PDO::PARAM_INT);
							$prep->bindValue(':itemId',		$item->getInvId(),		PDO::PARAM_INT);
							$prep->executePlus($db, __FILE__, __LINE__);
							$prep->closeCursor();
							$prep = NULL;	
						}
						
						
						//Ajouter un message
						$message .= "\n-" . $qteVente . "x" . $item->getNom() . ', pour : ' . fctCreditFormat($prixVente, true);
						
						$vendu++;
					}
				}
			}
			
			//Compiler les résultats
			$message .= "\nTotal : " . $vendu . ' item(s) pour ' . fctCreditFormat($totalProfit, true);
			Member_He::add(NULL, $perso->getId(), 'Système',$message ,HE_AUCUN, HE_TOUS);
			$perso->setCash();


		}
		
		
		//Soustraire les PA
		$perso->changePa  ('-', $coutPa);
		$perso->setPa();
				
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}


