DROP TABLE IF EXISTS sacoche_livret_jointure_parcours_prof;

CREATE TABLE sacoche_livret_jointure_parcours_prof (
  livret_parcours_id SMALLINT(3) UNSIGNED NOT NULL DEFAULT 0,
  prof_id           MEDIUMINT(6) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY ( livret_parcours_id , prof_id ),
  KEY prof_id (prof_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
