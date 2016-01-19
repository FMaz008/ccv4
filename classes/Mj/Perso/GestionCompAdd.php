<?php
/** Gestion des compétences du jeu
*
* @package Mj
*/

class Mj_Perso_GestionCompAdd
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		



		if (isset($_POST['save']))
		{
			
			preg_match('/^[A-Za-z]{4}$/', $_POST['abbr'], $matches);
			if (count($matches)==0)
			{
				unset($_POST['save']);
				return fctErrorMSG('L\'abbréviation doit faire 4 caractères alpha.', '?mj=Perso_GestionCompAdd',$_POST,false);
			}
			$query = 'SELECT nom'
					. ' FROM ' . DB_PREFIX . 'competence'
					. ' WHERE abbr=:abbr'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':abbr',	strtolower($_POST['abbr']),		PDO::PARAM_STR);
			$prep->execute($db, __FILE__, __LINE__);
			$arr = $prep->fetch();
			$prep->closeCursor();
			$prep = NULL;

			if($arr === false)
			{
				unset($_POST['save']);
				return fctErrorMSG('Cet abbréviation existe déjà pour: ' . $arr['nom'] . '.', '?mj=Perso_GestionCompAdd',$_POST,false);
			}
			
			$query = 'INSERT INTO ' . DB_PREFIX . 'competence'
					. ' (`nom`, `abbr`, `description`, `efface`, `inscription`)'
					. ' VALUES'
					. ' (:nom, :abbr, :description, "1", :inscription);';
			$prep = $db->prepare($query);
			$prep->bindValue(':nom',			$_POST['nom'],				PDO::PARAM_STR);
			$prep->bindValue(':abbr',			strtolower($_POST['abbr']),	PDO::PARAM_STR);
			$prep->bindValue(':description',	$_POST['description'],		PDO::PARAM_STR);
			$prep->bindValue(':inscription',	$_POST['inscription'],		PDO::PARAM_STR);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			
			
			//Retourner le template complété/rempli
			$tpl->set('PAGE', 'Perso_GestionComp');
			return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/redirect.htm',__FILE__,__LINE__);

		}
		
		if(isset($_POST))
			fctStripMagicQuote($_POST);
		
		$comp = array();
		$comp['nom'] = isset($_POST['nom']) ? $_POST['nom'] : '';
		$comp['abbr'] = isset($_POST['abbr']) ? $_POST['abbr'] : '';
		$comp['description'] = isset($_POST['description']) ? $_POST['description'] : '';
		$comp['inscription'] = isset($_POST['inscription']) ? $_POST['inscription'] : '0';
		
		$tpl->set('COMP', $comp);
		$tpl->set('ADDMOD', 'Add');
		$tpl->set('ADDMOD_TXT', 'Ajouter');
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Perso/GestionCompAddMod.htm',__FILE__,__LINE__);


	}
}

