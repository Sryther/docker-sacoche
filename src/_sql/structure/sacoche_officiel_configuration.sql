DROP TABLE IF EXISTS sacoche_officiel_configuration;

-- Attention : pas de valeur par défaut possible pour les champs TEXT et BLOB... sauf NULL !

CREATE TABLE sacoche_officiel_configuration (
  officiel_type         ENUM("releve","bulletin","livret") COLLATE utf8_unicode_ci NOT NULL DEFAULT "bulletin",
  configuration_ref     VARCHAR(15)                        COLLATE utf8_unicode_ci NOT NULL DEFAULT "defaut",
  configuration_nom     VARCHAR(60)                        COLLATE utf8_unicode_ci NOT NULL DEFAULT "Paramétrage principal",
  configuration_contenu TEXT                               COLLATE utf8_unicode_ci NOT NULL COMMENT "json_encode() des paramètres",
  PRIMARY KEY ( officiel_type , configuration_ref ),
  KEY configuration_ref (configuration_ref)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_officiel_configuration DISABLE KEYS;

-- configuration_contenu ne sera testé et rempli avec les valeurs par défaut que plus tard, car pas évident d'enfourner ici un json_encode() de tous les paramètres !

INSERT INTO sacoche_officiel_configuration VALUES
( "releve"  , "defaut", "Paramétrage principal", ""),
( "bulletin", "defaut", "Paramétrage principal", ""),
( "livret"  , "defaut", "Paramétrage principal", "");

ALTER TABLE sacoche_officiel_configuration ENABLE KEYS;
