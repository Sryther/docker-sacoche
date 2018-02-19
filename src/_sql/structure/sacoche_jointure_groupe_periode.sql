DROP TABLE IF EXISTS sacoche_jointure_groupe_periode;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."
-- Attention : pour un champ DATE ou DATETIME, DEFAULT NOW() ne fonctionne qu`à partir de MySQL 5.6.5
-- Attention : pour un champ DATE ou DATETIME, la configuration NO_ZERO_DATE (incluse dans le mode strict de MySQL 5.7.4 à 5.7.7), interdit les valeurs en dehors de 1000-01-01 00:00:00 à 9999-12-31 23:59:59

CREATE TABLE sacoche_jointure_groupe_periode (
  groupe_id           MEDIUMINT(6)                                                 UNSIGNED                NOT NULL DEFAULT 0,
  periode_id          MEDIUMINT(3)                                                 UNSIGNED                NOT NULL DEFAULT 0,
  jointure_date_debut DATE                                                                                          DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL.",
  jointure_date_fin   DATE                                                                                          DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL.",
  officiel_releve     ENUM("","1vide","2rubrique","3mixte","4synthese","5complet") COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  officiel_bulletin   ENUM("","1vide","2rubrique","3mixte","4synthese","5complet") COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY ( groupe_id , periode_id ),
  KEY periode_id (periode_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
