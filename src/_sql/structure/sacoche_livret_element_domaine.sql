DROP TABLE IF EXISTS sacoche_livret_element_domaine;

CREATE TABLE sacoche_livret_element_domaine (
  livret_element_domaine_id  TINYINT(2)  UNSIGNED                NOT NULL DEFAULT 0,
  livret_element_cycle_id    TINYINT(1)  UNSIGNED                NOT NULL DEFAULT 0,
  livret_element_domaine_nom VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (livret_element_domaine_id),
  KEY livret_element_cycle_id (livret_element_cycle_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_livret_element_domaine DISABLE KEYS;

INSERT INTO sacoche_livret_element_domaine (livret_element_domaine_id, livret_element_cycle_id, livret_element_domaine_nom) VALUES

-- Cycle 2

(21, 2, "Enseignement moral et civique"),
(22, 2, "Éducation physique et sportive"),
(23, 2, "Enseignements artistiques"),
(24, 2, "Français"),
(25, 2, "Langues vivantes"),
(26, 2, "Mathématiques"),
(27, 2, "Questionner le monde"),

-- Cycle 3

(30, 3, "Arts plastiques"),
(31, 3, "Enseignement moral et civique"),
(32, 3, "Éducation physique et sportive"),
(33, 3, "Éducation musicale"),
(34, 3, "Enseignements artistiques"),
(35, 3, "Français"),
(36, 3, "Histoire des Arts"),
(37, 3, "Histoire-Géographie"),
(38, 3, "Langues vivantes"),
(39, 3, "Mathématiques"),
(40, 3, "Sciences et technologie"),

-- Cycle 4

(41, 4, "Arts plastiques"),
(42, 4, "Enseignement moral et civique"),
(43, 4, "Éducation aux médias et à l’information (EMI)"),
(44, 4, "Éducation physique et sportive"),
(45, 4, "Éducation musicale"),
(46, 4, "Français"),
(47, 4, "Histoire des Arts"),
(48, 4, "Histoire-Géographie"),
(49, 4, "Langues vivantes"),
(50, 4, "Mathématiques"),
(51, 4, "Physique-Chimie"),
(52, 4, "Sciences de la vie et de la Terre"),
(53, 4, "Technologie");

ALTER TABLE sacoche_livret_element_domaine ENABLE KEYS;
 