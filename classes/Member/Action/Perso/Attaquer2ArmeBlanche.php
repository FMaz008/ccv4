<?php
/** Gestion du tour d'attaque
*
* @package Member_Action
*/
class Member_Action_Perso_Attaquer2ArmeBlanche extends Member_Action_Perso_Attaquer2
{

	/** Lancer une attaque
	*
	* Exemple d'utilisation - Attaquer une victime avec une arme blanche
	* <code>
	* Member_Action_Perso_Attaquer2ArmeBlanche::attaquer($perso, $victime, 'TC', $log1, $log2, $log3, true);
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
		
		if(isset($_POST['furtif']) && $tour==1)
		{
			
			$tauxReussite = $perso->getChancesReussite("FRTV");
			$de = rand(1,100);
			
			
			
			
			if(DEBUG_MODE)
				echo "<br /> Test furtivité [Dé={$de}, TauxRéussite={$tauxReussite}]";
			
			if($de < $tauxReussite || !$victime->getEsquive())
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
		

			
		//Calcul du taux de réussite
		$tauxReussiteATT = $perso->getChancesReussite("ARMB");
		if($victime->getEsquive())
			$tauxReussiteESQ = $victime->getChancesReussite("ESQV");
		else
			$tauxReussiteESQ = 0;
		$tauxReussite = ($tauxReussiteATT + (100-$tauxReussiteESQ) ) /2 * $bonus1 * $bonus2 * $bonus3;
		$de = rand(1,100);
		
		
		if(DEBUG_MODE)
			echo "<br /> Test réussite [Dé={$de}, TauxRéussite={$tauxReussite}]";
		
		if ($de >= $tauxReussite && $victime->getEsquive())	//L'attaque ÉCHOUE car la victime esquive
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
		else	//L'attaque réussit, calculer les dommages à la victime
		{
				
				
			//Trouver la localisation du coup porté
			if ($_POST['type_att'] == 'cible')
				//$localisationDuCoup['nom'] = $_POST['zones'];
                $localisationDuCoup = Member_Action_Perso_Attaquer2::infoDuCoupCible($_POST['zones']);
			else
				$localisationDuCoup = Member_Action_Perso_Attaquer2::localisationDuCoup($tauxReussite);
			
			$txtLoc = Member_Action_Perso_Attaquer2::getTxtLoc($localisationDuCoup['nom'], true);
					
			
			//Calculer les dégats infligés à la victime
			$armbId = $perso->convCompCodeToId('ARMB');
			$degats = ($perso->getCompRealLevel($armbId)+2)/2 * $dommagesBonus;
			
			//Prendre en compte l'arme
			$degats += $persoArme->getForce() * (rand(1,100)/100);
					
			//Modifier les dégats non-absorbé en fonction de la zone touchée. 
			$degats *= $localisationDuCoup['multiplicateur'];
			
			$degats = round($degats);
			
			
			//Vérifier si la victime a une armure à l'endroit du coup
			$i=0;
			$classe='Member_ItemDefense' . $localisationDuCoup['nom'];
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
				
				
				//Vérifier si l'arme s'endommage en frappant sur l'armure				
				if($persoArme->getResistance() < $victimeDefense->getResistance()) //L'arme s'endomage car l'armure est plus résistante que l'arme
				{
					//Calculer la différence entre l'armure et la force de frape. On ne peux pas se faire plus mal que notre propre force.
					$dif = $victimeDefense->getResistance() - $persoArme->getResistance();
					if ($dif > $persoArme->getResistance())
						$dif = $persoArme->getResistance();
					if ($dif < 1)
						$dif = 1;
					
					$dif = round($dif);
					$persoArme->changeResistance('-', $dif);
					
					if (!$estUneRiposte)
					{
						$msgPerso	.= " et vous endommagez votre arme de {$dif}.";
						$msgVictime	.= ".";
						$msgLieu	.= "\nL'aggresseur frappe sur l'armure {$txtLoc} de son opposant.";
					}
					else
					{
						$msgPerso	.= ".";
						$msgVictime	.= " et vous endommagez votre arme de {$dif}.";
						$msgLieu	.= "\nLa victime frappe sur l'armure {$txtLoc} de son opposant.";
					}
				}
				else
				{
					$msgPerso	.= ".";
					$msgVictime	.= ".";
				}
				
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
		
		
		return $ripostePossible;
		
	}
}
