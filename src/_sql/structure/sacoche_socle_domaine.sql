DROP TABLE IF EXISTS sacoche_socle_domaine;

CREATE TABLE sacoche_socle_domaine (
  socle_domaine_id           TINYINT(1)  UNSIGNED                NOT NULL DEFAULT 0,
  socle_domaine_ordre        TINYINT(1)  UNSIGNED                NOT NULL DEFAULT 0,
  socle_domaine_ordre_livret TINYINT(2)  UNSIGNED                         DEFAULT NULL,
  socle_domaine_code_livret  CHAR(7)     COLLATE utf8_unicode_ci          DEFAULT NULL,
  socle_domaine_nom_simple   VARCHAR(44) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  socle_domaine_nom_officiel VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (socle_domaine_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_socle_domaine DISABLE KEYS;

INSERT INTO sacoche_socle_domaine VALUES
(1, 1, NULL,      NULL, "Langages pour penser et communiquer"         , "Les langages pour penser et communiquer"           ),
(2, 2,   20, "MET_APP", "Méthodes et outils pour apprendre"           , "Les méthodes et outils pour apprendre"             ),
(3, 3,   30, "FRM_CIT", "Formation de la personne et du citoyen"      , "La formation de la personne et du citoyen"         ),
(4, 4,   40, "SYS_NAT", "Systèmes naturels et systèmes techniques"    , "Les systèmes naturels et les systèmes techniques"  ),
(5, 5,   50, "REP_MND", "Représentations du monde et activité humaine", "Les représentations du monde et l'activité humaine");

ALTER TABLE sacoche_socle_domaine ENABLE KEYS;
