DROP TABLE IF EXISTS sacoche_livret_jointure_enscompl_eleve;

CREATE TABLE sacoche_livret_jointure_enscompl_eleve (
  livret_enscompl_code VARCHAR(3)   COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  eleve_id             MEDIUMINT(6) UNSIGNED                NOT NULL DEFAULT 0,
  PRIMARY KEY ( eleve_id ),
  KEY livret_enscompl_code (livret_enscompl_code)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
