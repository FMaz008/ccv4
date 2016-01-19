<?php
/** Gestion de l'interface de gestion de la mairie
*
* @package Mj
*/

class Mj_Lieu_MairieQuestionAdd
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante

		$lettres = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');

		//Sauvegarder l'ajout
		if(isset($_POST['save']))
		{
			try
			{
				self::save($lettres);
				die("<script type=\"text/javascript\">location.href='?mj=Lieu_MairieQuestion';</script>");
			}
			catch(GameException $e)
			{
				$tpl->set('SHOWID', false);
				$tpl->set('SUBMITNAME', 'Add');
				$tpl->set('ACTIONTYPETXT', 'Ajouter');
				return fctErrorMSG('Erreur : ' . $e->getMessage(), '?mj=Lieu_MairieQuestionAdd',null,false);
			}
		}
		
		//Liste des sections
		$query = 'SELECT DISTINCT section'
				. ' FROM ' . DB_PREFIX . 'mairie_question;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$SECTIONS = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		$tpl->set('SECTIONS', $SECTIONS);
		
		
		
		
		//Générer le tableau des réponses
		$REPONSES = array();
		for($i=0; $i<count($lettres); $i++)
			$REPONSES[$i] = array(
								'reponse' => '',
								'reponse_tech' => $lettres[$i],
							);
		
		
		//Champs par défaut pour une question
		$QUESTION = array(
						'reponse_tech' => 'A', 
						'question' => ' ?', 
						'section' => '',
						'reponses' => $REPONSES
					);
		
		$tpl->set('QUESTION', $QUESTION);
		
		
		$tpl->set('SHOWID', false);
		$tpl->set('SUBMITNAME', 'Add');
		$tpl->set('ACTIONTYPETXT', 'Ajouter');
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/MairieQuestionAddmod.htm',__FILE__,__LINE__);
	}
	
	
	
	private static function save($lettres)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Valider la section
		if(!is_numeric($_POST['section'])){
			if($_POST['section']=='new'){
				if(is_numeric($_POST['new_section'])){
					$_POST['section'] = $_POST['new_section'];
				}
				else
				{
					throw new GameException('La section doit être numérique');
				}
			}
			else
			{
				throw new GameException('La section doit être numérique');
			}
		}

		// Valider la question
		if(empty($_POST['question']))
			throw new GameException('La question est vide.');

		// Valider l'existance des réponses
		$reponsesExist = false;
		for($i = 0; $i < count($lettres); $i++)
			if(!empty($_POST['reponse_' . $lettres[$i]]))
				$reponsesExist = true;
		if(!$reponsesExist)
			throw new GameException ('Il faut au moins avoir une réponse.');

		// Valider qu'une réponse a été choisie comme bonne
		if(!isset($_POST['reponse_tech']))
			throw new GameException ('Aucune réponse n\'a été choisie comme réponse valide.');
		
		//Créer la question
		$query = 'INSERT INTO ' . DB_PREFIX . 'mairie_question'
				. ' (`section`, `question`, `reponse_tech`)'
				. ' VALUES'
				. ' (:section, :question, :reponseTech);';
		$prep = $db->prepare($query);
		$prep->bindValue(':section',		$_POST['section'],	PDO::PARAM_STR);
		$prep->bindValue(':question',		$_POST['question'],	PDO::PARAM_STR);
		$prep->bindValue(':reponseTech', strtolower($_POST['reponse_tech']),	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		
		//Récupérer l'ID de la question ajoutée
		$lastid = $db->lastInsertId();
		
		
		//Créer les réponses
		$query = 'INSERT INTO ' . DB_PREFIX . 'mairie_question_reponse'
				. ' (`questionId`, `reponse_tech`, `reponse`)'
				. ' VALUES'
				. ' (:questionId, :reponseTech, :reponse);';
		$prepIns = $db->prepare($query);
		
		for($i=0; $i<count($lettres); $i++)
		{
			if(!empty($_POST['reponse_' . $lettres[$i]]))
			{
				
				$prepIns->bindValue(':questionId',		$lastid,		PDO::PARAM_INT);
				$prepIns->bindValue(':reponseTech', strtolower($lettres[$i]),	PDO::PARAM_STR);
				$prepIns->bindValue(':reponse',	$_POST['reponse_' . $lettres[$i]],	PDO::PARAM_STR);
				$prepIns->execute($db, __FILE__, __LINE__);
			}
		}
		$prepIns->closeCursor();
		$prepIns = NULL;
	}
}

