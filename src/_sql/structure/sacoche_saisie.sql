DROP TABLE IF EXISTS sacoche_saisie;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."
-- Attention : pour un champ DATE ou DATETIME, DEFAULT NOW() ne fonctionne qu`à partir de MySQL 5.6.5
-- Attention : pour un champ DATE ou DATETIME, la configuration NO_ZERO_DATE (incluse dans le mode strict de MySQL 5.7.4 à 5.7.7), interdit les valeurs en dehors de 1000-01-01 00:00:00 à 9999-12-31 23:59:59

CREATE TABLE sacoche_saisie (
  prof_id             MEDIUMINT(6) UNSIGNED                NOT NULL DEFAULT 0,
  eleve_id            MEDIUMINT(6) UNSIGNED                NOT NULL DEFAULT 0,
  devoir_id           MEDIUMINT(6) UNSIGNED                NOT NULL DEFAULT 0,
  item_id             MEDIUMINT(6) UNSIGNED                NOT NULL DEFAULT 0,
  saisie_date         DATE                                          DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL.",
  saisie_note         CHAR(2)      COLLATE utf8_unicode_ci NOT NULL DEFAULT "NN",
  saisie_info         VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Enregistrement statique du nom du devoir et du professeur, conservé les années suivantes.",
  saisie_visible_date DATE                                          DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL.",
  PRIMARY KEY ( devoir_id , eleve_id , item_id ),
  KEY prof_id (prof_id),
  KEY eleve_id (eleve_id),
  KEY item_id (item_id),
  KEY saisie_date (saisie_date),
  KEY saisie_visible_date (saisie_visible_date)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
