DROP TABLE IF EXISTS sacoche_jointure_devoir_prof;

CREATE TABLE sacoche_jointure_devoir_prof (
  devoir_id      MEDIUMINT(6)                     UNSIGNED                NOT NULL DEFAULT 0,
  prof_id        MEDIUMINT(6)                     UNSIGNED                NOT NULL DEFAULT 0,
  jointure_droit ENUM("voir","saisir","modifier") COLLATE utf8_unicode_ci NOT NULL DEFAULT "saisir",
  PRIMARY KEY ( devoir_id , prof_id ),
  KEY prof_id (prof_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
