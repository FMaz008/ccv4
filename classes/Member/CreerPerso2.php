<?php
/** Affichage de la page d'instruction pour la création d'un personnage.
 *
 * @package Member
 * @subpackage Inscription
 */
class Member_CreerPerso2
{
	public static function generatePage(&$tpl, &$session, &$account)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Déterminer la quantité de perso que ce compte à l'autorisation de créer
		$query = 'SELECT auth_creation_perso'
				. ' FROM ' . DB_PREFIX . 'account'
				. ' WHERE id=:id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',	$account->getId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if ($arr['auth_creation_perso']==0)
			return fctErrorMSG('Vous n\'avez pas l\'autorisation de créer un personnage. (cheat)');
		
		
		//Établir la liste des statistiques disponibles
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'stat;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__,__LINE__);
		$arrS = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		$arrStat = array();
		foreach($arrS as &$arr)
		{
			$arr['xp'] = 0;
			$arrStat[] = $arr;
		}
		$tpl->set('STATS', $arrStat);
		

		
		//Établir la liste des compétences disponibles
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'competence'
				. ' WHERE inscription="1";';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__,__LINE__);
		$arrC = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		$arrComp = array();
		foreach($arrC as &$arr)
		{
			$arr['lvl'] = 0;
			$arrComp[] = $arr;
		}
		$tpl->set('COMPS', $arrComp);
		
		
		
		//Établir la liste de caractéristiques disponibles.
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'caract'
				. ' WHERE type="system"'
					. ' AND catid=0'
				. ' ORDER BY nom;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__,__LINE__);
		$arrC = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		
		$cat = array();
		foreach($arrC as &$arr)
		{
			$arr['nom'] = stripslashes($arr['nom']);
			$arr['desc']= stripslashes($arr['desc']);
			$cat[] = $arr;
		}
		
		
		//Lister les caracts
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'caract'
				. ' WHERE type="system"'
					. ' AND catid>0'
				. ' ORDER BY nom;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__,__LINE__);
		$arrC = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		$signes = array();
		foreach($arrC as &$arr)
		{
			$arr['nom'] = stripslashes($arr['nom']);
			$arr['desc'] = stripslashes($arr['desc']);
			$caract[] = $arr;
		}
		$tpl->set('CAT', $cat);
		$tpl->set('CARACT', $caract);
		
		
		
		//Lister les caracts incompatibles
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'caract_incompatible;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__,__LINE__);
		$arrC = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		if(count($arrC) != 0 )
			$tpl->set('CARACT_INCOMPATIBLE', $arrC);
		
		
		
		//Resumer une création (mécanisme de recouvrement de donnée d'urgence, en cas d'erreur de la validation JS)
		if(isset($_POST['nom']))
		{
			//Avertir que le JS n'à pas su assurer la validation adéquatement
			fctBugReport('La validation JS de la création d\'un perso à échouée.', $_POST, __FILE__, __LINE__, __FUNCTION__, __CLASS__, __METHOD__, true, false, false);
			
			//Si les magicQuote sont activer, les retirer.
			fctStripMagicQuote($_POST);
			$tpl->set('PERSO', $_POST);
			
			
			$arrC=  array();
			foreach($caract as $arr)
			{ 
				if(isset($_POST['caractDesc_' . $arr['id']]) && !empty($_POST['caractDesc_' . $arr['id']]))
				{
					$arrC[$arr['id']] = $_POST['caractDesc_' . $arr['id']];
				}
			}
		
			$tpl->set('PERSO_CARACT', $arrC);
		}
		
		
		
		//Retourner le template complété/rempli
		$tpl->set('GAME_IS_CYBERCITY', GAME_IS_CYBERCITY);
		$tpl->set('REDIRECT_TO', 'CreerPerso3');
		$tpl->set('CHECK_URL', 'CreerPerso2Check');
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/creerPerso2.htm',__FILE__,__LINE__);
		
	}
}

