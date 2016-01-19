<?php
/** Gestion de l'interface pour créer un site sur Domnet
* Cette page est incluse par Member_Action_Item_Navigateur
* @package Member_Action
*/
class Member_Action_Item_NavigateurModacces
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		
		
		preg_match('/^([^\/]+)(?:[\/]([a-z0-9]+))?(?:[?](.+))?/', $_POST['url'], $matches);
		
		if(count($matches)<3)
			return fctErrorMsg('L\'URL du site est invalide (2).');
		
		$site_url = $matches[1];
		$mod_site_url = $matches[3];
		
		//Vérifier si l'URL existe 
		$site = Member_Siteweb::loadSite($mod_site_url);
		if (!$site)
			return fctErrorMsg('Cette URL n\'existe pas.');
		
		//Vérifier si l'accès est valide
		$acces = $site->checkAcces($_POST['user'], $_POST['pass']);
		
		if(!$acces)
			return fctErrorMsg('Vous ne possèdez pas les autorisations nécésaires (1).');
			
		if(!$acces->isAdmin())
			return fctErrorMsg('Vous ne possèdez pas les autorisations nécésaires (2).');
			
		
		if(isset($_POST['action']))
		{
			//En cas d'ajout, effectuer l'ajout du champ bidon AVANT
			if($_POST['action'] == 'add')
			{
				$query = 'INSERT INTO ' . DB_PREFIX . 'sitesweb_acces'
						. ' (`site_id`)'
						. ' VALUES'
						. ' (:id);';
				$prep = $db->prepare($query);
				$prep->bindValue(':id',			$site->getId(),			PDO::PARAM_INT);
				$prep->execute($db, __FILE__, __LINE__);
				$prep->closeCursor();
				$prep = NULL;
				
				$site->clearAcces();
			}
			
			if($_POST['action']=='mod')
			{
				$i=0;
				while( $ax = $site->getAcces($i++))
				{
					if(isset($_POST['ax_' . $ax->getId() . '_del']))
					{
						if ($acces->getId() == $ax->getId())
						{
							echo fctErrorMSG('Vous ne pouvez pas vous auto-supprimer.');
						}
						else
						{
							$query = 'DELETE FROM ' . DB_PREFIX . 'sitesweb_acces'
									. ' WHERE	id=:id'
										. ' AND site_id=:siteId'
									. ' LIMIT 1;';
							$prep = $db->prepare($query);
							$prep->bindValue(':siteId',			$site->getId(),		PDO::PARAM_INT);
							$prep->bindValue(':id',				$ax->getId(),		PDO::PARAM_INT);
							$prep->execute($db, __FILE__, __LINE__);
							$prep->closeCursor();
							$prep = NULL;
							
							$query = 'DELETE FROM ' . DB_PREFIX . 'sitesweb_pages_acces'
									. ' WHERE	user_id=:userId;';
							$prep = $db->prepare($query);
							$prep->bindValue(':userId',			$ax->getId(),		PDO::PARAM_INT);
							$prep->execute($db, __FILE__, __LINE__);
							$prep->closeCursor();
							$prep = NULL;
						}
					}
					else
					{
						if(isset($_POST['ax_' . $ax->getId() . '_user']))
						{
							$query = 'UPDATE ' . DB_PREFIX . 'sitesweb_acces'
										. ' SET	`user`		=:user,'
											. ' `pass`		=:pass,'
											. ' `accede`	=:accede,'
											. ' `poste`		=:poste,'
											. ' `modifier`	=:modifier,'
											. ' `admin`		=:admin'
										. ' WHERE	id=:id'
											. ' AND site_id=:siteId'
										. ' LIMIT 1;';
							$prep = $db->prepare($query);
							$prep->bindValue(':user',			$_POST['ax_' . $ax->getId() . '_user'],		PDO::PARAM_STR);
							$prep->bindValue(':pass',			$_POST['ax_' . $ax->getId() . '_pass'],		PDO::PARAM_STR);
							$prep->bindValue(':accede',		isset($_POST['ax_' . $ax->getId() . '_accede'])	? '1' : '0',		PDO::PARAM_STR);
							$prep->bindValue(':poste',			isset($_POST['ax_' . $ax->getId() . '_poste'])	? '1' : '0',		PDO::PARAM_STR);
							$prep->bindValue(':modifier',		isset($_POST['ax_' . $ax->getId() . '_modifier'])?'1' : '0',		PDO::PARAM_STR);
							$prep->bindValue(':admin',			isset($_POST['ax_' . $ax->getId() . '_admin'])	? '1' : '0',		PDO::PARAM_STR);
							$prep->bindValue(':siteId',			$site->getId(),		PDO::PARAM_INT);
							$prep->bindValue(':id',				$ax->getId(),		PDO::PARAM_INT);
							$prep->execute($db, __FILE__, __LINE__);
							$prep->closeCursor();
							$prep = NULL;
						}
					}
					
				}
				$site->clearAcces();
			}
		}
		
		
		//Trouver le accès déjà existants
		$i=0;
		$arrAcces = array();
		while( $ax = $site->getAcces($i++))
			$arrAcces[] = $ax;
		
		$tpl->set('ACCES', $arrAcces);
		
		//Retourner le template complété/rempli
		$tpl->set('SITE', $site);
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/NavigateurDomnetAcces.htm',__FILE__,__LINE__);
	}
}

