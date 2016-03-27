<?php
/**
 * Page de test
 *
 * @author Quentin Virol
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Visitor
 */
 
class Visitor_Test
{
	public static function generatePage(&$tpl, &$session, &$account)
	{
		$timeNow = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));
		$timeNowAff = fctToGameTime($timeNow);
		
		$timeFutur = strtotime("+1 month", $timeNow);
		$timeFuturAff = fctToGameTime($timeFutur);
		
		$tpl->set('TIMENOW', $timeNow);
		$tpl->set('TIMENOWAFF', $timeNowAff);
		$tpl->set('TIMEFUTUR', $timeFutur);
		$tpl->set('TIMEFUTURAFF', $timeFuturAff);
		
		$transactionArr = array(	'transaction_id' => 10,
									'transaction_compte_from' => '1111-1111',
									'transaction_compte_to' => '2222-2222',
									'transaction_valeur' => 250,
									'transaction_description' => 'description test',
									'transaction_date' => $timeNow
								);
								
		$transaction = new Member_BanqueTransactionAuto($transactionArr);
		
		$tpl->set('TRANSACTIONARRAY', $transactionArr);
		$tpl->set('TRANSACTION', $transaction);
		
		$transaction2 = new Member_BanqueTransactionAuto($transactionArr);
		$transaction2->changeCompteIdTo(94);
		
		$tpl->set('TRANSACTION2', $transaction2);

        $dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante

        $query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'perso_fouille';
		$prep = $db->prepare($query);
		$prep->executePlus($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

        if(!empty($arr))
        {
			$arr['expiration'] = fctToGameTime($arr['expiration']);
        }
        $tpl->set('FOUILLE', $arr);
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Visitor/test.htm',__FILE__,__LINE__);
	}
}

