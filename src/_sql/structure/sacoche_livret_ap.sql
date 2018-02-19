DROP TABLE IF EXISTS sacoche_livret_ap;

CREATE TABLE sacoche_livret_ap (
  livret_ap_id    SMALLINT(3)  UNSIGNED                NOT NULL AUTO_INCREMENT,
  livret_page_ref VARCHAR(6)   COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  groupe_id       MEDIUMINT(6) UNSIGNED                NOT NULL DEFAULT 0,
  livret_ap_titre VARCHAR(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (livret_ap_id),
  KEY livret_page_ref (livret_page_ref),
  KEY groupe_id (groupe_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT="Accompagnement Personnalisé";
