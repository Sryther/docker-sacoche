DROP TABLE IF EXISTS sacoche_livret_element_item;

CREATE TABLE sacoche_livret_element_item (
  livret_element_item_id  SMALLINT(4)  UNSIGNED                NOT NULL DEFAULT 0,
  livret_element_theme_id SMALLINT(3)  UNSIGNED                NOT NULL DEFAULT 0,
  livret_element_item_nom VARCHAR(185) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (livret_element_item_id),
  KEY livret_element_theme_id (livret_element_theme_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_livret_element_item DISABLE KEYS;

INSERT INTO sacoche_livret_element_item (livret_element_item_id, livret_element_theme_id, livret_element_item_nom) VALUES

-- Cycle 2 - Enseignement moral et civique - Tous niveaux

(   1,   1, "Être capable d'écoute"),
(   2,   1, "Accepter les différences"),
(   3,   1, "Connaître et respecter les règles de vie de la classe et de l’école"),
(   4,   1, "Identifier les symboles de la République présents dans l’école"),
(   5,   1, "Mettre en œuvre les règles de la communication dans un débat"),
(   6,   1, "Prendre des responsabilités dans la classe et dans l’école"),
(   7,   1, "Savoir coopérer"),

-- Cycle 2 - Éducation physique et sportive - Tous niveaux

(  11,  11, "Natation"),
(  12,  11, "Activités de roule (vélo, roller, …)"),
(  13,  11, "Activités nautiques"),
(  14,  11, "Parcours d’orientation"),
(  15,  11, "Parcours d’escalade"),
(  16,  11, "Se déplacer dans l’eau sur une quinzaine de mètres sans appui et après un temps d’immersion"),
(  17,  11, "Réaliser un parcours en adaptant ses déplacements à un environnement inhabituel dans un espace inhabituel et sécurisé"),
(  18,  11, "Respecter les règles de sécurité qui s’appliquent"),

(  21,  12, "Jeux traditionnels"),
(  22,  12, "Jeux collectifs avec ballon"),
(  23,  12, "Jeux de combat"),
(  24,  12, "Jeux de raquettes"),
(  25,  12, "S’engager dans un affrontement individuel ou collectif en respectant les règles du jeu"),
(  26,  12, "Contrôler son engagement moteur et affectif"),
(  27,  12, "Connaitre le but du jeu"),
(  28,  12, "Reconnaitre ses partenaires et ses adversaires"),

(  31,  13, "Activités athlétiques"),
(  32,  13, "Courir vite et courir longtemps"),
(  33,  13, "Lancer loin et lancer précis"),
(  34,  13, "Sauter haut et sauter loin"),
(  35,  13, "Remplir quelques rôles spécifiques (chronométreur, starter par exemple)"), 

(  41,  14, "Danse"),
(  42,  14, "Activités gymniques"),
(  43,  14, "Arts du cirque"),
(  44,  14, "Mémoriser et reproduire avec son corps une séquence simple d’actions"),
(  45,  14, "Inventer et présenter une séquence simple d’actions"),

-- Cycle 2 - Enseignements artistiques - Tous niveaux

(  51,  21, "Expérimenter, produire, créer des productions plastiques de natures diverses"),
(  52,  21, "Mettre en œuvre un projet artistique individuel ou collectif"),
(  53,  21, "S’exprimer, analyser sa pratique, celle de ses pairs ; établir une relation avec celle des artistes, s’ouvrir à l’altérité"),
(  54,  21, "Se repérer dans les domaines liés aux arts plastiques, connaître et comparer quelques œuvres d’art"),

(  61,  22, "Chanter une mélodie simple, une comptine ou un chant avec une intonation juste"),
(  62,  22, "Écouter, comparer des éléments sonores, des musiques"),
(  63,  22, "Explorer, imaginer des représentations diverses de musiques"),
(  64,  22, "Échanger, partager ses émotions, exprimer ses préférences"),

-- Cycle 2 - Français - Tous niveaux

(  71,  31, "Écouter pour comprendre des messages oraux ou des textes lus par un adulte"),
(  72,  31, "Dire pour être entendu et compris"),
(  73,  31, "Participer à des échanges dans des situations diversifiées"),
(  74,  31, "Adopter une attitude critique par rapport au langage produit"),

(  81,  32, "Identifier des mots de manière de plus en plus aisée"),
(  82,  32, "Comprendre un texte"),
(  83,  32, "Pratiquer différentes formes de lecture"),
(  84,  32, "Lire à voix haute"),
(  85,  32, "Contrôler sa compréhension"),

(  91,  33, "Copier de manière experte"),
(  92,  33, "Produire des écrits"),
(  93,  33, "Réviser et améliorer l’écrit qu’on a produit"),

( 101,  34, "Maitriser les relations entre l’oral et l’écrit"),
( 102,  34, "Mémoriser et se remémorer l’orthographe de mots fréquents et de mots irréguliers dont le sens est connu"),
( 103,  34, "Identifier les principaux éléments d’une phrase simple"),
( 104,  34, "Raisonner pour résoudre des problèmes orthographiques, d’accord essentiellement"),
( 105,  34, "Comprendre comment se forment les verbes et orthographier les formes verbales les plus fréquentes"),
( 106,  34, "Identifier des relations entre les mots, entre les mots et leur contexte d’utilisation ; s’en servir pour mieux comprendre"),
( 107,  34, "Enrichir son répertoire de mots, les mémoriser et les réutiliser"),

-- Cycle 2 - Langues vivantes - Tous niveaux

( 111,  41, "Comprendre des mots familiers et des expressions très courantes au sujet de soi, de sa famille et de l'environnement concret et immédiat, si les gens parlent lentement et distinctement"),

( 121,  42, "Utiliser des expressions et des phrases simples pour se décrire, décrire le lieu d'habitation et les gens de l’entourage"),

( 131,  43, "Poser des questions simples sur des sujets familiers ou sur ce dont on a immédiatement besoin, ainsi que répondre à de telles questions"),

( 141,  44, "Identifier quelques grands repères culturels de l’environnement quotidien des élèves du même âge dans les pays ou régions étudiés"),

-- Cycle 2 - Mathématiques - Tous niveaux

( 151,  51, "Comprendre et utiliser des nombres entiers pour dénombrer, ordonner, repérer, comparer"),
( 152,  51, "Nommer, lire, écrire, représenter des nombres entiers"),
( 153,  51, "Résoudre des problèmes en utilisant des nombres entiers et le calcul"),
( 154,  51, "Calculer avec des nombres entiers"),

( 161,  52, "Comparer, estimer, mesurer des longueurs, des masses, des contenances, des durées"),
( 162,  52, "Utiliser le lexique, les unités, les instruments de mesures spécifiques de ces grandeurs"),
( 163,  52, "Résoudre des problèmes impliquant des longueurs, des masses, des contenances, des durées, des prix"),

( 171,  53, "(Se) repérer et (se) déplacer en utilisant des repères et des représentations"),
( 172,  53, "Reconnaitre, nommer, décrire, reproduire quelques solides"),
( 173,  53, "Reconnaitre, nommer, décrire, reproduire, construire quelques figures géométriques"),
( 174,  53, "Reconnaitre et utiliser les notions d’alignement, d’angle droit, d’égalité de longueurs, de milieu, de symétrie"),

-- Cycle 2 - Questionner le monde - Tous niveaux

( 181,  61, "Connaitre des caractéristiques du monde vivant, ses interactions, sa diversité"),
( 182,  61, "Reconnaitre des comportements favorables à sa santé"),

( 191,  62, "Identifier les trois états de la matière et observer des changements d’états"),
( 192,  62, "Identifier un changement d’état de l’eau dans un phénomène de la vie quotidienne"),

( 201,  63, "Comprendre la fonction et le fonctionnement d’objets fabriqués"),
( 202,  63, "Réaliser quelques objets et circuits électriques simples, en respectant des règles élémentaires de sécurité"),
( 203,  63, "Commencer à s’approprier un environnement numérique"),

( 211,  64, "Se repérer dans l’espace et le représenter"),
( 212,  64, "Situer un lieu sur une carte, sur un globe ou sur un écran informatique"),

( 221,  65, "Se repérer dans le temps et mesurer des durées"),
( 222,  65, "Repérer et situer quelques évènements dans un temps long"),

( 231,  66, "Comparer quelques modes de vie des hommes et des femmes, et quelques représentations du monde"),
( 232,  66, "Comprendre qu’un espace est organisé"),
( 233,  66, "Identifier des paysages"),

-- Cycle 3 - Enseignement moral et civique - CM1-CM2

( 241,  71, "Exprimer en les régulant ses émotions et ses sentiments"),
( 242,  71, "Respecter autrui et accepter les différences"),
( 243,  71, "Les droits et les devoirs de l’élève, du citoyen"),
( 244,  71, "Les principes et les valeurs de la République française"),
( 245,  71, "Adapter son comportement et son attitude à différents contextes et d’obéissance aux règles"),
( 246,  71, "Argumenter et justifier son point de vue dans un débat ou une discussion sur les valeurs"),
( 247,  71, "Exposer son point de vue dans un débat en respectant le point de vue des autres"),
( 248,  71, "La responsabilité face aux usages de l’informatique et d’internet"),
( 249,  71, "Prendre des responsabilités dans la classe et dans l’école"),
( 250,  71, "Nuancer son point de vue en tenant compte du point de vue des autres"),
( 251,  71, "S'engager dans la réalisation d’un projet collectif (projet de classe, d’école, communal, national …)"),

-- Cycle 3 - Éducation physique et sportive - CM1-CM2

( 261,  81, "Parcours d’orientation"),
( 262,  81, "Savoir nager"),
( 263,  81, "Parcours d’escalade"),
( 264,  81, "Activités nautiques"),
( 265,  81, "Activités de roule (vélo, roller, …)"),
( 266,  81, "Réaliser, seul ou à plusieurs, un parcours dans plusieurs environnements inhabituels"),
( 267,  81, "Connaitre et respecter les règles de sécurité"),
( 268,  81, "Identifier la personne responsable à alerter ou la procédure en cas de problème"),
( 269,  81, "Valider l'attestation scolaire du savoir nager (ASSN)"),

( 271,  82, "Jeux traditionnels"),
( 272,  82, "Jeux collectifs avec ballon"),
( 273,  82, "Jeux de combat"),
( 274,  82, "Jeux de raquettes"),
( 275,  82, "S'organiser tactiquement pour rechercher le gain du match (ou du combat)"),
( 276,  82, "Respecter les partenaires, les adversaires et l'arbitre"),
( 277,  82, "Assurer différents rôles sociaux (joueur, arbitre, observateur)"),
( 278,  82, "Accepter le résultat de la rencontre"),

( 281,  83, "Activités athlétiques"),
( 282,  83, "Natation"),
( 283,  83, "Combiner une course, un saut, un lancer pour faire une meilleure performance cumulée"),
( 284,  83, "Mesurer et quantifier les performances, les enregistrer, les comparer, les classer, les traduire en représentations graphiques"),
( 285,  83, "Assumer les rôles de chronométreur et d'observateur"),

( 291,  84, "Danse"),
( 292,  84, "Activités gymniques"),
( 293,  84, "Arts du cirque"),
( 294,  84, "Réaliser en petits groupes une séquence acrobatique ou à visée artistique"),
( 295,  84, "Savoir filmer une prestation pour la revoir et la faire évoluer"),
( 296,  84, "Respecter les prestations des autres et accepter de se produire devant les autres"),

-- Cycle 3 - Enseignements artistiques - CM1-CM2

( 301,  91, "Expérimenter, produire, créer des productions plastiques de natures diverses"),
( 302,  91, "Mettre en œuvre un projet artistique individuel ou collectif"),
( 303,  91, "S’exprimer, analyser sa pratique, celle de ses pairs ; établir une relation avec celle des artistes, s’ouvrir à l’altérité"),
( 304,  91, "Se repérer dans les domaines liés aux arts plastiques, connaître et comparer quelques œuvres d’art"),

( 311,  92, "Chanter et interpréter une mélodie simple avec une intonation juste et avec expressivité"),
( 312,  92, "Écouter, comparer et commenter des éléments sonores d’origines diverses"),
( 313,  92, "Explorer, imaginer et créer des représentations diverses de musiques"),
( 314,  92, "Échanger, partager et argumenter ses choix et ses préférences"),

( 321,  93, "Donner un avis argumenté sur ce que représente ou exprime une œuvre d’art"),
( 322,  93, "Dégager d’une œuvre d’art, par l’observation ou l’écoute, ses principales caractéristiques techniques et formelles"),
( 323,  93, "Relier des caractéristiques d’une œuvre d’art à des usages ainsi qu’au contexte historique et culturel de sa création"),
( 324,  93, "Savoir se repérer dans un musée, dans un lieu d’art, un site patrimonial"),

-- Cycle 3 - Français - CM1-CM2

( 331, 101, "Écouter pour comprendre un message oral, un propos, un discours, un texte lu"),
( 332, 101, "Parler en prenant en compte son auditoire"),
( 333, 101, "Participer à des échanges dans des situations diversifiées"),
( 334, 101, "Adopter une attitude critique par rapport au langage produit"),

( 341, 102, "Lire avec fluidité"),
( 342, 102, "Comprendre un texte littéraire et l'interpréter"),
( 343, 102, "Comprendre des textes, des documents et des images et les interpréter"),
( 344, 102, "Contrôler sa compréhension, être un lecteur autonome"),

( 351, 103, "Écrire à la main de manière fluide et efficace"),
( 352, 103, "Écrire avec un clavier rapidement et efficacement"),
( 353, 103, "Recourir à l'écriture pour réfléchir et pour apprendre"),
( 354, 103, "Produire des écrits variés"),
( 355, 103, "Réécrire à partir de nouvelles consignes ou faire évoluer son texte"),
( 356, 103, "Prendre en compte les normes de l'écrit pour formuler, transcrire et réviser"),

( 361, 104, "Maitriser les relations entre l'oral et l'écrit"),
( 362, 104, "Acquérir la structure, le sens et l'orthographe des mots"),
( 363, 104, "Maitriser la forme des mots en lien avec la syntaxe"),
( 364, 104, "Observer le fonctionnement du verbe et l'orthographier"),
( 365, 104, "Identifier les éléments d'une phrase simple en relation avec son sens ; distinguer phrase simple et phrase complexe"),

-- Cycle 3 - Histoire-Géographie - CM1

( 371, 110, "Histoire : Et avant la France ?"),
( 372, 110, "Histoire : Le temps des rois"),
( 373, 110, "Histoire : Le temps de la Révolution et de l’Empire"),

( 381, 111, "Géographie : Découvrir le(s) lieu(x) où j’habite"),
( 382, 111, "Géographie : Se loger, travailler, se cultiver, avoir des loisirs en France"),
( 383, 111, "Géographie : Consommer en France"),

-- Cycle 3 - Histoire-Géographie - CM2

( 391, 112, "Histoire : Le temps de la République"),
( 392, 112, "Histoire : L’âge industriel en France"),
( 393, 112, "Histoire : La France, des guerres mondiales à l’Union européenne"),

( 401, 113, "Géographie : Se déplacer"),
( 402, 113, "Géographie : Communiquer d’un bout à l’autre du monde grâce à internet"),
( 403, 113, "Géographie : Mieux habiter"),

-- Cycle 3 - Histoire-Géographie - CM1-CM2

( 411, 114, "Situer des grandes périodes historiques"),
( 412, 114, "Ordonner des faits et les situer"),
( 413, 114, "Utiliser des documents"),
( 414, 114, "Mémoriser et mobiliser ses repères historiques"),

( 421, 115, "Nommer et localiser les grands repères géographiques"),
( 422, 115, "Nommer, localiser un lieu dans un espace géographique"),
( 423, 115, "Appréhender la notion d’échelle géographique"),
( 424, 115, "Mémoriser et mobiliser ses repères géographiques"),

( 431, 116, "Poser et se poser des questions"),
( 432, 116, "Formuler des hypothèses"),
( 433, 116, "Vérifier"),
( 434, 116, "Justifier"),

( 441, 117, "Connaitre et utiliser différents systèmes d’information"),
( 442, 117, "Trouver, sélectionner et exploiter des informations dans une ressource numérique"),
( 443, 117, "Identifier la ressource numérique utilisée"),

( 451, 118, "Comprendre le sens général d’un document"),
( 452, 118, "Identifier le document et savoir pourquoi il doit être identifié"),
( 453, 118, "Extraire des informations pertinentes"),
( 454, 118, "Savoir que le document exprime un point de vue, identifier et questionner le sens implicite d’un document"),

( 461, 119, "Écrire pour structurer sa pensée, argumenter et écrire pour communiquer"),
( 462, 119, "Reconnaître un récit historique"),
( 463, 119, "S’exprimer à l’oral"),
( 464, 119, "S’approprier et utiliser un lexique historique et géographique"),
( 465, 119, "Réaliser des productions"),
( 466, 119, "Utiliser des cartes"),

( 471, 120, "Organiser son travail dans le cadre d’un groupe"),
( 472, 120, "Travailler en commun"),
( 473, 120, "Utiliser les outils numériques dans le travail collectif"),

-- Cycle 3 - Langues vivantes - CM1-CM2

( 481, 121, "Écouter et comprendre des messages oraux simples relevant de la vie quotidienne, des histoires simples"),
( 482, 121, "Mémoriser des mots, des expressions courantes"),
( 483, 121, "Utiliser des indices sonores et visuels pour déduire le sens de mots inconnus, d’un message"),

( 491, 122, "Utiliser le contexte, les illustrations et les connaissances pour comprendre un texte"),
( 492, 122, "Reconnaitre des mots isolés dans un énoncé, un court texte"),
( 493, 122, "S’appuyer sur des mots outils, des structures simples, des expressions rituelles"),
( 494, 122, "Percevoir la relation entre certains graphèmes et phonèmes spécifiques à la langue"),

( 501, 123, "Mémoriser et reproduire des énoncés"),
( 502, 123, "S’exprimer de manière audible, en modulant débit et voix"),
( 503, 123, "Participer à des échanges simples pour être entendu et compris dans quelques situations diversifiées de la vie quotidienne"),

( 511, 124, "Écrire des mots et des expressions dont l’orthographe et la syntaxe ont été mémorisés"),
( 512, 124, "Écrire des phrases en s’appuyant sur un modèle connu"),

( 521, 125, "Poser des questions simples"),
( 522, 125, "Mobiliser des énoncés dans des échanges simples et fréquents"),
( 523, 125, "Utiliser des procédés très simples pour commencer, poursuivre et terminer une conversation brève"),

( 531, 126, "Identifier quelques grands repères culturels de l’environnement quotidien des élèves du même âge dans les pays ou régions étudiés"),
( 532, 126, "Mobiliser ses connaissances culturelles pour décrire un personnage, un lieu ou pour raconter un fait, un événement"),

-- Cycle 3 - Mathématiques - CM1-CM2

( 541, 131, "Utiliser et représenter les grands nombres entiers, des fractions simples, les nombres décimaux"),
( 542, 131, "Calculer avec des nombres entiers et des nombres décimaux"),
( 543, 131, "Résoudre des problèmes en utilisant des fractions simples, les nombres décimaux et le calcul"),

( 551, 132, "(Se) repérer et (se) déplacer dans l'espace en utilisant ou en élaborant des représentations"),
( 552, 132, "Reconnaitre, nommer, décrire, reproduire, représenter, construire des figures et solides"),
( 553, 132, "Reconnaitre et utiliser quelques relations géométriques"),

( 561, 133, "Comparer, estimer, mesurer des grandeurs géométriques avec des nombres entiers et des nombres décimaux : longueur (périmètre), aire, volume, angle"),
( 562, 133, "Utiliser le lexique, les unités, les instruments de mesures spécifiques de ces grandeurs"),
( 563, 133, "Résoudre des problèmes impliquant des grandeurs (géométriques, physiques, économiques) en utilisant des nombres entiers et des nombres décimaux"),

-- Cycle 3 - Sciences et technologie - CM1-CM2

( 571, 141, "Matière, mouvement, énergie, information : décrire les états et la constitution de la matière à l'échelle macroscopique"),
( 572, 141, "Le vivant, sa diversité et les fonctions qui les caractérisent : observer et décrire différents types de mouvements"),
( 573, 141, "Matériaux et objets techniques : identifier différentes sources d'énergie"),
( 574, 141, "La planète Terre, les êtres vivants dans leur environnement : identifier un signal et une information"),

( 581, 142, "Pratiquer des démarches scientifiques et technologiques"),
( 582, 142, "Concevoir, créer, réaliser"),
( 583, 142, "S’approprier des outils et des méthodes"),
( 584, 142, "Pratiquer des langages"),
( 585, 142, "Mobiliser des outils numériques"),
( 586, 142, "Adopter un comportement éthique et responsable"),
( 587, 142, "Se situer dans l’espace et dans le temps"),

-- Cycle 3 - Arts plastiques - Sixième

( 591, 151, "Expérimenter, produire, créer"),
( 592, 151, "Mettre en œuvre un projet artistique"),
( 593, 151, "S’exprimer, analyser sa pratique, celle de ses pairs ; établir une relation avec celle des artistes, s’ouvrir à l’altérité"),
( 594, 151, "Se repérer dans les domaines liés aux arts plastiques, être sensible aux questions de l’art"),

( 601, 152, "La représentation plastique et les dispositifs de présentation"),
( 602, 152, "Les fabrications et la relation entre l’objet et l’espace"),
( 603, 152, "La matérialité de la production plastique et la sensibilité aux constituants de l’œuvre"),

-- Cycle 3 - Enseignement moral et civique - Sixième

( 611, 161, "Partager et réguler des émotions, des sentiments"),
( 612, 161, "Respecter autrui et accepter les différences"),
( 613, 161, "Manifester le respect des autres dans son langage et son attitude"),
( 614, 161, "Comprendre le sens des symboles de la République"),
( 615, 161, "Coopérer"),

( 621, 162, "Comprendre les notions de droits et devoirs, les accepter et les appliquer"),
( 622, 162, "Respecter tous les autres et notamment appliquer les principes de l’égalité des femmes et des hommes"),
( 623, 162, "Reconnaitre les principes et les valeurs de la République et de l’Union européenne"),
( 624, 162, "Reconnaitre les traits constitutifs de la République française"),

( 631, 163, "Prendre part à une discussion, un débat ou un dialogue"),
( 632, 163, "Nuancer son point de vue en tenant compte du point de vue des autres"),
( 633, 163, "Comprendre la laïcité"),
( 634, 163, "Prendre conscience des enjeux civiques de l’usage de l’informatique et de l’Internet"),
( 635, 163, "Distinguer son intérêt personnel de l’intérêt collectif"),

( 641, 164, "S’engager dans la réalisation d’un projet collectif"),
( 642, 164, "Pouvoir expliquer ses choix et ses actes"),
( 643, 164, "Savoir participer et prendre sa place dans un groupe"),
( 644, 164, "Expliquer en mots simples la fraternité et la solidarité"),

-- Cycle 3 - Éducation physique et sportive - Sixième

( 651, 171, "Parcours d’orientation"),
( 652, 171, "Parcours d’escalade"),
( 653, 171, "Savoir nager"),
( 654, 171, "Activités nautiques"),
( 655, 171, "Activités de roule (vélo, roller…)"),
( 656, 171, "Réaliser un parcours dans plusieurs environnements inhabituels"),
( 657, 171, "Connaitre et respecter les règles de sécurité"),
( 658, 171, "Alerter en cas de problème"),
( 659, 171, "Valider l'attestation scolaire du savoir nager (ASSN)"),

( 661, 172, "Jeux traditionnels"),
( 662, 172, "Jeux collectifs avec ballon"),
( 663, 172, "Jeux de combat"),
( 664, 172, "Jeux de raquettes"),
( 665, 172, "S’organiser pour gagner"),
( 666, 172, "Maintenir un engagement moteur"),
( 667, 172, "Respecter les partenaires, les adversaires et l'arbitre"),
( 668, 172, "Assurer différents rôles (joueur, arbitre, observateur)"),
( 669, 172, "Accepter le résultat de la rencontre"),

( 671, 173, "Activités athlétiques"),
( 672, 173, "Natation"),
( 673, 173, "Réaliser des efforts et enchainer plusieurs actions motrices"),
( 674, 173, "Combiner une course, un saut, un lancer"),
( 675, 173, "Mesurer et analyser une performance"),
( 676, 173, "Assumer les rôles de chronométreur et d'observateur"),

( 681, 174, "Danse"),
( 682, 174, "Activités gymniques"),
( 683, 174, "Arts du cirque"),
( 684, 174, "Réaliser en petits groupes une séquence acrobatique ou à visée artistique"),
( 685, 174, "Filmer une prestation pour la revoir et la faire évoluer"),
( 686, 174, "Respecter les prestations des autres et accepter de se produire devant eux"),

-- Cycle 3 - Éducation musicale - Sixième

( 691, 181, "Chanter et interpréter"),
( 692, 181, "Écouter, comparer et commenter"),
( 693, 181, "Explorer, imaginer et créer"),
( 694, 181, "Échanger, partager et argumenter"),

-- Cycle 3 - Français - Sixième

( 701, 191, "Écouter pour comprendre"),
( 702, 191, "Parler en prenant en compte son auditoire"),
( 703, 191, "Participer à des échanges"),
( 704, 191, "Adopter une attitude critique"),

( 711, 192, "Lire avec fluidité"),
( 712, 192, "Comprendre un texte et l’interpréter"),
( 713, 192, "Être un lecteur autonome"),

( 721, 193, "Écrire à la main de manière fluide et efficace"),
( 722, 193, "Écrire avec un clavier rapidement et efficacement"),
( 723, 193, "Écrire pour réfléchir"),
( 724, 193, "Produire des écrits variés"),
( 725, 193, "Faire évoluer son texte"),
( 726, 193, "Prendre en compte les normes de l’écrit"),

( 731, 194, "Maitriser les relations entre l'oral et l'écrit"),
( 732, 194, "Acquérir la structure, le sens et l'orthographe des mots"),
( 733, 194, "Maitriser la forme des mots"),
( 734, 194, "Observer le fonctionnement du verbe et l'orthographier"),
( 735, 194, "Analyser la phrase simple"),

( 741, 195, "Le monstre, aux limites de l’humain"),
( 742, 195, "Récits d’aventures"),
( 743, 195, "Récits de création ; création poétique"),
( 744, 195, "Résister au plus fort : ruses, mensonges et masques"),

-- Cycle 3 - Histoire des Arts - Sixième

( 751, 201, "Donner un avis argumenté sur ce que représente ou exprime une œuvre d’art"),
( 752, 201, "Dégager d’une œuvre d’art ses principales caractéristiques"),
( 753, 201, "Relier des caractéristiques d’une œuvre d’art à des usages ainsi qu’au contexte de sa création"),
( 754, 201, "Se repérer dans un musée, dans un lieu d’art, un site patrimonial"),

-- Cycle 3 - Histoire-Géographie - Sixième

( 761, 204, "Les débuts de l’humanité"),
( 762, 204, "La « révolution » néolithique"),
( 763, 204, "Premiers États, premières écritures"),

( 771, 205, "Le monde des cités grecques"),
( 772, 205, "Rome du mythe à l’histoire"),
( 773, 205, "La naissance du monothéisme juif dans un monde polythéiste"),

( 781, 206, "Conquêtes, paix romaine et romanisation"),
( 782, 206, "Des chrétiens dans l’empire"),
( 783, 206, "Les relations de l’empire romain avec les autres mondes anciens : l’ancienne route de la soie et la Chine des Han"),

( 791, 207, "Les métropoles et leurs habitants"),
( 792, 207, "La ville de demain"),

( 801, 208, "Habiter un espace à forte(s) contrainte(s) naturelle(s) ou / et de grande biodiversité"),
( 802, 208, "Habiter un espace de faible densité à vocation agricole"),

( 811, 209, "Littoral industrialo-portuaire, littoral touristique"),

( 821, 210, "La répartition de la population mondiale et ses dynamiques"),
( 822, 210, "La variété des formes d’occupation spatiale dans le monde"),

( 831, 211, "Situer des grandes périodes historiques"),
( 832, 211, "Ordonner des faits et les situer"),
( 833, 211, "Utiliser des documents"),
( 834, 211, "Mémoriser et mobiliser ses repères historiques"),

( 841, 212, "Nommer et localiser les grands repères géographiques"),
( 842, 212, "Appréhender la notion d’échelle géographique"),
( 843, 212, "Mémoriser et mobiliser les repères géographiques"),

( 851, 213, "Poser et se poser des questions"),
( 852, 213, "Formuler des hypothèses"),
( 853, 213, "Vérifier"),
( 854, 213, "Justifier"),

( 861, 214, "Connaitre et utiliser différents systèmes d’information"),
( 862, 214, "Trouver, sélectionner et exploiter des informations dans une ressource numérique"),
( 863, 214, "Identifier la ressource numérique utilisée"),

( 871, 215, "Comprendre le sens général d’un document"),
( 872, 215, "Identifier le document"),
( 873, 215, "Extraire des informations d’un document"),
( 874, 215, "Comprendre le sens général d’un document"),

( 881, 216, "Écrire pour penser, argumenter, communiquer et échanger"),
( 882, 216, "Reconnaitre un récit historique"),
( 883, 216, "S’exprimer à l’oral"),
( 884, 216, "S’approprier et utiliser un lexique historique et géographique"),
( 885, 216, "Réaliser des productions"),
( 886, 216, "Utiliser des cartes"),

( 891, 217, "Organiser son travail dans le cadre d’un groupe"),
( 892, 217, "Travailler en commun"),
( 893, 217, "Utiliser les outils numériques dans le travail collectif"),

-- Cycle 3 - Langues vivantes - Sixième

( 901, 220, "Des mots familiers et des expressions très courantes"),
( 902, 220, "Comprendre une intervention brève, claire et simple"),

( 911, 221, "Comprendre dans un message des mots familiers et des phrases très simples"),
( 912, 221, "Comprendre des textes courts et simples"),

( 921, 222, "Utiliser des expressions et des phrases simples"),
( 922, 222, "Se présenter brièvement, parler en termes simples de quelqu’un, d’une activité, d’un lieu"),

( 931, 223, "Copier un modèle écrit, écrire un court message et renseigner un questionnaire simple"),
( 932, 223, "Ecrire un texte court et articulé simplement"),

( 941, 224, "Communiquer, de façon simple, avec l’aide d’un interlocuteur"),
( 942, 224, "Communiquer de façon simple"),

( 951, 225, "Identifier quelques grands repères culturels"),
( 952, 225, "Repérer les indices culturels et mobiliser ses connaissances culturelles"),

( 961, 226, "Corps humain, vêtements, modes de vie"),
( 962, 226, "Portrait physique et moral"),
( 963, 226, "Environnement urbain"),

( 971, 227, "Situation géographique"),
( 972, 227, "Caractéristiques physiques et repères culturels"),
( 973, 227, "Figures historiques et contemporaines"),
( 974, 227, "Pages d’histoire spécifiques"),

( 981, 228, "Littérature de jeunesse"),
( 982, 228, "Contes, mythes et légendes"),
( 983, 228, "Héros et héroïnes"),

( 991, 229, "Groupe nominal"),
( 992, 229, "Groupe verbal"),
( 993, 229, "Construction de la phrase"),

(1001, 230, "Phonèmes"),
(1002, 230, "Accents et rythmes"),
(1003, 230, "Intonation"),
(1004, 230, "Lien phonie / graphie"),

-- Cycle 3 - Mathématiques - Sixième

(1011, 231, "Utiliser et représenter les grands nombres entiers, des fractions simples, les nombres décimaux"),
(1012, 231, "Calculer avec des nombres entiers et des nombres décimaux"),
(1013, 231, "Résoudre des problèmes en utilisant des fractions simples, les nombres décimaux et le calcul"),

(1021, 232, "Comparer, estimer, mesurer des longueurs, des masses, des contenances, des durées"),
(1022, 232, "Utiliser le lexique, les unités, les instruments de mesures spécifiques de ces grandeurs"),
(1023, 232, "Résoudre des problèmes impliquant des longueurs, des masses, des contenances, des durées, des prix"),

(1031, 233, "(Se) repérer et (se) déplacer en utilisant des repères et des représentations"),
(1032, 233, "Reconnaitre, nommer, décrire, reproduire quelques solides"),
(1033, 233, "Reconnaitre, nommer, décrire, reproduire, construire quelques figures géométriques"),
(1034, 233, "Reconnaitre et utiliser les notions d’alignement, d’angle droit, d’égalité de longueurs, de milieu, de symétrie"),

(1041, 234, "Chercher"),
(1042, 234, "Modéliser"),
(1043, 234, "Représenter"),
(1044, 234, "Raisonner"),
(1045, 234, "Calculer"),
(1046, 234, "Communiquer"),

-- Cycle 3 - Sciences et technologie - Sixième

(1051, 241, "États de la matière à l’échelle macroscopique"),
(1052, 241, "Les différents types de mouvements"),
(1053, 241, "Les différentes sources d’énergie"),
(1054, 241, "Signal et information"),

(1061, 242, "Les organismes"),
(1062, 242, "Besoins en aliments de l’être humain ; transformation et conservation des aliments"),
(1063, 242, "Développement et reproduction des êtres vivants"),
(1064, 242, "Origine et devenir de la matière organique des êtres vivants"),

(1071, 243, "Évolution des besoins et des objets"),
(1072, 243, "Fonctionnement, fonctions et constitutions des objets techniques"),
(1073, 243, "Familles de matériaux"),
(1074, 243, "Concevoir et produire un objet technique"),
(1075, 243, "Communication et gestion de l’information"),

(1081, 244, "La Terre dans le système solaire et les conditions de vie sur la terre"),
(1082, 244, "Les enjeux liés à l’environnement"),

(1091, 245, "Pratiquer des démarches scientifiques et technologiques"),
(1092, 245, "Concevoir, créer, réaliser"),
(1093, 245, "S’approprier les outils et les méthodes"),
(1094, 245, "Pratiquer des langages"),
(1095, 245, "Mobiliser les outils numériques"),
(1096, 245, "Adopter un comportement éthique et responsable"),
(1097, 245, "Se situer dans l’espace et le temps"),

-- Cycle 4 - Arts plastiques - Tous niveaux

(1101, 251, "Expérimenter, produire, créer"),
(1102, 251, "Mettre en œuvre un projet artistique"),
(1103, 251, "S’exprimer, analyser sa pratique, celle de ses pairs"),
(1104, 251, "Se repérer dans les domaines liés aux arts plastiques, être sensible aux questions de l’art"),

(1111, 252, "La représentation ; images, réalité et fiction"),
(1112, 252, "La matérialité de l’œuvre ; l’objet et l’œuvre"),
(1113, 252, "L’œuvre, l’espace, l’auteur, le spectateur"),

-- Cycle 4 - Enseignement moral et civique - Tous niveaux

(1121, 261, "Exprimer des sentiments moraux"),
(1122, 261, "Comprendre que l’aspiration personnelle à la liberté suppose de reconnaître celle d’autrui"),
(1123, 261, "Comprendre la diversité des sentiments d’appartenance civiques, sociaux, culturels, religieux"),
(1124, 261, "Connaitre les principes, valeurs et symboles de la citoyenneté française et de la citoyenneté européenne"),

(1131, 262, "Expliquer les grands principes de la justice et leur lien avec le règlement intérieur et la vie de l’établissement"),
(1132, 262, "Identifier les grandes étapes du parcours d’une loi dans la République française"),
(1133, 262, "Définir les principaux éléments des grandes déclarations des Droits de l’homme"),

(1141, 263, "Expliquer les différentes dimensions de l’égalité"),
(1142, 263, "Comprendre les enjeux de la laïcité"),
(1143, 263, "Reconnaître les grandes caractéristiques d’un État démocratique"),

(1151, 264, "Expliquer le lien entre l’engagement et la responsabilité"),
(1152, 264, "Expliquer le sens et l’importance de l’engagement individuel ou collectif"),
(1153, 264, "Connaitre les principaux droits sociaux"),
(1154, 264, "Comprendre la relation entre l’engagement des citoyens et l’engagement des élèves"),
(1155, 264, "Connaitre les grands principes qui régissent la défense nationale"),

-- Cycle 4 - Éducation aux médias et à l’information (EMI) - Tous niveaux

(1161, 271, "Utiliser les médias et les informations de manière autonome"),
(1162, 271, "Exploiter l’information de manière raisonnée"),
(1163, 271, "Utiliser les médias de manière responsable"),
(1164, 271, "Produire, communiquer, partager des informations"),

-- Cycle 4 - Éducation physique et sportive - Tous niveaux

(1171, 281, "Canoë-kayak"),
(1172, 281, "Ski"),
(1173, 281, "VTT"),
(1174, 281, "Escalade"),
(1175, 281, "Randonnée"),
(1176, 281, "Réussir un déplacement planifié dans un milieu plus ou moins connu"),
(1177, 281, "Gérer ses ressources pour réaliser un parcours"),
(1178, 281, "Assurer la sécurité de son camarade"),

(1181, 282, "Basket-ball"),
(1182, 282, "Football"),
(1183, 282, "Handball"),
(1184, 282, "Volley-ball"),
(1185, 282, "Rugby"),
(1186, 282, "Badminton"),
(1187, 282, "Tennis de table"),
(1188, 282, "Boxe française"),
(1189, 282, "Lutte"),
(1190, 282, "Judo"),
(1191, 282, "Jeux traditionnels"),
(1192, 282, "Réaliser des actions décisives"),
(1193, 282, "Adopter son engagement moteur en fonction de son état physique et du rapport de force"),
(1194, 282, "Être solidaire de ses partenaires et respectueux de ses adversaires et de l’arbitre"),
(1195, 282, "Observer et co-arbitrer"),
(1196, 282, "Accepter le résultat de la rencontre et savoir l’analyser"),

(1201, 283, "Demi-fond"),
(1202, 283, "Courses de haies"),
(1203, 283, "Hauteur"),
(1204, 283, "Lancers"),
(1205, 283, "Sauts"),
(1206, 283, "Relais vitesse"),
(1207, 283, "Natation longue"),
(1208, 283, "Natation de vitesse"),
(1209, 283, "Gérer son effort, faire des choix pour réaliser la meilleure performance"),
(1210, 283, "S’engager dans un programme de préparation individuel ou collectif"),
(1211, 283, "Planifier et réaliser une épreuve combinée"),
(1212, 283, "S'échauffer avant un effort"),
(1213, 283, "Aider ses camarades et assumer différents rôles sociaux (juge d’appel, chronométreur…)"),

(1221, 284, "Aérobic"),
(1222, 284, "Acro sport et gymnastique"),
(1223, 284, "Arts du cirque"),
(1224, 284, "Danse"),
(1225, 284, "Mobiliser les capacités expressives du corps"),
(1226, 284, "Participer activement, au sein d’un groupe, à un projet artistique"),
(1227, 284, "Apprécier des prestations"),

-- Cycle 4 - Éducation musicale - Tous niveaux

(1231, 291, "Réaliser des projets musicaux d’interprétation ou de création"),
(1232, 291, "Écouter, comparer et construire une culture musicale commune"),
(1233, 291, "Explorer, imaginer, créer et produire"),
(1234, 291, "Échanger, partager, argumenter et débattre"),

-- Cycle 4 - Français - Tous niveaux

(1241, 301, "Comprendre des messages oraux"),
(1242, 301, "S’exprimer de façon maitrisée"),
(1243, 301, "Participer à des échanges"),
(1244, 301, "Exploiter les ressources de la parole"),

(1251, 302, "Lire des images, des documents et des textes non littéraires"),
(1252, 302, "Lire des textes littéraires et fréquenter des œuvres d’art"),
(1253, 302, "Analyser une œuvre et repérer ses effets esthétiques"),

(1261, 303, "Communiquer par écrit ses sentiments et ses opinions"),
(1262, 303, "Adopter les procédés d’écriture qui répondent à la consigne et à l’objectif"),
(1263, 303, "Écrire pour réfléchir"),
(1264, 303, "Exploiter des lectures pour enrichir son écrit"),
(1265, 303, "S’initier à l’argumentation"),

(1271, 304, "Connaitre les différences entre l’oral et l’écrit"),
(1272, 304, "Maitriser la phrase simple"),
(1273, 304, "Analyser la phrase complexe"),
(1274, 304, "Connaitre le rôle de la ponctuation"),
(1275, 304, "Maitriser les accords dans la phrase"),
(1276, 304, "Maitriser le fonctionnement du verbe"),
(1277, 304, "Maitriser l’usage du vocabulaire"),
(1278, 304, "Connaitre des notions d’analyse littéraire"),

-- Cycle 4 - Français - Cinquième

(1281, 305, "Le voyage et l’aventure (récits d’aventures, de voyages …)"),
(1282, 305, "Avec autrui : familles, amis, réseaux (comédies, récits d’enfance et d’adolescence …)"),
(1283, 305, "Imaginer des univers nouveaux (contes merveilleux, romans d’anticipation …)"),
(1284, 305, "Héros / héroïnes et héroïsmes (épopées, romans de chevalerie …)"),
(1285, 305, "L’être humain est-il maître de la nature ? (descriptions, récits d’anticipation…)"),

-- Cycle 4 - Français - Quatrième

(1291, 306, "Dire l’amour (poèmes lyriques, tragédies…)"),
(1292, 306, "Individu et société : confrontations de valeurs ? (tragédies, tragicomédies, romans, nouvelles…)"),
(1293, 306, "La fiction pour interroger le réel (romans, nouvelles réalistes ou naturalistes…)"),
(1294, 306, "Informer, s’informer, déformer ? (articles de presse…)"),
(1295, 306, "La ville, lieu de tous les possibles ? (descriptions issues des romans du XIXe siècle, poèmes…)"),

-- Cycle 4 - Français - Troisième

(1301, 307, "Se raconter, se représenter"),
(1302, 307, "Dénoncer les travers de la société"),
(1303, 307, "Visions poétiques du monde"),
(1304, 307, "Agir dans la cité : individu et pouvoir"),
(1305, 307, "Progrès et rêves scientifiques"),

-- Cycle 4 - Histoire des Arts - Tous niveaux

(1311, 311, "Décrire une œuvre d’art par ses dimensions matérielles, formelles, de sens et d’usage, en employant un lexique adapté"),
(1312, 311, "Associer une œuvre à une époque et une civilisation à partir des éléments observés"),
(1313, 311, "Proposer une analyse critique simple et une interprétation d’une œuvre"),
(1314, 311, "Construire un exposé de quelques minutes sur un petit ensemble d’œuvres ou une problématique artistique"),
(1315, 311, "Rendre compte de la visite d’un lieu de conservation ou de diffusion artistique ou de la rencontre avec un métier du patrimoine"),

(1321, 312, "Arts et société à l’époque antique et au haut Moyen-âge"),
(1322, 312, "Formes et circulations artistiques"),
(1323, 312, "Le sacre de l’artiste"),
(1324, 312, "État, société, et modes de vie (XIIIe-XVIIIe)."),
(1325, 312, "L’art au temps des Lumières et des révolutions"),
(1326, 312, "De la Belle Epoque aux « années folles » : l’ère des avant-gardes (1870-1930)"),
(1327, 312, "Les arts entre liberté et propagande (1910-1945)"),
(1328, 312, "Les arts à l’ère de la consommation de masse (de 1945 à nos jours)"),

-- Cycle 4 - Histoire-Géographie - Tous niveaux

(1331, 321, "Situer un fait"),
(1332, 321, "Ordonner des faits les uns par rapport aux autres"),
(1333, 321, "Mettre en relation des faits"),
(1334, 321, "Identifier des continuités et des ruptures chronologiques"),

(1341, 322, "Nommer et localiser les grands repères géographiques"),
(1342, 322, "Nommer, localiser et caractériser un lieu"),
(1343, 322, "Situer des lieux et des espaces"),
(1344, 322, "Nommer, localiser et caractériser des espaces"),
(1345, 322, "Utiliser des représentations analogiques et numériques des espaces"),

(1351, 323, "Poser et se poser des questions à propos de situations historiques ou géographiques"),
(1352, 323, "Construire des hypothèses d’interprétation de phénomènes historiques ou géographiques"),
(1353, 323, "Vérifier"),
(1354, 323, "Justifier"),

(1361, 324, "Connaitre et utiliser différents systèmes d’information"),
(1362, 324, "Trouver, sélectionner et exploiter des informations dans une ressource numérique"),
(1363, 324, "Utiliser des ressources numériques"),
(1364, 324, "Vérifier l’origine, la source des informations et leur pertinence"),
(1365, 324, "Exercer son esprit critique sur les données numériques"),

(1371, 325, "Comprendre le sens général d’un document"),
(1372, 325, "Identifier le document et son point de vue particulier"),
(1373, 325, "Extraire des informations pertinentes"),
(1374, 325, "Confronter un document à ce qu’on peut connaitre par ailleurs du sujet étudié"),
(1375, 325, "Utiliser ses connaissances pour expliciter, expliquer le document et exercer son esprit critique"),

(1381, 326, "Écrire pour structurer, argumenter et communiquer"),
(1382, 326, "Réaliser des productions"),
(1383, 326, "S’approprier et utiliser un lexique historique et géographique"),
(1384, 326, "S’initier aux techniques d’argumentation"),

(1391, 327, "Organiser son travail dans le cadre d’un groupe"),
(1392, 327, "Adapter son rythme de travail à celui du groupe"),
(1393, 327, "Défendre ses choix"),
(1394, 327, "Négocier une solution commune"),
(1395, 327, "Utiliser les outils numériques dans le travail collectif"),

-- Cycle 4 - Histoire-Géographie - Cinquième

(1401, 331, "Byzance et l’Europe carolingienne"),
(1402, 331, "De la naissance de l’islam à la prise de Bagdad par les Mongols"),

(1411, 332, "L’ordre seigneurial"),
(1412, 332, "L’émergence d’une nouvelle société urbaine"),
(1413, 332, "L’affirmation de l’État monarchique dans le Royaume des Capétiens et des Valois"),

(1421, 333, "Le monde au temps de Charles Quint et Soliman le Magnifique"),
(1422, 333, "Humanisme, réformes et conflits religieux"),
(1423, 333, "Du Prince de la Renaissance au roi absolu (François Ier, Henri IV, Louis XIV)"),

(1431, 334, "La croissance démographique et ses effets"),
(1432, 334, "Répartition de la richesse et de la pauvreté dans le monde"),

(1441, 335, "L’énergie, l’eau : des ressources à ménager et à mieux utiliser"),
(1442, 335, "L’alimentation dans le monde"),

(1451, 336, "Le changement global et ses principaux effets géographiques régionaux"),
(1452, 336, "Prévenir les risques industriels et technologiques"),

-- Cycle 4 - Histoire-Géographie - Quatrième

(1461, 341, "Bourgeoisies marchandes, négoces internationaux, traites négrières et esclavage au XVIIIe siècle"),
(1462, 341, "L’Europe des Lumières"),
(1463, 341, "La Révolution française et l’Empire : nouvel ordre politique et société révolutionnée en France et en Europe"),

(1471, 342, "L’Europe de la « révolution industrielle »"),
(1472, 342, "Conquêtes et sociétés coloniales"),

(1481, 343, "Une difficile conquête : voter de 1815 à 1870"),
(1482, 343, "La Troisième République"),
(1483, 343, "Conditions féminines dans une société en mutation"),

(1491, 344, "Espace et paysages de l’urbanisation"),
(1492, 344, "Des villes inégalement connectées aux réseaux de la mondialisation"),

(1501, 345, "Un monde de migrants"),
(1502, 345, "Le tourisme et ses espaces"),

(1511, 346, "Mers et Océans : un monde maritimisé"),
(1512, 346, "L’adaptation du territoire des États-Unis aux nouvelles conditions de la mondialisation"),
(1513, 346, "Les dynamiques d’un grand ensemble géographique africain"),

-- Cycle 4 - Histoire-Géographie - Troisième

(1521, 351, "Civils et militaires dans la Première Guerre mondiale"),
(1522, 351, "Démocraties fragilisées et expériences totalitaires dans l’Europe de l’entre-deux-guerres"),
(1523, 351, "La Deuxième Guerre mondiale, une guerre d’anéantissement"),
(1524, 351, "La France défaite et occupée. Régime de Vichy, collaboration, Résistance"),

(1531, 352, "Indépendances et construction de nouveaux États"),
(1532, 352, "Un monde bipolaire au temps de la guerre froide"),
(1533, 352, "Affirmation et mise en œuvre du projet européen"),
(1534, 352, "Enjeux et conflits dans le monde après 1989"),

(1541, 353, "1944-1947 : refonder la République, redéfinir la démocratie"),
(1542, 353, "La Ve République, de la République gaullienne à l’alternance et à la cohabitation"),
(1543, 353, "Femmes et hommes dans la société des années 1950 aux années 1980"),

(1551, 354, "Les aires urbaines, une nouvelle géographie d’une France mondialisée"),
(1552, 354, "Les espaces productifs et leurs évolutions"),
(1553, 354, "Les espaces de faible densité et leurs atouts"),

(1561, 355, "Aménager pour répondre aux inégalités croissantes entre territoires français"),
(1562, 355, "Les territoires ultra-marins français"),

(1571, 356, "L’Union européenne, un nouveau territoire de référence et d’appartenance"),
(1572, 356, "La France et l’Europe dans le monde"),

-- Cycle 4 - Langues vivantes - Tous niveaux

(1581, 360, "[niveau A1] Comprendre des mots familiers et des expressions très courantes"),
(1582, 360, "[niveau A2] Comprendre une intervention brève"),
(1583, 360, "[niveau B1] Comprendre les points essentiels d’un message"),

(1591, 361, "[niveau A1] Comprendre dans un message des mots familiers et des phrases très simples"),
(1592, 361, "[niveau A2] Comprendre des textes courts et simples"),
(1593, 361, "niveau B1] Comprendre des textes rédigés dans une langue courante et renvoyant à un sujet connu"),

(1601, 362, "[niveau A1] Utiliser des expressions et des phrases simples pour parler de soi"),
(1602, 362, "[niveau A2] Se présenter brièvement, parler en termes simples de quelqu’un, d’une activité, d’un lieu"),
(1603, 362, "[niveau B1] Prendre la parole sur des sujets connus"),

(1611, 363, "[niveau A1] Copier un modèle écrit, écrire un court message et renseigner un questionnaire simple"),
(1612, 363, "[niveau A2] Ecrire un texte court et articulé simplement"),
(1613, 363, "[niveau B1] Rédiger un texte court et construit sur un sujet connu"),

(1621, 364, "[niveau A1] Communiquer, de façon simple, avec l’aide de l’interlocuteur"),
(1622, 364, "[niveau A2] Communiquer de façon simple"),
(1623, 364, "niveau B1] Prendre part spontanément à une conversation sur un sujet connu"),

(1631, 365, "[niveau A1] Identifier quelques grands repères culturels"),
(1632, 365, "[niveau A2] Repérer les indices culturels et mobiliser ses connaissances culturelles"),
(1633, 365, "niveau B1] Repérer et comprendre les spécificités des pays et des régions concernés"),

(1641, 366, "Codes socio-culturels"),
(1642, 366, "Médias, modes de communication, réseaux sociaux, publicité"),
(1643, 366, "Langages artistiques"),

(1651, 367, "École et société"),
(1652, 367, "Voyages et migrations"),
(1653, 367, "Rencontres avec d’autres cultures"),

(1661, 368, "Groupe nominal"),
(1662, 368, "Groupe verbal"),
(1663, 368, "Expression du temps"),
(1664, 368, "Énoncés simples et complexes"),
(1665, 368, "Construction de la phrase"),

(1671, 369, "Régularités de la langue orale"),
(1672, 369, "Variations dans les usages de la langue"),

-- Cycle 4 - Mathématiques - Tous niveaux

(1681, 371, "Comparer, calculer, résoudre les problèmes"),
(1682, 371, "Notions de divisibilité et de nombres premiers"),
(1683, 371, "Calcul littéral"),

(1691, 372, "Interpréter, représenter et traiter des données"),
(1692, 372, "Comprendre et utiliser des notions élémentaires de probabilités"),
(1693, 372, "Résoudre des problèmes de proportionnalité"),

(1701, 373, "Calculer avec des grandeurs mesurables ; exprimer les résultats dans les unités adaptées"),
(1702, 373, "Comprendre l’effet de quelques transformations sur des grandeurs géométriques"),

(1711, 374, "Représenter l’espace"),
(1712, 374, "Utiliser les notions de géométrie plane pour démontrer"),

(1721, 375, "Écrire, mettre au point un programme simple"),

(1731, 376, "Chercher"),
(1732, 376, "Modéliser"),
(1733, 376, "Représenter"),
(1734, 376, "Raisonner"),
(1735, 376, "Calculer"),
(1736, 376, "Communiquer"),

-- Cycle 4 - Physique-Chimie - Tous niveaux

(1741, 381, "Pratiquer des démarches scientifiques et technologiques"),
(1742, 381, "Concevoir, créer, réaliser"),
(1743, 381, "S’approprier les outils et les méthodes"),
(1744, 381, "Pratiquer des langages"),
(1745, 381, "Mobiliser les outils numériques"),
(1746, 381, "Se situer dans l’espace et le temps"),

(1751, 382, "États de la matière"),
(1752, 382, "Transformations chimiques"),
(1753, 382, "La matière dans l’univers"),

(1761, 383, "Caractériser un mouvement"),
(1762, 383, "Modéliser une interaction"),

(1771, 384, "Sources, transferts conversions et formes d’énergie"),
(1772, 384, "Conservation de l’énergie"),
(1773, 384, "Circuits électriques simples et lois de l’électricité"),

(1781, 385, "Les différents types de signaux"),
(1782, 385, "Propriétés des signaux"),

-- Cycle 4 - Sciences de la vie et de la Terre - Tous niveaux

(1791, 391, "Pratiquer des démarches scientifiques et technologiques"),
(1792, 391, "Concevoir, créer, réaliser"),
(1793, 391, "S’approprier les outils et les méthodes"),
(1794, 391, "Pratiquer des langages"),
(1795, 391, "Mobiliser les outils numériques"),
(1796, 391, "Adopter un comportement éthique et responsable"),
(1797, 391, "Se situer dans l’espace et le temps"),

(1801, 392, "Phénomènes géologiques"),
(1802, 392, "Météorologie et climatologie"),
(1803, 392, "Impacts de l’action humaine"),
(1804, 392, "Comportements responsables"),

(1811, 393, "Organisation du monde vivant"),
(1812, 393, "Mettre en relation des faits et établir des relations de causalité"),

(1821, 394, "Processus biologiques et organisme humain"),
(1822, 394, "Comportements responsables en matière de santé"),

-- Cycle 4 - Technologie - Tous niveaux

(1831, 401, "Pratiquer des démarches scientifiques et technologiques"),

(1841, 402, "Concevoir, créer, réaliser"),

(1851, 403, "S’approprier les outils et les méthodes"),

(1861, 404, "Pratiquer des langages"),

(1871, 405, "Mobiliser les outils numériques"),

(1881, 406, "Adopter un comportement éthique et responsable"),

(1891, 407, "Se situer dans l’espace et le temps"),

(1901, 408, "Imaginer des solutions, matérialiser des idées"),
(1902, 408, "Réaliser un prototype"),

(1911, 409, "Évolution des objets et systèmes"),
(1912, 409, "Outils de description"),
(1913, 409, "Objets communicants et bonnes pratiques"),

(1921, 410, "Fonctionnement et structure d’un objet"),
(1922, 410, "Utiliser une modélisation, simulation des objets"),

(1931, 411, "Fonctionnement d’un réseau informatique"),
(1932, 411, "Écrire, mettre au point et exécuter un programme");

ALTER TABLE sacoche_livret_element_item ENABLE KEYS;
