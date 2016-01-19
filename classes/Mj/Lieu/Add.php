<?php
/** Gestion de l'interface des déplacement
*
* @package Mj
*/
class Mj_Lieu_Add
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(isset($_POST['save']))
		{
			$id = self::save();
			die("<script>location.href='?mj=Lieu_Mod&id=$id';</script>");
		}
		
		
		$tpl->set('ACTIONTYPETXT',"Ajouter");
		$tpl->set('SUBMITNAME','Add');
		$tpl->set('SHOWID',false);
		
		//Déprotéger les textes stockés afin qu'il soit lisibles correctement
		$arr['nom_technique'] = '';
		$arr['nom_affiche'] = '';
		$arr['description'] = '';
		$arr['dimension'] = 'M';
		$arr['boutique_cash'] ='';
		$arr['boutique_compte'] ='';
		$arr['boutique_vol'] ='0';
		$arr['qteMateriel'] = '';
		$arr['coeff_soin'] = '';
		$arr['image'] = '';
		$arr['notemj'] = '';
		
		if(isset($_GET['id']) || isset($_POST['id']))
		{
			$id = isset($_POST['id']) ? mysql_real_escape_string($_POST['id']) : mysql_real_escape_string($_GET['id']);
			
			//Fetcher toutes les informations concernant le perso
			$query = 'SELECT nom_technique'
					. ' FROM ' . DB_PREFIX . 'lieu'
					. ' WHERE id=:lieuId'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':lieuId',		$id,	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$arrTmp = $prep->fetch();
			$prep->closeCursor();
			$prep = NULL;
			$arr['nom_technique'] = $arrTmp['nom_technique'] . '.';
		}
		
		$tpl->set("LIEU",$arr);
		
		
		//Faire la liste de tout les lieux
		$arr=array();
		$query = 'SELECT nom_technique'
					. ' FROM ' . DB_PREFIX . 'lieu'
					. ' ORDER BY nom_technique ASC;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		$tpl->set("PAGE_LIEUX",$arrAll);
		
		//lister le dossier d'image
		$dir2 = dir($account->getSkinRemotePhysicalPath() . "../_common/lieux/");
		
		$arrurl = array();
		$arr=array();
		while ($url = $dir2->read())
			$arrurl[]=$url;
		
		natcasesort($arrurl);
		$arrurl = array_values($arrurl);
		for ($i=0;$i<count($arrurl);$i++)
			if ($arrurl[$i]!='' && substr($arrurl[$i],0,1)!='.')
				$arr[$i] = $arrurl[$i];
		
		$tpl->set('IMGS',$arr);
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/Addmod.htm',__FILE__,__LINE__);
	}
	
	
	
	
	private static function save()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if (empty($_POST['qteMateriel']))
			$_POST['qteMateriel'] = 0;
			
		if (empty($_POST['coeff_soin']))
			$_POST['coeff_soin'] = 0;
	
		if (empty($_POST['casiers']))
			$_POST['casiers'] = 0;
		
		$_POST['boutique_cash'] = str_replace(',','.',$_POST['boutique_cash']);
		if (!is_numeric($_POST['boutique_cash']))
			$_POST['boutique_cash'] = 0;
		
		$query = 'INSERT INTO ' . DB_PREFIX . 'lieu'
				. ' (nom_technique,nom_affiche,dimension,description,image, boutique_cash, boutique_compte, boutique_vol, coeff_soin, qteMateriel, notemj)'
				. ' VALUES '
				. ' (:lieuTech, :nom, :dimension, :description, :image, :boutiqueCash, :boutiqueCompte, :boutiqueVol, :coeffSoin, :qteMateriel, :noteMj);';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuTech',	$_POST['pre'] . $_POST['nom_technique'],	PDO::PARAM_STR);
		$prep->bindValue(':nom',		$_POST['nom_affiche'],						PDO::PARAM_STR);
		$prep->bindValue(':dimension',	$_POST['dimension'],						PDO::PARAM_STR);
		$prep->bindValue(':description',$_POST['description'],						PDO::PARAM_STR);
		$prep->bindValue(':image',		$_POST['image'],							PDO::PARAM_STR);
		
		if(isset($_POST['boutique_cash']))
			$prep->bindValue(':boutiqueCash',	$_POST['boutique_cash'],			PDO::PARAM_INT);
		else
			$prep->bindValue(':boutiqueCash',	NULL,								PDO::PARAM_NULL);
		
		if(isset($_POST['boutique_compte']))
			$prep->bindValue(':boutiqueCompte',	$_POST['boutique_compte'],			PDO::PARAM_STR);
		else
			$prep->bindValue(':boutiqueCompte',	NULL,								PDO::PARAM_NULL);
		
		$prep->bindValue(':boutiqueVol',		$_POST['boutique_vol'],				PDO::PARAM_INT);
		$prep->bindValue(':coeffSoin',			$_POST['coeff_soin'],				PDO::PARAM_INT);
		$prep->bindValue(':qteMateriel',		$_POST['qteMateriel'],				PDO::PARAM_INT);
		$prep->bindValue(':noteMj',			$_POST['notemj'],					PDO::PARAM_STR);
		
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		$lieuId = $db->lastInsertId();
		
		//Ajouter le gérant s'il y en a un
		if(isset($_POST['addGerant']) && !empty($_POST['addGerant']))
		{
			$query = 'INSERT INTO ' . DB_PREFIX . 'boutiques_gerants'
				. ' (`persoid`, `boutiqueid`)'
				. ' VALUES'
				. ' (:idPerso, :idLieu);';
			$prep = $db->prepare($query);
			$prep->bindValue(':idLieu',	$lieuId, PDO::PARAM_STR);
			$prep->bindValue(':idPerso', $_POST['addGerant'], PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
		}
		
		return $lieuId;
	}
}

