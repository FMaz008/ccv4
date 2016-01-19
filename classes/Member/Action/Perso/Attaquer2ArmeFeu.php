<?php
/** Gestion du tour d'attaque
*
* @package Member_Action
*/
class Member_Action_Perso_Attaquer2ArmeFeu extends Member_Action_Perso_Attaquer2{

	/** Lancer une attaque
	*
	* Exemple d'utilisation - Attaquer une victime avec une arme à feu
	* <code>
	* Member_Action_Perso_Attaquer2ArmeFeu::attaquer($perso, $victime, 'TC', $log1, $log2, $log3, true);
	* </code>
	* <br>
	* 
	* @param object &$perso  Quel est le perso qui attaque
	* @param object &$victime  Qui est le perso attaqué
	* @param string $portee  Portee désirée par le joueur
	*
	* @return bool Retourne TRUE si la victime doit riposter à la fin du tour d'attaque
	*/
	public static function attaquer(&$perso, &$victime, $portee,
										&$msgPerso, &$msgVictime, &$msgLieu,
										$tour, &$esquiveNbr,
										$estUneRiposte=false)
	{
		if(DEBUG_MODE)
			echo "<br />ID Vict.:" . $victime->getId();
		
		$ripostePossible				=	true; //Les attaques à mains nues sont toutes ripostable car la porté est TC
		$bonus1							=	1.00;
		$bonus2							=	1.00;
		$bonus3							=	1.00;
		$dommagesBonus					=	1.00;
		
		//Vérifier si la furtivité réussie
		//var_dump($_POST['furtif']);
		//var_dump($_POST['type_att']);
		
		if(isset($_POST['furtif']) && $tour==1)
		{
			
			$tauxReussite = $perso->getChancesReussite("FRTV");
			$de = rand(1,100);
			
			
			if(DEBUG_MODE)
				echo "<br /> Test furtivité [Dé={$de}, TauxRéussite={$tauxReussite}]";
			
			
			if($de < $tauxReussite)
			{
				$msgPerso	.= "\nVous arrivez à vous approcher furtivement de votre victime.";
				$msgVictime	.= "\nUne personne s'approche furtivement de vous sans que vous ne vous vous en rendiez compte.";
				$msgLieu	.= "\nUne personne s'approche discrètement d'une autre.";
				$bonus1		=	1.30;	// + 30% de chances de réussir l'attaque
				$ripostePossible			=	false;
			}
			else
			{
				$msgPerso	.= "\nVous essayez de vous approcher furtivement de votre victime, mais vous vous faite repérer.";
				$msgVictime	.= "\nUne personne tente de s'approcher furtivement de vous avec une démarche suspecte.";
				$msgLieu	.= "\nUne personne s'approche discrètement d'une autre, mais cette dernière l'appercoit.";
				$bonus1		=	0.90;	// -10% de chances de réussir l'attaque
			}
		}
		
		//Vérifier si l'attaque est ciblée
		if ($_POST['type_att'] == 'cible')
		{
			$bonus2			=	0.75; //-25% de chances de réussir l'attaque
			$dommagesBonus	=	1.15; //15% de dommages en plus
		}
		
		
		$persoArme	= $perso->getArme();
		
		$bonus3 = self::tblDistance($portee);
		
		
		
		//Effectuer X attaques, selon la cadence de l'arme (tir par tour)
		for($tir=1; $tir<=$persoArme->getTirParTour(); $tir++)
		{
			//Tester la fiabilité de l'arme
			$de = rand(1,100);
			
			
			if(DEBUG_MODE)
				echo "<br /> Tir #{$tir}";
			
			if(DEBUG_MODE)
				echo "<br /> Test fiabilité [Dé={$de}, ArmeFiabilite=" . $perso->getArme()->getFiabilite() . "]";
			
			
			if($persoArme->getMunition()==0) //Il ne reste plus de munition
			{
				if (!$estUneRiposte)
				{
					$msgPerso	.= "\nVous êtes à court de munition.";
					$msgVictime	.= "\nVotre aggresseur est à court de munition.";
				}
				else
				{
					$msgPerso	.= "\nVotre victime est à court de munition.";
					$msgVictime	.= "\nVous êtes à court de munition.";
				}
				break; //Fin de l'attaque
			}
			
			
			if ($de >= $persoArme->getFiabilite()) //l'arme ÉCHOUE le test de fiabilité
			{ 
				if (!$estUneRiposte)
				{
					$msgPerso	.= "\nL'arme semble mal fonctionner, l'attaque échoue.";
					$msgVictime	.= "\nL'arme de votre aggresseur semble mal fonctionner, son attaque échoue.";
				}
				else
				{
					$msgPerso	.= "\nL'arme de votre victime semble mal fonctionner, l'attaque échoue.";
					$msgVictime	.= "\nVotre arme semble mal fonctionner et l'attaque échoue.";
				}
				
				//Test critique : l'arme s'endommage
				if($de > 90)
				{
					//Valeur arbitraire pour la dégradation des armes
					$degArme = 3;
					$persoArme->changeResistance('-', $degArme);
					if (!$estUneRiposte)
					{
						$msgPerso	.= " Votre arme s'endommage de {$degArme}.";
					}
					else
					{
						$msgVictime	.= " Votre arme s'endommage de {$degArme}.";
					}
				}
				break; //Fin de l'attaque
			}
			
			
			
			//Rabaisser les munitions de l'arme
			$persoArme->useMunition(1); // Munition -=1
			
			
			//Calcul du taux de réussite
			$tauxReussiteARM = $perso->getChancesReussite("ARMF");
			if($victime->getEsquive())
				$tauxReussiteESQ = $victime->getChancesReussite("ESQV");
			else
				$tauxReussiteESQ = 0;
				
			$tauxReussite = ($tauxReussiteARM + (100-$tauxReussiteESQ) ) /2 * $bonus1 * $bonus2 * $bonus3;
			$de = rand(1,100);
			
			
			if(DEBUG_MODE)
				echo "<br /> Test réussite [Dé={$de}, TauxRéussite={$tauxReussite}]";
			
			if ($de >= $tauxReussite && $victime->getEsquive())//L'attaque ÉCHOUE car la victime esquive
			{
				if ($victime->isConscient())
				{
					$esquiveNbr++;
					if (!$estUneRiposte)
					{
						$msgPerso	.= "\nVotre attaque échoue, votre victime esquive.";
						$msgVictime	.= "\nVous arrivez à esquiver l'attaque.";
						$msgLieu	.= "\nLa victime arrive à esquiver l'attaque.";
					}
					else
					{
						$msgPerso	.= "\nVotre victime tente une riposte mais vous esquivez.";
						$msgVictime	.= "\nVous tentez une riposte mais votre opposant esquive.";
						$msgLieu	.= "\nLa victime tente une attaque mais se fait esquiver.";
					}
				}
				else
				{
					if (!$estUneRiposte)
					{
						$msgPerso	.= "\nVotre attaque échoue.";
						$msgVictime	.= "\nL'attaque ne vous atteint pas.";
						$msgLieu	.= "\nL'aggresseur rate la victime inconsciente.";
					}
					else
					{
						$msgPerso	.= "\nL'attaque ne vous atteint pas.";
						$msgVictime	.= "\nVotre attaque échoue.";
						$msgLieu	.= "\nLa victime rate l'aggresseur inconscient.";
					}
				}
			}
			else //L'attaque réussit, calculer les dommages à la victime
			{
					
					
					//Trouver la localisation du coup porté
					if ($_POST['type_att'] == 'cible')
						//$localisationDuCoup['nom'] = $_POST['zones'];
						$localisationDuCoup = Member_Action_Perso_Attaquer2::infoDuCoupCible($_POST['zones']);
					else
						$localisationDuCoup = Member_Action_Perso_Attaquer2::localisationDuCoup($tauxReussite);
					
					$txtLoc = Member_Action_Perso_Attaquer2::getTxtLoc($localisationDuCoup['nom'], true);
					
					
					
					//Calculer les dégats infligés à la victime
					$armfId = $perso->convCompCodeToId('ARMF');
					
					$effMin = 0.50; //L'arme affectera au minimum de 50% de sa force
					$eff100 = $perso->getCompRealLevel($armfId) /12; //F5 = 0.46
					$efficacite = $effMin + ($effMin*$eff100); //F5 = 0.70
					
					if(DEBUG_MODE)
						echo 'Eff.: ' . $efficacite;
					
					$degats = $persoArme->getForce() * $efficacite;
					$degats *= $localisationDuCoup['multiplicateur']; //Modifier les dégats non-absorbé en fonction de la zone touchée. 
					$degats *= $dommagesBonus;
					$degats = round($degats);
					
					//Vérifier si la victime a une armure à l'endroit du coup
					$i=0;
					$classe = 'Member_ItemDefense' . $localisationDuCoup['nom'];
					while( $item = $victime->getInventaire($i++))
					{
						if($item instanceof $classe)
						{
							if($item->isEquip())
							{
								$victimeDefense = $item;
								break;
							}
						}
					}
					
					
					// La victime possède une armure
					if(isset($victimeDefense))
					{
						
						//Calculer les dégats absorbées par l'armure, et mettre à jours les messages
						$degats = $victimeDefense->absorbDamage($degats, $msgPerso, $msgVictime, $txtLoc, $estUneRiposte);
						$msgPerso	.= ".";
						$msgVictime	.= ".";
					}
					
					//Mettre à jour les PV de la victime					
					$victime->changePv('-', $degats);
					
					if (!$estUneRiposte)
					{
						$msgPerso	.= "\nVous arrivez à blesser votre victime {$txtLoc} de {$degats} PV.";
						$msgVictime	.= "\nVotre aggresseur arrive à vous blesser {$txtLoc} de {$degats} PV.";
						$msgLieu	.= "\nL'aggresseur arrive à porter un coup {$txtLoc}.";
					}
					else
					{
						$msgPerso	.= "\nVotre victime arrive à vous infliger {$degats} PV de dégat {$txtLoc}.";
						$msgVictime	.= "\nVous arrivez à blesser votre aggresseur {$txtLoc} de {$degats} PV.";
						$msgLieu	.= "\nLa victime arrive à porter un coup {$txtLoc}.";
					}
			}
			
			
		} // Fin de la boucle des tirs/tour
		$persoArme->setMunition($persoArme->getMunition());
		return $ripostePossible;
		
	}
	
	
	private static function tblDistance($portee)
	{
		switch ($portee)
		{
			case 'TC':	return 0.80;	break;
			case 'C':	return 1.15;	break;
			case 'M':	return 1.00;	break;
			case 'L':	return 0.85;	break;
			case 'TL':	return 0.70;	break;
		}
	}
}
