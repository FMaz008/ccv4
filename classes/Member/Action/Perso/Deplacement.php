<?php
/** Gestion de l'interface des déplacement
*
* @package Member_Action
*/
class Member_Action_Perso_Deplacement
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Vérifier l'état du perso
		if(!$perso->isAutonome())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.');
			
		
		//Générer la liste des lieux connexes
		$i=0;
		$arrLieux = array();
		while($lien = $perso->getLieu()->getLink($i, $perso->getId()))
			$arrLieux[$i++] = $lien;
		$tpl->set('LIEUX', $arrLieux);
		
		//Générer la liste des personnages (à qui nous pourrions tenir la porte)
		$i=0; $e=0;
		$arrPersos = array();
		while( $tmp = $perso->getLieu()->getPerso($perso, $i++))
			if($tmp->getId() != $perso->getId())
				$arrPersos[$e++] = $tmp;
		$tpl->set('PERSOS', $arrPersos);
		
		//Générer la liste des personnages que nous pourrions forcer à entrer (Perso menotté ou insconscient)
		$i=0; $e=0;
		$arrPersos = array();
		while( $tmp = $perso->getLieu()->getPerso($perso, $i++))
			if($tmp->getId() != $perso->getId() && (!$tmp->isConscient() || $tmp->getMenotte() || $tmp->getPa()==0))
				$arrPersos[$e++] = $tmp;
		$tpl->set('PERSOS_DEPLACABLE', $arrPersos);
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Perso/Deplacement.htm',__FILE__,__LINE__);
	}
}

