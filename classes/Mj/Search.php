<?php
/** 
* Effectuer des recherches pour le panel MJ
*
* @package Mj
* @subpackage Ajax
* @todo: Uniformiser ce fichier avec le format: selectLieu listLieu. À faire lorsqu'on implantera les id partout au lieu des noms techniques.
*/

class Mj_Search
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if (!isset($_POST['searchWhat']))
			die("Erreur01");
		
		switch ($_POST['searchWhat'])
		{
			
			//Rercherche selon un nom du lieu (Pour l'envoi d'un message à plusieurs lieux)
			case 'Lieu_Sendmsg':
				$query = 'SELECT l.id, l.nom_technique, l.nom_affiche, count(p.id) as nbr_perso'
						. ' FROM ' . DB_PREFIX . 'lieu as l'
						. ' LEFT JOIN ' . DB_PREFIX . 'perso as p'
							. ' ON (p.lieu = l.nom_technique)'
						. ' WHERE	l.id=:id'
							. ' OR l.nom_technique LIKE :nomTech'
						. ' GROUP BY l.id'
						. ' ORDER BY l.nom_technique ASC;';
				$prep = $db->prepare($query);
				$prep->bindValue(':id',			$_POST['search'],				PDO::PARAM_INT);
				$prep->bindValue(':nomTech',	'%' . $_POST['search'] . '%',	PDO::PARAM_STR);
				$prep->execute($db, __FILE__, __LINE__);
				$arrAll = $prep->fetchAll();
				$prep->closeCursor();
				$prep = NULL;
				
				if(count($arrAll) == 0)
				{
					?>
					Aucun résultat<br />
					<?
				}
				else
				{
					foreach($arrAll as &$arr)
					{
						?>
						<a href="#" onclick="lieuSelect('<?=$arr["id"];?>', '<?=$arr["nom_technique"];?>', '<?=addslashes($arr["nom_affiche"]);?>')"><?=$arr["nom_technique"];?></a> (<?=$arr['nbr_perso'];?>)<br />
						<?
					}
				}
				break;
				
			//Rercherche selon un nom du lieu (Avec utilisation du nom technique du lieu)
			case 'p_modifier_lieu':
				$query = 'SELECT id, nom_technique, nom_affiche'
						. ' FROM ' . DB_PREFIX . 'lieu'
						. ' WHERE	id=:id'
							. ' OR nom_technique LIKE :nomTech;';
				$prep = $db->prepare($query);
				$prep->bindValue(':id',			$_POST['search'],				PDO::PARAM_INT);
				$prep->bindValue(':nomTech',	'%' . $_POST['search'] . '%',	PDO::PARAM_STR);
				$prep->execute($db, __FILE__, __LINE__);
				$arrAll = $prep->fetchAll();
				$prep->closeCursor();
				$prep = NULL;

				if(count($arrAll) == 0)
				{
					?>
					Aucun résultat<br />
					<?
				}
				else
				{
					foreach($arrAll as &$arr)
					{
						?>
						<a href="#" onclick="Changer_Lieu2('<?=$arr["nom_technique"];?>')"><?=$arr["nom_technique"];?></a><br />
						<?
					}
				}
				?>
				... <a href="#" onclick="Changer_Lieu();">Chercher</a> ...
				<?
				break;
				
			//Rercherche selon un nom du lieu (Avec utilisation de l'ID du lieu)
			case 'selectLieu':
				$query = 'SELECT id, nom_technique, nom_affiche'
						. ' FROM ' . DB_PREFIX . 'lieu'
						. ' WHERE	id=:id'
							. ' OR nom_technique LIKE :nomTech;';
				$prep = $db->prepare($query);
				$prep->bindValue(':id',			$_POST['search'],				PDO::PARAM_INT);
				$prep->bindValue(':nomTech',	'%' . $_POST['search'] . '%',	PDO::PARAM_STR);
				$prep->execute($db, __FILE__, __LINE__);
				$arrAll = $prep->fetchAll();
				$prep->closeCursor();
				$prep = NULL;

				if(count($arrAll) == 0)
				{
					?>
					Aucun résultat<br />
					<?
				}
				else
				{
					foreach($arrAll as &$arr)
					{
						?>
						<a href="#" onclick="Changer_Lieu2(<?=$arr['id'];?>, '<?=$arr["nom_technique"];?>')"><?=$arr["nom_technique"];?></a><br />
						<?
					}
				}
				?>
				... <a href="#" onclick="Changer_Lieu();">Chercher</a> ...
				<?
				break;
				
			//Rercherche selon un nom de compte (Pour la modification du compte auquel un perso est associé
			case 'p_modifier_compte':
				$query = 'SELECT id, user'
						. ' FROM ' . DB_PREFIX . 'account'
						. ' WHERE	id=:id'
							. ' OR user LIKE :user;';
				$prep = $db->prepare($query);
				$prep->bindValue(':id',			$_POST['search'],			PDO::PARAM_INT);
				$prep->bindValue(':user',	'%' . $_POST['search'] . '%',	PDO::PARAM_STR);
				$prep->execute($db, __FILE__, __LINE__);
				$arrAll = $prep->fetchAll();
				$prep->closeCursor();
				$prep = NULL;

				if(count($arrAll) == 0)
				{
					?>
					Aucun résultat<br />
					<?
				}
				else
				{
					foreach($arrAll as &$arr)
					{
						?>
						<a href="#" onclick="Changer_Compte2('<?=$arr["id"];?>','<?=$arr["user"];?>')"><?=$arr["user"];?></a><br />
						<?
					}
				}
				?>
				... <a href="#" onclick="Changer_Compte();">Chercher</a> ...
				<?
				break;
				
			//Rercherche selon un nom de perso
			case 'p_ajouter_perso':
				$query = 'SELECT id, nom'
						. ' FROM ' . DB_PREFIX . 'perso'
						. ' WHERE	id=:id'
							. ' OR nom LIKE :nom;';
				$prep = $db->prepare($query);
				$prep->bindValue(':id',			$_POST['search'],				PDO::PARAM_INT);
				$prep->bindValue(':nom',	'%' . $_POST['search'] . '%',	PDO::PARAM_STR);
				$prep->execute($db, __FILE__, __LINE__);
				$arrAll = $prep->fetchAll();
				$prep->closeCursor();
				$prep = NULL;

				if(count($arrAll) == 0)
				{
					?>
					Aucun résultat<br />
					<?
				}
				else
				{
					foreach($arrAll as &$arr)
					{
						?>
						<a href="#" onclick="Ajouter_Perso2('<?=$arr["id"];?>','<?=$arr["nom"];?>')"><?=$arr["nom"];?></a><br />
						<?
					}
				}
				?>
				... <a href="#" onclick="Ajouter_Perso();">Chercher</a> ...
				<?
				break;
			
			
			
			### Menu principal Panel MJ
			
			//Rercherche selon un nom de compte
			case 'compte':
				$query = 'SELECT id, user'
						. ' FROM ' . DB_PREFIX . 'account'
						. ' WHERE	id=:id'
							. ' OR user LIKE :user'
						. ' ORDER BY user ASC;';
				$prep = $db->prepare($query);
				$prep->bindValue(':id',			$_POST['search'],			PDO::PARAM_INT);
				$prep->bindValue(':user',	'%' . $_POST['search'] . '%',	PDO::PARAM_STR);
				$prep->execute($db, __FILE__, __LINE__);
				$arrAll = $prep->fetchAll();
				$prep->closeCursor();
				$prep = NULL;

				if(count($arrAll) == 0)
				{
					echo 'MULTI|Aucun résultat<br />';
				}
				elseif(count($arrAll)==1)
				{
					//BUG?: Nécéssaire de $arr = $arrAll[0] ?
					$arrAll = $arrAll[0];
					?>
					<?=$arrAll['id'];?>|
					<input type="radio" name="id" value="<?=$arrAll['id'];?>" onclick="setLinkAccount(<?=$arrAll['id'];?>);" CHECKED /><?=$arrAll['user'];?><br />
					<?
				}
				else
				{
					echo 'MULTI|';
					foreach($arrAll as &$arr)
					{
						?>
						<input type="radio" name="id" value="<?=$arr['id'];?>" onclick="setLinkAccount(<?=$arr['id'];?>);" /><?=$arr['user'];?><br />
						<?
					}
				}
				break;
				
			//Recherche selon un nom de perso
			case 'perso':
				$query = 'SELECT p.id, a.user, p.nom'
						. ' FROM ' . DB_PREFIX . 'perso as p'
						. ' LEFT JOIN ' . DB_PREFIX . 'account as a ON (a.id=p.userId)'
						. ' WHERE	p.id=:id'
							. ' OR p.nom LIKE :nom'
						. ' ORDER BY p.nom ASC;';
				$prep = $db->prepare($query);
				$prep->bindValue(':id',			$_POST['search'],			PDO::PARAM_INT);
				$prep->bindValue(':nom',	'%' . $_POST['search'] . '%',	PDO::PARAM_STR);
				$prep->execute($db, __FILE__, __LINE__);
				$arrAll = $prep->fetchAll();
				$prep->closeCursor();
				$prep = NULL;
				
				if(count($arrAll) == 0)
				{
					echo 'MULTI|Aucun résultat<br />';
				}
				elseif(count($arrAll)==1)
				{
					$arrAll = $arrAll[0];
					echo $arrAll['id'];
					?>|
					<input type="button" class="button" style="width:20px;" value="<" onclick="document.forms['compte'].search.value='<?=$arrAll['user'];?>';gosearch('compte')" />
					<input type="radio" name="id" value="<?=$arrAll['id'];?>" onclick="setLinkPerso(<?=$arrAll['id'];?>);" checked="checked" /><?=$arrAll['nom'];?><br />
					<?
				}
				else
				{
					echo 'MULTI|';
					foreach($arrAll as &$arr)
					{
						?>
						<input type="button" class="button" style="width:20px;" value="<" onclick="document.forms['compte'].search.value='<?=$arr['user'];?>';gosearch('compte')" />
						<input type="radio" name="id" value="<?=$arr['id'];?>" onclick="setLinkPerso(<?=$arr['id'];?>);" /><?=$arr['nom'];?><br />
						<?
					}
				}
				break;
				
			//Recherche selon un nom de lieu (Technique ou affichable)
			case 'lieu':
				$query = 'SELECT id, nom_technique, nom_affiche'
						. ' FROM ' . DB_PREFIX . 'lieu'
						. ' WHERE	id=:id'
							. ' OR nom_technique LIKE :nom'
						. ' ORDER BY nom_technique ASC;';
				$prep = $db->prepare($query);
				$prep->bindValue(':id',			$_POST['search'],			PDO::PARAM_INT);
				$prep->bindValue(':nom',	'%' . $_POST['search'] . '%',	PDO::PARAM_STR);
				$prep->execute($db, __FILE__, __LINE__);
				$arrAll = $prep->fetchAll();
				$prep->closeCursor();
				$prep = NULL;
					
				if(count($arrAll) == 0)
				{
					echo 'MULTI|Aucun résultat<br />';
				}
				elseif (count($arrAll)==1)
				{
					$arrAll = $arrAll[0];
					?>
					<?=$arrAll['id'];?>|
					<input type="radio" name="id" value="<?=$arrAll['id'];?>" onclick="setLinkLieu(<?=$arrAll['id'];?>);" checked="checked" /><?=$arrAll['nom_technique'];?><br />
					<?
				}
				else
				{
					echo 'MULTI|';
					foreach($arrAll as &$arr)
					{
						?>
						<input type="radio" name="id" value="<?=$arr['id'];?>" onclick="setLinkLieu(<?=$arr['id'];?>);" /><?=$arr['nom_technique'];?><br />
						<?
					}
				}
				break;
			
			
			
			
			//### RECHERCHE POUR DONNER À (De l'inventaire)
			
			//Recherche selon un nom de perso
			case 'inventaire_perso':
				$query = 'SELECT p.id, a.user, p.nom, p.lieu'
						. ' FROM ' . DB_PREFIX . 'perso as p'
						. ' LEFT JOIN ' . DB_PREFIX . 'account as a ON (a.id=p.userId)'
						. ' WHERE	p.id=:id'
							. ' OR p.nom LIKE :nom'
						. ' ORDER BY p.nom ASC;';
				$prep = $db->prepare($query);
				$prep->bindValue(':id',			$_POST['search'],			PDO::PARAM_INT);
				$prep->bindValue(':nom',	'%' . $_POST['search'] . '%',	PDO::PARAM_STR);
				$prep->execute($db, __FILE__, __LINE__);
				$arrAll = $prep->fetchAll();
				$prep->closeCursor();
				$prep = NULL;
				
				$checked = '';
				if (count($arrAll)==1)
					$checked = 'checked="checked" ';
					
				foreach($arrAll as &$arr)
				{
					?>
					<input type="radio" name="persoId" value="<?=$arr['id'];?>" <?=$checked;?>/>
						<strong><?=$arr['nom'];?></strong>
						 <i>(<?=$arr['lieu'];?>)</i>
						<br />
					<?
				}
				break;
				
			//Recherche selon un nom de casier
			case 'inventaire_casier':
				$query = 'SELECT id_casier, nom_casier, lieu_casier'
						. ' FROM ' . DB_PREFIX . 'lieu_casier'
						. ' WHERE	id_casier=:id'
							. ' OR nom_casier LIKE :nomC'
							. ' OR lieu_casier LIKE :nomL'
						. ' ORDER BY lieu_casier ASC, nom_casier ASC;';
				$prep = $db->prepare($query);
				$prep->bindValue(':id',			$_POST['search'],			PDO::PARAM_INT);
				$prep->bindValue(':nomC',	'%' . $_POST['search'] . '%',	PDO::PARAM_STR);
				$prep->bindValue(':nomL',	'%' . $_POST['search'] . '%',	PDO::PARAM_STR);
				$prep->execute($db, __FILE__, __LINE__);
				$arrAll = $prep->fetchAll();
				$prep->closeCursor();
				$prep = NULL;
				
				$checked = '';
				if (count($arrAll)==1)
					$checked = 'checked="checked" ';
					
				foreach($arrAll as &$arr)
				{
					?>
					<input type="radio" name="casierId" value="<?=$arr['id'];?>" <?=$checked;?>/>
						<strong><?=$arr['lieu_casier'];?></strong>; 
						<i><?=$arr['nom_casier'];?></i>
						(Id# <?=$arr['id_casier'];?>)
						<br />
					<?
				}
				break;
			
			
			//Recherche selon un nom de lieu (Technique ou affichable)
			case 'inventaire_lieu':
				$query = 'SELECT id, nom_technique, nom_affiche'
						. ' FROM ' . DB_PREFIX . 'lieu'
						. ' WHERE	id=:id'
							. ' OR nom_technique LIKE :nom'
						. ' ORDER BY nom_technique ASC;';
				$prep = $db->prepare($query);
				$prep->bindValue(':id',			$_POST['search'],			PDO::PARAM_INT);
				$prep->bindValue(':nom',	'%' . $_POST['search'] . '%',	PDO::PARAM_STR);
				$prep->execute($db, __FILE__, __LINE__);
				$arrAll = $prep->fetchAll();
				$prep->closeCursor();
				$prep = NULL;
				
				
					
				if(count($arrAll)==0)
				{
					?>
					Aucun résultat<br />
					<?
				}
				else
				{
					$checked = '';
					if (count($arrAll)==1)
						$checked = 'checked="checked" ';
					
					foreach($arrAll as &$arr)
					{
						?>
						<input type="radio" name="lieuTech" value="<?=$arr['nom_technique'];?>" <?=$checked;?>/>
							<strong><?=$arr['nom_technique'];?></strong>; 
							<i><?=$arr['nom_affiche'];?></i>
							<br />
						<?
					}
				}
				break;
				
			//Recherche selon un nom d'un item (affichable ou id)
			case 'inventaire_item':
				$query = 'SELECT db_id, db_nom, db_type'
						. ' FROM ' . DB_PREFIX . 'item_db'
						. ' WHERE	db_id=:id'
							. ' OR db_nom LIKE :nom'
							. ' OR db_type LIKE :type'
						. ' ORDER BY db_type,db_soustype,db_nom ASC;';
				$prep = $db->prepare($query);
				$prep->bindValue(':id',			$_POST['search'],			PDO::PARAM_INT);
				$prep->bindValue(':nom',	'%' . $_POST['search'] . '%',	PDO::PARAM_STR);
				$prep->bindValue(':type',	'%' . $_POST['search'] . '%',	PDO::PARAM_STR);
				$prep->execute($db, __FILE__, __LINE__);
				$arrAll = $prep->fetchAll();
				$prep->closeCursor();
				$prep = NULL;
				
				if(count($arrAll)==0)
				{
					?>
					Aucun résultat<br />
					<?
				}
				else
				{
					foreach($arrAll as &$arr)
					{
						foreach($arr as &$e)
							if(is_string($e))
								$e = stripslashes($e);
						?>
						<input type="checkbox" name="itemId[]" value="<?=$arr['db_id'];?>" />
						Qte.: <input type="text" class="text" name="item<?=$arr['db_id'];?>" value="1" size="2" />
						<?=$arr['db_type'];?>; <?=$arr['db_nom'];?><br />
						<?
					}
				}
				break;
				
				
				
				
			//### Mj_Item_Inv_Mod: Un item peut être associé à un PERSO, un LIEU (incluant les casiers), ou une BOUTIQUE.
			
			//Recherche selon un nom de perso
			case 'item_asso_perso':
				$query = 'SELECT p.id, a.user, p.nom'
						. ' FROM ' . DB_PREFIX . 'perso as p'
						. ' LEFT JOIN ' . DB_PREFIX . 'account as a ON (a.id=p.userId)'
						. ' WHERE	p.id=:id'
							. ' OR p.nom LIKE :nom'
						. ' ORDER BY p.nom ASC;';
				$prep = $db->prepare($query);
				$prep->bindValue(':id',			$_POST['search'],			PDO::PARAM_INT);
				$prep->bindValue(':nom',	'%' . $_POST['search'] . '%',	PDO::PARAM_STR);
				$prep->execute($db, __FILE__, __LINE__);
				$arrAll = $prep->fetchAll();
				$prep->closeCursor();
				$prep = NULL;
				
				if(count($arrAll)==0)
				{
					?>
					Aucun résultat<br />
					<?
				}
				else
				{
					foreach($arrAll as &$arr)
					{
						?>
						<a href="#" onclick="Changer_Perso2('<?=$arr['id'];?>','<?=mysql_real_escape_string($arr['nom']);?>')"><?=$arr['nom'];?></a><br />
						<?
					}
				}
				?>
				... <a href="#" onclick="Changer_Perso();">Chercher</a> ...
				<?
				break;
				
				
			//Recherche selon un nom de lieu (Technique ou affichable)
			case 'item_asso_lieu':
				$query = 'SELECT id, nom_technique, nom_affiche'
						. ' FROM ' . DB_PREFIX . 'lieu'
						. ' WHERE	id=:id'
							. ' OR nom_technique LIKE :nom'
						. ' ORDER BY nom_technique ASC;';
				$prep = $db->prepare($query);
				$prep->bindValue(':id',			$_POST['search'],			PDO::PARAM_INT);
				$prep->bindValue(':nom',	'%' . $_POST['search'] . '%',	PDO::PARAM_STR);
				$prep->execute($db, __FILE__, __LINE__);
				$arrAll = $prep->fetchAll();
				$prep->closeCursor();
				$prep = NULL;

				if(count($arrAll)==0)
				{
					?>
					Aucun résultat<br />
					<?
				}
				else
				{
					foreach($arrAll as &$arr)
					{
						?>
						<a href="#" onclick="Changer_Lieu2('<?=$arr['nom_technique'];?>','<?=mysql_real_escape_string($arr['nom_affiche']);?>')"><?=$arr['nom_affiche'];?></a><br />
						<?
					}
				}
				?>
				... <a href="#" onclick="Changer_Lieu();">Chercher</a> ...
				<?
				break;
				
				
			//Recherche selon un nom de perso
			case 'item_asso_boutique':
				$query = 'SELECT id, nom_technique, nom_affiche'
						. ' FROM ' . DB_PREFIX . 'lieu'
						. ' WHERE	proprioid IS NOT NULL'
							. ' AND ('
								. ' id=:id'
								. ' OR nom_technique LIKE :nom'
							. ' )'
						. ' ORDER BY nom_technique ASC;';
				$prep = $db->prepare($query);
				$prep->bindValue(':id',			$_POST['search'],			PDO::PARAM_INT);
				$prep->bindValue(':nom',	'%' . $_POST['search'] . '%',	PDO::PARAM_STR);
				$prep->execute($db, __FILE__, __LINE__);
				$arrAll = $prep->fetchAll();
				$prep->closeCursor();
				$prep = NULL;

				if(count($arrAll)==0)
				{
					?>
					Aucun résultat<br />
					<?
				}
				else
				{
					foreach($arrAll as &$arr)
					{
						?>
						<a href="#" onclick="Changer_Lieu2('<?=$arr['nom_technique'];?>','<?=mysql_real_escape_string($arr['nom_affiche']);?>')"><?=$arr['nom_affiche'];?></a><br />
						<?
					}
				}
				?>
				... <a href="#" onclick="Changer_Boutique();">Chercher</a> ...
				<?
				break;
				
			// ### Mj_Item -> Requete AJAX de recherche d'item en circulation
			case 'item_locate_by_dbId':
				?>
				<table class="subcenter">
				<tr>
					<td class="name">Lieux</td>
					<td class="name">Boutiques (Inv)</td>
					<td class="name">Casiers (Inv)</td>
					<td class="name">Items (Inv Sac)</td>
					<td class="name">Perso</td>
				</tr>
				<tr>
					<td class="value">
						<?
						$query = 'SELECT DISTINCT id, inv_lieutech, nom_technique, nom_affiche'
								. ' FROM ' . DB_PREFIX . 'item_inv'
								. ' LEFT JOIN ' . DB_PREFIX . 'lieu ON (nom_technique=inv_lieutech)'
								. ' WHERE 	inv_lieutech IS NOT NULL'
									. ' AND inv_dbid=:id;';
						$prep = $db->prepare($query);
						$prep->bindValue(':id',			$_POST['db_id'],			PDO::PARAM_INT);
						$prep->execute($db, __FILE__, __LINE__);
						$arrAll = $prep->fetchAll();
						$prep->closeCursor();
						$prep = NULL;

						if(count($arrAll)==0)
						{
							echo '-';
						}
						else
						{
							foreach($arrAll as &$arr)
							{
								if(empty($arr['id']))
								{
									echo '<i>Lieu innexistant (' . $arr['inv_lieutech'] . ')</i><br />';
								}
								else
								{
									echo '<a href="?mj=Lieu_Inventaire&id=' . $arr['id'] . '">' . $arr['nom_affiche'] . '</a><br />';
								}
							}
						}
					?>
					</td>
					<td class="value">
						<?
						$query = 'SELECT DISTINCT id, inv_boutiquelieutech, nom_technique, nom_affiche'
								. ' FROM ' . DB_PREFIX . 'item_inv'
								. ' LEFT JOIN ' . DB_PREFIX . 'lieu ON (nom_technique=inv_boutiquelieutech)'
								. ' WHERE	inv_boutiquelieutech IS NOT NULL'
									. ' AND inv_dbid=:id;';
						$prep = $db->prepare($query);
						$prep->bindValue(':id',			$_POST['db_id'],			PDO::PARAM_INT);
						$prep->execute($db, __FILE__, __LINE__);
						$arrAll = $prep->fetchAll();
						$prep->closeCursor();
						$prep = NULL;
				
						if(count($arrAll)==0)
						{
							echo '-';
						}
						else
						{
							foreach($arrAll as &$arr)
							{
								if(empty($arr['id']))
									echo '<i>Lieu innexistant (' . $arr['inv_boutiquelieutech'] . ')</i><br />';
								else
									echo '<a href="?mj=Lieu_Mod&id=' . $arr['id'] . '">' . $arr['nom_affiche'] . '</a><br />';
							}
						}
					?>
					</td>
					<td class="value">
						<?
						$query = 'SELECT DISTINCT id_casier, inv_idcasier, nom_casier'
								. ' FROM ' . DB_PREFIX . 'item_inv'
								. ' LEFT JOIN ' . DB_PREFIX . 'lieu_casier ON (id_casier=inv_idcasier)'
								. ' WHERE	inv_idcasier IS NOT NULL'
									. ' AND inv_dbid=:id;';
						$prep = $db->prepare($query);
						$prep->bindValue(':id',			$_POST['db_id'],			PDO::PARAM_INT);
						$prep->execute($db, __FILE__, __LINE__);
						$arrAll = $prep->fetchAll();
						$prep->closeCursor();
						$prep = NULL;
				
						if(count($arrAll)==0)
						{
							echo '-';
						}
						else
						{
							foreach($arrAll as &$arr)
							{
								if(empty($arr['id']))
									echo '<i>Casier innexistant (' . $arr['inv_idcasier'] . ')</i><br />';
								else
									echo '<a href="javascript:alert(\'a venir quand la gestion des casiers sera faite.\');">' . $arr['nom_casier'] . '</a><br />';
							}
						}
					?>
					</td>
					<td class="value">
						<?
						$query = 'SELECT DISTINCT db_id, inv_itemid, db_nom'
								. ' FROM ' . DB_PREFIX . 'item_inv'
								. ' LEFT JOIN ' . DB_PREFIX . 'item_db ON (db_id=inv_itemid)'
								. ' WHERE	inv_idcasier IS NOT NULL'
									. ' AND inv_dbid=:id;';
						$prep = $db->prepare($query);
						$prep->bindValue(':id',			$_POST['db_id'],			PDO::PARAM_INT);
						$prep->execute($db, __FILE__, __LINE__);
						$arrAll = $prep->fetchAll();
						$prep->closeCursor();
						$prep = NULL;
						
						if(count($arrAll)==0)
						{
							echo '-';
						}
						else
						{
							foreach($arrAll as &$arr)
							{
								if(empty($arr['db_id']))
									echo '<i>Sac innexistant (' . $arr['inv_itemid'] . ')</i><br />';
								else
									echo '<a href="javascript:alert(\'a venir quand la gestion des sacs sera faite.\');">' . $arr['db_nom'] . '</a><br />';
							}
						}
					?>
					</td>
					<td class="value">
						<?
						$query = 'SELECT nom, id'
								. ' FROM ' . DB_PREFIX . 'item_inv'
								. ' LEFT JOIN ' . DB_PREFIX . 'perso ON (id=inv_persoid)'
								. ' WHERE	inv_persoid IS NOT NULL'
									. ' AND inv_dbid=:id;';
						$prep = $db->prepare($query);
						$prep->bindValue(':id',			$_POST['db_id'],			PDO::PARAM_INT);
						$prep->execute($db, __FILE__, __LINE__);
						$arrAll = $prep->fetchAll();
						$prep->closeCursor();
						$prep = NULL;

						if(count($arrAll)==0)
						{
							echo '-';
						}
						else
						{
							foreach($arrAll as &$arr)
								echo '<a href="?mj=Perso_Inventaire&id=' . $arr['id'] . '">' . $arr['nom'] . '</a><br />';
						}
					?>
					</td>
				</tr>
				</table>
				<?
				
				
				break;
			default:
				?>
				Erreur !
				<?
				break;
		}
		
		die(); //Ne pas afficher le bas de page
	}
}

