<?php
/** Gestion de l'action frapper à une porte
*
* @package Member_Action
*
*/

class Member_Action_Perso_FrapperPorte
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl = '?popup=1&amp;m=Action_Perso_Deplacement';
		
		$coutPa = 0;
		
		//Valider l'état du perso
		if(!$perso->isAutonome())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.', $errorUrl);
		
		
		//Valider si un lieu de destination à été recu
		if(!isset($_POST['frapto']))
			return fctErrorMSG('Vous devez sélectionner la porte sur laquelle vous désirez frapper.', $errorUrl);
		
		//ETAPE-1: GÉNÉRER LES DONNÉES NÉCÉSSAIRE À L'ACTION:
		//Générer la liste des lieux connexes
		$i=0;
		$found=false;
		while(!$found && $lien = $perso->getLieu()->getLink($i++, $perso->getId()))
			if ($lien->getId() == $_POST['frapto'])
				$found=true;
				
		//Valider si le lieu choisi est accessible en tant que sous-lieu du lieu actuel
		if(!$found)
			return fctErrorMSG('La porte que vous avez sélectionné n\'existe pas.', $errorUrl);
			
		//Valider si le perso à suffisamment de PA pour effectuer les actions demandées
		if($perso->getPa() <= $coutPa)
			return fctErrorMSG('Vous n\'avez pas assez de PA pour effectuer cette action.', $errorUrl);
			
		//Effectuer l'action
		
		//Retirer les PA
		if ($lien->getPa() > 0)
		{
			$perso->changePa('-', $coutPa);
		}
		
		$perso->setPa();
		$msgTo;
		$msgFrom;
		$msgPerso;
		
		//Faire la liste de tout les personnages du lieu de départ
		$i=0; $e=0;
		$arrFrom=array();
		$msgFrom = "Vous voyez une personne frapper à la porte donnant sur [i]" . $lien->getNom() . "[/i].";
		while( $tmp = $perso->getLieu()->getPerso($perso, $i++))
			if($tmp->getId() != $perso->getId())
				$arrFrom[$e++] = $tmp->getId();
				
				
		//Faire la liste de tout les personnages du lieu de destination
		$i=0; $e=0;
		$arrTo=array();
		$msgTo = "Vous entendez qu'une personne frappe à la porte donnant sur [i]" . $perso->getLieu()->getNom() . "[/i].";
		while( $tmp = $lien->getPerso($perso, $i++))
			if($tmp->getId() != $perso->getId()) //Théoriquement cette validation ne peux JAMAIS arriver à ==
				$arrTo[$e++] = $tmp->getId();
		
		//Mesage dans le HE du joueur
		$msgPerso = "Vous frappez à la porte donnant sur [i]" . $lien->getNom() . "[/i].";
		
		//Vérifier si le joueur a ajouter un message
		if($_POST['msg'] != null){
			$msgTo 		.= "\n\n" . $_POST['msg'];
			$msgFrom	.= "\n\n" . $_POST['msg'];
			$msgPerso	.= "\n\n" . $_POST['msg'];
		}
		
		//Ajouter les messages dans les HE
		Member_He::add($perso->getId(), $arrFrom, 'frap', $msgFrom, HE_AUCUN, HE_TOUS);
		Member_He::add(NULL, $arrTo, 'frap', $msgTo, HE_AUCUN, HE_TOUS);
		Member_He::add(NULL, $perso->getId(), 'frap', $msgPerso, HE_AUCUN, HE_TOUS);
		
		//Rafraichir le HE
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}
