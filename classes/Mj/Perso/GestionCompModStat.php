<?php
/** Gestion des statistiques modifiant une compétence
*
* @package Mj
*/

class Mj_Perso_GestionCompModStat
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_GET['id']))
			return fctErrorMSG('Vous devez sélectionner une compétence.', '?mj=Perso_GestionComp',null,false);
		
		
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'competence'
				. ' WHERE id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',		$_GET['id'],PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr === false)
			return fctErrorMSG('Impossible de trouver cette compétence.', '?mj=Perso_GestionComp',null,false);
			
		
		if(isset($_POST['save']))
		{
			
			//Établir la liste des compétences
			$query = 'SELECT s.*, cs.stat_multi'
					. ' FROM ' . DB_PREFIX . 'stat as s'
					. ' LEFT JOIN ' . DB_PREFIX . 'competence_stat as cs'
						. ' ON (cs.statid = s.id AND cs.compid=:id);';
			$prep = $db->prepare($query);
			$prep->bindValue(':id',		$_GET['id'],				PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$arrAll = $prep->fetchAll();
			$prep->closeCursor();
			$prep = NULL;


			$query = 'INSERT INTO ' . DB_PREFIX . 'competence_stat'
					. ' (`compid`, `statid`, `stat_multi`)'
					. ' VALUES'
					. ' (:compId, :statId, :multi);';
			$prepIns = $db->prepare($query);

			$query = 'UPDATE ' . DB_PREFIX . 'competence_stat'
					. ' SET stat_multi=:multi'
					. ' WHERE compid = :compId'
						. ' AND statid=:statId'
					. ' LIMIT 1;';
			$prepUpd = $db->prepare($query);

			$query = 'DELETE FROM ' . DB_PREFIX . 'competence_stat'
					. ' WHERE compid = :compId'
						. ' AND statid=:statId'
					. ' LIMIT 1;';
			$prepDel = $db->prepare($query);
			
			foreach($arrAll as &$arr)
			{
				$fieldName = 'multi_' . $arr['abbr'];
				if(isset($_POST[$fieldName]))
				{
					if(!empty($_POST[$fieldName]) && !is_numeric($_POST[$fieldName]))
					{
						unset($_POST['save']);
						return fctErrorMSG('Le multiplicateur doit être numérique.', '?mj=Perso_GestionCompModStat&id=' . $_GET['id'],$_POST,false);
					}
					
					//Ajouter le champ
					if(empty($arr['stat_multi']) && !empty($_POST[$fieldName]))
					{
						$prepIns->bindValue(':compId',		$_GET['id'],		PDO::PARAM_INT);
						$prepIns->bindValue(':statId',		$arr['id'],			PDO::PARAM_INT);
						$prepIns->bindValue(':multi',		$_POST[$fieldName],	PDO::PARAM_INT);
						$prepIns->execute($db, __FILE__, __LINE__);
					}
					
					//Modifier le champ
					if(!empty($arr['stat_multi']) && !empty($_POST[$fieldName]))
					{
						$prepUpd->bindValue(':compId',		$_GET['id'],		PDO::PARAM_INT);
						$prepUpd->bindValue(':statId',		$arr['id'],			PDO::PARAM_INT);
						$prepUpd->bindValue(':multi',		$_POST[$fieldName],	PDO::PARAM_INT);
						$prepUpd->execute($db, __FILE__, __LINE__);
					}
					
					//Supprimer le champ
					if(!empty($arr['stat_multi']) && empty($_POST[$fieldName]))
					{
						$prepDel->bindValue(':compId',		$_GET['id'],	PDO::PARAM_INT);
						$prepDel->bindValue(':statId',		$arr['id'],		PDO::PARAM_INT);
						$prepDel->execute($db, __FILE__, __LINE__);
					}
					 
				} 
			
			}
			$prepDel->closeCursor();
			$prepDel = NULL;
			$prepIns->closeCursor();
			$prepIns = NULL;
			$prepUpd->closeCursor();
			$prepUpd = NULL;
			
			
			//Retourner le template complété/rempli
			$tpl->set('PAGE', 'Perso_GestionComp');
			return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/redirect.htm',__FILE__,__LINE__);

		}
		
		
		//Établir la liste des compétences
		$query = 'SELECT s.*, cs.stat_multi'
				. ' FROM ' . DB_PREFIX . 'stat as s'
				. ' LEFT JOIN ' . DB_PREFIX . 'competence_stat as cs'
					. ' ON (cs.statid = s.id AND cs.compid=:compId)'
				. ' ORDER BY s.nom;';
		$prep = $db->prepare($query);
		$prep->bindValue(':compId',		$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		foreach($arrAll as &$arr)
		{
			$arr['nom'] = stripslashes($arr['nom']);
			$arr['desc'] = stripslashes($arr['description']);
			if($arr['stat_multi']===null)
				$arr['stat_multi'] = '';
		}
		$tpl->set('STAT', $arrAll);
		$tpl->set('COMP_ID', $_GET['id']);
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Perso/GestionCompModStat.htm',__FILE__,__LINE__);
	}
}

