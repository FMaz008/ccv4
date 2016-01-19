<?php
/** Gestion de l'action dépouiller un personnage (Dépouiller les items choisient sur la victime)
*
* @package Member_Action
*/
class Member_Action_Perso_Depouiller2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl = '?popup=1&amp;m=Action_Perso_Depouiller';
		$errorUrl2= '?popup=1&amp;m=Action_Perso_DepouillerList';
		
		$pa_cout_item = 0; //Cout en PA par item
		
		
		//Vérifier l'état du perso
		if(!$perso->isConscient())
			return fctErrorMSG('Vous n\'êtes pas en état de pouvoir effectuer cette action.', $errorUrl);
		
		
		//Vérifier si un personnage est sélectionné
		if(!isset($_POST['persoid']))
			return fctErrorMSG('Aucun personnage sélectionné.', $errorUrl);
		
		
		//Vérifier si le montant à retirer est bien recu
		if(!isset($_POST['cash']))
			return fctErrorMSG('Aucun montant recu.', $errorUrl2, $_POST);
		
		if(!is_numeric($_POST['cash']))
			return fctErrorMSG('Le montant doit être numérique.', $errorUrl2, $_POST);
		
		//Trouver la victime
		$i=0;
		while( $tmp = $perso->getLieu()->getPerso($perso, $i++))
		{
			if($tmp->getId() == $_POST['persoid'])
			{
				$victime = $tmp;
				break;
			}
		}
		
		//Valider si personnage que l'on veux dépouiller est actuellement accessible
		if(!isset($victime))
			return fctErrorMSG('Ce personnage n\'est pas accessible (innexistant ou déplacé).', $errorUrl2, $_POST);
		
		
		//Valider si le personnage que l'on veux dépouiller est bien non-autonome
		if($victime->isAutonome() && $victime->getPa()>0)
		{
			//Valider si on a une autorisation pour fouiller la personne
			$query = 'SELECT toid'
						. ' FROM ' . DB_PREFIX . 'perso_fouille'
						. ' WHERE fromid=:fromId'
							. ' AND toid=:toId'
						. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':fromId',	$perso->getId(),	PDO::PARAM_INT);
			$prep->bindValue(':toId',	$victime->getId(),	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$arr = $prep->fetch();
			$prep->closeCursor();
			$prep = NULL;
			
			if($arr === false)
				return fctErrorMSG('Ce personnage est en mesure de se défendre. Vous devez lui demander la permission... ou l\'amocher un peu... ;) (cheat).', $errorUrl2, $_POST);
		}
			
		
		
		if($victime->getCash()<$_POST['cash'])
			return fctErrorMSG('Vous ne pouvez pas prendre plus d\'argent que la victime possède.', $errorUrl2, $_POST);
		
		if($_POST['cash'] < 0)
			return fctErrorMSG('Vous ne pouvez pas prendre un montant négatif.', $errorUrl2, $_POST);
		
		
		
		$str_items = '';
		$prcost=0;
		$pacost=0;
		//Lister les items possédé par la victime
		$items=array();
		$i = 0;
		while( $item = $victime->getInventaire($i++))
		{
			echo 'tour ' . $i . ' ';
			if(isset($_POST['chk_items_' . $item->getInvId()]))
			{
				$pacost += $pa_cout_item;
				$prcost += $item->getPr();
				$items[] = $item;
				$str_items .= (($str_items=='') ? '' : ', ') . '[i]' . $item->getNom() . '[/i] x' . $_POST['qte_' . $item->getInvId()];
				if($_POST['qte_' . $item->getInvId()] > $item->getQte())
					return fctErrorMSG('Vous ne pouvez pas prendre plus d\'items que votre victime n\'en possède.', $errorUrl2, $_POST);
			}
		}
		
		//Valider les PA
		if($perso->getPa()<=$pacost)
			return fctErrorMSG('Vous n\'avez pas assez de PA.',$errorUrl2, array('persoid'=>$_POST['persoid']));
		
		
		
		//Valider PR
		if($perso->getPrMax()-$perso->getPr()<$prcost && $prcost>0)
			return fctErrorMSG('Vous n\'avez pas assez de PR disponible.', $errorUrl2, array('persoid'=>$_POST['persoid']));
		
		
		
		$perso->changePa('-', $pacost);
		$perso->setPa();
		
		
		//Tranférer les items de la victime vers l'inventaire du perso
		if(isset($items))
		{
			foreach($items as $item)
				$item->transfererVersPerso($perso, $_POST['qte_' . $item->getInvId()]);
		
			$perso->refreshInventaire();
		}
		
		//Transférer l'argent
		if($_POST['cash']>0)
		{
			$victime->changeCash('-', $_POST['cash']);
			$perso->changeCash('+', $_POST['cash']);
			$victime->setCash();
			$perso->setCash();
			$str_items .= (($str_items=='') ? '' : ', ') . $_POST['cash'] . GAME_DEVISE;
		}
		
		//Ajouter les messages au HE
		Member_He::add($perso->getId(), $victime->getId(), 'depouiller', "Vous dépouillez une personne: " . $str_items . '.', HE_TOUS, HE_AUCUN);
		Member_He::add($perso->getId(), $victime->getId(), 'depouiller', "Vous vous faites dépouiller: " . $str_items . '.', HE_AUCUN, HE_TOUS);
		
		
		
		
		
		//Envoyer le message à tout les gens présent sur le lieu
		$i=0;
		$arrPersoLieu = array();
		while( $tmp = $perso->getLieu()->getPerso($perso, $i++))
			if($tmp->getId() != $perso->getId() && $tmp->getId() != $victime->getId())
				$arrPersoLieu[count($arrPersoLieu)] = $tmp->getId();
		Member_He::add(array($perso->getId(), $victime->getId()), $arrPersoLieu, 'depouiller', "Vous voyez une personne en dépouiller une autre", HE_AUCUN, HE_TOUS);
		
		
		
		
		
		//Rafraichir le HE
		if(!DEBUG_MODE)
			return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}

