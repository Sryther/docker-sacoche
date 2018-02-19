DROP TABLE IF EXISTS sacoche_referentiel;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."
-- Attention : pour un champ DATE ou DATETIME, DEFAULT NOW() ne fonctionne qu`à partir de MySQL 5.6.5
-- Attention : pour un champ DATE ou DATETIME, la configuration NO_ZERO_DATE (incluse dans le mode strict de MySQL 5.7.4 à 5.7.7), interdit les valeurs en dehors de 1000-01-01 00:00:00 à 9999-12-31 23:59:59

CREATE TABLE sacoche_referentiel (
  matiere_id                    SMALLINT(4)                                                                                                UNSIGNED                NOT NULL DEFAULT 0,
  niveau_id                     MEDIUMINT(6)                                                                                               UNSIGNED                NOT NULL DEFAULT 0,
  referentiel_partage_etat      ENUM("bof","non","oui","hs")                                                                               COLLATE utf8_unicode_ci NOT NULL DEFAULT "non"         COMMENT "[oui] = référentiel partagé sur le serveur communautaire ; [non] = référentiel non partagé avec la communauté ; [bof] = référentiel dont le partage est sans intérêt (pas novateur) ; [hs] = référentiel dont le partage est sans objet (matière spécifique)",
  referentiel_partage_date      DATE                                                                                                                                        DEFAULT NULL          COMMENT "Ne vaut normalement jamais NULL.",
  referentiel_calcul_methode    ENUM("geometrique","arithmetique","classique","bestof1","bestof2","bestof3","frequencemin","frequencemax") COLLATE utf8_unicode_ci NOT NULL DEFAULT "geometrique" COMMENT "Coefficients en progression géométrique, arithmetique, ou moyenne classique non pondérée, ou conservation des meilleurs scores. Valeur surclassant la configuration par défaut.",
  referentiel_calcul_limite     TINYINT(2)                                                                                                 UNSIGNED                NOT NULL DEFAULT 5             COMMENT "Nombre maximum de dernières évaluations prises en comptes (0 pour les prendre toutes). Valeur surclassant la configuration par défaut.",
  referentiel_calcul_retroactif ENUM("non","oui","annuel")                                                                                 COLLATE utf8_unicode_ci NOT NULL DEFAULT "non"         COMMENT "Avec ou sans prise en compte des évaluations antérieures. Valeur surclassant la configuration par défaut.",
  referentiel_mode_synthese     ENUM("inconnu","sans","domaine","theme")                                                                   COLLATE utf8_unicode_ci NOT NULL DEFAULT "inconnu",
  referentiel_mode_livret       ENUM("domaine","theme","item")                                                                             COLLATE utf8_unicode_ci NOT NULL DEFAULT "domaine",
  referentiel_information       VARCHAR(128)                                                                                               COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY ( matiere_id , niveau_id ),
  KEY niveau_id (niveau_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
