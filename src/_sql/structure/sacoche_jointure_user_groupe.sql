DROP TABLE IF EXISTS sacoche_jointure_user_groupe;

CREATE TABLE sacoche_jointure_user_groupe (
  user_id     MEDIUMINT(6) UNSIGNED NOT NULL DEFAULT 0,
  groupe_id   MEDIUMINT(6) UNSIGNED NOT NULL DEFAULT 0,
  jointure_pp TINYINT(1)   UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY ( user_id , groupe_id ),
  KEY groupe_id (groupe_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
