DROP TABLE IF EXISTS sacoche_jointure_selection_item;

CREATE TABLE sacoche_jointure_selection_item (
  selection_item_id MEDIUMINT(6) UNSIGNED NOT NULL DEFAULT 0,
  item_id           MEDIUMINT(6) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY ( selection_item_id , item_id ),
  KEY item_id (item_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
