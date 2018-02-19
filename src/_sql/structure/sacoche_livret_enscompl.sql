DROP TABLE IF EXISTS sacoche_livret_enscompl;

CREATE TABLE sacoche_livret_enscompl (
  livret_enscompl_id   TINYINT(1)  UNSIGNED                NOT NULL DEFAULT 0,
  livret_enscompl_code VARCHAR(3)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_enscompl_nom  VARCHAR(35) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (livret_enscompl_id),
  UNIQUE KEY (livret_enscompl_code)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT="Enseignements de complément";

ALTER TABLE sacoche_livret_enscompl DISABLE KEYS;

INSERT INTO sacoche_livret_enscompl VALUES
(0, "AUC", "Aucun"),
(1, "LCA", "Langues et cultures de l'Antiquité"),
(2, "LCR", "Langue et culture régionale"),
(3, "LSF", "Langue des signes française"),
(4, "LVE", "Langue vivante étrangère"),
(5, "PRO", "Découverte professionnelle");

ALTER TABLE sacoche_livret_enscompl ENABLE KEYS;
