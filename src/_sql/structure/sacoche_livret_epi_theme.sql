DROP TABLE IF EXISTS sacoche_livret_epi_theme;

CREATE TABLE sacoche_livret_epi_theme (
  livret_epi_theme_code    VARCHAR(7)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_epi_theme_origine TINYINT(1)  UNSIGNED                NOT NULL DEFAULT 2 COMMENT "1 pour les thématiques nationales, 2 pour les thématiques personnalisées",
  livret_epi_theme_nom     VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (livret_epi_theme_code)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_livret_epi_theme DISABLE KEYS;

INSERT INTO sacoche_livret_epi_theme (livret_epi_theme_code, livret_epi_theme_nom) VALUES
("EPI_SAN", 1, "Corps, santé, bien-être et sécurité"),
("EPI_ART", 1, "Culture et création artistiques"),
("EPI_EDD", 1, "Transition écologique et développement durable"),
("EPI_ICC", 1, "Information, communication, citoyenneté"),
("EPI_LGA", 1, "Langues et cultures de l'Antiquité"),
("EPI_LGE", 1, "Langues et cultures étrangères ou régionales"),
("EPI_PRO", 1, "Monde économique et professionnel"),
("EPI_STS", 1, "Sciences, technologie et société");

ALTER TABLE sacoche_livret_epi_theme ENABLE KEYS;
