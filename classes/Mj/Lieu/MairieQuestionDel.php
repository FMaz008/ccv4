<?php
/** Gestion de l'interface de gestion des banques
*
* @package Mj
*/

class Mj_Lieu_MairieQuestionDel
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_POST['questionId']))
			return fctErrorMSG('Vous devez sélectionner une question', '?mj=Lieu_MairieQuestion');
		
		
		
		
		//Supprimer les réponses
		$query = 'DELETE FROM ' . DB_PREFIX . 'mairie_question_reponse'
				. ' WHERE `questionId`=:questionId;';
		$prep = $db->prepare($query);
		$prep->bindValue(':questionId',	$_POST['questionId'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		
		//Supprimer la question
		$query = 'DELETE FROM ' . DB_PREFIX . 'mairie_question'
				. ' WHERE `id`=:questionId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':questionId',	$_POST['questionId'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		
		//Redirection
		die("<script type=\"text/javascript\">location.href='?mj=Lieu_MairieQuestion';</script>");
	}
}

