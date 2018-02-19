DROP TABLE IF EXISTS sacoche_livret_seuil;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."

CREATE TABLE sacoche_livret_seuil (
  livret_page_ref   VARCHAR(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_colonne_id TINYINT(2) UNSIGNED                NOT NULL DEFAULT 0,
  livret_seuil_min  TINYINT(3) UNSIGNED                NOT NULL DEFAULT 0 COMMENT "Entre 0 et 99 ; doit être cohérent avec l'ordre.",
  livret_seuil_max  TINYINT(3) UNSIGNED                NOT NULL DEFAULT 0 COMMENT "Entre 1 et 100 ; doit être cohérent avec l'ordre.",
  PRIMARY KEY (livret_page_ref,livret_colonne_id),
  KEY livret_colonne_id (livret_colonne_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_livret_seuil DISABLE KEYS;

INSERT INTO sacoche_livret_seuil (livret_page_ref, livret_colonne_id, livret_seuil_min, livret_seuil_max) VALUES
-- reussite
('cycle1', 11,  0,  30),
('cycle1', 12, 31,  69),
('cycle1', 13, 70, 100),
-- objectif
('cp'    , 21,  0,  34),
('cp'    , 22, 35,  64),
('cp'    , 23, 65,  89),
('cp'    , 24, 90, 100),
('ce1'   , 21,  0,  34),
('ce1'   , 22, 35,  64),
('ce1'   , 23, 65,  89),
('ce1'   , 24, 90, 100),
('ce2'   , 21,  0,  34),
('ce2'   , 22, 35,  64),
('ce2'   , 23, 65,  89),
('ce2'   , 24, 90, 100),
('cm1'   , 21,  0,  34),
('cm1'   , 22, 35,  64),
('cm1'   , 23, 65,  89),
('cm1'   , 24, 90, 100),
('cm2'   , 21,  0,  34),
('cm2'   , 22, 35,  64),
('cm2'   , 23, 65,  89),
('cm2'   , 24, 90, 100),
-- maitrise
('cycle2', 31,  0,  34),
('cycle2', 32, 35,  59),
('cycle2', 33, 60,  80),
('cycle2', 34, 81, 100),
('cycle3', 31,  0,  34),
('cycle3', 32, 35,  59),
('cycle3', 33, 60,  80),
('cycle3', 34, 81, 100),
('cycle4', 31,  0,  34),
('cycle4', 32, 35,  59),
('cycle4', 33, 60,  80),
('cycle4', 34, 81, 100),
-- objectif (si choisi)
('6e'    , 21,  0,  34),
('6e'    , 22, 35,  64),
('6e'    , 23, 65,  89),
('6e'    , 24, 90, 100),
('5e'    , 21,  0,  34),
('5e'    , 22, 35,  64),
('5e'    , 23, 65,  89),
('5e'    , 24, 90, 100),
('4e'    , 21,  0,  34),
('4e'    , 22, 35,  64),
('4e'    , 23, 65,  89),
('4e'    , 24, 90, 100),
('3e'    , 21,  0,  34),
('3e'    , 22, 35,  64),
('3e'    , 23, 65,  89),
('3e'    , 24, 90, 100),
-- position (si choisi)
('6e'    , 41,  0,  24),
('6e'    , 42, 25,  49),
('6e'    , 43, 50,  74),
('6e'    , 44, 75, 100),
('5e'    , 41,  0,  24),
('5e'    , 42, 25,  49),
('5e'    , 43, 50,  74),
('5e'    , 44, 75, 100),
('4e'    , 41,  0,  24),
('4e'    , 42, 25,  49),
('4e'    , 43, 50,  74),
('4e'    , 44, 75, 100),
('3e'    , 41,  0,  24),
('3e'    , 42, 25,  49),
('3e'    , 43, 50,  74),
('3e'    , 44, 75, 100);
-- moyenne     -> sans objet
-- pourcentage -> sans objet

ALTER TABLE sacoche_livret_seuil ENABLE KEYS;
