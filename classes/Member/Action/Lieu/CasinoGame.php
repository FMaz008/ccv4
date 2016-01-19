<?php
/** Gestion des jeux
* AJAX
* @package Member_Action
*/
class Member_Action_Lieu_CasinoGame
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		
		if(!isset($_POST['game']))
			die('1');
		
		//Vérifier l'état du perso
		if(!$perso->isAutonome())
			die('2');
		
		
		
		//Rechercher le casino
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'casino'
				. ' WHERE casino_lieu=:nomTech'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':nomTech',	$perso->getLieu()->getNomTech(),	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		
		//Valider si le casino existe
		if (!$arr)
			die('2');
		
		$casino = new Member_Casino($arr);
		
		
		switch($_POST['game'])
		{
			case 'machine':
				self::machine($db, $perso, $casino);
				break;
		}
		
		
		
		
			
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/CasinoMachine.htm',__FILE__,__LINE__);
		
	}
	
	
	private static function machine(&$db, &$perso, $casino)
	{
		$arrChr = array('!', '?', 'Z', '$', '0', 'C', '&', '%', '@', '+', '~', '*', '#');
		$mise = 500;
		$grosLot = 50000;

		if($perso->getCash()<$mise)
			die('OK|-|-|-|Veuillez insérer une pièce.|' . $perso->getCash() . '|' . $perso->getPa());

		
		$a = rand(0, count($arrChr)-1);
		$b = rand(0, count($arrChr)-1);
		$c = rand(0, count($arrChr)-1);
		$cash = 0;
		$msg = "Rien :(";
		
		if($a===$b || $a===$c || $b===$c)
		{
			//Petit Lot
			$cash = $mise*2;
			$msg = '!! GAGNANT ' . $cash . ' ' . GAME_DEVISE . ' !!';
		}
		
		if($a===$b && $a===$c)
		{
			//Gros Lot
			$cash = $grosLot;
			$msg = '!!! GROS LOT ' . $cash . ' ' . GAME_DEVISE . ' !!!';
		}
		
		$cash = $cash>$casino->getCash() ? $casino->getCash() : $cash;
		if($cash===0)
		{
			$perso->changeCash('-', $mise);
			$casino->changeCash('+', $mise);
		}
		else
		{
			$perso->changeCash('+', $cash);
			$casino->changeCash('-', $cash);
		}
		$perso->changePa('-', 2);
		$perso->setCash();
		$perso->setPa();
		$casino->setCash();
		
		
		die('OK' . '|' . $arrChr[$a] . '|' . $arrChr[$b] . '|' . $arrChr[$c] . '|' . $msg . '|' . $perso->getCash() . '|' . $perso->getPa());
		
	}
}

