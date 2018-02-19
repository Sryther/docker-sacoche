DROP TABLE IF EXISTS sacoche_jointure_referentiel_socle;

CREATE TABLE sacoche_jointure_referentiel_socle (
  item_id             MEDIUMINT(6) UNSIGNED NOT NULL DEFAULT 0,
  socle_cycle_id      TINYINT(1)   UNSIGNED NOT NULL DEFAULT 0,
  socle_composante_id TINYINT(2)   UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY ( item_id , socle_cycle_id , socle_composante_id ),
  KEY item_id (item_id),
  KEY socle_cycle_id (socle_cycle_id),
  KEY socle_composante_id (socle_composante_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
