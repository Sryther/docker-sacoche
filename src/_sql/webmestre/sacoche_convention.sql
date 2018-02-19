DROP TABLE IF EXISTS sacoche_convention;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."
-- Attention : pas de valeur par défaut possible pour les champs TEXT et BLOB
-- Attention : pour un champ DATE ou DATETIME, DEFAULT NOW() ne fonctionne qu`à partir de MySQL 5.6.5
-- Attention : pour un champ DATE ou DATETIME, la configuration NO_ZERO_DATE (incluse dans le mode strict de MySQL 5.7.4 à 5.7.7), interdit les valeurs en dehors de 1000-01-01 00:00:00 à 9999-12-31 23:59:59

CREATE TABLE sacoche_convention (
  convention_id          SMALLINT(5)  UNSIGNED                NOT NULL AUTO_INCREMENT,
  sacoche_base           MEDIUMINT(8) UNSIGNED                NOT NULL DEFAULT 0,
  connexion_nom          VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT '""',
  convention_date_debut  DATE                                          DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL.",
  convention_date_fin    DATE                                          DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL.",
  convention_creation    DATE                                          DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL.",
  convention_signature   DATE                                          DEFAULT NULL,
  convention_paiement    DATE                                          DEFAULT NULL,
  convention_relance     DATE                                          DEFAULT NULL,
  convention_activation  TINYINT(1)   UNSIGNED                NOT NULL DEFAULT 0,
  convention_mail_renouv DATE                                          DEFAULT NULL,
  convention_commentaire TEXT         COLLATE utf8_unicode_ci,
  PRIMARY KEY (convention_id),
  UNIQUE KEY (sacoche_base,connexion_nom,convention_date_debut),
  KEY convention_date_fin (convention_date_fin),
  KEY convention_activation (convention_activation)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT "Pour les conventions ENT établissements (serveur Sésamath).";
