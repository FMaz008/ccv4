<?php
/** Gestion d'un casier (page de modification)
*
* @package Mj
*/

class Mj_Lieu_EtudeSave
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		$COMPS = array('ACRO', 'ARMB', 'ARMC', 'ARMF', 'ARML', 'ARMU', 'ARTI', 'ATHL', 'CHIM', 'CHRG', 'CROC', 'CRYP', 'CUIS', 'CYBR', 'DRSG', 'ELEC', 'ENSG', 'ESQV', 'EXPL', 'FORG', 'FRTV', 'GENE', 'HCKG', 'HRDW', 'LNCR', 'MECA', 'MRCH', 'PCKP', 'PLTG', 'PROG', 'PSYC', 'SCRS', 'TOXI');
		
		
		//Si le lieu est spécifié, le passer au template pour créer un lien de retour
		if(!isset($_POST['LIEU_ID']))
			return fctErrorMSG('Aucun lieu spécifié.');
		
		$tpl->set('LIEU_ID', $_POST['LIEU_ID']);
		
		
		
		
		
		//Trouver toutes les compétences déjà enregistrées
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'lieu_etude'
				. ' WHERE lieuId=:lieuId;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',	$_POST['LIEU_ID'],	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__, __LINE__);
		$ALREADY_SAVED = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		
		$query = 'INSERT INTO ' . DB_PREFIX . 'lieu_etude'
				. ' (`lieuId`, `comp`, `cout_pa`, `cout_cash`, `qualite_lieu`)'
				. ' VALUES'
				. ' (:lieuId, :comp, :pa, :cash, :qualiteLieu);';
		$prepIns = $db->prepare($query);
		
		$query = 'DELETE FROM ' . DB_PREFIX . 'lieu_etude'
				. ' WHERE	`lieuId`=:lieuId'
					. ' AND `comp`=:comp'
				. ' LIMIT 1;';
		$prepDel = $db->prepare($query);
		
		$query = 'UPDATE ' . DB_PREFIX . 'lieu_etude'
				. ' SET `cout_pa` = :coutPa,'
					. ' `cout_cash` = :coutCash,'
					. ' `qualite_lieu` = :qualiteLieu'
				. ' WHERE `lieuId`=:lieuId'
					. ' AND `comp`=:comp'
				. ' LIMIT 1;';
		$prepMaj = $db->prepare($query);
		
		//Déterminer les entrés à ajouter et à supprimer
		for($i=0; $i<count($COMPS);$i++)
		{
			$comp = $COMPS[$i];
			
			$found = false;
			foreach($ALREADY_SAVED as $saved)
			{
				if(strtoupper($saved['comp'])==$comp)
				{
					$found=true;
					$break;
				}
			}
			
			if(isset($_POST[$comp]) && !$found) // Nouvel élément (Ajouter)
			{
				
				$prepIns->bindValue(':lieuId',	$_POST['LIEU_ID'],		PDO::PARAM_INT);
				$prepIns->bindValue(':comp',	$comp,					PDO::PARAM_STR);
				$prepIns->bindValue(':pa',		$_POST[$comp . '_pa'],	PDO::PARAM_INT);
				$prepIns->bindValue(':cash',	$_POST[$comp . '_cash'],PDO::PARAM_INT);
				$prepIns->bindValue(':qualiteLieu',	$_POST[$comp . '_qualite'],PDO::PARAM_STR);
				$prepIns->execute($db, __FILE__, __LINE__);
				
			}
			
			if(!isset($_POST[$comp]) && $found) // Ancien élément (Supprimer)
			{
				$prepDel->bindValue(':lieuId',	$_POST['LIEU_ID'],		PDO::PARAM_INT);
				$prepDel->bindValue(':comp',	$comp,					PDO::PARAM_STR);
				$prepDel->execute($db, __FILE__, __LINE__);
			}
			
			if(isset($_POST[$comp]) && $found) // Element existant (Mise à jour)
			{
				$prepMaj->bindValue(':lieuId',		$_POST['LIEU_ID'],		PDO::PARAM_INT);
				$prepMaj->bindValue(':comp',		$comp,					PDO::PARAM_STR);
				$prepMaj->bindValue(':coutPa',		$_POST[$comp . '_pa'],	PDO::PARAM_INT);
				$prepMaj->bindValue(':coutCash',	$_POST[$comp . '_cash'],PDO::PARAM_INT);
				$prepMaj->bindValue(':qualiteLieu',	$_POST[$comp . '_qualite'],PDO::PARAM_STR);
				$prepMaj->execute($db, __FILE__, __LINE__);
			}
		}
		$prepIns->closeCursor();
		$prepIns = NULL;	
		$prepDel->closeCursor();
		$prepDel = NULL;
		
		die("<script type=\"text/javascript\">location.href='?mj=Lieu_Etude&id=" . $_POST['LIEU_ID'] . "';</script>");
	}
}

