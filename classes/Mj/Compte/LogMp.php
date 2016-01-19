<?php
/** Affichage des 25 abonnements M+ les plus récents
*
* @package Mj
*/
class Mj_Compte_LogMp
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
	
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		$query = 'SELECT l.*, a.user'
				. ' FROM ' . DB_PREFIX . 'log_mp as l'
				. ' LEFT JOIN ' . DB_PREFIX . 'account as a ON (a.id=l.userId)'
				. ' ORDER BY `date` DESC'
				. ' LIMIT 25;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$LOGS = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		foreach($LOGS as &$arr)
		{
			
			$arr['date'] = date('Y/m/d H:i:s', $arr['date']);
			$arr['post'] = str_replace(
									'  ',
									'&nbsp;&nbsp;',
									nl2br(
										var_export(
											unserialize(
												stripslashes(
													$arr['post']
												)
											),
											true
										)
									)
								);
			
			if ($arr['statusCC']!='valide')
				$arr['statusCC'] = '<span style="color:red;">' . $arr['statusCC'] . '</span>';
			
			if ($arr['statusPP']=='invalide')
				$arr['statusPP'] = '<span style="color:red;">' . $arr['statusPP'] . '</span>';
		}
		
		//envoie des infos à l'autre page (tpl)
		$tpl->set('LOGS',$LOGS);
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Compte/logMp.htm');
	}
}

