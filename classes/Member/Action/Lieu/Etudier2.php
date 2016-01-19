<?php
/** Gestion de l'Action d'étude
*
* @package Member_Action
*/
class Member_Action_Lieu_Etudier2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl = '?popup=1&amp;m=Action_Lieu_Etudier';
		
		
		//Valider l'état du perso
		if (!$perso->isConscient())
			return fctErrorMSG('Vous n\'êtes pas en état d\'effectuer cette action.', $errorUrl);
		
		
		//Valider si la compétence à été sélectionnée
		if(!isset($_POST['comp']))
			return fctErrorMSG('Vous devez sélectionner une compétence.', $errorUrl);
		
		
		//Valider si le nombre de tours à été sélectionné
		if(!isset($_POST['tours']) || !is_numeric($_POST['tours']))
			return fctErrorMSG('Vous devez saisir un nombre de tour(s) valide.', $errorUrl);
		
		
		
		//Valider la comp et trouver les données sur les couts et l'efficacité
		$query = 'SELECT *'
				. ' FROM `' . DB_PREFIX . 'lieu_etude`'
				. ' WHERE	`lieuId`=:lieuId'
					. ' AND `comp`=:comp'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',		$perso->getLieu()->getId(),		PDO::PARAM_INT);
		$prep->bindValue(':comp',		$_POST['comp'],					PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		
		//Valider si le lieu supporte l'étude de cette compétence
		if($arr === false)
			return fctErrorMSG('L\'étude de cette compétence n\'est pas possible dans ce lieu.', $errorUrl);
		
		
		
		//Traiter les données relative à l'action
		$coutCashTotal	= ($_POST['tours'] * $arr['cout_cash']);
		$coutPaTotal	= ($_POST['tours'] * $arr['cout_pa']);
		
		
		
		//Valider si le perso possède assez d'argent
		if ($perso->getCash() < $coutCashTotal)
			return fctErrorMSG('Vous n\'avez pas assez d\'argent pour effectuer cette action.', $errorUrl);
			
			
		//Valider si le perso possède assez de PA
		if ($perso->getPa() <= $coutPaTotal)
			return fctErrorMSG('Vous n\'avez pas assez de PA pour effectuer cette action.', $errorUrl);
		
		
		
		
		//Effectuer le calcul de réussite
		$newXp = 0;
		for($i=1;$i<=$_POST['tours'];$i++)
		{
			$de = rand(1,100);
			if($de < $arr['qualite_lieu']) //Réussite (Selon le % de chance que la matière soit assimilé selon l'ambiance du lieu)
				$newXp++;
		}
		
		
		//Retirer l'argent, les PA, et prendre en compte le gain de compétence
		$perso->changeCash('-', $coutCashTotal);
		$perso->changePa('-', $coutPaTotal);
		$perso->setComp(array($arr['comp'] => $newXp)); //Gain de compétence
		$perso->setCash();
		$perso->setPa();
		
		//Ajouter l'argent à la boutique
		$perso->getLieu()->changeBoutiqueCash('+', $coutCashTotal);
		$perso->getLieu()->setBoutiqueCash();
		
		//Établir un degré d'efficacité en fonction du gain de compétence et du nombre de tour utilisé
		$efficacite = ($newXp * 100 / $_POST['tours']);
		if($efficacite>80)
			$efficaciteTxt = "bonne efficacité";
		elseif($efficacite>60)
			$efficaciteTxt = "efficacité moyenne";
		else
			$efficaciteTxt = "faible efficacité";
		
		//Historique de vente d'étude
		$perso->getLieu()->addBoutiqueHistorique('vente', $coutCashTotal, 'temps d\'étude', 0, 0);
		
		//Ajouter le message au HE
		Member_He::add(NULL, $perso->getId(), 'etude', "Vous étudiez pendant $coutPaTotal Pa ($coutCashTotal" . GAME_DEVISE . ") avec une $efficaciteTxt.");
		
		
		//Rafraichir le HE
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}

