<?php
/** Gestion du tour d'attaque
*
* @package Member_Action
*/
class Member_Action_Perso_Attaquer2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl = '?popup=1&amp;m=Action_Perso_Attaquer';
		
		
		// ### VALIDATIONS
		
		//Vérifier l'état du perso
		if(!$perso->isAutonome())
			return fctErrorMSG('Vous n\'êtes pas en état d\'effectuer cette action.', $errorUrl);
		
		
		
		//Vérifier si un personnage à été sélectionné
		if(!isset($_POST['perso_id']))
			return fctErrorMSG('Vous devez sélectionner la personne que vous désirez attaquer.', $errorUrl);
		
		
		
		//Vérifier si un nombre de tours à été sélectionné
		if (!isset($_POST['tours']) || !is_numeric($_POST['tours']))
			return fctErrorMSG('Vous devez sélectionner le nombre de tour(s) à effectuer.', $errorUrl);
		
		
		
		//Vérifier si nous avons assez de PA pour attaquer
		$cout_pa = Member_Action_Perso_Attaquer::coutPaTotal($perso, $_POST['tours']);
		if ($_POST['type_att'] == 'cible')	$cout_pa+=15;
		if (isset($_POST['furtif']))		$cout_pa+=15;
		if($perso->getPa() <= $cout_pa)
			return fctErrorMSG('Vous n\'avez pas assez de PA pour effectuer cette action.', $errorUrl);
		
		
		
		//Vérifier si le perso sélectionné est bien présent dans le lieu actuel
		$i=0;
		while( $tmp = $perso->getLieu()->getPerso($perso, $i++))
			if($tmp->getId() == $_POST['perso_id']){
				$victime = $tmp;
				break;
			}
		if (!isset($victime))
			return fctErrorMSG('Le personnage sélectionné n\'est pas présent dans le lieu actuel.', $errorUrl);
		
		
		
		//ATTAQUE CIBLÉE: Vérifier si une zone à été sélectionnée
		if ($_POST['type_att'] == 'cible' && !isset($_POST['zones']))
			return fctErrorMSG('Une attaque ciblée doit avoir une zone à cibler.', $errorUrl);
			
			
			
		//CHEAT+HACK: Vérifier l'état innitial de la  victime
		if(!$perso->isVivant())
			return fctErrorMSG('Vous ne pouvez pas attaquer une personne déjà morte.', $errorUrl);
		
		
		
		//CHEAT+HACK: ATTAQUE CIBLÉE: Valider qu'une attaque ciblé n'a qu'un seul tour
		if ($_POST['type_att'] == 'cible' && $_POST['tours']>1)
			return fctErrorMSG('Une attaque ciblée ne peut avoir qu\'un seul tour.', $errorUrl);
		
		
		//CHEAT+HACK: Vérifier si la portée est valide
		$porteeSelectionnee = $_POST['portee'];
		$porteeArme = $perso->getArme()->getPortee();
		$dimensionLieu = $perso->getLieu()->getDimension();
		
		//Trouver la distance la plus courte entre la portée de l'arme et la dimension de la piece.
		$porteeAutoriseeMax = Member_Action_Perso_Attaquer::porteePlusPetite($dimensionLieu,$porteeArme) ? $dimensionLieu : $porteeArme;
		
		if(!Member_Action_Perso_Attaquer::porteePlusPetite($porteeSelectionnee,$porteeAutoriseeMax))
			return fctErrorMSG('La portée ne peut dépasser la capacité de l\'arme ou la taille du lieu.', $errorUrl);
		
		
		
		// ### DÉBUT DU MOTEUR D'ATTAQUE
		$tour = 0;
		$persoEsq = 0; //Nombre d'esquive effectuées
		$victimeEsq = 0; //Nombre d'esquive effectuées
		
		//Messages d'intro
		$msgPerso = 'Vous tentez une attaque';
		$msgVictime = 'Une personne tente une attaque';
		$msgLieu = 'Vous voyez une personne tenter une attaque';
		
		if (isset($_POST['furtif']))
		{
			$msgPerso .= ' furtive';
			$att_furtive = true;
		}
		
		if ($_POST['type_att'] == 'cible')
			$msgPerso .= " ciblée (Zone visée: " . $_POST['zones'] . "):";
		else
			$msgPerso .= " par tours (tour(s) tentés: " . $_POST['tours'] . "):";
		
		$noticeInconscient = true;
		$noticeVivant = true;
		$noticeParalyse = true;
		
		$noticeInconscientAtt = true;
		$noticeVivantAtt = true;
		$noticeParalyseAtt = true;
		
		$attStop = false;
		do //Boucle des tours d'attaques
		{
			$tour++;
			if(DEBUG_MODE)
				echo "<hr />Tour #{$tour}<br />";
			
			$msgPerso .= "\n\nTour #" . $tour . ' (' . $perso->getArme()->getNom() . ')';
			$msgVictime .= "\n\nTour #" . $tour . ' (' . $perso->getArme()->getNom() . ')';
			$msgLieu .= "\n\nTour #" . $tour . ' (' . $perso->getArme()->getNom() . ')';
			
			//Lancer le tour d'attaque en fonction du type d'arme utilisé		
			switch(get_class($perso->getArme()))
			{
				case 'Member_ItemArmeMainsnues':
					$riposte = Member_Action_Perso_Attaquer2ArmeMainsnues::attaquer($perso, $victime, $porteeSelectionnee, $msgPerso, $msgVictime, $msgLieu, $tour, $victimeEsq, false);
					break;
				case 'Member_ItemArmeBlanche':
					$riposte = Member_Action_Perso_Attaquer2ArmeBlanche::attaquer($perso, $victime, $porteeSelectionnee, $msgPerso, $msgVictime, $msgLieu, $tour, $victimeEsq, false);
					break;
				case 'Member_ItemArmeFeu':
					$riposte = Member_Action_Perso_Attaquer2ArmeFeu::attaquer($perso, $victime, $porteeSelectionnee, $msgPerso, $msgVictime, $msgLieu, $tour, $victimeEsq, false);
					break;
				//case 'Member_ItemArmeExplosive':
				//	$riposte = Member_Action_Perso_Attaquer2ArmeExplosive::attaquer($perso, $victime, $porteeSelectionnee, $msgPerso, $msgVictime, $msgLieu, $tour, $victimeEsq, false);
				//	break;
				//case 'Member_ItemArmeLourde':
				//	$riposte = Member_Action_Perso_Attaquer2ArmeLourde::attaquer($perso, $victime, $porteeSelectionnee, $msgPerso, $msgVictime, $msgLieu, $tour, $victimeEsq, false);
				//	break;
				case 'Member_ItemArmeParalysante':
					$riposte = Member_Action_Perso_Attaquer2ArmeParalysante::attaquer($perso, $victime, $porteeSelectionnee, $msgPerso, $msgVictime, $msgLieu, $tour, $victimeEsq, false) ;
					break;
				default:
					fctBugReport('Type d\'arme innexistant (' . get_class($perso->getArme()) . ')', array($perso, $perso->getArme()), __FILE__, __LINE__);
					break;
			}

			if($victime->getPa()==0 && $noticeParalyse)
			{
				$msgPerso	.= "\nVotre opposant est paralysé.";
				$msgVictime	.= "\nVous êtes paralysé.";
				$msgLieu	.= "\nLa victime est paralysée.";
				$noticeParalyse = false; //Pour ne pas repetter à chaque tour
			}
			
			//Vérifier l'état de la victime et afficher si elle tombe inconsciente ou meurt
			if(!$victime->isConscient() && $noticeInconscient)
			{
				$msgPerso	.= "\nVotre opposant est inconscient.";
				$msgVictime	.= "\nVous êtes inconscient.";
				$msgLieu	.= "\nLa victime est inconsciente.";
				$noticeInconscient = false; //Pour ne pas repetter à chaque tour
			}
			if(!$victime->isVivant() && $noticeVivant)
			{
				$msgVictime .= "\nVous êtes mort.";
				$noticeVivant = false; //Pour ne pas repetter à chaque tour
			}
			
			if(isset($_POST['furtif']))
				unset($_POST['furtif']); //De cette facon, la furtivité est uniquement tenté au premier tour.
				
			//Au besoin, lancer la riposte de la part de la victime
			if ($riposte && $victime->isAutonome() && $victime->getPa()>0 && $victime->getReaction()=='riposte')
			{
				if (DEBUG_MODE)
					echo "<br /><br />Riposte:";
				
				//Vérifier si la porté est plus distante que le nombre de tour (TC(1)=0 tour d'attente, C(2)=1 tour d'attente, M(3)=2 tours d'attente, etc..)
				if($tour < Member_Action_Perso_Attaquer::porteeVersInt($porteeSelectionnee))
				{
					$msgPerso	.= "\nVotre opposant aimerait riposter, mais il est trop loin pour le moment.";
					$msgVictime	.= "\nVous aimeriez riposter, mais vous êtes trop loin pour le moment.";
					$msgLieu	.= "\nLa victime est trop éloignée de son agresseur pour pouvoir riposter.";
				}
				else //La riposte a lieu
				{
					$msgPerso	.= "\nVotre opposant tente de riposter (" . $victime->getArme()->getNom() . '): ';
					$msgVictime	.= "\nVous tentez de riposter (" . $victime->getArme()->getNom() . '): ';
					$msgLieu	.= "\nLa victime tente de riposter (" . $victime->getArme()->getNom() . '): ';
					
					switch(get_class($victime->getArme()))
					{
						case 'Member_ItemArmeMainsnues':
							$riposte = Member_Action_Perso_Attaquer2ArmeMainsnues::attaquer($victime, $perso, $porteeSelectionnee, $msgPerso, $msgVictime, $msgLieu, $tour, $persoEsq, true);
							break;
						case 'Member_ItemArmeBlanche':
							$riposte = Member_Action_Perso_Attaquer2ArmeBlanche::attaquer($victime, $perso, $porteeSelectionnee, $msgPerso, $msgVictime, $msgLieu, $tour, $persoEsq, true);
							break;
						case 'Member_ItemArmeFeu':
							$riposte = Member_Action_Perso_Attaquer2ArmeFeu::attaquer($victime, $perso, $porteeSelectionnee, $msgPerso, $msgVictime, $msgLieu, $tour, $persoEsq, true);
							break;
						//case 'Member_ItemArmeExplosive':
						//	$riposte = Member_Action_Perso_Attaquer2ArmeExplosive::attaquer($victime, $perso, $porteeSelectionnee, $msgPerso, $msgVictime, $msgLieu, $tour, $persoEsq, true);
						//	break;
						//case 'Member_ItemArmeLourde':
						//	$riposte = Member_Action_Perso_Attaquer2ArmeLourde::attaquer($victime, $perso, $porteeSelectionnee, $msgPerso, $msgVictime, $msgLieu, $tour, $persoEsq, true);
						//	break;
						case 'Member_ItemArmeParalysante':
							$riposte = Member_Action_Perso_Attaquer2ArmeParalysante::attaquer($victime, $perso, $porteeSelectionnee, $msgPerso, $msgVictime, $msgLieu, $tour, $persoEsq, true);
							break;
						default:
							fctBugReport('Type d\'arme innexistant (' . get_class($victime->getArme()) . ')', array($victime, $victime->getArme()), __FILE__, __LINE__);
							break;
					}
					
					//On vérifie l'état de l'agresseur et on affiche dnas le HE en cas de changement d'état
					if($perso->getPa() == 0 && $noticeParalyseAtt)
					{	
						$msgPerso	.= "\nVous êtes paralysé.";
						$msgVictime	.= "\nVotre agresseur est paralysé.";
						$msgLieu	.= "\nL'agresseur est paralysée.";
						$noticeParalyseAtt = false; //Pour ne pas repetter à chaque tour
					}

					if(!$perso->isConscient() && $noticeInconscientAtt)
					{	
						$msgPerso	.= "\nVous êtes inconscient.";
						$msgVictime	.= "\nVotre agresseur est inconscient.";
						$msgLieu	.= "\nL'agresseur est inconsciente.";
						$noticeInconscientAtt = false; //Pour ne pas repetter à chaque tour
					}
					if(!$perso->isVivant() && $noticeVivantAtt)
					{
						$msgPerso .= "\nVous êtes mort.";
						$noticeVivantAtt = false; //Pour ne pas repetter à chaque tour
					}
				}
			}
			
			
				
			//Vérifier les options d'arrêt
			if ($_POST['att_stop']=='normal'	&& !$victime->isNormal())		$attStop = true;
			if ($_POST['att_stop']=='autonome'	&& !$victime->isAutonome())		$attStop = true;
			if ($_POST['att_stop']=='conscient'	&& !$victime->isConscient())	$attStop = true;
			
			//Si il faut arrêter, vérifier si l'arrêt réussit ou échoue
			if($attStop)
			{
				$reussite_arret = 85 + $perso->getStatRealLevel('PER')*3;
				
				$de = rand(1,100);
				if ($de >= $reussite_arret) //ÉCHEC de l'arrêt
					$attStop = false;
			}
			
			
			if ($attStop)
			{
				$msgPerso	.= "\n\nVous décidez de vous en tenir là, son compte est bon.";
				$msgVictime	.= "\n\nL'aggresseur arrête soudainement son attaque: votre compte est bon.";
				$msgLieu	.= "\n\nL'aggresseur arrête soudainement son attaque.";
			}
			
			
			
			if ($riposte && $victime->isAutonome() && $victime->getPa()>0 && $victime->getReaction()=='fuir')
			{
				//Lister tout les lieux en libre accès accessibles de l'emplacement actuel
				$i=0;
				while($lien = $perso->getLieu()->getLink($i++))
					if($lien->getProtection()=='0')
						$lieuFuite[] = $lien;
				
				if($e>0)
				{
					$choix = rand(0, $e-1);
					$query = 'UPDATE ' . DB_PREFIX . 'perso'
							. ' SET lieu=:lieuTech'
							. ' WHERE id =:persoId'
							. ' LIMIT 1;';
					$prep = $db->prepare($query);
					$prep->bindValue(':lieuTech',	$lieuFuite[$choix]->getNomTech(),	PDO::PARAM_STR);
					$prep->bindValue(':persoId',	$victime->getId(),					PDO::PARAM_INT);
					$prep->execute($db, __FILE__, __LINE__);
					$arr = $prep->fetch();
					$prep->closeCursor();
					$prep = NULL;
					
					$msgPerso	.= "\n\nLa victime arrive à prendre la fuite vers [i]" . $lieuFuite[$choix]->getNom() . "[/i].";
					$msgVictime	.= "\n\nVous arrivez à prendre la fuite vers [i]" . $lieuFuite[$choix]->getNom() . "[/i].";
					$msgLieu	.= "\n\nLa victime arrive à prendre la fuite vers [i]" . $lieuFuite[$choix]->getNom() . "[/i].";
					$attStop = true;
				}
				else
				{
					$msgPerso	.= "\n\nLa victime tente de fuir mais ne trouve aucune issue.";
					$msgVictime	.= "\n\nVous tentez de fuir mais vous ne trouvez aucune issue.";
					$msgLieu	.= "\n\nLa victime tente de fuir mais ne trouve aucune issue.";
				}
			}
			
			
			//Vérifier si le nombre de tours maximum est atteind (ou dépassé)
			if ($tour>=$_POST['tours'])
				$attStop = true;
				
			//Vérifier que l'agresseur est encore en état de se battre
			if(!$perso->isAutonome())
			{
				$msgPerso	.= "\nVous n'êtes plus en état de vous battre.";
				$msgVictime	.= "\nVotre agresseur n'est plus en état de se battre.";
				$msgLieu	.= "\nL'agresseur n'est plus en état de se battre.";
				$attStop = true;
			}
				
			
		}while(!$attStop); //Fin de la boucle des tours
		
		//### FIN DU MOTEUR D'ATTAQUE
		
		
		
		
		
		
		
		
		//Retirer les PA de l'attaquant
		$perso->changePa('-', $cout_pa);
		$perso->setPa();
		$perso->setPv($victime, 'Attaque');
		$victime->setPv($perso, 'Attaque');
		$victime->setPa();
		
		if(!$perso->getArme() instanceof Member_ItemArmeMainsnues)
		{
			$resistance = ($perso->getArme()->getResistance() < 0) ? 0 : $perso->getArme()->getResistance();
			$perso->getArme()->setResistance($resistance);
			
			//Vérifier si l'arme tombe à 0
			if($perso->getArme()->getResistance() == 0)
			{
				$perso->getArme()->desequiper();
				$msgPerso .= "\n\nVotre arme s'est trop endommagée pendant l'attaque et elle est maintenant inutilisable.";
			}
		}
		
		if(!$victime->getArme() instanceof Member_ItemArmeMainsnues)
		{
			$resistance = ($victime->getArme()->getResistance() < 0) ? 0 : $victime->getArme()->getResistance();
			$victime->getArme()->setResistance($resistance);
			
			//Vérifier si l'arme tombe à 0
			if($victime->getArme()->getResistance() == 0)
			{
				$victime->getArme()->desequiper();
				$msgVictime .= "\n\nVotre arme s'est trop endommagée pendant l'attaque et elle est maintenant inutilisable.";
			}
		}
		
		//Gain en STAT+COMP
		$msgPerso .= self::gainXP($perso, $perso->getArme(), ($_POST['type_att'] == 'cible'), isset($att_furtive), $tour, $persoEsq);
		$msgVictime .= self::gainXP($victime, $victime->getArme(), false, false, $tour, $victimeEsq);
		
		
		
		//Envoyer le message aux 2 personnes impliqués
		Member_He::add($perso->getId(), $victime->getId(), 'attaque', $msgPerso, HE_TOUS, HE_AUCUN);
		Member_He::add($perso->getId(), $victime->getId(), 'attaque', $msgVictime, HE_AUCUN, HE_TOUS);
		
		//Envoyer le message à tout les gens présent sur le lieu
		$i=0;
		$arrPersoLieu = array();
		while( $tmp = $perso->getLieu()->getPerso($perso, $i++))
			if($tmp->getId() != $perso->getId() && $tmp->getId() != $victime->getId())
				$arrPersoLieu[count($arrPersoLieu)] = $tmp->getId();
		
		if(count($arrPersoLieu)>0)
			Member_He::add(array($perso->getId(), $victime->getId()), $arrPersoLieu, 'attaque', $msgLieu, HE_AUCUN, HE_TOUS);
		
		
		
		//Rafraichir le HE
		if(!DEBUG_MODE)
			return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
		
	}
	
	
	
	private static function gainXP(&$perso, &$arme, $ciblee, $furtif, $tours, $nbrEsquive)
	{
		$msgPerso = "\n";
		switch(get_class($arme))
		{
			case 'Member_ItemArmeMainsnues':
				if ($ciblee)
				{
					if ($furtif) //Ciblé + Furtif
					{
						$msgPerso .= "\n" . $perso->setStat(array(	'AGI' => '+01',
																	'FOR' => '+03',
																	'DEX' => '-01',
																	'PER' => '-02',
																	'INT' => '-01' 	));
						$msgPerso .= "\n" . $perso->setComp(array(	'ARMC' => rand(1,3),
																	'FRTV' => rand(1,3),
																	'ESQV' => $tours+$nbrEsquive));
					}
					else //Ciblé + Non-Furtif
					{
						$msgPerso .= "\n" . $perso->setStat(array(	'AGI' => '+01',
																	'FOR' => '+02',
																	'DEX' => '-01',
																	'PER' => '-01',
																	'INT' => '-01'	));
						$msgPerso .= "\n" . $perso->setComp(array(	'ARMC' => rand(1,3),
																	'ESQV' => $tours+$nbrEsquive));
					}
				}
				else
				{
					if ($furtif) //Non-Ciblé + Furtif
					{
						$msgPerso .= "\n" . $perso->setStat(array(	'AGI' => '+02',
																	'FOR' => '+01',
																	'DEX' => '-02',
																	'PER' => '+00',
																	'INT' => '-01'	));
						$msgPerso .= "\n" . $perso->setComp(array(	'ARMC' => ($tours*rand(1,3)), 
																	'FRTV' => rand(1,3),
																	'ESQV' => $tours+$nbrEsquive));
					}
					else //Non-Ciblé + Non-Furtif
					{
						$msgPerso .= "\n" . $perso->setStat(array(	'AGI' => '+01',
																	'FOR' => '+00',
																	'DEX' => '-02',
																	'PER' => '+01',
																	'INT' => '+00'	));
						$msgPerso .= "\n" . $perso->setComp(array(	'ARMC' => ($tours*rand(1,3)),
																	'ESQV' => $tours+$nbrEsquive));
					}
				}
				break;
			case 'Member_ItemArmeBlanche':
				if ($ciblee){
					if ($furtif) //Ciblé + Furtif
					{
						$msgPerso .= "\n" . $perso->setStat(array(	'AGI' => '+01',
																	'FOR' => '+03',
																	'DEX' => '-01',
																	'PER' => '-02',
																	'INT' => '-01' 	));
						$msgPerso .= "\n" . $perso->setComp(array(	'ARMB' => rand(1,3),
																	'FRTV' => rand(1,3),
																	'ESQV' => $tours+$nbrEsquive));
					}
					else //Ciblé + Non-Furtif
					{
						$msgPerso .= "\n" . $perso->setStat(array(	'AGI' => '+01',
																	'FOR' => '+02',
																	'DEX' => '-01',
																	'PER' => '-01',
																	'INT' => '-01'	));
						$msgPerso .= "\n" . $perso->setComp(array(	'ARMB' => rand(1,3),
																	'ESQV' => $tours+$nbrEsquive));
					}
				}
				else
				{
					if ($furtif) //Non-Ciblé + Furtif
					{
						$msgPerso .= "\n" . $perso->setStat(array(	'AGI' => '+02',
																	'FOR' => '+01',
																	'DEX' => '-02',
																	'PER' => '+00',
																	'INT' => '-01'	));
						$msgPerso .= "\n" . $perso->setComp(array(	'ARMB' => ($tours*rand(1,3)), 
																	'FRTV' => rand(1,3),
																	'ESQV' => $tours+$nbrEsquive));
					}
					else //Non-Ciblé + Non-Furtif
					{
						$msgPerso .= "\n" . $perso->setStat(array(	'AGI' => '+01',
																	'FOR' => '+00',
																	'DEX' => '-02',
																	'PER' => '+01',
																	'INT' => '+00'	));
						$msgPerso .= "\n" . $perso->setComp(array(	'ARMB' => ($tours*rand(1,3)),
																	'ESQV' => $tours+$nbrEsquive));
					}
				}
				break;
			case 'Member_ItemArmeFeu':
			case 'Member_ItemArmeParalysante':
				if ($ciblee)
				{
					if ($furtif) //Ciblé + Furtif
					{
						$msgPerso .= "\n" . $perso->setStat(array(	'AGI' => '+01',
																	'FOR' => '+03',
																	'DEX' => '-01',
																	'PER' => '-02',
																	'INT' => '-01' 	));
						$msgPerso .= "\n" . $perso->setComp(array(	'ARMF' => rand(1,3),
																	'FRTV' => rand(1,3),
																	'ESQV' => $tours+$nbrEsquive));
					}else{ //Ciblé + Non-Furtif
						$msgPerso .= "\n" . $perso->setStat(array(	'AGI' => '+01',
																	'FOR' => '+02',
																	'DEX' => '-01',
																	'PER' => '-01',
																	'INT' => '-01'	));
						$msgPerso .= "\n" . $perso->setComp(array(	'ARMF' => rand(1,3),
																	'ESQV' => $tours+$nbrEsquive));
					}
				}
				else
				{
					if ($furtif) //Non-Ciblé + Furtif
					{
						$msgPerso .= "\n" . $perso->setStat(array(	'AGI' => '+02',
																	'FOR' => '+01',
																	'DEX' => '-02',
																	'PER' => '+00',
																	'INT' => '-01'	));
						$msgPerso .= "\n" . $perso->setComp(array(	'ARMF' => ($tours*rand(1,3)), 
																	'FRTV' => rand(1,3),
																	'ESQV' => $tours+$nbrEsquive));
					}
					else //Non-Ciblé + Non-Furtif
					{
						$msgPerso .= "\n" . $perso->setStat(array(	'AGI' => '+01',
																	'FOR' => '+00',
																	'DEX' => '-02',
																	'PER' => '+01',
																	'INT' => '+00'	));
						$msgPerso .= "\n" . $perso->setComp(array(	'ARMF' => ($tours*rand(1,3)),
																	'ESQV' => $tours+$nbrEsquive));
					}
				}
				break;
			//case 'Member_ItemArmeExplosive':
			//	
			//	break;
			//case 'Member_ItemArmeLourde':
			//	
			//	break;
			
		}
		return $msgPerso;
	}
	
	
	
	protected static function localisationDuCoup($tauxReussite)
	{
		//Tableau qui détermine la localisation du coup
		$localisation = array(
			0=>array(
				0=>array('min'=>1 , 'nom'=>'Tete' , 'multiplicateur'=>1.3), // 15 %
				1=>array('min'=>16, 'nom'=>'Torse', 'multiplicateur'=>1.2), // 38 %
				2=>array('min'=>54, 'nom'=>'Bras' , 'multiplicateur'=>1.0), // 15 %
				3=>array('min'=>69, 'nom'=>'Main' , 'multiplicateur'=>1.0), // 07 %
				4=>array('min'=>76, 'nom'=>'Jambe', 'multiplicateur'=>1.0), // 15 %
				5=>array('min'=>91, 'nom'=>'Pied' , 'multiplicateur'=>1.0), // 05 %
				6=>array('min'=>96, 'nom'=>'Rien' , 'multiplicateur'=>0.0) // 05 %
			),
			1=>array(
				0=>array('min'=>1 , 'nom'=>'Tete' , 'multiplicateur'=>1.3), // 19 %
				1=>array('min'=>20, 'nom'=>'Torse', 'multiplicateur'=>1.2), // 38 %
				2=>array('min'=>58, 'nom'=>'Bras' , 'multiplicateur'=>1.0), // 13 %
				3=>array('min'=>71, 'nom'=>'Main' , 'multiplicateur'=>1.0), // 07 %
				4=>array('min'=>78, 'nom'=>'Jambe', 'multiplicateur'=>1.0), // 14 %
				5=>array('min'=>92, 'nom'=>'Pied' , 'multiplicateur'=>1.0), // 04 %
				6=>array('min'=>96, 'nom'=>'Rien' , 'multiplicateur'=>0.0) // 05 %
			),
			2=>array(
				0=>array('min'=>1 , 'nom'=>'Tete' , 'multiplicateur'=>1.3), // 23 %
				1=>array('min'=>24, 'nom'=>'Torse', 'multiplicateur'=>1.2), // 38 %
				2=>array('min'=>62, 'nom'=>'Bras' , 'multiplicateur'=>1.0), // 11 %
				3=>array('min'=>73, 'nom'=>'Main' , 'multiplicateur'=>1.0), // 07 %
				4=>array('min'=>80, 'nom'=>'Jambe', 'multiplicateur'=>1.0), // 12 %
				5=>array('min'=>92, 'nom'=>'Pied' , 'multiplicateur'=>1.0), // 04 %
				6=>array('min'=>96, 'nom'=>'Rien' , 'multiplicateur'=>0.1) // 05 %
			),
			3=>array(
				0=>array('min'=>1 , 'nom'=>'Tete' , 'multiplicateur'=>1.3), // 28 %
				1=>array('min'=>29, 'nom'=>'Torse', 'multiplicateur'=>1.2), // 38 %
				2=>array('min'=>67, 'nom'=>'Bras' , 'multiplicateur'=>1.0), // 09 %
				3=>array('min'=>76, 'nom'=>'Main' , 'multiplicateur'=>1.0), // 06 %
				4=>array('min'=>82, 'nom'=>'Jambe', 'multiplicateur'=>1.0), // 11 %
				5=>array('min'=>93, 'nom'=>'Pied' , 'multiplicateur'=>1.0), // 03 %
				6=>array('min'=>96, 'nom'=>'Rien' , 'multiplicateur'=>0.0) // 05 %
			),
			4=>array(
				0=>array('min'=>1 , 'nom'=>'Tete' , 'multiplicateur'=>1.3), // 31 %
				1=>array('min'=>32, 'nom'=>'Torse', 'multiplicateur'=>1.2), // 38 %
				2=>array('min'=>70, 'nom'=>'Bras' , 'multiplicateur'=>1.0), // 07 %
				3=>array('min'=>77, 'nom'=>'Main' , 'multiplicateur'=>1.0), // 06 %
				4=>array('min'=>83, 'nom'=>'Jambe', 'multiplicateur'=>1.0), // 10 %
				5=>array('min'=>93, 'nom'=>'Pied' , 'multiplicateur'=>1.0), // 03 %
				6=>array('min'=>96, 'nom'=>'Rien' , 'multiplicateur'=>0.0) // 05 %
			),
			5=>array(
				0=>array('min'=>1 , 'nom'=>'Tete' , 'multiplicateur'=>1.3), // 35 %
				1=>array('min'=>36, 'nom'=>'Torse', 'multiplicateur'=>1.2), // 37 %
				2=>array('min'=>73, 'nom'=>'Bras' , 'multiplicateur'=>1.0), // 06 %
				3=>array('min'=>79, 'nom'=>'Main' , 'multiplicateur'=>1.0), // 05 %
				4=>array('min'=>84, 'nom'=>'Jambe', 'multiplicateur'=>1.0), // 09 %
				5=>array('min'=>93, 'nom'=>'Pied' , 'multiplicateur'=>1.0), // 03 %
				6=>array('min'=>96, 'nom'=>'Rien' , 'multiplicateur'=>0.0) // 05 %
			),
			6=>array(
				0=>array('min'=>1 , 'nom'=>'Tete' , 'multiplicateur'=>1.3), // 39 %
				1=>array('min'=>40, 'nom'=>'Torse', 'multiplicateur'=>1.2), // 37 %
				2=>array('min'=>77, 'nom'=>'Bras' , 'multiplicateur'=>1.0), // 05 %
				3=>array('min'=>82, 'nom'=>'Main' , 'multiplicateur'=>1.0), // 05 %
				4=>array('min'=>87, 'nom'=>'Jambe', 'multiplicateur'=>1.0), // 07 %
				5=>array('min'=>94, 'nom'=>'Pied' , 'multiplicateur'=>1.0), // 02 %
				6=>array('min'=>96, 'nom'=>'Rien' , 'multiplicateur'=>0.0) // 05 %
			),
			7=>array(
				0=>array('min'=>1 , 'nom'=>'Tete' , 'multiplicateur'=>1.3), // 41 %
				1=>array('min'=>42, 'nom'=>'Torse', 'multiplicateur'=>1.2), // 37 %
				2=>array('min'=>79, 'nom'=>'Bras' , 'multiplicateur'=>1.0), // 05 %
				3=>array('min'=>84, 'nom'=>'Main' , 'multiplicateur'=>1.0), // 04 %
				4=>array('min'=>88, 'nom'=>'Jambe', 'multiplicateur'=>1.0), // 06 %
				5=>array('min'=>94, 'nom'=>'Pied' , 'multiplicateur'=>1.0), // 02 %
				6=>array('min'=>96, 'nom'=>'Rien' , 'multiplicateur'=>0.0) // 05 %
			),
			8=>array(
				0=>array('min'=>1 , 'nom'=>'Tete' , 'multiplicateur'=>1.3), // 43 %
				1=>array('min'=>44, 'nom'=>'Torse', 'multiplicateur'=>1.2), // 37 %
				2=>array('min'=>81, 'nom'=>'Bras' , 'multiplicateur'=>1.0), // 04 %
				3=>array('min'=>85, 'nom'=>'Main' , 'multiplicateur'=>1.0), // 04 %
				4=>array('min'=>89, 'nom'=>'Jambe', 'multiplicateur'=>1.0), // 05 %
				5=>array('min'=>94, 'nom'=>'Pied' , 'multiplicateur'=>1.0), // 02 %
				6=>array('min'=>96, 'nom'=>'Rien' , 'multiplicateur'=>0.0) // 05 %
			),
			9=>array(
				0=>array('min'=>1 , 'nom'=>'Tete' , 'multiplicateur'=>1.3), // 46 %
				1=>array('min'=>47, 'nom'=>'Torse', 'multiplicateur'=>1.2), // 37 %
				2=>array('min'=>84, 'nom'=>'Bras' , 'multiplicateur'=>1.0), // 03 %
				3=>array('min'=>87, 'nom'=>'Main' , 'multiplicateur'=>1.0), // 04 %
				4=>array('min'=>91, 'nom'=>'Jambe', 'multiplicateur'=>1.0), // 05 %
				5=>array('min'=>96, 'nom'=>'Pied' , 'multiplicateur'=>1.0), // 02 %
				6=>array('min'=>98, 'nom'=>'Rien' , 'multiplicateur'=>0.0) // 03 %
			),
			10=>array(
				0=>array('min'=>1 , 'nom'=>'Tete' , 'multiplicateur'=>1.3), // 50 %
				1=>array('min'=>51, 'nom'=>'Torse', 'multiplicateur'=>1.2), // 37 %
				2=>array('min'=>88, 'nom'=>'Bras' , 'multiplicateur'=>1.0), // 02 %
				3=>array('min'=>90, 'nom'=>'Main' , 'multiplicateur'=>1.0), // 02 %
				4=>array('min'=>92, 'nom'=>'Jambe', 'multiplicateur'=>1.0), // 02 %
				5=>array('min'=>94, 'nom'=>'Pied' , 'multiplicateur'=>1.0), // 02 %
				6=>array('min'=>96, 'nom'=>'Rien' , 'multiplicateur'=>0.0) // 05 %
			),
			11=>array(
				0=>array('min'=>1 , 'nom'=>'Tete' , 'multiplicateur'=>1.3), // 55 %
				1=>array('min'=>56, 'nom'=>'Torse', 'multiplicateur'=>1.2), // 36 %
				2=>array('min'=>92, 'nom'=>'Bras' , 'multiplicateur'=>1.0), // 01 %
				3=>array('min'=>93, 'nom'=>'Main' , 'multiplicateur'=>1.0), // 01 %
				4=>array('min'=>94, 'nom'=>'Jambe', 'multiplicateur'=>1.0), // 01 %
				5=>array('min'=>95, 'nom'=>'Pied' , 'multiplicateur'=>1.0), // 01 %
				6=>array('min'=>96, 'nom'=>'Rien' , 'multiplicateur'=>0.0) // 05 %
			),
			12=>array(
				0=>array('min'=>1 , 'nom'=>'Tete' , 'multiplicateur'=>1.3), // 64 %
				1=>array('min'=>65, 'nom'=>'Torse', 'multiplicateur'=>1.2), // 27 %
				2=>array('min'=>92, 'nom'=>'Bras' , 'multiplicateur'=>1.0), // 01 %
				3=>array('min'=>93, 'nom'=>'Main' , 'multiplicateur'=>1.0), // 01 %
				4=>array('min'=>94, 'nom'=>'Jambe', 'multiplicateur'=>1.0), // 01 %
				5=>array('min'=>95, 'nom'=>'Pied' , 'multiplicateur'=>1.0), // 01 %
				6=>array('min'=>96, 'nom'=>'Rien' , 'multiplicateur'=>0.0) // 05 %
			),
			13=>array(
				0=>array('min'=>1 , 'nom'=>'Tete' , 'multiplicateur'=>1.3), // 73 %
				1=>array('min'=>74, 'nom'=>'Torse', 'multiplicateur'=>1.2), // 18 %
				2=>array('min'=>92, 'nom'=>'Bras' , 'multiplicateur'=>1.0), // 01 %
				3=>array('min'=>93, 'nom'=>'Main' , 'multiplicateur'=>1.0), // 01 %
				4=>array('min'=>94, 'nom'=>'Jambe', 'multiplicateur'=>1.0), // 01 %
				5=>array('min'=>95, 'nom'=>'Pied' , 'multiplicateur'=>1.0), // 01 %
				6=>array('min'=>96, 'nom'=>'Rien' , 'multiplicateur'=>0.0) // 05 %
			),
			14=>array(
				0=>array('min'=>1 , 'nom'=>'Tete' , 'multiplicateur'=>1.3), // 82 %
				1=>array('min'=>83, 'nom'=>'Torse', 'multiplicateur'=>1.2), // 09 %
				2=>array('min'=>92, 'nom'=>'Bras' , 'multiplicateur'=>1.0), // 01 %
				3=>array('min'=>93, 'nom'=>'Main' , 'multiplicateur'=>1.0), // 01 %
				4=>array('min'=>94, 'nom'=>'Jambe', 'multiplicateur'=>1.0), // 01 %
				5=>array('min'=>95, 'nom'=>'Pied' , 'multiplicateur'=>1.0), // 01 %
				6=>array('min'=>96, 'nom'=>'Rien' , 'multiplicateur'=>0.0) // 05 %
			),
			15=>array(
				0=>array('min'=>1 , 'nom'=>'Tete' , 'multiplicateur'=>1.3), // 90 %
				1=>array('min'=>91, 'nom'=>'Torse', 'multiplicateur'=>1.2), // 01 %
				2=>array('min'=>92, 'nom'=>'Bras' , 'multiplicateur'=>1.0), // 01 %
				3=>array('min'=>93, 'nom'=>'Main' , 'multiplicateur'=>1.0), // 01 %
				4=>array('min'=>94, 'nom'=>'Jambe', 'multiplicateur'=>1.0), // 01 %
				5=>array('min'=>95, 'nom'=>'Pied' , 'multiplicateur'=>1.0), // 01 %
				6=>array('min'=>96, 'nom'=>'Rien' , 'multiplicateur'=>0.0) // 05 %
			)
		);
		
		//Convertir la valeur du taux de réussite sur une échelle de 0 à 20 (Le taux de réussite va de 0 à 200)
		$tauxReussiteSur20 = round(($tauxReussite/10),0);
		if ($tauxReussiteSur20 > 15)
			$tauxReussiteSur20 = 15; //Dans le cas d'échec critique, il y a un maximum de "critique".
		
		//trouver ou le coup est localisé
		$de = rand(1,100);
		for($i=0; $i<=6; $i++)	//TECHNIQUE: Détecter dans quel tranche du tableau se situe le résultat du Dé
			if ($localisation[$tauxReussiteSur20][$i]['min'] > $de)
				return $localisation[$tauxReussiteSur20][$i-1];
		return $localisation[$tauxReussiteSur20][6];
	}
	
    
    protected static function infoDuCoupCible($techLoc)
	{
        switch($techLoc)
        {
            case 'Tete':
                return array('min'=>1 , 'nom'=>'Tete' , 'multiplicateur'=>1.3);
                break;
            case 'Main':
                return array('min'=>93, 'nom'=>'Main' , 'multiplicateur'=>1.0);
                break;
            case 'Jambe':
                return array('min'=>94, 'nom'=>'Jambe', 'multiplicateur'=>1.0);
                break;
            case 'Torse':
                return array('min'=>91, 'nom'=>'Torse', 'multiplicateur'=>1.2);
                break;
            case 'Bras':
                return array('min'=>92, 'nom'=>'Bras' , 'multiplicateur'=>1.0);
                break;
            case 'Pied':
                return array('min'=>95, 'nom'=>'Pied' , 'multiplicateur'=>1.0);
                break;
            case 'Rien':
                return array('min'=>96, 'nom'=>'Rien' , 'multiplicateur'=>0.0);
                break;
        }
    }
    
	protected static function getTxtLoc($techLoc, $article=false)
	{
		$txtLoc = strtolower($techLoc);
		
		if($article)
		{
			switch($techLoc)
			{
				case 'Tete':
				case 'Main':
				case 'Jambe':
					return 'à la ' . $txtLoc;
					break;
				case 'Torse':
				case 'Bras':
				case 'Pied':
					return 'au ' . $txtLoc;
					break;
				case 'Rien':
					break;
			}
		}
	}
}

