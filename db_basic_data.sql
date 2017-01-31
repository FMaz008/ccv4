-- Créer un compte test
INSERT INTO `cc_account` (`id`,`user`,`pass`,`email`,`sexe`,`date_inscr`,`remise`,`code_validation`,`skin`)
VALUES (1, 'test', 'teH0wLIpW0gyQ', 'test@test.com', 'm', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), NULL, 'dark_blue');

-- Ajouter les droits d'accès administrateur.
INSERT INTO `cc_mj` (`id`, `userId`, `nom`, `poste`, `email_prefix`, `present`, `ax_ppa`, `ax_ej`, `ax_hj`, `ax_admin`, `last_connection`)
VALUES (1, 1, 'Test', 'Compte test', 'admin', 1, 1, 1, 1, 1, NOW());


-- SET FOREIGN_KEY_CHECKS=0;

-- phpMyAdmin SQL Dump
-- version 3.2.2.1deb1
-- http://www.phpmyadmin.net
--
-- Serveur: 127.0.0.1:9999
-- Généré le : Ven 12 Mars 2010 à 18:34
-- Version du serveur: 5.0.51
-- Version de PHP: 5.2.10-2ubuntu6.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


--
-- Contenu de la table `cc_caract`
--

INSERT INTO `cc_caract` (`id`, `catid`, `type`, `nom`, `desc`) VALUES
(1, 0, 'system', 'Physique - Corps', ''),
(2, 1, 'system', 'Géant', 'Votre taille explose les 2 mètres, vous avez tendance à vous manger les cadres de porte mais au moins vous n''êtes jamais perdus dans la foule.'),
(3, 1, 'system', 'Nabot', 'Grand en esprit peut-être, mais pour ce qui est de la taille, vous frôlez le ridicule... et le plancher aussi d''ailleurs. Votre apparence frôle d''ailleurs la difformité.'),
(4, 1, 'system', 'Enfant', 'Petit, faible, peu souvent prit au sérieux, un enfant trouvera néanmoins plus facilement une cachette, sera plus agile ou habile de ses mains. Pour autant il aura tendance à apprendre plus rapidement qu''un adulte toutes les leçons que la vie aura pu lui apporter.'),
(5, 1, 'system', 'Vieillard', 'Si vous êtes moins fort et moins résistant qu''avant, parfois un peu oublieux de vos petits besoins ou du nom de la charmante jeune fille qui vous parle depuis tout à l''heure, le grand age comporte certains avantages dont les plus rusés sauront user à merveille.'),
(6, 1, 'system', 'Obèse', 'Vous êtes gros. Aussi discret qu''un éléphant dans une boutique de porcelaine: voilà un dicton qui vous va comme un gant.'),
(7, 1, 'system', 'Gringalet', 'Un petit passage entre deux murs ? Faites attention de ne pas casser votre délicate ossature en deux quand même.'),
(8, 0, 'system', 'Physique - Apparence et impression', ''),
(9, 8, 'system', 'Freak', 'Vous êtes un vrai monstre, digne d''une galerie des horreurs, tant dans votre style qu''à votre gueule, et quand vous sortez de votre trou, vous faites pleurer les enfants... quand ceux-ci ils ne vous jettent pas des pierres.'),
(10, 8, 'system', 'Belle tronche', 'Jouez le jeu un minimum, soyez souriant, et les gens auront tendance à vous accorder le bon dieu sans confession. A tort ou à raison...'),
(11, 8, 'system', 'Tête de truand', 'On vous voit et on vous imagine entrain de violer la petit Marie ou égorger le pauvre Henri... Vous n''inspirez rien de bon ! Ce n''est pas votre faute quand même si vous êtes constamment constipé...'),
(12, 8, 'system', 'Impressionnant', 'Vous êtes une masse même si en réalité vous n''êtes pas très fort, en tout cas pas la peine de hausser la voix pour qu''une personne vous écoute ou se sente impressionnée. Méfiez vous cependant à ce que vos interlocuteurs ne se méprennent pas sur vos intentions.'),
(13, 8, 'system', 'Traits quelconques', '« Euh il était de taille moyenne et les cheveux foncés, a part ça je vois pas ! »  Oui vous êtes monsieur tout le monde, vous n''avez aucun signe particulier, vous vous fondez dans la foule, et ça vous va très bien comme ça.'),
(14, 8, 'system', 'Sosie', 'Vous ressemblez trait pour trait à quelqu''un de connu, ou inconnu, les gens ont souvent votre nom sur le bout de la langue. '),
(15, 8, 'system', 'Trouble de l''élocution', 'Bégaiement, zozotement, voix de fausset, accent épouvantable... Tout pour passer l''envie de vous écouter ou rendre difficile la transmission d''un message. Surtout s''il est important.'),
(16, 8, 'system', 'Élocution de président', 'Vous savez parler avec tacte, vous avez souvent le mot juste. Vous prenez-vous pour quelqu''un d''autre ?'),
(17, 8, 'system', 'Puanteur', 'Si vous prenez guère soin de vous, et vous dégagez rapidement une odeur nauséabonde. ça demande beaucoup d''effort pour pouvoir discuter avec vous, surtout pour vous !'),
(18, 0, 'system', 'Physique - Santé', ''),
(19, 18, 'system', 'Prothèse', 'Vous allez aussi souvent chez le doc que chez le mécano depuis qu''une partie de votre corps a été remplacée par un joli tas de ferraille. Plus ou moins perfectionnée, cette extension artificielle de vous même en remplacement d''un élément perdu en route n''est cependant pas sans contrepartie.'),
(20, 18, 'system', 'Immunité', 'Vous avez du tellement forcer sur certaines saloperies dans votre vie, qu?un certain produit poison ou drogue ne vous fait presque plus aucun effet. Par contre, il est pas impossible que vous ayez perdu un peu de neurones en contre-partie.'),
(21, 18, 'system', 'Allergie', 'Les poils de chat peuvent vous faire tousser, le pollen provoquer une poussée d?acné, le poivre vous fait convulser, les piqûres d?abeilles sont fatales... et fuck mère nature. Aaaah, si seulement il n''y avait qu''elle, mais certains tissus de synthèse peuvent vous donner des plaques et l''odeur de la colle vous inflige une profonde nausée.'),
(22, 18, 'system', 'Sens émoussé', 'Myope comme une taupe, sourd comme un pot, un handicap sensoriel vous cause quotidiennement son lot de problèmes. Reste qu''il est facile d''excuser certaines choses sur votre état.'),
(23, 18, 'system', 'Maladie incurable', 'Vous avez chopé une vraie saloperie, et si personne ne s''en charge avant, c''est elle qui vous tuera. Faire pitier? Humm...'),
(24, 18, 'system', 'Mauvaise cicatrisation', 'Vous guérissez moins vite que la normale. Vos os sont fragiles, les blessures laissent leurs traces livides sur votre peau, vos articulations jadis brisées vous font mal par temps humide et les plaies ont vite tendance à s''infecter.'),
(25, 18, 'system', 'Troubles sexuels ou hormonaux', 'Rien à faire, vous bandez mou. A moins que votre petit intérieur ne soit aussi sec que le désert du Sahara. Non non, elle n''est pas petite! J''ai froid, c''est tout...'),
(26, 18, 'system', 'Faible constitution', 'C''est un fait, vous avez beau vous muscler, vous avez toujours du mal à soulever des poids pourtant raisonnables. Vous attrapez la crève pour un rien, une balle dans le pied vous rend infirme jusqu''à sa guérison et dans l''ensemble, vous avez du mal à vous faire passer pour superman.'),
(27, 18, 'system', 'Handicapé', 'Manchot, cul-de-jatte, paraplégique, muet... Vous êtes quelque peu diminué physiquement, et vous avez souvent de quoi vous sentir comme le zèbre blessé du troupeau en cela que vos performances dans certains domaines n''égaleront jamais celle des autres et que toujours vous serez différent.'),
(28, 18, 'system', 'Dépendant', 'Eh oui ! Vous êtes un beau toxico ! Vous carburez à une ou plusieurs cochonneries et vous vivez l''enfer à chaque fois que vous n?avez pas votre dose.'),
(29, 0, 'system', 'Mental - Traits de caractère', ''),
(30, 29, 'system', 'Au taquet', 'Vous êtes toujours à 100% à ce que vous faites, le degré ultime de la concentration, seulement voilà, quand une tâche vous absorbe, le GIGN au grand complet pourrait faire un défilé de majorette sous vos yeux que vous ne les verriez même pas.'),
(31, 29, 'system', 'Dans les nuages', 'Rien à faire, vous avez du mal à vous concentrer longtemps sur une tache, et lorsque l''on vous parle vous avez tendance à rêvasser inconsciemment assez rapidement. Sans parler des fréquents oublis et des petites maladresses que votre tempérament distrait provoque. Mais, où étaient vos clefs déjà, la dernière fois que vous les avez vues?'),
(32, 29, 'system', 'Âme sensible', 'Eh oui, rien que la vue du sang vous fait gerber et la souffrance d''autrui vous est intolérable. Ah, pauvre mammy qui traverse toute seule la rue! Pas de voitures dans le dôme? Pas grave, on est scout ou on ne l''est pas...'),
(33, 29, 'system', 'Sadique', 'Torturer ou tuer à petit feu vous fait prendre votre pied. Jeune enfant c''était les insectes, maintenant, à qui allez vous vous en prendre?'),
(34, 29, 'system', 'Méfiant', 'Que vous voyiez le mal partout peut se comprendre, mais que vous ne voyiez le Bien nulle part, c?est déjà plus problématique.'),
(35, 29, 'system', 'Naif', 'La moindre parole d''une personne que vous connaissez, ou même d''un inconnu, est prise pour vraie. Vous êtes un ignare et vous passez votre temps à vous nourrir des âneries que vous disent les autres avec le sourire confiant de celui qui sait qu''il peut toujours compter sur les autres.'),
(36, 29, 'system', 'Clairvoyant', 'Vous êtes d''une lucidité remarquable, lorsque vous analysez une situation, il est courant que certains détails ne soient perçus que par vous. Votre cerveau est constamment en ébullition et la moindre énigme vous fait frétiller. Ceux qui ont un QI plus faible que le votre (ou que celui que vous croyez avoir) vous ennuient généralement au plus haut point.'),
(37, 29, 'system', 'Ramolli du bulbe', 'Vous êtes un peu crétin sur les bords, l''évidence ne vous saute déjà pas aux yeux, alors pour ce qui est de faire des déductions pertinentes, faudra pas compter sur vous. De toute façon, aligner deux pensées abstraites cohérentes est déjà pour vous une sorte d''exploit.'),
(38, 29, 'system', 'Individualiste', 'Pour vous le seul moyen de survivre dans ce monde c''est de la jouer solo, chacun pour sa gueule, les autres peuvent toujours crever si ils ne vous sont d''aucune utilité.'),
(39, 29, 'system', 'Altruiste', 'Vous ne pouvez pas résister aux femmes, aux enfants, aux chiens qui font les yeux mouillés... Rien à faire, même si vous savez faire une bêtise c''est plus fort que vous. Sans parler d''éventuels témoins qui pourraient vous prendre pour un gros niais.'),
(40, 29, 'system', 'Sang-froid', 'Vous avez déjà affrontez la mort tellement de fois que pour vous Satan en personne ne serais pas plus impressionnant qu''un caniche nain.'),
(41, 29, 'system', 'Lavette', 'Un gamin fait sauter un pétard 500 mètres plus loin, et vous êtes déjà les mains sur la tête et couché dans le caniveau le plus proche?'),
(42, 29, 'system', 'Zen', 'Le calme incarné, que vos détracteurs vous insulte à en perdre haleine si ça leur chante, vous vous contentez de bailler et passez votre chemin.'),
(43, 29, 'system', 'Furieux', 'Il vous en faut très peu pour vous mettre dans des états dingues, la moindre provocation vous rends totalement hors de contrôle? Vous seriez près à arracher la tête de votre maman pour peu qu''elle vous ai regarder de travers.'),
(44, 29, 'system', 'Présomptueux ', 'Vous savez ce que vous voulez: le pouvoir. Opportuniste, peu ou pas scrupuleux, la fin est plus importante que les moyens et vous êtes prêt à tout faire pour arriver à vos fins, avec ou sans discrétion, de toute façon vous êtes convaincu d?en être capable? Seulement vous en êtes bien le seul. '),
(45, 29, 'system', 'Loser', 'C''est pas tellement que vous êtes nul, c''est surtout que vous en êtes convaincu, votre manque d''initiative n''a d''égal que le peu de conviction que vous y mettez.'),
(46, 29, 'system', 'Optimiste', 'Votre vision des choses frise l''utopie. Il est très difficile de vous faire déprimer ou baisser les bras, et cet enthousiasme mal placé est souvent plus agaçant qu''autre chose.'),
(47, 29, 'system', 'Pessimiste', '« Monde de merde » est votre troisième phrase préférée après « on va tous crever » et « ça marchera jamais » vous êtes tellement démoralisant qu''on vous quitte souvent avec des envies de suicide.'),
(48, 29, 'system', 'Victime de la mode', 'Toutes vos économies passent en fringues, accessoires, bains moussants et autres breloques. Tout est bon pour être beau. Tout est bon pour être à la mode. Tout est bon pour être la star de la soirée et montrer que vous avez les moyens. Même si en réalité vous ne lez avez pas.'),
(49, 29, 'system', 'Clodo', 'Votre apparence est bien la dernière chose qui vous importe, vous exécrer la superficialité à en devenir repoussant.'),
(50, 0, 'system', 'Mental - Santé mentale', ''),
(51, 50, 'system', 'Phobie', 'Une peur qui vous hante depuis votre plus tendre enfance ou à la suite d''un choc traumatisant et qui, une fois face à elle, vous fait perdre tous vos moyens.\r\n(Handicap. A préciser, ainsi que la cause de traumatisme)'),
(52, 50, 'system', 'Fascination', 'Vous êtes en adoration devant certaines choses au point de perdre de vue le reste... une rock star, un type d''arme, des cheveux rouges, Roberto Malone... vous pourriez rester des heures à gober les mouches comme un gogol ou devenir hystérique comme une ménagère à un concert de Johnny Hallyday.\r\n(Handicap. A préciser)'),
(53, 50, 'system', 'Hypocondriaque', 'Ah! '),
(54, 50, 'system', 'Trouble Obsessionnel Compulsif', 'TOC: Une manie, un tic, une marotte, très énervant pour les autres, mais n''y a rien à faire, c''est plus fort que vous.'),
(55, 50, 'system', 'Amnésie', 'Et oui, vous ne vous souvenez absolument pas de votre passé avant une certaine date, rien à faire, pour vous c?est comme si le monde avait toujours été aussi merdique. N''est ce pas parfaitement vrai? En tout cas il y a de quoi se méfier... qui sait si ce passé aux oubliettes ne va pas vous rattraper?'),
(56, 50, 'system', 'Mémoire de poisson rouge', 'Cinq minutes? Quoi, déjà? Vous disiez quoi à l''instant et... de toute façon, qui êtes vous?'),
(57, 50, 'system', 'Illuminé', 'Une croyance, une idée, un point de vue... oui mais très développé, c''est devenu votre unique raison de vivre, vous en parlez tout le temps et y croyez dur comme fer.'),
(58, 50, 'system', 'Prédicateur', 'La fin du moooooonde, la fin du monde est proooooooche! Non, c''est vrai? N''auriez vous pas tendance à être obsédé par cette échéance toute proche? '),
(59, 50, 'system', 'Sommeil troublé', 'Réveils fréquents, cauchemars. le moins que l''on puisse dire c''est que vous dormez rarement beaucoup et presque jamais bien, à moins que certaines conditions bien particulières ne soient réunies. Bien sur vous êtes moins efficace et avez tendance à vous montrer à chier avec votre entourage... mais qui ne le serait pas à votre place, hein? Hein? HEIN?!'),
(60, 50, 'system', 'Autiste', 'Vous vivez dans votre bulle, renfermé sur vous même. Vous ne dialoguez que très peu ou alors à votre seule intention, et en réalité, on se demande même si vous êtes réellement doué de parole ou de raison.'),
(61, 50, 'system', 'Attardé sexuel', 'Votre jargon est digne d''un porno amateur boutonneux. Vos phrases sont remplies de ce champ lexical si gracieux, sans parler des les sous-entendus et autres stéréotypes que vous avez brillamment repêché dans les cassettes de porno les plus croustillantes de votre arrière grand père.'),
(62, 50, 'system', 'Frigide', 'Rien à faire, rien n''éveille véritablement votre passion, tant que c''est un tant soit peu charnel. Pendant que votre partenaire fait tout ce qu''il peut -si tant est qu''il ait pu parvenir aussi loin et qu''il soit doué d''autant de courage-, ses efforts ne vous font rien et vous préférez contempler le plafond d''un oeil blasé en bonne étoile de mer.'),
(63, 50, 'system', 'Peur du sexe opposé', 'Plus de caissiers masculins partout, voilà ce qui assurait à un parti des prochaines élections votre vote.'),
(64, 50, 'system', 'Paranoïaque', 'Si jamais vous sélectionnez cette caractéristiques, peut-être que plus personne voudra vous parler. Peut-être que vous allez être mis sous surveillance. Et si ce site n''était en fait qu''une conspiration gouvernementale pour vous piéger?'),
(65, 50, 'system', 'Boulimique', 'Miam!'),
(66, 50, 'system', 'Pyromane', 'Excusez-moi, vous auriez pas un peu de feu sur vous ? ... Merci! Dites-moi, cette maison, ce n''est pas la vôtre n''est-ce pas ?'),
(67, 50, 'system', 'Mythomane', 'En plus d''avoir soulevé d''une main la voiture pour sauver une vieille dame, de l''autre vous vous occupiez de faire la signalisation pour faire circuler les voitures. Un surhumain, vous? Non, juste un gros mytho.'),
(68, 50, 'system', 'Cleptomane', 'Ce n''est pas que vous n''avez pas d''argent sur vous, c''est que ce n''est pas le vôtre. Vous serez donc encore une fois contraint d''emprunter sans autorisation cette décoration en forme de bulle qui produit de la neige quand on la brasse vigoureusement: chose qui vous est réellement essentielle d''ailleurs.'),
(69, 0, 'system', 'Social - Concept/Emploi', ''),
(70, 69, 'system', 'Bidasse', 'Vous avez passé un certain temps dans l''armée, les forces de l''ordre, ou une quelconque milice. Gradé ou simple troufion, vous n''en avez pas moins conservé certaines connaissances et autres automatismes. (spécialité en arme à feu, armes lourdes, et athlétisme)'),
(71, 69, 'system', 'Mc Gyver', 'Un bout de ficèle, du sable et l''épingle à cheveux de votre grand-mère. En moins de temps qu''il n''en faut pour le dire vous avez sauvé le monde. Parfois saugrenues, vos idées lumineuses et autres bricolages instables tiennent du génie. Où se situe la frontière entre la folie et ce que vous faites? L''important c''est que ça marche, et dans votre domaine vous êtes l''as du bricolage. (spécialité en mécanique, électronique et chimie)'),
(72, 69, 'system', 'Ami de l''ombre', 'Vous votre truc c''est voler aux riches pour donner au pauvre... type que vous êtes. Infiltrer les poches ou les demeures de vos concitoyens est pour vous d''une simplicité enfantine.\r\nIl n''y a pas de sot métier, il y en a juste des plus légaux que d''autres.\r\n(spécialités recommandées: furtivité, crochetage, pickpocket)'),
(73, 69, 'system', 'Dr Frankenstein', 'It''s alive! It''s alive!'),
(74, 69, 'system', 'Moine Tibétain', 'Pour vous, le métier d''exterminateur est une abomination. Mais vous n''irez très certainement pas militer, car vous risqueriez de nuire à la vie de quelqu''un.\r\nPaix.'),
(75, 69, 'system', 'Geek', 'Si vous passez entre une jolie personne et une boutique d''informatique, pas la peine de vous demander de quelle couleur était ses cheveux. Enfin, sauf ont veux connaitre le code hexadécimal de la couleur, ou la taille en binaire.'),
(76, 0, 'system', 'Social - Traits sociaux', ''),
(77, 76, 'system', 'Minable', 'Vous n''êtes même pas digne d''inspirer la pitié, tout juste le clampin lambda de service, totalement insignifiant, essayez seulement de la ramener en public, et au mieux votre manque de charisme ne fera que sourire.'),
(78, 76, 'system', 'Réfugié du trou du cul du monde', '« Manger », « boire », « merci », c''est tout ce que vous connaissez car votre langue maternelle c''est le moldave? Vous serez vite pris pour l''abruti de service, sauf si un personnage bilingue est la pour traduire.'),
(79, 76, 'system', 'Terrible secret', 'Du mono-testiculaire au mafieux repentis, il y''a certaines choses que vous devez absolument dissimuler sous peine d''avoir des ennuis ou d''être la risée de tous. C''est affreux, c''est infâme, et même si ce n''est pas si grave après tout, vous en tout cas vous en faites une montagne. Dès qu''une discussion s''en approche vous devenez nerveux voir irritable.'),
(80, 76, 'system', 'Recherché', 'A tord ou a raison vous vous êtes mit a dos des gens qui n''hésiteraient pas à vous cribler de balles à l''instant même ou vous retrouvez devant eux.'),
(81, 76, 'system', 'Paria', 'Exclu(e) de la société, pour X raison vous n''obtenez guère plus de considération qu''un déchet toxique dont elle aimerait se débarrasser. Mais c''est réciproque, alors que tous ces connards conformistes aillent se faire foutre !'),
(82, 76, 'system', 'Bourgeois', 'Vous visez généralement les hautes sphères de la société. Rapidement, vous trouver avec qui entrer en contact, et vous arrivez à intégrer la bourgeoisie de l''endroit ou vous êtes.'),
(83, 76, 'system', 'Magnétisme animal', 'On se retourne dans la rue dès votre arrivée, vous avez ce petit quelque chose de sauvage, intensément sensuel. Est ce une odeur? Une façon de marcher? De quoi agiter les hormones du sexe opposé... à vos risques et périls.'),
(84, 76, 'system', 'Présence inquiétante', 'Une aura maléfique ou simplement dérangeante, vous êtes extrêmement intimidant, mais en contrepartie, les gens auront du mal à vous faire confiance. Que les passants moyen se retournent sur votre passage ou pressent le pas, votre vue ne laisse jamais indifférent.');

--
-- Contenu de la table `cc_caract_incompatible`
--

INSERT INTO `cc_caract_incompatible` (`id1`, `id2`) VALUES
(3, 2),
(4, 5),
(7, 6),
(9, 10),
(4, 12),
(2, 13),
(3, 13),
(9, 13),
(15, 16),
(32, 33),
(74, 33),
(34, 35),
(41, 43),
(42, 43),
(45, 46),
(47, 46),
(48, 49);

--
-- Contenu de la table `cc_competence`
--

INSERT INTO `cc_competence` (`id`, `abbr`, `nom`, `description`, `efface`, `inscription`) VALUES
(1, 'acro', 'Acrobatie', 'Pour les monte-en-l''air qui aiment les arts du cirque, ou plus simplement s''inviter par la fenêtre chez leur victime.', '1', '1'),
(2, 'armb', 'Armes blanches', 'La faux, le sabre, le poignard, la machette, le kriss, l''épée, le glaive, la serpe, ou encore le stylet, bref toutes ces choses qui tranchent ou perforent ça vous plaît !', '0', '1'),
(3, 'armc', 'Corps à corps', 'Gauche, droite, gauche, droite... Vous, vous aimez la sueur, le ring et le sac de sable. A moins que les combats de rue plus libres conviennent mieux à votre âme de tête brulée.', '0', '1'),
(4, 'armf', 'Armes à feu', 'Que ça soit la vieille arquebuse de votre arrière-arrière-arrière grand-père ou le dernier Uzi du marché en passant par le petit calibre que l''on peut trouver aux puces, tout ce qui fait plein de trous et de bruits vous maitrisez.', '0', '1'),
(5, 'arml', 'Armes lourdes', 'Aaaah je vous sens tout excité à l''idée de pouvoir attaquer tout un régiment avec votre mitrailleuse Gatling. Ou peut-être que vous souhaitez faire exploser la voiture du chef de la Police avec votre lance-roquette ?', '0', '1'),
(6, 'armu', 'Armurier', 'Nettoyer une arme, changer ses pièces, fabriquer ses propres munitions, tout ça c''est votre rayon! D''ailleurs, vicieux comme vous êtes, vous allez sans doute élaborer un tout nouveau type de munitions qui transpercera le blindage comme du beurre.', '0', '1'),
(7, 'arti', 'Artisanat', 'Confectionner des bottes, des chapeaux de pailles, des bijoux ou encore des pots en terre cuite que vous pourrez balancer sur les punks, une chose est sûre : vos mains, vous savez vous en servir !', '1', '1'),
(8, 'athl', 'Athletisme', 'Le cent mètres en 9,70s ? Semer la police à pattes ? Tout ça, c''est dans vos cordes.', '1', '1'),
(9, 'chim', 'Chimie', 'Qu''allez-vous concocter ? De l''acide ? Un truc pour déboucher les toilettes du bar d''à côté ? Ouvrir un laboratoire pour élaborer de nouvelles drogues ?', '1', '1'),
(10, 'chrg', 'Chirurgie', 'Le scalpel vous le maitrisez, mais retirer une balle à côté du coeur de votre patient dans une cave demande un peu plus de sang-froid.', '1', '1'),
(11, 'croc', 'Crochetage', 'Plus aucune serrure n''aura de secret pour vous... surtout pas celle de votre voisin qui a cette magnifique TV HD.', '1', '1'),
(12, 'cryp', 'Cryptage', 'Vous ce n''est pas les clefs physiques mais virtuelles qui vous branchent, et vous savez aussi bien verrouiller l''accès à vos petits secrets que déverrouiller ceux des autres.', '1', '1'),
(13, 'cuis', 'Cuisine', 'Vos ratas auront du succès, même si vos ingrédients de base laisseront parfois à désirer...', '1', '1'),
(14, 'cybr', 'Cybernetique', 'Le Corps est un organe dérangeant, seuls l''Esprit et la Machine sont immortels.', '1', '1'),
(15, 'drsg', 'Dressage', 'Votre côté dominateur se voit parfaitement, et vous savez le faire sentir aux bêtes.', '1', '1'),
(16, 'elec', 'Electronique', 'Les circuits imprimés n''ont pas de secret pour vous, vous adorez jouer aux labyrinthes avec, en retrouvant la sortie, et à l''occasion vous les soudez même ensemble pour faire marcher tout un tas d''appareils tout à fait indispensables.', '1', '1'),
(17, 'ensg', 'Enseignement', 'Vous aimez les réformes ? Un emploi instable ne vous fait pas plus peur qu''une classe de petits monstres incultes ? Alors ce métier est fait pour vous.', '1', '1'),
(18, 'esqv', 'Esquive', 'Prendre des gnons c''est pas votre truc, vous préférez épuiser votre adversaire en évitant chacun de ses coups.', '0', '1'),
(19, 'expl', 'Explosifs', 'Dynamite, C4, semtex, TNT, nitroglycérine, tout ça vous connaissez mais il peut s''avérer difficile de s''en procurer dans le Dôme. Alors place aux autres joyeusetés comme des bombes artisanales encore méconnues des services de Police !', '0', '1'),
(20, 'forg', 'Forge', 'Le feu et le métal. Revenez à la source de toute civilisation en façonnant de vos mains les objets de la nouvelle ère.', '0', '1'),
(21, 'frtv', 'Furtivité', 'Se déplacer sans trébucher sur une bouteille de bière qui traine par terre est tout un art dans le Dôme.', '1', '1'),
(22, 'gene', 'Genetique', 'Pour les frankenstein en herbe qui aiment jouer à Dieu le père avec des macaques à multi-rectum... chacun ses lubies.', '1', '1'),
(23, 'hckg', 'Hacking', 'Vous adorez fouiner dans les ordinateurs des autres ou tout simplement prendre votre pied en regardant la tronche des flics quand vous piratez leur serveur.', '1', '1'),
(24, 'hrdw', 'Hardware', 'Le matériel informatique ça vous connait, d''ailleurs vous étiez justement en train de fabriquer votre propre système, juste pour rigoler un coup avec vos potes.', '1', '1'),
(25, 'lncr', 'Lancer', 'Reproduisez Mai 68 en jetant les pavés du Dôme sur la tête de ceux qui ne vous reviennent pas !', '0', '1'),
(26, 'meca', 'Mécanique', 'Le cambouis et l''huile ne vous font pas peur, vous adorez les mécanismes qui tournent ronds, la clef de douze est votre amie... Que dire de plus ?', '1', '1'),
(27, 'mrch', 'Marchandage', 'Baissez systématiquement le prix de vos achats en prétendant que vous avez une famille très nombreuse et qu''après tout les articles de votre vendeur ne sont pas aussi bien qu''il le prétend.', '1', '1'),
(28, 'pckp', 'Pickpocket', 'Glisser habilement ses doigts dans les poches des passants sans se faire sectionner la main n''est pas donné à tout le monde.', '0', '1'),
(29, 'pltg', 'Pilotage', 'Vous ferez corps avec tout ce qui fait vroum, teuf teuf ou tougoudougoudou.', '1', '1'),
(30, 'prog', 'Programmation', 'Geeks, transformez votre vice en qualité.', '1', '1'),
(31, 'psyc', 'Psychiatrie', 'Soigner les fous et les déficients mentaux ça vous passionne. Vous allez surement adorer les récits du dingue qui a découpé un enfant de quatre ans en rondelles avant de s''en servir comme garniture pour sa soirée crêpe.', '1', '1'),
(32, 'scrs', 'Premiers secours', 'Faire du bouche à bouche à la jolie blonde qui vient de faire un malaise ou compresser les plaies d''un passant qui n''a pas eu de chance en rencontrant une machette, oui, oui, tout ça vous aimez et le maitrisez.', '1', '1'),
(33, 'toxi', 'Toxicologie', 'Étudier, c''est votre maitre mot. Vous voulez tout connaitre sur le poison de manière générale : comment en fabriquer, les effets sur l''organisme, le moyen de le détecter et bien entendu comment le combattre.', '1', '1');


--
-- Contenu de la table `cc_stat`
--

INSERT INTO `cc_stat` (`id`, `abbr`, `nom`, `description`) VALUES
(1, 'agi', 'Agilité', 'La capacité de votre personnage à se mouvoir avec aisance, escalader les gouttières et survivre au Kamasutra.'),
(2, 'dex', 'Dextérité', 'Représente la faculté de votre personnage à savoir faire quelque chose de ses dix doigts. Que ce soit pour réaliser une opération de chirurgie cardiaque avec une cuillère, ou réparer son ordinateur.'),
(3, 'for', 'Force', 'Capacité nervo-musculaire exercée dans tout rapport physique avec votre environnement. Elle détermine la tonicité de votre personnage et lui permet de soulever cette pétoire de rêve qui crache 1000 balles à la minute.'),
(4, 'int', 'Intelligence', 'Ce qui fait la différence entre une carotte et Einstein. Faculté de votre personnage à analyser et déduire d''une situation problématique pour la résoudre.'),
(5, 'per', 'Perception', '"- C''est quoi ce bruit ? - Rien, ta gueule. - C''est quoi cette odeur ? - J''ai mangé un kebab ce midi." Niveau de sensibilité de votre personnage à son environnement. Mais on dit que la perte de certains sens peut amener à développer ceux restants... ');

SET FOREIGN_KEY_CHECKS=1;



--
-- Contenu de la table cc_item_db
--

INSERT INTO `cc_item_db` (`db_id`, `db_type`, `db_soustype`, `db_regrouper`, `db_nom`, `db_desc`, `db_valeur`, `db_img`, `db_pr`, `db_pn`, `db_force`, `db_portee`, `db_tir_par_tour`, `db_fiabilite`, `db_precision`, `db_capacite`, `db_pass`, `db_forumaccess`, `db_masque`, `db_seuilresistance`, `db_resistance`, `db_duree`, `db_shock_pa`, `db_shock_pv`, `db_boost_pa`, `db_boost_pv`, `db_perc_stat_agi`, `db_perc_stat_dex`, `db_perc_stat_per`, `db_perc_stat_for`, `db_perc_stat_int`, `db_internet`, `db_mcread`, `db_mcwrite`, `db_memoire`, `db_afficheur`, `db_anonyme`, `db_param`, `db_notemj`) VALUES ('8', 'livre', 'aucun', '0', 'Livre: Notice d\'inscription;', '', '0', 'SYS_none.gif', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Bienvenu sur CyberCity ! Ceci est un texte d\'introduction qui peut être modifié via le panel MJ en tant qu\'item de type Livre.', '');
INSERT INTO `cc_item_db` (`db_id`, `db_type`, `db_soustype`, `db_regrouper`, `db_nom`, `db_desc`, `db_valeur`, `db_img`, `db_pr`, `db_pn`, `db_force`, `db_portee`, `db_tir_par_tour`, `db_fiabilite`, `db_precision`, `db_capacite`, `db_pass`, `db_forumaccess`, `db_masque`, `db_seuilresistance`, `db_resistance`, `db_duree`, `db_shock_pa`, `db_shock_pv`, `db_boost_pa`, `db_boost_pv`, `db_perc_stat_agi`, `db_perc_stat_dex`, `db_perc_stat_per`, `db_perc_stat_for`, `db_perc_stat_int`, `db_internet`, `db_mcread`, `db_mcwrite`, `db_memoire`, `db_afficheur`, `db_anonyme`, `db_param`, `db_notemj`) VALUES ('6', 'livre', 'aucun', '0', 'Livre: Règles Hors-Jeu;', '', '0', 'SYS_none.gif', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ceci sont les règles Hors Jeu qui peuvent être modifiées via le panel MJ en tant qu\'item de type Livre.', '');
INSERT INTO `cc_item_db` (`db_id`, `db_type`, `db_soustype`, `db_regrouper`, `db_nom`, `db_desc`, `db_valeur`, `db_img`, `db_pr`, `db_pn`, `db_force`, `db_portee`, `db_tir_par_tour`, `db_fiabilite`, `db_precision`, `db_capacite`, `db_pass`, `db_forumaccess`, `db_masque`, `db_seuilresistance`, `db_resistance`, `db_duree`, `db_shock_pa`, `db_shock_pv`, `db_boost_pa`, `db_boost_pv`, `db_perc_stat_agi`, `db_perc_stat_dex`, `db_perc_stat_per`, `db_perc_stat_for`, `db_perc_stat_int`, `db_internet`, `db_mcread`, `db_mcwrite`, `db_memoire`, `db_afficheur`, `db_anonyme`, `db_param`, `db_notemj`) VALUES ('4', 'livre', 'aucun', '0', 'Livre: Texte d\'introduction;', '', '0', 'SYS_none.gif', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ceci est le texte d\'introduction qui peut être modifié via le panel MJ en tant qu\'item de type Livre.', '');
INSERT INTO `cc_item_db` (`db_id`, `db_type`, `db_soustype`, `db_regrouper`, `db_nom`, `db_desc`, `db_valeur`, `db_img`, `db_pr`, `db_pn`, `db_force`, `db_portee`, `db_tir_par_tour`, `db_fiabilite`, `db_precision`, `db_capacite`, `db_pass`, `db_forumaccess`, `db_masque`, `db_seuilresistance`, `db_resistance`, `db_duree`, `db_shock_pa`, `db_shock_pv`, `db_boost_pa`, `db_boost_pv`, `db_perc_stat_agi`, `db_perc_stat_dex`, `db_perc_stat_per`, `db_perc_stat_for`, `db_perc_stat_int`, `db_internet`, `db_mcread`, `db_mcwrite`, `db_memoire`, `db_afficheur`, `db_anonyme`, `db_param`, `db_notemj`) VALUES ('7', 'livre', 'aucun', '0', 'Livre: Texte création de perso;', '', '0', 'SYS_none.gif', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ceci est le texte de création de perso qui peut être modifié via le panel MJ en tant qu\'item de type Livre.', '');
INSERT INTO `cc_item_db` (`db_id`, `db_type`, `db_soustype`, `db_regrouper`, `db_nom`, `db_desc`, `db_valeur`, `db_img`, `db_pr`, `db_pn`, `db_force`, `db_portee`, `db_tir_par_tour`, `db_fiabilite`, `db_precision`, `db_capacite`, `db_pass`, `db_forumaccess`, `db_masque`, `db_seuilresistance`, `db_resistance`, `db_duree`, `db_shock_pa`, `db_shock_pv`, `db_boost_pa`, `db_boost_pv`, `db_perc_stat_agi`, `db_perc_stat_dex`, `db_perc_stat_per`, `db_perc_stat_for`, `db_perc_stat_int`, `db_internet`, `db_mcread`, `db_mcwrite`, `db_memoire`, `db_afficheur`, `db_anonyme`, `db_param`, `db_notemj`) VALUES ('9', 'livre', 'aucun', '0', 'Livre: Notice d\'inscription;', '', '0', 'SYS_none.gif', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Bienvenu sur CyberCity ! Ceci le message d\'accueil dans les HE qui peut être modifié via le panel MJ en tant qu\'item de type Livre.', '');