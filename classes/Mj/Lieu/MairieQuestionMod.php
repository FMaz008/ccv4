<?php
/** Gestion de l'interface de gestion de la mairie
* @package Mj
*/

class Mj_Lieu_MairieQuestionMod
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		$lettres = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
		
		
		if(!isset($_POST['questionId']))
			return fctErrorMSG('Vous devez sélectionner une question', '?mj=Lieu_MairieQuestion',null,false);
		if(!is_numeric($_POST['questionId']))
			return fctErrorMSG('Erreur : Id non numérique', '?mj=Lieu_MairieQuestion',null,false);

		//Vérification de l'existence de la question
		$query = 'SELECT *'
					. ' FROM `' . DB_PREFIX . 'mairie_question`'
					. ' WHERE `id`=:id LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',	$_POST['questionId'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		if(empty($arr))
			return fctErrorMSG('Erreur : Question non existante', '?mj=Lieu_MairieQuestion',null,false);
		
		//Sauvegarder l'ajout
		$isError = false;
		if(isset($_POST['save']))
		{
			try
			{
				self::save($lettres);
				die("<script type=\"text/javascript\">location.href='?mj=Lieu_MairieQuestion';</script>");
			}
			catch(GameException $e)
			{
				$isError = true;
				$msgError = $e->getMessage();
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
		
		
		//Remplir le tableau des réponses
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'mairie_question_reponse'
				. ' WHERE `questionId`=:questionId'
				. ' ORDER BY `reponse_tech` ASC;';
		$prep = $db->prepare($query);
		$prep->bindValue(':questionId',	$_POST['questionId'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		foreach($arrAll as &$arr)
		{
			for($i=0; $i<count($lettres); $i++)
			{
				if(strtolower($lettres[$i]) == $arr['reponse_tech'])
				{
					$REPONSES[$i]['reponse'] = stripslashes($arr['reponse']);
					break;
				}
			}
		}
		
		
		//Recherche des informations sur la question
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'mairie_question'
				. ' WHERE `id`=:questionId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':questionId',	$_POST['questionId'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		$QUESTION = array(
						'id' => $arr['id'],
						'reponse_tech' => $arr['reponse_tech'], 
						'question' => $arr['question'], 
						'section' => $arr['section'],
						'reponses' => $REPONSES
					);
		
		$tpl->set('QUESTION', $QUESTION);
		
		
		$tpl->set('SHOWID', true);
		$tpl->set('SUBMITNAME', 'Mod');
		$tpl->set('ACTIONTYPETXT', 'Modifier');

		if($isError)
			return fctErrorMSG('Erreur : ' . $msgError, '?mj=Lieu_MairieQuestion',null,false);
		else
		{
			//Retourner le template complété/rempli
			return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/MairieQuestionAddmod.htm',__FILE__,__LINE__);
		}
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
				}else{
					throw new GameException('La section doit être numérique');
				}
			}else{
				throw new GameException('La section doit être numérique');
			}
		}

		//Valider la question
		if(!isset($_POST['question']))
			throw new GameException('La question est vide.');
		//Valider les réponses
		$noReponse = true;
		for ($i = 0; $i < count($lettres); $i++)
			if (!empty($_POST['reponse_' . $lettres[$i]]))
				$noReponse = false;
		if($noReponse == true)
			throw new GameException ('Il n\'y a aucune réponse à la question.');
		//Valider une réponse valide
		if(!isset($_POST['reponse_tech']))
			throw new GameException('Aucune réponse n\'a été sélectionnée comme réponse valide.');
		
		//Mettre à jour la question
		$query = 'UPDATE ' . DB_PREFIX . 'mairie_question'
				. ' SET `section`=:section,'
					. ' `question`=:question,'
					. ' `reponse_tech`=:reponse'
				. ' WHERE `id`=:questionId;';
		$prep = $db->prepare($query);
		$prep->bindValue(':section',	$_POST['section'],	PDO::PARAM_STR);
		$prep->bindValue(':question',	$_POST['question'],	PDO::PARAM_STR);
		$prep->bindValue(':reponse',	strtolower($_POST['reponse_tech']),	PDO::PARAM_STR);
		$prep->bindValue(':questionId',	$_POST['questionId'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		
		//Créer les réponses
		$query = 'UPDATE ' . DB_PREFIX . 'mairie_question_reponse'
				. ' SET `reponse`=:reponse'
				. ' WHERE	`questionId`=:questionId'
					. ' AND `reponse_tech`=:reponseTech;';
		$prepUpd = $db->prepare($query);
		
		$query = 'DELETE FROM ' . DB_PREFIX . 'mairie_question_reponse'
				. ' WHERE 	`questionId`=:questionId'
					. ' AND `reponse_tech`=:reponseTech;';
		$prepDel = $db->prepare($query);
		
		for($i=0; $i<count($lettres); $i++)
		{
			if(!empty($_POST['reponse_' . $lettres[$i]]))
			{
				$prepUpd->bindValue(':reponse',		$_POST['reponse_' . $lettres[$i]],	PDO::PARAM_STR);
				$prepUpd->bindValue(':reponseTech',	strtolower($lettres[$i]),			PDO::PARAM_STR);
				$prepUpd->bindValue(':questionId',	$_POST['questionId'],	PDO::PARAM_INT);
				$prepUpd->execute($db, __FILE__, __LINE__);
			}
			else
			{
				$prepDel->bindValue(':reponseTech',	strtolower($lettres[$i]),			PDO::PARAM_STR);
				$prepDel->bindValue(':questionId',	$_POST['questionId'],	PDO::PARAM_INT);
				$prepDel->execute($db, __FILE__, __LINE__);
			}
		}
		$prepUpd->closeCursor();
		$prepUpd = NULL;
		$prepDel->closeCursor();
		$prepDel = NULL;
	}
}

