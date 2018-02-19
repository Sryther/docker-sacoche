DROP TABLE IF EXISTS sacoche_partenaire;

CREATE TABLE sacoche_partenaire (
  partenaire_id           TINYINT(3)   UNSIGNED                NOT NULL AUTO_INCREMENT,
  partenaire_denomination VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  partenaire_nom          VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  partenaire_prenom       VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  partenaire_courriel     VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  partenaire_password     CHAR(32)     COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  partenaire_connecteurs  VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Liste des connecteurs séparés par des virgules.",
  PRIMARY KEY (partenaire_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT "Pour les partenaires ENT conventionnés (serveur Sésamath).";
