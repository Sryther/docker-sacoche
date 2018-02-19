DROP TABLE IF EXISTS sacoche_image;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."
-- Attention : pas de valeur par défaut possible pour les champs TEXT et BLOB... sauf NULL !

CREATE TABLE sacoche_image (
  user_id       MEDIUMINT(6)                     UNSIGNED                NOT NULL DEFAULT 0 COMMENT "0 pour le tampon de l'établissement (objet signature) ou le logo de l'établissement",
  image_objet   ENUM("signature","photo","logo") COLLATE utf8_unicode_ci NOT NULL DEFAULT "photo" COMMENT "[photo] pour les élèves, [signature] pour les professeurs et directeurs, [logo] pour l'établissement",
  image_contenu MEDIUMBLOB                                               NOT NULL,
  image_format  CHAR(4)                          COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  image_largeur SMALLINT(4)                      UNSIGNED                NOT NULL DEFAULT 0,
  image_hauteur SMALLINT(4)                      UNSIGNED                NOT NULL DEFAULT 0,
  UNIQUE KEY user_id (user_id,image_objet)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
