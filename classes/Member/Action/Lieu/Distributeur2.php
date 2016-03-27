<?php
/** Gestion de l'interface d'une boutique
*
* @package Member_Action
*/
class Member_Action_Lieu_Distributeur2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		$errorUrl = '?popup=1&amp;m=Action_Lieu_Distributeur';
		
		
		//Déclaration des variables pour cette action
		$pacost = array();
		$pacost['achat'] = 10;
		$pacost['nego'] = 20;
		//$pacost['vol'] = 40;
		$msg = '';
		
		
		
		//Vérifier l'état du perso
		if(!$perso->isAutonome())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.', $errorUrl);
		
		
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


		
		//Lister l'inventaire du distributeur
		$query = 'SELECT i.*, db.*'
				. ' FROM ' . DB_PREFIX . 'lieu_distributeur as d'
				. ' LEFT JOIN ' . DB_PREFIX . 'producteur_inv as i'
					. ' ON (i.producteurId = d.producteurId)'
				. ' INNER JOIN ' . DB_PREFIX . 'item_db as db'
					. ' ON (db.db_id = i.itemDbId)'
				. ' WHERE lieuId=:lieuId;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',	$perso->getLieu()->getId(),	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__, __LINE__);
		$arrItem = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		
		//Variables des totaux
		$prTotal=0;
		$prixTotal=0;	//Achat
		$itemsList='';	//Achat
		
		//Valider les items ... à acheter
		$i=0; 
		$achat = false;
		foreach($arrItem as $item)
		{
			if(isset($_POST['itema' . $item['id']]))
			{
				$qte = $_POST['itema' . $item['id']];
				
				//Valider si la quantité est possible
				if(!is_numeric($qte) || $qte<0)
					return fctErrorMSG('Les quantités doivent être numérique et supérieures à zéro.',$errorUrl);

				if($qte>$item['qte'])
					return fctErrorMSG('Vous ne pouvez pas obtenir plus que ce que l\'inventaire contient.',$errorUrl);
				
				//Calculer les totaux
				$qte = round($qte);
				
				
				//Items que le perso désire acheter
				if($qte>0)
				{
					$prTotal+= $item['db_pr'];
					$prixTotal+= $item['prix']*$qte;
					
					//Générer la liste des items pour l'inclure dans le message de conclusion
					$itemsList .=  ($itemsList!='' ? ',' : '') . ($item['pack']*$qte) . 'x[i]' . stripslashes($item['db_nom']) . '[/i]';
					
					$achat=true;
				}

			}
		}
		
		
		//Valider si le perso possède assez de PR disponible
		if(($perso->getPrMax()-$perso->getPr()) < $prTotal)
			return fctErrorMSG('Vous n\'avez pas assez de PR.');
		
		
		//Valider si le joueur a sélectionné des items à vendre ou à achaté
		// ( éviter le cas ou il clique 'achat' sans rien sélectionner )
        if ($_POST['buy'] && !$achat )
			return fctErrorMSG('Vous n\'avez sélectionné aucun objet.', $errorUrl);
		
		if($prixTotal<200)
			return fctErrorMSG('L\'achat minimal est de 200' . GAME_DEVISE . '.', $errorUrl);
		
		
		
		switch ($_POST['achat_type'])
		{
			case 'achat':
				$msg	.= 'Vous effectuez l\'achat de ' . $itemsList . "\n";
				$vol	= false;
				break;
				
			case 'nego':
				$msg	.= 'Vous tentez la négociation pour acheter ' . $itemsList . "\n";
				$vol	= false;
				
				$chances_reussite = $perso->getChancesReussite('MRCH');
				$de = rand(0,100);
				
				if(DEBUG_MODE)
					echo "<br />" . $de . "<" . $chances_reussite;
				
				
				if ($de < $chances_reussite)
				{//Reussite
					//Calculer le rabais
					$compId		= $perso->convCompCodeToId('MRCH');
					$level		= $perso->getCompRealLevel($compId);
					$rabais		= rand(1,($level*5)+5); //De 1 à 65%
					$prixTotal	*= (100-$rabais)/100;
					$msg .= " et vous arrivez à obtenir une réduction de " . round($rabais,2) . "%, soit un prix d'achat de " . $prixTotal . GAME_DEVISE . ".\n";
				}
				else
				{
					$msg .= " mais c'est un échec.\n";
				}
				break;
		}
		
		
		
		
		
		
		//Vérifier les fond disponible selon le mode de paiement
		if ($_POST['pay_type']=='cash') //PAIEMENT CASH
		{
			
			//Valider le montant
			if ($perso->getCash() < $prixTotal)
				return fctErrorMSG('Vous n\'avez pas assez d\'argent pour effectuer cette transaction.', $errorUrl);
			
			//Débiter le perso
			$perso->changeCash('-', $prixTotal);
			$perso->setCash();
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
					. ' LEFT JOIN ' . DB_PREFIX . 'banque_comptes ON (compte_banque = carte_banque AND compte_compte = carte_compte)'
					. ' WHERE carte_id=:carteId'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':carteId',	$item->getNoCarte(),	PDO::PARAM_INT);
			$prep->executePlus($db, __FILE__, __LINE__);
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
			//--> Fait dans la requête précédente
			
			//Valider si le compte existe
			if ($arr['compte_banque']===NULL)
				return fctErrorMSG('Le compte est innexistant.', $errorUrl);
				
				
			//Instancier le compte
			$COMPTE = new Member_BanqueCompte($arr);
			
			
			//Valider si le compte possède assez d'argent pour payer le total
			if($COMPTE->getCash() < $prixTotal && $COMPTE->getCash() != -1)
				return fctErrorMSG('Compte sans fond.', $errorUrl);
			
			
			
			
			
			
			//Débiter le compte du perso
			$COMPTE->changeCash((($prixTotal>0) ? '-' : '+'), $prixTotal);
			$COMPTE->setCash();
			$COMPTE->add_bq_hist('distributeur', '', 'SDPD', $prixTotal);
			
			
		}
		
		
		
		
		
		
		//ICI, TOUT EST PAYÉ: IL FAUT MAINTENANT TRANSFÉRER LES ITEMS CORRECTEMENT.
		$query = 'UPDATE ' . DB_PREFIX . 'producteur'
				. ' SET `cash`=`cash`+:cash'
				. ' WHERE id=:id'
				. ' LIMIT 1;';
		$prepProd = $db->prepare($query);

		$query = 'UPDATE ' . DB_PREFIX . 'producteur_inv'
				. ' SET qte=qte-:qte'
				. ' WHERE id=:id'
				. ' LIMIT 1;';
		$prepInv= $db->prepare($query);

		$query = 'SELECT inv_id'
				. ' FROM ' . DB_PREFIX . 'item_inv'
				. ' WHERE inv_dbid=:dbId'
					. ' AND inv_persoid=:persoId;';
		$prepItem = $db->prepare($query);
		
		
			$prep->closeCursor();
			$prep = NULL;
		foreach($arrItem as $item)
		{
			if(!isset($_POST['itema' . $item['id']]))
				continue;
				
			$qte = (int)$_POST['itema' . $item['id']];
			if($qte<=0)
				continue;

			
			$pack = (int)$item['pack'];
			$itemQte = $qte*$pack;
			
			//Payer la caisse du producteur
			$prepProd->bindValue(':cash',	($qte*$item['prix']),	PDO::PARAM_INT);
			$prepProd->bindValue(':id',		$item['producteurId'],	PDO::PARAM_INT);
			$prepProd->execute($db, __FILE__, __LINE__);
		
		
			//Retirer la quantité en inventaire
			$prepInv->bindValue(':qte',		$qte,			PDO::PARAM_INT);
			$prepInv->bindValue(':id',		$item['id'],	PDO::PARAM_INT);
			$prepInv->execute($db, __FILE__, __LINE__);
		
			
			//Mettre les items dans l'inventaire du joueur
			if($item['db_regrouper']=='1')
			{
				//Vérifier si le perso actuel possède déjà cet item, si oui: augmenter la qte.
				$prepItem->bindValue(':dbId',		$item['db_id'],		PDO::PARAM_INT);
				$prepItem->bindValue(':persoId',	$perso->getId(),	PDO::PARAM_INT);
				$prepItem->execute($db, __FILE__, __LINE__);
				$arrItem = $prepItem->fetch();
				
				if ($arr !== false) //L'item supporte la quantité et existe
				{
					//Augmenter la Qte
					$prepInv->bindValue(':qte',		$itemQte,			PDO::PARAM_INT);
					$prepInv->bindValue(':id',		$arrItem['inv_id'],	PDO::PARAM_INT);
					$prepInv->execute($db, __FILE__, __LINE__);
					
					//Ne pas ajouter d'item avec la requête INSERT ci-dessous
					$queryQte = 0;
					$qte = 0;
				}
				else //L'item supporte la quantité, mais n'existe pas.
				{
					$queryQte = $itemQte;
					$loopQte = 1;
				}
			}
			else //L'item ne supporte pas la quantité
			{
				$queryQte = 1;
				$loopQte = $itemQte;
			}
			
			$query = 'INSERT INTO ' . DB_PREFIX . 'item_inv'
					. ' (`inv_id`,`inv_dbid`,`inv_persoid`,`inv_equip`,`inv_qte`,`inv_munition`,`inv_resistance`,`inv_remiseleft`,`inv_pn`)'
					. ' VALUES'
					. ' (NULL,     :dbId,     :persoId,     :equip,     :qte,     :munition,     :resistance,     NULL,     		:pn)'																					
			$prepInv->bindValue(':dbId',		$item['db_id'],		PDO::PARAM_INT);
			$prepInv->bindValue(':persoId',		$perso->getId(),	PDO::PARAM_INT);

			if($item['db_type'] == 'arme' || $item['db_type'] == 'defense')
				$prepInv->bindValue(':equip',	0,					PDO::PARAM_INT);
			else
				$prepInv->bindValue(':equip',	NULL,				PDO::PARAM_NULL);
			
			$prepInv->bindValue(':qte',			$queryQte,			PDO::PARAM_INT);

			if($arr['db_soustype'] == 'arme_feu')
				$prepInv->bindValue(':munition',	0,				PDO::PARAM_INT);
			else
				$prepInv->bindValue(':munition',	NULL,			PDO::PARAM_NULL);
				
			if(isset($item['db_resistance']))
				$prepInv->bindValue(':resistance',	0,				PDO::PARAM_INT);
			else
				$prepInv->bindValue(':resistance',	NULL,			PDO::PARAM_NULL);

			if($item['db_type'] == 'nourriture')
				$prepInv->bindValue(':pn',			$item['db_pn'],	PDO::PARAM_INT);
			else
				$prepInv->bindValue(':pn',			NULL,			PDO::PARAM_NULL);
			
			
			
			$q=1;
			while ($q<=$loopQte)
			{
				$prepInv->execute($db, __FILE__, __LINE__);
				$q++;
			}
				
			
		} //End foreach
		
		
		$perso->refreshInventaire();
		
		//Modifier les PA
		$perso->changePa('-', $pacost[$_POST['achat_type']]);
		$perso->setPa();
		
		
		//Copier le message dans les HE
		if(!empty($msg))
			Member_He::add(NULL, $perso->getId(), 'distributeur', $msg);
		
		
		//Rafraichir le HE
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}

