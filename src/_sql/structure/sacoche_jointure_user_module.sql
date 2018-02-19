DROP TABLE IF EXISTS sacoche_jointure_user_module;

CREATE TABLE sacoche_jointure_user_module (
  user_id      MEDIUMINT(6) UNSIGNED                NOT NULL DEFAULT 0,
  module_objet VARCHAR(31)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  module_url   VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY ( user_id , module_objet ),
  KEY module_objet (module_objet)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
