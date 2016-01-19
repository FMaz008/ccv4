<?php
/** Suppression des cartes associés à un compte
*
* @package Member_Action
*/
class Member_Action_Lieu_BanqueCarteDel
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		//Valider si un # de compte à été recu
		if(!isset($_POST['compte']))
			return fctErrorMSG('Ce compte est invalide (aucun compte).');
		
		//Valider si un numéro de carte à été recu
		if(!isset($_POST['cid']))
			return fctErrorMSG('Cete carte est invalide (aucune carte).');
		
		//Passer le # du compte au template
		$tpl->set('COMPTE', $_POST['compte']);
		
		//Instancier le compte
		try
		{
			$compte = Member_BanqueCompte::getCompteFromNoCompte($_POST['compte']);
		}
		catch(GameException $e)
		{
			return fctErrorMSG($e->getMessage());
		}	
						
		//Vérifier si le compte appartiend bien au perso
		if ($compte->getIdPerso() != $perso->getId())
			return fctErrorMSG('Ce compte ne vous appartiend pas.');
		
		//supprimer la carte
		$compte->delAccesCard($_POST['cid']);
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Banque_carte_redirect.htm',__FILE__,__LINE__);
	}
}

