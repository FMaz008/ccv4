<?php
/** Gestion de l'interface de l'action Parler: Afficher l'interface pour parler.
*
* @package Member_Action
*/
class Member_Action_Perso_Parler
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		$i=0;
		$persoDansLeLieuActuel = array();
		while( $tmp = $perso->getLieu()->getPerso($perso, $i++))
			if($tmp->getId() != $perso->getId())
				$persoDansLeLieuActuel[] = $tmp;
		
		$tpl->set('LIST_PERSO', $persoDansLeLieuActuel);
		
		$i=0;
		$badgeEnPossessionDuPerso = array();
		while( $item = $perso->getInventaire($i++))
			if($item instanceof Member_ItemBadge)
				$badgeEnPossessionDuPerso[] = $item;
		
		$tpl->set('LIST_BADGE', $badgeEnPossessionDuPerso);
		
		if($perso->getVisaPerm()=='1')
			$tpl->set('VISA_VERT', true);
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Perso/Parler.htm',__FILE__,__LINE__);
	}
}

