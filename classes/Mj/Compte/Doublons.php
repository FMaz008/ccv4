<?php
/** Affichage des comptes s'étant connecté avec la même ip lors des 5 dernières
 *	connections.
 * 
 * @package Mj
 */

class Mj_Compte_Doublons
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante

		// récupérer les doublons
		// id - user - ip - date
		$query = 'SELECT `' . DB_PREFIX . 'account`.`id`, `temp3`.`user`, `temp3`.`ip`, `temp3`.`timestamp` FROM'
				. ' (SELECT `' . DB_PREFIX . 'log_conn`.`user`, `temp2`.`ip`, `' . DB_PREFIX . 'log_conn`.`timestamp`'
				. ' FROM `' . DB_PREFIX . 'log_conn` JOIN'
				. ' (SELECT `ip`, COUNT(`ip`) AS `nb` FROM'
				. ' (SELECT DISTINCT `user`, `ip` FROM `' . DB_PREFIX . 'log_conn` WHERE 1) AS `temp`'
				. ' GROUP BY `ip`'
				. ' HAVING `nb` > 1) AS `temp2`'
				. ' ON (`' . DB_PREFIX . 'log_conn`.`ip` = `temp2`.`ip`)'
				. ' ORDER BY `ip`, `' . DB_PREFIX . 'log_conn`.`timestamp` DESC) AS `temp3` LEFT JOIN `' . DB_PREFIX . 'account` ON (`' . DB_PREFIX . 'account`.`user` = `temp3`.`user`);';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$doublons = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		// envoie des infos à l'autre page (tpl)
		$tpl->set('DOUBLONS', $doublons);

		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Compte/doublons.htm');
	}
}

?>
