DROP TABLE IF EXISTS sacoche_livret_colonne;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."

CREATE TABLE sacoche_livret_colonne (
  livret_colonne_id               TINYINT(2)  UNSIGNED                NOT NULL DEFAULT 0,
  livret_colonne_type             VARCHAR(11) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_colonne_ordre            TINYINT(1)  UNSIGNED                NOT NULL DEFAULT 0  COMMENT "De 1 à 3 ou 4.",
  livret_colonne_titre            VARCHAR(35) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_colonne_legende          VARCHAR(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Paramétrable pour l'échelle.",
  livret_colonne_seuil_defaut_min TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0  COMMENT "Entre 0 et 99 ; doit être cohérent avec l'ordre.",
  livret_colonne_seuil_defaut_max TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0  COMMENT "Entre 1 et 100 ; doit être cohérent avec l'ordre.",
  livret_colonne_couleur_1        CHAR(7)     COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_colonne_couleur_2        CHAR(7)     COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (livret_colonne_id),
  UNIQUE KEY (livret_colonne_type,livret_colonne_ordre),
  KEY livret_colonne_ordre (livret_colonne_ordre)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_livret_colonne DISABLE KEYS;

INSERT INTO sacoche_livret_colonne (livret_colonne_id, livret_colonne_type, livret_colonne_ordre, livret_colonne_titre, livret_colonne_legende, livret_colonne_seuil_defaut_min, livret_colonne_seuil_defaut_max, livret_colonne_couleur_1, livret_colonne_couleur_2) VALUES
(11, "reussite"   , 1, "Réussite",                          "ne réussit pas encore"  ,  0,  30, "#c7e1f5", ""),
(12, "reussite"   , 2, "Réussite",                          "est en voie de réussite", 31,  69, "#acd4f1", ""),
(13, "reussite"   , 3, "Réussite",                          "réussit souvent"        , 70, 100, "#91c9ed", ""),
(21, "objectif"   , 1, "Objectifs d'apprentissage",         "Non atteints"           ,  0,  34, "#e5f2fb", "#f2f8fd"),
(22, "objectif"   , 2, "Objectifs d'apprentissage",         "Partiellement atteints" , 35,  64, "#cce5f7", "#d8ecf9"),
(23, "objectif"   , 3, "Objectifs d'apprentissage",         "Atteints"               , 65,  89, "#b3d9f4", "#bfdff6"),
(24, "objectif"   , 4, "Objectifs d'apprentissage",         "Dépassés"               , 90, 100, "#9acef0", "#a6d4f2"),
(31, "maitrise"   , 1, "Maîtrise des composantes du socle", "Maîtrise insuffisante"  ,  0,  34, "#e5f2fb", "#f2f8fd"),
(32, "maitrise"   , 2, "Maîtrise des composantes du socle", "Maîtrise fragile"       , 35,  59, "#cce5f7", "#d8ecf9"),
(33, "maitrise"   , 3, "Maîtrise des composantes du socle", "Maîtrise satisfaisante" , 60,  80, "#b3d9f4", "#bfdff6"),
(34, "maitrise"   , 4, "Maîtrise des composantes du socle", "Très bonne maîtrise"    , 81, 100, "#9acef0", "#a6d4f2"),
(41, "position"   , 1, "Échelle",                           "1 sur 4"                ,  0,  24, "#dbe5f1", ""),
(42, "position"   , 2, "Échelle",                           "2 sur 4"                , 25,  49, "#8db3e2", ""),
(43, "position"   , 3, "Échelle",                           "3 sur 4"                , 50,  74, "#548dd4", ""),
(44, "position"   , 4, "Échelle",                           "4 sur 4"                , 75, 100, "#0070c0", ""),
(51, "moyenne"    , 1, "Moyenne",                           "de l'élève"             ,  0,  20, ""       , ""),
(52, "moyenne"    , 2, "Moyenne",                           "de la classe"           ,  0,  20, ""       , ""),
(61, "pourcentage", 1, "Pourcentage",                       "de l'élève"             ,  0, 100, ""       , ""),
(62, "pourcentage", 2, "Pourcentage",                       "de la classe"           ,  0, 100, ""       , "");

ALTER TABLE sacoche_livret_colonne ENABLE KEYS;
