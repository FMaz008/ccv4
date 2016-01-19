<?php
/**
 * Réception de la réponse du joueur pour sa fouille corporelle
 *
 * @package Member_Action
 */
class Member_Action_Perso_FouillerGo
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_GET['choix']))
			return fctErrorMSG('Paramêtre requis manquant(1).');
			
		if(!isset($_GET['id']))
			return fctErrorMSG('Paramêtre requis manquant(2).');
		
		$persoid = (int)$_GET['id'];
		
		if($persoid===0)
			return fctErrorMSG('Paramêtre requis manquant(3).');
		
		
		//Vérifier que la demande de menottage est bien réelle
		$query = 'SELECT fromid'
					. ' FROM ' . DB_PREFIX . 'perso_fouille'
					. ' WHERE fromid=:fromId'
						. ' AND toid=:toId'
						. ' AND reponse=0'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':fromId',	$persoid,			PDO::PARAM_INT);
		$prep->bindValue(':toId',	$perso->getId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if ($arr===false)
			return fctErrorMSG('Cette demande de fouille est expirée.');
		
		
		
		if($_GET['choix']==1)
		{
			//Accepter d'être fouillé
			
			
			//Sauvegarder la réponse positive
			$query = 'UPDATE ' . DB_PREFIX . 'perso_fouille'
						. ' SET reponse=1'
						. ' WHERE fromid=:fromId'
							. ' AND toid=:toId'
						. ' LIMIT 1;';
			
			$msg = 'La demande de fouille à été acceptée.';
			
		}
		else
		{
			//Refuser d'être fouillé
			
			//Supprimer la demande
			$query = 'DELETE FROM ' . DB_PREFIX . 'perso_fouille'
						. ' WHERE fromid=:fromId'
							. ' AND toid=:toId'
						. ' LIMIT 1;';
			
			$msg = 'La demande de fouille à été refusée.';
			
		}
		$prep = $db->prepare($query);
		$prep->bindValue(':fromId',	$persoid,			PDO::PARAM_INT);
		$prep->bindValue(':toId',	$perso->getId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		
		Member_He::add($perso->getId(), $persoid, 'fouille', $msg);
		
		
		//Rafraichir le HE
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}
