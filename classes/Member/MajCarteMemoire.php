<?php
/**Editer le contenu d'une carte memoire
 *
 * @package Member
 * @todo: Cette classe n'à rien à faire ici, elle devrait être dans les actions, ou intégré à l'item
 */

//RESTE A FAIRE:
//voir pour le cryptage Aller :\
class Member_Action_MajCarteMemoire
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl = '?popup=1&m=Action_AfficherCartesMemoire';
	
	
		//decomposition de la var crée pour savoir type et ip (ordi ou cartemem) + mise en var
		$memoireT = explode('-', $_POST['idtype']);	
		$type_mem = $memoireT[0];
		$idMemoire = $memoireT[1];
	
	
		

		//récup du lecteur
		$i=0;
		while( $item = $perso->getInventaire($i++))
		{
			if($item instanceof 'Member_ItemOrdinateur' && $item->getInvId() == $_POST['lecteur']){
				$lecteur = $item;
				break;
			}
		}
		
		//récup contenu des cartes mémoires ou lecteurs
		$i=0;
		while( $item = $perso->getInventaire($i++))
		{
			if(DEBUG_MODE)
				echo $item . "<br />";
			
			if($item->getInvId() == $idMemoire)
			{
				$valid = false;
				if($type_mem=='lect' && $item instanceof Member_ItemOrdinateur)
					$valid = true;
			
				if($type_mem=='cm' && $item instanceof Member_ItemCartememoire)
					$valid = true;
				
			
				if($valid)
					$memoire = $item;
			}
			
		}
		
		
		if($memoire == NULL)
			return fctErrorMSG('Erreur avec ce lecteur de cartes', $errorUrl);	
		
					
		//verif de tous les post (numeric +  validité clef + existance d'un lecteur)
		if((!is_numeric($_POST['clef'])))
			return fctErrorMSG('Ceci n\'est pas une clef de cryptage numérique', $errorUrl);
		
		
		
		if(($_POST['clef_crypt'] != md5($memoire->getKey()) ) && ($memoire->getKey() != NULL) && ($memoire->getKey() != 0))
			return fctErrorMSG('Le contenu de cette mémoire n\'a pas pu être décrypté avec cette clef', $errorUrl);
		
		
		
		//Calculer la taille du nouveau contenu
		$nombre_carac = strlen($_POST['edit']);
		
		
		//Valider si la taille du contenu peut être stocké dans l'espace disponible
		if($nombre_carac > $memoire->getMemorySizeMax())
			return fctErrorMSG('le contenu que vous avez tapé est trop grand', $errorUrl);
		
		
		$carac_restant = $nombre_carac;//Pour enregistrer la mémoire actuellement utilisée


		if($_POST['change_clef'] == NULL)
		{
		
			if((!is_numeric($_POST['clef'])) AND ($_POST['clef'] != NULL))
				return fctErrorMSG('Ceci n\'est pas une clef de cryptage numérique', $errorUrl);
			
			$clef_save = $_POST['clef'];
			
		}
		
		
		if($_POST['change_clef'] == 'on')
			$clef_save = $memoire->getKey();
		
		
		//fin vérifs
		
		//$msg_text =	replace_specials($_POST['edit'],'add',false,false);
		$lecteur->majMem($idMem,$_POST['edit'],$_POST['clef']);
	

		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/majCarteMemoire.htm',__FILE__,__LINE__);	
	}
}

