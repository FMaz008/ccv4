<?php
/** Gestion de l'interface de gestion des questions de la mairie
*
* @package Mj
*/

class Mj_Lieu_MairieQuestion
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		//Liste des sections
		$SECTIONS = array();
		$query = 'SELECT DISTINCT section'
				. ' FROM ' . DB_PREFIX . 'mairie_question;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		foreach($arrAll as &$arr)
			$SECTIONS[$arr['section']] = array('section' => $arr['section'], 'question' => null);
		
		
		//Liste des question et de leur réponse
		$query = 'SELECT q.id, q.question, q.section, r.reponse'
				. ' FROM ' . DB_PREFIX . 'mairie_question as q'
				. ' LEFT JOIN ' . DB_PREFIX . 'mairie_question_reponse as r'
					. ' ON (r.questionId=q.id AND r.reponse_tech=q.reponse_tech);';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		
		
		foreach($arrAll as &$arr)
			$SECTIONS[$arr['section']]['questions'][] = $arr;
		
		
		$tpl->set('SECTIONS', $SECTIONS);
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/MairieQuestion.htm',__FILE__,__LINE__);
	}
}
