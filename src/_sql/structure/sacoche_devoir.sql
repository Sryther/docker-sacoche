DROP TABLE IF EXISTS sacoche_devoir;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."
-- Attention : pour un champ DATE ou DATETIME, DEFAULT NOW() ne fonctionne qu`à partir de MySQL 5.6.5
-- Attention : pour un champ DATE ou DATETIME, la configuration NO_ZERO_DATE (incluse dans le mode strict de MySQL 5.7.4 à 5.7.7), interdit les valeurs en dehors de 1000-01-01 00:00:00 à 9999-12-31 23:59:59

CREATE TABLE sacoche_devoir (
  devoir_id            MEDIUMINT(6)           UNSIGNED                NOT NULL AUTO_INCREMENT,
  proprio_id           MEDIUMINT(6)           UNSIGNED                NOT NULL DEFAULT 0,
  groupe_id            MEDIUMINT(6)           UNSIGNED                NOT NULL DEFAULT 0,
  devoir_date          DATE                                                    DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL.",
  devoir_info          VARCHAR(60)            COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  devoir_visible_date  DATE                                                    DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL.",
  devoir_autoeval_date DATE                                                    DEFAULT NULL ,
  devoir_doc_sujet     VARCHAR(255)           COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  devoir_doc_corrige   VARCHAR(255)           COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  devoir_fini          TINYINT(1)             UNSIGNED                NOT NULL DEFAULT 0,
  devoir_eleves_ordre  ENUM("alpha","classe") COLLATE utf8_unicode_ci NOT NULL DEFAULT "alpha",
  PRIMARY KEY (devoir_id),
  KEY proprio_id (proprio_id),
  KEY groupe_id (groupe_id),
  KEY devoir_date (devoir_date),
  KEY devoir_visible_date (devoir_visible_date)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
