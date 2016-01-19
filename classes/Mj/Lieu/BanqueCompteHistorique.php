<?php
/** Gestion de l'interface d'affichage des historiques bancaire
*
* @package Mj
*/

class Mj_Lieu_BanqueCompteHistorique
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		$query = 'SELECT compte_banque, compte_compte'
				. ' FROM ' . DB_PREFIX . 'banque_comptes'
				. ' WHERE compte_id=:compteId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':compteId',	$_GET['id'],		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		$compte_no = $arr['compte_banque'] . '-' . $arr['compte_compte'];
		$tpl->set('COMPTE', $compte_no);
		
		
		//Lister toutes les transaction
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'banque_historique'
				. ' WHERE compte=:compteNo'
				. ' ORDER BY date ASC;';
		$prep = $db->prepare($query);
		$prep->bindValue(':compteNo',	$compte_no,		PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		
		if ($arr !== false)
		{
			$i=0;
			$HISTORIQUE = array();
			while($arr= mysql_fetch_assoc($result))
			{
				$HISTORIQUE[$i]['id']		= $arr['id'];
				$HISTORIQUE[$i]['date']		= fctToGameTime($arr['date']); //A quelle heure à été envoyé le message ?
				$HISTORIQUE[$i]['code']		= $arr['code'];
				$HISTORIQUE[$i]['depot']	= ($arr['depot']==0) ? '' : fctCreditFormat($arr['depot']);
				$HISTORIQUE[$i]['retrait']	= ($arr['retrait']==0) ? '' : fctCreditFormat($arr['retrait']);
				$HISTORIQUE[$i]['solde']	= fctCreditFormat($arr['solde']);
				$HISTORIQUE[$i]['vers']		= $arr['compte2'];
				$i++;
			}
			$tpl->set('HISTORIQUE',$HISTORIQUE);
		}
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/BanqueCompteHistorique.htm',__FILE__,__LINE__);
	}
}
