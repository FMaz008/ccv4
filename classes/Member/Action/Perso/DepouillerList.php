<?php
/** Gestion de l'action dépouiller un personnage (Afficher la liste des items présent sur le personnage)
*
* @package Member_Action
*/
class Member_Action_Perso_DepouillerList
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		$errorUrl = '?popup=1&amp;m=Action_Perso_Depouiller';
		
		
		$pa_cost_base = 25; //Cout de base de l'action (en PA)
		$pa_cout_item = 0; //Cout supplémentaire par item (en PA)
		
		
		//Vérifier l'état du perso
		if(!$perso->isConscient())
			return fctErrorMSG('Vous n\'êtes pas en état de pouvoir effectuer cette action.', $errorUrl);
			
		//Vérifier si un personnage est sélectionné
		if(!isset($_POST['persoid']))
			return fctErrorMSG('Aucun personnage sélectionné.', $errorUrl);
		
		//Valider les PA
		if($perso->getPa()<=$pa_cost_base)
			return fctErrorMSG('Vous n\'avez pas assez de PA.', $errorUrl);
		
		
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
		
		if(!isset($victime))
			return fctErrorMSG('Ce personnage n\'est pas accessible (innexistant ou déplacé).', $errorUrl);
		
		if($victime->isAutonome() && $victime->getPa() > 0)
		{
			//Valider si on a une autorisation pour fouiller la personne
			$query = 'SELECT toid'
						. ' FROM ' . DB_PREFIX . 'perso_fouille'
						. ' WHERE fromid=:fromId'
							. ' AND toid=:toId'
							. ' AND `reponse`=1'
						. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':fromId',	$perso->getId(),	PDO::PARAM_INT);
			$prep->bindValue(':toId',	$victime->getId(),	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$arr = $prep->fetch();
			$prep->closeCursor();
			$prep = NULL;
			
			if($arr === false)
				return fctErrorMSG('Ce personnage est en mesure de se défendre. Vous devez lui demander la permission... ou l\'amocher un peu... ;)', $errorUrl);
		}
			
		
		
		//Lister les items possédé par la victime
		$i=0;
		$items=array();
		while( $item = $victime->getInventaire($i++))
			$items[$i] = $item;
		
		if(!empty($items))
			$tpl->set('INV_VICTIME', $items);
		
		
		//Cout PA
		$perso->changePa('-', $pa_cost_base);
		$perso->setPa();
		$tpl->set('PA', $perso->getPa());
		$tpl->set('PA_COUT_ITEM', $pa_cout_item);
		
		$tpl->set('CASH_VICTIME', $victime->getCash());
		$tpl->set('ID_VICTIME', $victime->getId());
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Perso/DepouillerList.htm',__FILE__,__LINE__);
	}
}
?>
