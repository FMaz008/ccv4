<?php
/** Gestion de l'interface d'une boutique
*
* @package Member_Action
*/
class Member_Action_Lieu_Boutique2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl = '?popup=1&amp;m=Action_Lieu_Boutique';
		
		
		//Déclaration des variables pour cette action
		$pacost = array();
		$pacost['achat'] = 5;
		$pacost['nego'] = 20;
		$pacost['vol'] = 40;
		$msg = '';
		
		
		
		//Vérifier l'état du perso
		if(!$perso->isAutonome())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.', $errorUrl);
		
		
		//Valider si le lieu actuel est une boutique
		if(!$perso->getLieu()->isBoutique())
			return fctErrorMSG('Ce lieu n\'est pas une boutique.');
		
		
		//Valider si le mode d'achat à été sélectionn.
		if (!isset($_POST['achat_type']))
			return fctErrorMSG('Vous n\'avez pas sélectionné le mode d\'achat.', $errorUrl);
			
		//Valider si le mode d'achat est possible:
		if($_POST['achat_type']!='achat' && $_POST['achat_type']!='nego' && $_POST['achat_type']!='vol')
			return fctErrorMSG('Le mode d\'achat est invalide.', $errorUrl);
			
		
		//Valider si le type de paiement à été sélectionné (dans le cas ou on ne commet pas un vol)
		if (!isset($_POST['pay_type']) && $_POST['achat_type']!='vol')
			return fctErrorMSG('Vous n\'avez pas sélectionné le mode de paiement.', $errorUrl);
		
		
		//Valider si le perso possède assez de PA
		if ($perso->getPa() <= $pacost[$_POST['achat_type']])
			return fctErrorMSG('Vous n\'avez pas assez de PA.', $errorUrl);
		
		//Variables des totaux
		$prTotal=0;
		$prixTotal=0;	//Achat
		$itemsList='';	//Achat
		$prixVenteTotal=0;	//Vente
		$itemsVenteList='';	//Vente
		
		$marchandage = false;
		
		
		//... Des items à vendre
		$i=0;
		$vente=false;
		while( $item = $perso->getInventaire($i++))
		{
			if(isset($_POST['itemv' . $item->getInvId()]))
			{
				//Valider que le perso possède l'item
				$found=false;
				$b=0;
				while( $bitem = $perso->getLieu()->getBoutiqueInventaire($b++))
				{
					if($bitem->getDbId()==$item->getDbId())
					{
						$found=true;
						break;
					}
				}
				if(!$found)
					return fctErrorMSG('Vous tentez de vendre un item que la boutique ne veux pas.',$errorUrl);

				
				//Valider si la quantité est possible
				$qte = $_POST['itemv' . $item->getInvId()];
				$qte = round($qte);
				
				if(!is_numeric($qte) || $qte<0 )
					return fctErrorMSG('Les quantités doivent être numérique et supérieures à zéro.',$errorUrl);
					
				if($qte > $item->getQte())
					return fctErrorMSG('Vous ne pouvez vendre plus d\'item que vous n\'avez.',$errorUrl);
				
				if($qte>0)
				{
					//Calculer les totaux
					$prTotal-= $item->getPr();
					$prixVenteTotal+= $bitem->getBoutiquePrixAchat()*$qte;

					//Générer la liste des items pour l'inclure dans le message de conclusion
					$itemsVenteList .=  ($itemsVenteList!='' ? ',' : '') . $qte . 'x[i]' . $item->getNom() . '[/i]';
					
					$vente = true;
					
				}
			}
		}
		
		//Valider les items ... à acheter
		$i=0; 
		$achat = false;
		while( $item = $perso->getLieu()->getBoutiqueInventaire($i++))
		{
			if(isset($_POST['itema' . $item->getInvId()]))
			{
				$qte = $_POST['itema' . $item->getInvId()];
				$qte = round($qte);
				
				//Valider si la quantité est possible
				if(!is_numeric($qte) || $qte<0 )
					return fctErrorMSG('Les quantités doivent être numérique et supérieures à zéro.',$errorUrl);

				if($qte > $item->getQte())
					return fctErrorMSG('Vous ne pouvez acheter plus d\'items qu\'il y en a de proposés.',$errorUrl);
					
				if($qte>0)
				{
					//Calculer les totaux
					$prTotal+= $item->getPr();
					$prixTotal+= $item->getBoutiquePrixVente()*$qte;
					
					//Générer la liste des items pour l'inclure dans le message de conclusion
					$itemsList .=  ($itemsList!='' ? ',' : '') . $qte . 'x[i]' . $item->getNom() . '[/i]';
					
					$achat=true;
				}
			}
		}



		
		//Valider si le perso possède assez de PR disponible
		if($perso->getPr() <= $perso->getPrMax() && $perso->getPrMax()-$perso->getPr() < $prTotal)
			return fctErrorMSG('Vous n\'avez pas assez de PR. (' . $prTotal . ' requis, ' . ($perso->getPrMax()-$perso->getPr()) . ' disponible)');
		
		
		//Valider si le joueur a sélectionné des items à vendre ou à achaté
		// ( éviter le cas ou il clique 'achat' sans rien sélectionner )
        if ( $_POST['buy'] && !$vente && !$achat )
                return fctErrorMSG('Vous n\'avez sélectionné aucun objet.', $errorUrl);

		
		
		//Valider si la personne tente de vendre et de voler en meme temps
		//(elle ne recevrait pas l'argent de ses ventes)
		if($vente && $_POST['achat_type']=='vol')
			return fctErrorMSG('Vous ne pouvez pas vendre et voler dans la même action.', $errorUrl);


		if($_POST['achat_type']==='vol' && !$perso->getLieu()->canVol())
			return fctErrorMSG('Le vol n\'est pas envisageable dans ce lieu.', $errorUrl);
		
		
		if ($achat || $vente)
		{
			switch ($_POST['achat_type'])
			{
				case 'achat':
					if($achat){
						$msg	.= "Vous effectuez l'achat de " . $itemsList . "\n";
						$vol	= false;
						$achat	= true;
					}
					if($vente){
						$msg 	.= "Vous effectuez la vente de " . $itemsVenteList . "\n";
						$vol 	= false;
						$vente	= true;
					}
					$msg .= $perso->setStat(array('INT' => '+01', 'FOR' => '-01' ));
					break;
					
				case 'nego':
					if($achat){
						$msg	.= "Vous tentez la négociation pour acheter " . $itemsList . "\n";
						$vol	= false;
						$achat	= true;
					}
					if($vente){
						$msg 	.= "Vous tentez la négociation pour vendre " . $itemsVenteList . "\n";
						$vol	= false;
						$vente	= true;
					}
					
					$chances_reussite = $perso->getChancesReussite('MRCH');
					$de = rand(0,100);
					
					if(DEBUG_MODE)
						echo "<br />" . $de . "<" . $chances_reussite;
					
					
					if ($de < $chances_reussite)
					{//Reussite
						$marchandage = true;
						//Calculer le rabais
						$compId		= $perso->convCompCodeToId('MRCH');
						$level		= $perso->getCompRealLevel($compId);
						$rabais		= rand(1,($level*5)+5); //De 1 à 65%
						$prixTotal	*= (100-$rabais)/100;
						$prixVenteTotal	*= 1+(100-$rabais)/100;
						$msg .= " et vous arrivez à obtenir une réduction de " . round($rabais,2) . "%, soit un prix d'achat de " . $prixTotal . GAME_DEVISE . " et un prix de vente de " . $prixVenteTotal . GAME_DEVISE .".\n";

						$msg .= $perso->setStat(array('INT' => '+02', 'DEX' => '-01', 'FOR' => '-01' ));
						
					
					}
					else
					{
						$msg .= $perso->setStat(array('INT' => '+01', 'FOR' => '-01' ));
						$msg .= " mais c'est un échec.\n";
					}	
					$msg .= $perso->setComp(array('MRCH' => rand(1,3) ));
					break;
					
				case 'vol':
					$msg	.= "Vous tentez le vol de " . $itemsList;
					$vol	= true;
					
					$chances_reussite = $perso->getChancesReussite('PCKP');
					$de = rand(0,100);
																					echo "<br />" . $de . "<" . $chances_reussite;
					if ($de < $chances_reussite)
					{//Reussite
						$achat		= true;
						$prixTotal	= 0;
						$msg 		.= " avec succès.";
					}
					else
					{
						$achat	= false;
						$msg .= " mais c'est un échec.";
					}
					$msg .= $perso->setComp(array('PCKP' => rand(1,3) ));
					break;
					
			}
		}

		$clientPay = ($prixTotal >= $prixVenteTotal); //True = le client paie, False = la boutique paie.
		if($clientPay)
			$differencePrix = $prixTotal - $prixVenteTotal;
		else
			$differencePrix = $prixVenteTotal - $prixTotal;
		
		
		
		//Vérifier les fond disponible selon le mode de paiement
		if (($achat && !$vol) || $vente)
		{
			if ($_POST['pay_type']=='cash') //PAIEMENT CASH
			{
				
				//Valider le montant
				if ($clientPay && $perso->getCash() < $differencePrix)
					return fctErrorMSG('Vous n\'avez pas assez d\'argent pour effectuer cette transaction.', $errorUrl);

				if (!$clientPay && $perso->getLieu()->getBoutiqueCash() < $differencePrix)
					return fctErrorMSG('La boutique ne possède pas suffisamment pour vous acheter pour vous payer ' . $differencePrix . GAME_DEVISE . '.', $errorUrl);
				
				//Débiter le perso
				$perso->changeCash('+', $prixVenteTotal);
				$perso->changeCash('-', $prixTotal);
				$perso->setCash();
					
				//Payer la caisse de la boutique
				$perso->getLieu()->changeBoutiqueCash('+', $prixTotal);
				$perso->getLieu()->changeBoutiqueCash('-', $prixVenteTotal);
				$perso->getLieu()->setBoutiqueCash();
			}
			else //PAIEMENT DIRECT
			{
				
				//Valider si une carte a été sélectionnée
				if (!isset($_POST['cardid']))
					return fctErrorMSG('Vous n\'avez pas sélectionné de carte de guichet.', $errorUrl);
				
				
				//Valider si un nip a été saisi
				if (!isset($_POST['nip']) || empty($_POST['nip']))
					return fctErrorMSG('Vous n\'avez pas entré de NIP.', $errorUrl);
				
				
				//Instancier la carte de guichet sélectionnée
				$i=0;
				while( $item = $perso->getInventaire($i++))
				{
					if($item instanceof Member_ItemCartebanque)
					{
						if($item->getInvId() == $_POST['cardid'])
						{
							$CARTE = $item;
							break;
						}
					}
				}
				
				
				//Valider si la carte (item) a été trouvée en inventaire
				if (!isset($CARTE))
					return fctErrorMSG('Carte de guichet introuvable.', $errorUrl);
				
				
				//Trouver la carte et son accès
				$query = 'SELECT *'
						. ' FROM ' . DB_PREFIX . 'banque_cartes'
						. ' WHERE carte_id=:carteId;';
				$prep = $db->prepare($query);
				$prep->bindValue(':carteId',	$item->getNoCarte(),	PDO::PARAM_INT);
				$prep->execute($db, __FILE__, __LINE__);
				$arr = $prep->fetch();
				$prep->closeCursor();
				$prep = NULL;
				
				//Valider si la carte est associée à un compte
				if ($arr === false)
					return fctErrorMSG('Carte de guichet est désactivée.', $errorUrl);
				
				
				//Instancier la carte
				$CARTE_ACCESS = new Member_BanqueCarte($arr);
				
				
				//Valider si la carte est ... valide
				if(!$CARTE_ACCESS->isValid())
					return fctErrorMSG('Carte de guichet est désactivée pour le moment.', $errorUrl);
				
				
				//Valider si le NIP est correcte
				if($CARTE_ACCESS->getNip() != $_POST['nip'])
					return fctErrorMSG('NIP est erronné.', $errorUrl);
				
				
				
				//Rechercher le compte relié à la carte
				$query = 'SELECT *' 
							. ' FROM ' . DB_PREFIX . 'banque_cartes'
							. ' LEFT JOIN ' . DB_PREFIX . 'banque_comptes ON (compte_banque = carte_banque AND compte_compte = carte_compte)'
							. ' WHERE carte_id =:carteId'
							. ' LIMIT 1;';
				$prep = $db->prepare($query);
				$prep->bindValue(':carteId',	$item->getNoCarte(),	PDO::PARAM_INT);
				$prep->execute($db, __FILE__, __LINE__);
				$arr = $prep->fetch();
				$prep->closeCursor();
				$prep = NULL;
				
				
				//Valider si le compte existe
				if ($arr === false)
					return fctErrorMSG('Le compte est innexistant.', $errorUrl);
					
					
				//Instancier le compte
				$COMPTE = new Member_BanqueCompte($arr);
				
				
				//Valider si le compte possède assez d'argent pour payer le total
				if($clientPay && $COMPTE->getCash() < $differencePrix && $COMPTE->getCash() != -1)
					return fctErrorMSG('Compte sans fond.', $errorUrl);
				
				
				
				
				//Rechercher le compte bancaire de la boutique
				$query = 'SELECT * '
						. ' FROM ' . DB_PREFIX . 'banque_comptes'
						. ' WHERE	compte_banque=:banque'
							. ' AND compte_compte=:compte'
						. ' LIMIT 1;';
				$prep = $db->prepare($query);
				$prep->bindValue(':banque',		$perso->getLieu()->getBoutiqueNoBanque(),	PDO::PARAM_STR);
				$prep->bindValue(':compte',		$perso->getLieu()->getBoutiqueNoCompte(),	PDO::PARAM_STR);
				$prep->execute($db, __FILE__, __LINE__);
				$arr = $prep->fetch();
				$prep->closeCursor();
				$prep = NULL;
				
				//Valider si le compte de la boutique existe
				if ($arr === false)
					return fctErrorMSG('Le compte de la boutique est innexistant.', $errorUrl);
					
				
				//Instancier le compte bancaire de la boutique
				$COMPTE_BOUTIQUE = new Member_BanqueCompte($arr);

				//Valider si le compte possède assez d'argent pour payer le total
				if(!$clientPay && $COMPTE_BOUTIQUE->getCash() < $differencePrix)
					return fctErrorMSG('Le compte de la boutique manque de fond.', $errorUrl);
				
			
				
				
				//Débiter le compte du perso
				$COMPTE->changeCash(($clientPay ? '-' : '+'), $differencePrix);
				$COMPTE->setCash();
				$COMPTE->add_bq_hist($COMPTE_BOUTIQUE->getNoBanque() . '-' . $COMPTE_BOUTIQUE->getNoCompte(), '', 'SDPD', $differencePrix);
				
				
				//Payer le compte de la boutique
				$COMPTE_BOUTIQUE->changeCash(($clientPay ? '+' : '-'), $differencePrix);
				$COMPTE_BOUTIQUE->setCash();
				$COMPTE_BOUTIQUE->add_bq_hist($COMPTE->getNoBanque() . '-' . $COMPTE->getNoCompte(), 'RCPD', 0, $differencePrix);
				
			}
			
		}
		
		
		
		
		//TOUT EST PAYÉ, IL FAUT MAINTENANT TRANSFÉRER CEUX-CI CORRECTEMENT.
		if ($achat){
			$i=0;
			while( $item = $perso->getLieu()->getBoutiqueInventaire($i++))
			{
				if(isset($_POST['itema' . $item->getInvId()]))
				{
					$qte = $_POST['itema' . $item->getInvId()];
					if($qte>0)
					{
						$item->transfererVersPerso($perso, $qte);
					}
				}
			}
			
			//Historique d'achat
			if(!$vol)
			{
				$perso->getLieu()->addBoutiqueHistorique('vente', $prixTotal, $itemsList, $marchandage, ($_POST['pay_type'] != 'cash'));
			}
		}
		
		
		if ($vente)
		{
			$i=0;
			while( $item = $perso->getInventaire($i++))
			{
				if(isset($_POST['itemv' . $item->getInvId()]))
				{
					$qte = $_POST['itemv' . $item->getInvId()];
					if($qte>0)
					{
						$item->transfererVersBoutique($perso->getLieu(), $qte);
					}
				}
			}
			
			//Historique de vente
			$perso->getLieu()->addBoutiqueHistorique('achat', $prixVenteTotal, $itemsVenteList, $marchandage, ($_POST['pay_type'] != 'cash'));
		}
		
		$perso->refreshInventaire();
		
		//Modifier les PA
		if($achat)
		{
			$perso->changePa('-', $pacost[$_POST['achat_type']]);
			$perso->setPa();
		}
		
		
		//Copier le message dans les HE
		if(!empty($msg))
			Member_He::add(NULL, $perso->getId(), 'boutique', $msg);
		
		
		//Rafraichir le HE
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}

