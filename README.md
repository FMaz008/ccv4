# CyberCity 2034 V4.0

Ceci est le code source pour CyberCity 2034 V4.0. le commit initial de ce repository correspond à la version 652 anciennement sous SVN.<br />
Ce code est basé sur l'ancienne révision 652 datant du départ de Francois Mazerolle (moi).<br />
<br />
Libre à vous de vous amuser avec et à lancer vos propres jeux !

# Installation:
1) Copier les fichiers<br />
2) Renommer const.inc.php_template en const.inc.php<br />
3) Configurer const.inc.php<br />
4) Importer la structure de base de données du fichier db_structure_only.sql<br />
5) Importer les données de base du fichier db_basic_data.sql<br />
--> L'utilisateur test (mot de passe test) sera créé.<br />
6) Avant de créer des personnages, créer au moins 1 lieu, et configurer les valeurs suivante de const.inc.php:
- LIEU_DEPART
- INNACTIVITE_TELEPORT_LOCATION
- INNACTIVITE_VOLUNTARY_LOCATION

<br />
Si vous avez des problèmes, contactez-moi directement au email indiqué dans mes commits.<br />
<br />

# Thèmes/Skin:
Pour développer un nouveau skin, il suffit de créer un nouveau dossier dans /tpl/.
Lorsque le moteur tente d'utiliser un fichier de votre skin, si le fichier n'existe pas dans votre skin, le moteur chargera le fichier dans le skin par défaut (dark_blue).
Autrement dit, il vous suffit de copier les fichiers de dark_blue que vous souhaitez modifier dans le dossier de votre skin. Assurez-vous de conserver l'arborescence des dossiers.
J'ai laissé cyber_rust pour que vous ayez un exemple de skin additionel.
Pour clarifier, si vous supprimez cyber_rust, dark_blue fonctionnera, mais à l'inverse, si vous supprimez dark_blue, cyber_rust ne fonctionnera pas, puisqu'il contient uniquement les fichiers différents de dark_blue.

