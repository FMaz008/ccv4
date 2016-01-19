<?php
/** Gestion de l'action de mise à jour du répertoire d'un téléphone
* AJAX
* @package Member_Action
*/
class Member_Action_Item_TelephonerMajCompte2 { 
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
			}
		}	
		
		
		
		try
		{
			$telephone->majCompte($_POST['compte'],$_POST['nip']);	
		}
		catch(Exception $e)
		{
			die($e->getMessage());
		}
		
		die('OK');
	}
}
