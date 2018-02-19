DROP TABLE IF EXISTS sacoche_livret_saisie_memo_detail;

-- Attention : pas de valeur par défaut possible pour les champs TEXT et BLOB... sauf NULL !

CREATE TABLE sacoche_livret_saisie_memo_detail (
  livret_saisie_id MEDIUMINT(8) UNSIGNED NOT NULL       DEFAULT 0,
  acquis_detail    TEXT         COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY ( livret_saisie_id )
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT="Pour archiver le détail des items évalués avec leur score et leur état d'acquisition.";
