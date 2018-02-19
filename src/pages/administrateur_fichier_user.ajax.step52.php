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
// Étape 52 - Traitement des actions à effectuer sur les utilisateurs (tous les cas)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['classes'] -> $tab_i_classe_TO_id_base ; $tab_liens_id_base['groupes'] -> $tab_i_groupe_TO_id_base ; $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
$tab_liens_id_base = FileSystem::recuperer_fichier_infos_serializees( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'liens_id_base.txt' );
$tab_i_classe_TO_id_base   = $tab_liens_id_base['classes'];
$tab_i_groupe_TO_id_base   = $tab_liens_id_base['groupes'];
$tab_i_fichier_TO_id_base  = $tab_liens_id_base['users'];
// On récupère le fichier avec des infos sur les utilisateurs : $tab_memo_analyse['modifier'] : id -> array ; $tab_memo_analyse['ajouter'] : i -> array ; $tab_memo_analyse['retirer'] : i -> array
$tab_memo_analyse = FileSystem::recuperer_fichier_infos_serializees( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'memo_analyse.txt' );
// Récupérer les éléments postés
$tab_check = (isset($_POST['f_check'])) ? explode(',',$_POST['f_check']) : array() ;
$tab_mod = array();  // id à modifier
$tab_add = array();  // i à ajouter
$tab_del = array();  // id à supprimer
foreach($tab_check as $check_infos)
{
  if(substr($check_infos,0,4)=='mod_')
  {
    $tab_mod[] = Clean::entier( substr($check_infos,4) );
  }
  elseif(substr($check_infos,0,4)=='add_')
  {
    $tab_add[] = Clean::entier( substr($check_infos,4) );
  }
  elseif(substr($check_infos,0,4)=='del_')
  {
    $tab_del[] = Clean::entier( substr($check_infos,4) );
  }
}
// Dénombrer combien d'actuels et d'anciens au départ
$profil_type = ($import_profil!='professeur') ? $import_profil : array('professeur','directeur') ;
list($nb_debut_actuel,$nb_debut_ancien) = DB_STRUCTURE_ADMINISTRATEUR::DB_compter_users_suivant_statut($profil_type);
// Retirer des users éventuels
$nb_del = 0;
if(count($tab_del))
{
  foreach($tab_del as $id_base)
  {
    if( isset($tab_memo_analyse['retirer'][$id_base]) )
    {
      $sortie_date_mysql = $tab_memo_analyse['retirer'][$id_base];
      // Mettre à jour l'enregistrement
      DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_user( $id_base , array(':sortie_date'=>$sortie_date_mysql) );
      $nb_del++;
      // En cas de sortie d'un élève, retirer les notes AB etc ultérieures à cette date de sortie, afin d'éviter des bulletins totalement vides
      if($profil_type=='eleve')
      {
        DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_user_saisies_absences_apres_sortie( $id_base , $sortie_date_mysql );
      }
    }
  }
}
// Ajouter des users éventuels
$nb_add = 0;
$tab_password = array();
$separateur = ';';
$classe_ou_profil = ($import_profil=='eleve') ? 'CLASSE' : 'PROFIL' ;
$fcontenu_csv = 'SCONET_Id'.$separateur.'SCONET_N°'.$separateur.'REFERENCE'.$separateur.$classe_ou_profil.$separateur.'NOM'.$separateur.'PRENOM'.$separateur.'COURRIEL'.$separateur.'LOGIN'.$separateur.'MOT DE PASSE'."\r\n\r\n";
$fcontenu_pdf_tab = array();
if(count($tab_add))
{
  // Récupérer les noms de classes pour le fichier avec les logins/mdp
  $tab_nom_classe = array();
  if($import_profil=='eleve')
  {
    $DB_TAB = DB_STRUCTURE_REGROUPEMENT::DB_lister_classes();
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_nom_classe[$DB_ROW['groupe_id']] = $DB_ROW['groupe_nom'];
    }
  }
  foreach($tab_add as $i_fichier)
  {
    if( isset($tab_memo_analyse['ajouter'][$i_fichier]) )
    {
      // Il peut théoriquement subsister un conflit de sconet_id pour des users ayant même reference, et réciproquement...
      // Construire le login
      $login = Outil::fabriquer_login($tab_memo_analyse['ajouter'][$i_fichier]['prenom'] , $tab_memo_analyse['ajouter'][$i_fichier]['nom'] , $tab_memo_analyse['ajouter'][$i_fichier]['profil_sigle']);
      // Puis tester le login (parmi tout le personnel de l'établissement)
      if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('login',$login) )
      {
        // Login pris : en chercher un autre en remplaçant la fin par des chiffres si besoin
        $login = DB_STRUCTURE_ADMINISTRATEUR::DB_rechercher_login_disponible($login);
      }
      // Construire le password
      if( ($import_profil!='eleve') || (!$_SESSION['TAB_PROFILS_ADMIN']['MDP_LONGUEUR_MINI']['ELV']) || (empty($tab_memo_analyse['ajouter'][$i_fichier]['birth_date'])) )
      {
        $password = Outil::fabriquer_mdp($tab_memo_analyse['ajouter'][$i_fichier]['profil_sigle']);
      }
      else
      {
        $password = str_replace('/','',$tab_memo_analyse['ajouter'][$i_fichier]['birth_date']);
      }
      // Attention à la date de naissance, définie seulement pour les élèves
      $birth_date = empty($tab_memo_analyse['ajouter'][$i_fichier]['birth_date']) ? NULL : To::date_french_to_mysql($tab_memo_analyse['ajouter'][$i_fichier]['birth_date']) ;
      // Attention aux LV, définies seulement pour les élèves
      $eleve_uai = empty($tab_memo_analyse['ajouter'][$i_fichier]['uai_origine']) ? ''  : $tab_memo_analyse['ajouter'][$i_fichier]['uai_origine'] ;
      $eleve_lv1 = empty($tab_memo_analyse['ajouter'][$i_fichier]['lv1'])         ? 100 : $tab_memo_analyse['ajouter'][$i_fichier]['lv1'] ;
      $eleve_lv2 = empty($tab_memo_analyse['ajouter'][$i_fichier]['lv2'])         ? 100 : $tab_memo_analyse['ajouter'][$i_fichier]['lv2'] ;
      // Ajouter l'utilisateur
      $user_id = DB_STRUCTURE_COMMUN::DB_ajouter_utilisateur(
        $tab_memo_analyse['ajouter'][$i_fichier]['sconet_id'],
        $tab_memo_analyse['ajouter'][$i_fichier]['sconet_num'],
        $tab_memo_analyse['ajouter'][$i_fichier]['reference'],
        $tab_memo_analyse['ajouter'][$i_fichier]['profil_sigle'],
        $tab_memo_analyse['ajouter'][$i_fichier]['genre'],
        $tab_memo_analyse['ajouter'][$i_fichier]['nom'],
        $tab_memo_analyse['ajouter'][$i_fichier]['prenom'],
        $birth_date,
        $tab_memo_analyse['ajouter'][$i_fichier]['courriel'],
        $tab_memo_analyse['ajouter'][$i_fichier]['email_origine'],
        $login,
        Outil::crypter_mdp($password),
        '', // id_ent
        '', // id_gepi
        $tab_memo_analyse['ajouter'][$i_fichier]['classe'],
        $eleve_uai,
        $eleve_lv1,
        $eleve_lv2
      );
      if($import_profil=='professeur')
      {
        // Pour les professeurs et directeurs, abonnement obligatoire aux signalements d'un souci pour une appréciation d'un bilan officiel
        DB_STRUCTURE_NOTIFICATION::DB_ajouter_abonnement( $user_id , 'bilan_officiel_appreciation' , 'accueil' );
      }
      $tab_i_fichier_TO_id_base[$i_fichier] = (int) $user_id;
      $nb_add++;
      $tab_password[$user_id] = $password;
      $classe_ou_profil = ($import_profil=='eleve') ? $tab_nom_classe[$tab_memo_analyse['ajouter'][$i_fichier]['classe']] : $tab_memo_analyse['ajouter'][$i_fichier]['profil_sigle'] ;
      $fcontenu_csv .= '"'.$tab_memo_analyse['ajouter'][$i_fichier]['sconet_id'].'"'.$separateur.'"'.$tab_memo_analyse['ajouter'][$i_fichier]['sconet_num'].'"'.$separateur.'"'.$tab_memo_analyse['ajouter'][$i_fichier]['reference'].'"'.$separateur.$classe_ou_profil.$separateur.$tab_memo_analyse['ajouter'][$i_fichier]['nom'].$separateur.$tab_memo_analyse['ajouter'][$i_fichier]['prenom'].$separateur.$tab_memo_analyse['ajouter'][$i_fichier]['courriel'].$separateur.$login.$separateur.'"'.$password.'"'."\r\n";
      $ligne1 = $classe_ou_profil;
      $ligne2 = $tab_memo_analyse['ajouter'][$i_fichier]['nom'].' '.$tab_memo_analyse['ajouter'][$i_fichier]['prenom'];
      $ligne3 = 'Utilisateur : '.$login;
      $ligne4 = 'Mot de passe : '.$password;
      $fcontenu_pdf_tab[] = $ligne1."\r\n".$ligne2."\r\n".$ligne3."\r\n".$ligne4;
    }
  }
}
// Modifier des users éventuels
$nb_mod = 0;
if(count($tab_mod))
{
  foreach($tab_mod as $id_base)
  {
    // Il peut théoriquement subsister un conflit de sconet_id pour des users ayant même reference, et réciproquement... idem pour l'adresse mail...
    $tab_champs = ($import_profil=='eleve')
                ? array( 'sconet_id' , 'sconet_num' , 'reference' , 'classe' , 'genre' , 'nom' , 'prenom' , 'birth_date' , 'courriel' , 'email_origine' , 'uai_origine' , 'lv1' , 'lv2' )
                : array( 'sconet_id' , 'reference' , 'profil_sigle' , 'genre' , 'nom' , 'prenom' , 'courriel' , 'email_origine' ) ;
    $DB_VAR  = array();
    foreach($tab_champs as $champ_ref)
    {
      if($tab_memo_analyse['modifier'][$id_base][$champ_ref] !== FALSE)
      {
        $DB_VAR[':'.$champ_ref] = ($champ_ref!='birth_date') ? $tab_memo_analyse['modifier'][$id_base][$champ_ref] : To::date_french_to_mysql($tab_memo_analyse['modifier'][$id_base][$champ_ref]) ;
      }
    }
    if($tab_memo_analyse['modifier'][$id_base]['entree'] !== FALSE)
    {
      $DB_VAR[':sortie_date'] = $tab_memo_analyse['modifier'][$id_base]['entree'];
    }
    // bilan
    if( count($DB_VAR) )
    {
      DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_user( $id_base , $DB_VAR );
    }
    $nb_mod++;
  }
}
// On enregistre (tableau mis à jour)
$tab_liens_id_base = array('classes'=>$tab_i_classe_TO_id_base,'groupes'=>$tab_i_groupe_TO_id_base,'users'=>$tab_i_fichier_TO_id_base);
FileSystem::enregistrer_fichier_infos_serializees( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'liens_id_base.txt', $tab_liens_id_base );
// Afficher le bilan
$lignes        = '';
$nb_fin_actuel = 0;
$nb_fin_ancien = 0;
$profil_type = ($import_profil!='professeur') ? $import_profil : array('professeur','directeur') ;
$with_classe = ($import_profil=='eleve') ? TRUE : FALSE ;
$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( $profil_type , 2 /*actuels_et_anciens*/ , 'user_id,user_sconet_id,user_sconet_elenoet,user_reference,user_profil_nom_court_singulier,user_nom,user_prenom,user_prenom,user_login,user_sortie_date' /*liste_champs*/ , $with_classe , TRUE /*tri_statut*/ );
foreach($DB_TAB as $DB_ROW)
{
  if(TODAY_MYSQL<$DB_ROW['user_sortie_date']) {$nb_fin_actuel++;} else {$nb_fin_ancien++;}
  if($mode=='complet')
  {
    $class       = (isset($tab_password[$DB_ROW['user_id']])) ? ' class="new"' : '' ;
    $td_password = (isset($tab_password[$DB_ROW['user_id']])) ? '<td class="new">'.html($tab_password[$DB_ROW['user_id']]).'</td>' : '<td class="i">champ crypté</td>' ;
    $champ = ($import_profil=='eleve') ? $DB_ROW['groupe_ref'] : $DB_ROW['user_profil_nom_court_singulier'] ;
    $date_sortie = ($DB_ROW['user_sortie_date']!=SORTIE_DEFAUT_MYSQL) ? To::date_mysql_to_french($DB_ROW['user_sortie_date']) : '-' ;
    $lignes .= '<tr'.$class.'><td>'.html($DB_ROW['user_sconet_id']).'</td><td>'.html($DB_ROW['user_sconet_elenoet']).'</td><td>'.html($DB_ROW['user_reference']).'</td><td>'.html($champ).'</td><td>'.html($DB_ROW['user_nom']).'</td><td>'.html($DB_ROW['user_prenom']).'</td><td'.$class.'>'.html($DB_ROW['user_login']).'</td>'.$td_password.'<td>'.$date_sortie.'</td></tr>'.NL;
  }
}
$s_debut_actuel = ($nb_debut_actuel>1) ? 's' : '';
$s_debut_ancien = ($nb_debut_ancien>1) ? 's' : '';
$s_fin_actuel   = ($nb_fin_actuel>1)   ? 's' : '';
$s_fin_ancien   = ($nb_fin_ancien>1)   ? 's' : '';
$s_mod = ($nb_mod>1) ? 's' : '';
$s_add = ($nb_add>1) ? 's' : '';
$s_del = ($nb_del>1) ? 's' : '';
if($nb_add)
{
  // On archive les nouveaux identifiants dans un fichier tableur (csv tabulé)
  $profil = ($import_profil=='eleve') ? 'eleve' : ( ($import_profil=='parent') ? 'parent' : 'personnel' ) ;
  $fnom = 'identifiants_'.$_SESSION['BASE'].'_'.$profil.'_'.FileSystem::generer_fin_nom_fichier__date_et_alea();
  FileSystem::ecrire_fichier( CHEMIN_DOSSIER_LOGINPASS.$fnom.'.csv' , To::csv($fcontenu_csv) );
  // On archive les nouveaux identifiants dans un fichier pdf (classe fpdf + script étiquettes)
  $pdf = new PDF_Label(array('paper-size'=>'A4', 'metric'=>'mm', 'marginLeft'=>5, 'marginTop'=>5, 'NX'=>3, 'NY'=>8, 'SpaceX'=>7, 'SpaceY'=>5, 'width'=>60, 'height'=>30, 'font-size'=>11));
  $pdf -> AddFont('Arial','' ,'arial.php');
  $pdf -> SetFont('Arial'); // Permet de mieux distinguer les "l 1" etc. que la police Times ou Courrier
  $pdf -> AddPage();
  $pdf -> SetFillColor(245,245,245);
  $pdf -> SetDrawColor(145,145,145);
  sort($fcontenu_pdf_tab);
  foreach($fcontenu_pdf_tab as $text)
  {
    $pdf -> Add_Label(To::pdf($text));
  }
  FileSystem::ecrire_sortie_PDF( CHEMIN_DOSSIER_LOGINPASS.$fnom.'.pdf' , $pdf );
}
$champ = ($import_profil=='eleve') ? 'Classe' : 'Profil' ;
Json::add_str('<p><label class="valide">'.$nb_debut_actuel.' utilisateur'.$s_debut_actuel.' actuel'.$s_debut_actuel.' et '.$nb_debut_ancien.' utilisateur'.$s_debut_ancien.' ancien'.$s_debut_ancien.' &rarr; '.$nb_mod.' utilisateur'.$s_mod.' modifié'.$s_mod.' + '.$nb_add.' utilisateur'.$s_add.' ajouté'.$s_add.' &minus; '.$nb_del.' utilisateur'.$s_del.' retiré'.$s_del.' &rarr; '.$nb_fin_actuel.' utilisateur'.$s_fin_actuel.' actuel'.$s_fin_actuel.' et '.$nb_fin_ancien.' utilisateur'.$s_fin_ancien.' ancien'.$s_fin_ancien.'.</label></p>'.NL);
if($mode=='complet')
{
  Json::add_str('<table>'.NL);
  Json::add_str(  '<thead>'.NL);
  Json::add_str(    '<tr><th>Id Sconet</th><th>N° Sconet</th><th>Référence</th><th>'.$champ.'</th><th>Nom</th><th>Prénom</th><th>Login</th><th>Mot de passe</th><th>Sortie</th></tr>'.NL);
  Json::add_str(  '</thead>'.NL);
  Json::add_str(  '<tbody>'.NL);
  Json::add_str(    $lignes);
  Json::add_str(  '</tbody>'.NL);
  Json::add_str('</table>'.NL);
}
if($nb_add)
{
  Json::add_str('<ul class="puce p"><li><a href="#" class="step53">Récupérer les identifiants de tout nouvel utilisateur inscrit.</a><input id="archive" name="archive" type="hidden" value="'.$fnom.'" /><label id="ajax_msg">&nbsp;</label></li></ul>'.NL);
}
else
{
  Json::add_str('<p class="astuce">Il n\'y a aucun nouvel utilisateur inscrit, donc pas d\'identifiants à récupérer.</p>'.NL);
  switch($import_origine.'+'.$import_profil)
  {
    case 'siecle+eleve'       : $etape = 6; $STEP = 61; break;
    case 'siecle+professeur'  : $etape = 6; $STEP = 61; break;
    case 'tableur+eleve'      : $etape = 6; $STEP = 61; break;
    case 'tableur+professeur' : $etape = 6; $STEP = 61; break;
    case 'siecle+parent'      : $etape = 4; $STEP = 71; break;
    case 'tableur+parent'     : $etape = 4; $STEP = 71; break;
    case 'onde+parent'        : $etape = 4; $STEP = 71; break;
    case 'factos+parent'      : $etape = 4; $STEP = 71; break;
    case 'onde+eleve'         : $etape = 5; $STEP = 90; break;
  }
  Json::add_str('<ul class="puce p"><li><a href="#step'.$STEP.'" id="passer_etape_suivante">Passer à l\'étape '.$etape.'.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL);
}

?>
