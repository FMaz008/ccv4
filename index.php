<?php
/*
 * Page principale du moteur de jeu.
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.2
 * @package CyberCity_2034
 */
 

/**
 * Fonction qui gère l'inclusion des fichiers.
 *
 * Cette fonction est utilisée par {@link __autoload()}.
 *
 * @param string $filePath Chemin complet vers le fichier à inclure.
 */
function fctRequire($filePath)
{
	if(!file_exists($filePath))
		if(defined('DEBUG_MODE') && DEBUG_MODE)
			die ('<span style="background-color:#000;color:#FFF;"><strong><font color=red>404:</font></strong> ' . $filePath . '</span>');
		else
			die ('<font color=red>Erreur 404 - Page non-trouvée.</font>');
	require $filePath;
}


/**
 * Génère le chargement automatique des classes
 *
 * @param int $classname Nom de la classe à charger
 */
function __autoload($classname)
{
	$path = str_replace('_',DIRECTORY_SEPARATOR, $classname);
	fctRequire('classes/' . $path . '.php');
}


//Inclure les fichiers de bases
fctRequire ('const.inc.php');
fctRequire ('function.php');




//Débuter le chronomètre du temps de génération
$timerTotal = new Timer();
$timerTotal->start();


//Désactiver la cache du navigateur
Header('Cache-Control: no-cache');
Header('Pragma: no-cache');
Header('Content-type: text/html; charset=' . SITE_CHARSET); //S'assurer que les caractères francais soient bien pris en comptes.


//Suppression des magic quote si activé
if(get_magic_quotes_gpc() && isset($_POST))
	foreach($_POST as $key=>$value)
		if(is_string($_POST[$key]))
			$_POST[$key] = stripslashes($value);




/**
 * Gestion de l'affichage général du site.
 * 
 * Cette classe gère l'affichage de toutes les pages du site.
 *
 * <br>
 * Cette classe instancie:
 *  - {@link MySQLConnection}
 *  - {@link Account}
 *  - {@link Session}
 *  - {@link Template}
 * 
 * <br>
 * Cette classe s'occupe de générer:
 * - L'affichage des statistiques du site (Membre, Inscription, Online)
 * - L'affichage du menu
 * - L'affichage d'une page selon les paramètre GET
 * - L'affichage des statistiques de génération de page (Requêtes, Temps)
 */
class Main
{
	/*
	 * @access private
	 * @var MySQLConnection|mixed
	 */
	private $db;
	
	/*
	 * @access private
	 * @var Session
	 */
	private $session;
	
	/*
	 * @access private
	 * @var Account
	 */
	private $account;
	
	/*
	 * @access private
	 * @var Template
	 */
	private $tpl;
	
	
	function __construct()
	{
		//Établir la connexion MySQL (Temps: 0.002 sec)
		try
		{
			$dbMan = DbManager::getInstance();
			$this->db = $dbMan->newConn('game', DB_HOST, DB_USER, DB_PASS, DB_BASE);
		}
		catch (Exception $e)
		{
            die('Impossible d\'établir la connexion: ' . $e->getMessage());
        }
        
        
		//Instancier le compte (Temps: 0.0002 sec)
		$this->account = new Account();
		
		
		//Démarrer la session (Temps: 0.0161 sec)
		$this->session = Session::load();
		
		
		
		//Si un utilisateur est défini dans la session, charger le compte associé
		$userId = $this->session->getVar('userId');
		if ($userId!==NULL)
			$this->account->loadAccount($userId, $this->session->getVar('logged'), __FILE__, __LINE__);
		
		
		
		//Démarrer le système de template (Temps: 0.0008 sec)
		$this->tpl = new Template($this->account);
		$this->tpl->set('SKIN_VIRTUAL_PATH',	$this->account->getSkinRemoteVirtualPath());	//Chemin root du skin
		$this->tpl->set('SKIN_ONLINEOFFLINE',	$this->account->skinOnlineOffline());
		$this->tpl->set('GAME_SESSION_LENGHT',	(SESSION_TIMEOUT*60));	//Durée en secondes de la session pour le countdown javascript
		$this->tpl->set('GAME_TIME',			fctToGameTime(time()));	//Temps affiché dans le jeu
		$this->tpl->set('GAME_DEVISE',			GAME_DEVISE);
		$this->tpl->set('SITE_CHARSET', 		SITE_CHARSET);
		$this->tpl->set('IS_LOGGED', 			($userId!==NULL) ? true : 0); //True si connecté
		
		
		
		
		
		//Trouver le # de la révision SVN
		$rev = $this->getSvnRevision('revision.txt');
		if($rev !== false)
			$this->tpl->set('SITE_REVISION', $rev);

		
		
		//Charger le contenu demandé
		$this->tpl->set('PAGE_SOURCE', $this->generatePage());
		
		
		
		if (!isset($_GET['popup']))
		{
			
			//Générer des données de la zone de gauche
			$this->generateStat();
			$this->generateMenu();
			
			
			//Créer la liste des liens sur les sujets récents du forum
			if(DB_FORUM_HOST !== NULL)
			{
                $arrForumTopic = array();
				$LEFTBAR_FORUM = $this->generateForumLink($arrForumTopic);
				$this->tpl->set('LEFTBAR_FORUM', $LEFTBAR_FORUM);
				$this->tpl->set('FORUM_RECENT_TOPIC', $arrForumTopic);
			}
			
			
			$page = $this->account->getSkinRemotePhysicalPath() . 'html/index_full.htm';
		}
		else
		{
			
			$page = $this->account->getSkinRemotePhysicalPath() . 'html/index_lite.htm';
		}
		
		
		//Afficher la page
		global $timerTotal, $memStart;
		
		//Afficher la page.
		$this->tpl->set('QUERY_COUNT',		$this->db->getQueryCount());
		$this->tpl->set('SQL_GEN_TIME',		$this->db->getQueryTime());
		$this->tpl->set('MEMORY_USAGE',		fctFormatBytes(memory_get_peak_usage()));
		$this->tpl->set('PAGE_GEN_TIME',	round($timerTotal->finish(),5));
		echo $this->tpl->fetch($page,__FILE__,__LINE__);
		
	}
	
	
	
	
	
	
	/**
	* Générer quelques statistiques à propo du jeu.
	* Elles sont placé dans les templates
	*
	* @return void
	*/
	private function generateStat()
	{
		//Nombre de joueurs
		$query='SELECT count(id)'
				. ' FROM ' . DB_PREFIX . 'account;';
		$arr=$this->db->query($query,__FILE__,__LINE__)->fetch();
		$this->tpl->set('GAME_PLAYERS', $arr[0]);
		
		//Nombre d'inscriptions
		$query='SELECT count(id)'
				. ' FROM ' . DB_PREFIX . 'perso'
				. ' WHERE inscription_valide="0";';
		$arr=$this->db->query($query,__FILE__,__LINE__)->fetch();
		$this->tpl->set('GAME_SUBSCR', $arr[0]);
		
		//Nombre d'online
		$query='SELECT count(idcookie)'
				. ' FROM ' . DB_PREFIX . 'session;';
		$arr=$this->db->query($query,__FILE__,__LINE__)->fetch();
		$this->tpl->set('GAME_ONLINE', $arr[0]);
		
	}
	
	
	/**
	* Générer le menu principal à afficher
	* Il est placé dans les templates
	*
	* @return void
	*/
	private function generateMenu()
	{
		//Si connecté ( mot de passe = ok ), afficher le menu membre
		if ($this->session->getVar('userId')!==NULL)
		{
			//Si accès valide ( mail confirmé ), afficher le contenu du menu membre
			if($this->session->getVar('logged')===true)
			{
				$menuarr = array();
				$arrPersos = $this->session->getVar('persoList');
				if (!empty($arrPersos))
				{
					foreach($arrPersos as $perso)
					{
						$menuarr[] = array (
										'url' => '?m=index&amp;perso=' . $perso['id'],
										'txt' => $perso['nom']
										);
					}
					$this->tpl->set('MENU_PERSO', $menuarr);
					unset($menuarr);
				}
				if ($this->account->getMjId() !== null)
					$this->tpl->set('MENU_MJ', true);
				
			}
			$page = $this->account->getSkinRemotePhysicalPath() . 'html/menu_member.htm';
		}
		else
		{
			$page = $this->account->getSkinRemotePhysicalPath() . 'html/menu_visitor.htm';
		}
		
		$this->tpl->set('MENU', $this->tpl->fetch($page,__FILE__,__LINE__));
	}
	
	
	/**
	 * Analyse les paramètres GET et les droits d'accès et retourne la page qui sera affichée
	 * 
	 * @return string Source HTML de la page
	 */
	private function getPage()
	{
		
		//Si une page non-visiteur est spécifiée et non authorisé
		if (!isset($_GET['v']) && !$this->account->isLogged()) //Non autorisé
		{
			//Ces pages ont le droit d'être affichées (changement de mail)
			$arr = array('Config_Compte', 'Config_Compte2', 'News');
			
			if (isset($_GET['m']))
				if(in_array($_GET['m'], $arr)) //Il s'agit d'une page autorisé
					if($this->session->getVar('userId')!==NULL) //Est authentifié
						return array('type' => 'm', 'page' => $_GET['m']);
			
			//Dans tous les autres cas, l'accès est refusé
			return array('type' => 'v', 'page' => 'Login');
			
		}
		
		if(isset($_GET['v'])) //Page visiteur
			return array('type' => 'v', 'page' => $_GET['v']);
		
		if(isset($_GET['m'])) //Page Membre
			return array('type' => 'm', 'page' => $_GET['m']);
		
		if(isset($_GET['mj']) && $this->account->getMjId()!==NULL ) //Page MJ
			return array('type' => 'mj', 'page' => $_GET['mj']);
			
		
		//Si aucune sélection
		return array('type' => 'v', 'page' => 'Main');
	}
	
	/**
	 * Déterminer quelle page le site doit afficher
	 */
	private function generatePage()
	{
		
		
		
		//Affichage des erreurs
		if(isset($_POST['erreur']))
			$this->tpl->set('ERREUR', htmlspecialchars(stripslashes($_POST['erreur'])));
		
		
		
		//Trouver la page à générer
		$arrPage = $this->getPage();
		
		//Assainir le nom du fichier
		$arrPage['page'] = fctRemoveAllNonAlpha($arrPage['page']);
		
		//Générer la page demandée
		switch($arrPage['type'])
		{
			case 'v':
				return $this->genVisitorPage($arrPage['page']);
			case 'm':
				return $this->genMemberPage($arrPage['page']);
			case 'mj':
				return $this->genMjPage($arrPage['page']);
			default:
				return $this->genVisitorPage('Main');
				
		}
		
		
	}
	
	
	
	
	
	private function genVisitorPage($file)
	{
		
		$valRet = call_user_func_array(
						array('Visitor_' . $file , 'generatePage'),
						array(&$this->tpl,
								&$this->session,
								&$this->account
							)
						);
			
		if ($file==='Login2' && $this->session->getVar('logged')===true) //Si l'utilisateur viens juste de s'authentifier
		{
			//Trouver si la personne à au moins 1 perso de créé
			$query = 'SELECT id'
						. ' FROM ' . DB_PREFIX . 'perso'
						. ' WHERE userid=:userId'
						. ' LIMIT 1;';
			$prep = $this->db->prepare($query);
			$prep->bindValue(':userId',		$this->session->getVar('userId'),		PDO::PARAM_INT);
			$prep->execute($this->db, __FILE__, __LINE__);
			$arr = $prep->fetch();
			$prep->closeCursor();
			$prep = NULL;
			
			if($arr===false)
			{
				header('location: ?m=News');
				//Générer le menu principal
				//$this->generateMenu($this->tpl, $this->db, $this->session, $this->account);
			}
			else
			{
				header('location: ?m=index&perso=' . (int)$arr['id']);
			}
		}
			
		return $valRet;
	}
	
	
	private function genMemberPage($file)
	{
		//Si aucun perso est chargé ET qu'on ne veux pas en charger un
		if($this->session->getVar('persoId')===NULL && !isset($_GET['perso']))
		{
		
			//Page n'ayant besoin d'être en train de jouer un perso
			$canAccessWithoutPerso = false;
			$pages = explode(',',str_replace(' ','',ENGINE_ACCESS_WITHOUT_PERSO));
			foreach($pages as $page)
			{
				if($page == $file)
				{
					$canAccessWithoutPerso = true;
					break;
				}
			}
			unset($pages, $page);
			if($canAccessWithoutPerso)
			{
				return call_user_func_array(
								array('Member_' . $file , 'generatePage'),
								array(&$this->tpl,
										&$this->session,
										&$this->account
									)
								);
			}
			else
			{
				return fctErrorMSG('Aucun personnage sélectionné.');
			}
			unset($canAccessWithoutPerso);
			
		}
		else //Un personnage à été sélection ou est sauvegardé
		{
		
			
			//S'il faudrait charger un perso mais que rien n'est demandé
			if($this->session->getVar('persoId')===NULL && !isset($_GET['perso']))
				return fctErrorMSG('Vous devez sélectionner un personnage.');
			
			
			//Si aucun perso n'est actuellement joué, procéder à son chargement
			if(isset($_GET['perso']) && $_GET['perso'] != $this->session->getVar('persoId'))
			{
				//Rechercher parmis notre liste de personnage si celui demandé y est.
				$persoFound = false;
				foreach($this->session->getVar('persoList') as $arrPerso)
				{
					if ($arrPerso['id'] == $_GET['perso'])
					{
						$persoFound=true;
						break;
					}
				}
				if(!$persoFound)
					return fctErrorMSG('Tentative de jouer un perso qui ne vous appartiend pas. (cheat)');	
				
				$this->session->setVar('persoId', (int)$_GET['perso']);
			}
				
			try
			{
				$perso = Member_Perso::load($this->session->getVar('persoId'));
				$this->tpl->set('CURRENT_PERSO_ID', $perso->getId());
				
				if($perso->getHeMsgCount()>15000)
				{
					fctBugReport(
						'Un problème avec le HE à été détecté. Un rapport d\'erreur à été créé et votre compte sera vérifié sous peu.',
						$perso,
						__FILE__, __LINE__,
						__FUNCTION__, __CLASS__, __METHOD__,
						true, true, true);
				}
				//$this->session->setVar('perso', $perso);
			}
			catch(Exception $e)
			{
				return fctErrorMSG($e->getMessage());	
			}
				
			return call_user_func_array(
							array('Member_' . $file , 'generatePage'),
							array(&$this->tpl,
									&$this->session,
									&$this->account,
									&$perso
								)
							);
			
		}//Fin du if: Si un personnage à été sélection ou est sauvegardé
		
	}
	
	
	private function genMjPage($file)
	{
		//Rechercher le compte MJ
		$query = 'SELECT *'
					. ' FROM ' . DB_PREFIX . 'mj'
					. ' WHERE id=' . (int)$this->account->getMjId() 
						. ' AND userId=' . (int)$this->account->getId()
					. ' LIMIT 1;';
		$arr =$this->db->query($query, __FILE__,__LINE__)->fetch();
		
		//Instancier le compte MJ
		$mj = new Mj_Mj($arr);
		
		$this->tpl->set('MJ_CSS', true);
		
		return call_user_func_array(
						array('Mj_' . $file , 'generatePage'),
						array(&$this->tpl,
								&$this->session,
								&$this->account, 
								&$mj
							)
						);
	}
	
	
	private function generateForumLink(&$arrTopic)
	{
		$txt ='';
		
		//Établir la connexion MySQL (Temps: 0.002 sec)
		try
		{
			$dbMan = DbManager::getInstance();
			$db = $dbMan->newConn('forum', DB_FORUM_HOST, DB_FORUM_USER, DB_FORUM_PASS, DB_FORUM_BASE);
		}
		catch (Exception $e)
		{
            die('Impossible d\'établir la connexion: ' . $e->getMessage());
        }
		
		$query = 'SELECT msgf.subject,msgf.id_topic
					FROM ' . DB_FORUM_PREFIX . 'messages AS msgl,
							' . DB_FORUM_PREFIX . 'messages AS msgf,
							' . DB_FORUM_PREFIX . 'topics AS t
					WHERE t.ID_LAST_MSG = msgl.ID_MSG
						AND t.ID_FIRST_MSG = msgf.ID_MSG
						AND FIND_IN_SET(t.ID_BOARD, "' . FORUM_BOARD_IDS . '")
					GROUP BY t.ID_TOPIC
					ORDER BY msgl.poster_time DESC
					LIMIT ' . FORUM_SUBJECT_LIMIT . ';';
			
		$result = $db->query($query, __FILE__, __LINE__);
        
        //$txt pour la rétro-compatibilité avec les vieux skin (darkblueOld et Cyberrust)
        $txt .= '<div class="forumtitre">Quoi de neuf à CyberCity ?</div>';
        while ($arr = $result->fetch())
        {
            $txt .= '<hr class="forumsep" /><div class="forumsujet">';
            $txt .= '<a href="' . FORUM_URL . 'index.php/topic,' . $arr['id_topic'] . '.0.html">';
            $txt .= $arr['subject'];
            $txt .= '</a>';
            $txt .= '</div>';
            
            $arrTopic[] = array('url'=> FORUM_URL . 'index.php/topic,' . $arr['id_topic'] . '.0.html', 'subject'=> $arr['subject']);
        }

		return $txt;
	}
	
	private function getSvnRevision($filename)
	{
		if(!file_exists($filename))
			return false;
		
		$handle = @fopen($filename, 'r');
		if (!$handle)
			return false;
		
		$buffer = fgets($handle);
		fclose($handle);
		return $buffer;
	}
}

//Générer la page
$main = new Main();


