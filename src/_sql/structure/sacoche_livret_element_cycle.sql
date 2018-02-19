DROP TABLE IF EXISTS sacoche_livret_element_cycle;

CREATE TABLE sacoche_livret_element_cycle (
  livret_element_cycle_id  TINYINT(1) UNSIGNED                NOT NULL DEFAULT 0,
  livret_element_cycle_nom CHAR(7)    COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (livret_element_cycle_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_livret_element_cycle DISABLE KEYS;

INSERT INTO sacoche_livret_element_cycle (livret_element_cycle_id, livret_element_cycle_nom) VALUES
(2, "Cycle 2"),
(3, "Cycle 3"),
(4, "Cycle 4");

ALTER TABLE sacoche_livret_element_cycle ENABLE KEYS;
