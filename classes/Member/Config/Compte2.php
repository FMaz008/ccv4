<?php
/** Gestion des options du compte
*
* @package Member
*/
class Member_Config_Compte2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso=null)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		$errorUrl = '?m=Config_Compte';
		
		
		
		
		
		//Modification du mot de passe si nécéssaire
		if(isset($_POST['pass1']) && isset($_POST['pass2']) && $_POST['pass1'] != "")
		{
			if ($_POST['pass1'] != $_POST['pass2'])
				return fctErrorMSG('les mots de passes ne correspondent pas', $errorUrl);
				
			$newPass=crypt(addslashes($_POST['pass1']), strtolower(addslashes($account->getUser())));
			$query= 'UPDATE ' . DB_PREFIX . 'account'
					. ' SET pass=:pass'
					. ' WHERE id=:userId'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':userId',		$account->getId(),	PDO::PARAM_INT);
			$prep->bindValue(':pass',		$newPass,			PDO::PARAM_STR);
			$prep->execute($db, __FILE__,__LINE__);
		}
		
		
		
		
		if ($_POST['adv_rem'] > '0')
		{
			$query= 'UPDATE ' . DB_PREFIX . 'account'
					. ' SET remise=remise+:extraTime'
					. ' WHERE id=:userId'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':userId',		$account->getId(),				PDO::PARAM_INT);
			$prep->bindValue(':extraTime',	(int)$_POST['adv_rem']*60*60,	PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
		}
		
		

		//Modifier le HE et le Skin
		$query = 'UPDATE ' . DB_PREFIX . 'account'
				. ' SET skin=:skin,'
					. ' heitems=:heItems'
				. ' WHERE id=:userId'
				. ' LIMIT 1';
		$prep = $db->prepare($query);
		$prep->bindValue(':userId',		$account->getId(),		PDO::PARAM_INT);
		$prep->bindValue(':heItems',	$_POST['heitems'],		PDO::PARAM_INT);
		$prep->bindValue(':skin',		$_POST['skinname'],		PDO::PARAM_STR);
		$prep->execute($db, __FILE__,__LINE__);
		
		
		//Modification du email
		if(!isset($_POST['email']) || empty($_POST['email']))
			return fctErrorMSG('Email manquant.', $errorUrl);
		
		
		preg_match('/^[A-Za-z0-9._-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/', $_POST['email'], $matches);
		if (count($matches)==0)
			return fctErrorMSG('Le format du email est invalide.', $errorUrl);
		
		
		if($account->getEmail() !== $_POST['email'])
		{
			$valCode = fctRandomString(15);
			$tpl->set('BASE_URL',	SITE_VIRTUAL_PATH);
			$tpl->set('USER',		$account->getUser());
			$tpl->set('VAL_CODE',	$valCode);
			$tpl->set('OLD_MAIL',	$account->getEmail());
			$tpl->set('NEW_MAIL',	$_POST['email']);
		
			
			//Modification du email
			$query= 'UPDATE ' . DB_PREFIX . 'account'
					. ' SET email=:email,'
						. ' `code_validation`=:valCode'
					. ' WHERE id=:userId'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':userId',		$account->getId(),	PDO::PARAM_INT);
			$prep->bindValue(':email',		$_POST['email'],	PDO::PARAM_STR);
			$prep->bindValue(':valCode',	$valCode,			PDO::PARAM_STR);
			$prep->execute($db, __FILE__,__LINE__);
			
			
			//Envoi du email
			$MSG = $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Config/email_change.htm',__FILE__,__LINE__);
			$ret= @mail(
						$_POST['email'],
						"Cybercity 2034 - Inscription",
						$MSG,
						"From: robot@cybercity2034.com\n"
						. "MIME-Version: 1.0\n"
						. "Content-type: text/html; charset=utf-8\n"
						);
	
			if(!$ret)
				return fctErrorMSG('Une erreur s\'est produite et le email de validation n\'a pu être envoyé correctement.', $errorUrl);
		
		}
		
		
		//Retourner à la page par défaut
		header('location:?m=News');
		return '';
	}
}

