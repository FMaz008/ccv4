<?php
/** Soigner un personnage depuis un lieu (blessures + graves): But générer un template des personnes blessées léger à soigner
*
* @package Member_Action
*/
class Member_Action_Lieu_Soigner
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		if(!$perso->isAutonome())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.');
		
		/*
		if($lieu->getCoeffSoin() == 0)
			return fctErrorMSG('Aucun soin avancé disponible dans ce lieu. Veuillez vous reporter aux soin de la partie ');		
		*/
		
		$i=0;$e=0;
		$persoSoignable = array();
		while($arrPerso = $perso->getLieu()->getPerso($perso, $i++))
		{
			if($arrPerso->getPv() < $arrPerso->getPvMax())
			{
				$persoSoignable[$e]['perso'] = $arrPerso;
				
				//Info TXT sur l'état du perso
				if ($arrPerso->isNormal())
					$persoSoignable[$e]['etat'] = "En santé";
				elseif ($arrPerso->isAutonome())
					$persoSoignable[$e]['etat'] = "Légèrement blessé";
				elseif($arrPerso->isConscient())
					$persoSoignable[$e]['etat'] = "Blessé gravement";
				elseif($arrPerso->isVivant())
					$persoSoignable[$e]['etat'] = "Inconscient";
				else
					$persoSoignable[$e]['etat'] = "Mort";
				
				$e++;
			}
		}
		
		
		$tpl->set('PERSO_SOIGNABLE', $persoSoignable);
		$tpl->set('LIEU', $perso->getLieu());	
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Soigner.htm',__FILE__,__LINE__);	
	}
}
