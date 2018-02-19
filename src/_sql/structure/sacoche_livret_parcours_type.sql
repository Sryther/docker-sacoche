DROP TABLE IF EXISTS sacoche_livret_parcours_type;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."

CREATE TABLE sacoche_livret_parcours_type (
  livret_parcours_type_code VARCHAR(7)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_parcours_type_nom  VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (livret_parcours_type_code)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_livret_parcours_type DISABLE KEYS;

INSERT INTO sacoche_livret_parcours_type (livret_parcours_type_code, livret_parcours_type_nom) VALUES
("PAR_AVN", "Parcours avenir"                              ),
("PAR_CIT", "Parcours citoyen"                             ),
("PAR_ART", "Parcours d'éducation artistique et culturelle"),
("PAR_SAN", "Parcours éducatif de santé"                   );

ALTER TABLE sacoche_livret_parcours_type ENABLE KEYS;
