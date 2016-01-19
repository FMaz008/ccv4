<?php
/** Affichage de la page d'instruction pour la modification d'un personnage
 *
 * @package Member
 * @subpackage Inscription
 */
class Member_ModPerso
{
	public static function generatePage(&$tpl, &$session, &$account)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if (!isset($_GET['id']))
			return fctErrorMSG('Données requises manquantes. (cheat)');
		

		
		//Établir la liste de signes disponibles.
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'caract'
				. ' WHERE type="system"'
					. ' AND catid=0'
				. ' ORDER BY nom;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__,__LINE__);
		$arrCat = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		foreach($arrCat as &$arr)
		{
			$arr['nom'] = stripslashes($arr['nom']);
			$arr['desc']= stripslashes($arr['desc']);
		}
		$tpl->set('CAT', $arrCat);
		


		//Fetcher les caractéristiques du perso
		$query = 'SELECT c.*, p.desc as perso_desc'
				. ' FROM ' . DB_PREFIX . 'caract as c'
				. ' LEFT JOIN ' . DB_PREFIX . 'perso_caract as p ON (p.caractid=c.id AND p.persoid=:persoId)'
				. ' WHERE c.type="system"'
					. ' AND c.catid>0'
				. ' ORDER BY c.nom;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arrCaract = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		$arrC = array();
		foreach($arrCaract as &$arr)
		{
			$arr['nom'] = stripslashes($arr['nom']);
			$arr['desc']= stripslashes($arr['desc']);
			
			$fn = 'caractDesc_' . $arr['id'];
			if(isset($_POST[$fn]) && !empty($_POST[$fn]))
				$arrC[$arr['id']] = $_POST[$fn];
			elseif($arr['perso_desc']!==null)
				$arrC[$arr['id']] = stripslashes($arr['perso_desc']); 
		
		}
		$tpl->set('CARACT', $arrCaract);
		$tpl->set('PERSO_CARACT', $arrC);
		
		
		
		//Établir la liste des statistiques disponibles
		$query = 'SELECT s.*, IFNULL(p.xp, 0) as xp'
				. ' FROM ' . DB_PREFIX . 'stat as s'
				. ' LEFT JOIN ' . DB_PREFIX . 'perso_stat as p ON (p.statid = s.id AND p.persoid=:persoId);';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arrStat = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		foreach($arrStat as &$arr)
		{
			if(isset($_POST['stat_' . $arr['id']]))
				$arr['xp'] = $_POST['stat_' . $arr['id']];
		}
		$tpl->set('STATS', $arrStat);
		
		
		
		
		//Établir la liste des compétences disponibles
		$query = 'SELECT c.*, IFNULL(p.xp, 0) as xp'
				. ' FROM ' . DB_PREFIX . 'competence as c'
				. ' LEFT JOIN ' . DB_PREFIX . 'perso_competence as p ON (p.compid = c.id AND p.persoid=:persoId)'
				. ' WHERE c.inscription="1";';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arrComp = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		
		foreach($arrComp as &$arr)
		{
			if(isset($_POST['comp_' . $arr['id']]))
				$arr['lvl'] = $_POST['comp_' . $arr['id']];
			else
				$arr['lvl'] = Member_Perso::convCompXpToLevel($arr['xp']);
		}
		$tpl->set('COMPS', $arrComp);






		
		
		//Trouver les informations sur le perso
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'perso'
				. ' WHERE	id=:persoId'
					. ' AND userid=:userId'
					. ' AND inscription_valide IN ("0","mod")'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$_GET['id'],		PDO::PARAM_INT);
		$prep->bindValue(':userId',		$account->getId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arrP = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

		
		if($arrP === false)
			return fctErrorMSG('Ce personnage n\'existe pas, ne vous appartiend pas, ou n\'est pas en phase de refus. (cheat)');
		
		
		//Formater correctement certaines données:
		$arrP['nom']			= stripslashes($arrP['nom']);
		$arrP['description']	= stripslashes($arrP['description']);
		$arrP['background']		= stripslashes($arrP['background']);
		$arrP['yeux']			= stripslashes($arrP['yeux']);
		$arrP['ethnie']			= stripslashes($arrP['ethnie']);
		$arrP['cheveux']		= stripslashes($arrP['cheveux']);
		$arrP['taillem']  		= substr($arrP['taille'],0,1);
		$arrP['taillecm'] 		= substr($arrP['taille'],2,2);

		
		if(isset($_POST) && count($_POST)>0)
			$tpl->set('PERSO', $_POST);
		else
			$tpl->set('PERSO', $arrP);
		
		
		
		
		
		
		
		
		//Trouver la raison du refus
		if($arrP['inscription_valide']=='mod')
		{
			$perso= new Member_Perso($arrP);
			$he = new Member_He($account, $perso);
			
			$heMsg = $he->listMessages($perso, 0, $account->getMsgPerPage());
			
			$code='';
			foreach($heMsg as $msg)
			{
				if($msg->getType() == 'inscription')
				{
					$tpl->set('MSG',$msg);
					$code .= $tpl->fetch($account->getSkinRemotePhysicalPath() . '/html/Member/he_item.htm');
				}
			}
			if($code!='')
				$tpl->set('PAGE_HE_MESSAGES',$code);
		}
		

		$tpl->set('GAME_IS_CYBERCITY', GAME_IS_CYBERCITY);
		
		
		//Retourner le template complété/rempli
		$tpl->set('REDIRECT_TO', 'ModPerso2&id=' . $_GET['id']);
		$tpl->set('CHECK_URL', 'ModPersoCheck&id=' . $_GET['id']);
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/creerPerso2.htm',__FILE__,__LINE__);
		
	}
}

