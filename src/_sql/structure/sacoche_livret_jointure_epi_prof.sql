DROP TABLE IF EXISTS sacoche_livret_jointure_epi_prof;

CREATE TABLE sacoche_livret_jointure_epi_prof (
  livret_epi_id SMALLINT(3)  UNSIGNED NOT NULL DEFAULT 0,
  matiere_id    SMALLINT(4)  UNSIGNED NOT NULL DEFAULT 0,
  prof_id       MEDIUMINT(6) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY ( livret_epi_id , matiere_id , prof_id ),
  KEY matiere_id (matiere_id),
  KEY prof_id (prof_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
