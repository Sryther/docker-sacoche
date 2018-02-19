DROP TABLE IF EXISTS sacoche_livret_element_theme;

CREATE TABLE sacoche_livret_element_theme (
  livret_element_theme_id   SMALLINT(3)  UNSIGNED                NOT NULL DEFAULT 0,
  livret_element_domaine_id TINYINT(2)   UNSIGNED                NOT NULL DEFAULT 0,
  livret_element_niveau_id  TINYINT(2)   UNSIGNED                NOT NULL DEFAULT 0,
  livret_element_theme_nom  VARCHAR(115) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (livret_element_theme_id),
  KEY livret_element_domaine_id (livret_element_domaine_id),
  KEY livret_element_niveau_id (livret_element_niveau_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_livret_element_theme DISABLE KEYS;

INSERT INTO sacoche_livret_element_theme (livret_element_theme_id, livret_element_domaine_id, livret_element_niveau_id, livret_element_theme_nom) VALUES

-- Cycle 2 - Enseignement moral et civique - Tous niveaux

(  1, 21, 20, "Compétences travaillées"),

-- Cycle 2 - Éducation physique et sportive - Tous niveaux

( 11, 22, 20, "Adapter ses déplacements à des environnements variés"),
( 12, 22, 20, "Conduire et maitriser un affrontement collectif et interindividuel"),
( 13, 22, 20, "Produire une performance"),
( 14, 22, 20, "S’exprimer devant les autres par une prestation artistique et/ou acrobatique"),

-- Cycle 2 - Enseignements artistiques - Tous niveaux

( 21, 23, 20, "Arts plastiques"),
( 22, 23, 20, "Éducation musicale"),

-- Cycle 2 - Français - Tous niveaux

( 31, 24, 20, "Langage oral"),
( 32, 24, 20, "Lecture et compréhension de l’écrit"),
( 33, 24, 20, "Écriture"),
( 34, 24, 20, "Étude de la langue (grammaire, orthographe, lexique)"),

-- Cycle 2 - Langues vivantes - Tous niveaux

( 41, 25, 20, "Comprendre l’oral"),
( 42, 25, 20, "S’exprimer oralement en continu"),
( 43, 25, 20, "Prendre part à une conversation"),
( 44, 25, 20, "Découvrir quelques aspects culturels de la langue"),

-- Cycle 2 - Mathématiques - Tous niveaux

( 51, 26, 20, "Nombres et calculs"),
( 52, 26, 20, "Grandeurs et mesures"),
( 53, 26, 20, "Espace et géométrie"),

-- Cycle 2 - Questionner le monde - Tous niveaux

( 61, 27, 20, "Vivant, matière, objets - Le monde du vivant"),
( 62, 27, 20, "Vivant, matière, objets - La matière"),
( 63, 27, 20, "Vivant, matière, objets - Les objets techniques"),
( 64, 27, 20, "Espace, temps - Se situer dans l’espace"),
( 65, 27, 20, "Espace, temps - Se situer dans le temps"),
( 66, 27, 20, "Espace, temps - Explorer les organisations du monde"),

-- Cycle 3 - Enseignement moral et civique - CM1-CM2

( 71, 31, 33, "Compétences travaillées"),

-- Cycle 3 - Éducation physique et sportive - CM1-CM2

( 81, 32, 33, "Adapter ses déplacements à des environnements variés"),
( 82, 32, 33, "Conduire et maitriser un affrontement collectif et interindividuel"),
( 83, 32, 33, "Produire une performance"),
( 84, 32, 33, "S’exprimer devant les autres par une prestation artistique et/ou acrobatique"),

-- Cycle 3 - Enseignements artistiques - CM1-CM2

( 91, 34, 33, "Arts plastiques"),
( 92, 34, 33, "Éducation musicale"),
( 93, 34, 33, "Histoire des arts"),

-- Cycle 3 - Français - CM1-CM2

(101, 35, 33, "Langage oral"),
(102, 35, 33, "Lecture et compréhension de l’écrit"),
(103, 35, 33, "Écriture"),
(104, 35, 33, "Étude de la langue (grammaire, orthographe, lexique)"),

-- Cycle 3 - Histoire-Géographie - CM1

(110, 37, 31, "Histoire"),
(111, 37, 31, "Géographie"),

-- Cycle 3 - Histoire-Géographie - CM2

(112, 37, 32, "Histoire"),
(113, 37, 32, "Géographie"),

-- Cycle 3 - Histoire-Géographie - CM1-CM2

(114, 37, 33, "Se repérer dans le temps : construire des repères historiques"),
(115, 37, 33, "Se repérer dans l'espace : construire des repères géographiques"),
(116, 37, 33, "Raisonner, justifier une démarche et des choix effectués"),
(117, 37, 33, "S’informer dans le monde du numérique"),
(118, 37, 33, "Comprendre un document"),
(119, 37, 33, "Pratiquer différents langages en histoire et en géographie"),
(120, 37, 33, "Coopérer et mutualiser"),

-- Cycle 3 - Langues vivantes - CM1-CM2

(121, 38, 33, "Écouter et comprendre"),
(122, 38, 33, "Lire et comprendre"),
(123, 38, 33, "Parler en continu"),
(124, 38, 33, "Écrire"),
(125, 38, 33, "Réagir et dialoguer"),
(126, 38, 33, "Découvrir des aspects culturels de la langue"),

-- Cycle 3 - Mathématiques - CM1-CM2

(131, 39, 33, "Nombres et calculs"),
(132, 39, 33, "Grandeurs et mesures"),
(133, 39, 33, "Espace et géométrie"),

-- Cycle 3 - Sciences et technologie - CM1-CM2

(141, 40, 33, "Thèmes"),
(142, 40, 33, "Compétences travaillées"),

-- Cycle 3 - Arts plastiques - Sixième

(151, 30, 34, "Compétences travaillées"),
(152, 30, 34, "Questionnements"),

-- Cycle 3 - Enseignement moral et civique - Sixième

(161, 31, 34, "La sensibilité : soi et les autres"),
(162, 31, 34, "Le droit et la règle : des principes pour vivre avec les autres"),
(163, 31, 34, "Le jugement : penser par soi-même et avec les autres"),
(164, 31, 34, "L’engagement : agir individuellement et collectivement"),

-- Cycle 3 - Éducation physique et sportive - Sixième

(171, 32, 34, "Adapter ses déplacements à des environnements variés"),
(172, 32, 34, "Conduire et maitriser un affrontement collectif et interindividuel"),
(173, 32, 34, "Produire une performance optimale mesurable à une échéance donnée"),
(174, 32, 34, "S’exprimer devant les autres par une prestation artistique et/ou acrobatique"),

-- Cycle 3 - Éducation musicale - Sixième

(181, 33, 34, "Compétences travaillées"),

-- Cycle 3 - Français - Sixième

(191, 35, 34, "Langage oral"),
(192, 35, 34, "Lecture et compréhension de l’écrit"),
(193, 35, 34, "Écriture"),
(194, 35, 34, "Étude de la langue (grammaire, orthographe, lexique)"),
(195, 35, 34, "Culture littéraire et artistique"),

-- Cycle 3 - Histoire des Arts - Sixième

(201, 36, 34, "Compétences travaillées"),

-- Cycle 3 - Histoire-Géographie - Sixième

(204, 37, 34, "Histoire : La longue histoire de l’humanité et des migrations"),
(205, 37, 34, "Histoire : Récits fondateurs, croyances et citoyenneté dans la Méditerranée antique au 1er millénaire avant J.-C."),
(206, 37, 34, "Histoire : L’Empire romain dans le monde antique"),
(207, 37, 34, "Géographie : Habiter une métropole"),
(208, 37, 34, "Géographie : Habiter un espace de faible densité"),
(209, 37, 34, "Géographie : Habiter les littoraux"),
(210, 37, 34, "Géographie : Le monde habité"),
(211, 37, 34, "Se repérer dans le temps : construire des repères historiques"),
(212, 37, 34, "Se repérer dans l’espace : construire des repères géographiques"),
(213, 37, 34, "Raisonner, justifier une démarche et les choix effectués"),
(214, 37, 34, "S’informer dans le monde du numérique"),
(215, 37, 34, "Comprendre un document"),
(216, 37, 34, "Pratiquer différents langages en histoire et en géographie"),
(217, 37, 34, "Coopérer et mutualiser"),

-- Cycle 3 - Langues vivantes - Sixième

(220, 38, 34, "Écouter et comprendre"),
(221, 38, 34, "Lire et comprendre"),
(222, 38, 34, "Parler en continu"),
(223, 38, 34, "Écrire"),
(224, 38, 34, "Réagir et dialoguer"),
(225, 38, 34, "Découvrir des aspects culturels de la langue"),
(226, 38, 34, "La personne et la vie quotidienne"),
(227, 38, 34, "Repères géographiques, historiques et culturels des villes, pays et régions"),
(228, 38, 34, "Imaginaire"),
(229, 38, 34, "Grammaire"),
(230, 38, 34, "Phonologie"),

-- Cycle 3 - Mathématiques - Sixième

(231, 39, 34, "Nombres et calculs"),
(232, 39, 34, "Grandeurs et mesures"),
(233, 39, 34, "Espace et géométrie"),
(234, 39, 34, "Compétences travaillées"),

-- Cycle 3 - Sciences et technologie - Sixième

(241, 40, 34, "Matière, mouvement, énergie, information"),
(242, 40, 34, "Le vivant, sa diversité et les fonctions qui les caractérisent"),
(243, 40, 34, "Matériaux et objets techniques"),
(244, 40, 34, "La Planète Terre, les êtres vivants dans leur environnement"),
(245, 40, 34, "Compétences travaillées"),

-- Cycle 4 - Arts plastiques - Tous niveaux

(251, 41, 40, "Compétences travaillées"),
(252, 41, 40, "Questionnements"),

-- Cycle 4 - Enseignement moral et civique - Tous niveaux

(261, 42, 40, "La sensibilité : soi et les autres"),
(262, 42, 40, "Le droit et la règle : des principes pour vivre avec les autres"),
(263, 42, 40, "Le jugement : penser par soi-même et avec les autres"),
(264, 42, 40, "L’engagement : agir individuellement et collectivement"),

-- Cycle 4 - Éducation aux médias et à l’information (EMI) - Tous niveaux

(271, 43, 40, "Compétences travaillées"),

-- Cycle 4 - Éducation physique et sportive - Tous niveaux

(281, 44, 40, "Adapter ses déplacements à des environnements variés"),
(282, 44, 40, "Conduire et maitriser un affrontement collectif et interindividuel"),
(283, 44, 40, "Produire une performance optimale mesurable à une échéance donnée"),
(284, 44, 40, "S’exprimer devant les autres par une prestation artistique et/ou acrobatique"),

-- Cycle 4 - Éducation musicale - Tous niveaux

(291, 45, 40, "Compétences travaillées"),

-- Cycle 4 - Français - Tous niveaux

(301, 46, 40, "Langage oral"),
(302, 46, 40, "Lecture et compréhension de l’écrit"),
(303, 46, 40, "Écriture"),
(304, 46, 40, "Étude de la langue (grammaire, orthographe, lexique)"),

-- Cycle 4 - Français - Cinquième

(305, 46, 41, "Culture littéraire et artistique"),

-- Cycle 4 - Français - Quatrième

(306, 46, 42, "Culture littéraire et artistique"),

-- Cycle 4 - Français - Troisième

(307, 46, 43, "Culture littéraire et artistique"),

-- Cycle 4 - Histoire des Arts - Tous niveaux

(311, 47, 40, "Compétences travaillées"),
(312, 47, 40, "Thématiques"),

-- Cycle 4 - Histoire-Géographie - Tous niveaux

(321, 48, 40, "Se repérer dans le temps : construire des repères historiques"),
(322, 48, 40, "Se repérer dans l’espace : construire des repères géographiques"),
(323, 48, 40, "Raisonner, justifier une démarche et les choix effectués"),
(324, 48, 40, "S’informer dans le monde du numérique"),
(325, 48, 40, "Analyser et comprendre un document"),
(326, 48, 40, "Pratiquer différents langages en histoire et en géographie"),
(327, 48, 40, "Coopérer et mutualiser"),

-- Cycle 4 - Histoire-Géographie - Cinquième

(331, 48, 41, "Histoire : Chrétientés et islam (VIe – XIIIe siècles), des mondes en contact"),
(332, 48, 41, "Histoire : Société, Église et pouvoir politique dans l’occident féodal (XIe – XVe siècles)"),
(333, 48, 41, "Histoire : Transformations de l’Europe et ouverture sur le monde aux XVIe et XVIIe siècles"),
(334, 48, 41, "Géographie : La question démographique et l’inégal développement"),
(335, 48, 41, "Géographie : Des ressources limitées, à gérer et à renouveler"),
(336, 48, 41, "Géographie : Prévenir les risques, s’adapter au changement global"),

-- Cycle 4 - Histoire-Géographie - Quatrième

(341, 48, 42, "Histoire : Le XVIIIe siècle. Expansions, Lumières et révolutions"),
(342, 48, 42, "Histoire : L’Europe et le monde au XIXe siècle"),
(343, 48, 42, "Histoire : Société, culture et politique dans la France du XIXe siècle"),
(344, 48, 42, "Géographie : L’urbanisation du monde"),
(345, 48, 42, "Géographie : Les mobilités humaines transnationales"),
(346, 48, 42, "Géographie : Des espaces transformés par la mondialisation"),

-- Cycle 4 - Histoire-Géographie - Troisième

(351, 48, 43, "Histoire : L’Europe, un théâtre majeur des guerres totales (1914-1945)"),
(352, 48, 43, "Histoire : Le monde depuis 1945"),
(353, 48, 43, "Histoire : Françaises et Français dans une République repensée"),
(354, 48, 43, "Géographie : Dynamiques territoriales de la France contemporaine"),
(355, 48, 43, "Géographie : Pourquoi et comment aménager le territoire ?"),
(356, 48, 43, "Géographie : La France et l’Union européenne"),

-- Cycle 4 - Langues vivantes - Tous niveaux

(360, 49, 40, "Écouter et comprendre"),
(361, 49, 40, "Lire et comprendre"),
(362, 49, 40, "Parler en continu"),
(363, 49, 40, "Écrire"),
(364, 49, 40, "Réagir et dialoguer"),
(365, 49, 40, "Découvrir des aspects culturels de la langue"),
(366, 49, 40, "Langages"),
(367, 49, 40, "Notions culturelles"),
(368, 49, 40, "Grammaire"),
(369, 49, 40, "Phonologie"),

-- Cycle 4 - Mathématiques - Tous niveaux

(371, 50, 40, "Nombres et calculs"),
(372, 50, 40, "Organisation et gestion de données, fonctions"),
(373, 50, 40, "Grandeurs et mesures"),
(374, 50, 40, "Espace et géométrie"),
(375, 50, 40, "Algorithmique et programmation"),
(376, 50, 40, "Compétences travaillées"),

-- Cycle 4 - Physique-Chimie - Tous niveaux

(381, 51, 40, "Compétences travaillées"),
(382, 51, 40, "Organisation et transformations de la matière"),
(383, 51, 40, "Mouvement et interaction"),
(384, 51, 40, "L’énergie et ses conversions"),
(385, 51, 40, "Signaux pour observer et communiquer"),

-- Cycle 4 - Sciences de la vie et de la Terre - Tous niveaux

(391, 52, 40, "Compétences travaillées"),
(392, 52, 40, "La planète terre, l’environnement et l’action humaine"),
(393, 52, 40, "Le vivant et son évolution"),
(394, 52, 40, "Le corps humain et la santé"),

-- Cycle 4 - Technologie - Tous niveaux

(401, 53, 40, "Pratiquer des démarches scientifiques et technologiques"),
(402, 53, 40, "Concevoir, créer, réaliser"),
(403, 53, 40, "S’approprier les outils et les méthodes"),
(404, 53, 40, "Pratiquer des langages"),
(405, 53, 40, "Mobiliser les outils numériques"),
(406, 53, 40, "Adopter un comportement éthique et responsable"),
(407, 53, 40, "Se situer dans l’espace et le temps"),
(408, 53, 40, "Design, innovation et créativité"),
(409, 53, 40, "Objets techniques, services et changements induits dans la société"),
(410, 53, 40, "Modélisation et simulation des objets et systèmes techniques"),
(411, 53, 40, "Informatique et programmation");

ALTER TABLE sacoche_livret_element_theme ENABLE KEYS;
