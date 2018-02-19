DROP TABLE IF EXISTS sacoche_referentiel_item;

-- Attention : pas de valeur par défaut possible pour les champs TEXT et BLOB... sauf NULL !

CREATE TABLE sacoche_referentiel_item (
  item_id    MEDIUMINT(6) UNSIGNED                NOT NULL AUTO_INCREMENT,
  theme_id   SMALLINT(4)  UNSIGNED                NOT NULL DEFAULT 0,
  item_ordre TINYINT(2)   UNSIGNED                NOT NULL DEFAULT 0 COMMENT "Commence à 0.",
  item_ref   VARCHAR(3)   COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  item_nom   VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  item_abrev VARCHAR(15)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  item_coef  TINYINT(2)   UNSIGNED                NOT NULL DEFAULT 1,
  item_cart  TINYINT(1)   UNSIGNED                NOT NULL DEFAULT 1 COMMENT "0 pour empêcher les élèves de demander une évaluation sur cet item.",
  item_lien  VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  item_comm  TEXT         COLLATE utf8_unicode_ci NOT NULL COMMENT "Commentaire associé à l'item, par exemple des échelles descriptives.",
  PRIMARY KEY (item_id),
  KEY theme_id (theme_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
