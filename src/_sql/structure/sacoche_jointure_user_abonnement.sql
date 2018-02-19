DROP TABLE IF EXISTS sacoche_jointure_user_abonnement;

CREATE TABLE sacoche_jointure_user_abonnement (
  user_id        MEDIUMINT(6)               UNSIGNED                NOT NULL DEFAULT 0,
  abonnement_ref VARCHAR(30)                COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  jointure_mode  ENUM("courriel","accueil") COLLATE utf8_unicode_ci NOT NULL DEFAULT "courriel",
  PRIMARY KEY ( user_id , abonnement_ref ),
  KEY abonnement_ref (abonnement_ref)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
