<?php
/* Gestion de l'interface de modification d'une fréquence média en particulier
 * 
 * @package MJ
 */
 class Mj_Lieu_MediasMod
 {
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$errorUrl = '?mj=Lieu_Medias';
	
		//Vérification des données
		if(!isset($_POST['id']))
			return fctErrorMSG('Aucun média n\'a été sélectionné.', $errorUrl);
			
		//Si on passe par autre média, vérifier que les champs sont renseignés
		if($_POST['id'] == 'new')
		{
			if(empty($_POST['new_mediaType']) || empty($_POST['new_canalId']))
				return fctErrorMSG('Tous les champs ne sont pas remplis.', $errorUrl);
		}
		
		$mediaType = $_POST[$_POST['id'] . '_mediaType'];
		$canalId = $_POST[$_POST['id'] . '_canalId'];
		
		$tpl->set('ID', $_POST['id']);
		$tpl->set('MEDIA_TYPE', $mediaType);
		$tpl->set('CANAL_ID', $canalId);
		
		if($mediaType == 'tele')
			$tpl->set('MEDIA_TYPE_AFF', 'Télé');
		else
			$tpl->set('MEDIA_TYPE_AFF', 'Radio');
		
		if(isset($_POST['add']))
		{
			$errorUrl = '?mj=Lieu_Medias';
		
			if(!isset($_POST['emissionAdd']))
				return fctErrorMSG('Aucune émission n\'a été sélectionnée.', $errorUrl);
			
			if(empty($_POST['messageAdd']))
				return fctErrorMSG('Aucun message n\'a été rédigé.', $errorUrl);
				
			if($_POST['emissionAdd'] == 'new' && empty($_POST['new_emissionNom']))
				return fctErrorMSG('Aucun titre d\'émission n\'a été rédigé.', $errorUrl);
				
			if($_POST['emissionAdd'] == 'old' && empty($_POST['old_emissionNom']))
				return fctErrorMSG('Aucun titre d\'émission n\'a été sélectionné.', $errorUrl);
			
			self::add($mediaType, $canalId);
		}
		
		if(isset($_POST['delete']))
		{
			self::delete($mediaType, $canalId);
		}
		
		$messages = Member_MediaFactory::getAllMessage($mediaType, $canalId);
		$titres = Member_MediaFactory::getAllEmissionName($mediaType, $canalId);
		
		$tpl->set('MEDIA_MESSAGES', $messages);
		$tpl->set('TITRE_LISTE', $titres);
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/MediasMod.htm',__FILE__,__LINE__);
	}
	
	private static function add($mediaType, $canalId)
	{
		Member_MediaFactory::addMessage($mediaType, $canalId, $_POST[$_POST['emissionAdd'] . '_emissionNom'], $_POST['messageAdd']);
	}
	
	private static function delete($mediaType, $canalId)
	{
		$messages = Member_MediaFactory::getAllMessage($mediaType, $canalId);
		
		foreach($messages as $mes)
		{
			if(isset($_POST[$mes['id']]))
				Member_MediaFactory::deleteMessage($mes['id']);
		}
	}
 }