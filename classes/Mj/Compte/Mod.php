<?php
/** Gestion de l'interface des déplacement
*
* @package Mj
*/
class Mj_Compte_Mod
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
	
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if (isset($_POST['save'])) //verif qu'il y a eu une modif de faite
		{
			try
			{
				self::save($mj);
			}
			catch(Exception $e)
			{
				return fctErrorMSG($e->getMessage());
			}
			header('location:?mj=index');
		}


		//query pour avoir les infos de la personne
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'account'
				. ' WHERE id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',	$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$ACC = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

		
		//envoie des infos à l'autre page (tpl) + developpement de la requête + transformation des timestamp
		if ($ACC !== false)
		{
			$ACC['date_inscr'] = date('d/m/Y H \h i', $ACC['date_inscr'] ); //transformation du timstamp
			$ACC['last_conn'] = date('d/m/Y H \h i', $ACC['last_conn'] ); //idem

			$tpl->set('ACC',$ACC); //envoie des infos général

			
			
			
			if ($ACC['mp'] != '0')
			{
				//transformation du temps  pour l'expiration du membre plus
				$date_mp = array(
								'jour' =>  date('d', $ACC['mp_expiration'] ),
								'mois' => date('m', $ACC['mp_expiration'] ),
								'annee' => date('Y', $ACC['mp_expiration'] )
							);
				
			}
			else
			{
				$date_mp = array(
								'jour' =>  date('d'),
								'mois' => date('m'),
								'annee' => date('Y')
							);
			}

			//envoie de l'array
			$tpl->set('date_mp',$date_mp);
				
			//transformation du temps  pour l'heure de remise
			$remise_initiale = array(
									'annee' => date('Y', $ACC['remise']),
									'mois' => date('m', $ACC['remise']),
									'jour' => date('d', $ACC['remise']),
									'heure' => date('H', $ACC['remise'] ), 
									'minute' => date('i', $ACC['remise'] )
								);
			//envoie de l'array
			$tpl->set('remise_initiale',$remise_initiale);
		}
		
		//affichage des persos du compte :
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'perso'
				. ' WHERE userId=:userId;';
		$prep = $db->prepare($query);
		$prep->bindValue(':userId',	$ACC['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$info_pj = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		foreach($info_pj as &$arr)
		{
			$arr['cash'] = fctCreditFormat($arr['cash'],true);
		}
			
		//envoie des infos à l'autre page (tpl)
		$tpl->set('PERSOS',$info_pj);
		$tpl->set('ACCESS_ADMIN', $mj->accessAdmin());
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Compte/Modifier.htm');
	}
	
	
	
	
	
	
	private static function save(&$mj)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		// Vérification M+
		if ($mj->accessAdmin()) //Le privilège MJ Admin est requis pour modifier les M+
		{
			//si le membre plus a été supprimé le temps est mis à 0
			if(isset($_POST['mp']) && $_POST['mp'] == '0')
			{
				$mp_expiration = '0';
			
			//Vérifier si la mise à jour est due.
			}
			elseif(isset($_POST['mp_mois']) && is_numeric($_POST['mp_mois'])
				&& isset($_POST['mp_jour']) && is_numeric($_POST['mp_jour'])
				&& isset($_POST['mp_annee']) && is_numeric($_POST['mp_annee']))
			{
					$mois = $_POST['mp_mois'];
					$jour = $_POST['mp_jour'];	
					$annee = $_POST['mp_annee'];	
					$mp_expiration =  mktime(0, 0, 0, $mois, $jour, $annee);
			}
			else //Sinon, erreur
			{
				throw new Exception('Les champs d\'expiration M+ doivent être en chiffre uniquement.');
				return;
			}
		}
	
	
		//Heure de la Remise
		if (   !isset($_POST['rem_heure']) || !is_numeric($_POST['rem_heure'])
			|| !isset($_POST['rem_minute']) || !is_numeric($_POST['rem_minute']))
		{
			throw new Exception('Les champs de remise doivent être en chiffre uniquement.');
			return;
		}
		
		
		$heure = $_POST['rem_heure'];
		$minute = $_POST['rem_minute'];
		$remise =  mktime($heure, $minute, 0, date('m'), date('d'), date('Y'));
			
		
		//query pour la modification :
		$query = 'UPDATE ' . DB_PREFIX . 'account'
				. ' SET'
				//	. ' user=:user,'
					. ' email=:email,'
					. ' sexe=:sexe,'
					. ' bloque=:bloque,'
					. ' remise=:remise,';
		
		if($mj->accessAdmin())
		{
			$query .=	'mp=:mp,'
					. ' mp_expiration=:mp_expiration,';
		}
		
		$query .=		'auth_doublons=:auth_doublons,'
					. ' auth_creation_perso=:auth_creation_perso'
				. ' WHERE id=:id;';
		
		$prep = $db->prepare($query);
		$prep->bindValue(':id',						$_GET['id'],		PDO::PARAM_INT);
		//$prep->bindValue(':user',					$_POST['user'],		PDO::PARAM_STR);
		$prep->bindValue(':email',					$_POST['email'],	PDO::PARAM_STR);
		$prep->bindValue(':sexe',					$_POST['sexe'],		PDO::PARAM_STR);
		$prep->bindValue(':bloque',					isset($_POST['bloquer']) ? '1' : '0',	PDO::PARAM_STR);
		$prep->bindValue(':remise',					$remise,	PDO::PARAM_INT);

		if($mj->accessAdmin())
		{
			$prep->bindValue(':mp',						$_POST['mp'],	PDO::PARAM_STR);
			$prep->bindValue(':mp_expiration',			$mp_expiration,	PDO::PARAM_INT);
		}
		
		$prep->bindValue(':auth_doublons',			isset($_POST['doublons']) ? '1' : '0',	PDO::PARAM_STR);
		$prep->bindValue(':auth_creation_perso',	$_POST['creation_perso'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
	}
}
