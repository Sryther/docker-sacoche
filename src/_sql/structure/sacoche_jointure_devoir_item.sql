DROP TABLE IF EXISTS sacoche_jointure_devoir_item;

CREATE TABLE sacoche_jointure_devoir_item (
  devoir_id      MEDIUMINT(6) UNSIGNED NOT NULL DEFAULT 0,
  item_id        MEDIUMINT(6) UNSIGNED NOT NULL DEFAULT 0,
  jointure_ordre TINYINT(2)   UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY ( devoir_id , item_id ),
  KEY item_id (item_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
