<?php
/** Désactivation du menottage
*
* Note: Une menotte équipée signifie qu'elle est en utilisation. Le joueur menotté à le inv_id d'inscrit dans le champ menotte de la table perso.
* @package Member_Action
*/
class Member_Action_Item_MenotterDel
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		$errorUrl = '?popup=1&m=Action_Item_Menotter';
		
		
		if(!isset($_POST['toPersoId']))
			return fctErrorMSG('Vous devez sélectionner une personne à détacher.', $errorUrl);
		
		
		//Trouver le perso dans le lieu actuel
		$i=0;
		$persoMenotte = false;
		while( $tmp = $perso->getLieu()->getPerso($perso, $i++))
		{
			if($tmp->getId() == $_POST['toPersoId'])
			{
				$persoMenotte = $tmp;
				break;
			}
		}
		
		//Vérifier si le perso est bien menotté
		if(!$persoMenotte->getMenotte())
			return fctErrorMSG('Cette personne n\'est pas menottée.', $errorUrl);
		
		
		//Vérifier que l'on possède l'item requis pour détacher le menotté
		$found=false;
		$i=0;
		while( $item = $perso->getInventaire($i++) ) //Liste de tout les items du perso
		{
			if($item->getInvId() == $persoMenotte->getMenotte())
			{
				$found=true;
				break;
			}
		}
		
		if(!$found)
			return fctErrorMSG('Vous ne pouvez pas détacher cette personne.', $errorUrl);
		
		
		
		//Tout est ok, détacher la personne
		$query = 'UPDATE ' . DB_PREFIX . 'perso'
					. ' SET menotte = NULL'
					. ' WHERE id=:persoId'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',			$persoMenotte->getId(),			PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		//Déséquiper l'item pour éviter un double menottage
		$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
					. ' SET inv_equip="0"'
					. ' WHERE inv_id=:itemId'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':itemId',			$persoMenotte->getMenotte(),		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
				
		$msg = 'Les menottes ont été retirées.';
		
		Member_He::add($perso->getId(), $persoMenotte->getId(), 'menotte', $msg);
		
		
		//Rafraichir le HE
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}
