<?php
/** Gestion des perso
*
* @package Mj
*/
class Mj_Perso_Mod
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if (isset($_POST['save']))
		{
			//Fetcher toutes les informations concernant le perso
			$query = 'SELECT *'
						. ' FROM ' . DB_PREFIX . 'perso'
						. ' WHERE id=:persoId'
						. ' LIMIT 1;';
			$prepP = $db->prepare($query);
			$prepP->bindValue(':persoId',		$_GET['id'],	PDO::PARAM_INT);
			$prepP->execute($db, __FILE__, __LINE__);
			$arr = $prepP->fetch();
			
			
			$perso = new Member_Perso($arr);
			
			
			//Vérifier tout ce qui a changé
			$msg = array();
			
			if($perso->getUserId() != $_POST['userId'])
				$msg[] = 'UserId: ' . $_POST['userId'];
			
			if($perso->getNom() != $_POST['nom'])
				$msg[] = 'Nom: ' . $_POST['nom'];
			
			if($perso->getSexe() != $_POST['sexe'])
				$msg[] = 'Sexe: ' . $_POST['sexe'];
			
			if($perso->getAge() != $_POST['age'])
				$msg[] = 'Age: ' . $_POST['age'];
			
			if($perso->getTaille() != $_POST['taille'])
				$msg[] = 'Taille: ' . $_POST['taille'];
			
			if($perso->getEthnie() != $_POST['ethnie'])
				$msg[] = 'Ethnie: ' . $_POST['ethnie'];
			
			if($perso->getYeux() != $_POST['yeux'])
				$msg[] = 'Yeux: ' . $_POST['yeux'];
			
			if($perso->getCheveux() != $_POST['cheveux'])
				$msg[] = 'Cheveux: ' . $_POST['cheveux'];
			
			if($perso->getType() != $_POST['playertype'])
				$msg[] = 'Type: ' . $_POST['playertype'];
			
			if($perso->getPrMax() != $_POST['prmax'])
				$msg[] = 'Pr Max: ' . $_POST['prmax'];
			
			if($perso->getPa() != $_POST['pa'])
				$msg[] = 'Pa: ' . $_POST['pa'];
			
			if($perso->getPaMax() != $_POST['pamax'])
				$msg[] = 'Pa Max: ' . $_POST['pamax'];
			
			if($perso->getPv() != $_POST['pv'])
				$msg[] = 'Pv: ' . $_POST['pv'];
			
			if($perso->getPvMax() != $_POST['pvmax'])
				$msg[] = 'Pv Max: ' . $_POST['pvmax'];
			
			if($perso->getPn() != $_POST['pn'])
				$msg[] = 'Pn: ' . $_POST['pn'];
			
			if($perso->getCash() != $_POST['cash'])
				$msg[] = 'Argent: ' . $_POST['cash'];

			try
			{
				if($perso->getLieu()->getNomTech() != $_POST['lieu'])
					$msg[] = 'Lieu: ' . $_POST['lieu'];
			}
			catch(Exception $e)
			{
				$msg[] = 'Lieu: ' . $_POST['lieu'];
			}
			if($perso->getCurrentAction() != $_POST['current_action'])
				$msg[] = "Action Courrante: \n" . $_POST['current_action'];
			
			if($perso->getDescription() != $_POST['description'])
				$msg[] = "Description: \n" . $_POST['description'];
			
			if($perso->getBackground() != $_POST['background'])
				$msg[] = "Background: \n" . $_POST['background'];
				
			if($perso->getNoteMj() != $_POST['note_mj'])
				$msg[] = "Note MJ: \n" . $_POST['note_mj'];
			
			if($perso->isBloque() != $_POST['actif'])
				$msg[] = 'Actif: ' . $_POST['actif'];
			
			if($perso->getVisaPerm() != $_POST['visavert'])
				$msg[] = 'VisaVert: ' . $_POST['visavert'];
			
			
			if($perso->getPv() > 0 && $_POST['pv'] < 0)
				$perso->logMort($mj->getNom(), 'Modifier Perso');
				
			$menotte = ($_POST['menotte'] == false) ? NULL : $_POST['menotteId'];
			if($perso->getMenotte() != $menotte)
			{
				$msg[] = 'Menotté : ' . (($menotte) ? 'oui (' . $menotte . ').' : 'non.');
				
				//Gestion des menottes
				//Si menotté : 
					//Vérifier si l'item a bien la capacité menottage
					//Vérifier si l'item n'est pas déjà utilisé
					//Equiper l'item
					//Si passage de monotté à menotté avec autre item => déséquiper ancien item
				//Si passage de menotté à non menotté => déséquiper l'item
				if($menotte)
				{
					try
					{
						$newMenotte = Member_ItemFactory::createFromInvId($menotte);
					}
					catch(Exception $e)
					{
						return fctErrorMSG($e->getMessage());
					}
					
					$query = 'SELECT *'
							. ' FROM ' . DB_PREFIX . 'item_menu'
							. ' WHERE item_dbid = :dbid'
							. ' AND url="Menotter" LIMIT 1;';
					$prep = $db->prepare($query);
					$prep->bindValue(':dbid', $newMenotte->getDbId(), PDO::PARAM_INT);
					$prep->execute($db, __FILE__, __LINE__);
					$arrAll = $prep->fetchAll();
					$prep->closeCursor();
					$prep = NULL;
					
					if(count($arrAll) == 0)
						return fctErrorMSG('L\'item choisi pour menotter n\'a pas la capacité menotter.');
						
					if($newMenotte->isEquip())
						return fctErrorMSG('L\'item choisi pour menotter est déjà utilisé.');
						
					//Équiper l'item
					$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
							. ' SET inv_equip="1"'
							. ' WHERE inv_id=:itemId'
							. ' LIMIT 1;';
					$prep = $db->prepare($query);
					$prep->bindValue(':itemId',	$newMenotte->getInvId(), PDO::PARAM_INT);
					$prep->execute($db, __FILE__, __LINE__);
					$prep->closeCursor();
					$prep = NULL;
					
					if($perso->getMenotte())
					{
						$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
								. ' SET inv_equip="0"'
								. ' WHERE inv_id=:itemId'
								. ' LIMIT 1;';
						$prep = $db->prepare($query);
						$prep->bindValue(':itemId',	$perso->getMenotte(), PDO::PARAM_INT);
						$prep->execute($db, __FILE__, __LINE__);
						$prep->closeCursor();
						$prep = NULL;
					}
				}
				else
				{
					$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
							. ' SET inv_equip="0"'
							. ' WHERE inv_id=:itemId'
							. ' LIMIT 1;';
					$prep = $db->prepare($query);
					$prep->bindValue(':itemId',	$perso->getMenotte(), PDO::PARAM_INT);
					$prep->execute($db, __FILE__, __LINE__);
					$prep->closeCursor();
					$prep = NULL;
				}
			}
			
			//modification
			$query = 'UPDATE ' . DB_PREFIX . 'perso'
					. ' SET'
						. ' userId=:userId,'
						. ' nom=:nom,'
						. ' sexe=:sexe,'
						. ' age=:age,'
						. ' taille=:taille,'
						. ' ethnie=:ethnie,'
						. ' yeux=:yeux,'
						. ' cheveux=:cheveux,'
						. ' playertype=:playerType,'
						. ' prmax=:prMax,'
						. ' pa=:pa,'
						. ' pamax=:paMax,'
						. ' pv=:pv,'
						. ' pvmax=:pvMax,'
						. ' pn=:pn,'
						. ' cash=:cash,'
						. ' lieu=:lieu,'
						. ' current_action=:currentAction,'
						. ' description=:description,'
						. ' background=:background,'
						. ' note_mj=:noteMj,'
						. ' bloque=:bloque,'
						. ' visa_perm=:visaPerm,'
						. ' menotte = :menotte'
					. ' WHERE id=:persoId;';
			$prep = $db->prepare($query);
			$prep->bindValue(':persoId',		$_GET['id'],		PDO::PARAM_INT);
			$prep->bindValue(':userId',			$_POST['userId'],	PDO::PARAM_INT);
			$prep->bindValue(':nom',			$_POST['nom'],		PDO::PARAM_STR);
			$prep->bindValue(':sexe',			$_POST['sexe'],		PDO::PARAM_STR);
			$prep->bindValue(':age',			$_POST['age'],		PDO::PARAM_INT);
			$prep->bindValue(':taille',			$_POST['taille'],	PDO::PARAM_STR);
			$prep->bindValue(':ethnie',			$_POST['ethnie'],	PDO::PARAM_STR);
			$prep->bindValue(':yeux',			$_POST['yeux'],		PDO::PARAM_STR);
			$prep->bindValue(':cheveux',		$_POST['cheveux'],	PDO::PARAM_STR);
			$prep->bindValue(':playerType',		$_POST['playertype'],	PDO::PARAM_STR);
			$prep->bindValue(':prMax',			$_POST['prmax'],	PDO::PARAM_INT);
			$prep->bindValue(':pa',				$_POST['pa'],		PDO::PARAM_INT);
			$prep->bindValue(':paMax',			$_POST['pamax'],	PDO::PARAM_INT);
			$prep->bindValue(':pv',				$_POST['pv'],		PDO::PARAM_INT);
			$prep->bindValue(':pvMax',			$_POST['pvmax'],	PDO::PARAM_INT);
			$prep->bindValue(':pn',				$_POST['pn'],		PDO::PARAM_INT);
			$prep->bindValue(':cash',			$_POST['cash'],		PDO::PARAM_INT);
			$prep->bindValue(':lieu',			$_POST['lieu'],		PDO::PARAM_STR);
			$prep->bindValue(':currentAction',	$_POST['current_action'],	PDO::PARAM_STR);
			$prep->bindValue(':description',	$_POST['description'],	PDO::PARAM_STR);
			$prep->bindValue(':background',		$_POST['background'],	PDO::PARAM_STR);
			$prep->bindValue(':noteMj',			$_POST['note_mj'],		PDO::PARAM_STR);
			$prep->bindValue(':bloque',			$_POST['actif'],		PDO::PARAM_STR);
			$prep->bindValue(':visaPerm',		$_POST['visavert'],		PDO::PARAM_STR);
			$prep->bindValue(':persoId',		$_GET['id'],			PDO::PARAM_INT);
			$prep->bindValue(':menotte',		$menotte,				PDO::PARAM_STR);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;

			
			//Fetcher toutes les informations concernant le perso
			$prepP->bindValue(':persoId',		$_GET['id'],	PDO::PARAM_INT);
			$prepP->execute($db, __FILE__, __LINE__);
			$arr = $prepP->fetch();

			$perso = new Member_Perso($arr);


			
			//Sauvegarder les stats
			$query = 'INSERT INTO ' . DB_PREFIX . 'perso_stat'
					. ' (persoid, statid, xp)'
					. ' VALUES'
					. ' (:persoId, :statId, :xp);';
			$prepIns = $db->prepare($query);

			$query = 'UPDATE ' . DB_PREFIX . 'perso_stat'
					 . ' SET xp=:xp'
					 . ' WHERE persoid=:persoId'
						. ' AND statid=:statId'
					 . ' LIMIT 1;';
			$prepUpd = $db->prepare($query);
			
			foreach($perso->getStat() as $id)
			{
				$statCode = $perso->getStatCode($id);
				$fieldName = 'stat_' . $statCode;
				if(isset($_POST[$fieldName]) && is_numeric($_POST[$fieldName]) && $perso->getStatRawXp($id) != $_POST[$fieldName])
				{
					
					//Construire le message d'information
					$statDiff = (int)$_POST[$fieldName] - $perso->getStatRawXp($id);
					if($statDiff > 0)
						$statDiff = '+' . $statDiff;
					$msg[] = 'Stat ' . strtoupper($statCode) . ': ' . $statDiff;
					
					
					if ($perso->getStatRawXp($id)===NULL)
					{
						$prepIns->bindValue(':persoId',		$perso->getId(),	PDO::PARAM_INT);
						$prepIns->bindValue(':statId',		$id,				PDO::PARAM_INT);
						$prepIns->bindValue(':xp',			$_POST[$fieldName],	PDO::PARAM_INT);
						$prepIns->execute($db, __FILE__, __LINE__);
					}
					else
					{
						$prepUpd->bindValue(':persoId',		$perso->getId(),	PDO::PARAM_INT);
						$prepUpd->bindValue(':statId',		$id,				PDO::PARAM_INT);
						$prepUpd->bindValue(':xp',			$_POST[$fieldName],	PDO::PARAM_INT);
						$prepUpd->execute($db, __FILE__, __LINE__);
					}
				}
			}
			$prepIns->closeCursor();
			$prepIns = NULL;
			$prepUpd->closeCursor();
			$prepUpd = NULL;
			







			
			//Sauvegarder les comps
			$query = 'INSERT INTO ' . DB_PREFIX . 'perso_competence'
					 . ' (`persoid`, `compid`, `xp`)'
					 . 'VALUES'
					 . '(:persoId, :compId, :xp);';
			$prepIns = $db->prepare($query);

			$query = 'UPDATE ' . DB_PREFIX . 'perso_competence'
					 . ' SET xp=:xp'
					 . ' WHERE persoid=:persoId'
						. ' AND compid=:compId'
					 . ' LIMIT 1;';
			$prepUpd = $db->prepare($query);

			$query = 'DELETE FROM ' . DB_PREFIX . 'perso_competence'
						 . ' WHERE persoid=:persoId'
							. ' AND compid=:compId'
						 . ' LIMIT 1;';
			$prepDel = $db->prepare($query);

			foreach($perso->getComp() as $id)
			{
				$compCode = $perso->getCompCode($id);
				$fieldName = 'comp_' . $compCode;
				
				//La comp doit être définie
				if(isset($_POST[$fieldName]) && !empty($_POST[$fieldName]) && is_numeric($_POST[$fieldName]) && $perso->getCompXp($id) != $_POST[$fieldName])
				{
					//Construire le message d'information
					$compDiff = (int)$_POST[$fieldName] - $perso->getCompXp($id);
					if($compDiff > 0)
						$compDiff = '+' . $compDiff;
					$msg[] = 'Comp ' . strtoupper($compCode) . ': ' . $compDiff;
					
					
					//Créer la comp
					if($perso->getCompXp($id)===NULL)
					{
						$prepIns->bindValue(':persoId',		$perso->getId(),	PDO::PARAM_INT);
						$prepIns->bindValue(':compId',		$id,				PDO::PARAM_INT);
						$prepIns->bindValue(':xp',			$_POST[$fieldName],	PDO::PARAM_INT);
						$prepIns->execute($db, __FILE__, __LINE__);
					}
					//Modifier la comp
					else
					{
						$prepUpd->bindValue(':persoId',		$perso->getId(),	PDO::PARAM_INT);
						$prepUpd->bindValue(':compId',		$id,				PDO::PARAM_INT);
						$prepUpd->bindValue(':xp',			$_POST[$fieldName],	PDO::PARAM_INT);
						$prepUpd->execute($db, __FILE__, __LINE__);
					}
				}
				
				//La compétence doit être effacée
				if(isset($_POST[$fieldName]) && empty($_POST[$fieldName]) && $perso->getCompXp($id)!==NULL)
				{
					$prepDel->bindValue(':persoId',		$perso->getId(),	PDO::PARAM_INT);
					$prepDel->bindValue(':compId',		$id,				PDO::PARAM_INT);
					$prepDel->execute($db, __FILE__, __LINE__);
					
					//Construire le message d'information
					$msg[] = 'Comp ' . strtoupper($compCode) . ': 0';
				}
			}
			$prepIns->closeCursor();
			$prepIns = NULL;
			$prepUpd->closeCursor();
			$prepUpd = NULL;
			$prepDel->closeCursor();
			$prepDel = NULL;





			
			//Modifier / Supprimer une caract
			$query = 'SELECT p.*, c.nom'
					. ' FROM ' . DB_PREFIX . 'perso_caract as p'
					. ' LEFT JOIN ' . DB_PREFIX . 'caract as c'
						. ' ON (c.id = p.caractid)'
					. ' WHERE p.`persoid`=:persoId;';
			$prep = $db->prepare($query);
			$prep->bindValue(':persoId', $_GET['id']);
			$prep->execute($db, __FILE__, __LINE__);
			$arrAll = $prep->fetchAll();
			$prep->closeCursor();
			$prep = NULL;

			$query = 'UPDATE ' . DB_PREFIX . 'perso_caract'
					. ' SET `desc`=:description'
					. ' WHERE `id`=:id;';
			$prepUpd = $db->prepare($query);

			$query = 'DELETE FROM ' . DB_PREFIX . 'perso_caract'
					. ' WHERE `id`=:id'
					. ' LIMIT 1;';
			$prepDel = $db->prepare($query);

			foreach($arrAll as &$row)
			{
				
				//Supprimer ?
				if(isset($_POST['del_caract_' . $row['id']]))
				{
					$prepDel->bindValue(':id',	$row['id'],		PDO::PARAM_INT);
					$prepDel->execute($db, __FILE__, __LINE__);
					
					$msg[] = 'Suppression de la caract ' . stripslashes($row['nom']);
				}
				else
				{
					$newDesc = fctScriptProtect($_POST['caract_' . $row['id']]);
					
					if(stripslashes($row['desc']) != $newDesc)
					{
						//Modifier
						$prepUpd->bindValue(':description',	$newDesc,		PDO::PARAM_STR);
						$prepUpd->bindValue(':id',			$row['id'],		PDO::PARAM_INT);
						$prepUpd->execute($db, __FILE__, __LINE__);
					
						$msg[] = 'Modification de la caract ' . stripslashes($row['nom']) . ":\n" . $newDesc;
					}
				}
			}
			$prepUpd->closeCursor();
			$prepUpd = NULL;
			$prepDel->closeCursor();
			$prepDel = NULL;
			
			
			//Ajouter une caract
			if($_POST['add_caract']!=0)
			{
				$query = 'INSERT INTO ' . DB_PREFIX . 'perso_caract'
						. ' (`caractid`,`persoid`)'
						. ' VALUES'
						. ' (:caractId, :persoId);';
				$prep = $db->prepare($query);
				$prep->bindValue(':caractId',	$_POST['add_caract'],	PDO::PARAM_INT);
				$prep->bindValue(':persoId',	$_GET['id'],			PDO::PARAM_INT);
				$prep->execute($db, __FILE__, __LINE__);
				$prep->closeCursor();
				$prep = NULL;
			
				$msg[] = 'Ajout de la caract #' . $_POST['add_caract'] . ":\n";
			}
			
			


			
			//Recharger la liste des perso du compte actuel (compte du MJ)
			$query = 'SELECT id, nom
						FROM ' . DB_PREFIX . 'perso
						WHERE userId=:userId;';
			$prep = $db->prepare($query);
			$prep->bindValue(':userId',	$session->getVar('userId'),	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$arrAll = $prep->fetchAll();
			$prep->closeCursor();
			$prep = NULL;
			
			$session->setVar('persoList', $arrAll);
			


			
			
			//Afficher les messages et logger dans le HE MJ
			if(count($msg)>0)
			{
				$msg = implode(",\n", $msg);
				$tpl->set('MODIFICATIONS', nl2br($msg));
				$mj->addHe("Modification du personnage: \n" . $msg, $perso->getNom(), 'perso');
			}
			
			$prepP->closeCursor();
			$prepP = NULL;
		} // End save
		
		
		
		
		
		
		
		
		
		
		
		//Fetcher toutes les informations concernant le perso
		$query = 'SELECT p.*, a.user as user'
				. ' FROM ' . DB_PREFIX . 'perso as p'
				. ' LEFT JOIN ' . DB_PREFIX . 'account as a'
					. ' ON (a.id=p.userId)'
				. ' WHERE p.id=:persoId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

			
		if($arr === false)
			return fctErrorMSG('Ce personnage n\'existe pas.');
			
		try
		{	
			$perso = new Member_Perso($arr);
		}
		catch(Exception $e)
		{
			return fctErrorMSG($e->getMessage());
		}

		try
		{
			$tpl->set('PERSO_LIEU', $perso->getLieu()->getNomTech());
		}
		catch(Exception $e)
		{
			$tpl->set('PERSO_LIEU','LIEU CORROMPU');
		}
		
		$tpl->set('PERSO',$perso);
		$tpl->set('ACCOUNT_USER', $arr['user']);
		$tpl->set('BACKGROUND', stripslashes($arr['background']));
		$tpl->set('NOTE_MJ', stripslashes($arr['note_mj']));
		
		


		
		//Trouver les drogues consommées
		$query = 'SELECT inv_id'
				. ' FROM ' . DB_PREFIX . 'item_inv'
				. ' WHERE inv_equip="1"'
					. ' AND inv_dbid="2"'
					. ' AND inv_persoid=:persoId'
				. ' ;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$perso->getId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		$drogues = array();
		foreach($arrAll as &$arr)
			$drogues[] = '<a href="?mj=Item_Inv_Mod&id=' . (int)$arr['inv_id'] . '&rpage=Perso_Mod&rid=' . (int)$perso->getId() . '">' . (int)$arr['inv_id'] . '</a>';
		
		if(count($drogues)>0)
			$tpl->set('DROGUES', implode(', ', $drogues));
		
		
		
		
		//Trouver les caractéristiques du perso
		$query = 'SELECT p.*, c.nom, t.nom as cat'
				. ' FROM ' . DB_PREFIX . 'perso_caract as p'
				. ' LEFT JOIN ' . DB_PREFIX . 'caract as c ON (c.id = p.caractid)'
				. ' LEFT JOIN ' . DB_PREFIX . 'caract as t ON (t.id = c.catid)'
				. ' WHERE p.persoid=:persoId;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$perso->getId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		
		foreach($arrAll as &$arr)
		{
			$arr['cat'] = stripslashes($arr['cat']);
			$arr['nom'] = stripslashes($arr['nom']);
			$arr['desc'] = stripslashes($arr['desc']);
		}
		
		$tpl->set('CARACT', $arrAll);
		




		//Trouver toutes les caractéristiques
		$query = 'SELECT c.*, t.nom as cat'
				. ' FROM ' . DB_PREFIX . 'caract as c'
				. ' LEFT JOIN ' . DB_PREFIX . 'caract as t ON (t.id = c.catid)'
				. ' WHERE c.catid!=0'
				. ' ORDER BY t.nom, c.nom;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$perso->getId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		foreach($arrAll as &$arr)
		{
			$arr['cat'] = stripslashes($arr['cat']);
			$arr['nom'] = stripslashes($arr['nom']);
		}
		
		$tpl->set('ALLCARACT', $arrAll);



		//trouver les modificateurs des compétences:
		$query = 'SELECT c.*, p.xp, s.statid, s.stat_multi'
				. ' FROM ' . DB_PREFIX . 'competence as c'
				. ' LEFT JOIN ' . DB_PREFIX . 'perso_competence as p ON (p.compid = c.id AND p.persoid=:persoId)'
				. ' LEFT JOIN ' . DB_PREFIX . 'competence_stat as s ON (s.compid = c.id)'
				. ' ORDER BY c.id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$perso->getId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		$arrComp = array();
		foreach($arrAll as &$arr)
			if($arr['statid']===NULL)
				$arrComp[$arr['id']] = NULL; //Aucun modificateur pour cette comp
			else
				$arrComp[$arr['id']][$arr['statid']] = $arr['stat_multi'];
		
		foreach($arrComp as &$arr)
		{
			if($arr === NULL)
			{
				$arr['txt'] = '';
			}
			else
			{
				$txtBuilder = array();
				foreach($arr as $statId => $multi)
					$txtBuilder[] = strtoupper($perso->getStatCode($statId)) . 'x' . $multi;
				
				$arr['txt'] = implode (' + ', $txtBuilder);
			}
		}
		$tpl->set('COMP_MOD_TXT', $arrComp);
		
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Perso/Mod.htm'); 
	}
}




