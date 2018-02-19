DROP TABLE IF EXISTS sacoche_livret_export;

-- Attention : pas de valeur par défaut possible pour les champs TEXT et BLOB... sauf NULL !

CREATE TABLE sacoche_livret_export (
  user_id                 MEDIUMINT(6)            UNSIGNED                NOT NULL DEFAULT 0,
  livret_page_ref         VARCHAR(6)              COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_page_periodicite ENUM("periode","cycle") COLLATE utf8_unicode_ci NOT NULL DEFAULT "periode",
  jointure_periode        TINYINT(2)              UNSIGNED                         DEFAULT NULL COMMENT "Renseigné si livret_page_periodicite = periode ; @see sacoche_periode.periode_livret",
  sacoche_version         VARCHAR(11)             COLLATE utf8_unicode_ci NOT NULL DEFAULT ""   COMMENT "Pour écarter des exports obsolètes si besoin.",
  export_contenu          TEXT                    COLLATE utf8_unicode_ci          DEFAULT NULL,
  UNIQUE KEY export_id ( user_id , livret_page_periodicite , jointure_periode ),
  KEY livret_page_ref (livret_page_ref),
  KEY livret_page_periodicite (livret_page_periodicite),
  KEY jointure_periode (jointure_periode)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
