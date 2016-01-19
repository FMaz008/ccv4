<?php
/** Gestion de l'interface d'une boutique
*
* @package Member_Action
*/
class Member_Action_Perso_Donnercash2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		$errorUrl = '?popup=1&amp;m=Action_Perso_Donnercash';
		
		//Déclaration des variables pour cette action
		$pacost = 2; //PA par item
		
		//Vérifier l'état du perso
		if(!$perso->isConscient())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.',$errorUrl);
		
		
		//Créer le template
		if($perso->getPa()<=$pacost)
			return fctErrorMSG('Vous n\'avez pas assez de PA.',$errorUrl);
		
		//Cash spécifié ?
		if(!isset($_POST['cash']))
			return fctErrorMSG('Aucun montant.',$errorUrl);
			
		//Vérifier la validité du montant
		if(!is_numeric($_POST['cash']) || $_POST['cash']<1)
			return fctErrorMSG('Montant invalide',$errorUrl);
		
		if($_POST['cash']>$perso->getCash())
			return fctErrorMSG('Vous ne pouvez pas transf&eacute;rer plus que vous poss&eacute;dez.',$errorUrl);
		
		
		//Vérifier si un perso a bien été sélectionné
                if ( !is_numeric($_POST['toPersoId']) )
                	return fctErrorMSG('Vous n\'avez pas s&eacute;lectionn&eacute; de destinataire.',$errorUrl);
		
		//Vérifier si le perso à qui donner l'argent est présent dans le bon lieu
		$found = false;
		$i=0;
		while($toPerso = $perso->getLieu()->getPerso($perso, $i++))
		{
			if($toPerso->getId() == $_POST['toPersoId'])
			{
				$found=true;
				break;
			}
		}
		if(!$found)
			return fctErrorMSG('Ce personnage n\'est pas dans le lieu ou vous vous trouvez actuellement.',$errorUrl);
		

		//Tranférer l'item du perso actuel vers l'autre perso.
		$perso->changePa('-', $pacost);
		$perso->setPa();
		
		$perso->changeCash('-', $_POST['cash']);
		$perso->setCash();
		
		$toPerso->changeCash('+', $_POST['cash']);
		$toPerso->setCash();
		
		
		Member_He::add($perso->getId(), $toPerso->getId(), 'donner', "Montant d'argent transféré: " . fctCreditFormat($_POST['cash'], true));
		
		
		//Rafraichir le HE
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}

