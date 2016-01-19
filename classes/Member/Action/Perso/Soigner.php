<?php
/** Soigner un personnage (blessures superficielles): But générer un template des personnes blessées léger à soigner
*
* @package Member_Action
*/
class Member_Action_Perso_Soigner
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
	
		//Valider l'état du perso
		if(!$perso->isConscient())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.');
		
		
		$i=0;
		$e=0;
		$persoSoignable = array();
		while($arrPerso = $perso->getLieu()->getPerso($perso, $i++))
		{
			if(($arrPerso->getPv() < $arrPerso->getPvMax()))
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
		
		$i=0; $e=0;
		$trousses=array();
		while( $item = $perso->getInventaire($i++))
		{
			if($item instanceof Member_ItemTrousse)
			{
				if($item->getResistance() > 0)
					$trousses[$e++] = $item;
			}	
		}		

	$tpl->set('PERSO_SOIGNABLE', $persoSoignable);
	$tpl->set('TROUSSES', $trousses);
	
	//Retourner le template complété/rempli
	return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Perso/Soigner.htm',__FILE__,__LINE__);	
	}
}
