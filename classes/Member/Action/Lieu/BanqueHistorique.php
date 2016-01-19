<?php
/** Gestion des relever de compte en banque
*
* @package Member_Action
*/
class Member_Action_Lieu_BanqueHistorique
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
	
	
		//Vérifier les paramêtres requis
		if(!isset($_POST['compte']))
			return fctErrorMSG('Ce compte est invalide (aucun compte).');
		
		
		//Valider le # du compte (TODO: REGEX !!!!)
		if(strlen($_POST['compte'])!=19)
			return fctErrorMSG('Ce compte est invalide (no invalide).');
		
		
		//Séparer le # de banque
		$banque_no = substr($_POST['compte'],0,4);
		$compte_no = substr($_POST['compte'],5,14);
		
		
		//Passer le # de compte au template
		$tpl->set('COMPTE', $_POST['compte']);
		
		
		//Rechercher le compte afin d'y faire des opérations.
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'banque_comptes'
				. ' WHERE	compte_banque=:banque'
					. ' AND compte_compte=:compte'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':compte',		$compte_no,	PDO::PARAM_STR);
		$prep->bindValue(':banque',		$banque_no,	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		//Valider si le compte existe
		if($arr === false)
			return fctErrorMSG('Ce compte n\'existe pas (' . $_POST['compte'] . ')');
		
		//Instancier le compte
		$compte = new Member_BanqueCompte($arr);
				
				
		//Vérifier si le compte appartiend bien au perso
		if ($compte->getIdPerso() != $perso->getId())
			return fctErrorMSG('Ce compte ne vous appartiend pas.');
		
		
		
		
		
		//Lister toutes les transaction
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'banque_historique'
				. ' WHERE compte=:compte'
				. ' ORDER BY date ASC;';
		$prep = $db->prepare($query);
		$prep->bindValue(':compte',		$_POST['compte'],	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arrTrs = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		if (count($arrTrs) == 0)
		{
			$HISTORIQUE = array();
			$i=0;
			foreach($arrTrs as &$arr)
			{
				$HISTORIQUE[$i]['id']		= $arr['id'];
				$HISTORIQUE[$i]['date']		= fctToGameTime($arr['date']); //A quelle heure à été envoyé le message ?
				$HISTORIQUE[$i]['code']		= $arr['code'];
				$HISTORIQUE[$i]['depot']	= ($arr['depot']==0) ? '' : fctCreditFormat($arr['depot']);
				$HISTORIQUE[$i]['retrait']	= ($arr['retrait']==0) ? '' : fctCreditFormat($arr['retrait']);
				$HISTORIQUE[$i]['solde']	= fctCreditFormat($arr['solde']);
				$i++;
			}
			$tpl->set('HISTORIQUE',$HISTORIQUE);
		}
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Banque_historique.htm',__FILE__,__LINE__);
	}
}
