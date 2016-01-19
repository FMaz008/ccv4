<?php
/** Gestion de l'interface d'un guichet automatique: Afficher le clavier pour composer le NIP
*
* @package Member_Action
*/
class Member_Action_Lieu_Guichet2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl = '?popup=1&amp;m=Action_Lieu_Guichet';
		
		
		//Vérifier l'état du perso
		if(!$perso->isAutonome())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.', $errorUrl);
			
		//Vérifier si une carte à été sélectionnée
		if (!isset($_POST['carteid']) || !is_numeric($_POST['carteid']))
			return fctErrorMSG('Aucune carte sélectionnée.', $errorUrl);
			
		
		$tpl->set('CARD_ID',$_POST['carteid']);
		
		
		//ToDo: Vérifier si le # de carte est valide
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'banque_cartes'
				. ' WHERE carte_id = :carteId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':carteId',		$_POST['carteid'],		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

		
		if ($arr === false)
			return fctErrorMSG('Cette carte à été désactivée.', $errorUrl);
		
		
		
		//Afficher le clavier numérique pour composer le NIP
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Guichet2.htm',__FILE__,__LINE__);
		
	}
}

