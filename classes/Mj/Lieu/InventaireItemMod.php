<?php
/** Gestion de l'inventaire du personnage
*
* @package Mj
*/

class Mj_Lieu_InventaireItemMod
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		
		
		//Instancier le lieu
		try
		{
			$lieu = Member_LieuFactory::createFromId((int)$_GET['id']);
		}
		catch(Exception $e)
		{
			return fctErrorMSG($e->getMessage());
		}
		
			
		//Lister l'inventaire
		$i=0; $e=0;
		$arrItem = array();
		while( $item = $lieu->getItems($i++))
		{
			if(isset($_POST['inv' . $item->getInvId()]) && is_numeric($_POST['inv' . $item->getInvId()]) && $_POST['inv' . $item->getInvId()] != $item->getQte())
			{
				$item->setQte((int)$_POST['inv' . $item->getInvId()]);
			}
			if(isset($_POST[$item->getInvId() . '_cache']) && isset($_POST['inv' . $item->getInvId() . 'TauxCache']) && is_numeric($_POST['inv' . $item->getInvId() . 'TauxCache']) && isset($_POST['inv' . $item->getInvId() . 'CacheNo']) && is_numeric($_POST['inv' . $item->getInvId() . 'CacheNo']))
			{
				$item->cacherObjet($_POST['inv' . $item->getInvId() . 'TauxCache'], $_POST['inv' . $item->getInvId() . 'CacheNo']);
			}
			elseif($item->iscache())
			{
				$item->decacherObjet();
			}
				
		}
		
		//Retourner le template complété/rempli
		$tpl->set('PAGE', 'Lieu_Inventaire&id=' . $_GET['id'] );
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/redirect.htm',__FILE__,__LINE__);
	}
}

