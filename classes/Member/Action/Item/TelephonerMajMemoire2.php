<?php
/** Gestion de l'action de mise à jour du répertoire d'un téléphone
* AJAX
* @package Member_Action
*/
class Member_Action_Item_TelephonerMajMemoire2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		if(!$perso->isConscient())
			return fctErrorMSG('Vous n\'êtes pas en état d\'effectuer cette action.', $errorUrl);
		
		
		$idTel = $_POST['idtelephone'];
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
		
		
		$i=0;
		$mem = '';
		for($i=0;$i<$telephone->getMemorySizeMax();$i++)
		{
			if(isset($_POST['nom_' . $i]) && isset($_POST['no_' . $i]))
			{
				$nom = str_replace(';','',$_POST['nom_' . $i]);
				$no = str_replace(';','',$_POST['no_' . $i]);
				
				$mem .= $nom . ';' . $no . ";\n";
			}
			else
			{
				$mem .= ";;\n";
			}
		}
		
		try
		{
			$telephone->majRepertoire($mem);
		}
		catch(Exception $e)
		{
			die($e->getMessage());
		}
		
		die('OK');
	}
}

