DROP TABLE IF EXISTS sacoche_structure;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."
-- Attention : pour un champ DATE ou DATETIME, DEFAULT NOW() ne fonctionne qu`à partir de MySQL 5.6.5
-- Attention : pour un champ DATE ou DATETIME, la configuration NO_ZERO_DATE (incluse dans le mode strict de MySQL 5.7.4 à 5.7.7), interdit les valeurs en dehors de 1000-01-01 00:00:00 à 9999-12-31 23:59:59

CREATE TABLE sacoche_structure (
  sacoche_base               MEDIUMINT(8) UNSIGNED                NOT NULL AUTO_INCREMENT,
  geo_id                     SMALLINT(5)  UNSIGNED                NOT NULL DEFAULT 0,
  structure_uai              CHAR(8)      COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  structure_localisation     VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  structure_denomination     VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  structure_contact_nom      VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  structure_contact_prenom   VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  structure_contact_courriel VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  structure_inscription_date DATE                                          DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL.",
  PRIMARY KEY (sacoche_base),
  KEY geo_id (geo_id),
  KEY structure_uai (structure_uai)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
