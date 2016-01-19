<?php
/** Gestion de l'interface de l'action Téléphoner: Envoyer le message
*
* @package Member_Action
*/
class Member_Action_Item_TelephonerMajCompte
{ 
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!$perso->isConscient())
			return fctErrorMSG('Vous n\'êtes pas en état d\'effectuer cette action.', $errorUrl);
		
		
		$idTel = $_POST['telephone'];
		$i=0;
		while( $item = $perso->getInventaire($i++))
		{
			if($item->getInvId() == $idTel && $item->getIdProprio() == $perso->getId())
			{
				$telephone = $item;
				break;
			}
		}
		
		$numCompte = '';
		if($telephone->getNocompte() != "")
			$numCompte = $telephone->getNobanque()."-".$telephone->getNocompte();
		
		
		$tpl->set('idTel', $telephone->getInvId());
		$tpl->set('numCompte', $numCompte);
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/TelephonerMajCompte.htm',__FILE__,__LINE__);
	}
}

