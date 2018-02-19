DROP TABLE IF EXISTS sacoche_parametre_profil;

-- Attention : pas de valeur par défaut possible pour les champs TEXT et BLOB... sauf NULL !

CREATE TABLE IF NOT EXISTS sacoche_parametre_profil (
  user_profil_type    VARCHAR(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  profil_param_menu   TEXT        COLLATE utf8_unicode_ci,
  profil_param_favori TINYTEXT    COLLATE utf8_unicode_ci,
  PRIMARY KEY ( user_profil_type )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_parametre_profil DISABLE KEYS;

INSERT INTO sacoche_parametre_profil (user_profil_type, profil_param_menu, profil_param_favori) VALUES
('eleve'     , NULL, NULL),
('parent'    , NULL, NULL),
('professeur', NULL, NULL),
('directeur' , NULL, NULL);

ALTER TABLE sacoche_parametre_profil ENABLE KEYS;
