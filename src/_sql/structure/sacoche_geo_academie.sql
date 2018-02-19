DROP TABLE IF EXISTS sacoche_geo_academie;

CREATE TABLE sacoche_geo_academie (
  geo_academie_id  TINYINT(2)  UNSIGNED                NOT NULL DEFAULT 0,
  geo_academie_nom VARCHAR(25) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (geo_academie_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_geo_academie DISABLE KEYS;

INSERT INTO sacoche_geo_academie VALUES
( 1, "Paris"),
( 2, "Aix-Marseille"),
( 3, "Besançon"),
( 4, "Bordeaux"),
( 5, "Caen"),
( 6, "Clermont-Ferrand"),
( 7, "Dijon"),
( 8, "Grenoble"),
( 9, "Lille"),
(10, "Lyon"),
(11, "Montpellier"),
(12, "Nancy-Metz"),
(13, "Poitiers"),
(14, "Rennes"),
(15, "Strasbourg"),
(16, "Toulouse"),
(17, "Nantes"),
(18, "Orléans-Tours"),
(19, "Reims"),
(20, "Amiens"),
(21, "Rouen"),
(22, "Limoges"),
(23, "Nice"),
(24, "Créteil"),
(25, "Versailles"),
(27, "Corse"),
(28, "Réunion"),
(31, "Martinique"),
(32, "Guadeloupe"),
(33, "Guyane"),
(40, "Nouvelle-Calédonie"),
(41, "Polynésie Française"),
(42, "Wallis-et-Futuna"),
(43, "Mayotte"),
(44, "St-Pierre-et-Miquelon"),
(99, "Sans objet");

ALTER TABLE sacoche_geo_academie ENABLE KEYS;
