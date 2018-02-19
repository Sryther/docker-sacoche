DROP TABLE IF EXISTS sacoche_jointure_parent_eleve;

CREATE TABLE sacoche_jointure_parent_eleve (
  parent_id      MEDIUMINT(6) UNSIGNED NOT NULL DEFAULT 0,
  eleve_id       MEDIUMINT(6) UNSIGNED NOT NULL DEFAULT 0,
  resp_legal_num TINYINT(1)   UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY ( parent_id , eleve_id ),
  KEY eleve_id (eleve_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
