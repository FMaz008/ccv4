<?php
/** Gestion de l'action de consommer un item. Cette page est utilis�e UNIQUEMENT par AJAX. des # d'erreur sont retourn�, pas des message. Aucune interface graphique.
*
* @package Member_Action
*/
class Member_Action_Perso_InventaireConso
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$actionPa = 2;
		
		if (!isset($_POST['id']) || (isset($_POST['id']) && !is_numeric($_POST['id'])))
			die('00|' . rawurlencode('Vous devez s&eacute;lectionner un item pour effectuer cette action.'));
			
		//V�rifier l'�tat du perso
		if(!$perso->isConscient())
			die($_POST['id'] . '|' . rawurlencode('Votre n\'&ecirc;tes pas en �tat d\'effectuer cette action.'));
		
		if($perso->getPa() <= $actionPa)
			die($_POST['id'] . '|' . rawurlencode('Vous n\'avez pas assez de PA pour effectuer cette action.'));
		
		
		
		
		//Trouver en inventaire l'item que l'on souhaite �quiper
		$i=0; $item = null;
		while( $tmp = $perso->getInventaire($i++))
		{
			if($tmp->getInvId() == $_POST['id'])
			{
				$item = $tmp;
				break;
			}
		}
		if(empty($item))
			die($_POST['id'] . '|' . rawurlencode('Cet item ne vous appartiend pas. (cheat)'));
		
		
		
		
		//Si l'item est une nourriture, le supprimer et ajouter les PN
		if ($item instanceof Member_ItemNourriture)
		{
			//Trouver combien de PN le perso pourrait consommer
			$pnNeeded = $perso->getPnMax() - $perso->getPn();
			//echo "pnNeeded:". $pnNeeded ."<br/>"; ///////////////////
			
			if($perso->getPn() >= $perso->getPnMax())
				die($_POST['id'] . '|' . rawurlencode('Vous n\'avez pas faim... vraiment pas faim. Une bouch&eacute; de plus et vous pourriez bien exploser. (et non, ca ne vous permettera pas de faire un attentat kamikaze)'));
			
			
			//Trouver combien de PN on va consommer
			$usePn = $item->getPn() >= $pnNeeded ? $pnNeeded : $item->getPn();
			//echo "usePN:". $usePN ."<br/>"; ///////////////////
			
			if ($item->getPn()-$usePn == 0) //L'item est vide
			{
				if ($item->getQte()==1)
					$query = 'DELETE FROM ' . DB_PREFIX . 'item_inv'
							. ' WHERE inv_id=:itemId'
							. ' LIMIT 1;';
				else
					$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
							. ' SET inv_qte = inv_qte - 1'
							. ' WHERE inv_id=:itemId'
							. ' LIMIT 1;';
				
				//echo $query ."<br/>"; ///////////////////
				
				$prep = $db->prepare($query);
				$prep->bindValue(':itemId',	$_POST['id'],	PDO::PARAM_INT);
				$prep->execute($db, __FILE__, __LINE__);
				$prep->closeCursor();
				$prep = NULL;
			}
			else
			{
				//echo "nb de PN de l'item chang�<br/>"; ///////////////////
				$item->changePn('-', $usePn);
				$item->setPn();
			}
			
			$perso->changePn('+', $usePn);
			$perso->setPn();
			
			
		//Si l'item est une drogue, 
		}
		elseif ($item instanceof Member_ItemDrogueDrogue)
		{
			//Copier une instance de la drogue � consommer.
			$newId = Member_Item::duplicateItem($item->getInvId(), 1, $perso->getId());
			
			//�quiper (consommer) la drogue, et appliquer la dur�e
			$remiseleft = rand($item->getDuree()-1, $item->getDuree()+1); //Dur�e un peu al�atoire
			
			$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
					. ' SET inv_remiseleft =:remiseLeft,'
						. '	inv_equip="1"'
					. ' WHERE	inv_persoid=:persoId'
						. ' AND inv_id=:itemId'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':remiseLeft',		$remiseleft,		PDO::PARAM_INT);
			$prep->bindValue(':persoId',		$perso->getId(),	PDO::PARAM_INT);
			$prep->bindValue(':itemId',			$newId,				PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			$perso->boostPa('+', $item->getBoostPa());
			$perso->boostPv('+', $item->getBoostPv());
			$perso->setPv($perso, 'Consommation drogue');

			//R�duire la quantit� du lot restat, ou le supprimer selon le cas
			if ($item->getQte()==1)
			{
				$query = 'DELETE FROM ' . DB_PREFIX . 'item_inv'
						. ' WHERE	inv_persoid=:persoId'
							. ' AND inv_id=:itemId'
						. ' LIMIT 1;';
			}
			else
			{
				$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
						. ' SET inv_qte = inv_qte -1'
						. ' WHERE	inv_persoid=:persoId'
							. ' AND inv_id=:itemId'
						. ' LIMIT 1;';
			}
			$prep = $db->prepare($query);
			$prep->bindValue(':persoId',		$perso->getId(),	PDO::PARAM_INT);
			$prep->bindValue(':itemId',			$item->getInvId(),	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
		}
		else
		{
			die($_POST['id'] . '|' . rawurlencode('Vous ne pouvez pas consommer ce type d\'item.'));
		}
		
		
		$perso->refreshInventaire(); //Recalculer l'inventaire (les PR)
		$perso->changePa('-', $actionPa);
		$perso->setPa();
		Member_He::add('System', $perso->getId(), 'conso', "Vous consommez votre [i]" . $item->getNom() . '[/i]');
		//Faire la liste de tout les personnages du lieu
		$i=0; $e=0;
		$arrFrom=array();
		while( $tmp = $perso->getLieu()->getPerso($perso, $i++))
			if($tmp->getId() != $perso->getId())
				$arrFrom[$e++] = $tmp->getId();
		Member_He::add($perso->getId(), $arrFrom, 'conso', "Vous voyez une personne consommer : [i]" . $item->getNom() . '[/i]', HE_AUCUN, HE_UNIQUEMENT_MOI);
		
		die($_POST['id'] . '|OK|' . $perso->getPa() . '|' . $perso->getPr() . '|' . $perso->getPn()); //Tout est OK
	}
}

