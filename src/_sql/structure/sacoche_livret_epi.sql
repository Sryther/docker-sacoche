DROP TABLE IF EXISTS sacoche_livret_epi;

CREATE TABLE sacoche_livret_epi (
  livret_epi_id         SMALLINT(3)  UNSIGNED                NOT NULL AUTO_INCREMENT,
  livret_epi_theme_code VARCHAR(7)   COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_page_ref       VARCHAR(6)   COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  groupe_id             MEDIUMINT(6) UNSIGNED                NOT NULL DEFAULT 0,
  livret_epi_titre      VARCHAR(128)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (livret_epi_id),
  KEY livret_epi_theme_code (livret_epi_theme_code),
  KEY livret_page_ref (livret_page_ref),
  KEY groupe_id (groupe_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT="Enseignements Pratiques Interdisciplinaires";
