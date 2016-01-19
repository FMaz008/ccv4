<?php
/**Ouvrir une carte mémoire pour voir son contenu
*
* @package Member_Action
*/

class Member_Action_Item_OrdinateurMemoire
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Valider si le # d'ordi est bien recu (requis pour l'adresse de retour des erreurs)
		if(!isset($_POST['ordiId']) || !is_numeric($_POST['ordiId']))
			return fctErrorMSG('Id non numérique', '?popup=1&amp;m=Action_Item_Ordinateur');
		
		$pcId = $_POST['ordiId'];
		$errorUrl = '?popup=1&amp;m=Action_Item_Ordinateur2&amp;id=' . $pcId;
		
		
		
		
		
		//Trouver l'ordinateur demandé
		$i=0;
		$pc = false;
		while( $item = $perso->getInventaire($i++))
		{
			if($item->getInvId()==$pcId)
			{
				$pc = $item;
				break;
			}
		}
		
		//Valider si le joueur possède l'item
		if($pc===false)
			return fctErrorMSG('Cet item ne vous appartient pas.', $errorUrl);
		
		//Valider si l'item est de type ordinateur
		if(!$pc instanceof Member_ItemOrdinateur)
			return fctErrorMSG('Cet item n\'est pas un appareil informatique.', $errorUrl);
		
		
		
		
		
		
		
		//Valider si une clé à été saisie
		if($pc->isCrypt() && !isset($_POST[$pc->getInvId() . '_cle']))
			return fctErrorMSG('Vous devez spécifier une clé d\'encodage.', $errorUrl);
		
		
		if($pc->isCrypt())
		{
			//Valider si la clé saisie est numérique
			$cle = $_POST[$pc->getInvId() . '_cle'];
			if($pc->isCrypt() && !is_numeric($cle))
				return fctErrorMSG('La clé d\'encodage doit être numérique.', $errorUrl);
			
			
			//Valider si la clé d'encodage est valide
			if($pc->isCrypt() && $cle!=$pc->getKey())
				return fctErrorMSG('Le contenu de cette mémoire n\'a pas pu être décrypté avec cette clef', $errorUrl);
		}
		
		
		
		
		
		//Tout est ok, vérifier s'il faut sauvegarder la carte
		if(isset($_POST['save']))
		{
			if(!isset($_POST['data']))
				return fctErrorMSG('Impossible d\'effectuer la sauvegarde. (1)', $errorUrl);
			
			
			if(!isset($_POST['newCle']))
				return fctErrorMSG('Impossible d\'effectuer la sauvegarde. (2)', $errorUrl);
			
			
			if($_POST['newCle']!='' && !is_numeric($_POST['newCle']))
				return fctErrorMSG('La clé de cryptage devait être numérique, ou vide.', $errorUrl);
			
			
			if(strlen($_POST['data'])>$pc->getMemorySizeMax())
				return fctErrorMSG('La longueur des données dépassent la capacité de la mémoire.', $errorUrl);
			
			//Sauvegarder les informations
			$pc->setKey($_POST['newCle']);
			$pc->setMemory($_POST['data']);
			
			$tpl->set('SAVED', true);
		}
		
		
		
		$tpl->set('PC_ID',			$pc->getInvId());
		$tpl->set('PC_NOM',			$pc->getNom());
		$tpl->set('IS_CRYPT', 		$pc->isCrypt());
		$tpl->set('PC_KEY',			$pc->getKey());	
		$tpl->set('CAN_EDIT',		true); //Tout les appareils peuvent accéder à leur mémoire interne
		$tpl->set('PC_MEM',			$pc->getMemorySize());
		$tpl->set('PC_MEM_MAX',		$pc->getMemorySizeMax());
		$tpl->set('PC_DATA',		$pc->getMemory());
		$tpl->set('PC_PERM_DATA',	$pc->getPermMemory());
		
		

		
			
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/OrdinateurMemoire.htm',__FILE__,__LINE__);	
	}
}


	
