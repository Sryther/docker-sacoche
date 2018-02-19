DROP TABLE IF EXISTS sacoche_notification;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."
-- Attention : pas de valeur par défaut possible pour les champs TEXT et BLOB... sauf NULL !
-- Attention : pour un champ DATE ou DATETIME, DEFAULT NOW() ne fonctionne qu`à partir de MySQL 5.6.5
-- Attention : pour un champ DATE ou DATETIME, la configuration NO_ZERO_DATE (incluse dans le mode strict de MySQL 5.7.4 à 5.7.7), interdit les valeurs en dehors de 1000-01-01 00:00:00 à 9999-12-31 23:59:59

CREATE TABLE sacoche_notification (
  notification_id         INT(10)                                             UNSIGNED                NOT NULL AUTO_INCREMENT COMMENT "Table en lien avec les tables sacoche_abonnement et sacoche_jointure_user_abonnement.",
  user_id                 MEDIUMINT(6)                                        UNSIGNED                NOT NULL DEFAULT 0,
  abonnement_ref          VARCHAR(30)                                         COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  notification_attente_id MEDIUMINT(8)                                        UNSIGNED                         DEFAULT NULL   COMMENT "En cas de modification, pour retrouver une notification non encore envoyée ; passé à NULL une fois la notification envoyée.",
  notification_statut     ENUM("attente","consultable","consultée","envoyée") COLLATE utf8_unicode_ci NOT NULL DEFAULT "attente",
  notification_date       DATETIME                                                                             DEFAULT NULL   COMMENT "Ne vaut normalement jamais NULL.",
  notification_contenu    TEXT                                                COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (notification_id),
  KEY user_id (user_id),
  KEY abonnement_ref (abonnement_ref),
  KEY notification_attente_id (notification_attente_id),
  KEY notification_statut (notification_statut),
  KEY notification_date (notification_date)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
