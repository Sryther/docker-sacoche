DROP TABLE IF EXISTS sacoche_parametre_acquis;

CREATE TABLE sacoche_parametre_acquis (
  acquis_id        TINYINT(1)  UNSIGNED                NOT NULL AUTO_INCREMENT,
  acquis_actif     TINYINT(1)  UNSIGNED                NOT NULL DEFAULT 0  COMMENT "Au minimum 2 états d'acquisition actifs",
  acquis_ordre     TINYINT(1)  UNSIGNED                NOT NULL DEFAULT 0  COMMENT "De 1 à 6 ; à considérer pour tous les affichages",
  acquis_seuil_min TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0  COMMENT "Entre 0 et 99 ; pour les états actifs, doit être cohérent avec l'ordre.",
  acquis_seuil_max TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0  COMMENT "Entre 1 et 100 ; pour les états actifs, doit être cohérent avec l'ordre.",
  acquis_valeur    TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0  COMMENT "Pourcentage d'acquisition considéré (entre 0 à 100) ; pour les états actifs, doit être cohérent avec l'ordre.",
  acquis_couleur   CHAR(7)     COLLATE utf8_unicode_ci NOT NULL DEFAULT "#ffffff",
  acquis_sigle     VARCHAR(3)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  acquis_legende   VARCHAR(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (acquis_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_parametre_acquis DISABLE KEYS;

INSERT INTO sacoche_parametre_acquis VALUES 
( 1, 1, 1,   0,  39,   0, "#ff9999", "NA", "Non acquis."           ),
( 2, 1, 2,  40,  60,  50, "#ffdd33", "VA", "Partiellement acquis." ),
( 3, 1, 3,  61, 100, 100, "#99ff99", "A" , "Acquis."               ),
( 4, 0, 4,   0,   0,   0, "#ffffff", ""  , ""                      ),
( 5, 0, 5,   0,   0,   0, "#ffffff", ""  , ""                      ),
( 6, 0, 6,   0,   0,   0, "#ffffff", ""  , ""                      );

ALTER TABLE sacoche_parametre_acquis ENABLE KEYS;
