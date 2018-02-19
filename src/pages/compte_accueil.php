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
$TITRE = html(Lang::_("Bienvenue dans votre espace identifié"));

/*
 * Tableau des rubriques
 * toutes peuvent être masqués sauf "alert" qui est obligatoire
 * ( la mémorisation de leur état s'effectue dans sacoche_user.user_param_accueil sauf pour "messages" qui se fait dans sacoche_message.message_dests_cache )
 */

$masque_faiblesses = ($_SESSION['USER_PROFIL_TYPE']=='professeur') ? html(Lang::_("Items récents à retravailler"))         : html(Lang::_("Items récents à améliorer")) ;
$masque_saisies    = ($_SESSION['USER_PROFIL_TYPE']=='professeur') ? html(Lang::_("Notes à saisir"))                       : html(Lang::_("Auto-évaluations en cours")) ;
$masque_officiel   = ($_SESSION['USER_PROFIL_TYPE']=='professeur') ? html(Lang::_("Bilans officiels ouverts à la saisie")) : html(Lang::_("Nouveaux bilans officiels à consulter")) ;

$tab_accueil = array(
 'user'          => array( 'contenu'=>''      , 'nombre'=>0, 'masque'=>html(Lang::_("Informations d'accueil")) ) ,
 'favori'        => array( 'contenu'=>''      , 'nombre'=>0, 'masque'=>html(Lang::_("Raccourcis vers les menus favoris")) ) ,
 'alert'         => array( 'contenu'=>''      , 'nombre'=>0, 'masque'=>NULL ) ,
 'notifications' => array( 'contenu'=>''      , 'nombre'=>0, 'masque'=>NULL ) ,
 'messages'      => array( 'contenu'=>array() , 'nombre'=>0, 'masque'=>"" ) ,
 'previsions'    => array( 'contenu'=>''      , 'nombre'=>0, 'masque'=>html(Lang::_("Évaluations prévues")) ) ,
 'resultats'     => array( 'contenu'=>''      , 'nombre'=>0, 'masque'=>html(Lang::_("Résultats récents")) ) ,
 'faiblesses'    => array( 'contenu'=>''      , 'nombre'=>0, 'masque'=>$masque_faiblesses ) ,
 'reussites'     => array( 'contenu'=>''      , 'nombre'=>0, 'masque'=>html(Lang::_("Items récents les mieux réussis")) ) ,
 'demandes'      => array( 'contenu'=>''      , 'nombre'=>0, 'masque'=>html(Lang::_("Demandes d'évaluations")) ) ,
 'saisies'       => array( 'contenu'=>''      , 'nombre'=>0, 'masque'=>$masque_saisies ) ,
 'officiel'      => array( 'contenu'=>''      , 'nombre'=>0, 'masque'=>$masque_officiel ) ,
 'socle'         => array( 'contenu'=>''      , 'nombre'=>0, 'masque'=>"" ) ,
 'help'          => array( 'contenu'=>''      , 'nombre'=>0, 'masque'=>html(Lang::_("Astuce du moment")) ) ,
 'ecolo'         => array( 'contenu'=>''      , 'nombre'=>0, 'masque'=>html(Lang::_("Protégeons l'environnement")) ) ,
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// [alert] - Alertes (pour l'administrateur) ; affiché après [user] mais à définir avant
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Mis en session car réutilisé pour des profils autres qu'administrateur
if(!in_array($_SESSION['USER_PROFIL_TYPE'],array('webmestre','developpeur','partenaire')))
{
  $_SESSION['NB_DEVOIRS_ANTERIEURS'] = DB_STRUCTURE_COMMUN::DB_compter_devoirs_annees_scolaires_precedentes();
}

if($_SESSION['USER_PROFIL_TYPE']=='administrateur')
{
  $alerte_novice = FALSE ;
  $info_rentree  = FALSE ;
  if(!DB_STRUCTURE_ADMINISTRATEUR::DB_compter_matieres_etabl())
  {
    $tab_accueil['alert']['contenu'] .= '<p class="danger">Aucune matière n\'est choisie pour l\'établissement !<br /><a href="./index.php?page=administrateur_etabl_matiere">Gestion des matières.</a></p>';
    $alerte_novice = TRUE ;
  }
  if(!DB_STRUCTURE_ADMINISTRATEUR::DB_compter_niveaux_etabl( TRUE /*with_specifiques*/ ))
  {
    $tab_accueil['alert']['contenu'] .= '<p class="danger">Aucun niveau n\'est choisi pour l\'établissement !<br /><a href="./index.php?page=administrateur_etabl_niveau">Gestion des niveaux.</a></p>';
    $alerte_novice = TRUE ;
  }
  elseif(!DB_STRUCTURE_ADMINISTRATEUR::DB_compter_niveaux_etabl( FALSE /*with_specifiques*/ ))
  {
    $tab_accueil['alert']['contenu'] .= '<p class="danger">Aucun niveau de classe n\'est choisi pour l\'établissement !<br /><a href="./index.php?page=administrateur_etabl_niveau">Gestion des niveaux.</a></p>';
    $alerte_novice = TRUE ;
  }
  if($_SESSION['NB_DEVOIRS_ANTERIEURS'])
  {
    $tab_accueil['alert']['contenu'] .= '<p class="probleme">Année scolaire précédente non archivée&nbsp;!<br />Au changement d\'année scolaire un administrateur doit <a href="./index.php?page=administrateur_nettoyage">lancer l\'initialisation annuelle des données</a>.<br />Ne poursuivez pas tant que cela n\'est pas fait&nbsp;!</p>';
    $info_rentree  = TRUE ;
  }
  if(!$_SESSION['USER_EMAIL'])
  {
    $tab_accueil['alert']['contenu'] .= '<p class="danger">Votre adresse de courriel n\'est pas renseignée !<br /><a href="./index.php?page=compte_email">Saisir une adresse e-mail</a> pour ne pas être bloqué en cas de perte de mot de passe.</p>';
  }
  if($alerte_novice)
  {
    // volontairement pas en pop-up mais dans un nouvel onglet
    $tab_accueil['alert']['contenu'] .= '<p><span class="manuel"><a target="_blank" rel="noopener noreferrer" href="'.SERVEUR_GUIDE_ADMIN.'">Guide de démarrage d\'un administrateur de <em>SACoche</em>.</a></span></p>';
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// [user] - Message de bienvenue (informations utilisateur : infos profil, infos selon profil, infos adresse de connexion)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_accueil['user']['contenu'] = '';
// infos connexion (pas si webmestre ni partenaire)
if(isset($_SESSION['DELAI_CONNEXION']))
{
  $tab_accueil['user']['contenu'] .= '<p class="i"><TG> '.sprintf(html(Lang::_("Bonjour %s.")),'<b>'.html($_SESSION['USER_PRENOM']).'</b>').' ';
  if($_SESSION['FIRST_CONNEXION'])                             { $tab_accueil['user']['contenu'] .= html(Lang::_("Heureux de faire votre connaissance ; bonne découverte de SACoche !")).'</p>'; }
  elseif($_SESSION['DELAI_CONNEXION']<  43200 /*0.5*24*3600*/) { $tab_accueil['user']['contenu'] .= html(Lang::_("Déjà de retour ? Décidément on ne se quitte plus !")).'</p>'; }
  elseif($_SESSION['DELAI_CONNEXION']< 108000 /*  2*24*3600*/) { $tab_accueil['user']['contenu'] .= html(Lang::_("Bonne navigation, et merci de votre fidélité !")).'</p>'; }
  elseif($_SESSION['DELAI_CONNEXION']< 604800 /*  7*24*3600*/) { $tab_accueil['user']['contenu'] .= html(Lang::_("Content de vous retrouver après cette pause de quelques jours !")).'</p>'; }
  elseif($_SESSION['DELAI_CONNEXION']<3024000 /* 35*24*3600*/) { $tab_accueil['user']['contenu'] .= html(Lang::_("Quel plaisir de vous revoir : le temps semble long sans vous !")).'</p>'; }
  else                                                         { $tab_accueil['user']['contenu'] .= html(Lang::_("On ne s'était pas vu depuis trop longtemps : vous nous avez manqué !")).'</p>'; }
  unset( $_SESSION['FIRST_CONNEXION'] , $_SESSION['DELAI_CONNEXION'] );
  $_SESSION['DEUXIEME_PASSAGE'] = TRUE;
}
elseif(isset($_SESSION['DEUXIEME_PASSAGE']))
{
  $tab_accueil['user']['contenu'] .= '<p class="i"><TG> '.sprintf(html(Lang::_("Encore là %s ? Vous avez raison, faites comme chez vous !")),'<b>'.html($_SESSION['USER_PRENOM']).'</b>');
  unset($_SESSION['DEUXIEME_PASSAGE']);
  $_SESSION['PASSAGES_SUIVANTS'] = TRUE;
}
elseif(isset($_SESSION['PASSAGES_SUIVANTS']))
{
  $tab_accueil['user']['contenu'] .= '<p class="i"><TG> '.sprintf(html(Lang::_("Toujours là %s ? Pas de souci, restez le temps que vous voulez !")),'<b>'.html($_SESSION['USER_PRENOM']).'</b>');
}
// infos profil
$tab_accueil['user']['contenu'] .= '<p>'.sprintf(html(Lang::_("Vous êtes dans l'environnement %s.")),'<b>'.$_SESSION['USER_PROFIL_NOM_LONG'].'</b>').'</p>';
// infos selon profil
if($_SESSION['USER_PROFIL_TYPE']=='parent')
{
  if($_SESSION['NB_ENFANTS'])
  {
    $tab_nom_enfants = array();
    foreach($_SESSION['OPT_PARENT_ENFANTS'] as $DB_ROW)
    {
      $tab_nom_enfants[] =html($DB_ROW['texte']);
    }
    $tab_accueil['user']['contenu'] .= '<p>'.html(Lang::_("Élève(s) affecté(s) dans une classe associé(s) à votre compte :")).' <b>'.implode('</b> ; <b>',$tab_nom_enfants).'</b>.</p>';
  }
  else
  {
    $tab_accueil['user']['contenu'] .= '<p class="danger">'.$_SESSION['OPT_PARENT_ENFANTS'].'</p>';
  }
}
elseif($_SESSION['USER_PROFIL_TYPE']=='eleve')
{
  if(!$_SESSION['ELEVE_CLASSE_ID'])
  {
    $tab_accueil['user']['contenu'] .= '<p class="danger">Vous n\'êtes pas affecté(e) dans une classe !<br />Afin de pouvoir consulter vos résultats, un administrateur doit renseigner votre classe.</p>';
  }
}
elseif($_SESSION['USER_PROFIL_TYPE']=='administrateur')
{
  // Indication connexions SSO existantes si non choisies
  $uai_departement = (int)substr($_SESSION['WEBMESTRE_UAI'],0,3);
  if( $uai_departement && ($_SESSION['CONNEXION_MODE']=='normal') )
  {
    require(CHEMIN_DOSSIER_INCLUDE.'tableau_sso.php');
    $tab_memo_ent_possible = array();
    $tab_corse_dep = array('2A','2B');
    $tab_corse_uai = array('620','720');
    foreach($tab_connexion_mode as $connexion_mode => $mode_texte)
    {
      foreach($tab_connexion_info[$connexion_mode] as $connexion_ref => $tab_infos)
      {
        list($departement,$connexion_nom) = explode('|',$connexion_ref);
        $departement = str_replace( $tab_corse_dep , $tab_corse_uai , $departement );
        if( ($uai_departement==$departement) && $tab_infos['etat'] )
        {
          $tab_memo_ent_possible[$connexion_ref] = $connexion_nom;
        }
      }
    }
    $nb_ent_possibles = count($tab_memo_ent_possible);
    if($nb_ent_possibles)
    {
      $tab_texte_ent_possibles = array();
      $mot_ent = ($nb_ent_possibles>1) ? 'des ENT' : 'de l\'ENT' ;
      $tab_texte_ent_possibles['intro'] = 'Sur votre département <em>SACoche</em> peut utiliser l\'authentification '.$mot_ent.' <b>'.implode(' - ',$tab_memo_ent_possible).'</b> &rarr; <a href="./index.php?page=administrateur_etabl_connexion">Gestion du mode d\'identification.</a>';
      if( IS_HEBERGEMENT_SESAMATH && CONVENTION_ENT_REQUISE && is_file(CHEMIN_FICHIER_WS_SESAMATH_ENT) )
      {
        require(CHEMIN_FICHIER_WS_SESAMATH_ENT);
        foreach($tab_memo_ent_possible as $connexion_ref => $connexion_nom)
        {
          list($departement,$connexion_nom) = explode('|',$connexion_ref);
          if( isset($tab_connecteurs_convention[$connexion_ref]) && $tab_ent_convention_infos[$tab_connecteurs_convention[$connexion_ref]]['actif'] )
          {
            $tab_texte_ent_possibles[$connexion_ref] = '<a target="_blank" rel="noopener noreferrer" href="'.SERVEUR_GUIDE_ENT.'#toggle_partenariats">'.$tab_ent_convention_infos[$tab_connecteurs_convention[$connexion_ref]]['texte'].'</a>';
          }
        }
      }
      $tab_accueil['user']['contenu'] .= '<p class="astuce">'.implode('<br />',$tab_texte_ent_possibles).'</p>';
    }
  }
  if(!$tab_accueil['alert']['contenu'])
  {
    // volontairement pas en pop-up mais dans un nouvel onglet
    $tab_accueil['user']['contenu'] .= '<p><span class="manuel"><a target="_blank" rel="noopener noreferrer" href="'.SERVEUR_GUIDE_ADMIN.'">Guide de démarrage d\'un administrateur de <em>SACoche</em>.</a></span></p>';
  }
  if( $info_rentree || Outil::test_periode_rentree() )
  {
    // volontairement pas en pop-up mais dans un nouvel onglet
    $tab_accueil['user']['contenu'] .= '<p><span class="manuel"><a target="_blank" rel="noopener noreferrer" href="'.SERVEUR_GUIDE_RENTREE.'">Guide de changement d\'année d\'un administrateur de <em>SACoche</em>.</a></span></p>';
  }
  if( Outil::test_periode_sortie() )
  {
    $lien_contact_referent = (HEBERGEUR_INSTALLATION=='multi-structures') ? '<a href="./index.php?page=administrateur_etabl_identite"><span class="b">indiquer le nouveau contact référent éventuel</span></a> et à ' : '' ;
    $tab_accueil['user']['contenu'] .= '<p><span class="danger">Si vous passez la main à la prochaine rentrée</span>, alors pensez à '.$lien_contact_referent.'<a href="./index.php?page=administrateur_administrateur"><span class="b">transmettre des identifiants d\'administrateur</span></a>.</p>';
  }
}
// infos adresse de connexion
if(in_array($_SESSION['USER_PROFIL_TYPE'],array('webmestre','developpeur','partenaire')))
{
  $tab_accueil['user']['contenu'] .= '<div>Pour vous connecter à cet espace, utilisez l\'adresse <b>'.URL_DIR_SACOCHE.'?'.$_SESSION['USER_PROFIL_TYPE'].'</b></div>';
  $tab_accueil['user']['masque'] = NULL;
}
else
{
  if(HEBERGEUR_INSTALLATION=='multi-structures')
  {
    $tab_accueil['user']['contenu'] .= '<div>'.html(Lang::_("Adresse à utiliser pour une sélection automatique de l'établissement")).'&nbsp;: <b>'.URL_DIR_SACOCHE.'?base='.$_SESSION['BASE'].'</b></div>';
  }
  if($_SESSION['CONNEXION_MODE']!='normal')
  {
    $get_base = ($_SESSION['BASE']) ? '='.$_SESSION['BASE'] : '' ;
    $tab_accueil['user']['contenu'] .= '<div>'.html(Lang::_("Adresse à utiliser pour une connexion automatique avec l'authentification externe")).'&nbsp;: <b>'.URL_DIR_SACOCHE.'?sso'.$get_base.'</b></div>';
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// [favori] - Raccourcis vers les menus favoris
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($_SESSION['FAVORI'])
{
  $tab_accueil['favori']['contenu'] .= '<p class="b"><TG> '.html(Lang::_("Raccourcis favoris")).'</p>'.$_SESSION['FAVORI'];
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// [notifications] - Indication du nombre de notifications en attente
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(!in_array($_SESSION['USER_PROFIL_TYPE'],array('webmestre','developpeur','partenaire')))
{
  $nb_notifications_non_vues = DB_STRUCTURE_NOTIFICATION::DB_compter_notifications_non_vues($_SESSION['USER_ID']);
  if($nb_notifications_non_vues)
  {
    $s = ($nb_notifications_non_vues>1) ? 's' : '' ;
    $tab_accueil['notifications']['contenu'] .= '<div class="b">'.html(Lang::_("Notifications à consulter")).'</div>';
    $tab_accueil['notifications']['contenu'] .= '<p>Vous avez <a href="./index.php?page=consultation_notifications"><span class="b">'.$nb_notifications_non_vues.' notification'.$s.'</span></a> non vue'.$s.'.</p>';
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// [messages] & [ecolo] - Panneau d'informations (message d'autres utilisateurs) ou message écolo
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(!in_array($_SESSION['USER_PROFIL_TYPE'],array('webmestre','developpeur','partenaire')))
{
  $DB_TAB = DB_STRUCTURE_MESSAGE::DB_lister_messages_for_user_destinataire( $_SESSION['USER_ID'] , $_SESSION['USER_PROFIL_TYPE'] );
  if(!empty($DB_TAB))
  {
    foreach($DB_TAB as $key => $DB_ROW)
    {
      $findme = ','.$_SESSION['USER_ID'].',';
      $tab_accueil['messages']['contenu'][$DB_ROW['message_id']] = array(
        'titre'   => html(Lang::_("Message")).' ('.html(To::texte_identite($DB_ROW['user_nom'],FALSE,$DB_ROW['user_prenom'],TRUE,$DB_ROW['user_genre'])).')',
        'message' => Outil::make_lien(nl2br(html($DB_ROW['message_contenu'])),'html'),
        'visible' => (strpos($DB_ROW['message_dests_cache'],$findme)===FALSE),
      );
    }
  }
  if( (!count($tab_accueil['messages']['contenu'])) && ($_SESSION['USER_PROFIL_TYPE']!='administrateur') )
  {
    $tab_accueil['ecolo']['contenu'] = '<p class="b"><TG> '.html(Lang::_("Afin de préserver l'environnement, n'imprimer que si nécessaire !")).'</p><div>'.html(Lang::_("Enregistrer la version numérique d'un document (grille, relevé, bilan) suffit pour le consulter, l'archiver, le partager, …")).'</div>';
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// [previsions] - Évaluations prévues (élèves & parents)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($_SESSION['USER_PROFIL_TYPE']=='eleve') || ( ($_SESSION['USER_PROFIL_TYPE']=='parent') && ($_SESSION['NB_ENFANTS']>0) ) )
{
  $tab_eleves = ($_SESSION['USER_PROFIL_TYPE']=='eleve') ? array(0=>array('valeur'=>$_SESSION['USER_ID'],'classe_id'=>$_SESSION['ELEVE_CLASSE_ID'])) : $_SESSION['OPT_PARENT_ENFANTS'] ;
  $nb_eleves = count($tab_eleves);
  foreach($tab_eleves as $tab_eleve_info)
  {
    $eleve_id  = $tab_eleve_info['valeur'];
    $classe_id = $tab_eleve_info['classe_id'];
    $DB_TAB = DB_STRUCTURE_ELEVE::DB_lister_prochains_devoirs_eleve( $eleve_id , $classe_id );
    if(!empty($DB_TAB))
    {
      if(!$tab_accueil['previsions']['nombre'])
      {
        $tab_accueil['previsions']['contenu'] = '<div class="b"><TG> '.$tab_accueil['previsions']['masque'].'</div>';
      }
      $tab_accueil['previsions']['nombre'] += count($DB_TAB);
      $tab_accueil['previsions']['contenu'].= '<ul class="puce p">';
      $param_eleve_id = ($nb_eleves>1) ? '&amp;eleve_id='.$eleve_id.'&amp;classe_id='.$classe_id : '' ;
      $text_eleve_nom = ($nb_eleves>1) ? html($tab_eleve_info['texte']).' || ' : '' ;
      foreach($DB_TAB as $DB_ROW)
      {
        $date_affich = To::date_mysql_to_french($DB_ROW['devoir_date']);
        $tab_accueil['previsions']['contenu'] .= '<li>'.$text_eleve_nom.html($date_affich).' || <a href="./index.php?page=evaluation&amp;section=voir&amp;devoir_id='.$DB_ROW['devoir_id'].$param_eleve_id.'">'.html(To::texte_identite($DB_ROW['prof_nom'],FALSE,$DB_ROW['prof_prenom'],TRUE,$DB_ROW['prof_genre'])).' || '.html($DB_ROW['devoir_info']).'</a></li>';
      }
      $tab_accueil['previsions']['contenu'].= '</ul>';
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// [resultats] - Résultats récents (élèves & parents)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($_SESSION['USER_PROFIL_TYPE']=='eleve') || ( ($_SESSION['USER_PROFIL_TYPE']=='parent') && ($_SESSION['NB_ENFANTS']>0) ) )
{
  $nb_jours_consideres = 7;
  $tab_eleves = ($_SESSION['USER_PROFIL_TYPE']=='eleve') ? array( 0 => array( 'valeur'=>$_SESSION['USER_ID'] , 'classe_id'=>$_SESSION['ELEVE_CLASSE_ID'] ) ) : $_SESSION['OPT_PARENT_ENFANTS'] ;
  $nb_eleves = count($tab_eleves);
  foreach($tab_eleves as $tab_eleve_info)
  {
    $eleve_id  = $tab_eleve_info['valeur'];
    $classe_id = $tab_eleve_info['classe_id'];
    $DB_TAB = DB_STRUCTURE_ELEVE::DB_lister_derniers_devoirs_eleve_avec_notes_saisies( $eleve_id , $nb_jours_consideres );
    if(!empty($DB_TAB))
    {
      if(!$tab_accueil['resultats']['nombre'])
      {
        $tab_accueil['resultats']['contenu'] = '<div class="b"><TG> '.$tab_accueil['resultats']['masque'].'</div>';
      }
      $tab_accueil['resultats']['nombre'] += count($DB_TAB);
      $tab_accueil['resultats']['contenu'].= '<ul class="puce p">';
      $param_eleve_id = ($nb_eleves>1) ? '&amp;eleve_id='.$eleve_id.'&amp;classe_id='.$classe_id : '' ;
      $text_eleve_nom = ($nb_eleves>1) ? html($tab_eleve_info['texte']).' || ' : '' ;
      foreach($DB_TAB as $DB_ROW)
      {
        $date_affich = To::date_mysql_to_french($DB_ROW['devoir_date']);
        $tab_accueil['resultats']['contenu'] .= '<li>'.$text_eleve_nom.html($date_affich).' || <a href="./index.php?page=evaluation&amp;section=voir&amp;devoir_id='.$DB_ROW['devoir_id'].$param_eleve_id.'">'.html(To::texte_identite($DB_ROW['prof_nom'],FALSE,$DB_ROW['prof_prenom'],TRUE,$DB_ROW['prof_genre'])).' || '.html($DB_ROW['devoir_info']).'</a></li>';
      }
      $tab_accueil['resultats']['contenu'].= '</ul>';
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// [faiblesses] - Items récents à retravailler (prof) ou Items récents à améliorer (élèves / parents)
// [reussites] - Items récents les mieux réussis (élèves / parents)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// TODO : PARTIE PROF A DEVELOPPER

if( ($_SESSION['USER_PROFIL_TYPE']=='eleve') || ( ($_SESSION['USER_PROFIL_TYPE']=='parent') && ($_SESSION['NB_ENFANTS']>0) ) )
{
  $tab_eleves = ($_SESSION['USER_PROFIL_TYPE']=='eleve') ? array(0=>array('valeur'=>$_SESSION['USER_ID'])) : $_SESSION['OPT_PARENT_ENFANTS'] ;
  $nb_eleves = count($tab_eleves);
  $nb_jours_consideres = 14;
  $nb_resultats_maximum = max( 4 , 10-2*$nb_eleves );
  $tab_notes_observees = array();
  $numero = 0;
  $nombre_median = floor( $_SESSION['NOMBRE_CODES_NOTATION'] / 2 ) + 0.5; // par exemple 3 donne 1,5 (1+0,5) et 4 donne 2,5 (2+0,5)
  foreach( $_SESSION['NOTE_ACTIF'] as $note_id )
  {
    $numero++;
    if($numero<$nombre_median)
    {
      $tab_notes_observees[$note_id] = 'faiblesses';
    }
    else if($numero>$nombre_median)
    {
      $tab_notes_observees[$note_id] = 'reussites';
    }
  }
  $longueur_intitule_item_maxi = ($nb_eleves==1) ? 100 : 75 ;
  foreach($tab_eleves as $eleve_num => $tab_eleve_info)
  {
    $eleve_id  = $tab_eleve_info['valeur'];
    $DB_TAB = DB_STRUCTURE_ELEVE::DB_lister_derniers_resultats_eleve( $eleve_id , $nb_jours_consideres , $_SESSION['USER_PROFIL_TYPE'] );
    if(!empty($DB_TAB))
    {
      // On parcourt une première fois le tableau pour ne pas compter plusieurs fois un même item + cibler les plus mauvais / récents résultats + éventuellement limiter leur nb
      $tab_selection_faiblesses_key = array();
      $tab_selection_reussites_key  = array();
      foreach($DB_TAB as $item_id => $DB_ROW)
      {
        if( isset($tab_notes_observees[$DB_ROW[0]['saisie_note']]) && !isset($tab_selection_faiblesses_key[$item_id]) && !isset($tab_selection_reussites_key[$item_id]) )
        {
          
          ${'tab_selection_'.$tab_notes_observees[$DB_ROW[0]['saisie_note']].'_key'}[$item_id] = $DB_ROW[0]['saisie_note'].$DB_ROW[0]['saisie_date'];
        }
      }
      $tab_critere = array( 'faiblesses' , 'reussites' );
      foreach($tab_critere as $critere)
      {
        if(count(${'tab_selection_'.$critere.'_key'}))
        {
          arsort(${'tab_selection_'.$critere.'_key'});
          ${'tab_selection_'.$critere.'_key'} = array_slice ( ${'tab_selection_'.$critere.'_key'} , 0 , $nb_resultats_maximum , TRUE );
          // $tab_selection_*_key a maintenant les bons indices, on poursuit
          if(!$tab_accueil[$critere]['nombre'])
          {
            $tab_accueil[$critere]['contenu'] = '<div class="b"><TG> '.$tab_accueil[$critere]['masque'].'</div>';
          }
          $tab_accueil[$critere]['nombre'] += count(${'tab_selection_'.$critere.'_key'});
          $tab_accueil[$critere]['contenu'].= '<ul class="puce p">';
          $param_eleve_num = ($nb_eleves==1) ? '' : '&amp;eleve_num='.$eleve_num ;
          $text_eleve_nom  = ($nb_eleves==1) ? '' : html($tab_eleve_info['texte']).' || ' ;
          foreach(${'tab_selection_'.$critere.'_key'} as $item_id => $tab_temp)
          {
            $date_affich = To::date_mysql_to_french($DB_TAB[$item_id][0]['saisie_date']);
            $item_ref = ($DB_TAB[$item_id][0]['ref_perso']) ? $DB_TAB[$item_id][0]['matiere_ref'].'.'.$DB_TAB[$item_id][0]['ref_perso'] : $DB_TAB[$item_id][0]['matiere_ref'].'.'.$DB_TAB[$item_id][0]['ref_auto'] ;
            $tab_accueil[$critere]['contenu'] .= '<li>'.Html::note_image($DB_TAB[$item_id][0]['saisie_note'],'','').' '.$text_eleve_nom.html($date_affich).' || <a href="./index.php?page=releve&amp;section=items&amp;matiere_id='.$DB_TAB[$item_id][0]['matiere_id'].'&amp;item_id='.$item_id.$param_eleve_num.'">'.html($DB_TAB[$item_id][0]['matiere_nom']).' || '.html($item_ref.' - '.Outil::afficher_texte_tronque($DB_TAB[$item_id][0]['item_nom'],$longueur_intitule_item_maxi)).'</a></li>';
          }
          $tab_accueil[$critere]['contenu'].= '</ul>';
        }
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// [demandes] - Info Demandes d'évaluations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(in_array($_SESSION['USER_PROFIL_TYPE'],array('professeur','eleve')))
{
  if($_SESSION['USER_PROFIL_TYPE']=='professeur')
  {
    $DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_compter_demandes_evaluation($_SESSION['USER_ID'],$_SESSION['USER_JOIN_GROUPES']);
    $page = 'evaluation&amp;section=demande_professeur';
  }
  if($_SESSION['USER_PROFIL_TYPE']=='eleve')
  {
    $DB_TAB = DB_STRUCTURE_ELEVE::DB_compter_demandes_evaluation($_SESSION['USER_ID']);
    $page = 'evaluation&amp;section=demande_eleve';
  }
  if(!empty($DB_TAB))
  {
    $nb_etat_prof  = empty($DB_TAB['prof']['nombre'])  ? 0 : $DB_TAB['prof']['nombre']  ;
    $nb_etat_eleve = empty($DB_TAB['eleve']['nombre']) ? 0 : $DB_TAB['eleve']['nombre'] ;
    $tab_accueil['demandes']['nombre']  = $nb_etat_prof + $nb_etat_eleve;
    $tab_accueil['demandes']['contenu'] = '<p class="b"><TG> '.$tab_accueil['demandes']['masque'].'</p>';
    if($nb_etat_eleve)
    {
      $s = ($DB_TAB['eleve']['nombre']>1) ? 's' : '' ;
      $tab_accueil['demandes']['contenu'] .= '<p>Vous avez <a href="./index.php?page='.$page.'"><span class="b">'.$nb_etat_eleve.' demande'.$s.' d\'évaluation'.$s.'</span></a> en attente de réponse.</p>';
    }
    if($nb_etat_prof)
    {
      $s = ($DB_TAB['prof']['nombre']>1) ? 's' : '' ;
      $tab_accueil['demandes']['contenu'] .= '<p>Vous avez <a href="./index.php?page='.$page.'"><span class="b">'.$nb_etat_prof.' demande'.$s.' d\'évaluation'.$s.'</span></a> en cours de préparation.</p>';
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// [saisies] - Notes à saisir (prof) ou auto-évaluations en cours (élèves)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// TODO : PARTIE PROF A DEVELOPPER

if($_SESSION['USER_PROFIL_TYPE']=='eleve')
{
  $DB_TAB = DB_STRUCTURE_ELEVE::DB_lister_devoirs_eleve_avec_autoevaluation_en_cours( $_SESSION['USER_ID'] , $_SESSION['ELEVE_CLASSE_ID'] );
  if(!empty($DB_TAB))
  {
    $tab_accueil['saisies']['nombre']  = count($DB_TAB);
    $tab_accueil['saisies']['contenu'] = '<div class="b"><TG> '.$tab_accueil['saisies']['masque'].'</div>';
    $tab_accueil['saisies']['contenu'].= '<ul class="puce p">';
    foreach($DB_TAB as $DB_ROW)
    {
      $date_affich = To::date_mysql_to_french($DB_ROW['devoir_date']);
      $tab_accueil['saisies']['contenu'] .= '<li>'.html($date_affich).' || <a href="./index.php?page=evaluation&amp;section=voir&amp;devoir_id='.$DB_ROW['devoir_id'].'&amp;autoeval">'.html(To::texte_identite($DB_ROW['prof_nom'],FALSE,$DB_ROW['prof_prenom'],TRUE,$DB_ROW['prof_genre'])).' || '.html($DB_ROW['devoir_info']).'</a></li>';
    }
    $tab_accueil['saisies']['contenu'].= '</ul>';
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// [officiel] - Bilans officiels ouverts à la saisie (prof) ou à consulter (élèves / parents)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// TODO : PARTIE PROF A DEVELOPPER

if( ($_SESSION['USER_PROFIL_TYPE']=='eleve') || ( ($_SESSION['USER_PROFIL_TYPE']=='parent') && ($_SESSION['NB_ENFANTS']>0) ) )
{
  // Cette section reprend pas mal de code issu de la page [officiel_voir_archive.php]
  $tab_types = array
  (
    'livret'   => array( 'droit'=>'OFFICIEL_LIVRET'   , 'titre'=>'Livret Scolaire'  ) ,
    'releve'   => array( 'droit'=>'OFFICIEL_RELEVE'   , 'titre'=>'Relevé d\'évaluations' ) ,
    'bulletin' => array( 'droit'=>'OFFICIEL_BULLETIN' , 'titre'=>'Bulletin scolaire'     ) ,
  );
  $droit_voir_archives_pdf = FALSE;
  foreach($tab_types as $BILAN_TYPE => $tab)
  {
    $droit_voir_archives_pdf = $droit_voir_archives_pdf || Outil::test_user_droit_specifique($_SESSION['DROIT_'.$tab['droit'].'_VOIR_ARCHIVE']) ;
  }
  if($droit_voir_archives_pdf)
  {
    // identifiants élèves concernés
    $tab_eleve_id = array();
    if($_SESSION['USER_PROFIL_TYPE']=='eleve')
    {
      $tab_eleve_id[] = $_SESSION['USER_ID'];
    }
    else
    {
      foreach($_SESSION['OPT_PARENT_ENFANTS'] as $tab)
      {
        $tab_eleve_id[] = $tab['valeur'];
      }
    }
    $tab_eleves = ($_SESSION['USER_PROFIL_TYPE']=='eleve') ? array(0=>array('valeur'=>$_SESSION['USER_ID'])) : $_SESSION['OPT_PARENT_ENFANTS'] ;
    $nb_eleves = count($tab_eleve_id);
    // lister les bilans officiels archivés
    $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_officiel_archive_sans_infos( implode(',',$tab_eleve_id) );
    foreach($DB_TAB as $DB_ROW)
    {
      $key_type = ($DB_ROW['archive_type']=='sacoche') ? $DB_ROW['archive_ref'] : 'livret' ;
      if( Outil::test_user_droit_specifique($_SESSION['DROIT_'.$tab_types[$key_type]['droit'].'_VOIR_ARCHIVE']) && is_null($DB_ROW['archive_date_consultation_'.$_SESSION['USER_PROFIL_TYPE']]) )
      {
        $text_eleve_nom = ($nb_eleves>1) ? html($_SESSION['OPT_PARENT_ENFANTS'][array_search($DB_ROW['user_id'],$tab_eleve_id)]['texte']).' || ' : '' ;
        $objet = ($DB_ROW['archive_type']=='sacoche') ? $tab_types[$DB_ROW['archive_ref']]['titre'] : $tab_types[$DB_ROW['archive_type']]['titre'].' '.$DB_ROW['archive_ref'] ;
        $tab_accueil['officiel']['nombre'] += 1;
        $tab_accueil['officiel']['contenu'].= '<li>'.$text_eleve_nom.'<a href="./index.php?page=officiel_voir_archive&archive_id='.$DB_ROW['officiel_archive_id'].'">'.$objet.' || '.html($DB_ROW['periode_nom']).'</a></li>';
      }
    }
    if($tab_accueil['officiel']['nombre'])
    {
      $tab_accueil['officiel']['contenu'] = '<div class="b"><TG> '.$tab_accueil['officiel']['masque'].'</div>'.'<ul class="puce p">'.$tab_accueil['officiel']['contenu'].'</ul>';
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// [socle] - ... en prévision de qqchose... à définir...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// [help] - Astuce du jour
// ////////////////////////////////////////////////////////////////////////////////////////////////////

require(CHEMIN_DOSSIER_INCLUDE.'tableau_astuces.php'); // Charge $tab_astuces[$profil][]
$astuce_nombre = isset($tab_astuces[$_SESSION['USER_PROFIL_TYPE']]) ? count($tab_astuces[$_SESSION['USER_PROFIL_TYPE']]) : 0 ;
if($astuce_nombre)
{
  $coef_distorsion = 2;
  $i_alea = mt_rand(0,99) / 100; // nombre aléatoire entre 0,00 et 0,99
  $i_dist = pow($i_alea,$coef_distorsion) ; // distorsion pour accentuer le nombre de résultats proches de 0
  $indice = (int)floor($astuce_nombre*$i_dist);
  $tab_accueil['help']['contenu'] .= '<p class="b"><TG> '.html(Lang::_("Le saviez-vous ?")).'</p><p>'.$tab_astuces[$_SESSION['USER_PROFIL_TYPE']][$indice].'</p>';
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On passe à l'affichage de tout ça !
// ////////////////////////////////////////////////////////////////////////////////////////////////////

foreach($tab_accueil as $type => $tab_type_infos)
{
  extract($tab_type_infos); // $contenu $masque $nombre
  if( is_string($contenu) && ($contenu!='') )
  {
    if($masque!==NULL)
    {
      $info_nombre = ($nombre) ? ' <span class="fluo g">('.$nombre.')</span>' : '' ;
      $class_moins = (strpos($_SESSION['USER_PARAM_ACCUEIL'],$type)===FALSE) ? '' : ' hide' ;
      $class_plus  = (strpos($_SESSION['USER_PARAM_ACCUEIL'],$type)!==FALSE) ? '' : ' hide' ;
      $toggle_moins = '<a href="#toggle_'.$type.'" class="toggle_moins" title="Masquer"></a>';
      $toggle_plus  = '<div id="'.$type.'_plus" class="rien64'.$class_plus.'"><a href="#toggle_'.$type.'" class="toggle_plus" title="Voir"></a> '.$masque.''.$info_nombre.'</div>';
    }
    else
    {
      $class_moins = $class_plus = $toggle_moins = $toggle_plus = '' ;
    }
    echo $toggle_plus.'<div id="'.$type.'_moins" class="p accueil64 '.$type.'64'.$class_moins.'">'.str_replace('<TG>',$toggle_moins,$contenu).'</div>'.NL.'<hr />'.NL;
  }
  elseif( is_array($contenu) && count($contenu) ) // Seul 'messages' actuellement
  {
    foreach($contenu as $message_id => $tab_donnees_rubrique)
    {
      $class_moins = ( $tab_donnees_rubrique['visible']) ? '' : ' hide' ;
      $class_plus  = (!$tab_donnees_rubrique['visible']) ? '' : ' hide' ;
      $toggle_moins = '<a href="#toggle_'.$type.$message_id.'" class="toggle_moins" title="Masquer"></a>';
      $toggle_plus  = '<div id="'.$type.$message_id.'_plus" class="rien64'.$class_plus.'"><a href="#toggle_'.$type.$message_id.'" class="toggle_plus" title="Voir"></a> '.$tab_donnees_rubrique['titre'].'</div>';
      echo $toggle_plus.'<div id="'.$type.$message_id.'_moins" class="p accueil64 '.$type.'64'.$class_moins.'">'.'<p><span class="b">'.$toggle_moins.' '.$tab_donnees_rubrique['titre'].'</span></p>'.'<p>'.$tab_donnees_rubrique['message'].'</p>'.'</div>'.NL.'<hr />'.NL;
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Et enfin pour terminer : affichage d'une communication si convention signée par un partenaire ENT
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(isset($_SESSION['CONVENTION_PARTENAIRE_ENT_COMMUNICATION']))
{
  echo $_SESSION['CONVENTION_PARTENAIRE_ENT_COMMUNICATION'];
}

?>