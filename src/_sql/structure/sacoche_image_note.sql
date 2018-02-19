DROP TABLE IF EXISTS sacoche_image_note;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."
-- Attention : pas de valeur par défaut possible pour les champs TEXT et BLOB... sauf NULL !

CREATE TABLE sacoche_image_note (
  image_note_id   SMALLINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
  image_contenu_h MEDIUMBLOB           NOT NULL,
  image_contenu_v MEDIUMBLOB           NOT NULL,
  PRIMARY KEY (image_note_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
