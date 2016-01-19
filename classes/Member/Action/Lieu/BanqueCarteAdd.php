<?php
/** Gestion des cartes associés à un compte
*
* @package Member_Action
*/
class Member_Action_Lieu_BanqueCarteAdd
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		//Valider si le # de compte a été recu
		if(!isset($_POST['compte']))
			return fctErrorMSG('Ce compte est invalide (aucun compte).');
		
		//Passer le # de compte au template
		$tpl->set('COMPTE', $_POST['compte']);
		
		//Instancier le compte
		try
		{
			$compte = Member_BanqueCompte::getCompteFromNoCompte($_POST['compte']);
		}
		catch(Exception $e)
		{
			return fctErrorMSG($e->getMessage());
		}

		//Vérifier si le compte appartiend bien au perso
		if ($compte->getIdPerso() != $perso->getId())
			return fctErrorMSG('Ce compte ne vous appartiend pas.');
				
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Banque_carte_add.htm',__FILE__,__LINE__);
	}
}
