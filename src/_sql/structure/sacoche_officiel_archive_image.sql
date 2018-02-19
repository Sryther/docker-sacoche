DROP TABLE IF EXISTS sacoche_officiel_archive_image;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."
-- Attention : pas de valeur par défaut possible pour les champs TEXT et BLOB... sauf NULL !

CREATE TABLE sacoche_officiel_archive_image (
  archive_image_md5     CHAR(32)   COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  archive_image_contenu MEDIUMBLOB                         NOT NULL COMMENT "Archivage des images de sacoche_officiel_archive ici car cela fait gagner beaucoup de place (logo ou tampon de l'établissement inséré à chaque fois).",
  PRIMARY KEY (archive_image_md5)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
