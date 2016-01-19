<?php
/** Gestion de l'interface des déplacement
*
* @package Member_Action
*/
class Member_Action_Perso_Deplacement2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl = '?popup=1&amp;m=Action_Perso_Deplacement';
		
		
		//Valider l'état du perso
		if(!$perso->isAutonome())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.', $errorUrl);
		
		
		//Valider si un lieu de destination à été recu
		if(!isset($_POST['moveto']))
			return fctErrorMSG('Vous devez sélectionner le lieu vers lequel vous désirer vous déplacer.', $errorUrl);
		
			
		//Valider si un type d'action à été recu
		if(!isset($_POST['RAD_typeaction']))
			return fctErrorMSG('Vous devez sélectionner le type d\'action.', $errorUrl);
			
		
		
		
		
			
		
		//ETAPE-1: GÉNÉRER LES DONNÉES NÉCÉSSAIRE À L'ACTION:
		//Générer la liste des lieux connexes
		$i=0;
		$found=false;
		while(!$found && $lien = $perso->getLieu()->getLink($i++, $perso->getId()))
			if ($lien->getId() == $_POST['moveto'])
				$found=true;
		
		//Valider si le lieu choisi est accessible en tant que sous-lieu du lieu actuel
		if(!$found)
			return fctErrorMSG('Le lieu que vous avez sélectionné n\'existe pas.', $errorUrl);
			
		//Passer les informations sur le lieu de destination au template
		$tpl->set('LIEU', $lien);
		
		
		
		//Générer la liste des personnages (à qui le joueur veux tenir la porte)
		$i=0;
		$arrPersoTenirPorte = array();
		$arrPersoForcerEntrer = array();
		while( $tmp = $perso->getLieu()->getPerso($perso, $i++))
		{
			if($tmp->getId() != $perso->getId())
			{
				//Tenir la porte
				if(isset($_POST['t_' . $tmp->getId()]))
					$arrPersoTenirPorte[] = (int)$tmp->getId();

				//Forcer à entrer
				if(isset($_POST['f_' . $tmp->getId()]))
					if(!$tmp->isConscient() || $tmp->getMenotte() || $tmp->getPa()==0)
						$arrPersoForcerEntrer[] = (int)$tmp->getId();
			}
		}
		
		//Valider si le joueur à choisi de tenir la porte et à sélectionné des gens	
		if($_POST['RAD_typeaction']=='tenirporte' && count($arrPersoTenirPorte)==0)
			return fctErrorMSG('Si vous désirer uniquement tenir la porte, vous devez sélectionner au moins une personne à qui tenir la porte.', $errorUrl);
		//$tpl->set('PERSOS', $arrPersoTenirPorte);


		//Valider que le joueur ne tiens pas la porte en même temps qu'il tente un déplacement furtif
		if($_POST['RAD_typeaction']=='tenirporte' && isset($_POST['CHK_furtif']))
			return fctErrorMSG('Vous ne pouvez pas effectuer un déplacement furtif sans vous déplacer.', $errorUrl);
		
		
		
		
		
		
		
		
		
		
		//ETAPE-2: VALIDATION SELON LES DONNÉES DYNAMIQUES (Cout/Pa):
		//Calcul du cout en PA de l'action
		$coutPa=0;
		
		//$coutPa += count($arrPersoTenirPorte) * 5;
		
		$coutPa += count($arrPersoForcerEntrer) * 10;
		
		if($_POST['RAD_typeaction']=='deplacer')
			$coutPa+= $lien->getPa();
		
		if(isset($_POST['CHK_furtif']))
			$coutPa+=10 ;

		
		//GESTION DES PROTECTION
		$protection = $lien->getProtection();
		
		//Si la porte est tenue, retirer les protections
		if (!empty($protection))
		{
			$query = 'SELECT id'
					. ' FROM ' . DB_PREFIX . 'lieu_tenirporte'
					. ' WHERE de=:de'
						. ' AND vers=:vers'
						. ' AND qui=:persoId'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':de',			$perso->getLieu()->getNomTech(),PDO::PARAM_STR);
			$prep->bindValue(':vers',		$lien->getNomTech(),			PDO::PARAM_STR);
			$prep->bindValue(':persoId',	$perso->getId(),				PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$arr = $prep->fetch();
			$prep->closeCursor();
			$prep = NULL;
			
			if ($arr !== false) //La porte est tenu, retirer la protection
			{
				$protection = null;
				$porteTenueId = $arr[0];
			}
			$coutPa += 1;
		}
		
		
		//Valider si le perso à suffisamment de PA pour effectuer les actions demandées
		if($perso->getPa() <= $coutPa)
			return fctErrorMSG('Vous n\'avez pas assez de PA pour effectuer cette action.', $errorUrl);
		
		
		//Valider si le perso à suffisamment d'argent pour effectuer les actions demandées
		if($perso->getCash() < $lien->getCout())
			return fctErrorMSG('Vous n\'avez pas assez d\'argent pour effectuer cette action.', $errorUrl);
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		//ÉTAPE-3 : En cas de DIGIPASS ou de CLÉ, vérifier la validité de ceux-ci (si le joueur les à entré)
		if(!empty($protection))
		{
			if($protection=='pass' && isset($_POST['pass']))
				$code = $_POST['pass'];
			
			if($protection=='clef' && isset($_POST['cle']))
			{
				$i=0;
				while( $item = $perso->getInventaire($i++))
				{
					if($item instanceof Member_ItemClef)
					{
						if($item->getInvId() == $_POST['cle'])
						{
							$code = $item->getCode();
							break;
						}
					}
				}
			}
			
			//Valider le code, si OK, retirer la protection
			if (isset($code))
				if(strval($code) === strval($lien->getPass()))
					$protection=null;
		}
		
		
		
		
		
		
		//S'il y a une protection d'active; Afficher la bonne page en ce qui concerne les protections
		if(!empty($protection))
		{
			$tpl->set('moveto'					, $_POST['moveto']);
			$tpl->set('RAD_typeaction'			, $_POST['RAD_typeaction']);
			$tpl->set('CHK_furtif'				, (isset($_POST['CHK_furtif'])) ? $_POST['CHK_furtif'] : null);
			$tpl->set('CHK_keepcurrentaction'	, (isset($_POST['CHK_keepcurrentaction'])) ? $_POST['CHK_keepcurrentaction'] : null);
			$tpl->set('arrPorteTenuA'			, $arrPersoTenirPorte);
			$tpl->set('arrPorteForcerA'			, $arrPersoForcerEntrer);
			
			switch ($protection)
			{
				case 'clef':		//Requiert une clé (Générer la liste des clés en inventaire)
					if(isset($code)) // un code a été entré mais la protection est toujours en place, donc le code était mauvais.
					{
						//Retirer des PA pour l'entré du pass ou de la clé
						$perso->changePa('-', 1);
						
						$tpl->set('WRONGPASS', true);
					}
					if(isset($_POST['CHK_keepcurrentaction']))
						$tpl->set('CHK_keepcurrentaction', $_POST['CHK_keepcurrentaction']);
						
					if(isset($_POST['CHK_furtif']))
						$tpl->set('CHK_furtif', $_POST['CHK_furtif']);
					$tpl->set('RAD_typeaction', $_POST['RAD_typeaction']);
					$tpl->set('moveto', $_POST['moveto']);
					
					
					
					$i=0; $e=0;
					$cle = array();
					while( $item = $perso->getInventaire($i++))
						if($item instanceof Member_ItemClef)
							$cle[$e++] = $item;
					$tpl->set('CLES', $cle);
					
					return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Perso/Deplacement_cle.htm',__FILE__,__LINE__);
					break;
					
					
				case 'pass':	//Requiert un code DIGIPASS
					if(isset($code)) // un code a été entré mais la protection est toujours en place, donc le code était mauvais.
					{
						//Retirer des PA pour l'entré du pass ou de la clé
						$perso->changePa('-', 1);
						$tpl->set('WRONGPASS', true);
					}
					if(isset($_POST['CHK_keepcurrentaction']))
						$tpl->set('CHK_keepcurrentaction', $_POST['CHK_keepcurrentaction']);
						
					if(isset($_POST['CHK_furtif']))
						$tpl->set('CHK_furtif', $_POST['CHK_furtif']);
					$tpl->set('RAD_typeaction', $_POST['RAD_typeaction']);
					$tpl->set('moveto', $_POST['moveto']);
					
					return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Perso/Deplacement_digipass.htm',__FILE__,__LINE__);
					break;
					
					
				case 'ban':		//Banni du lieu
					return fctErrorMSG('Vous êtes actuellement banni de ce lieu, vous ne pouvez pas y accéder.', $errorUrl);
					break;
			}
		}
		
		
		
		//EFFECTUER LE DÉPLACEMENT
		//Retirer l'argent
		if ($lien->getCout() > 0)
		{
			$perso->changeCash('-', $lien->getCout());
			$perso->setCash();
		}
		
		//Retirer les PA
		$perso->changePa('-', $coutPa);
		$perso->setPa();
		$msg = '';
		
		
		//Si la porte à été tenue, enregistrer la 'porte tenue' puis envoyer le message aux personnes concernées.
		$expire_time = mktime (date("H")+TENIRPORTE_TIMEOUT, date("i"), date("s"), date("m"), date("d"), date("Y"));
		$arrTenirPortePersoId=array();

		$query='INSERT IGNORE INTO ' . DB_PREFIX . 'lieu_tenirporte'
				. ' (de,vers,qui,expiration)'
				. ' VALUES'
				. ' (:de, :vers, :persoId, :expiration);';
		$prep = $db->prepare($query);
		
		foreach($arrPersoTenirPorte as $pid)
		{
			$prep->bindValue(':de',			$perso->getLieu()->getNomTech(),	PDO::PARAM_STR);
			$prep->bindValue(':vers',		$lien->getNomTech(),				PDO::PARAM_STR);
			$prep->bindValue(':persoId',	$pid,								PDO::PARAM_INT);
			$prep->bindValue(':expiration',	$expire_time,						PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);

			$arrTenirPortePersoId[] = $pid;
		}
		$prep->closeCursor();
		$prep = NULL;
		
		if(count($arrTenirPortePersoId)>0)
			Member_He::add($perso->getId(), $arrTenirPortePersoId, 'move', "La porte est tenue ouverte du lieu " . $perso->getLieu()->getNom() . " vers " . $lien->getNom());



		
		
		if($_POST['RAD_typeaction']=='deplacer')
		{
	
			//Calculer la réussite d'un déplacement furtif
			$deplacementFurtif = false;
			if (isset($_POST["CHK_furtif"]))
			{
				
				$de = rand(0,100);
				$chance = $perso->getChancesReussite('FRTV');
				if ($de < $chance)//Reussite		
				{	
					
					$msg .= "Vous effectuez un déplacement furtif avec succès et personne ne vous voit.\n";
					$msg .= $perso->setStat(array('PER' => '+01', 'DEX' => '+01', 'FOR' => '-02' ));
					$msg .= $perso->setComp(array('FRTV' => rand(1,3) ));
					
					$deplacementFurtif = true;
					
				}
				else //Echec
				{
					
					$msg .= "Vous tentez un déplacement furtif mais c'est un échec.\n";
					$msg .= $perso->setStat(array('AGI' => '+01', 'DEX' => '-01'));
					$msg .= $perso->setComp(array('FRTV' => rand(1,3) ));
					
				}
			}
		
		
		
			//Envoyer les message de déplacement
			
			if(!$deplacementFurtif)
			{
				
				
				$ret = in_array($perso->getLieu()->getNomTech(), array(INNACTIVITE_TELEPORT_LOCATION, INNACTIVITE_VOLUNTARY_LOCATION));
				if(!$ret)
				{
					//Faire la liste de tout les personnages du lieu de départ
					$i=0; $e=0;
					$arrFrom=array();
					while( $tmp = $perso->getLieu()->getPerso($perso, $i++))
						if($tmp->getId() != $perso->getId())
							$arrFrom[$e++] = $tmp->getId();
					Member_He::add($perso->getId(), $arrFrom, 'move', "Vous voyez une personne sortir du lieu où vous vous trouvez en direction de [i]" . $lien->getNom() . "[/i].", HE_AUCUN, HE_TOUS); //HE_UNIQUEMENT_MOI
				}
				
				$ret = in_array($lien->getNomTech(), array(INNACTIVITE_TELEPORT_LOCATION, INNACTIVITE_VOLUNTARY_LOCATION));
				if(!$ret)
				{
					//Faire la liste de tout les personnages du lieu de destination
					$i=0; $e=0;
					$arrTo=array();
					while( $tmp = $lien->getPerso($perso, $i++))
						if($tmp->getId() != $perso->getId()) //Théoriquement cette validation ne peux JAMAIS arriver à ==
							$arrTo[$e++] = $tmp->getId();
					Member_He::add($perso->getId(), $arrTo, 'move', "Vous voyez une personne en provenance de [i]" . $perso->getLieu()->getNom() . "[/i] entrer dans le lieu où vous vous trouvez.", HE_AUCUN, HE_TOUS); //HE_UNIQUEMENT_MOI
				}
			}
			
			

			
			
			//Je suis le joueur à qui on à tenu la porte, je passe, donc effacer mon accès (si accès il y a).
			if (isset($porteTenueId))
			{
				$query = 'DELETE FROM ' . DB_PREFIX . 'lieu_tenirporte'
						. ' WHERE id=:porteId'
						. ' LIMIT 1;';
				$prep = $db->prepare($query);
				$prep->bindValue(':porteId',	$porteTenueId,		PDO::PARAM_INT);
				$prep->execute($db, __FILE__, __LINE__);
				$prep->closeCursor();
				$prep = NULL;
			}
			
			
			
			//Effectuer les déplacements forcés:
			if(count($arrPersoForcerEntrer)>0)
			{
				$queryIn = implode(',', $arrPersoForcerEntrer);
				
				$query = 'UPDATE ' . DB_PREFIX . 'perso'
							. ' SET lieu=:nomTech'
							. ' WHERE id IN(' . $queryIn . ')'
							. ' LIMIT :limit;';
				$prep = $db->prepare($query);
				$prep->bindValue(':nomTech',	$lien->getNomTech(),			PDO::PARAM_STR);
				$prep->bindValue(':limit',		count($arrPersoForcerEntrer),	PDO::PARAM_INT);
				$prep->execute($db, __FILE__, __LINE__);
				$prep->closeCursor();
				$prep = NULL;
				
				Member_He::add($perso->getId(), $arrPersoForcerEntrer, 'move', "Vous vous faites déplacer du lieu [i]" . $perso->getLieu()->getNom() . "[/i] vers [i]" . $lien->getNom() . "[/i]", HE_AUCUN, HE_TOUS);
			}

			
			//SUPPRIMER L'action courrante
			$queryAddon01 = '';
			if (!isset($_POST['CHK_keepcurrentaction']))
				$queryAddon01 = ', current_action=""';
			

			
			//Effectuer le déplacement
			$msg .= "Vous vous déplacez vers [i]" . $lien->getNom() . "[/i].";
			$query = 'UPDATE ' . DB_PREFIX . 'perso'
						. ' SET lieu=:nomTech'
						. $queryAddon01
						. ' WHERE id=:persoId'
						. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':nomTech',	$lien->getNomTech(),	PDO::PARAM_STR);
			$prep->bindValue(':persoId',	$perso->getId(),		PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			
			//Ajouter le message du déplacement
			Member_He::add('System', $perso->getId(), 'move', $msg, HE_AUCUN, HE_TOUS);
		}
	
	
		//Rafraichir le HE
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}
