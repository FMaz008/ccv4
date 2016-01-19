<?php
/**
 * Page de traitement de la demande de ré-initialisation du mot de passe
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Visitor
 */
 
class Visitor_Passrecover2
{
	public static function generatePage(&$tpl, &$session, &$account)
	{
		
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		//Valider si le email est passé en paramêtre.
		if(!isset($_POST['email']))
			return fctErrorMSG('Donnés requises manquantes.');
		
		//Trouver le nom d'utilisateur correspondant au email fourni
		$query = 'SELECT user'
					. ' FROM ' . DB_PREFIX . 'account'
					. ' WHERE email=:email'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':email',		$_POST['email'],		PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$content = $prep->fetch();
		
		if ($content === false)
			return fctErrorMSG('Cette adresse ne figure pas dans notre base de données.');
		
		$user = $content[0];
		
		
		//Générer un mot de passe temporaire et former le message du email
		$rnd_pass = fctRandomString(15);
		
		$tpl->set('user', $user);
		$tpl->set('pass', $rnd_pass);
		$tpl->set('hostip', $_SERVER['REMOTE_ADDR']);
		$MSG = $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Visitor/passrecover2_email.htm',__FILE__,__LINE__);
		$ret = @mail(
				$_POST["email"],
				"Cybercity2034.com - Mot de passe",
				$MSG,
					"From: robot@cybercity2034.com\n"
					. "MIME-Version: 1.0\n"
					. "Content-type: text/html; charset=utf-8\n"
				);
		if(!$ret)
			return fctErrorMSG("Erreur lors de l'envoi du email, veuillez contacter l'administrateur sur le forum.<br />Le mot de passe n'a pas été changé.");
		
		
		//Modifier le mot de passe
		$pass = crypt($rnd_pass,strtolower($user));
		$query = 'UPDATE `' . DB_PREFIX . 'account`'
				. ' SET `pass`=:pass'
				. ' WHERE email=:email'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':pass',		$pass,				PDO::PARAM_STR);
		$prep->bindValue(':email',		$_POST['email'],	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Visitor/passrecover2.htm',__FILE__,__LINE__);
		
	}
}

