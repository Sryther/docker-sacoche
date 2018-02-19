DROP TABLE IF EXISTS sacoche_niveau_famille;

CREATE TABLE sacoche_niveau_famille (
  niveau_famille_id        SMALLINT(3) UNSIGNED                NOT NULL DEFAULT 0,
  niveau_famille_categorie TINYINT(1)  UNSIGNED                NOT NULL DEFAULT 0 COMMENT "1 = Niveaux usuels ; 2 = Niveaux particuliers ; 3 = Niveaux classes",
  niveau_famille_ordre     TINYINT(1)  UNSIGNED                NOT NULL DEFAULT 0,
  niveau_famille_nom       VARCHAR(63) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (niveau_famille_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_niveau_famille DISABLE KEYS;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."
-- Attention : en cas d`ajout, adapter DB_lister_niveaux_famille() dans requetes_structure_administrateur.php

INSERT INTO sacoche_niveau_famille VALUES
(  1, 2,  1, "Cycles (regroupements de niveaux)"),
(  2, 2,  2, "CECRL (Cadre Européen Commun de Référence pour les Langues)"),
(  3, 2,  3, "APSA (Activités Physiques, Sportives et Artistiques)"),
(  4, 2,  4, "Niveaux adaptés (pour élèves à besoins particuliers)"),
( 60, 3,  1, "Premier degré"),                                           -- dispositif de formation 0~4 & 60~62
(100, 3,  2, "Collège"),                                                 -- dispositif de formation 100~106
(160, 3,  3, "SEGPA / Pré-apprentissage"),                               -- dispositif de formation 110~167
(200, 3,  4, "Filière générale"),                                        -- dispositif de formation 200~202
(210, 3,  4, "Filière technologique"),                                   -- dispositif de formation 210~214
(220, 3,  5, "BT (Brevet de Technicien)"),                               -- dispositif de formation 220~232
(240, 3,  6, "CAP (Certificat d'Aptitude Professionnelle) en 1 an"),     -- dispositif de formation 240
(241, 3,  6, "CAP (Certificat d'Aptitude Professionnelle) en 2 ans"),    -- dispositif de formation 241
(242, 3,  6, "CAP (Certificat d'Aptitude Professionnelle) en 3 ans"),    -- dispositif de formation 242
(243, 3,  6, "BEP (Brevet d'Etudes Professionnel)"),                     -- dispositif de formation 243~244
(247, 3,  6, "Bac Pro (Baccalauréat Professionnel)"),                    -- dispositif de formation 245~247
(250, 3,  6, "BMA (Brevet des Métiers d'Art) en 1 an"),                  -- dispositif de formation 250
(251, 3,  6, "BMA (Brevet des Métiers d'Art) en 2 ans"),                 -- dispositif de formation 251
(253, 3,  6, "MC (Mention Complémentaire)"),                             -- dispositif de formation 253
(254, 3,  6, "BP (Brevet Professionnel)"),                               -- dispositif de formation 254
(271, 3,  6, "CAPa (Certificat d'Aptitude Professionnelle Agricole)"),   -- dispositif de formation 271
(276, 3,  6, "Bac Pro A (Baccalauréat Professionnel Agricole)"),         -- dispositif de formation 276
(290, 3,  6, "Formations spécialisées"),                                 -- dispositif de formation 290~293 & 318~330
(301, 3,  6, "CPGE (Classes Préparatoires aux Grandes Écoles)"),         -- dispositif de formation 300~301
(310, 3,  6, "BTS (Brevet de Technicien Supérieur) en 1 an"),            -- dispositif de formation 310
(311, 3,  6, "BTS (Brevet de Technicien Supérieur) en 2 ans"),           -- dispositif de formation 311
(312, 3,  6, "BTS (Brevet de Technicien Supérieur) en 3 ans"),           -- dispositif de formation 312
(313, 3,  6, "DTS (Diplôme de Technicien Supérieur)"),                   -- dispositif de formation 313
(315, 3,  6, "DMA (Diplôme des Métiers d'Art) en 1 an"),                 -- dispositif de formation 315
(316, 3,  6, "DMA (Diplôme des Métiers d'Art) en 2 ans"),                -- dispositif de formation 316
(350, 3,  6, "DUT (Diplôme Universitaire de Technologie)"),              -- dispositif de formation 350
(370, 3,  6, "BTSa (Brevet de Technicien Supérieur Agricole) en 1 an"),  -- dispositif de formation 370
(371, 3,  6, "BTSa (Brevet de Technicien Supérieur Agricole) en 2 ans"), -- dispositif de formation 371
(390, 3,  7, "Préparations diverses post-BAC"),                          -- dispositif de formation 390~441
(740, 3,  7, "Formation complémentaire / Décrochage scolaire"),          -- dispositif de formation 740~742 & 753
(900, 3,  7, "LMD (Diplôme d'enseignement supérieur)"),                  -- dispositif de formation 900 (non référencé)
(999, 1,  0, "Niveaux principaux");

ALTER TABLE sacoche_niveau_famille ENABLE KEYS;
