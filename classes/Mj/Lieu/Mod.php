<?php
/**
 * Gestion de l'interface de modification d'un lieu.
 *
 * @package Mj
 */
class Mj_Lieu_Mod
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if (!isset($_POST['id']) && !isset($_GET['id']))
			return fctErrorMSG('Vous devez sélectionner un lieu.');
		
		
		if (isset($_GET['id']))
			$_POST['id'] = $_GET['id'];
		
		
		
		
		
		//Fetcher toutes les informations concernant le lieu
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'lieu'
				. ' WHERE id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',	$_POST['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

		
		//Déprotéger les textes stockés afin qu'il soit lisibles correctement
		$nomtech = $arr['nom_technique']; //Utile pour trouver les liens du lieu ci-bas

		//Récupérer les informations sur les gérants du lieu
		$query = 'SELECT p.nom as nom, p.id as id FROM ' . DB_PREFIX . 'boutiques_gerants as b'
				. ' LEFT JOIN ' . DB_PREFIX . 'perso as p ON (p.id = b.persoid)'
				. ' WHERE b.boutiqueid = :id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',	$arr['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrGerants = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		if (isset($_POST['liens'])) //Il s'agit d'une modification dans les LIAISONS
		{
			self::saveLien($arr);
		}
		elseif(isset($_POST['save']) && isset($_POST['id'])) // Il s'agit d'une modification du LIEU
		{
			self::saveLieu($arr, $arrGerants);
			
			
			//Re-Fetcher toutes les informations concernant le lieu
			$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'lieu'
				. ' WHERE id=:id'
				. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':id',	$_POST['id'],	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$arr = $prep->fetch();
			$prep->closeCursor();
			$prep = NULL;
			
			$query = 'SELECT p.nom as nom, p.id as id FROM ' . DB_PREFIX . 'boutiques_gerants as b'
				. ' LEFT JOIN ' . DB_PREFIX . 'perso as p ON (p.id = b.persoid)'
				. ' WHERE b.boutiqueid = :id;';
			$prep = $db->prepare($query);
			$prep->bindValue(':id',	$arr['id'],	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$arrGerants = $prep->fetchAll();
			$prep->closeCursor();
			$prep = NULL;
		}
		
		$nomtech = $arr['nom_technique']; //Utile pour trouver les liens du lieu ci-bas
		$arr['nom_technique'] = stripslashes($arr['nom_technique']);
		$arr['nom_affiche'] = stripslashes($arr['nom_affiche']);
		$arr['description'] = stripslashes($arr['description']);
		$arr['notemj'] = stripslashes($arr['notemj']);
		
		$tpl->set('LIEUID',$arr['id']); //Pour le formulaire de modification des liaisons
		$tpl->set('LIEU',$arr);
		$tpl->set('GERANTS', $arrGerants);
		
		// Afficher les informations sur le lieu
		if(isset($_GET['id'])){ $_POST['id']=$_GET['id']; }
		
		
		$tpl->set('ACTIONTYPETXT','Modifier');
		$tpl->set('SUBMITNAME','Mod');
		$tpl->set('SHOWID',true);
		
		
		
		
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
		$counter=0;
		$arrurl = array();
		
		$arr=array();
		while ($url = $dir2->read())
			$arrurl[$counter++]=$url;
			
		natcasesort($arrurl);
		$arrurl = array_values($arrurl);
		for ($i=0;$i<count($arrurl);$i++)
			if ($arrurl[$i]!='' && substr($arrurl[$i],0,1)!='.')
				$arr[$i] = $arrurl[$i];
		
		$tpl->set('IMGS',$arr);
		
		
		
		
		//Générer la liste des actions associables à un lieu
		$query = 'SELECT id,url,caption
					FROM ' . DB_PREFIX . 'lieu_menu
					WHERE lieutech=:nomTech;';
		$prep = $db->prepare($query);
		$prep->bindValue(':nomTech',	$nomtech,	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		
		if (count($arrAll)>0)
		{
			
			foreach($arrAll as &$arr)
			{
				$arr['caption'] = stripslashes($arr['caption']);
				
				//Trouver le config_url si disponible
				$ret = Mj_Lieu::getLinkedAction($arr['url']);
				if(!empty($ret) && !empty($ret['config_url']))
					$arr['config_url'] = $ret['config_url'];
				
			}
			$tpl->set('ACTIONS',$arrAll);
		}
		
		
		
		
		//Générer chaque lignes des liens individuellement
		$i=-1;
		$LIENS1 = array();
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'lieu_lien'
				. ' WHERE `from`=:nomTech;';
		$prep = $db->prepare($query);
		$prep->bindValue(':nomTech',	$nomtech,	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		foreach($arrAll as &$arr)
		{
			$arr['prefix'] = '';
			$tpl->set('lien',$arr);
			$LIENS1[$i++] = $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/AddmodLien.htm');
		}
		$tpl->set('LIENS1',$LIENS1);
		
		$i=-1;
		$LIENS2 = array();
		$query = 'SELECT *' 
				. ' FROM ' . DB_PREFIX . 'lieu_lien'
				. ' WHERE `to`=:nomTech;';
		$prep = $db->prepare($query);
		$prep->bindValue(':nomTech',	$nomtech,	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		foreach($arrAll as &$arr)
		{
			$arr['prefix'] = '';
			$tpl->set('lien',$arr);
			$LIENS2[$i++] = $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/AddmodLien.htm');
			
		}
		$tpl->set('LIENS2',$LIENS2);

		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/Addmod.htm');
	}
	
	
	
	
	
	private static function saveLien(&$arr)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if (isset($_POST['lien_add'])) //AJOUTS de LIAISONS
		{
			echo "A";
			$query = 'INSERT INTO ' . DB_PREFIX . 'lieu_lien'
					. ' (`from`, `to`, `icon`, `pa`, `cout`, `protection`, `pass`, `bloque`)'
					. ' VALUES'
					. ' (:from, :to, :icon, :pa, :cout, :protection, :pass, :bloque);';
			$prep = $db->prepare($query);
			
			for ($i=1;$i<=$_POST['total_add'];$i++)
			{
				echo "<br />" . $i . "/" . $_POST['total_add']; 
				if (!empty($_POST[$i.'_al_from']) && !empty($_POST[$i.'_al_to']))
				{
					echo " ValOK ";
					
					$prep->bindValue(':from',	$_POST[$i.'_al_from'],	PDO::PARAM_STR);
					$prep->bindValue(':to',		$_POST[$i.'_al_to'],	PDO::PARAM_STR);
					$prep->bindValue(':icon',	$_POST[$i.'_al_icon'],	PDO::PARAM_STR);
					$prep->bindValue(':pa',		$_POST[$i.'_al_pa'],	PDO::PARAM_STR);
					$prep->bindValue(':cout',	$_POST[$i.'_al_cout'],	PDO::PARAM_STR);
					$prep->bindValue(':protection',	$_POST[$i.'_al_protection'],	PDO::PARAM_STR);
					$prep->bindValue(':pass',	$_POST[$i.'_al_pass'],	PDO::PARAM_STR);
					$prep->bindValue(':bloque',	$_POST[$i.'_al_bloque'],	PDO::PARAM_STR);
					$prep->execute($db, __FILE__, __LINE__);
					
					if (isset($_POST[$i . '_al_sym'])) // Créer le lien inverse si coché
					{
						echo " - SYM ";
						
						$prep->bindValue(':to',		$_POST[$i.'_al_from'],	PDO::PARAM_STR); //Inversé
						$prep->bindValue(':from',	$_POST[$i.'_al_to'],	PDO::PARAM_STR); //Inversé
						$prep->bindValue(':icon',	$_POST[$i.'_al_icon'],	PDO::PARAM_STR);
						$prep->bindValue(':pa',		$_POST[$i.'_al_pa'],	PDO::PARAM_INT);
						$prep->bindValue(':cout',	$_POST[$i.'_al_cout'],	PDO::PARAM_INT);
						$prep->bindValue(':protection',	$_POST[$i.'_al_protection'],	PDO::PARAM_STR);
						$prep->bindValue(':pass',	$_POST[$i.'_al_pass'],	PDO::PARAM_STR);
						$prep->bindValue(':bloque',	$_POST[$i.'_al_bloque'],	PDO::PARAM_STR);
						$prep->execute($db, __FILE__, __LINE__);
						
					}
				}
			}
			$prep->closeCursor();
			$prep = NULL;
			
		}
		elseif(isset($_POST['lien_save'])) // MODIFICATION de LIAISONS
		{
			echo "M";
			//Étape 1: Faire la liste de toutes les liaisons
			$query = 'SELECT id'
					. ' FROM ' . DB_PREFIX . 'lieu_lien'
					. ' WHERE `from`=:from'
						. ' OR `to`=:to;';
			$prep = $db->prepare($query);
			$prep->bindValue(':from',	$arr['nom_technique'],	PDO::PARAM_STR);
			$prep->bindValue(':to',		$arr['nom_technique'],	PDO::PARAM_STR);
			$prep->execute($db, __FILE__, __LINE__);
			$arrAll = $prep->fetchAll();
			$prep->closeCursor();
			$prep = NULL;

			
			$query = 'UPDATE ' . DB_PREFIX . 'lieu_lien'
					. ' SET `from`=:from,'
						. ' `to`=:to,'
						. ' `icon`=:icon,'
						. ' `pa`=:pa,'
						. ' `cout`=:cout,'
						. ' `protection`=:protection,'
						. ' `pass`=:pass,'
						. ' `bloque`=:bloque'
					. ' WHERE `id`=:id;';
			$prep = $db->prepare($query);
			
			foreach($arrAll as &$arr)
			{
				//Etape2: Mettre à jours le lien
				$prep->bindValue(':from',		$_POST[$arr['id'] . '_from'],	PDO::PARAM_STR);
				$prep->bindValue(':to',			$_POST[$arr['id'] . '_to'],		PDO::PARAM_STR);
				$prep->bindValue(':icon',		$_POST[$arr['id'] . '_icon'],	PDO::PARAM_STR);
				$prep->bindValue(':pa',			$_POST[$arr['id'] . '_pa'],		PDO::PARAM_INT);
				$prep->bindValue(':cout',		$_POST[$arr['id'] . '_cout'],	PDO::PARAM_INT);
				$prep->bindValue(':protection',	$_POST[$arr['id'] . '_protection'],	PDO::PARAM_STR);
				$prep->bindValue(':pass',		$_POST[$arr['id'] . '_pass'],	PDO::PARAM_STR);
				$prep->bindValue(':bloque',		$_POST[$arr['id'] . '_bloque'],	PDO::PARAM_STR);
				$prep->bindValue(':id',			$arr['id'],						PDO::PARAM_INT);
				$prep->execute($db, __FILE__, __LINE__);
			}
			$prep->closeCursor();
			$prep = NULL;
		}
		elseif(isset($_POST['lien_del'])) // SUPRESSION de LIAISONS
		{
			echo "D";
			//Étape 1: Faire la liste de toutes les liaisons
			$query = 'SELECT id'
					. ' FROM ' . DB_PREFIX . 'lieu_lien'
					. ' WHERE `from`=:from'
						. ' OR `to`=:to;';
			$prep = $db->prepare($query);
			$prep->bindValue(':from',	$arr['nom_technique'],	PDO::PARAM_STR);
			$prep->bindValue(':to',		$arr['nom_technique'],	PDO::PARAM_STR);
			$prep->execute($db, __FILE__, __LINE__);
			$arrAll = $prep->fetchAll();
			$prep->closeCursor();
			$prep = NULL;


			$query = 'DELETE FROM ' . DB_PREFIX . 'lieu_lien'
					. ' WHERE id=:id'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			foreach($arrAll as &$arr)
			{
				// Etape 2: Si coché, supprimer !
				if (isset($_POST[$arr['id'] . '_del']))
				{
					$prep->bindValue(':id',	$arr['id'],	PDO::PARAM_INT);
					$prep->execute($db, __FILE__, __LINE__);
				}
			}
			$prep->closeCursor();
			$prep = NULL;
			
		}
	}
	
	
	
	private static function saveLieu(&$arr, &$arrGerants)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if (empty($_POST['id'])){ return fctErrorMSG('ID invalide!!!'); }
		
		if(isset($_POST['boutique_cash']))
			$_POST['boutique_cash'] = str_replace(',','.',$_POST['boutique_cash']);
			if (!is_numeric($_POST['boutique_cash']))
				$_POST['boutique_cash'] = NULL;
		
		if (empty($_POST['qteMateriel']))
			$_POST['qteMateriel'] = 0;
		
		if (empty($_POST['coeff_soin']))
			$_POST['coeff_soin'] = 0;
		
		$query = 'UPDATE ' . DB_PREFIX . 'lieu'
				. ' SET'
					. ' `nom_technique`=:nomTech,'
					. ' `nom_affiche`=:nomAff,'
					. ' `dimension`=:dimension,'
					. ' `description`=:description,'
					. ' `image`=:image,'
					. ' `boutique_cash`=:boutiqueCash,'
					. ' `boutique_compte`=:boutiqueCompte,'
					. ' `boutique_vol`=:boutiqueVol,'
					. ' `coeff_soin`=:coeffSoin,'
					. ' `qteMateriel`=:qteMateriel,'
					. ' `notemj`=:noteMj'
				. ' WHERE id=:id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':nomTech',	$_POST['nom_technique'],PDO::PARAM_STR);
		$prep->bindValue(':nomAff',		$_POST['nom_affiche'],	PDO::PARAM_STR);
		$prep->bindValue(':dimension',	$_POST['dimension'],	PDO::PARAM_STR);
		$prep->bindValue(':description',$_POST['description'],	PDO::PARAM_STR);
		$prep->bindValue(':image',		$_POST['image'],		PDO::PARAM_STR);
		$prep->bindValue(':boutiqueCompte',	$_POST['boutique_compte'],	PDO::PARAM_STR);
		
		if(!isset($_POST['boutique_cash']))
			$prep->bindValue(':boutiqueCash',	NULL,	PDO::PARAM_NULL);
		else
			$prep->bindValue(':boutiqueCash',	$_POST['boutique_cash'],	PDO::PARAM_STR);

		$prep->bindValue(':boutiqueVol',	$_POST['boutique_vol'],	PDO::PARAM_INT);
		$prep->bindValue(':coeffSoin',		$_POST['coeff_soin'],	PDO::PARAM_STR);
		$prep->bindValue(':qteMateriel',	$_POST['qteMateriel'],	PDO::PARAM_STR);
		$prep->bindValue(':noteMj',			$_POST['notemj'],		PDO::PARAM_STR);
		$prep->bindValue(':id',				$_POST['id'],			PDO::PARAM_INT);
		
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		//Mise à jour de la table cc_boutiques_gerants
		//Vérifier s'il y a des gérants à supprimer
		$query = 'DELETE FROM ' . DB_PREFIX . 'boutiques_gerants'
				. ' WHERE `boutiqueid` = :idLieu'
				. ' AND `persoid` = :idPerso'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':idLieu',	$arr['id'],PDO::PARAM_STR);
		foreach($arrGerants as $gerant)
		{
			if(isset($_POST['delGerant_' . $gerant['id']]))
			{
				$prep->bindValue(':idPerso', $gerant['id'], PDO::PARAM_INT);
				$prep->execute($db, __FILE__, __LINE__);
			}
		}
		$prep->closeCursor();
		$prep = NULL;
		
		//Vérifier s'il y a un gérant à ajouter
		if(isset($_POST['addGerant']) && !empty($_POST['addGerant']))
		{
			$query = 'INSERT INTO ' . DB_PREFIX . 'boutiques_gerants'
				. ' (`persoid`, `boutiqueid`)'
				. ' VALUES'
				. ' (:idPerso, :idLieu);';
			$prep = $db->prepare($query);
			$prep->bindValue(':idLieu',	$arr['id'],PDO::PARAM_STR);
			$prep->bindValue(':idPerso', $_POST['addGerant'], PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
		}
		
		//Si le lieutechnique à changer, faire les transferts
		if (stripslashes($_POST['nom_technique']) != $arr['nom_technique'])
		{
			//Transférer les actions disponibles pour ce lieu
			$query = 'UPDATE ' . DB_PREFIX . 'lieu_menu'
					. ' SET lieutech=:newNomTech'
					. ' WHERE lieutech=:oldNomTech;';
			$prep = $db->prepare($query);
			$prep->bindValue(':newNomTech',		$_POST['nom_technique'],	PDO::PARAM_STR);
			$prep->bindValue(':oldNomTech',		$arr['nom_technique'],		PDO::PARAM_STR);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;

			
			//Téléporter les perso vers le nouveau nom de lieu
			$query = 'UPDATE ' . DB_PREFIX . 'perso'
					. ' SET lieu=:newNomTech'
					. ' WHERE lieu=:oldNomTech;';
			$prep = $db->prepare($query);
			$prep->bindValue(':newNomTech',		$_POST['nom_technique'],	PDO::PARAM_STR);
			$prep->bindValue(':oldNomTech',		$arr['nom_technique'],		PDO::PARAM_STR);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
		}
		
		//Mise à jour des actions actuelles
		$query = 'SELECT id'
				. ' FROM ' . DB_PREFIX . 'lieu_menu'
				. ' WHERE lieutech=:nomTech;';
		$prep = $db->prepare($query);
		$prep->bindValue(':nomTech',		$_POST['nom_technique'],	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		$query = 'DELETE'
				. ' FROM ' . DB_PREFIX . 'lieu_menu'
				. ' WHERE id=:id'
				. ' LIMIT 1;';
		$prepDel = $db->prepare($query);

		$query = 'UPDATE ' . DB_PREFIX . 'lieu_menu'
				. ' SET caption=:caption'
				. ' WHERE id=:id;';
		$prepUpd = $db->prepare($query);
		foreach($arrAll as &$arr)
		{
			if (isset($_POST['delAction_' . $arr['id']]))
			{
				$prepDel->bindValue(':id',	$arr['id'],	PDO::PARAM_INT);
				$prepDel->execute($db, __FILE__, __LINE__);
			}
			else
			{
				$prepUpd->bindValue(':id',		$arr['id'],	PDO::PARAM_INT);
				$prepUpd->bindValue(':caption',	$_POST[$arr['id'] . '_actioncaption'],	PDO::PARAM_STR);
				$prepUpd->execute($db, __FILE__, __LINE__);
			}
		}
		$prepDel->closeCursor();
		$prepDel = NULL;
		$prepUpd->closeCursor();
		$prepUpd = NULL;


		$query = 'INSERT INTO ' . DB_PREFIX . 'lieu_menu'
				. ' (`lieutech`,`caption`,`url`)'
				. ' VALUES'
				. ' (:nomTech, :caption, :url);';
		$prep = $db->prepare($query);
		
		//Insertion de nouvelles actions associer au lieu
		if ($_POST['total_action_add']>0)
		{
			for($i=1;$i<=$_POST['total_action_add'];$i++)
			{
				if (!empty($_POST[$i . '_actionpage_add']))
				{
					$prep->bindValue(':nomTech',	$_POST['nom_technique'],			PDO::PARAM_STR);
					$prep->bindValue(':caption',	$_POST[$i . '_actioncaption_add'],	PDO::PARAM_STR);
					$prep->bindValue(':url',		$_POST[$i . '_actionpage_add'],		PDO::PARAM_STR);
					$prep->execute($db, __FILE__, __LINE__);
				}
			}
		}
		$prep->closeCursor();
		$prep = NULL;
		
	}
	
}




