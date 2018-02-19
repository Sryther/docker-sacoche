DROP TABLE IF EXISTS sacoche_structure_origine;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."

CREATE TABLE sacoche_structure_origine (
  structure_uai          CHAR(8)     COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  structure_denomination VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  structure_localisation VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  structure_courriel     VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (structure_uai)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
