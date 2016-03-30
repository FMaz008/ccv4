<?php
/** Gestion de l'interface d'un guichet automatique: Sélectionner une carte
*
* @package Member_Action
*/
class Member_Action_Lieu_MairieVisaVertExam
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl = '?popup=1&amp;m=Action_Lieu_MairieVisaVert';
		$questionsParSection = 7;
		$coutCash = 0;
		$coutPa = 33;

		//Vérifier si le perso possède déjà son visa vert
		if($perso->getVisaPerm()=="1")
			return fctErrorMSG('Vous avez déjà votre Visa Vert.', $errorUrl);
		
		//Vérifier l'état du perso
		if(!$perso->isNormal())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.', $errorUrl);
		
		//Vérifier si une demande d'examen à été recue
		if(!isset($_POST['Exam']))
			return fctErrorMSG('Merci de passer par le formulaire.', $errorUrl);
		
		
		//Vérifier si le perso a suffisamment d'argent sur lui
		if($perso->getCash() < $coutCash)
			return fctErrorMSG('Vous n\'avez pas assez d\'argent pour effectuer cette action.', $errorUrl);
		
		
		//Valider si le perso a suffisamment de PA
		if($perso->getPa() <= $coutPa)
			return fctErrorMSG('Vous n\'avez pas assez de PA pour effectuer cette action.', $errorUrl);
		
		
		//Faire payer le droit de passage de l'examen
		$perso->changeCash('-', $coutCash);
		$perso->changePa('-', $coutPa);
		$perso->setCash();
		$perso->setPa();
		
		
		//Setter le tag de l'examen (pour éviter de refaire la même copie après la correction)
		$tpl->set('NO_EXAM', rand(1000000000,9999999999));
		
		
		
		//Fetcher les sections
		$query = 'SELECT DISTINCT `section`'
				. ' FROM `' . DB_PREFIX . 'mairie_question`;';
		$prepSection = $db->prepare($query);
		$prepSection->executePlus($db, __FILE__, __LINE__);
		$arrSectionId = $prepSection->fetchAll();
		$prepSection->closeCursor();
		$prepSection = NULL;
		
		if(count($arrSectionId) == 0)
			return fctErrorMSG('Aucune question dans la base de données.', $errorUrl);

		
		$query = 'SELECT `id`'
					. ' FROM `' . DB_PREFIX . 'mairie_question`'
					. ' WHERE `section`=:sectionId'
					. ' ORDER BY RAND()'
					. ' LIMIT :nbrQuestion;';
		$prepQuestion = $db->prepare($query);

		$s = -1;
		$SECTIONS = array();
		foreach($arrSectionId as &$arr)
		{
			$s++;
			$SECTIONS[] = array(
								'section' => $arr['section'],
								'questions' => array()
							);
			
			//Déterminer les questions à poser
			
			$prepQuestion->bindValue(':sectionId',		$arr['section'],		PDO::PARAM_INT);
			$prepQuestion->bindValue(':nbrQuestion',	$questionsParSection,	PDO::PARAM_INT);
			$prepQuestion->executePlus($db, __FILE__, __LINE__);
			$arrQuestionId = $prepQuestion->fetchAll();

			//s'assurer d'avoir un tableau unidimentionnel
			foreach($arrQuestionId as &$id)
				$id = $id['id'];
			
			
			$inStr = implode(',', $arrQuestionId);
			
			//Fetcher les questions+réponses d'une section
			$query = 'SELECT q.`id`, q.`question`, r.`reponse`, r.`reponse_tech`'
					. ' FROM `' . DB_PREFIX . 'mairie_question` as q'
					. ' LEFT JOIN `' . DB_PREFIX . 'mairie_question_reponse` as r ON (r.`questionId` = q.`id`)'
					. ' WHERE q.`id` IN (' . $inStr . ')'
					. ' ORDER BY RAND();';
			$prep = $db->prepare($query);
			$prep->executePlus($db, __FILE__, __LINE__);
			$arrQuestion = $prep->fetchAll();
			$prep->closeCursor();
			$prep = NULL;
			
			foreach($arrQuestion as &$arr2)
			{
				
				if(!isset($SECTIONS[$s]['questions'][$arr2['id']])) //Nouvelle question
				{
					$SECTIONS[$s]['questions'][$arr2['id']] = array(
														'id' => $arr2['id'],
														'question' => $arr2['question'],
														'reponses' => array()
													);
				}
				
				$SECTIONS[$s]['questions'][$arr2['id']]['reponses'][$arr2['reponse_tech']] = array(
																		'reponse' => $arr2['reponse'],
																		'reponse_tech' => $arr2['reponse_tech']
																	);
				
			}
		}
		$prepQuestion->closeCursor();
		$prepQuestion = NULL;
			
		
		$tpl->set('PA', $perso->getPa());
		$tpl->set('CASH', $perso->getCash());
		$tpl->set('SECTIONS', $SECTIONS);
		//echo nl2br(str_replace(" ", '&nbsp;&nbsp;&nbsp;', var_export($SECTIONS, true)));
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/MairieVisaVertExam.htm',__FILE__,__LINE__);
	}
}

