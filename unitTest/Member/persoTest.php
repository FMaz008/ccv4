<?php
require ('const.inc.php'); //Pour les variables de la DB

class Member_PersoTest extends PHPUnit_Framework_TestCase
{
	private $vcArr; //Valid Construct Array
	private $validPersoId = 325;
	
	function __construct()
	{
		$dbMan = DbManager::getInstance();
		if(!$dbMan->connExist('game'))
		{
			$db = $dbMan->newConn('game', DB_HOST, DB_USER, DB_PASS, DB_BASE);
			$db->beginTransaction();
		}
	}
	
	function __destruct()
	{
		$dbMan = DbManager::getInstance();
		if($dbMan->connExist('game'))
		{
			$db = $dbMan->getConn('game');
			$db->rollBack();
			$dbMan->closeConn('game');
		}
	}
	
	function setUp() //Un genre de constructeur pour chaque fonction
	{
		//Instancier une connexion
		
		//Créer un tableau valide d'un personnage typique
		$this->vcArr = array(
			'id'=>325,
			'userId'=>20,
			'pa'=>90,
			'pamax'=>99,
			'pv'=>90,
			'pvmax'=>99,
			'pn'=>90,
			'prmax'=>10,
			'sexe'=>'f',
			'age'=>25,
			'taille'=>'1m80',
			'ethnie'=>'caucassien',
			'yeux'=>'bleu',
			'cheveux'=>'brun',
			'cash'=>1000,
			'background'=>'Lorem ipsum dolor sit amet',
			'description'=>'Lorem ipsum dolor sit amet',
			'current_action'=>'Lorem ipsum dolor sit amet',
			'note_mj'=>'Lorem ipsum dolor sit amet',
			'imgurl'=>'image.jpg',
			'playertype'=>'pj',
			'inscription_valide'=>'1',
			'bloque'=>'0',
			'soin'=>0,
			'menotte'=>null,
			'esquive'=>1,
			'reaction'=>'riposte',
			'lieu'=>'CV.central',
			'lng1'=>'en',
			'lng1_lvl'=>'bi',
			'lng2'=>'fr',
			'lng2_lvl'=>'bi',
			'heQte'=>500,
			'visa_perm'=>'1',
			'nom'=>'Nom du Perso'
		);
	}

	function tearDown() //Un genre de destructeur pour chaque fonction
	{
		
	}
	
	function testBasicGet() //Doit commencer par test
	{
		$obj = new Member_Perso($this->vcArr);
		
		$this->assertEquals($this->vcArr['id'], $obj->getId());
		$this->assertEquals($this->vcArr['pa'], $obj->getPa());
		$this->assertEquals($this->vcArr['pamax'], $obj->getPaMax());
		$this->assertEquals($this->vcArr['pn'], $obj->getPn());
		$this->assertEquals($this->vcArr['pv'], $obj->getPv());
		$this->assertEquals($this->vcArr['pvmax'], $obj->getPvMax());
		$this->assertEquals($this->vcArr['pn'], $obj->getPn());
		$this->assertEquals(99, $obj->getPnMax());
		$this->assertEquals($this->vcArr['prmax'], $obj->getPrMax());
		$this->assertEquals($this->vcArr['yeux'], $obj->getYeux());
		$this->assertEquals($this->vcArr['cheveux'], $obj->getCheveux());
		$this->assertEquals($this->vcArr['ethnie'], $obj->getEthnie());
		$this->assertEquals($this->vcArr['userId'], $obj->getUserId());
		$this->assertEquals($this->vcArr['cash'], $obj->getCash());
		$this->assertEquals($this->vcArr['sexe'], $obj->getSexe());
		$this->assertEquals($this->vcArr['age'], $obj->getAge());
		$this->assertEquals($this->vcArr['taille'], $obj->getTaille());
		$this->assertEquals($this->vcArr['description'], $obj->getDescription());

		$this->assertEquals($this->vcArr['lng1'], $obj->getLangue1Code());
		$this->assertEquals($this->vcArr['lng1_lvl'], $obj->getLangue1LvlCode());
		$this->assertEquals($this->vcArr['lng2'], $obj->getLangue2Code());
		$this->assertEquals($this->vcArr['lng2_lvl'], $obj->getLangue2LvlCode());
		
		
		
		//Validations requérants une connexion MySQL
		/*
		$this->assertEquals(0, $obj->getPr());
		$this->assertEquals(???, $obj->getLieu());
		*/
	}

	function testGetName()
	{
		$this->vcArr['nom']='Nom Test';
		$obj = new Member_Perso($this->vcArr);
		$this->assertEquals($this->vcArr['nom'], $obj->getNom());

		$this->vcArr['sexe']='f';
		$tmp = array(null, false, '');
		foreach($tmp as $value)
		{
			$this->vcArr['nom']=$value;
			$obj = new Member_Perso($this->vcArr);
			$this->assertSame('Inconnue', $obj->getNom());
		}
		
		$this->vcArr['sexe']='m';
		$tmp = array(null, false, '');
		foreach($tmp as $value)
		{
			$this->vcArr['nom']=$value;
			$obj = new Member_Perso($this->vcArr);
			$this->assertSame('Inconnu', $obj->getNom());
		}
	}


	
	function testGetSoin()
	{
		$this->vcArr['soin']=1;
		$obj = new Member_Perso($this->vcArr);
		$this->assertTrue($obj->getSoin());

		$this->vcArr['soin']=0;
		$obj = new Member_Perso($this->vcArr);
		$this->assertFalse($obj->getSoin());


		//Valeurs 'impossibles'
		$tmp = array(null,"test", false, true, -1, 1000, '');
		foreach($tmp as $value)
		{
			$this->vcArr['menotte']=$value;
			$obj = new Member_Perso($this->vcArr);
			$this->assertFalse($obj->getSoin());
		}
		
	}

	
	function testGetMenotte()
	{
		$this->vcArr['menotte']=123;
		$obj = new Member_Perso($this->vcArr);
		$this->assertEquals(123, $obj->getMenotte());
		
		$this->vcArr['menotte']=0;
		$obj = new Member_Perso($this->vcArr);
		$this->assertFalse($obj->getMenotte());
		
		
		//Valeurs 'impossibles'
		$tmp = array(null, "test", false, true, -1, '');
		foreach($tmp as $value)
		{
			$this->vcArr['menotte']=$value;
			$obj = new Member_Perso($this->vcArr);
			$this->assertFalse($obj->getMenotte());
		}
		
	}


	function testConvCodeLngToTxt()
	{
		$this->assertEquals('yiddish', Member_Perso::convCodeLngToTxt('yi'));
		$this->assertFalse(Member_Perso::convCodeLngToTxt('zz'));
		
		//Valeurs 'impossibles'
		$tmp = array(null, false, true, -1, '');
		foreach($tmp as $value)
		{
			$this->assertFalse(Member_Perso::convCodeLngToTxt($value));
		}
	}

	
	function testConvCodeMaitriseLngToTxt()
	{
		$this->assertEquals('bilingue', Member_Perso::convCodeMaitriseLngToTxt('bi'));
		$this->assertFalse(Member_Perso::convCodeMaitriseLngToTxt('zz'));
		
		//Valeurs 'impossibles'
		$tmp = array(null, false, true, -1, '');
		foreach($tmp as $value)
		{
			$this->assertFalse(Member_Perso::convCodeMaitriseLngToTxt($value));
		}
	}


	function testChangePa()
	{
		$this->vcArr['pa'] = 90;
		$obj = new Member_Perso($this->vcArr);
		$obj->changePa('-', 5);
		$this->assertEquals(85, $obj->getPa());

		$this->vcArr['pa'] = 90;
		$obj = new Member_Perso($this->vcArr);
		$obj->changePa('+', 5);
		$this->assertEquals(95, $obj->getPa());

		$this->vcArr['pa'] = 98;
		$obj = new Member_Perso($this->vcArr);
		$obj->changePa('+', 5);
		$this->assertEquals($this->vcArr['pamax'], $obj->getPa());
		
		$this->vcArr['pa'] = 2;
		$obj = new Member_Perso($this->vcArr);
		$obj->changePa('-', 5);
		$this->assertEquals(0, $obj->getPa());
		
	}
	
	function testChangePn()
	{
		$this->vcArr['pn'] = 90;
		$obj = new Member_Perso($this->vcArr);
		$obj->changePn('-', 5);
		$this->assertEquals(85, $obj->getPn());

		$this->vcArr['pn'] = 90;
		$obj = new Member_Perso($this->vcArr);
		$obj->changePn('+', 5);
		$this->assertEquals(95, $obj->getPn());

		$this->vcArr['pn'] = 98;
		$obj = new Member_Perso($this->vcArr);
		$obj->changePn('+', 5);
		$this->assertEquals(99, $obj->getPn());
		
		$this->vcArr['pn'] = 2;
		$obj = new Member_Perso($this->vcArr);
		$obj->changePn('-', 5);
		$this->assertEquals(0, $obj->getPn());
		
	}
	
	function testChangePv()
	{
		$this->vcArr['pv'] = 90;
		$obj = new Member_Perso($this->vcArr);
		$obj->changePv('-', 5);
		$this->assertEquals(85, $obj->getPv());

		$this->vcArr['pv'] = 90;
		$obj = new Member_Perso($this->vcArr);
		$obj->changePv('+', 5);
		$this->assertEquals(95, $obj->getPv());

		$this->vcArr['pv'] = 98;
		$obj = new Member_Perso($this->vcArr);
		$obj->changePv('+', 5);
		$this->assertEquals($this->vcArr['pvmax'], $obj->getPv());
		
		$this->vcArr['pv'] = 2;
		$obj = new Member_Perso($this->vcArr);
		$obj->changePv('-', 5);
		$this->assertEquals(0, $obj->getPv());
		
	}

	function testChangeCash()
	{
		$this->vcArr['cash'] = 90;
		$obj = new Member_Perso($this->vcArr);
		$obj->changeCash('-', 5);
		$this->assertEquals(85, $obj->getCash());

		$this->vcArr['cash'] = 90;
		$obj = new Member_Perso($this->vcArr);
		$obj->changeCash('+', 5);
		$this->assertEquals(95, $obj->getCash());
		
		$this->vcArr['cash'] = 2;
		$obj = new Member_Perso($this->vcArr);
		$obj->changeCash('-', 5);
		$this->assertEquals(0, $obj->getCash());

		$this->vcArr['cash'] = 10;
		$obj = new Member_Perso($this->vcArr);
		$obj->changeCash('+', 2.4);
		$this->assertEquals(12, $obj->getCash());
		
		$this->vcArr['cash'] = 10;
		$obj = new Member_Perso($this->vcArr);
		$obj->changeCash('+', 2.999999999);
		$this->assertEquals(12, $obj->getCash());
	}

	
	
	function testgetStatName()
	{
		$obj = new Member_Perso($this->vcArr);
		$this->assertEquals('Force', $obj->getStatName(3));
	}

	
	function testgetStatCode()
	{
		$obj = new Member_Perso($this->vcArr);
		$this->assertEquals('for', $obj->getStatCode(3));
	}

	
	function testConvStatXpToLevel()
	{
		$this->assertEquals(-5,Member_Perso::convStatXpToLevel(-500));
		$this->assertEquals(-5,Member_Perso::convStatXpToLevel(-400));
		$this->assertEquals(-4,Member_Perso::convStatXpToLevel(-399));
		$this->assertEquals(-4,Member_Perso::convStatXpToLevel(-200));
		$this->assertEquals(-3,Member_Perso::convStatXpToLevel(-199));
		$this->assertEquals(-3,Member_Perso::convStatXpToLevel(-100));
		$this->assertEquals(-2,Member_Perso::convStatXpToLevel(-99));
		$this->assertEquals(-2,Member_Perso::convStatXpToLevel(-50));
		$this->assertEquals(-1,Member_Perso::convStatXpToLevel(-49));
		$this->assertEquals(-1,Member_Perso::convStatXpToLevel(-20));
		$this->assertEquals(0,Member_Perso::convStatXpToLevel(-1));
		$this->assertEquals(0,Member_Perso::convStatXpToLevel(1));
		$this->assertEquals(1,Member_Perso::convStatXpToLevel(20));
		$this->assertEquals(1,Member_Perso::convStatXpToLevel(49));
		$this->assertEquals(2,Member_Perso::convStatXpToLevel(50));
		$this->assertEquals(2,Member_Perso::convStatXpToLevel(99));
		$this->assertEquals(3,Member_Perso::convStatXpToLevel(100));
		$this->assertEquals(3,Member_Perso::convStatXpToLevel(199));
		$this->assertEquals(4,Member_Perso::convStatXpToLevel(200));
		$this->assertEquals(4,Member_Perso::convStatXpToLevel(399));
		$this->assertEquals(5,Member_Perso::convStatXpToLevel(400));
		$this->assertEquals(5,Member_Perso::convStatXpToLevel(500));

		//Valeurs 'impossibles'
		$tmp = array(null, '', 'test', array());
		foreach($tmp as $value)
		{
			$this->assertEquals(0,Member_Perso::convStatXpToLevel($value));
		}
	}

	function testConvStatLevelToXp()
	{
		$this->assertEquals(-400, Member_Perso::convStatLevelToXp(-6));
		$this->assertEquals(-400, Member_Perso::convStatLevelToXp(-5));
		$this->assertEquals(-200, Member_Perso::convStatLevelToXp(-4));
		$this->assertEquals(-100, Member_Perso::convStatLevelToXp(-3));
		$this->assertEquals(-50, Member_Perso::convStatLevelToXp(-2));
		$this->assertEquals(-20, Member_Perso::convStatLevelToXp(-1));
		$this->assertEquals(0, Member_Perso::convStatLevelToXp(0));
		$this->assertEquals(20, Member_Perso::convStatLevelToXp(1));
		$this->assertEquals(50, Member_Perso::convStatLevelToXp(2));
		$this->assertEquals(100, Member_Perso::convStatLevelToXp(3));
		$this->assertEquals(200, Member_Perso::convStatLevelToXp(4));
		$this->assertEquals(400, Member_Perso::convStatLevelToXp(5));
		$this->assertEquals(400, Member_Perso::convStatLevelToXp(6));

		//Valeurs 'impossibles'
		$tmp = array(null, '', 'test', array());
		foreach($tmp as $value)
		{
			$this->assertEquals(0, Member_Perso::convStatLevelToXp($value));
		}
	}
	
	function testSetStat()
	{
		$obj = Member_Perso::load($this->validPersoId);

		$expected = $obj->getStatRawXp(3) +3 ;
		$obj->setStat(array('FOR'=>'+03', 'AGI'=>'-03'));
		$this->assertEquals($expected, $obj->getStatRawXp(3));

		$expected = $obj->getStatRawXp(3) -3 ;
		$obj->setStat(array('FOR'=>'-03', 'AGI'=>'+03'));
		$this->assertEquals($expected, $obj->getStatRawXp(3));
	}

	function testGetLieu()
	{
		$obj = Member_Perso::load($this->validPersoId);
		
		$this->assertTrue($obj->getLieu()->confirmPerso($obj, $obj->getId()));
	}
	
}
