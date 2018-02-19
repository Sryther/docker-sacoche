DROP TABLE IF EXISTS sacoche_jointure_message_destinataire;

CREATE TABLE IF NOT EXISTS sacoche_jointure_message_destinataire (
  message_id        MEDIUMINT(6)                                           UNSIGNED                NOT NULL DEFAULT 0,
  user_profil_type  VARCHAR(15)                                            COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  destinataire_type ENUM("all","niveau","classe","groupe","besoin","user") COLLATE utf8_unicode_ci NOT NULL DEFAULT "user",
  destinataire_id   MEDIUMINT(6)                                           UNSIGNED                NOT NULL DEFAULT 0,
  PRIMARY KEY ( message_id , user_profil_type , destinataire_type , destinataire_id ),
  KEY destinataire ( destinataire_type , destinataire_id ),
  KEY message_id (message_id),
  KEY user_profil_type (user_profil_type)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
