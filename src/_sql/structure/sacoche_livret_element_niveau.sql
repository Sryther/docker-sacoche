DROP TABLE IF EXISTS sacoche_livret_element_niveau;

CREATE TABLE sacoche_livret_element_niveau (
  livret_element_niveau_id  TINYINT(2)   UNSIGNED                NOT NULL DEFAULT 0,
  livret_element_cycle_id   TINYINT(1)   UNSIGNED                NOT NULL DEFAULT 0,
  livret_element_niveau_nom VARCHAR(12)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (livret_element_niveau_id),
  KEY livret_element_cycle_id (livret_element_cycle_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_livret_element_niveau DISABLE KEYS;

INSERT INTO sacoche_livret_element_niveau (livret_element_niveau_id, livret_element_cycle_id, livret_element_niveau_nom) VALUES

-- Cycle 2

(20, 2, "Tous niveaux"),

-- Cycle 3

(30, 3, "Tous niveaux"),
(31, 3, "CM1"),
(32, 3, "CM2"),
(33, 3, "CM1-CM2"),
(34, 3, "Sixième"),

-- Cycle 4

(40, 4, "Tous niveaux"),
(41, 4, "Cinquième"),
(42, 4, "Quatrième"),
(43, 4, "Troisième");

ALTER TABLE sacoche_livret_element_niveau ENABLE KEYS;
