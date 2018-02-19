DROP TABLE IF EXISTS sacoche_selection_item;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."

CREATE TABLE sacoche_selection_item (
  selection_item_id  MEDIUMINT(6) UNSIGNED                NOT NULL AUTO_INCREMENT,
  proprio_id         MEDIUMINT(6) UNSIGNED                NOT NULL DEFAULT 0,
  selection_item_nom VARCHAR(60)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (selection_item_id),
  KEY proprio_id (proprio_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
