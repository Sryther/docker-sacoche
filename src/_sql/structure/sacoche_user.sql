DROP TABLE IF EXISTS sacoche_user;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."
-- Attention : pas de valeur par défaut possible pour les champs TEXT et BLOB... sauf NULL !
-- Attention : pour un champ DATE ou DATETIME, DEFAULT NOW() ne fonctionne qu`à partir de MySQL 5.6.5
-- Attention : pour un champ DATE ou DATETIME, la configuration NO_ZERO_DATE (incluse dans le mode strict de MySQL 5.7.4 à 5.7.7), interdit les valeurs en dehors de 1000-01-01 00:00:00 à 9999-12-31 23:59:59

CREATE TABLE sacoche_user (
  user_id             MEDIUMINT(6)            UNSIGNED                NOT NULL AUTO_INCREMENT,
  user_sconet_id      MEDIUMINT(6)            UNSIGNED                NOT NULL DEFAULT 0   COMMENT "ELEVE.ELEVE.ID pour un élève ; INDIVIDU_ID pour un prof ; PERSONNE_ID pour un parent",
  user_sconet_elenoet SMALLINT(5)             UNSIGNED                NOT NULL DEFAULT 0   COMMENT "ELENOET pour un élève (entre 2000 et 5000 ; parfois appelé n° GEP avec un 0 devant). Ce champ sert aussi pour un import Factos (élèves et parents).",
  user_reference      CHAR(11)                COLLATE utf8_unicode_ci NOT NULL DEFAULT ""  COMMENT "Dans Sconet, ID_NATIONAL pour un élève (pour un prof ce pourrait être le NUMEN mais il n'est pas renseigné et il faudrait deux caractères de plus). Ce champ sert aussi pour un import tableur.",
  user_profil_sigle   CHAR(3)                 COLLATE utf8_unicode_ci NOT NULL DEFAULT ""  COMMENT "Nomenclature issue de la BCN (table n_fonction_filiere) et de user_profils SDET.",
  user_genre          ENUM("I","M","F")       COLLATE utf8_unicode_ci NOT NULL DEFAULT "I" COMMENT "Indéterminé / Masculin / Féminin",
  user_nom            VARCHAR(50)             COLLATE utf8_unicode_ci NOT NULL DEFAULT ""  COMMENT "Longueur max 100 dans Siècle...",
  user_prenom         VARCHAR(50)             COLLATE utf8_unicode_ci NOT NULL DEFAULT ""  COMMENT "Longueur max 100 dans Siècle...",
  user_naissance_date DATE                                                     DEFAULT NULL,
  user_email          VARCHAR(100)            COLLATE utf8_unicode_ci NOT NULL DEFAULT ""  COMMENT "Longueur max 254 dans Siècle... ; @see https://mydnic.be/post/longueur-dun-varchar-pour-un-champ-email",
  user_email_origine  ENUM("","user","admin") COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  user_login          VARCHAR(50)             COLLATE utf8_unicode_ci NOT NULL DEFAULT ""  COMMENT "Voir aussi sacoche_user_profil.user_profil_login_modele",
  user_password       CHAR(32)                COLLATE utf8_unicode_ci NOT NULL DEFAULT ""  COMMENT "En MD5 avec un salage.",
  user_langue         VARCHAR(6)              COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  user_daltonisme     TINYINT(1)              UNSIGNED                NOT NULL DEFAULT 0,
  user_connexion_date DATETIME                                                 DEFAULT NULL,
  user_sortie_date    DATE                                            NOT NULL DEFAULT "9999-12-31" COMMENT "Une valeur NULL par défaut compliquerait les requêtes (il faudrait tester NULL || > NOW ).",
  eleve_classe_id     MEDIUMINT(6)            UNSIGNED                NOT NULL DEFAULT 0,
  eleve_lv1           TINYINT(3)              UNSIGNED                NOT NULL DEFAULT 100 COMMENT "Langue vivante 1 pour le livret scolaire.",
  eleve_lv2           TINYINT(3)              UNSIGNED                NOT NULL DEFAULT 100 COMMENT "Langue vivante 2 pour le livret scolaire.",
  eleve_uai_origine   CHAR(8)                 COLLATE utf8_unicode_ci NOT NULL DEFAULT ""  COMMENT "Pour un envoi de documents officiels à l'établissement d'origine.",
  user_id_ent         VARCHAR(63)             COLLATE utf8_unicode_ci NOT NULL DEFAULT ""  COMMENT "Paramètre renvoyé après une identification CAS depuis un ENT (ça peut être le login, mais ça peut aussi être un numéro interne à l'ENT...).",
  user_id_gepi        VARCHAR(63)             COLLATE utf8_unicode_ci NOT NULL DEFAULT ""  COMMENT "Login de l'utilisateur dans Gepi utilisé pour un transfert note/moyenne vers un bulletin.",
  user_param_accueil  VARCHAR(127)            COLLATE utf8_unicode_ci NOT NULL DEFAULT ""  COMMENT "Ce qui est masqué (et non ce qui est affiché).",
  user_param_menu     TEXT                    COLLATE utf8_unicode_ci                      COMMENT "Ce qui est masqué (et non ce qui est affiché).",
  user_param_favori   TINYTEXT                COLLATE utf8_unicode_ci,
  user_pass_key       CHAR(32)                COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (user_id),
  UNIQUE KEY user_login (user_login),
  KEY profil_sigle (user_profil_sigle),
  KEY user_sortie_date (user_sortie_date),
  KEY eleve_classe_id (eleve_classe_id),
  KEY user_id_ent (user_id_ent),
  KEY user_id_gepi (user_id_gepi)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
