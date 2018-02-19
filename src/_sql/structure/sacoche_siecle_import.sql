DROP TABLE IF EXISTS sacoche_siecle_import;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."
-- Attention : pour un champ DATE ou DATETIME, DEFAULT NOW() ne fonctionne qu`à partir de MySQL 5.6.5
-- Attention : pour un champ DATE ou DATETIME, la configuration NO_ZERO_DATE (incluse dans le mode strict de MySQL 5.7.4 à 5.7.7), interdit les valeurs en dehors de 1000-01-01 00:00:00 à 9999-12-31 23:59:59
-- Attention : pas de valeur par défaut possible pour les champs TEXT et BLOB... sauf NULL !

CREATE TABLE sacoche_siecle_import (
  siecle_import_objet   VARCHAR(12) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  siecle_import_date    DATE                                         DEFAULT NULL,
  siecle_import_annee   CHAR(4)     COLLATE utf8_unicode_ci          DEFAULT NULL,
  siecle_import_contenu MEDIUMTEXT  COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (siecle_import_objet)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_siecle_import DISABLE KEYS;

INSERT INTO sacoche_siecle_import (siecle_import_objet, siecle_import_date, siecle_import_annee, siecle_import_contenu) VALUES
('sts_emp_UAI' , NULL, NULL, ''),
('Nomenclature', NULL, NULL, ''),
('Communs'     , NULL, NULL, ''),
('Eleves'      , NULL, NULL, ''),
('Onde'        , NULL, NULL, ''); -- Élèves du 1er degré

ALTER TABLE sacoche_siecle_import ENABLE KEYS;
