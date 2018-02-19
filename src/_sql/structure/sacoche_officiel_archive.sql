DROP TABLE IF EXISTS sacoche_officiel_archive;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."
-- Attention : pas de valeur par défaut possible pour les champs TEXT et BLOB... sauf NULL !
-- Attention : pour un champ DATE ou DATETIME, DEFAULT NOW() ne fonctionne qu`à partir de MySQL 5.6.5
-- Attention : pour un champ DATE ou DATETIME, la configuration NO_ZERO_DATE (incluse dans le mode strict de MySQL 5.7.4 à 5.7.7), interdit les valeurs en dehors de 1000-01-01 00:00:00 à 9999-12-31 23:59:59

-- 4 colonnes archive_md5_image* car pour le bilan LSU de fin de cycle 2 on peut avoir logo EN + logo école + signature instit + signature directeur

CREATE TABLE sacoche_officiel_archive (
  officiel_archive_id              MEDIUMINT(6)             UNSIGNED                NOT NULL AUTO_INCREMENT,
  user_id                          MEDIUMINT(6)             UNSIGNED                NOT NULL DEFAULT 0,
  structure_uai                    CHAR(8)                  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  annee_scolaire                   VARCHAR(9)               COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  archive_type                     ENUM("sacoche","livret") COLLATE utf8_unicode_ci NOT NULL DEFAULT "sacoche",
  archive_ref                      VARCHAR(9)               COLLATE utf8_unicode_ci NOT NULL DEFAULT ""   COMMENT "Valeur de livret_page_ref ou de officiel_type.",
  periode_id                       MEDIUMINT(3)             UNSIGNED                NOT NULL DEFAULT 0,
  periode_nom                      VARCHAR(40)              COLLATE utf8_unicode_ci NOT NULL DEFAULT ""   COMMENT "Pour garder trace de cette chaîne.",
  structure_denomination           VARCHAR(50)              COLLATE utf8_unicode_ci NOT NULL DEFAULT ""   COMMENT "Pour garder trace de cette chaîne.",
  sacoche_version                  VARCHAR(11)              COLLATE utf8_unicode_ci NOT NULL DEFAULT ""   COMMENT "Pour adapter le code de restauration de l'archive si besoin.",
  archive_date_generation          DATE                                                      DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL.",
  archive_date_consultation_eleve  DATE                                                      DEFAULT NULL ,
  archive_date_consultation_parent DATE                                                      DEFAULT NULL ,
  archive_contenu                  MEDIUMTEXT               COLLATE utf8_unicode_ci          DEFAULT NULL COMMENT "Pour les relevés d'évaluations le contenu peut dépasser la capacité d'un type TEXT.",
  archive_md5_image1               CHAR(32)                 COLLATE utf8_unicode_ci          DEFAULT NULL,
  archive_md5_image2               CHAR(32)                 COLLATE utf8_unicode_ci          DEFAULT NULL,
  archive_md5_image3               CHAR(32)                 COLLATE utf8_unicode_ci          DEFAULT NULL,
  archive_md5_image4               CHAR(32)                 COLLATE utf8_unicode_ci          DEFAULT NULL,
  PRIMARY KEY (officiel_archive_id),
  UNIQUE KEY archive_id (user_id,structure_uai,annee_scolaire,archive_type,archive_ref,periode_id),
  KEY structure_uai (structure_uai),
  KEY annee_scolaire (annee_scolaire),
  KEY archive_type (archive_type),
  KEY archive_ref (archive_ref),
  KEY periode_id (periode_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
