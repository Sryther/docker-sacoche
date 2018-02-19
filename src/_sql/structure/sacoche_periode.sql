DROP TABLE IF EXISTS sacoche_periode;

CREATE TABLE sacoche_periode (
  periode_id     MEDIUMINT(3) UNSIGNED                NOT NULL AUTO_INCREMENT,
  periode_ordre  TINYINT(1)   UNSIGNED                NOT NULL DEFAULT 1,
  periode_nom    VARCHAR(40)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  periode_livret TINYINT(2)   UNSIGNED                         DEFAULT NULL COMMENT "Période officielle utilisable pour le livret scolaire.",
  PRIMARY KEY (periode_id),
  KEY periode_livret (periode_livret)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
