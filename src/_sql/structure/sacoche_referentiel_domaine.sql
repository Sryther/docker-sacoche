DROP TABLE IF EXISTS sacoche_referentiel_domaine;

CREATE TABLE sacoche_referentiel_domaine (
  domaine_id    SMALLINT(4)  UNSIGNED                NOT NULL AUTO_INCREMENT,
  matiere_id    SMALLINT(4)  UNSIGNED                NOT NULL DEFAULT 0,
  niveau_id     MEDIUMINT(6) UNSIGNED                NOT NULL DEFAULT 0,
  domaine_ordre TINYINT(2)   UNSIGNED                NOT NULL DEFAULT 1 COMMENT "Commence à 1.",
  domaine_code  CHAR(1)      COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  domaine_ref   VARCHAR(3)   COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  domaine_nom   VARCHAR(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (domaine_id),
  KEY matiere_id (matiere_id),
  KEY niveau_id (niveau_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
