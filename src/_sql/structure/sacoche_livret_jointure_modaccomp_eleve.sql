DROP TABLE IF EXISTS sacoche_livret_jointure_modaccomp_eleve;

-- Attention : pas de valeur par défaut possible pour les champs TEXT et BLOB... sauf NULL !

CREATE TABLE sacoche_livret_jointure_modaccomp_eleve (
  livret_modaccomp_code VARCHAR(5)   COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  eleve_id              MEDIUMINT(6) UNSIGNED                NOT NULL DEFAULT 0,
  info_complement       TINYTEXT     COLLATE utf8_unicode_ci NOT NULL COMMENT "Dans le cas où la modalité d'accompagnement est PPRE.",
  PRIMARY KEY ( livret_modaccomp_code , eleve_id ),
  KEY eleve_id (eleve_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
