DROP TABLE IF EXISTS sacoche_geo;

CREATE TABLE sacoche_geo (
  geo_id    SMALLINT(5) UNSIGNED                NOT NULL AUTO_INCREMENT,
  geo_ordre SMALLINT(5) UNSIGNED                NOT NULL DEFAULT 0,
  geo_nom   VARCHAR(65) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (geo_id),
  KEY geo_ordre (geo_ordre)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO sacoche_geo VALUES (1, 1, 'Zone par défaut');
