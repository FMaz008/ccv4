<?php
/** Gestion de l'interface de l'action Téléphoner depuis une cabine téléphonique
*
* @package Member_Action
*/
class Member_Action_Lieu_TelephonerCabine
{ 
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl = '?popup=1&amp;m=Action_Lieu_TelephonerCabine';
		
		
		//Vérifier l'état du perso
		if(!$perso->isConscient())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.');
		
		
		if(isset($_POST['numero_destinataire'])) //Vérifier si le formulaire à été envoyé
		{
			
			//Valider si un # de téléphone à été fournis
			if(empty($_POST['numero_destinataire']))
				return fctErrorMSG('Aucun numéro de téléphone n\'a été entré.',$errorUrl);
			
			//Valider si un message à été rempli
			if(empty($_POST['message']))
				return fctErrorMSG('Aucun message n\'a été rédigé.', $errorUrl);
			
			
			self::cabineTelephoner($perso, $_POST['numero_destinataire'], $_POST['message']);

			//Rafraichir le HE
			return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
		}
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/TelephonerCabine.htm',__FILE__,__LINE__);
	}
	
	
	/**Permet de téléphoner depuis une cabine téléphonique
	* @param object $perso Objet du perso utilisant le tel
	* @param char $numero_destinataire Numéro du destinataire
	* @param char $message Message à envoyer
	*/
	public static function cabineTelephoner(&$perso, &$numero_destinataire, &$message)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl = '?popup=1&amp;m=Action_Lieu_TelephonerCabine';
		
		
		//Récup des info du téléphone de l'appelé
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'item_inv'
				. ' LEFT JOIN ' . DB_PREFIX . 'item_db ON (db_id = inv_dbid)'
				. ' WHERE db_type="telephone"'
					. ' AND inv_notel=:noTel'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':noTel',			$numero_destinataire,		PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		$tel_distant = new Member_ItemTelephone ($arr);
		
		//Si le téléphone n'existe pas
		if(empty($tel_distant))
			return fctErrorMSG('Numéro pas attribué.', $errorUrl);

		$affichage_dest = $tel_distant->getTypeAffichage();
		$lieu_perso = $perso->getLieu()->getNom();
		
		//Entête selon le type d'affichage que possède l'utilisateur
		if($affichage_dest == 2)
		{
			$entete_mess_dest = 'Appel provenant d\'une cabine téléphonique (' . $lieu_perso . '). ';
		}
		else
		{
			$entete_mess_dest = 'Votre téléphone ne possède pas d\'afficheur. ';
			
		}
		$entete_mess_dest.= 'Appel reçu sur le téléphone #' . $numero_destinataire;			
		
		
		$id_destinataire = $tel_distant->getIdProprio();
		$id_expediteur = $perso->getId();
		$cout_appel = 2;
		$cash_exp = $perso->getCash();
		
		
		//Valider si le personnage à assez d'argent en poche pour téléphoner de la cabine.
		if(($cash_exp < $cout_appel))
			return fctErrorMSG('Vous n\'avez pas assez d\'argent pour passer cet appel.', $errorUrl);	
		
		
		//Valider si le téléphone est possédé par quelqu'un
		if($id_destinataire === NULL)
			return fctErrorMSG('Ca sonne occupé. Réessayer plus tard.', $errorUrl);
		
		
		
		//Retrait du cash du portefeuille du gars
		$perso->changeCash('-', $cout_appel);
		$perso->setCash();
		
		
		
		//Mise de l'argent sur le compte commun
		$query = 'SELECT *'
					. ' FROM ' . DB_PREFIX . 'banque_comptes'
					. ' WHERE compte_id=1'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if ($arr === false)
			return fctErrorMSG('Le fond commun n\'existe plus. Contacter un MJ.', $errorUrl);

		$compteFC = new Member_BanqueCompte($arr);
		$compteFC->changeCash('+', $cout_appel);
		$compteFC->setCash();
		
		
		//Inscription de l'opération au fond commun
		$compteFC->add_bq_hist('', 'TELC', 0, $cout_appel);
		
		
		//Mise du message dans les HE des personnes impliquées
		$id_mess_he_dest = Member_He::add("Telephone", $id_destinataire, 'Telephone', $entete_mess_dest. "[sep]" . $message);
		$id_mess_he_exp = Member_He::add("Telephone", $id_expediteur, "Telephone", $message);		
		
		//Log MJ
		$query = 'INSERT INTO ' . DB_PREFIX . 'log_telephone'
				. ' (`id_he_exp`, `id_he_dest`, `date`, `from_tel`, `from_persoid`, `to_tel`, `to_persoid`)'
				. ' VALUES('
					. ' :fromMsgId,'
					. ' :toMsgId,'
					. ' UNIX_TIMESTAMP(),'
					. ' :lieu,'
					. ' :fromPersoId,'
					. ' :toNoTel,'
					. ' :toPersoId'
				. ' );';
		$prep = $db->prepare($query);
		$prep->bindValue(':fromMsgId',			$id_mess_he_exp,			PDO::PARAM_INT);
		$prep->bindValue(':toMsgId',			$id_mess_he_dest,			PDO::PARAM_INT);
		$prep->bindValue(':lieu',				$lieu_perso . '(Cabine)',	PDO::PARAM_STR);
		$prep->bindValue(':fromPersoId',		$id_expediteur,				PDO::PARAM_INT);
		$prep->bindValue(':toNoTel',			$numero_destinataire,		PDO::PARAM_STR);
		$prep->bindValue(':toPersoId',			$id_destinataire,			PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
	}
}
