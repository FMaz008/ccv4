<?php
/** Envoi de messages a tous les personnages d'un lieu
*
* @package Mj
*/
class Mj_Lieu_Sendmsg
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if (isset($_POST['send']))
		{
			if(empty($_POST['arrLieux']))
			{
				$tpl->set('STATUS_MSG', "Aucun lieu spécifié.");
			}
			else
			{
				$arrLieux = explode(',', $_POST['arrLieux']);

				$arr = array();
				foreach($arrLieux as &$tmp)
					$arr[] = '?';
				
				$queryAddon = implode(',', $arr);

				
				//Effectuer la liste de tout les lieux sélectionnées
				$query = 'SELECT p.id as persoId, l.id as lieuId'
						. ' FROM ' . DB_PREFIX . 'lieu as l'
						. ' LEFT JOIN ' . DB_PREFIX . 'perso as p'
							. ' ON (p.lieu = l.nom_technique)'
						. ' WHERE l.id IN (' . $queryAddon . ')'
						. ' ORDER BY l.id ASC;';
				$prep = $db->prepare($query);
				
				for($i=1; $i<=count($arrLieux); $i++)
					$prep->bindValue($i, $arrLieux[$i-1],	PDO::PARAM_STR);
				
				$prep->execute($db, __FILE__, __LINE__);
				$arrAll = $prep->fetchAll();
				$prep->closeCursor();
				$prep = NULL;
					
				$arrLieu = array();
				foreach($arrAll as &$arr)
					if($arr['persoId'] != NULL) //Si on sélectionne un lieu vide
						$arrLieu[$arr['lieuId']][] = $arr['persoId'];
				
				//Ajouter les messages aux HE
				foreach($arrLieu as $lieu)
					Member_He::add($_POST['from'], $lieu, 'mj', $_POST['msg']);
					
				
				$tpl->set('STATUS_MSG', "Message envoyé.");
			}
		}
		$tpl->set('MJ_NOM', $mj->getNom());
		
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/Sendmsg.htm'); 
	}
}




