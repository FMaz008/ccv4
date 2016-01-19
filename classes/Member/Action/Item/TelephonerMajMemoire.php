<?php
/** Gestion de l'interface de l'action mettre a jour et gestion du répertoire téléphonique de ce téléphone: Afficher l'interface de l'action
*
* @package Member_Action
*/
class Member_Action_Item_TelephonerMajMemoire
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
			if($item instanceof Member_ItemTelephone)
			{
				if($item->getInvId() == $idTel)
				{
					$telephone = $item;
					break;
				}
			}
		}
		
		if(!isset($telephone))
			return fctErrorMSG('Téléphone introuvable.');
		
		$tpl->set('telephone', $telephone);
		
		
		$arr = explode( ";\n",$telephone->getMemory());
		$mem=array();
		for($i=0;$i<$telephone->getMemorySizeMax();$i++)
		{
			if($i<count($arr))
			{
				$content = explode(';',$arr[$i]);
				if(count($content)==2)
				{
					$mem[$i] = $content;
				}
				else
				{
					$mem[$i] = array($content[0], '');
				}
			}
			else
			{
				$mem[$i] = array('','');
			}
		}
		$tpl->set('contenuMemoire', $mem);
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/TelephonerMajMemoire.htm',__FILE__,__LINE__);
	}
}

