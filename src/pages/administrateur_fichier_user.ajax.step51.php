<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU Affero General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Publique Générale GNU Affero pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU Affero avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if(!isset($STEP))       {exit('Ce fichier ne peut être appelé directement !');}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 51 - Analyse des données des utilisateurs (tous les cas)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['classes'] -> $tab_i_classe_TO_id_base ; $tab_liens_id_base['groupes'] -> $tab_i_groupe_TO_id_base ; $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
$tab_liens_id_base = FileSystem::recuperer_fichier_infos_serializees( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'liens_id_base.txt' );
$tab_i_classe_TO_id_base  = $tab_liens_id_base['classes'];
$tab_i_groupe_TO_id_base  = $tab_liens_id_base['groupes'];
$tab_i_fichier_TO_id_base = $tab_liens_id_base['users'];
// On récupère le fichier avec les utilisateurs : $tab_users_fichier['champ'] : i -> valeur, avec comme champs : sconet_id / sconet_num / reference / profil / genre / nom / prenom / birth_date / courriel / uai_origine / lv1 / lv2 / classe / groupes / matieres / adresse / enfant
$tab_users_fichier = FileSystem::recuperer_fichier_infos_serializees( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'users.txt' );
// On récupère le fichier avec les classes : $tab_classes_fichier['ref'] : i -> ref ; $tab_classes_fichier['nom'] : i -> nom ; $tab_classes_fichier['niveau'] : i -> niveau
$tab_classes_fichier = FileSystem::recuperer_fichier_infos_serializees( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'classes.txt' );
// On récupère le fichier avec les dates de sortie des élèves
$tab_date_sortie = FileSystem::recuperer_fichier_infos_serializees( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'date_sortie.txt' );
// On récupère le contenu de la base pour comparer : $tab_users_base['champ'] : id -> valeur, avec comme champs : sconet_id / sconet_num / reference / profil / genre / nom / prenom / birth_date / courriel / email_origine / uai_origine / lv1 / lv2 / statut / classe / adresse
$tab_users_base                  = array();
$tab_users_base['sconet_id'    ] = array();
$tab_users_base['sconet_num'   ] = array();
$tab_users_base['reference'    ] = array();
$tab_users_base['profil_sigle' ] = array();
$tab_users_base['genre'        ] = array();
$tab_users_base['nom'          ] = array();
$tab_users_base['prenom'       ] = array();
$tab_users_base['birth_date'   ] = array();
$tab_users_base['courriel'     ] = array();
$tab_users_base['email_origine'] = array();
$tab_users_base['uai_origine'  ] = array();
$tab_users_base['lv1'          ] = array();
$tab_users_base['lv2'          ] = array();
$tab_users_base['sortie'       ] = array();
$tab_users_base['classe'       ] = array();
$tab_users_base['adresse'      ] = array();
$profil_type = ($import_profil!='professeur') ? $import_profil : array('professeur','directeur') ;
$with_classe = ($import_profil=='eleve') ? TRUE : FALSE ;
$liste_champs = 'user_id,user_sconet_id,user_sconet_elenoet,user_reference,user_profil_sigle,user_genre,user_nom,user_prenom,user_naissance_date,user_email,user_email_origine,user_sortie_date,eleve_uai_origine,eleve_lv1,eleve_lv2';
$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( $profil_type , 2 /*actuels_et_anciens*/ , $liste_champs , $with_classe , FALSE /*tri_statut*/ );
foreach($DB_TAB as $DB_ROW)
{
  $tab_users_base['sconet_id'    ][$DB_ROW['user_id']] = $DB_ROW['user_sconet_id'];
  $tab_users_base['sconet_num'   ][$DB_ROW['user_id']] = $DB_ROW['user_sconet_elenoet'];
  $tab_users_base['reference'    ][$DB_ROW['user_id']] = $DB_ROW['user_reference'];
  $tab_users_base['profil_sigle' ][$DB_ROW['user_id']] = $DB_ROW['user_profil_sigle'];
  $tab_users_base['genre'        ][$DB_ROW['user_id']] = $DB_ROW['user_genre'];
  $tab_users_base['nom'          ][$DB_ROW['user_id']] = $DB_ROW['user_nom'];
  $tab_users_base['prenom'       ][$DB_ROW['user_id']] = $DB_ROW['user_prenom'];
  $tab_users_base['birth_date'   ][$DB_ROW['user_id']] = To::date_mysql_to_french($DB_ROW['user_naissance_date']);
  $tab_users_base['courriel'     ][$DB_ROW['user_id']] = $DB_ROW['user_email'] ;
  $tab_users_base['email_origine'][$DB_ROW['user_id']] = $DB_ROW['user_email_origine'] ;
  $tab_users_base['uai_origine'  ][$DB_ROW['user_id']] = $DB_ROW['eleve_uai_origine'] ;
  $tab_users_base['lv1'          ][$DB_ROW['user_id']] = $DB_ROW['eleve_lv1'];
  $tab_users_base['lv2'          ][$DB_ROW['user_id']] = $DB_ROW['eleve_lv2'];
  $tab_users_base['sortie'       ][$DB_ROW['user_id']] = $DB_ROW['user_sortie_date'] ;
  $tab_users_base['classe'       ][$DB_ROW['user_id']] = ($import_profil=='eleve') ? $DB_ROW['groupe_ref'] : '' ;
}
// Pour préparer l'affichage
$lignes_ignorer   = '';
$lignes_ajouter   = '';
$lignes_retirer   = '';
$lignes_modifier  = '';
$lignes_conserver = '';
$lignes_inchanger = '';
// Pour préparer l'enregistrement des données
$tab_users_ajouter  = array();
$tab_users_modifier = array();
$tab_users_retirer  = array();
// Comparer fichier et base : c'est parti !
$tab_indices_fichier = array_keys($tab_users_fichier['sconet_id']);
// Parcourir chaque entrée du fichier
foreach($tab_indices_fichier as $i_fichier)
{
  $id_base = FALSE;
  // Recherche sur sconet_id
  if( (!$id_base) && ($tab_users_fichier['sconet_id'][$i_fichier]) )
  {
    $id_base = array_search($tab_users_fichier['sconet_id'][$i_fichier],$tab_users_base['sconet_id']);
  }
  // Recherche sur sconet_num
  if( (!$id_base) && ($tab_users_fichier['sconet_num'][$i_fichier]) )
  {
    $id_base = array_search($tab_users_fichier['sconet_num'][$i_fichier],$tab_users_base['sconet_num']);
  }
  // Si pas trouvé, recherche sur reference
  if( (!$id_base) && ($import_profil=='eleve') && isset($tab_users_fichier['old_ine'][$i_fichier]) )
  {
    $id_base = array_search($tab_users_fichier['old_ine'][$i_fichier],$tab_users_base['reference']);
  }
  if( (!$id_base) && ($tab_users_fichier['reference'][$i_fichier]) )
  {
    $id_base = array_search($tab_users_fichier['reference'][$i_fichier],$tab_users_base['reference']);
  }
  // Si pas trouvé, recherche sur nom prénom
  if(!$id_base)
  {
    $tab_id_nom    = array_keys($tab_users_base['nom'],$tab_users_fichier['nom'][$i_fichier]);
    $tab_id_prenom = array_keys($tab_users_base['prenom'],$tab_users_fichier['prenom'][$i_fichier]);
    $tab_id_commun = array_intersect($tab_id_nom,$tab_id_prenom);
    $nb_homonymes  = count($tab_id_commun);
    if($nb_homonymes==1)
    {
      list($inutile,$id_base) = each($tab_id_commun);
    }
  }
  // Cas [1] : présent dans le fichier, absent de la base, pas de classe dans le fichier (élèves uniquements) : contenu à ignorer (probablement des anciens élèves, ou des élèves jamais venus, qu'il est inutile d'importer)
  if( ($import_profil=='eleve') && (!$id_base) && (!$tab_users_fichier['classe'][$i_fichier]) )
  {
    $indication = ($import_profil=='eleve') ? substr($tab_users_fichier['classe'][$i_fichier],1) : $tab_users_fichier['profil_sigle'][$i_fichier] ;
    $lignes_ignorer .= '<tr><th>Ignorer</th><td>'.html($tab_users_fichier['sconet_id'][$i_fichier].' / '.$tab_users_fichier['sconet_num'][$i_fichier].' / '.$tab_users_fichier['reference'][$i_fichier].' || '.$tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ('.$indication.')').'</td></tr>'.NL;
  }
  // Cas [2] : présent dans le fichier, absent de la base, prof ou classe indiquée dans le fichier si élève : contenu à ajouter (nouvel élève ou nouveau professeur / directeur)
  elseif( (!$id_base) && ( ($import_profil!='eleve') || ($tab_users_fichier['classe'][$i_fichier]) ) )
  {
    $indication = ($import_profil=='eleve') ? Clean::ref(substr($tab_users_fichier['classe'][$i_fichier],1)) : $tab_users_fichier['profil_sigle'][$i_fichier] ;
    $lignes_ajouter .= '<tr><th>Ajouter <input id="add_'.$i_fichier.'" name="add_'.$i_fichier.'" type="checkbox" checked /></th><td>'.html($tab_users_fichier['sconet_id'][$i_fichier].' / '.$tab_users_fichier['sconet_num'][$i_fichier].' / '.$tab_users_fichier['reference'][$i_fichier].' || '.$tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ('.$indication.')').'</td></tr>'.NL;
    $id_classe = ( ($import_profil=='eleve') && isset($tab_i_classe_TO_id_base[$tab_users_fichier['classe'][$i_fichier]]) ) ? $tab_i_classe_TO_id_base[$tab_users_fichier['classe'][$i_fichier]] : 0 ;
    $email_origine = ($tab_users_fichier['courriel'][$i_fichier]) ? 'admin' : '' ;
    $tab_users_ajouter[$i_fichier] = array(
      'sconet_id'     => $tab_users_fichier['sconet_id'   ][$i_fichier] ,
      'sconet_num'    => $tab_users_fichier['sconet_num'  ][$i_fichier] ,
      'reference'     => $tab_users_fichier['reference'   ][$i_fichier] ,
      'genre'         => $tab_users_fichier['genre'       ][$i_fichier] ,
      'nom'           => $tab_users_fichier['nom'         ][$i_fichier] ,
      'prenom'        => $tab_users_fichier['prenom'      ][$i_fichier] ,
      'courriel'      => $tab_users_fichier['courriel'    ][$i_fichier] ,
      'email_origine' => $email_origine ,
      'profil_sigle'  => $tab_users_fichier['profil_sigle'][$i_fichier] ,
      'classe'        => $id_classe
    );
    if($import_profil=='eleve')
    {
      $tab_users_ajouter[$i_fichier] = array_merge( $tab_users_ajouter[$i_fichier] , array(
        'birth_date'  => $tab_users_fichier['birth_date'  ][$i_fichier] ,
        'uai_origine' => $tab_users_fichier['uai_origine' ][$i_fichier] ,
        'lv1'         => $tab_users_fichier['lv1'         ][$i_fichier] ,
        'lv2'         => $tab_users_fichier['lv2'         ][$i_fichier] ,
      ) );
    }
  }
  // Cas [3] : présent dans le fichier, présent dans la base, pas de classe dans le fichier (élèves uniquements), actuel dans la base : contenu à retirer (probablement des élèves nouvellement sortants)
  elseif( ($import_profil=='eleve') && (!$tab_users_fichier['classe'][$i_fichier]) && ($tab_users_base['sortie'][$id_base]==SORTIE_DEFAUT_MYSQL) )
  {
    $indication = ($import_profil=='eleve') ? $tab_users_base['classe'][$id_base] : $tab_users_base['profil_sigle'][$id_base] ;
    $date_sortie_fr = TODAY_FR;
    $lignes_retirer .= '<tr><th>Retirer <input id="del_'.$id_base.'" name="del_'.$id_base.'" type="checkbox" checked /></th><td>'.html($tab_users_fichier['sconet_id'][$i_fichier].' / '.$tab_users_fichier['sconet_num'][$i_fichier].' / '.$tab_users_base['reference'][$id_base].' || '.$tab_users_base['nom'][$id_base].' '.$tab_users_base['prenom'][$id_base].' ('.$indication.')').' || <b>Sortie : non &rarr; '.$date_sortie_fr.'</b></td></tr>'.NL;
    $tab_users_retirer[$id_base] = To::date_french_to_mysql($date_sortie_fr);
  }
  // Cas [4] : présent dans le fichier, présent dans la base, pas de classe dans le fichier (élèves uniquements), ancien dans la base : contenu inchangé (probablement des anciens élèves déjà écartés)
  elseif( ($import_profil=='eleve') && (!$tab_users_fichier['classe'][$i_fichier]) && ($tab_users_base['sortie'][$id_base]!=SORTIE_DEFAUT_MYSQL) )
  {
    $indication = ($import_profil=='eleve') ? substr($tab_users_fichier['classe'][$i_fichier],1) : $tab_users_fichier['profil_sigle'][$i_fichier] ;
    $lignes_inchanger .= '<tr><th>Ignorer</th><td>'.html($tab_users_fichier['sconet_id'][$i_fichier].' / '.$tab_users_fichier['sconet_num'][$i_fichier].' / '.$tab_users_fichier['reference'][$i_fichier].' || '.$tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ('.$indication.')').'</td></tr>'.NL;
  }
  else
  {
    // On compare les données de 2 enregistrements pour voir si des choses ont été modifiées
    $td_modif = '';
    $nb_modif = 0;
    $tab_champs = ($import_profil=='eleve')
                ? array(
                    'sconet_id'   => 'Id Sconet' ,
                    'sconet_num'  => 'n° Sconet' ,
                    'reference'   => 'Référence' ,
                    'genre'       => 'Genre' ,
                    'nom'         => 'Nom' ,
                    'prenom'      => 'Prénom' ,
                    'birth_date'  => 'Date Naiss.' ,
                    'courriel'    => 'Courriel' ,
                    'uai_origine' => 'Origine' ,
                    'lv1'         => 'LV1' ,
                    'lv2'         => 'LV2' ,
                    'classe'      => 'Classe' ,
                  )
                : array(
                    'sconet_id'     => 'Id Sconet' ,
                    'reference'     => 'Référence' ,
                    'profil_sigle'  => 'Profil' ,
                    'genre'         => 'Civilité' ,
                    'nom'           => 'Nom' ,
                    'prenom'        => 'Prénom' ,
                    'courriel'      => 'Courriel' ,
                  );
    foreach($tab_champs as $champ_ref => $champ_aff)
    {
      if($champ_ref=='classe')
      {
        $id_classe = (isset($tab_i_classe_TO_id_base[$tab_users_fichier['classe'][$i_fichier]])) ? $tab_i_classe_TO_id_base[$tab_users_fichier['classe'][$i_fichier]] : 0 ;
        $tab_users_fichier[$champ_ref][$i_fichier] = ($id_classe) ? $tab_classes_fichier['ref'][$tab_users_fichier['classe'][$i_fichier]] : '' ;
      }
      else if($champ_ref=='courriel')
      {
        $test_saisie_user  = ($tab_users_base['email_origine'][$id_base]=='user') && Outil::test_user_droit_specifique( $_SESSION['DROIT_MODIFIER_EMAIL'] , NULL , 0 , $tab_users_fichier['profil_sigle'][$i_fichier] , NULL ) ;
        $test_saisie_admin = ($tab_users_base['email_origine'][$id_base]=='admin') && $tab_users_base[$champ_ref][$id_base] && !$tab_users_fichier[$champ_ref][$i_fichier] ;
        // On n'écrase pas lors d'un import massif : une valeur personnalisée par l'utilisateur ; une suppression d'une valeur probablement renseignée manuellement par un administrateur.
        if( $test_saisie_user || $test_saisie_admin )
        {
          $tab_users_base[$champ_ref][$id_base] = $tab_users_fichier[$champ_ref][$i_fichier];
        }
      }
      else if($champ_ref=='uai_origine')
      {
        // N'est renseigné que pour un import SIECLE et si élève pas déjà scolarisé dans cet établissement précédemment : on n'écrase pas la valeur en BDD dans les autres cas.
        if( !$tab_users_fichier[$champ_ref][$i_fichier] )
        {
          $tab_users_fichier[$champ_ref][$i_fichier] = $tab_users_base[$champ_ref][$id_base];
        }
      }
      else if( ($champ_ref=='lv1') || ($champ_ref=='lv2') )
      {
        // N'est renseigné que pour un import SIECLE : on n'écrase pas la valeur en BDD dans les autres cas.
        if( !$tab_users_fichier[$champ_ref][$i_fichier] || ($tab_users_fichier[$champ_ref][$i_fichier]==100) )
        {
          $tab_users_fichier[$champ_ref][$i_fichier] = $tab_users_base[$champ_ref][$id_base];
        }
      }
      if( ($tab_users_base[$champ_ref][$id_base]!=$tab_users_fichier[$champ_ref][$i_fichier]) )
      {
        $td_modif .= ' || <b>'.$champ_aff.' : '.aff_champ($import_profil,$champ_ref,$tab_users_base[$champ_ref][$id_base]).' &rarr; '.aff_champ($import_profil,$champ_ref,$tab_users_fichier[$champ_ref][$i_fichier]).'</b>';
        $tab_users_modifier[$id_base][$champ_ref] = ($champ_ref!='classe') ? $tab_users_fichier[$champ_ref][$i_fichier] : $id_classe ;
        $nb_modif++;
        if($champ_ref=='courriel')
        {
          $tab_users_modifier[$id_base]['email_origine'] = 'user';
        }
      }
      else
      {
        $td_modif .= ' || '.$champ_aff.' : '.aff_champ($import_profil,$champ_ref,$tab_users_base[$champ_ref][$id_base]);
        $tab_users_modifier[$id_base][$champ_ref] = FALSE;
        if($champ_ref=='courriel')
        {
          $tab_users_modifier[$id_base]['email_origine'] = FALSE;
        }
      }
    }
    if($tab_users_base['sortie'][$id_base]!=SORTIE_DEFAUT_MYSQL)
    {
      $td_modif .= ' || <b>Sortie : '.To::date_mysql_to_french($tab_users_base['sortie'][$id_base]).' &rarr; non</b>';
      $tab_users_modifier[$id_base]['entree'] = SORTIE_DEFAUT_MYSQL ;
      $nb_modif++;
    }
    else
    {
      $tab_users_modifier[$id_base]['entree'] = FALSE ;
    }
    // Cas [5] : présent dans le fichier, présent dans la base, classe indiquée dans le fichier si élève, ancien dans la base et/ou différence constatée : contenu à modifier (user revenant ou mise à jour)
    if($nb_modif)
    {
      $lignes_modifier .= '<tr><th>Modifier <input id="mod_'.$id_base.'" name="mod_'.$id_base.'" type="checkbox" checked /></th><td>'.mb_substr($td_modif,4).'</td></tr>'.NL;
    }
    // Cas [6] : présent dans le fichier, présent dans la base, classe indiquée dans le fichier si élève, actuel dans la base et aucune différence constatée : contenu à conserver (contenu identique)
    else
    {
      if($mode=='complet')
      {
        $indication = ($import_profil=='eleve') ? $tab_users_base['classe'][$id_base] : $tab_users_base['profil_sigle'][$id_base] ;
        $lignes_conserver .= '<tr><th>Conserver</th><td>'.html($tab_users_base['sconet_id'][$id_base].' / '.$tab_users_base['sconet_num'][$id_base].' / '.$tab_users_base['reference'][$id_base].' || '.$tab_users_base['nom'][$id_base].' '.$tab_users_base['prenom'][$id_base].' ('.$indication.')').'</td></tr>'.NL;
      }
    }
  }
  // Retenir la correspondance d'indice fichier -> base
  // Supprimer l'entrée de la base éventuelle afin de ne plus la rechercher pour les utilisateurs suivants
  if($id_base)
  {
    $tab_i_fichier_TO_id_base[$i_fichier] = $id_base;
    unset(
      $tab_users_base['sconet_id' ][$id_base] ,
      $tab_users_base['sconet_num'][$id_base] ,
      $tab_users_base['reference' ][$id_base] ,
      $tab_users_base['nom'       ][$id_base] ,
      $tab_users_base['prenom'    ][$id_base]
    );
  }
}
// Parcourir chaque entrée de la base
if(count($tab_users_base['sconet_id']))
{
  $tab_indices_base = array_keys($tab_users_base['sconet_id']);
  foreach($tab_indices_base as $id_base)
  {
    // Cas [7] : absent dans le fichier, présent dans la base, actuel : contenu à retirer (probablement un user nouvellement sortant)
    if($tab_users_base['sortie'][$id_base]==SORTIE_DEFAUT_MYSQL)
    {
      $indication = ($import_profil=='eleve') ? $tab_users_base['classe'][$id_base] : $tab_users_base['profil_sigle'][$id_base] ;
      $date_sortie_fr = isset($tab_date_sortie[$tab_users_base['sconet_id'][$id_base]]) ? $tab_date_sortie[$tab_users_base['sconet_id'][$id_base]] : TODAY_FR ;
      $lignes_retirer .= '<tr><th>Retirer <input id="del_'.$id_base.'" name="del_'.$id_base.'" type="checkbox" checked /></th><td>'.html($tab_users_base['sconet_id'][$id_base].' / '.$tab_users_base['sconet_num'][$id_base].' / '.$tab_users_base['reference'][$id_base].' || '.$tab_users_base['nom'][$id_base].' '.$tab_users_base['prenom'][$id_base].' ('.$indication.')').' || <b>Sortie : non &rarr; '.$date_sortie_fr.'</b></td></tr>'.NL;
      $tab_users_retirer[$id_base] = To::date_french_to_mysql($date_sortie_fr);
    }
    // Cas [8] : absent dans le fichier, présent dans la base, ancien : contenu inchangé (restant ancien)
    else
    {
      if($mode=='complet')
      {
        $indication = ($import_profil=='eleve') ? $tab_users_base['classe'][$id_base] : $tab_users_base['profil_sigle'][$id_base] ;
        $lignes_inchanger .= '<tr><th>Conserver</th><td>'.html($tab_users_base['sconet_id'][$id_base].' / '.$tab_users_base['sconet_num'][$id_base].' / '.$tab_users_base['reference'][$id_base].' || '.$tab_users_base['nom'][$id_base].' '.$tab_users_base['prenom'][$id_base].' ('.$indication.')').'</td></tr>'.NL;
      }
    }
  }
}
// On enregistre
$tab_memo_analyse = array(
  'modifier' => $tab_users_modifier,
  'ajouter'  => $tab_users_ajouter,
  'retirer'  => $tab_users_retirer,
);
FileSystem::enregistrer_fichier_infos_serializees( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'memo_analyse.txt', $tab_memo_analyse );
// On enregistre (tableau mis à jour)
$tab_liens_id_base = array(
  'classes' => $tab_i_classe_TO_id_base,
  'groupes' => $tab_i_groupe_TO_id_base,
  'users'   => $tab_i_fichier_TO_id_base,
);
FileSystem::enregistrer_fichier_infos_serializees( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'liens_id_base.txt', $tab_liens_id_base );
// On affiche
Json::add_str('<p><label class="valide">Veuillez vérifier le résultat de l\'analyse des utilisateurs.</label></p>'.NL);
if( $lignes_ajouter && $lignes_retirer )
{
  Json::add_str('<p class="danger">Si des utilisateurs sont à la fois proposés pour être retirés et ajoutés, alors allez modifier leurs noms/prénoms puis reprenez l\'import au début.</p>'.NL);
}
$ligne_vide = '<tr><td colspan="2">Aucun</td></tr>'.NL;
if(empty($lignes_ajouter  )) { $lignes_ajouter   = $ligne_vide; }
if(empty($lignes_retirer  )) { $lignes_retirer   = $ligne_vide; }
if(empty($lignes_modifier )) { $lignes_modifier  = $ligne_vide; }
if(empty($lignes_conserver)) { $lignes_conserver = $ligne_vide; }
if(empty($lignes_inchanger)) { $lignes_inchanger = $ligne_vide; }
if(empty($lignes_ignorer  )) { $lignes_ignorer   = $ligne_vide; }
Json::add_str('<table>'.NL);
// Cas [2]
Json::add_str(  '<tbody>'.NL);
Json::add_str(    '<tr><th colspan="2">Utilisateurs à ajouter (absents de la base, nouveaux dans le fichier).<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL);
Json::add_str(    $lignes_ajouter);
Json::add_str(  '</tbody>'.NL);
// Cas [3] et [7]
$texte_explication  = ($import_profil=='eleve') ? ' ou sans classe affectée' : ( ($import_profil=='parent') ? ' ou sans enfant actuel' : '' ) ;
$lien_documentation = ($import_profil=='eleve') ? ' <br /><span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__gestion_departs#toggle_eleves_sortants">DOC : Cas d\'un élève sortant en cours de période</a></span>' : '' ;
Json::add_str(  '<tbody>'.NL);
Json::add_str(    '<tr><th colspan="2">Utilisateurs à retirer (absents du fichier'.$texte_explication.')<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q>'.$lien_documentation.'</th></tr>'.NL);
Json::add_str(    $lignes_retirer);
Json::add_str(  '</tbody>'.NL);
// Cas [5]
Json::add_str(  '<tbody>'.NL);
Json::add_str(    '<tr><th colspan="2">Utilisateurs à modifier (ou à réintégrer)<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL);
Json::add_str(    $lignes_modifier);
Json::add_str(  '</tbody>'.NL);
// Cas [6]
if($mode=='complet')
{
  Json::add_str(  '<tbody>'.NL);
  Json::add_str(    '<tr><th colspan="2">Utilisateurs à conserver (actuels)</th></tr>'.NL);
  Json::add_str(    $lignes_conserver);
  Json::add_str(  '</tbody>'.NL);
}
// Cas [4] et [8]
if($mode=='complet')
{
  Json::add_str(  '<tbody>'.NL);
  Json::add_str(    '<tr><th colspan="2">Utilisateurs inchangés (anciens)</th></tr>'.NL);
  Json::add_str(    $lignes_inchanger);
  Json::add_str(  '</tbody>'.NL);
}
// Cas [1]
if($import_profil=='eleve')
{
  Json::add_str(  '<tbody>'.NL);
  Json::add_str(    '<tr><th colspan="2">Utilisateurs ignorés (sans classe affectée).</th></tr>'.NL);
  Json::add_str(    $lignes_ignorer);
  Json::add_str(  '</tbody>'.NL);
}
Json::add_str('</table>'.NL);
Json::add_str('<ul class="puce p"><li><a href="#step52" id="envoyer_infos_utilisateurs">Valider et afficher le bilan obtenu.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL);

?>
