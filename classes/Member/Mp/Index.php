<?php
/**
 * Affichage de l'interface d'abonnement.
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Mp
 */
class Member_Mp_Index
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Vérifier l'état du perso
		$tpl->set('MP_LVL', $account->getMemberLevel());
		$tpl->set('MP_TXT', $account->getMemberLevelTxt());
		$tpl->set('ACCOUNT_ID', $account->getId());
		$tpl->set('ACCOUNT_USER', $account->getUser());
		
		
		//Déterminer le temps restant à l'abonnement
		$tpl->set('MP_TREST', $account->getMemberRestant());
		
		
		//Historique des commandes liées à ce compte:
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'log_mp'
				. ' WHERE userId=:userId'
				. ' ORDER BY `date` DESC;';
		$prep = $db->prepare($query);
		$prep->bindValue(':userId',	$account->getId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		
		$i = 0;
		$LOGS = array();
		foreach($arrAll as &$arr)
		{
			
			$arr['date'] = date('Y/m/d H:i:s', $arr['date']);
			$arr['item'] = unserialize(stripslashes($arr['post']));
			$arr['item'] = $arr['item']['item_name'];
			
			if($arr['statusCC'] != "valide")
				$arr['statut'] = 'Transaction invalidée (CC).';
			else
				$arr['statut'] = $arr['statusPP'];
			
			$LOGS[$i] = $arr;
			$i++;
		}
		if(count($LOGS)>0)
			$tpl->set('LOGS', $LOGS);
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Mp/index.htm',__FILE__,__LINE__);
	}
}

