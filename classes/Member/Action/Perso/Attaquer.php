<?php
/** Gestion de l'interface de l'attaque
*
* @package Member_Action
*/
class Member_Action_Perso_Attaquer
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Vérifier l'état du perso
		if(!$perso->isAutonome())
			return fctErrorMSG('Vous n\'êtes pas en état de pouvoir effectuer cette action.');
		
		//Générer la liste des personnages présent dans le lieu actuel
		$i=0; $e=0;
		$arrPersos = array();
		while( $tmp = $perso->getLieu()->getPerso($perso, $i++))
		{
			if($tmp->getId() != $perso->getId())
			{
				//Info sur le perso
				$arrPersos[$e]['perso'] = $tmp;
				$arrPersos[$e]['disabled'] = '';
				
				//Info sur l'arme
				$arme = $tmp->getArme();
				if($arme instanceof Member_Arme)
					$arrPersos[$e]['arme'] = $arme->getNom();
				else
					$arrPersos[$e]['arme'] = '- n/a -';
				
				//Info TXT sur l'état du perso
				if ($tmp->isNormal())
					$arrPersos[$e]['etat'] = "En santé";
				elseif ($tmp->isAutonome())
					$arrPersos[$e]['etat'] = "Légèrement blessé";
				elseif($tmp->isConscient())
					$arrPersos[$e]['etat'] = "Blessé gravement";
				elseif($tmp->isVivant())
					$arrPersos[$e]['etat'] = "Inconscient";
				else{
					$arrPersos[$e]['etat'] = "Mort";
					$arrPersos[$e]['disabled'] = 'DISABLED';
				}
					
				$e++;
			}
		}
		$tpl->set('PERSOS', $arrPersos);
		
		
		//Afficher l'arme utilisée
		if ($perso->getMenotte())
			$tpl->set('ARME_NOM', "Mains menottées");
			
		elseif($perso->getArme() instanceof Member_ItemArme)
			$tpl->set('ARME_NOM', $perso->getArme()->getNom());
			
		else
			$tpl->set('ARME_NOM', "Mains nues");
		
		//Créer la liste des tours possibles
		$i=0;
		while ((self::coutPaTotal($perso, $i+1)<$perso->getPa() || $i<9) && $i<10)
		{
			$totalPa = self::coutPaTotal($perso, $i+1);
			$tours[$i] = array('no'=>$i+1, 'pa'=>$totalPa, 'statut'=> ($totalPa<$perso->getPa()) ? '' : 'DISABLED');
			$i++;
			
		}
		$tpl->set('TOURS', $tours);
		
		
		
		//Déterminer la porté maximale possible d'utiliser (la plus petite entre le lieu et l'arme)
		$porteeArme = $perso->getArme()->getPortee();
		$dimensionLieu = $perso->getLieu()->getDimension();
		$porteeAutoriseeMax = Member_Action_Perso_Attaquer::porteePlusPetite($dimensionLieu,$porteeArme) ? $dimensionLieu : $porteeArme;
		
		
		//Créer la liste des portées possibles
		$arr = array();
		$arr[0]['code'] = 'TC';
		$arr[0]['txt'] = 'Contact direct (Bout portant)';
		
		if($perso->getArme() instanceof Member_ItemArmeFeu){
			//Permettre les portées non-directes
			if(self::porteePlusPetite('C', $porteeAutoriseeMax))
				$arr[1] = array('code' => 'C', 'txt' => 'Courte portée');
			
			if(self::porteePlusPetite('M', $porteeAutoriseeMax))
				$arr[2] = array('code' => 'M', 'txt' => 'Moyenne');
			
			if(self::porteePlusPetite('L', $porteeAutoriseeMax))
				$arr[3] = array('code' => 'L', 'txt' => 'Longue distance');
			
			if(self::porteePlusPetite('TL', $porteeAutoriseeMax))
				$arr[4] = array('code' => 'TL', 'txt' => 'Très longue distance');
		}
		$tpl->set('PORTEE', $arr);
		
		
		
		//Créer la liste des zones ciblables
		$tour_cible['pa'] = self::coutPaPourUnTour($perso, 1) + 15;
		$tpl->set('tour_cible', $tour_cible);
		
		$zones[0] = array('tech'=>'Tete','nom'=>'Tête');
		$zones[1] = array('tech'=>'Torse','nom'=>'Torse');
		$zones[2] = array('tech'=>'Bras','nom'=>'Bras');
		$zones[3] = array('tech'=>'Main','nom'=>'Main');
		$zones[4] = array('tech'=>'Jambe','nom'=>'Jambe');
		$zones[5] = array('tech'=>'Pied','nom'=>'Pied');
		$tpl->set('ZONES', $zones);
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Perso/Attaquer.htm',__FILE__,__LINE__);
	}
	
	public static function coutPaPourUnTour(&$perso, $noDuTour)
	{
		//ancien systeme avec cout en pa variable
		$coutTours = array(25,20,15,10,5);
		
		//nouveau systeme avec cout fixe
		//$coutTours = array(11,11,11,11,11);
		
		$pa = ($noDuTour > count($coutTours)) ? $coutTours[count($coutTours)-1] : $coutTours[$noDuTour-1];
		return $perso->getArme()->getPa() + $pa;
	}
	
	public static function coutPaTotal(&$perso, $nbrTours)
	{
		if ($nbrTours<0)
			return 0;
		
		$coutPa=0;
		for ($i=0;$i<$nbrTours;$i++)
			$coutPa+=self::coutPaPourUnTour($perso, $i+1);
		return $coutPa;
	}
	
	public static function porteePlusPetite($a, $b)
	{
		return (self::porteeVersInt($a) <= self::porteeVersInt($b));
	}
	
	public static function porteeVersInt($portee)
	{
		switch($portee)
		{
			case 'TC':	return 1;	break;
			case 'C':	return 2;	break;
			case 'M':	return 3;	break;
			case 'L':	return 4;	break;
			case 'TL':	return 5;	break;
		}
	}
}

