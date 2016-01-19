<?php
/** Gestion de l'interface d'une boutique
 *
 * @package Member
 * @subpackage Contact
 */
class Member_ContactMj
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Lister les sujets
		$query = 'SELECT p.*, mj.nom as mjnom'
				. ' FROM ' . DB_PREFIX . 'ppa as p'
				. ' LEFT JOIN ' . DB_PREFIX . 'mj as mj ON (mj.id = mjid)'
				. ' WHERE	p.persoid=:persoId'
				. ' ORDER BY p.`statut` ASC, p.`date` DESC'
				. ' LIMIT 17;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$perso->getId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arrM = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;


		$query = 'SELECT `date`'
				. ' FROM ' . DB_PREFIX . 'ppa_reponses'
				. ' WHERE	sujetid=:sujetId'
					. ' AND mjid!=0'
				. ' ORDER BY `date` DESC'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		
		
		$i=0;
		$arrPpa = array();
		$ouvertCount = 0;
		
		if(count($arrM) != 0)
		{
			foreach($arrM as &$arr)
			{
				$arrPpa[$i] = $arr;
				$arrPpa[$i]['titre'] = stripslashes($arr['titre']);
				$arrPpa[$i]['date'] = fctToGameTime($arr['date']);
				
				if ($arr['mjid'] == 0)
					$arrPpa[$i]['mjnom'] = 'aucun';
				else
					$arrPpa[$i]['mjnom'] = $arr['mjnom'];
				
				if($arr['statut']=='ouvert')
					$ouvertCount++;
				
				//Vérifier la dernière réponse
				$prep->bindValue(':sujetId',	$arr['id'],	PDO::PARAM_INT);
				$prep->execute($db, __FILE__,__LINE__);
				$arrRep = $prep->fetch();
				
				$arrPpa[$i]['replyDate'] = $arrRep===false ? 'En attente' : fctToGameTime($arrRep['date']);
				
				$i++;
			}
		}
		
		
		
		if(count($arrPpa) > 0)
			$tpl->set('PPA', $arrPpa);
		
		if($ouvertCount < PPA_MAX)
			$tpl->set('CAN_POST', true);
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/contactMj.htm',__FILE__,__LINE__);
	}
}

