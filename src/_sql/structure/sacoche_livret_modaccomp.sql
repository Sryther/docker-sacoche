DROP TABLE IF EXISTS sacoche_livret_modaccomp;

CREATE TABLE sacoche_livret_modaccomp (
  livret_modaccomp_code VARCHAR(5)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_modaccomp_nom  VARCHAR(54) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (livret_modaccomp_code)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT="Modalités d'accompagnement";

ALTER TABLE sacoche_livret_modaccomp DISABLE KEYS;

INSERT INTO sacoche_livret_modaccomp VALUES
("PAP",   "Plan d’accompagnement personnalisé"),
("PAI",   "Projet d’accueil individualisé"),
("PPRE",  "Programme personnalisé de réussite éducative"),
("PPS",   "Projet personnalisé de scolarisation"),
("RASED", "Réseau d’aides spécialisées aux élèves en difficulté"),
("SEGPA", "Section d’enseignement général et professionnel adapté"),
("ULIS",  "Unité localisée pour l'inclusion scolaire"),
("UPE2A", "Unité pédagogique pour élèves allophones arrivants");

ALTER TABLE sacoche_livret_modaccomp ENABLE KEYS;
