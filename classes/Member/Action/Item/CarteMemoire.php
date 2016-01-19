<?php
/**Ouvrir une carte mémoire pour voir son contenu
*
* @package Member_Action
*/
class Member_Action_Item_CarteMemoire
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
		
		
		//Valider si une carte a été sélectionnée
		if(!isset($_POST['carteId']) || !is_numeric($_POST['carteId']))
			return fctErrorMSG('Vous n\'avez pas sélectionné de mémoire à lire', $errorUrl);
		
		
		$carteId = $_POST['carteId'];
		
		
		
		//Trouver l'ordinateur demandé
		$i=0;
		$pc = false;
		while( $item = $perso->getInventaire($i++))
		{
			if($item->getInvId()==$pcId)
			{
				$pc = $item;
			}
		}
		
		//Valider si le joueur possède l'item
		if($pc===false)
			return fctErrorMSG('Cet item ne vous appartient pas.', $errorUrl);
		
		//Valider si l'item est de type ordinateur
		if(!$pc instanceof Member_ItemOrdinateur)
			return fctErrorMSG('Cet item n\'est pas un appareil informatique.', $errorUrl);
		
		
		
		
		
		//Rechercher toutes les cartes mémoires
		$i=0;
		$carte=false;
		while( $item = $perso->getInventaire($i++))
		{
			if($item->getInvId()==$carteId)
			{
				$carte = $item;
				break;
			}
		}
		
		//Valider si le joueur possède l'item
		if($carte===false)
			return fctErrorMSG('Cet item ne vous appartient pas.', $errorUrl);
		
		//Valider si l'item est de type ordinateur
		if(!$carte instanceof Member_ItemOrdinateur && !$carte instanceof Member_ItemCartememoire)
			return fctErrorMSG('Cet item ne possède pas de mémoire.', $errorUrl);
		
		
		
		
		//Valider si une clé à été saisie
		if($carte->isCrypt() && !isset($_POST[$carte->getInvId() . '_cle']))
			return fctErrorMSG('Vous devez spécifier une clé d\'encodage.', $errorUrl);
		
		
		
		//Valider si la clé saisie est numérique
		$cle = $_POST[$carte->getInvId() . '_cle'];
		if($carte->isCrypt() && !is_numeric($cle))
			return fctErrorMSG('La clé d\'encodage doit être numérique.', $errorUrl);
		
		
		//Valider si la clé d'encodage est valide
		if($carte->isCrypt() && $cle!=$carte->getKey())
			return fctErrorMSG('Le contenu de cette mémoire n\'a pas pu être décrypté avec cette clef', $errorUrl);
		
		
		
		
		
		
		//Tout est ok, vérifier s'il faut sauvegarder la carte
		if(isset($_POST['save']))
		{
			if(!isset($_POST['data']))
				return fctErrorMSG('Impossible d\'effectuer la sauvegarde. (1)', $errorUrl);
			
			
			if(!isset($_POST['newCle']))
				return fctErrorMSG('Impossible d\'effectuer la sauvegarde. (2)', $errorUrl);
			
			
			if($_POST['newCle']!='' && !is_numeric($_POST['newCle']))
				return fctErrorMSG('La clé de cryptage devait être numérique, ou vide.', $errorUrl);
			
			
			if(strlen($_POST['data'])>$carte->getMemorySizeMax())
				return fctErrorMSG('La longueur des données dépassent la capacité de la mémoire.', $errorUrl);
			
			//Sauvegarder les informations
			$carte->setKey($_POST['newCle']);
			$carte->setMemory($_POST['data']);
			
			$tpl->set('SAVED', true);
		}
		
		
		
		
		$tpl->set('IS_CRYPT', 		$carte->isCrypt());
		$tpl->set('CARTE_KEY',		$carte->getKey());	
		
		
		$tpl->set('CARTE_ID',		$carte->getInvId());
		$tpl->set('CARTE_NOM',		$carte->getNom());
		$tpl->set('CAN_EDIT',		$carte->getMcWrite() && $pc->getMcWrite());
		$tpl->set('CARTE_MEM',		$carte->getMemorySize());
		$tpl->set('CARTE_MEM_MAX',	$carte->getMemorySizeMax());
		$tpl->set('CARTE_DATA',		$carte->getMemory());
		$tpl->set('CARTE_PERM_DATA',$carte->getPermMemory());
		
		$tpl->set('PC_ID',			$pc->getInvId());
		$tpl->set('PC_NOM',			$pc->getNom());

		
			
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/CarteMemoire.htm',__FILE__,__LINE__);	
	}
}

	
