DROP TABLE IF EXISTS sacoche_user_switch;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."
-- Attention : pas de valeur par défaut possible pour les champs TEXT et BLOB... sauf NULL !

CREATE TABLE IF NOT EXISTS sacoche_user_switch (
  user_switch_id    MEDIUMINT(6) UNSIGNED            NOT NULL AUTO_INCREMENT,
  user_switch_liste TINYTEXT COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (user_switch_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
