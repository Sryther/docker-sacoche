DROP TABLE IF EXISTS sacoche_parent_adresse;

-- https://portail-presse-poste.net-courrier.extra.laposte.fr/actualites/du-nouveau-sur-ladresse

CREATE TABLE IF NOT EXISTS sacoche_parent_adresse (
  parent_id              MEDIUMINT(6) UNSIGNED                NOT NULL DEFAULT 0,
  adresse_ligne1         VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  adresse_ligne2         VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  adresse_ligne3         VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  adresse_ligne4         VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  adresse_postal_code    VARCHAR(10)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "@see http://fr.wikipedia.org/wiki/Code_postal",
  adresse_postal_libelle VARCHAR(45)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  adresse_pays_nom       VARCHAR(35)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (parent_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
