<?php
/** Gestion des competence du jeu
*
* @package Mj
*/

class Mj_Perso_GestionCompDel
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_POST['id']))
			return fctErrorMSG('Vous devez sélectionner une compétence.', '?mj=Perso_GestionComp',null,false);
		
		$query = 'SELECT efface'
				. ' FROM ' . DB_PREFIX . 'competence'
				. ' WHERE id=:id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',		$_POST['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr === false)
			return fctErrorMSG('Cette compétence n\'existe pas.', '?mj=Perso_GestionComp',null,false);
		
		if($arr['efface']=='0')
			return fctErrorMSG('Cette compétence est requise car utilisée de facon statique par un module du jeu. Elle ne peut pas être effacé dynamiquement.', '?mj=Perso_GestionComp',null,false);
		
		
		
		
		
		//Retirer les modificateurs de comp
		$query = 'DELETE FROM ' . DB_PREFIX . 'competence_stat'
				. ' WHERE compid=:id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',		$_POST['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;

		
		//La retirer aux perso
		$query = 'DELETE FROM ' . DB_PREFIX . 'perso_competence'
				. ' WHERE compid=:id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',		$_POST['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;

		
		//Supprimer la caractéristique
		$query = 'DELETE FROM ' . DB_PREFIX . 'competence'
				. ' WHERE id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',		$_POST['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;

		
		//Retourner le template complété/rempli
		$tpl->set('PAGE', 'Perso_GestionComp');
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/redirect.htm',__FILE__,__LINE__);
	}
	

}

