<?php
/**
 * Page de traitement de l'inscription du compte
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Visitor
 */
 
class Visitor_Inscription2
{
	function generatePage(&$tpl, &$session, &$account)
	{
		
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		//Valider le user
		if(!isset($_POST['user']) || empty($_POST['user']))
			return fctErrorMSG('User manquant.', '?v=Inscription');
		
		if(strlen($_POST['user'])>25 || strlen($_POST['user'])<4)
			return fctErrorMSG('L\'utilisateur n\'a pas une longueur valide.', '?v=Inscription');
		
		preg_match('/^[a-zA-Z0-9_-]+$/', $_POST['user'], $matches);
		if (count($matches)==0)
			return fctErrorMSG('L\'utilisateur n\'est pas constitué de caractères valides uniquement.', '?v=Inscription');
		
		$query = 'SELECT id'
				. ' FROM ' . DB_PREFIX . 'account'
				. ' WHERE user=:user'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':user',	$_POST['user'], PDO::PARAM_STR);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();

		
		if($arr !== false)
			return fctErrorMSG('Ce nom d\'utilisateur est déjà utilisé.');
		
		
		
		//Valider le pass
		if(!isset($_POST['pass']) || empty($_POST['pass']))
			return fctErrorMSG('Pass manquant.', '?v=inscription');
		
		if(strlen($_POST['pass'])>25 || strlen($_POST['pass'])<4)
			return fctErrorMSG('Le mot de passe n\'a pas une longueur valide.', '?v=Inscription');
		
		if($_POST['pass'] == $_POST['user'])
			return fctErrorMSG('Le mot de passe est identique au nom d\'utilisateur.', '?v=Inscription');
		
		
		
		//Valider le Email
		if(!isset($_POST['email']) || empty($_POST['email']))
			return fctErrorMSG('Email manquant.', '?v=Inscription');
		
		
		preg_match('/^[A-Za-z0-9._-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/', $_POST['email'], $matches);
		if (count($matches)==0)
			return fctErrorMSG('Le format du email est invalide.', '?v=Inscription');
		
		
		$query = 'SELECT id'
					. ' FROM ' . DB_PREFIX . 'account'
					. ' WHERE email=:email;';
		$prep = $db->prepare($query);
		$prep->bindValue(':email',	$_POST['email'], PDO::PARAM_STR);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		
		if($arr !== false)
			return fctErrorMSG('Cette adresse email est déjà utilisé.');
		
		
		
		//Valider le sexe
		if(!isset($_POST['sexe']) || empty($_POST['sexe']))
			return fctErrorMSG('Sexe manquant.', '?v=Inscription');
		
		if($_POST['sexe']!='m' && $_POST['sexe']!='f')
			return fctErrorMSG('Sexe invalide.', '?v=Inscription');
		
		
		//Valider année naissance
		if(!isset($_POST['naissance']) || empty($_POST['naissance']))
			return fctErrorMSG('Année de naissance manquante.', '?v=Inscription');
		
		preg_match('/^(19|20)[0-9]{2}$/', $_POST['naissance'], $matches);
		if (count($matches)==0 || $_POST['naissance']>2005)
			return fctErrorMSG('Année de naissance invalide.', '?v=Inscription');
		
		
		
		//Valider les confirmations
		if(!isset($_POST['confirm1']))
			return fctErrorMSG('Vous devez accepter la condition #1.', '?v=Inscription');
		
		if(!isset($_POST['confirm2']))
			return fctErrorMSG('Vous devez accepter la condition #2.', '?v=Inscription');
		
		if(!isset($_POST['confirm3']))
			return fctErrorMSG('Vous devez accepter la condition #4.', '?v=Inscription');
		
		
		
		
		
		
		
		
		
		//Envoyer le email de confirmation
		$valCode = fctRandomString(15);
		$tpl->set('BASE_URL', SITE_VIRTUAL_PATH);
		$tpl->set('VAL_CODE', $valCode);
		$tpl->set('USER', $_POST['user']);
		$tpl->set('PASS', $_POST['pass']);
		
		$MSG = $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Visitor/inscription2_email.htm',__FILE__,__LINE__);
		$ret= @mail(
					$_POST['email'],
					"Cybercity 2034 - Inscription",
					$MSG,
					"From: robot@cybercity2034.com\n"
					. "MIME-Version: 1.0\n"
					. "Content-type: text/html; charset=utf-8\n"
					);
		
		if(!$ret)
			echo fctErrorMSG('Une erreur s\'est produite et le email de validation n\'a pu être envoyé correctement.');
		
		
		
		//Créer le compte
		$passCrypte =	crypt(
							addslashes($_POST['pass']),
							strtolower(addslashes($_POST['user']))
						);
		$query = 'INSERT INTO ' . DB_PREFIX . 'account'
					. ' (`user`,`pass`,`email`,`sexe`,`date_inscr`,`pub`,`pub_detail`,`remise`,`code_validation`,`skin`)'
					. ' VALUES'
					. ' (:user, :pass, :email, :sexe, UNIX_TIMESTAMP(), :pub, :pub_detail, UNIX_TIMESTAMP(), :code_validation, :skin);';
		$prep = $db->prepare($query);
		$prep->bindValue(':user',				$_POST['user'], 			PDO::PARAM_STR);
		$prep->bindValue(':pass',				$passCrypte, 				PDO::PARAM_STR);
		$prep->bindValue(':email',				$_POST['email'], 			PDO::PARAM_STR);
		$prep->bindValue(':sexe',				$_POST['sexe'], 			PDO::PARAM_STR);
		$prep->bindValue(':pub',				$_POST['pub'], 				PDO::PARAM_STR);
		$prep->bindValue(':pub_detail',			$_POST['pub_detail'], 		PDO::PARAM_STR);
		$prep->bindValue(':code_validation',	$valCode, 					PDO::PARAM_STR);
		$prep->bindValue(':skin',				SITE_DEFAULT_SKIN, 			PDO::PARAM_STR);
		$prep->execute($db, __FILE__,__LINE__);
		
		
		
		
		$tpl->set('EMAIL', $_POST['email']);
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Visitor/inscription2.htm',__FILE__,__LINE__);
		
	}
}

