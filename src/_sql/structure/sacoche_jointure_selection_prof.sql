DROP TABLE IF EXISTS sacoche_jointure_selection_prof;

CREATE TABLE sacoche_jointure_selection_prof (
  selection_item_id MEDIUMINT(8)            UNSIGNED                NOT NULL DEFAULT 0,
  prof_id           MEDIUMINT(6)            UNSIGNED                NOT NULL DEFAULT 0,
  jointure_droit    ENUM("voir","modifier") COLLATE utf8_unicode_ci NOT NULL DEFAULT "voir",
  PRIMARY KEY ( selection_item_id , prof_id ),
  KEY prof_id (prof_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
