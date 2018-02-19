DROP TABLE IF EXISTS sacoche_socle_cycle;

CREATE TABLE sacoche_socle_cycle (
  socle_cycle_id          TINYINT(1)  UNSIGNED                NOT NULL DEFAULT 0,
  socle_cycle_ordre       TINYINT(1)  UNSIGNED                NOT NULL DEFAULT 0,
  socle_cycle_nom         CHAR(7)     COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  socle_cycle_description VARCHAR(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (socle_cycle_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_socle_cycle DISABLE KEYS;

INSERT INTO sacoche_socle_cycle (socle_cycle_id, socle_cycle_ordre, socle_cycle_nom, socle_cycle_description) VALUES
(2, 1, "Cycle 2", "CP CE1 CE2 (apprentissages fondamentaux)"),
(3, 2, "Cycle 3", "CM1 CM2 6e (consolidation)"),
(4, 3, "Cycle 4", "5e 4e 3e (approfondissements)");

ALTER TABLE sacoche_socle_cycle ENABLE KEYS;
