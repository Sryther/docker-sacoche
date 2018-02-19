DROP TABLE IF EXISTS sacoche_livret_page;

CREATE TABLE sacoche_livret_page (
  livret_page_ref            VARCHAR(6)              COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_page_ordre          TINYINT(2)              UNSIGNED                NOT NULL DEFAULT 0,
  livret_page_moment         VARCHAR(17)             COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_page_titre_classe   VARCHAR(13)             COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_page_resume         VARCHAR(84)             COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_page_periodicite    ENUM("periode","cycle") COLLATE utf8_unicode_ci NOT NULL DEFAULT "periode",
  livret_page_rubrique_type  VARCHAR(10)             COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_page_rubrique_join  VARCHAR(7)              COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Modifiable, pour indiquer le type de jointure à utiliser (matiere | domaine | theme | item | user).",
  livret_page_colonne        VARCHAR(11)             COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Modifiable pour 6e 5e 4e 3e (moyenne | pourcentage | position | objectif).",
  livret_page_moyenne_classe TINYINT(1)              UNSIGNED                NOT NULL DEFAULT 0  COMMENT "Modifiable pour 6e 5e 4e 3e.",
  livret_page_epi            TINYINT(1)              UNSIGNED                NOT NULL DEFAULT 0,
  livret_page_ap             TINYINT(1)              UNSIGNED                NOT NULL DEFAULT 0,
  livret_page_parcours       VARCHAR(31)             COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Chaîne de livret_parcours_type_code (PAR_AVN,PAR_CIT,PAR_ART,PAR_SAN).",
  livret_page_vie_scolaire   TINYINT(1)              UNSIGNED                NOT NULL DEFAULT 0,
  PRIMARY KEY (livret_page_ref),
  UNIQUE KEY livret_page_ordre (livret_page_ordre),
  KEY livret_page_periodicite (livret_page_periodicite),
  KEY livret_page_rubrique_type (livret_page_rubrique_type),
  KEY livret_page_colonne (livret_page_colonne)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT="Livret Scolaire Unique";

ALTER TABLE sacoche_livret_page DISABLE KEYS;

INSERT INTO sacoche_livret_page (livret_page_ref, livret_page_ordre, livret_page_moment, livret_page_titre_classe, livret_page_resume, livret_page_periodicite, livret_page_rubrique_type, livret_page_rubrique_join, livret_page_colonne, livret_page_moyenne_classe, livret_page_epi, livret_page_ap, livret_page_parcours, livret_page_vie_scolaire) VALUES
("cycle1", 19, "Fin de maternelle", "Classe de GS" , "Synthèse des acquis scolaires"                                                       , "cycle"  , "c1_theme"  , "theme"  , "reussite", 0, 0, 0,                                "", 0),
("cp",     21, "Niveau CP"        , "Classe de CP" , "Suivi des acquis scolaires - Bilan de l'acquisition des connaissances et compétences", "periode", "c2_domaine", "domaine", "objectif", 0, 0, 0,         "PAR_CIT,PAR_ART,PAR_SAN", 0),
("ce1",    22, "Niveau CE1"       , "Classe de CE1", "Suivi des acquis scolaires - Bilan de l'acquisition des connaissances et compétences", "periode", "c2_domaine", "domaine", "objectif", 0, 0, 0,         "PAR_CIT,PAR_ART,PAR_SAN", 0),
("ce2",    23, "Niveau CE2"       , "Classe de CE2", "Suivi des acquis scolaires - Bilan de l'acquisition des connaissances et compétences", "periode", "c2_domaine", "domaine", "objectif", 0, 0, 0,         "PAR_CIT,PAR_ART,PAR_SAN", 0),
("cycle2", 29, "Fin de cycle 2"   , "Classe de CE2", "Maîtrise des composantes du socle - Synthèse des acquis scolaires"                   , "cycle"  , "c2_socle"  , "item"   , "maitrise", 0, 0, 0,                                "", 0),
("cm1",    31, "Niveau CM1"       , "Classe de CM1", "Suivi des acquis scolaires - Bilan de l'acquisition des connaissances et compétences", "periode", "c3_domaine", "domaine", "objectif", 0, 0, 0,         "PAR_CIT,PAR_ART,PAR_SAN", 0),
("cm2",    32, "Niveau CM2"       , "Classe de CM2", "Suivi des acquis scolaires - Bilan de l'acquisition des connaissances et compétences", "periode", "c3_domaine", "domaine", "objectif", 0, 0, 0,         "PAR_CIT,PAR_ART,PAR_SAN", 0),
("6e",     33, "Niveau 6e"        , "Classe de 6e" , "Suivi des acquis scolaires - Bilan de l'acquisition des connaissances et compétences", "periode", "c3_matiere", "matiere", "moyenne" , 1, 1, 1, "PAR_AVN,PAR_CIT,PAR_ART,PAR_SAN", 1),
("cycle3", 39, "Fin de cycle 3"   , "Classe de 6e" , "Maîtrise des composantes du socle - Synthèse des acquis scolaires"                   , "cycle"  , "c3_socle"  , "item"   , "maitrise", 0, 0, 0,                                "", 0),
("5e",     41, "Niveau 5e"        , "Classe de 5e" , "Suivi des acquis scolaires - Bilan de l'acquisition des connaissances et compétences", "periode", "c4_matiere", "matiere", "moyenne" , 1, 1, 1, "PAR_AVN,PAR_CIT,PAR_ART,PAR_SAN", 1),
("4e",     42, "Niveau 4e"        , "Classe de 4e" , "Suivi des acquis scolaires - Bilan de l'acquisition des connaissances et compétences", "periode", "c4_matiere", "matiere", "moyenne" , 1, 1, 1, "PAR_AVN,PAR_CIT,PAR_ART,PAR_SAN", 1),
("3e",     43, "Niveau 3e"        , "Classe de 3e" , "Suivi des acquis scolaires - Bilan de l'acquisition des connaissances et compétences", "periode", "c4_matiere", "matiere", "moyenne" , 1, 1, 1, "PAR_AVN,PAR_CIT,PAR_ART,PAR_SAN", 1),
("cycle4", 49, "Fin de cycle 4"   , "Classe de 3e" , "Maîtrise des composantes du socle - Synthèse des acquis scolaires"                   , "cycle"  , "c4_socle"  , "item"   , "maitrise", 0, 0, 0,                                "", 0);

ALTER TABLE sacoche_livret_page ENABLE KEYS;
