DROP TABLE IF EXISTS sacoche_demande;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."
-- Attention : pas de valeur par défaut possible pour les champs TEXT et BLOB... sauf NULL !
-- Attention : pour un champ DATE ou DATETIME, DEFAULT NOW() ne fonctionne qu`à partir de MySQL 5.6.5
-- Attention : pour un champ DATE ou DATETIME, la configuration NO_ZERO_DATE (incluse dans le mode strict de MySQL 5.7.4 à 5.7.7), interdit les valeurs en dehors de 1000-01-01 00:00:00 à 9999-12-31 23:59:59

CREATE TABLE sacoche_demande (
  demande_id         MEDIUMINT(6)         UNSIGNED                NOT NULL AUTO_INCREMENT,
  eleve_id           MEDIUMINT(6)         UNSIGNED                NOT NULL DEFAULT 0,
  matiere_id         SMALLINT(4)          UNSIGNED                NOT NULL DEFAULT 0,
  item_id            MEDIUMINT(6)         UNSIGNED                NOT NULL DEFAULT 0,
  prof_id            MEDIUMINT(6)         UNSIGNED                NOT NULL DEFAULT 0       COMMENT "Dans le cas où l'élève adresse sa demande à un prof donné.",
  periode_debut_date DATE                                                  DEFAULT NULL    COMMENT "Date de début de la période considérée pour calculer le score.",
  demande_date       DATE                                                  DEFAULT NULL    COMMENT "Ne vaut normalement jamais NULL.",
  demande_score      TINYINT(3)           UNSIGNED                         DEFAULT NULL    COMMENT "Sert à mémoriser le score avant réévaluation pour ne pas avoir à le recalculer ; valeur NULL si item non évalué.",
  demande_statut     ENUM("eleve","prof") COLLATE utf8_unicode_ci NOT NULL DEFAULT "eleve" COMMENT "[eleve] pour une demande d'élève ; [prof] pour une prévision d'évaluation par le prof ; une annulation de l'élève ou du prof efface l'enregistrement",
  demande_messages   TEXT                 COLLATE utf8_unicode_ci NOT NULL,
  demande_doc        VARCHAR(255)         COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (demande_id),
  UNIQUE KEY demande_key (eleve_id,matiere_id,item_id),
  KEY matiere_id (matiere_id),
  KEY item_id (item_id),
  KEY prof_id (prof_id),
  KEY demande_statut (demande_statut)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
