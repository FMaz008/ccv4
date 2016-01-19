<?php
/** Gestion de l'interface de gestion des banques
*
* @package Mj
*/

class Mj_Lieu_Banque
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Trouver toutes les banques et le nombre de compte ainsi que le solde des comptes
		$query = 'SELECT b.*, COUNT(c.compte_id) as comptes, SUM(compte_cash) as capital'
				. ' FROM ' . DB_PREFIX . 'banque as b'
				. ' LEFT JOIN ' . DB_PREFIX . 'banque_comptes as c ON (c.compte_banque = b.banque_no)'
				. ' GROUP BY c.compte_banque;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		if (count($arrAll)>0)
		{
			//Lister toutes les banques du jeu
			$arrBanque = array();
			foreach($arrAll as &$arr)
			{
				$arr['capital'] = fctCreditFormat($arr['capital'], true);
				$arrBanque[] = $arr;
			}
			
			$tpl->set('BANQUES',$arrBanque);
		}
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/Banque.htm',__FILE__,__LINE__);
	}
}

