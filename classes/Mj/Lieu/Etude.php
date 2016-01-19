<?php
/** Gestion des casiers
*
* @package Mj
*/

class Mj_Lieu_Etude
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		
		if(!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id']))
			return fctErrorMSG('Données requises manquantes.');
		
		
		
		
		//Trouver le nom technique de l'ID de lieu
		$query = 'SELECT nom_technique'
				. ' FROM ' . DB_PREFIX . 'lieu'
				. ' WHERE id=:lieuId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',	$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if ($arr === false)
			return fctErrorMSG('Ce lieu n\'existe pas.');
		
		$lieuTech = $arr['nom_technique'];
		$tpl->set('LIEU_TECH', $lieuTech);
		$tpl->set('LIEU_ID', (int)$_GET['id']);
		
		
		
		$perso = new Member_Perso();
		
		
		$ARR_COMPS = array('ACRO', 'ARMB', 'ARMC', 'ARMF', 'ARML', 'ARMU', 'ARTI', 'ATHL', 'CHIM', 'CHRG', 'CROC', 'CRYP', 'CUIS', 'CYBR', 'DRSG', 'ELEC', 'ENSG', 'ESQV', 'EXPL', 'FORG', 'FRTV', 'GENE', 'HCKG', 'HRDW', 'LNCR', 'MECA', 'MRCH', 'PCKP', 'PLTG', 'PROG', 'PSYC', 'SCRS', 'TOXI');
		$COMPS = array();
		for($i=0;$i<count($ARR_COMPS);$i++)
			$COMPS[$ARR_COMPS[$i]] = array(
										'comp' => $ARR_COMPS[$i],
										'compTxt' => $perso->getCompName($perso->convCompCodeToId($ARR_COMPS[$i]))
									);
		
		
		//Trouver toutes les compétences déjà enregistrées
		$query = 'SELECT *'
					. ' FROM ' . DB_PREFIX . 'lieu_etude'
					. ' WHERE lieuId=:lieuId;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',	$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$result = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		$ALREADY_SAVED = array();
		foreach($result as $arr)
		{
			$COMPS[strtoupper($arr['comp'])]['cout_pa'] = $arr['cout_pa'];
			$COMPS[strtoupper($arr['comp'])]['cout_cash'] = $arr['cout_cash'];
			$COMPS[strtoupper($arr['comp'])]['qualite_lieu'] = $arr['qualite_lieu'];
			$COMPS[strtoupper($arr['comp'])]['chk'] = true;
		}
		$tpl->set('COMPS', $COMPS);
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/Etude.htm',__FILE__,__LINE__);
	}
}
