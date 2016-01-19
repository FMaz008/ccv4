<?php
/** Envoi de messages a un personnage
*
* @package Mj
*/
class Mj_Perso_Sendmsg
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if (isset($_POST['send']))
		{
			//Envoyer le message
			Member_He::add($_POST['from'], $_POST['persoid'], 'mj', $_POST['msg']);
			
			$tpl->set("STATUS_MSG", "Message envoyé.");
		}
		
		//Fetcher toutes les informations concernant le perso
		$query = 'SELECT nom'
				. ' FROM ' . DB_PREFIX . 'perso'
				. ' WHERE id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',			$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
			
		$tpl->set("PERSO_ID", $_GET['id']);
		$tpl->set("MJ_NOM", $mj->getNom());
		$tpl->set("PERSO_NOM", $arr['nom']);
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Perso/Sendmsg.htm'); 
	}
}


