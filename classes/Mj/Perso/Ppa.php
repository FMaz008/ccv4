<?php
/** Gestion de l'interface de gestion d'un PPA
*
* @package Mj
*/
class Mj_Perso_Ppa
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_GET['id']))
			return fctErrorMSG('Données requises manquantes.');
		
		
		//Trouver les PPA associé au MJ
		$query = 'SELECT m.*, p.nom as perso'
				. ' FROM ' . DB_PREFIX . 'ppa as m'
				. ' LEFT JOIN ' . DB_PREFIX . 'perso as p ON (p.id = m.persoid) '
				. ' WHERE	m.id=:id'
				. ' LIMIT 1';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',	$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr === false)
			return fctErrorMSG('PPA innexistant ou attribué à un autre MJ.');
		
		
		$PPA = array();
		$i=0;
		
		//Afficher le message de base (Le PPA)
		$PPA[$i] = $arr;
		$PPA[$i]['msg'] = BBCodes(stripslashes($PPA[$i]['msg']));
		$PPA[$i]['notemj'] = stripslashes($PPA[$i]['notemj']);
		$PPA[$i]['datetxt'] = fctToGameTime($arr['date']);
		$i++;
		
		//			((			Non-Attribué, ou soi-même					)  &&	Ouvert(non-fermé))
		$CanReply = (($PPA[0]['mjid'] == 0 || $PPA[0]['mjid'] == $mj->getId()) && $PPA[0]['statut'] == 'ouvert');
		
		
		
		

		
		
		//Sauvegarder le commentaire de base du Ppa
		if(isset($_POST['save_comment']))
		{
			$query = 'UPDATE ' . DB_PREFIX . 'ppa'
					. ' SET notemj=:noteMj'
					. ' WHERE id =:id;';
			$prep = $db->prepare($query);
			$prep->bindValue(':noteMj',		htmlspecialchars($_POST['notemjbase']),	PDO::PARAM_STR);
			$prep->bindValue(':id',			$_GET['id'],	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			echo "<script type=\"text/javascript\">location.href='?mj=Perso_Ppa&id=" . $_GET['id'] . "';</script>";
			die();
		}
		
		//Sauvegarder l'attribution du PPA
		if (isset($_POST['save_saisi']))
		{
			$query = 'UPDATE ' . DB_PREFIX . 'ppa'
					. ' SET mjid=:mjId'
					. ' WHERE id =:id;';
			$prep = $db->prepare($query);
			$prep->bindValue(':mjId',		$_POST['mj'],	PDO::PARAM_INT);
			$prep->bindValue(':id',			$_GET['id'],	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;

			echo "<script type=\"text/javascript\">location.href='?mj=Perso_Ppa&id=" . $_GET['id'] . "';</script>";
			die();
		}
		
		//Ajout d'une réponse ?
		if (isset($_POST['reply']) && $CanReply)
		{
			$query = 'INSERT INTO ' . DB_PREFIX . 'ppa_reponses'
					. ' (`sujetid`, `mjid`, `date`, `msg`, `notemj`)'
					. ' VALUES'
					. ' (:sujetId, :mjId, UNIX_TIMESTAMP(), :msg, :noteMj);';
			$prep = $db->prepare($query);
			$prep->bindValue(':sujetId',	$_GET['id'],						PDO::PARAM_INT);
			$prep->bindValue(':mjId',		$mj->getId(),						PDO::PARAM_INT);
			$prep->bindValue(':msg',		htmlspecialchars($_POST['msg']),	PDO::PARAM_STR);
			$prep->bindValue(':noteMj',		htmlspecialchars($_POST['notemj']),	PDO::PARAM_STR);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;

			
			
			Member_He::add($mj->getNom(), $PPA[0]['persoid'], 'ppa', $_POST['msg']);
		}
		
		
		
		
		
		
		
		
		//Afficher les réponses
		$query = 'SELECT p.*, mj.nom as mjnom'
				. ' FROM ' . DB_PREFIX . 'ppa_reponses as p'
				. ' LEFT JOIN ' . DB_PREFIX . 'mj as mj ON (mj.id = p.mjid)'
				. ' WHERE p.sujetid=:id'
				. ' ORDER BY p.`date` ASC;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',			$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		if(count($arrAll)>0)
		{
			foreach($arrAll as &$arr)
			{
				$PPA[$i] = $arr;
				$PPA[$i]['msg'] = BBCodes(stripslashes($PPA[$i]['msg']));
				if(empty($PPA[$i]['notemj']))
					$PPA[$i]['notemj'] = "<i>- aucune note -</i>";
				else
					$PPA[$i]['notemj'] = BBCodes(stripslashes($PPA[$i]['notemj']));
				$PPA[$i]['datetxt'] = fctToGameTime($arr['date']);
				
				if(empty($arr['mjnom']))
					if($arr['mjid'] == 0)
						$PPA[$i]['mjnom'] = $PPA[0]['perso'];
					else
						$PPA[$i]['mjnom'] = 'MJ Inconnu';
				else
					$PPA[$i]['mjnom'] = stripslashes($arr['mjnom']);
				
				$i++;
			}
		}
		
		//Si PPA attribué, vérifier s'il traine (en attente) depuis plus de 3 jours
		//if($CanReply)
		//{
			$canClose = false;
			if($CanReply && $PPA[$i-1]['date'] <= mktime(0,0,0,date('m'),date('d')-3,date('Y')))
			{
				//$canReply = true;
				$canClose = true;
			}

			//Fermer le PPA si le MJ est autorisé à y répondre
			if($canClose && isset($_POST['close']))
			{
				$query = 'UPDATE ' . DB_PREFIX . 'ppa'
						. ' SET statut="ferme"'
						. ' WHERE	id=:sujetId'
						. ' LIMIT 1;';
				$prep = $db->prepare($query);
				$prep->bindValue(':sujetId',	$_GET['id'],	PDO::PARAM_INT);
				$prep->execute($db, __FILE__,__LINE__);
				$prep->closeCursor();
				$prep = NULL;

				header('location:?mj=Perso_Ppa&id=' . (int)$_GET['id']);
				die();
			}
		//}
		
		
		$query = 'SELECT id, nom'
				. ' FROM ' . DB_PREFIX . 'mj;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$MJ = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		$tpl->set("MJID", $mj->getId());
		$tpl->set("MJ", $MJ);
		$tpl->set("PPA",$PPA);
		$tpl->set("CAN_REPLY", $CanReply);
		$tpl->set("CAN_CLOSE", $canClose);
		
		
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . '/html/Mj/Perso/ppa.htm');
	}
}

