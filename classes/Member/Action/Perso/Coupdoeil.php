<?php
/** Gestion de l'interface de Coup d'oeil au lieu actuel
*
* @package Member_Action
*/
class Member_Action_Perso_Coupdoeil
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Vérifier l'état du perso
		if(!$perso->isConscient())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.');
		
		
		//Générer les informations sur le lieu actuel
		$tpl->set('LIEU_NOM',	$perso->getLieu()->getNom());
		$tpl->set('LIEU_DESC',	nl2br($perso->getLieu()->getDescription()));
		$tpl->set('LIEU_IMG', 	$perso->getLieu()->getImage());
		$tpl->set('id', 		$perso->getId());	//Afin d'éviter de s'auto-renommer.
		
		//Générer la liste des personnages présent dans le lieu actuel
		$i=0; $e=0;
		$arrPersos = array();
		while( $tmp = $perso->getLieu()->getPerso($perso, $i++)){
		
			//Info du perso
			$arrPersos[$e]['perso'] = $tmp;
			
			//Info sur l'arme (Mains nues si aucune)
			$arrPersos[$e]['arme'] = $tmp->getArme()->getNom();
			
			//Info TXT sur l'état du perso
			if ($tmp->isNormal())
				$arrPersos[$e]['etat'] = "Bien portant";
			elseif ($tmp->isAutonome())
				$arrPersos[$e]['etat'] = "Légèrement blessé";
			elseif($tmp->isConscient())
				$arrPersos[$e]['etat'] = "Blessé gravement";
			elseif($tmp->isVivant())
				$arrPersos[$e]['etat'] = "Inconscient";
			else
				$arrPersos[$e]['etat'] = "Mort";
				
			if($tmp->getPa()==0)
				$arrPersos[$e]['etat'] .= ' &amp; Paralysé';
			
			$e++;
		}
		$tpl->set('PERSOS', $arrPersos);
		
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Perso/Coupdoeil.htm',__FILE__,__LINE__);
	}
}
