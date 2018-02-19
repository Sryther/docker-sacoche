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

class RSS
{

  // //////////////////////////////////////////////////
  // Méthodes internes (privées)
  // //////////////////////////////////////////////////

  /**
   * Retourne le nom du fichier RSS d'un prof.
   * 
   * @param int     $prof_id
   * @return string
   */
  private static function nom_fichier_prof($prof_id)
  {
    // Le nom du RSS est tordu pour le rendre un minimum privé, sans être totalement aléatoire car il doit être fixe (mais il n'y a rien de confidentiel non plus).
    $fichier_nom_debut = 'rss_'.$prof_id;
    $fichier_nom_fin   = FileSystem::generer_fin_nom_fichier__pseudo_alea($fichier_nom_debut);
    return $fichier_nom_debut.'_'.$fichier_nom_fin.'.xml';
  }

  /**
   * Créer un fichier RSS d'un prof vierge (pour recueillir les demandes d'évaluations des élèves).
   * 
   * @param string   $fichier_chemin
   * @param string   $fichier_url
   * @return void
   */
  private static function creer_fichier($fichier_chemin,$fichier_url)
  {
    $fichier_contenu ='<?xml version="1.0" encoding="utf-8"?>'."\r\n";
    $fichier_contenu.='<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">'."\r\n";
    $fichier_contenu.='<channel>'."\r\n\r\n";
    $fichier_contenu.='  <title>SACoche</title>'."\r\n";
    $fichier_contenu.='  <link>'.URL_INSTALL_SACOCHE.'</link>'."\r\n";
    $fichier_contenu.='  <description>Demandes d\'évaluations.</description>'."\r\n";
    $fichier_contenu.='  <language>fr-FR</language>'."\r\n";
    $fichier_contenu.='  <lastBuildDate>'.date("r",$_SERVER['REQUEST_TIME']).'</lastBuildDate>'."\r\n";
    $fichier_contenu.='  <docs>http://www.scriptol.fr/rss/RSS-2.0.html</docs>'."\r\n";
    $fichier_contenu.='  <atom:link href="'.$fichier_url.'" rel="self" type="application/rss+xml" />'."\r\n";
    $fichier_contenu.='  <image>'."\r\n";
    $fichier_contenu.='    <url>'.SERVEUR_PROJET.'/_img/logo_rss.png</url>'."\r\n";
    $fichier_contenu.='    <title>SACoche</title>'."\r\n";
    $fichier_contenu.='    <link>'.URL_INSTALL_SACOCHE.'</link>'."\r\n";
    $fichier_contenu.='    <width>144</width>'."\r\n";
    $fichier_contenu.='    <height>45</height>'."\r\n";
    $fichier_contenu.='    <description></description>'."\r\n";
    $fichier_contenu.='  </image>'."\r\n\r\n";
    $fichier_contenu.='</channel>'."\r\n";
    $fichier_contenu.='</rss>'."\r\n";
    FileSystem::ecrire_fichier($fichier_chemin,$fichier_contenu);
  }

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

  /**
   * Retourne l'URL du fichier RSS d'un prof.
   * 
   * @param int   $prof_id
   * @return string
   */
  public static function url_prof($prof_id)
  {
    $fichier_nom    = RSS::nom_fichier_prof($prof_id);
    $fichier_chemin = CHEMIN_DOSSIER_RSS.$_SESSION['BASE'].DS.$fichier_nom;
    $fichier_url    = URL_DIR_RSS.$_SESSION['BASE'].'/'.$fichier_nom;
    // S'il n'existe pas, en créer un vierge.
    if(!file_exists($fichier_chemin))
    {
      RSS::creer_fichier($fichier_chemin,$fichier_url);
    }
    return $fichier_url;
  }

  /**
   * Mettre à jour le fichier RSS d'un prof avec une demande d'évaluation d'élève.
   * 
   * @param string   $fichier_chemin
   * @param string   $titre
   * @param string   $texte
   * @param string   $guid
   * @return void
   */
  public static function modifier_fichier_prof($prof_id,$titre,$texte,$guid)
  {
    $fichier_nom    = RSS::nom_fichier_prof($prof_id);
    $fichier_chemin = CHEMIN_DOSSIER_RSS.$_SESSION['BASE'].DS.$fichier_nom;
    $fichier_url    = URL_DIR_RSS.$_SESSION['BASE'].'/'.$fichier_nom;
    // S'il n'existe pas, en créer un vierge.
    if(!file_exists($fichier_chemin))
    {
      RSS::creer_fichier($fichier_chemin,$fichier_url);
    }
    // Ajouter l'article
    $date = date('r',$_SERVER['REQUEST_TIME']);
    $fichier_contenu = file_get_contents($fichier_chemin);
    $article ='  <item>'."\r\n";
    $article.='    <title>'.html($titre).'</title>'."\r\n";
    $article.='    <link>'.URL_INSTALL_SACOCHE.'</link>'."\r\n";
    $article.='    <description>'.html($texte).'</description>'."\r\n";
    $article.='    <pubDate>'.$date.'</pubDate>'."\r\n";
    $article.='    <guid isPermaLink="false">'.$guid.'</guid>'."\r\n";
    $article.='  </item>'."\r\n\r\n";
    $bad = '  </image>'."\r\n\r\n";
    $bon = '  </image>'."\r\n\r\n".$article;
    $fichier_contenu = str_replace($bad,$bon,$fichier_contenu);
    // Mettre à jour la date de reconstruction
    $pbad = '#<lastBuildDate>(.*?)</lastBuildDate>#';
    $pbon = '<lastBuildDate>'.$date.'</lastBuildDate>';
    $fichier_contenu = preg_replace($pbad,$pbon,$fichier_contenu);
    // Couper si le fichier est long (on le ramène à 100Ko) ; ça laisse encore environ 250 entrées dans le flux.
    if(mb_strlen($fichier_contenu)>120000)
    {
      $pos = mb_strpos($fichier_contenu,'</item>',100000);
      $fichier_contenu = mb_substr($fichier_contenu,0,$pos).'</item>'."\r\n\r\n".'</channel>'."\r\n".'</rss>'."\r\n";
    }
    // Enregistrer
    FileSystem::ecrire_fichier($fichier_chemin,$fichier_contenu);
  }

}
?>