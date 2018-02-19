DROP TABLE IF EXISTS sacoche_socle_composante;

CREATE TABLE sacoche_socle_composante (
  socle_composante_id           TINYINT(2)  UNSIGNED                NOT NULL DEFAULT 0,
  socle_domaine_id              TINYINT(1)  UNSIGNED                NOT NULL DEFAULT 0,
  socle_composante_ordre        TINYINT(1)  UNSIGNED                NOT NULL DEFAULT 0,
  socle_composante_ordre_livret TINYINT(2)  UNSIGNED                         DEFAULT NULL,
  socle_composante_code_livret  CHAR(7)     COLLATE utf8_unicode_ci          DEFAULT NULL,
  socle_composante_nom_simple   VARCHAR(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  socle_composante_nom_officiel VARCHAR(97) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (socle_composante_id),
  KEY socle_domaine_id (socle_domaine_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_socle_composante DISABLE KEYS;

INSERT INTO sacoche_socle_composante VALUES 
(11, 1, 1,   11, "CPD_FRA", "Langue française à l'oral et à l'écrit"                          , "Comprendre, s'exprimer en utilisant la langue française à l'oral et à l'écrit"),
(12, 1, 2,   12, "CPD_ETR", "Langues étrangères et régionales"                                , "Comprendre, s'exprimer en utilisant une langue étrangère et, le cas échéant, une langue régionale"),
(13, 1, 3,   13, "CPD_SCI", "Langages mathématiques, scientifiques et informatiques"          , "Comprendre, s'exprimer en utilisant les langages mathématiques, scientifiques et informatiques"),
(14, 1, 4,   14, "CPD_ART", "Langages des arts et du corps"                                   , "Comprendre, s'exprimer en utilisant les langages des arts et du corps"),
(21, 2, 1, NULL,      NULL, "Organisation du travail personnel"                               , ""),
(22, 2, 2, NULL,      NULL, "Coopération et réalisation de projets"                           , ""),
(23, 2, 3, NULL,      NULL, "Médias, démarches de recherche et de traitement de l'information", ""),
(24, 2, 4, NULL,      NULL, "Outils numériques pour échanger et communiquer"                  , ""),
(31, 3, 1, NULL,      NULL, "Expression de la sensibilité et des opinions, respect des autres", ""),
(32, 3, 2, NULL,      NULL, "La règle et le droit"                                            , ""),
(33, 3, 3, NULL,      NULL, "Réflexion et discernement"                                       , ""),
(34, 3, 4, NULL,      NULL, "Responsabilité, sens de l'engagement et de l'initiative"         , ""),
(41, 4, 1, NULL,      NULL, "Démarches scientifiques"                                         , ""),
(42, 4, 2, NULL,      NULL, "Conception, création, réalisation"                               , ""),
(43, 4, 3, NULL,      NULL, "Responsabilités individuelles et collectives"                    , ""),
(44, 4, 4, NULL,      NULL, "Connaissances à mobiliser"                                       , ""),
(51, 5, 1, NULL,      NULL, "L'espace et le temps"                                            , ""),
(52, 5, 2, NULL,      NULL, "Organisations et représentations du monde"                       , ""),
(53, 5, 3, NULL,      NULL, "Invention, élaboration, production"                              , ""),
(54, 5, 4, NULL,      NULL, "Connaissances à mobiliser"                                       , "");

ALTER TABLE sacoche_socle_composante ENABLE KEYS;
