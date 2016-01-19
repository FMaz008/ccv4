<?php
/** Gestion du visionnement des livres en bibliothèque
*
* @package Member_Action
*/
class Member_Action_Lieu_Etudier
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Valider l'état du perso
		if (!$perso->isConscient())
			return fctErrorMSG('Vous n\'êtes pas en état d\'effectuer cette action.');
		
		
		//Afficher la liste des sujets étudiable dans ce lieu
		$query = 'SELECT *'
				. ' FROM `' . DB_PREFIX . 'lieu_etude`'
				. ' WHERE lieuId=:lieuId;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',		$perso->getLieu()->getId(),		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		
		//Générer la liste des compétences(sujets) étudiables
		
		if (count($arrAll) != 0)
		{
			$COMPS = array();
			foreach($arrAll as &$arr)
			{
				$arr['matiere'] = $perso->getCompName($perso->convCompCodeToId(strtoupper($arr['comp'])));
				$COMPS[] = $arr;
			}
			
			//Passer la liste au template, uniquement s'il y a au au moins 1 sujet étudiable
			$tpl->set("COMPS",$COMPS);
		}
		
		
		
		//Retourner le template bâti.
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Etudier.htm',__FILE__,__LINE__);
	}
}
