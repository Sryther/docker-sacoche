DROP TABLE IF EXISTS sacoche_parametre_note;

CREATE TABLE sacoche_parametre_note (
  note_id      TINYINT(1)  UNSIGNED                NOT NULL AUTO_INCREMENT,
  note_actif   TINYINT(1)  UNSIGNED                NOT NULL DEFAULT 0  COMMENT "Au minimum 2 codes de notation actifs",
  note_ordre   TINYINT(1)  UNSIGNED                NOT NULL DEFAULT 0  COMMENT "De 1 à 6 ; à considérer pour tous les affichages",
  note_valeur  TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0  COMMENT "De 0 à 200 (en général 100 maxi) ; pour les codes actifs, doit être cohérent avec l'ordre.",
  note_image   VARCHAR(63) COLLATE utf8_unicode_ci NOT NULL DEFAULT "X",
  note_sigle   VARCHAR(3)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Équivalent textuel pour les impressions en niveaux de gris",
  note_legende VARCHAR(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  note_clavier TINYINT(1)  UNSIGNED                NOT NULL DEFAULT 0  COMMENT "De 1 à 9",
  PRIMARY KEY (note_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_parametre_note DISABLE KEYS;

INSERT INTO sacoche_parametre_note VALUES 
( 1, 1, 1,   0, "disque-double_rouge"     , "RR", "Très insuffisant." , 1 ),
( 2, 1, 2,  33, "disque_rouge"            , "R" , "Insuffisant."      , 2 ),
( 3, 1, 3,  67, "disque_vert-fonce"       , "V" , "Satisfaisant."     , 3 ),
( 4, 1, 4, 100, "disque-double_vert-fonce", "VV", "Très satisfaisant.", 4 ),
( 5, 0, 5,   0, "X"                       , ""  , ""                  , 5 ),
( 6, 0, 6,   0, "X"                       , ""  , ""                  , 6 );

ALTER TABLE sacoche_parametre_note ENABLE KEYS;
