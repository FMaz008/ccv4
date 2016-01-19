<?php
/** Gestion de l'interface de l'action Menotter: Afficher l'interface de l'action
*
* Note: Une menotte équipée signifie qu'elle est en utilisation. Le joueur menotté à le inv_id d'inscrit dans le champ menotte de la table perso.
* @package Member_Action
*/
class Member_Action_Item_Menotter2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		$errorUrl = '?popup=1&m=Action_Item_Menotter';
		
		
		if(!isset($_POST['inv_id']))
			return fctErrorMSG('Vous devez sélectionner un item permettant le menottage.', $errorUrl);
		
		
		if(!isset($_POST['toPersoId']))
			return fctErrorMSG('Vous devez sélectionner une personne à menotter.', $errorUrl);
		
		
		if(!isset($_POST['msg']) || empty($_POST['msg']))
			return fctErrorMSG('Vous devez entrer un message expliquant l\'action. (ex.: lecture des droits)', $errorUrl);
		
		
		
		//Lister tous les items en inventaire
		$i=0;
		$arrInv=array();
		while( $item = $perso->getInventaire($i++))
			$arrInv[] = $item->getDbId();
		
		$queryIn = implode(',',$arrInv);
		
		//Valider que l'item appartient au perso ET qu'il sert a menotter
		$query = 'SELECT item_dbid'
				. ' FROM ' . DB_PREFIX . 'item_menu'
				. ' WHERE item_dbid IN (' . $queryIn . ')'
					. ' AND url="Menotter";';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		$found=false;
		foreach($arrAll as &$arr)
		{
			$i=0;
			while( $item = $perso->getInventaire($i++) ){ //Liste de tout les items du perso
				if( $item->getInvId() == $_POST['inv_id'] && $item->getDbId() == $arr['item_dbid'] && !$item->isEquip() )
				{ //S'il y a correspondance, ajouter l'item à la liste à afficher
					$found=true;
					break 2;
				}
			}
		}
		
		if(!$found)
			return fctErrorMSG('Vous devez utiliser un item en votre possession permettant le menottage.', $errorUrl);
		
		
		
		//Valider que le personnage à menotter est bel et bien dans le lieu actuel
		$i=0;
		$found=false;
		$persoDansLeLieuActuel = array();
		while( $tmp = $perso->getLieu()->getPerso($perso, $i++))
		{
			if($tmp->getId() == $_POST['toPersoId'])
			{
				$found=true;
				break;
			}
		}
		
		if(!$found)
			return fctErrorMSG('Le personnage que vous avez sélectionné ne se situe pas dans le même lieu que vous.', $errorUrl);
		
		
		
		//Tout est OK, créer et envoyer la demande de Menottage
		$menId = $_POST['inv_id'];
		//Si une ancienne demande existait, la supprimer, de facon à la renouveller
		$query = 'DELETE FROM ' . DB_PREFIX . 'perso_menotte'
				. ' WHERE inv_id =:itemId'
					. ' AND to_id=:persoId;';
		$prep = $db->prepare($query);
		$prep->bindValue(':itemId',		$menId,					PDO::PARAM_INT);
		$prep->bindValue(':persoId',	$_POST['toPersoId'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		//Créer la demande
		$query = 'INSERT INTO ' . DB_PREFIX . 'perso_menotte'
				. ' (inv_id, to_id, expiration)'
				. ' VALUES'
				. ' (:itemId, :persoId, UNIX_TIMESTAMP());';
		$prep = $db->prepare($query);
		$prep->bindValue(':itemId',			$menId,					PDO::PARAM_INT);
		$prep->bindValue(':persoId',		$_POST['toPersoId'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
			
		//Soumettre le message confirmant la demande
		$msg = fctScriptProtect($_POST['msg']);
		Member_He::add($perso->getId(), (int)$_POST['toPersoId'], 'menotte', 
						$msg . 
						"\n[HJ: Acceptez-vous de vous laisser attacher ou menotter? [<a href=\"?m=Action_Item_MenotterGo&amp;id={$menId}&amp;choix=1\">Oui</a>/<a href=\"?m=Action_Item_MenotterGo&amp;id={$menId}&amp;choix=0\">Non</a>]]", 
						HE_AUCUN, HE_TOUS, false);
						
		Member_He::add($perso->getId(), (int)$_POST['toPersoId'], 'menotte', 
						$msg . 
						"\n[HJ: En attente de la réponse du joueur ...]", 
						HE_TOUS, HE_AUCUN, false);
		
		
		
		//Rafraichir le HE
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}
