DROP TABLE IF EXISTS sacoche_livret_parcours;

CREATE TABLE sacoche_livret_parcours (
  livret_parcours_id        SMALLINT(3)  UNSIGNED                NOT NULL AUTO_INCREMENT,
  livret_parcours_type_code VARCHAR(7)   COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_page_ref           VARCHAR(6)   COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  groupe_id                 MEDIUMINT(6) UNSIGNED                NOT NULL DEFAULT 0,
  PRIMARY KEY (livret_parcours_id),
  UNIQUE KEY livret_parcours (livret_parcours_type_code, livret_page_ref, groupe_id),
  KEY livret_page_ref (livret_page_ref),
  KEY groupe_id (groupe_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
