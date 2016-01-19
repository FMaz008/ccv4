<?php
/**
 * Traitement de la connexion au jeu.
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Visitor
 */
 
class Visitor_Login2
{
	public static function generatePage(&$tpl, &$session, &$account)
	{
		
		if(!isset($_POST['u']) || !isset($_POST['p']))
			return fctErrorMSG('Données requises manquantes.');
		

		if(isset($_POST['persoId']))
			$errorNo = self::tryLogin($session, $account, $_POST['u'], $_POST['p'], $_POST['persoId']);
		else
			$errorNo = self::tryLogin($session, $account, $_POST['u'], $_POST['p']);
		
		if(isset($_GET['popup']))
		{
			echo $errorNo;
			die();
		}

		
		//Échec de l'identification
		if ($errorNo!=0)
		{
			$tpl->set('ERRORNO',$errorNo);
			return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Visitor/login_wrong.htm',__FILE__,__LINE__);
		}
		
		//Authentification réussie
		Remise::doRemise($account, 10);
		
		return true;
	}
	
	
	
	private static function tryLogin(&$session, &$account, $user,$pass, $setPerso=null)
	{	//BUT: Faire une tentative de login
		
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		if(empty($user))
			return 2; // 2: Aucun utilisateur existant
			
		$query = 'SELECT id, pass, bloque, code_validation, log_login'
				. ' FROM ' . DB_PREFIX . 'account'
				. ' WHERE user=:user'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':user',	$user,		PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if ($arr === false)
			return 4;	// 4: Normalement impossible
		elseif (stripslashes($arr['pass']) != crypt(addslashes($pass),strtolower(addslashes($user))))
			return 5;	// 5: Mot de passe incorrecte
		elseif ($arr['bloque'] == '1')
			return 6;	// 6: Compte bloquee
		elseif ($arr['code_validation'] != NULL)
		{
			//Associer le compte à la session
			$session->setVar('userId', $arr['id']);
			return 7;	// 7: Email non-validé
		}
		else
		{
			//Login OK.
			
			//Associer le compte à la session
			$session->setVar('userId', $arr['id']); 
			$session->setVar('logged', true); 

			$queryAddon = '';
			if(defined('ENGINE_ARR_BANNED_IP'))
			{
				$arrBannedIp = explode(',', ENGINE_ARR_BANNED_IP);
				if(in_array($_SERVER['REMOTE_ADDR'], $arrBannedIp))
					$queryAddon = ', `bloque`="1"';
			}

			
			//Mettre à jour la derniere activité
			
			$query = 'UPDATE `' . DB_PREFIX . 'account`'
						. ' SET `last_conn`="' . CURRENT_TIME . '"'
							. $queryAddon
						. ' WHERE `id`=:id'
						. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':id',		(int)$session->getVar('userId'),		PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
		
			//Enregistrer le login
			$maxLogPerUser = 5;
			if($arr['log_login']=='1')
			{
				// Checker le nombre de log
				$query = 'SELECT *'
						. ' FROM `' . DB_PREFIX . 'log_conn`'
						. ' WHERE `user` = :user'
						. ' ORDER BY `timestamp` ASC;';
				$prep = $db->prepare($query);
				$prep->bindValue(':user', $user, PDO::PARAM_STR);
				$prep->execute($db, __FILE__, __LINE__);
				$results = $prep->fetchAll();
				$prep->closeCursor();
				$prep = NULL;

				// Si nombre max atteind -> supprimer la plus vieille entrée
				if (count($results) >= $maxLogPerUser)
				{
					$query = 'DELETE FROM `' . DB_PREFIX . 'log_conn`'
							 . ' WHERE `id` = :id LIMIT 1;';
					$prep = $db->prepare($query);
					$prep->bindValue(':id', $results[0]['id'], PDO::PARAM_INT);
					$prep->execute($db, __FILE__, __LINE__);
				}

				// Ajouter la nouvelle connexion
				$cookie = isset($_COOKIE['sessId']) ? $_COOKIE['sessId'] : '';			
				$query = 'INSERT INTO `' . DB_PREFIX . 'log_conn`'
					. ' (`id`, `date`, `timestamp`, `user`, `ip`, `host`, `cookie`, `client`)'
					. ' VALUES '
					. ' (NULL,  NOW(),  :timestamp,  :user,  :ip,  :host,  :cookie,  :client);';
				$prep = $db->prepare($query);
				$prep->bindValue(':timestamp',	CURRENT_TIME,							PDO::PARAM_INT);
				$prep->bindValue(':user',		$user,									PDO::PARAM_STR);
				$prep->bindValue(':ip',			$_SERVER['REMOTE_ADDR'],				PDO::PARAM_STR);
				$prep->bindValue(':host',		gethostbyaddr($_SERVER['REMOTE_ADDR']),	PDO::PARAM_STR);
				$prep->bindValue(':cookie',		$cookie,								PDO::PARAM_STR);
				$prep->bindValue(':client',		$_SERVER['HTTP_USER_AGENT'],			PDO::PARAM_STR);
				$prep->execute($db, __FILE__, __LINE__);
				$prep->closeCursor();
				$prep = NULL;
			}
			
			//Sauvegarder la liste des personnages du compte
			$query = 'SELECT id, nom'
						. ' FROM ' . DB_PREFIX . 'perso'
						. ' WHERE userId=:userId;';
			$prep = $db->prepare($query);
			$prep->bindValue(':userId',		$session->getVar('userId'),		PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$arrPerso = $prep->fetchAll();
			$prep->closeCursor();
			$prep = NULL;
			
			$session->setVar('persoList', $arrPerso);

			if(!empty($setPerso))
			{
				foreach($arrPerso as &$arr)
					if($arr['id'] == (int)$setPerso)
						$session->setVar('persoId', (int)$setPerso);
			}
			
			return 0; //Aucune erreur;
		}
	}
	
	
}

