DROP TABLE IF EXISTS sacoche_livret_saisie_jointure_prof;

CREATE TABLE sacoche_livret_saisie_jointure_prof (
  livret_saisie_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
  prof_id          MEDIUMINT(6) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY ( livret_saisie_id , prof_id ),
  KEY prof_id (prof_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT="Pour conserver la liste des enseignants associés au positionnement ou intervenus sur la saisie.";
