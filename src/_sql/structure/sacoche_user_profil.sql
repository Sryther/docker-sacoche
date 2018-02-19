DROP TABLE IF EXISTS sacoche_user_profil;

CREATE TABLE sacoche_user_profil (
  user_profil_sigle               CHAR(3)     COLLATE utf8_unicode_ci     NOT NULL,
  user_profil_structure           TINYINT(1)  UNSIGNED                    NOT NULL DEFAULT 1,
  user_profil_disponible          TINYINT(1)  UNSIGNED                    NOT NULL DEFAULT 1,
  user_profil_actif               TINYINT(1)  UNSIGNED                    NOT NULL DEFAULT 1,
  user_profil_obligatoire         TINYINT(1)  UNSIGNED                    NOT NULL DEFAULT 0,
  user_profil_type                VARCHAR(15) COLLATE utf8_unicode_ci     NOT NULL DEFAULT "",
  user_profil_join_groupes        ENUM("sansobjet","auto","all","config") NOT NULL DEFAULT "sansobjet",
  user_profil_join_matieres       ENUM("sansobjet","auto","all","config") NOT NULL DEFAULT "sansobjet",
  user_profil_nom_court_singulier VARCHAR(15) COLLATE utf8_unicode_ci     NOT NULL DEFAULT "",
  user_profil_nom_court_pluriel   VARCHAR(15) COLLATE utf8_unicode_ci     NOT NULL DEFAULT "",
  user_profil_nom_long_singulier  VARCHAR(40) COLLATE utf8_unicode_ci     NOT NULL DEFAULT "",
  user_profil_nom_long_pluriel    VARCHAR(40) COLLATE utf8_unicode_ci     NOT NULL DEFAULT "",
  user_profil_login_modele        VARCHAR(50) COLLATE utf8_unicode_ci     NOT NULL DEFAULT "ppp.nnnnnnnn",
  user_profil_mdp_longueur_mini   TINYINT(1)  UNSIGNED                    NOT NULL DEFAULT 6,
  user_profil_mdp_date_naissance  TINYINT(1)  UNSIGNED                    NOT NULL DEFAULT 0,
  user_profil_duree_inactivite    TINYINT(3)  UNSIGNED                    NOT NULL DEFAULT 30,
  PRIMARY KEY (user_profil_sigle),
  KEY user_profil_structure (user_profil_structure),
  KEY user_profil_disponible (user_profil_disponible),
  KEY user_profil_actif (user_profil_actif),
  KEY user_profil_obligatoire (user_profil_obligatoire),
  KEY user_profil_type (user_profil_type)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_user_profil DISABLE KEYS;

INSERT INTO sacoche_user_profil VALUES 
("OUT", 0, 1, 1, 1, "public"        , "sansobjet", "sansobjet", "non connecté"  , "non connectés"  , "utilisateur non connecté"           , "utilisateurs non connectés"            , "ppp.nnnnnnnn", 6, 0,  0),
("WBM", 0, 1, 1, 1, "webmestre"     , "sansobjet", "sansobjet", "webmestre"     , "webmestres"     , "webmestre (responsable du serveur)" , "webmestres (responsables du serveur)"  , "ppp.nnnnnnnn", 6, 0, 15),
("ENT", 0, 1, 1, 1, "partenaire"    , "sansobjet", "sansobjet", "partenaire"    , "partenaires"    , "partenariat conventionné (ENT)"     , "partenariats conventionnés (ENT)"      , "ppp.nnnnnnnn", 6, 0, 15),
("ADM", 1, 1, 1, 1, "administrateur", "sansobjet", "sansobjet", "administrateur", "administrateurs", "administrateur (de l'établissement)", "administrateurs (de l'établissement)"  , "ppp.nnnnnnnn", 6, 0, 30),
("ELV", 1, 1, 1, 1, "eleve"         , "config"   , "auto"     , "élève"         , "élèves"         , "élève"                              , "élèves"                                , "ppp.nnnnnnnn", 6, 0, 30),
("TUT", 1, 1, 1, 1, "parent"        , "auto"     , "auto"     , "parent"        , "parents"        , "responsable légal (parent, tuteur)" , "responsables légaux (parents, tuteurs)", "ppp.nnnnnnnn", 6, 0, 30),
("AVS", 1, 0, 0, 0, "parent"        , "auto"     , "auto"     , "AVS"           , "AVS"            , "auxiliaire de vie scolaire (AVS)"   , "auxiliaires de vie scolaire (AVS)"     , "ppp.nnnnnnnn", 6, 0, 30),
("DIR", 1, 1, 1, 1, "directeur"     , "all"      , "all"      , "directeur"     , "directeurs"     , "personnel de direction"             , "personnels de direction"               , "ppp.nnnnnnnn", 6, 0, 30),
("ENS", 1, 1, 1, 1, "professeur"    , "config"   , "config"   , "professeur"    , "professeurs"    , "personnel enseignant"               , "personnels enseignants"                , "ppp.nnnnnnnn", 6, 0, 30),
("IEX", 1, 1, 0, 0, "professeur"    , "config"   , "config"   , "intervenant"   , "intervenants"   , "intervenant extérieur"              , "intervenants extérieurs"               , "ppp.nnnnnnnn", 6, 0, 30),
("DOC", 1, 1, 1, 0, "professeur"    , "all"      , "config"   , "documentaliste", "documentalistes", "professeur documentaliste"          , "professeurs documentalistes"           , "ppp.nnnnnnnn", 6, 0, 30),
("EDU", 1, 1, 1, 0, "professeur"    , "all"      , "config"   , "CPE"           , "CPE"            , "conseiller d'éducation (CPE)"       , "conseillers d'éducation (CPE)"         , "ppp.nnnnnnnn", 6, 0, 30),
("AED", 1, 1, 0, 0, "professeur"    , "all"      , "config"   , "AED"           , "AED"            , "assistant d'éducation (AED)"        , "assistants d'éducation (AED)"          , "ppp.nnnnnnnn", 6, 0, 30),
("SUR", 1, 1, 0, 0, "professeur"    , "all"      , "config"   , "surveillant"   , "surveillants"   , "personnel de surveillance"          , "personnels de surveillance"            , "ppp.nnnnnnnn", 6, 0, 30),
("ORI", 1, 1, 0, 0, "professeur"    , "all"      , "config"   , "COP"           , "COP"            , "conseiller d'orientation (COP)"     , "conseillers d'orientation (COP)"       , "ppp.nnnnnnnn", 6, 0, 30),
("MDS", 1, 1, 0, 0, "professeur"    , "all"      , "config"   , "médecin"       , "médecins"       , "personnel médico-social"            , "personnels médico-sociaux"             , "ppp.nnnnnnnn", 6, 0, 30),
("ADF", 1, 1, 0, 0, "professeur"    , "all"      , "config"   , "administratif" , "administratifs" , "personnel administratif"            , "personnels administratifs"             , "ppp.nnnnnnnn", 6, 0, 30),
("PSE", 1, 1, 0, 0, "professeur"    , "all"      , "config"   , "socioéducatif" , "socioéducatifs" , "personnel socio-éducatif"           , "personnels socio-éducatifs"            , "ppp.nnnnnnnn", 6, 0, 30),
("INS", 1, 0, 0, 0, "inspecteur"    , "auto"     , "auto"     , "inspecteur"    , "inspecteurs"    , "inspecteur"                         , "inspecteurs"                           , "ppp.nnnnnnnn", 6, 0, 30);

ALTER TABLE sacoche_user_profil ENABLE KEYS;
