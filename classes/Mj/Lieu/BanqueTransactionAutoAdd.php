<?php
/** Gestion des transactions automatiques associées à un compte
*
* @package Member_Action
*/
class Mj_Lieu_BanqueTransactionAutoAdd
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		//Valider si le # de compte a été recu
		if(!isset($_POST['compteId']))
			return fctErrorMSG('Id du compte manquant.');
			
		//Valider si le # de la banque a été recu
		if(!isset($_POST['banqueId']))
			return fctErrorMSG('Id de la banque manquant.');
			
		// La Redirection des erreurs pour le panneau MJ ne fonctionne pas ?
		//$ErrorUrl = "?mj=Lieu_BanqueTransactionAuto&id=" . $_POST['compteId'] . "&bid=" . $_POST['banqueId'];
        $ErrorUrl = NULL;
			
		if(isset($_POST['save']))
		{
            try
            {
                return self::save();
            }
            catch(GameException $e)
            {
                return fctErrorMSG($e->getMessage(), $ErrorUrl);
            }
		}
		
		//Passer les # au template
		$tpl->set('BANKID', $_POST['banqueId']);
		
		//Instancier le compte
		try
		{
			$compte = Member_BanqueCompte::getCompteFromId($_POST['compteId']);
		}
		catch(GameException $e)
		{
			return fctErrorMSG($e->getMessage(), $ErrorUrl);
		}
		
		$tpl->set('COMPTE', $compte);
				
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/BanqueTransactionAutoAdd.htm',__FILE__,__LINE__);
	}
	
	public static function save()
	{
		//Instancier le compte
        $compteFrom = Member_BanqueCompte::getCompteFromId($_POST['compteId']);
        $compteTo = Member_BanqueCompte::getCompteFromNoCompte($_POST['compteNoTo']);
		
		//Vérifier les valeurs
		if(!is_numeric($_POST['value']))
            throw new GameException('Le montant doit être numérique.');
		if(	!isset($_POST['date_day']) || empty($_POST['date_day'])
			|| !isset($_POST['date_month']) || empty($_POST['date_month'])
			|| !isset($_POST['date_year']) || empty($_POST['date_year']))
			throw new GameException('La date est incomplète.');
		if(!is_numeric($_POST['date_day']) || !is_numeric($_POST['date_month']) || !is_numeric($_POST['date_year']))
			throw new GameException('La date est erronée.');
		if(!checkdate($_POST['date_month'], $_POST['date_day'], ($_POST['date_year'] - GAMETIME_DECAL)))
			throw new GameException('La date est erronée.');
		
		$date = mktime(0, 0, 0, $_POST['date_month'], $_POST['date_day'], ($_POST['date_year'] - GAMETIME_DECAL));
		
		//Créer la nouvelle transaction
		Member_BanqueTransactionAuto::createNewTransactionAuto($compteFrom->getId(), $compteTo->getId(), $_POST['value'], $_POST['description'], $date);
		
		die("<script type=\"text/javascript\">location.href='?mj=Lieu_BanqueTransactionAuto&id=" . $_POST['compteId'] . "&bid=" . $_POST['banqueId'] . "';</script>");
	}
}
