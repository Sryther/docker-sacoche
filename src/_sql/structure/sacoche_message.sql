DROP TABLE IF EXISTS sacoche_message;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."
-- Attention : pas de valeur par défaut possible pour les champs TEXT et BLOB... sauf NULL !
-- Attention : pour un champ DATE ou DATETIME, DEFAULT NOW() ne fonctionne qu`à partir de MySQL 5.6.5
-- Attention : pour un champ DATE ou DATETIME, la configuration NO_ZERO_DATE (incluse dans le mode strict de MySQL 5.7.4 à 5.7.7), interdit les valeurs en dehors de 1000-01-01 00:00:00 à 9999-12-31 23:59:59

CREATE TABLE IF NOT EXISTS sacoche_message (
  message_id          MEDIUMINT(8) UNSIGNED        NOT NULL AUTO_INCREMENT,
  user_id             MEDIUMINT(6) UNSIGNED        NOT NULL DEFAULT 0,
  message_debut_date  DATE                                  DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL.",
  message_fin_date    DATE                                  DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL.",
  message_contenu     TEXT COLLATE utf8_unicode_ci NOT NULL,
  message_dests_cache TEXT COLLATE utf8_unicode_ci NOT NULL, 
  PRIMARY KEY (message_id),
  KEY user_id (user_id),
  KEY message_debut_date (message_debut_date),
  KEY message_fin_date (message_fin_date)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
