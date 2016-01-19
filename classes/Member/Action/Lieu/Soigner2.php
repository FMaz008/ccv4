<?php
/** Soigner un personnage depuis un lieu (blessures + graves): But générer un template des personnes blessées léger à soigner
*
* @package Member_Action
*/
class Member_Action_Lieu_Soigner2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl = '?popup=1&amp;m=Action_Lieu_Soigner';
		
		
		if(!$perso->isAutonome())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.');
			
		$pvSuplementaires=0.75; //1pa soigne 0.75pv
		$facteurResistance = 3; //1résitance soigne 3 pv
		
		
		
		
		if(!isset($_POST['paSoin']))
			return fctErrorMSG('Variable requise manquante : PA.', $errorUrl);
		
		$pa = (int)$_POST['paSoin'];//Nombre de PAs saisis par l'utilisateur
		
		if(!is_numeric($pa))
			return fctErrorMSG('La valeur des PA doit être numérique.', $errorUrl);
		
		if(!isset($_POST['blesse']))
			return fctErrorMSG('Aucun personnage à soigner sélectionné', $errorUrl);
		
		if($perso->getLieu()->getQteMateriel()<=0)
			return fctErrorMSG('Le lieu ne contient plus de matériel médical.', $errorUrl);
		
		
		//Récup du perso à soigner
		$i=0;
		$blesse = null;
		while($arrPerso = $perso->getLieu()->getPerso($perso, $i++))
		{
			if(($arrPerso->getId() == $_POST['blesse']))
			{
				$blesse = $arrPerso;
				break;
			}
		}
		
		//Cheat, modification d'id de perso
		if($blesse == null)
			return fctErrorMSG('Cheat : Perso non soignable ou n\'étant pas dans votre lieu', $errorUrl);	
		
		
		//Si les capacités du lieu ne sont pas suffisantes pr soigner le perso
		if($blesse->getCoeffSoinNecessaire() > $perso->getLieu()->getCoeffSoin())
			return fctErrorMSG('Cette personne est dans un état trop grave pour les capacités des installations de ce lieu.', $errorUrl);
		
		
		//Pas assez de PA
		if($perso->getPa() <= $pa)
			return fctErrorMSG('Vous n\'avez pas assez de PA pour effectuer cette action.', $errorUrl);
		
		
		
		$reussiteChrg =  $perso->getChancesReussite("CHRG");
		$gainPV = 0;
		$materielUtilise = 0;
		
		//Tant que: le nombre de PA à dépenser n'est pas dépensé, que le perso n'est pas soigné, et qu'il reste de la résistance à la trousse
		for($i=0;
			($i<$pa)
			&& (round($gainPV + $blesse->getPv()) < $blesse->getPvMax())
			&& ($materielUtilise < $perso->getLieu()->getQteMateriel());
			$i++) {
			
			//Si le tour de soin est une réussite
			if(rand(1,100) < $reussiteChrg)
				$gainPV += $pvSuplementaires;
			$perso->changePa('-',1);
			
			$materielUtilise = round($gainPV / $facteurResistance);
			
			if(DEBUG_MODE)
				echo "<br />Tour#{$i} : Materiel utilisé : {$materielUtilise}, PV soignés : {$gainPV}";
		}
		$gainPV = round($gainPV);
		
		//Retirer le materiel utilisé du lieu
		$query = 'UPDATE ' . DB_PREFIX . 'lieu'
				. ' SET qteMateriel=qteMateriel-:qte'
				. ' WHERE id=:lieuId;';
		$prep = $db->prepare($query);
		$prep->bindValue(':qte',		$materielUtilise,				PDO::PARAM_INT);
		$prep->bindValue(':lieuId',		$perso->getLieu()->getId(),		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		
		$perso->setPa();
		$perso->setComp(array('CHRG' => rand(1,3) ));
		$blesse->changePv("+",$gainPV);
		$blesse->setPv($perso, 'Soigner (lieu)');
		
		
		
		Member_He::add($perso->getId(), $blesse->getId(), 'soin', 'Des soins sont prodigués et permettent de guérir ' . $gainPV . 'PV.');
		
		//Retourner le template complété/rempli
		if(!DEBUG_MODE)
			return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}
