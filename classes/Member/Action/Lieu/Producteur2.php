<?php
/** Gestion de l'action travailler à la production
*
* @package Member_Action
*/
class Member_Action_Lieu_Producteur2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl = '?popup=1&amp;m=Action_Lieu_Producteur';
		
		
		//Valider l'état du perso
		if (!$perso->isNormal())
			return fctErrorMSG('Vous n\'êtes pas en état d\'effectuer cette action.', $errorUrl);
		
		if ($perso->getMenotte())
			return fctErrorMSG('Vous ne pouvez pas travailler en étant menotté.');
		
		
		//Valider si des PA ont été sélectionnée
		if(!isset($_POST['pa']) || !is_numeric($_POST['pa']))
			return fctErrorMSG('Vous devez sélectionner des PA.', $errorUrl);
		
		$pa = (int)$_POST['pa'];
		
		//Trouver les informations sur le producteur
		$query = 'SELECT *'
				. ' FROM `' . DB_PREFIX . 'producteur`'
				. ' WHERE lieuId=:lieuId;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',		$perso->getLieu()->getId(),		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr === false)
			return fctErrorMSG('Ce lieu n\'est pas un producteur.', $errorUrl);
		
		
		//Validations
		if($perso->getPa() <= $pa)
			return fctErrorMSG('Vous n\'avez pas assez de PA pour effectuer cette action.', $errorUrl);
		
		if($arr['cash'] < $pa * $arr['pa_cash_ratio'])
			return fctErrorMSG('Le producteur n\'a soudainement plus besoin de vos services.', $errorUrl);
		
		
		
		//Travailler
		$cash = $pa * $arr['pa_cash_ratio'];
		$cashBonus = $cash * 0.1; //10% de bonus
		$cash = rand($cash - $cashBonus, $cash + $cashBonus);
		$cash = round($cash);
		
		$perso->changePa('-', $_POST['pa']);
		$perso->changeCash('+', $cash);
		
		$query = 'UPDATE ' . DB_PREFIX . 'producteur'
				. ' SET cash = cash-:cash,'
					. ' total_pa = total_pa+:pa'
				. ' WHERE id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':cash',	$cash,			PDO::PARAM_INT);
		$prep->bindValue(':pa',		$pa,			PDO::PARAM_INT);
		$prep->bindValue(':id',		$arr['id'],		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		$perso->setPa();
		$perso->setCash();
		
		$msg ='Vous travaillez pendant ' . $pa . '  Pa et obtenez une paie de ' . $cash . ' Cr. ';
		
		//Todo: Intégrer des COMPS ?
		//$perso->setComp(array($arr['comp'] => $newXp)); //Gain de compétence
		$msg .= $perso->setStat(array('AGI' => '-01', 'DEX' => '-02', 'FOR' => '+03' ));
		
		
		//Ajouter le message au HE
		Member_He::add('System', $perso->getId(), 'etude', $msg);
		
		
		//Rafraichir le HE
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}

