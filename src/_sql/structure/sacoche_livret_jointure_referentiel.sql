DROP TABLE IF EXISTS sacoche_livret_jointure_referentiel;

CREATE TABLE sacoche_livret_jointure_referentiel (
  livret_rubrique_type          VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_rubrique_ou_matiere_id SMALLINT(4) UNSIGNED                NOT NULL DEFAULT 0,
  element_id                    SMALLINT(5) UNSIGNED                         DEFAULT 0  COMMENT "matiere_id | domaine_id | theme_id | item_id | user_id ; la nature étant indiquée par sacoche_livret_page.livret_page_rubrique_join",
  PRIMARY KEY ( livret_rubrique_type , livret_rubrique_ou_matiere_id , element_id ),
  KEY rubrique ( livret_rubrique_type , livret_rubrique_ou_matiere_id ),
  KEY livret_rubrique_ou_matiere_id (livret_rubrique_ou_matiere_id),
  KEY element_id (element_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
