DROP TABLE IF EXISTS sacoche_parametre;

CREATE TABLE sacoche_parametre (
  parametre_nom    VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  parametre_valeur VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (parametre_nom)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO sacoche_parametre VALUES 
( "version_base" , "" );
