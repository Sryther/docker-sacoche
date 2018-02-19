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

// Stream handler to read from global variables
// Requis pour PDF_MemImage() MemImage() (http://www.fpdf.org/fr/script/script45.php)

class VariableStream
{
  var $varname;
  var $position;

  function stream_open($path, $mode, $options, &$opened_path)
  {
    $url = parse_url($path);
    $this->varname = $url['host'];
    if(!isset($GLOBALS[$this->varname]))
    {
      trigger_error('Global variable '.$this->varname.' does not exist', E_USER_WARNING);
      return FALSE;
    }
    $this->position = 0;
    return TRUE;
  }

  function stream_read($count)
  {
    $ret = substr($GLOBALS[$this->varname], $this->position, $count);
    $this->position += strlen($ret);
    return $ret;
  }

  function stream_eof()
  {
    return $this->position >= strlen($GLOBALS[$this->varname]);
  }

  function stream_tell()
  {
    return $this->position;
  }

  function stream_seek($offset, $whence)
  {
    if($whence==SEEK_SET)
    {
      $this->position = $offset;
      return TRUE;
    }
    return FALSE;
  }

  function stream_stat()
  {
    return array();
  }
}

// Extension de classe qui étend FPDF

class PDF extends FPDF
{

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Pour optimiser la gestion de la mémoire et éviter un « Fatal error : Allowed memory size ... »
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Consulter  http://www.fpdf.org/fr/script/script18.php (a l'avantage de ne rien changer au code)
   * Voir aussi http://www.fpdf.org/fr/script/script76.php (a l'inconvénient qu'il faut changer certaines syntaxes, et malgré un a priori favorable des tests ont montré que c'était totalement équivalent à l'autre script)
   * 
   * Attention : la mémoire utilisée par fpdf n'est pas comptabilisée dans memory_get_usage().
   * Un script peut annoncer utiliser 8Mo, et dépasser en réalité 32Mo même pour générer un pdf de 200Ko au final.
  **/

  public function _putpages()
  {
    $nb=$this->page;
    if(!empty($this->AliasNbPages))
    {
      //Replace number of pages
      for($n=1;$n<=$nb;$n++)
      {
        if($this->compress)
          $this->pages[$n]=gzcompress(str_replace($this->AliasNbPages,$nb,gzuncompress($this->pages[$n])));
        else
          $this->pages[$n]=str_replace($this->AliasNbPages,$nb,$this->pages[$n]);
      }
    }
    if($this->DefOrientation=='P')
    {
      $wPt=$this->DefPageSize[0]*$this->k;
      $hPt=$this->DefPageSize[1]*$this->k;
    }
    else
    {
      $wPt=$this->DefPageSize[1]*$this->k;
      $hPt=$this->DefPageSize[0]*$this->k;
    }
    $filter=($this->compress) ? '/Filter /FlateDecode ' : '';
    for($n=1;$n<=$nb;$n++)
    {
      //Page
      $this->_newobj();
      $this->_out('<</Type /Page');
      $this->_out('/Parent 1 0 R');
      if(isset($this->PageSizes[$n]))
        $this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]',$this->PageSizes[$n][0],$this->PageSizes[$n][1]));
      $this->_out('/Resources 2 0 R');
      if(isset($this->PageLinks[$n]))
      {
        //Links
        $annots='/Annots [';
        foreach($this->PageLinks[$n] as $pl)
        {
          $rect=sprintf('%.2F %.2F %.2F %.2F',$pl[0],$pl[1],$pl[0]+$pl[2],$pl[1]-$pl[3]);
          $annots.='<</Type /Annot /Subtype /Link /Rect ['.$rect.'] /Border [0 0 0] ';
          if(is_string($pl[4]))
            $annots.='/A <</S /URI /URI '.$this->_textstring($pl[4]).'>>>>';
          else
          {
            $l=$this->links[$pl[4]];
            $h=isset($this->PageSizes[$l[0]]) ? $this->PageSizes[$l[0]][1] : $hPt;
            $annots.=sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]>>',1+2*$l[0],$h-$l[1]*$this->k);
          }
        }
        $this->_out($annots.']');
      }
      $this->_out('/Contents '.($this->n+1).' 0 R>>');
      $this->_out('endobj');
      //Page content
      $p=$this->pages[$n];
      $this->_newobj();
      $this->_out('<<'.$filter.'/Length '.strlen($p).'>>');
      $this->_putstream($p);
      $this->_out('endobj');
    }
    //Pages root
    $this->offsets[1]=strlen($this->buffer);
    $this->_out('1 0 obj');
    $this->_out('<</Type /Pages');
    $kids='/Kids [';
    for($i=0;$i<$nb;$i++)
      $kids.=(3+2*$i).' 0 R ';
    $this->_out($kids.']');
    $this->_out('/Count '.$nb);
    $this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]',$wPt,$hPt));
    $this->_out('>>');
    $this->_out('endobj');
  }

  public function _endpage()
  {
    parent::_endpage();
    if($this->compress)
      $this->pages[$this->page] = gzcompress($this->pages[$this->page]);
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Pour ajuster l'étirement d'un texte dans une cellule en fonction de sa longueur
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Consulter  http://www.fpdf.org/fr/script/script62.php (repris partiellement)
 * Voir aussi http://www.fpdf.org/fr/script/script32.php
**/

  public function FitOn( $largeur_cellule , $texte )
  {
    $this->cMargin_memo = $this->cMargin ;
    if($largeur_cellule<=10)
    {
      $this->cMargin = $largeur_cellule/10 ;
    }
    $largeur_texte = $this->GetStringWidth($texte);
    $largeur_cellule = ($largeur_cellule!=0) ? $largeur_cellule : $this->w - $this->rMargin - $this->x ;
    $ratio = ($largeur_texte) ? ( $largeur_cellule - $this->cMargin*2 ) / $largeur_texte : 1 ;
    $fit = ($ratio < 1);
    if ($fit)
    {
      $horiz_scale = $ratio*100.0;
      $this->_out(sprintf('BT %.2F Tz ET',$horiz_scale));
    }
    return $fit;
  }

  public function FitOff($fit)
  {
    $this->cMargin = $this->cMargin_memo ;
    if ($fit)
    {
      $this->_out('BT 100 Tz ET');
    }
  }

  public function CellFit($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=FALSE, $link='')
  {
    $fit = $this->FitOn( $w , $txt );
    $align = ($fit) ? 'L' : $align ;
    $this->Cell($w,$h,$txt,$border,$ln,$align,$fill,$link);
    $this->FitOff($fit);
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Pour écrire un texte tourné
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Consulter  http://www.fpdf.org/fr/script/script31.php
 * Voir aussi http://www.fpdf.org/fr/script/script2.php
**/

  public function TextWithRotation($x, $y, $txt, $txt_angle, $font_angle=0)
  {
    $font_angle += 90+$txt_angle;
    $txt_angle  *= M_PI/180;
    $font_angle *= M_PI/180;
    $txt_dx  = cos($txt_angle);
    $txt_dy  = sin($txt_angle);
    $font_dx = cos($font_angle);
    $font_dy = sin($font_angle);
    $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',$txt_dx,$txt_dy,$font_dx,$font_dy,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
    if ($this->ColorFlag)
      $s = 'q '.$this->TextColor.' '.$s.' Q';
    $this->_out($s);
  }

/*
  public function TextWithDirection($x, $y, $txt, $direction='R')
  {
    if ($direction=='R')
      $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',1,0,0,1,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
    elseif ($direction=='L')
      $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',-1,0,0,-1,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
    elseif ($direction=='U')
      $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',0,1,-1,0,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
    elseif ($direction=='D')
      $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',0,-1,1,0,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
    else
      $s = sprintf('BT %.2F %.2F Td (%s) Tj ET',$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
    if ($this->ColorFlag)
      $s = 'q '.$this->TextColor.' '.$s.' Q';
    $this->_out($s);
  }
*/

  public function VertCell($width, $height, $txt, $border=0, $ln=0, $fill=FALSE)
  {
    $memo_x = $this->GetX();
    $memo_y = $this->GetY();
    // Cellule si bordure et/ou couleur
    if( $border || $fill )
    {
      $this->Cell($width , $height , '' , $border /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , $fill /*fond*/ , '');
    }
    // Texte tourné de 90°
    $x = $memo_x + ($width)/2 + 1 ;
    $y = $memo_y + $height - 1 ;
    $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',0,1,-1,0, $x*$this->k ,($this->h-$y)*$this->k , $this->_escape($txt) );
    if ($this->ColorFlag)
      $s = 'q '.$this->TextColor.' '.$s.' Q';
    $this->_out($s);
    // Positionnement xy
    switch($ln)
    {
      case 0 : $this->SetXY( $memo_x+$width , $memo_y ); break;
      case 1 : $this->SetXY( $this->lMargin , $memo_y+$height ); break;
      case 2 : $this->SetXY( $memo_x , $memo_y+$height ); break;
    }
  }

  public function VertCellFit($width, $height, $txt, $border=0, $ln=0, $fill=FALSE)
  {
    $fit = $this->FitOn( $height , $txt );
    $this->VertCell($width, $height, $txt, $border, $ln, $fill);
    $this->FitOff($fit);
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Pour savoir le nombre de lignes requises afin d'écrire un texte, et le découper en conséquence.
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Consulter  http://www.fpdf.org/fr/script/script49.php (modifié pour renvoyer une nouvelle chaine et pas passer l'ancienne en référence à la fonction)
 * Voir aussi http://www.fpdf.org/fr/script/script16.php (MultiCell permettant de spécifier un paramètre maxline et de renvoyer la partie de la chaîne qui n'a pas été imprimée, mais pas d'adaptation de l'interligne...)
 * Pourquoi pas regarder un jour GetStringWidth() <https://github.com/tbelliard/gepi/blob/master/fpdf/tfpdf.php> et cell_ajustee() <https://github.com/tbelliard/gepi/blob/master/lib/share-pdf.inc.php>
**/

  public function WordWrap($text, $maxwidth)
  {
    $text = trim($text);
    if ($text==='')
    {
      return array(0,'');
    }
    $space = $this->GetStringWidth(' ');
    $lines = explode("\n", $text);
    $text = '';
    foreach ($lines as $line)
    {
      $words = preg_split('/ +/', $line);
      $width = 0;
      foreach ($words as $word)
      {
        $wordwidth = $this->GetStringWidth($word);
        if ($wordwidth > $maxwidth)
        {
          // Word is too long, we cut it
          /*
          for($i=0; $i<strlen($word); $i++)
          {
            $wordwidth = $this->GetStringWidth(substr($word, $i, 1));
            if($width + $wordwidth <= $maxwidth)
            {
              $width += $wordwidth;
              $text .= substr($word, $i, 1);
            }
            else
            {
              $width = $wordwidth;
              $text = rtrim($text)."\n".substr($word, $i, 1);
            }
          }
          */
          $width = $wordwidth;
          $text = ($text) ? rtrim($text)."\n".$word.' ' : $word."\n" ;
        }
        elseif($width + $wordwidth <= $maxwidth)
        {
          $width += $wordwidth + $space;
          $text .= $word.' ';
        }
        else
        {
          $width = $wordwidth + $space;
          $text = rtrim($text)."\n".$word.' ';
        }
      }
      $text = rtrim($text)."\n";
    }
    $text = rtrim($text);
    $count = mb_substr_count($text,"\n")+1;
    return array($count,$text);
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Pour afficher des images chargées en mémoire sans avoir besoin de passer par un fichier temporaire.
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Consulter http://www.fpdf.org/fr/script/script45.php
 * Utilise aussi "stream_wrapper_register('var', 'VariableStream');" dans le constructeur et la classe "VariableStream" située au début de ce fichier
**/

  public function MemImage($data, $x=NULL, $y=NULL, $w=0, $h=0, $type='', $link='')
  {
    // Display the image contained in $data
    $v = 'img'.md5($data);
    $GLOBALS[$v] = $data;
    $this->Image('var://'.$v, $x, $y, $w, $h, $type, $link);
    unset($GLOBALS[$v]);
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Pour tracer un cercle (ou une ellipse)
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Consulter  http://www.fpdf.org/fr/script/script6.php
 * Voir aussi http://www.fpdf.org/fr/script/script28.php
 * Voir aussi http://www.fpdf.org/fr/script/script69.php
**/

  public function Circle($x, $y, $r, $style='D')
  {
    $this->Ellipse($x,$y,$r,$r,$style);
  }

  public function Ellipse($x, $y, $rx, $ry, $style='D')
  {
    if($style=='F')
      $op = 'f';
    elseif($style=='FD' || $style=='DF')
      $op = 'B';
    else
      $op = 'S';
    $lx = 4/3*(M_SQRT2-1)*$rx;
    $ly = 4/3*(M_SQRT2-1)*$ry;
    $k = $this->k;
    $h = $this->h;
    $this->_out(sprintf('%.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c', ($x+$rx)*$k, ($h-$y)*$k,       ($x+$rx)*$k, ($h-($y-$ly))*$k, ($x+$lx)*$k, ($h-($y-$ry))*$k,$x*$k, ($h-($y-$ry))*$k));
    $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',             ($x-$lx)*$k, ($h-($y-$ry))*$k, ($x-$rx)*$k, ($h-($y-$ly))*$k, ($x-$rx)*$k, ($h-$y)*$k));
    $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',             ($x-$rx)*$k, ($h-($y+$ly))*$k, ($x-$lx)*$k, ($h-($y+$ry))*$k, $x*$k,       ($h-($y+$ry))*$k));
    $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c %s',          ($x+$lx)*$k, ($h-($y+$ry))*$k, ($x+$rx)*$k, ($h-($y+$ly))*$k, ($x+$rx)*$k, ($h-$y)*$k,             $op));
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Pour tracer une ligne en pointillés
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Consulter  http://www.fpdf.org/fr/script/script33.php
**/

  function SetDash($black=NULL, $white=NULL)
  {
    if($black!==NULL)
      $s=sprintf('[%.3F %.3F] 0 d',$black*$this->k,$white*$this->k);
    else
      $s='[] 0 d';
    $this->_out($s);
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Attributs de la classe (équivalents des "variables")
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  // Pour les valeur de la session courante ou celles enregistrées lors d'un bilan officiel antérieur
  private $SESSION = array();
  // Couleurs de fond
  private $tab_couleur       = array();
  private $tab_choix_couleur = array();
  // Lettres utilisées en remplacement des images Lomer pour du noir et blanc
  private $tab_lettre = array();
  // Valeurs principales pour la mise en page PDF
  private $officiel      = NULL;
  private $orientation   = '';
  private $couleur       = 'oui';
  private $fond          = TRUE;
  private $legende       = 1;
  private $filigrane     = NULL;
  private $page_largeur  = 0;
  private $page_hauteur  = 0;
  private $marge_haut    = 5;
  private $marge_gauche  = 5;
  private $marge_droite  = 5;
  private $marge_bas     = 5;
  private $distance_pied = 0;
  private $page_largeur_moins_marges = 0;
  private $page_hauteur_moins_marges = 0;
  // Conserver les informations de l'élève pour une recopie sur plusieurs pages
  private $eleve_id     = 0;
  private $eleve_nom    = '';
  private $eleve_prenom = '';
  private $doc_titre    = '';
  // Définition de qqs variables supplémentaires
  private $releve_modele   = '';
  private $releve_format   = '';
  private $synthese_modele = '';
  private $afficher_score  = NULL;
  private $afficher_degre  = NULL;
  // idem
  private $cases_nb              = 0;
  private $cases_largeur         = 0;
  private $cases_hauteur         = 0;
  private $lignes_hauteur        = 0;
  private $reference_largeur     = 0;
  private $intitule_largeur      = 0;
  private $synthese_largeur      = 0;
  private $etiquette_hauteur     = 0;
  private $colonne_bilan_largeur = 0;
  private $colonne_vide_largeur  = 0;
  // idem
  private $pilier_largeur      = 0;
  private $section_largeur     = 0;
  private $item_largeur        = 0;
  private $pourcentage_largeur = 0;
  // idem
  private $eleve_largeur     = 0;
  private $taille_police     = 8;
  // idem
  private $lomer_espace_largeur = 0;
  private $lomer_espace_hauteur = 0;
  private $lomer_image_largeur  = 0;
  private $lomer_image_hauteur  = 0;
  // idem
  private $coef_conv_pixel_to_mm = 0;
  private $photo_hauteur_maxi    = 0;
  private $cadre_photo_hauteur   = 0;
  // idem
  private $page_nombre_alias      = '{|}'; // Pas celui de FPDF ($this->AliasNbPages) car géré différemment (plusieurs élèves par fichier) ; court car occupation en largeur prise en compte.
  private $page_numero_first      = 1;
  private $page_nombre_alignement = '';
  // idem
  private $tab_legende_notes_speciales_texte  = array('AB'=>'Absent','DI'=>'Dispensé','NE'=>'Non évalué','NF'=>'Non fait','NN'=>'Non noté','NR'=>'Non rendu');
  private $tab_legende_notes_speciales_nombre = array('AB'=>0       ,'DI'=>0         ,'NE'=>0           ,'NF'=>0         ,'NN'=>0         ,'NR'=>0          );

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode Magique - Constructeur
  // Tous les paramètres doivent avoir des valeurs par défaut pour ne pas poser de soucis en cas d'instanciation depuis une autre classe (FPDF_TPL ou PDFMerger par exemple).
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function __construct( $officiel=NULL , $orientation='portrait' , $marge_gauche=5 , $marge_droite=5 , $marge_haut=5 , $marge_bas=12 , $couleur='oui' , $fond='gris' , $legende='oui' , $filigrane=NULL , $session_archive=array() )
  {
    // Register var stream protocol => Voir MemImage()
    if (in_array('var', stream_get_wrappers()))
    {
      stream_wrapper_unregister('var');
    }
    stream_wrapper_register('var', 'VariableStream');
    // Appeler le constructeur de la classe mère
    parent::__construct( $orientation , 'mm' /*unit*/ , 'A4' /*format*/ );
    // On passe à la classe fille
    $this->officiel    = $officiel;
    $this->orientation = $orientation;
    $this->couleur     = $couleur;
    $this->fond        = ($fond=='gris') ? TRUE : FALSE ;
    $this->legende     = ($legende=='oui') ? 1 : 0 ;
    $this->filigrane   = $filigrane;
    // Valeurs de session utilisées
    // Peuvent être surchargées lors de l'impression d'une archive d'un bilan officiel (sauf les 2 derniers qui ne sont pas utilisés dans cette situation).
    $tab_clefs = array(
      'OFFICIEL'                   => array(),
      'ACQUIS'                     => array(),
      'LIVRET'                     => array(),
      'NOTE'                       => array(),
      'NOTE_ACTIF'                 => array(),
      'NOMBRE_CODES_NOTATION'      => 0,
      'DROIT_VOIR_SCORE_BILAN'     => '',
      'DROIT_VOIR_SCORE_MAITRISE'  => '',
      'ENVELOPPE'                  => array(),
      'ETABLISSEMENT_DENOMINATION' => '',
    );
    foreach( $tab_clefs as $CLEF => $default_value )
    {
      $this->SESSION[$CLEF] = ( $officiel && isset($session_archive[$CLEF]) ) ? $session_archive[$CLEF] : ( isset($_SESSION[$CLEF]) ? $_SESSION[$CLEF] : $default_value ) ;
    }
    // Pour un bilan officiel on prend les droits du profil parent, surtout qu'il peut être imprimé par un administrateur (pas de droit paramétré pour lui).
    $forcer_profil = ($this->officiel) ? 'TUT' : NULL ;
    $this->afficher_score = Outil::test_user_droit_specifique( $this->SESSION['DROIT_VOIR_SCORE_BILAN']    , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ , $forcer_profil );
    $this->afficher_degre = Outil::test_user_droit_specifique( $this->SESSION['DROIT_VOIR_SCORE_MAITRISE'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ , $forcer_profil );
    // Déclaration de la police pour la rendre disponible même si non présente sur le serveur
    $this->AddFont('Arial','' ,'arial.php');
    $this->AddFont('Arial','B','arialbd.php');
    // initialiser les marges principales
    if($orientation=='portrait')
    {
      $this->page_largeur  = 210;
      $this->page_hauteur  = 297;
      $this->marge_haut    = $marge_haut;
      $this->marge_gauche  = $marge_gauche;
      $this->marge_droite  = $marge_droite;
      $this->marge_bas     = ($this->officiel) ? $marge_bas : max(12,$marge_bas) ;
      $this->distance_pied = 9;
    }
    elseif($orientation=='landscape')
    {
      $this->page_largeur  = 297;
      $this->page_hauteur  = 210;
      $this->marge_haut    = $marge_droite;
      $this->marge_gauche  = $marge_haut;
      $this->marge_droite  = ($this->officiel) ? $marge_bas    : max(12,$marge_bas) ;
      $this->marge_bas     = ($this->officiel) ? $marge_gauche : max(10,$marge_gauche) ;
      $this->distance_pied = 7;
    }
    $this->page_largeur_moins_marges = $this->page_largeur - $this->marge_gauche - $this->marge_droite ;
    $this->page_hauteur_moins_marges = $this->page_hauteur - $this->marge_haut   - $this->marge_bas ;
    // Couleurs prédéfinies
    $this->tab_couleur['blanc']      = array('r'=>255,'v'=>255,'b'=>255);
    $this->tab_couleur['gris_clair'] = array('r'=>230,'v'=>230,'b'=>230);
    $this->tab_couleur['gris_moyen'] = array('r'=>190,'v'=>190,'b'=>190);
    $this->tab_couleur['gris_fonce'] = array('r'=>150,'v'=>150,'b'=>150);
    $this->tab_couleur['noir']       = array('r'=>  0,'v'=>  0,'b'=>  0);
    $this->tab_couleur['rougevif']   = array('r'=>255,'v'=>  0,'b'=>  0);
    // Couleurs des états d'acquisition ; il faut convertir l'hexadécimal en RVB décimal
    foreach( $this->SESSION['ACQUIS'] as $acquis_id => $tab_acquis_info )
    {
      $r = hexdec(substr($tab_acquis_info['COULEUR'],1,2));
      $v = hexdec(substr($tab_acquis_info['COULEUR'],3,2));
      $b = hexdec(substr($tab_acquis_info['COULEUR'],5,2));
      $this->tab_couleur['A'.$acquis_id.'oui'] = array('r'=>$r,'v'=>$v,'b'=>$b);
      $r = hexdec(substr($tab_acquis_info['GRIS'],1,2));
      $v = hexdec(substr($tab_acquis_info['GRIS'],3,2));
      $b = hexdec(substr($tab_acquis_info['GRIS'],5,2));
      $this->tab_couleur['A'.$acquis_id.'non'] = array('r'=>$r,'v'=>$v,'b'=>$b);
    }
    // Couleurs des degrés de maîtrise du socle ; il faut convertir l'hexadécimal en RVB décimal
    foreach( $this->SESSION['LIVRET'] as $maitrise_id => $tab_maitrise_info )
    {
      $r = hexdec(substr($tab_maitrise_info['COULEUR'],1,2));
      $v = hexdec(substr($tab_maitrise_info['COULEUR'],3,2));
      $b = hexdec(substr($tab_maitrise_info['COULEUR'],5,2));
      $this->tab_couleur['M'.$maitrise_id.'oui'] = array('r'=>$r,'v'=>$v,'b'=>$b);
      $r = hexdec(substr($tab_maitrise_info['GRIS'],1,2));
      $v = hexdec(substr($tab_maitrise_info['GRIS'],3,2));
      $b = hexdec(substr($tab_maitrise_info['GRIS'],5,2));
      $this->tab_couleur['M'.$maitrise_id.'non'] = array('r'=>$r,'v'=>$v,'b'=>$b);
    }
    // Couleurs pour le Livret Scolaire
    $this->tab_couleur['livret_gris']            = array('r'=>243,'v'=>243,'b'=>244);
    $this->tab_couleur['livret_bleu_fonce']      = array('r'=>  0,'v'=>150,'b'=>213);
    $this->tab_couleur['livret_bleu_clair']      = array('r'=>226,'v'=>240,'b'=>250);
    $this->tab_couleur['livret_vert_fonce']      = array('r'=>174,'v'=>212,'b'=> 77);
    $this->tab_couleur['livret_vert_clair']      = array('r'=>232,'v'=>243,'b'=>206);
    $this->tab_couleur['livret_titre_eval']      = array('r'=> 21,'v'=>149,'b'=>214); // #1595D6
    $this->tab_couleur['livret_titre_ap']        = array('r'=>150,'v'=>179,'b'=>213); // #96B3D5
    $this->tab_couleur['livret_titre_epi']       = array('r'=>150,'v'=>179,'b'=>213); // #96B3D5
    $this->tab_couleur['livret_titre_parcours']  = array('r'=>150,'v'=>179,'b'=>213); // #96B3D5
    $this->tab_couleur['livret_titre_modaccomp'] = array('r'=>150,'v'=>179,'b'=>213); // #96B3D5
    $this->tab_couleur['livret_titre_bilan']     = array('r'=>173,'v'=>212,'b'=> 78); // #ADD44E
    $this->tab_couleur['livret_titre_viesco']    = array('r'=>247,'v'=>146,'b'=> 55); // #F79237
    $this->tab_couleur['livret_fond_eval']       = array('r'=>219,'v'=>229,'b'=>241); // #DBE5F1
    $this->tab_couleur['livret_fond_ap']         = array('r'=>225,'v'=>238,'b'=>248); // ##E1EEF8 ou #EEF5FB ('r'=>238,'v'=>245,'b'=>251) pour plus clair
    $this->tab_couleur['livret_fond_epi']        = array('r'=>225,'v'=>238,'b'=>248); // ##E1EEF8 ou #EEF5FB ('r'=>238,'v'=>245,'b'=>251) pour plus clair
    $this->tab_couleur['livret_fond_parcours']   = array('r'=>225,'v'=>238,'b'=>248); // ##E1EEF8 ou #EEF5FB ('r'=>238,'v'=>245,'b'=>251) pour plus clair
    $this->tab_couleur['livret_fond_modaccomp']  = array('r'=>225,'v'=>238,'b'=>248); // ##E1EEF8 ou #EEF5FB ('r'=>238,'v'=>245,'b'=>251) pour plus clair
    $this->tab_couleur['livret_fond_bilan']      = array('r'=>236,'v'=>245,'b'=>215); // #ECF5D7
    $this->tab_couleur['livret_fond_viesco']     = array('r'=>254,'v'=>208,'b'=>163); // #FED0A3

    // Lettres utilisées en remplacement des images Lomer pour du noir et blanc
    foreach( $this->SESSION['NOTE_ACTIF'] as $note_id )
    {
      $this->tab_lettre[$note_id] = $this->SESSION['NOTE'][$note_id]['SIGLE'];
    }
    $this->tab_lettre['PA'] = '.....';
    // Les dimensions d'une image (photo, signature) sont données en pixels, et il faut les convertir en mm.
    // Problème : dpi inconnue ! On prend 96 par défaut... mais ça peut être 72 ou 300 ou ... ça dépend de chaque image...
    // mm = (pixels * 25.4) / dpi
    // pixels = (mm * dpi) / 25.4
    $this->coef_conv_pixel_to_mm = 25.4 / 96 ;
    // Alignement du nombre de pages et du rappel des infos sur les pages
    $this->page_nombre_alignement = ($this->officiel) ? 'R' : 'C' ;
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode Magique - Pour récupérer un attribut private (c'est comme s'il était en lecture seule)
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function __get($nom)
  {
    return (isset($this->$nom)) ? $this->$nom : null ;
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode Magique - Pour affecter une valeur à un attribut
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function __set($nom,$valeur)
  {
    $this->$nom = $valeur;
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour calculer les dimensions d'une image Lomer
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function calculer_dimensions_images( $espace_largeur , $espace_hauteur )
  {
    $this->lomer_espace_largeur = $espace_largeur;
    $this->lomer_espace_hauteur = $espace_hauteur;
    // Une image a des dimensions initiales de 20px sur 10px
    $rapport_largeur = ($espace_largeur-1) / 20 ;
    $rapport_hauteur = ($espace_hauteur-1) / 10 ;
    $centrage     = ($rapport_largeur<$rapport_hauteur) ? 'hauteur' : 'largeur';
    $rapport_coef = ($centrage=='hauteur') ? $rapport_largeur : $rapport_hauteur ;
    $rapport_coef = min( Math::floorTo( $rapport_coef , 0.01 ) , 0.4 ) ; // valeur approchée au centième près par défaut
    $this->lomer_image_largeur = floor(20*$rapport_coef) ;
    $this->lomer_image_hauteur = floor(10*$rapport_coef) ;
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthodes pour choisir une couleur de fond ou une couleur de tracé ou une couleur de texte
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @param string $couleur   gris_fonce | gris_moyen | gris_clair | blanc | V{x}oui | V{x}non | A{x}oui | A{x}non | M{x}oui | M{x}non
   */
  public function choisir_couleur_fond($couleur)
  {
    $this->SetFillColor($this->tab_couleur[$couleur]['r'] , $this->tab_couleur[$couleur]['v'] , $this->tab_couleur[$couleur]['b']);
  }

  /**
   * @param string $couleur   noir | gris_moyen | gris_clair
   */
  public function choisir_couleur_trait($couleur)
  {
    $this->SetDrawColor($this->tab_couleur[$couleur]['r'] , $this->tab_couleur[$couleur]['v'] , $this->tab_couleur[$couleur]['b']);
  }

  /**
   * @param string $couleur   noir | gris_fonce | rougevif
   */
  public function choisir_couleur_texte($couleur)
  {
    $this->SetTextColor($this->tab_couleur[$couleur]['r'] , $this->tab_couleur[$couleur]['v'] , $this->tab_couleur[$couleur]['b']);
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour afficher une note Lomer
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function afficher_note_lomer( $note , $border , $br , $fill='' )
  {
    $tab_fill = array( ''=>'blanc' , 'prev_date'=>'gris_moyen' , 'prev_year'=>'gris_fonce' );
    $this->choisir_couleur_fond($tab_fill[$fill]);
    switch ($note)
    {
      case '1' :
      case '2' :
      case '3' :
      case '4' :
      case '5' :
      case '6' :
      case 'PA':
        if($this->couleur != 'non')
        {
          $memo_x = $this->GetX();
          $memo_y = $this->GetY();
          $img_pos_x = $memo_x + ( ($this->lomer_espace_largeur - $this->lomer_image_largeur) / 2 ) ;
          $img_pos_y = $memo_y + ( ($this->lomer_espace_hauteur - $this->lomer_image_hauteur) / 2 ) ;
          $this->Cell( $this->lomer_espace_largeur , $this->lomer_espace_hauteur , '' , $border /*bordure*/ , $br /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
          $this->Image( Html::note_src($note) , $img_pos_x , $img_pos_y , $this->lomer_image_largeur , $this->lomer_image_hauteur , 'GIF' );
          // $this->SetXY($memo_x , $memo_y);
          // $this->Cell( $this->lomer_espace_largeur , $this->lomer_espace_hauteur , '' , $border /*bordure*/ , $br /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
        }
        else
        {
          $txt = $this->tab_lettre[$note];
          $this->CellFit( $this->lomer_espace_largeur , $this->lomer_espace_hauteur ,  $txt , $border /*bordure*/ , $br /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
        }
        break;
      case 'AB' :
      case 'DI' :
      case 'NE' :
      case 'NF' :
      case 'NN' :
      case 'NR' :
        $this->tab_legende_notes_speciales_nombre[$note]++;
        $tab_texte = array('AB'=>'Abs.','DI'=>'Disp.','NE'=>'N.E.','NF'=>'N.F.','NN'=>'N.N.','NR'=>'N.R.');
        $this->cMargin /= 2;
        $this->CellFit( $this->lomer_espace_largeur , $this->lomer_espace_hauteur , $tab_texte[$note] , $border /*bordure*/ , $br /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
        $this->cMargin *= 2;
        break;
      default :
        $this->Cell( $this->lomer_espace_largeur , $this->lomer_espace_hauteur , '' , $border /*bordure*/ , $br /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
    }
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour afficher un pourcentage d'items acquis (texte par état d'acquisition et couleur de fond suivant le seuil)
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function afficher_pourcentage_acquis( $gras , $tab_infos , $affich )
{
  // $tab_infos contient {acquis} / 'nb' / '%'
  if($tab_infos['%']===FALSE)
  {
    $this->choisir_couleur_fond('blanc');
    $this->Cell( $this->pourcentage_largeur , $this->cases_hauteur , '-' , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
  }
  else
  {
    $etat_acquisition = OutilBilan::determiner_etat_acquisition( $tab_infos['%'] , NULL , $this->SESSION['ACQUIS'] );
    $this->choisir_couleur_fond('A'.$etat_acquisition.$this->couleur);
    if($affich=='detail')
    {
      $detail_acquisition = OutilBilan::afficher_nombre_acquisitions_par_etat( $tab_infos , FALSE /*detail_couleur*/ , $this->SESSION['ACQUIS'] );
      $this->SetFont('Arial' , $gras , $this->taille_police);
      $this->CellFit( $this->pourcentage_largeur , $this->cases_hauteur , To::pdf($tab_infos['%'].'% acquis ('.$detail_acquisition.')') , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
    }
    elseif($affich=='pourcentage')
    {
      $this->SetFont('Arial' , $gras , $this->taille_police/2);
      $this->Cell( $this->pourcentage_largeur , $this->cases_hauteur , To::pdf($tab_infos['%']) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
    }
    elseif($affich=='rien')
    {
      $this->Cell( $this->pourcentage_largeur , $this->cases_hauteur , '' , 1 /*bordure*/ , 0 /*br*/ , '' /*alignement*/ , TRUE /*fond*/ );
    }
  }
}

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour afficher un score bilan (bilan sur 100 et couleur de fond suivant le seuil)
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function afficher_score_bilan( $score , $br )
  {
    if($score===FALSE)
    {
      $affichage = ($this->afficher_score) ? '-' : '' ;
      $this->choisir_couleur_fond('blanc');
      $this->Cell( $this->cases_largeur , $this->cases_hauteur , $affichage , 1 /*bordure*/ , $br /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
    }
    else
    {
      $this->choisir_couleur_fond('A'.OutilBilan::determiner_etat_acquisition($score,NULL,$this->SESSION['ACQUIS']).$this->couleur);
      $affichage = ($this->afficher_score) ? $score : '' ;
      $this->SetFont('Arial' , '' , $this->taille_police-2);
      $this->Cell( $this->cases_largeur , $this->cases_hauteur , $affichage , 1 /*bordure*/ , $br /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
      $this->SetFont('Arial' , '' , $this->taille_police);
    }
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour afficher un degré de maîtrise (valeur sur 100 et couleur de fond suivant l'indice du degré atteint)
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function afficher_degre_maitrise( $indice , $valeur , $unite='' , $all_columns=TRUE )
  {
    if($valeur===FALSE)
    {
      $largeur = ($all_columns) ? $this->cases_largeur * 4 : $this->cases_largeur ;
      $this->choisir_couleur_fond('blanc');
      $this->Cell( $largeur , $this->cases_hauteur , '-' , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
    }
    elseif($all_columns)
    {
      for( $i=1 ; $i<5 ; $i++ )
      {
        $this->choisir_couleur_fond('M'.$i.$this->couleur);
        if($i==$indice)
        {
          $affichage = ($this->afficher_degre) ? $valeur.$unite : 'X' ;
        }
        else
        {
          $affichage = '';
        }
        $this->Cell( $this->cases_largeur , $this->cases_hauteur , $affichage , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
      }
    }
    else
    {
      $this->choisir_couleur_fond('M'.$indice.$this->couleur);
      $affichage = ($this->afficher_degre) ? $valeur.$unite : '' ;
      $this->CellFit( $this->cases_largeur , $this->cases_hauteur , $affichage , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
    }
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour afficher une barre avec les états des items acquis (rectangles par état d'acquisition et couleur de fond suivant le seuil)
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function afficher_proportion_acquis( $largeur , $hauteur , $tab_infos , $total , $avec_texte_nombre , $avec_texte_code )
  {
    // $tab_infos contient les états d'acquisition
    $abscisse = $this->GetX();
    $ordonnee = $this->GetY();
    // Couleurs de fond + textes
    foreach($tab_infos as $acquis_id => $nb)
    {
      $this->choisir_couleur_fond('A'.$acquis_id.$this->couleur);
      $largeur_case = $largeur*$nb/$total ;
          if(  $avec_texte_nombre &&  $avec_texte_code ) { $texte_complet = $nb.' '.$this->SESSION['ACQUIS'][$acquis_id]['SIGLE']; }
      elseif( !$avec_texte_nombre &&  $avec_texte_code ) { $texte_complet = $this->SESSION['ACQUIS'][$acquis_id]['SIGLE']; }
      elseif( !$avec_texte_nombre && !$avec_texte_code ) { $texte_complet = ''; }
      elseif(  $avec_texte_nombre && !$avec_texte_code ) { $texte_complet = $nb; }
      $texte = ( (strlen($texte_complet)<$largeur_case) || !$avec_texte_nombre || !$avec_texte_code ) ? $texte_complet : $nb ;
      $this->CellFit( $largeur_case , $hauteur , To::pdf($texte) , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
    }
    // Bordure unique autour
    $this->SetXY( $abscisse , $ordonnee );
    $this->Cell( $largeur , $hauteur , '' , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour afficher une appréciation sur plusieurs lignes dans une zone de dimensions données
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function afficher_appreciation( $largeur_autorisee , $hauteur_autorisee , $taille_police , $taille_interligne , $texte )
  {
    $this->SetFont('Arial' , '' , $taille_police);
    // Traiter un éventuel nombre de retours à la ligne saisis excessifs
    $nombre_lignes_tolerees  = max( 1 , floor($hauteur_autorisee / $taille_interligne) );
    $nombre_lignes_actuelles = substr_count($texte,"\n") + 1 ;
    if($nombre_lignes_actuelles>$nombre_lignes_tolerees)
    {
      $texte = str_replace( "\n\n" , "\n" , $texte );
    }
    $nombre_lignes_actuelles = substr_count($texte,"\n") + 1 ;
    if($nombre_lignes_actuelles>$nombre_lignes_tolerees)
    {
      $tab_lignes = explode("\n",$texte);
      $tab_blocs  = array_chunk($tab_lignes, $nombre_lignes_tolerees);
      $texte = implode("\n",$tab_blocs[0]).' '.implode(' ',$tab_blocs[1]);
    }
    // Ajustement de la taille de la police et de l'interligne si appréciation trop longue
    do
    {
      list($nb_lignes,$split_texte) = $this->WordWrap($texte,$largeur_autorisee);
      $hauteur_requise = $nb_lignes*$taille_interligne ;
      $is_trop_haut = ( $hauteur_requise > $hauteur_autorisee ) ? TRUE : FALSE ;
      if($is_trop_haut)
      {
        $taille_police *= 0.95;
        $taille_interligne *= 0.95;
        $this->SetFontSize($taille_police);
      }
    }
    while($is_trop_haut);
    // Affichage du texte ligne par ligne
    $this->SetFont('Arial' , '' , $taille_police);
    $memo_abscisse = $this->GetX();
    $memo_ordonnee = $this->GetY();
    $division_verticale = ($nb_lignes>1) ? 3 : 2 ;
    $ordonnee = $this->GetY() + ($hauteur_autorisee - $hauteur_requise ) / $division_verticale ; // Verticalement, on laisse 1/3 marge dessus et 2/3 marge dessous, sauf s'il n'y a qu'une ligne auquel cas on centre
    $this->SetXY( $memo_abscisse , $ordonnee );
    $tab_lignes = explode("\n",$split_texte);
    for( $num_ligne=0 ; $num_ligne<$nb_lignes ; $num_ligne++ )
    {
      $this->CellFit( $largeur_autorisee , $taille_interligne , To::pdf($tab_lignes[$num_ligne]) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    }
    $this->SetXY( $memo_abscisse , $memo_ordonnee+$hauteur_autorisee );
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour afficher une signature ou un logo d'établissement d'un bilan officiel ou pour le livret scolaire
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function afficher_image( $largeur_bloc , $hauteur_autorisee , $tab_image , $img_objet /* signature | logo | logo_seul */ )
  {
    extract( $tab_image , EXTR_PREFIX_ALL , 'img' ); // $img_contenu , $img_format , $img_largeur , $img_hauteur
    $img_largeur *= $this->coef_conv_pixel_to_mm;
    $img_hauteur *= $this->coef_conv_pixel_to_mm;
    $coef_ratio_largeur = ($img_objet=='signature') ? 2 : min( $img_largeur/$img_hauteur , 2 ) ;
    $largeur_autorisee  = ($img_objet!='logo_seul') ? $hauteur_autorisee * $coef_ratio_largeur : $largeur_bloc;
    $coef_largeur = $largeur_autorisee / $img_largeur ;
    $coef_hauteur = $hauteur_autorisee / $img_hauteur ;
    $ratio = min( $coef_largeur , $coef_hauteur , 1 ) ;
    $img_largeur *= $ratio;
    $img_hauteur *= $ratio;
    $retrait_x = ($img_objet=='signature') ? max($hauteur_autorisee,$img_largeur)       : $img_largeur  ;
    $img_pos_x = ($img_objet=='signature') ? $this->GetX() + $largeur_bloc - $retrait_x : $this->GetX() ;
    $img_pos_y = $this->GetY() + ( $hauteur_autorisee - $img_hauteur ) / 2 ;
    // Test pour éviter une erreur en cas d'archive avec image manquante
    if(strlen($img_contenu)>32)
    {
      $this->MemImage( base64_decode($img_contenu) , $img_pos_x , $img_pos_y , $img_largeur , $img_hauteur , strtoupper($img_format) );
    }
    return $retrait_x;
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour afficher des informations additionnelles sur un bilan officiel (absences/retards ; profs principaux ; message personnalisé)
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function afficher_lignes_additionnelles($tab_pdf_lignes_additionnelles)
  {
    $this->SetFont('Arial' , '' , $this->taille_police*1.2);
    foreach($tab_pdf_lignes_additionnelles as $i => $texte)
    {
      $sens = ($i) ? -1 : 1 ;
      $this->SetXY($this->marge_gauche , $this->GetY() + $sens*$this->taille_police*0.1 );
      $this->CellFit( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($texte) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    }
  }

  /**
   * Initialiser la légende des codes de notation spéciaux
   *
   * @param void
   * @return void
   */
  public function legende_initialiser()
  {
    $this->tab_legende_notes_speciales_nombre = array_fill_keys( array_keys($this->tab_legende_notes_speciales_nombre) , 0 );
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour afficher la légende ( $type_legende = 'codes_notation' | 'anciennete_notation' | 'score_bilan' | 'degre_maitrise' | 'etat_acquisition' )
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function afficher_legende( $type_legende , $ordonnee , $force_nb = FALSE )
  {
    $espace  = '     ';
    $espace_mini  = '   ';
    $hauteur = min(4,$this->lignes_hauteur*0.9);
    $size    = ceil($hauteur * 1.6);
    $this->SetXY($this->marge_gauche , $ordonnee);
    $case_hauteur = $hauteur*0.9;
    $case_largeur = $hauteur*0.9*1.5;
    //
    // Afficher la légende des codes de notation
    //
    if($type_legende=='codes_notation')
    {
      // Le texte des codes de notation étant personnalisable, il peut falloir condenser en largeur...
      $texte = 'Codes d\'évaluation :';
      foreach( $this->SESSION['NOTE_ACTIF'] as $note_id )
      {
        $texte .= $espace.$this->SESSION['NOTE'][$note_id]['LEGENDE'];
      }
      $boites_nb = $this->SESSION['NOMBRE_CODES_NOTATION'];
      foreach($this->tab_legende_notes_speciales_nombre as $note => $nombre)
      {
        if($nombre)
        {
          $texte .= $espace.$this->tab_legende_notes_speciales_texte[$note];
          $boites_nb++;
        }
      }
      $largeur_dispo_pour_texte = $this->page_largeur_moins_marges - $boites_nb*$this->lomer_espace_largeur;

      $largeur_texte = $this->GetStringWidth($texte);
      $ratio = min( 1 , $largeur_dispo_pour_texte / $largeur_texte );
      // On y va maintenant
      $this->SetFont('Arial' , 'B' , $size);
      $this->Write($hauteur , To::pdf('Codes d\'évaluation :') , '');
      $this->SetFont('Arial' , '' , $size);
      $memo_lomer_espace_largeur = $this->lomer_espace_largeur;
      $memo_lomer_espace_hauteur = $this->lomer_espace_hauteur;
      $memo_taille_police = $this->taille_police;
      $this->taille_police = $size; // On est obligé de le changer provisoirement car, si impression N&B, afficher_note_lomer() l'utilise
      $this->calculer_dimensions_images($case_largeur,$case_hauteur);
      foreach( $this->SESSION['NOTE_ACTIF'] as $note_id )
      {
        $texte = $this->SESSION['NOTE'][$note_id]['LEGENDE'];
        $largeur = $this->GetStringWidth($texte)*$ratio*1.1;
        $this->Write($hauteur , $espace_mini , '');
        $this->afficher_note_lomer($note_id, 1 /*border*/ , 0 /*br*/ );
        $this->CellFit( $largeur , $hauteur , To::pdf($texte) , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
        $texte .= $espace.$this->SESSION['NOTE'][$note_id]['LEGENDE'];
      }
      foreach($this->tab_legende_notes_speciales_nombre as $note => $nombre)
      {
        if($nombre)
        {
          $texte = $this->tab_legende_notes_speciales_texte[$note];
          $largeur = $this->GetStringWidth($texte)*$ratio*1.1;
          $this->Write($hauteur , $espace_mini , '');
          $this->afficher_note_lomer($note, 1 /*border*/ , 0 /*br*/ );
          $this->CellFit( $largeur , $hauteur , To::pdf($texte) , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
          // $this->Write($hauteur , To::pdf($this->tab_legende_notes_speciales_texte[$note]) , '');
        }
      }
      $this->legende_initialiser();
      $this->calculer_dimensions_images($memo_lomer_espace_largeur,$memo_lomer_espace_hauteur);
      $this->taille_police = $memo_taille_police;
    }
    //
    // Afficher la légende de l'ancienneté de la notation
    //
    if($type_legende=='anciennete_notation')
    {
      $this->SetFont('Arial' , 'B' , $size);
      $this->Write($hauteur , To::pdf('Ancienneté :') , '');
      $this->SetFont('Arial' , '' , $size);
      $tab_etats = array(
        'blanc'      => "Sur la période.",
        'gris_moyen' => "Début d'année scolaire.",
        'gris_fonce' => "Année scolaire précédente.",
      );
      foreach($tab_etats as $couleur => $texte)
      {
        $this->Write($hauteur , $espace , '');
        $this->choisir_couleur_fond($couleur);
        $this->Cell($case_largeur , $case_hauteur , '' , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
        $this->Write($hauteur , To::pdf($texte) , '');
      }
    }
    //
    // Afficher la légende des scores bilan
    //
    if($type_legende=='score_bilan')
    {
      $this->SetFont('Arial' , 'B' , $size);
      $this->Write($hauteur , To::pdf('États d\'acquisitions :') , '');
      $this->SetFont('Arial' , '' , $size);
      foreach( $this->SESSION['ACQUIS'] as $acquis_id => $tab_acquis_info )
      {
        $texte_seuil = ($this->afficher_score) ? $tab_acquis_info['SEUIL_MIN'].' à '.$tab_acquis_info['SEUIL_MAX'] : '' ;
        $this->Write($hauteur , $espace , '');
        $this->choisir_couleur_fond('A'.$acquis_id.$this->couleur);
        $this->Cell(2*$case_largeur , $case_hauteur , To::pdf($texte_seuil) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
        $this->Write($hauteur , To::pdf($tab_acquis_info['LEGENDE']) , '');
      }
    }
    //
    // Afficher la légende des degrés de maîtrise du socle
    //
    if( ($type_legende=='degre_maitrise') || ($type_legende=='degre_maitrise_points') )
    {
      $this->SetFont('Arial' , 'B' , $size);
      $this->Write($hauteur , To::pdf('Maîtrise :') , ''); // Degrés de maîtrise
      $this->SetFont('Arial' , '' , $size);
      foreach( $this->SESSION['LIVRET'] as $maitrise_id => $tab_maitrise_info )
      {
        $texte_seuil = ($this->afficher_degre) ? $tab_maitrise_info['SEUIL_MIN'].' à '.$tab_maitrise_info['SEUIL_MAX'] : '' ;
        $texte_legende = ($type_legende=='degre_maitrise') ? $tab_maitrise_info['LEGENDE'] : $tab_maitrise_info['LEGENDE'].' ('.$tab_maitrise_info['POINTS'].' pts)' ;
        $texte_legende = ucfirst( str_replace( array('Maîtrise ','maîtrise ') , '' , $texte_legende ) ); // Peut sinon ne pas rentrer sur une ligne
        $this->Write($hauteur , $espace , '');
        $this->choisir_couleur_fond('M'.$maitrise_id.$this->couleur);
        $this->Cell(2*$case_largeur , $case_hauteur , To::pdf($texte_seuil) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
        $this->Write($hauteur , To::pdf($texte_legende) , '');
      }
    }
    //
    // Afficher la légende des états d'acquisition
    //
    if($type_legende=='etat_acquisition')
    {
      $this->SetFont('Arial' , 'B' , $size);
      $this->Write($hauteur , To::pdf('États d\'acquisitions :') , '');
      $this->SetFont('Arial' , '' , $size);
      foreach( $this->SESSION['ACQUIS'] as $acquis_id => $tab_acquis_info )
      {
        $this->Write($hauteur , $espace , '');
        $couleur_fond = (!$force_nb) ? 'A'.$acquis_id.$this->couleur : 'blanc' ;
        $this->choisir_couleur_fond($couleur_fond);
        $this->Cell($case_largeur , $case_hauteur , To::pdf($tab_acquis_info['SIGLE']) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
        $this->Write($hauteur , To::pdf($tab_acquis_info['LEGENDE']) , '');
      }
    }
    //
    $this->SetXY($this->marge_gauche , $ordonnee+$hauteur);
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour changer le pied de page
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function Footer()
  {
    if($this->officiel===NULL)
    {
      return;
    }
    else if($this->officiel===FALSE)
    {
      $initiales = To::texte_identite( $_SESSION['USER_NOM'] , FALSE , $_SESSION['USER_PRENOM'] , TRUE , $_SESSION['USER_GENRE'] );
      $texte = 'Généré le '.date("d/m/Y \à H\hi\m\i\\n").' par '.$initiales.' ('.$_SESSION['USER_PROFIL_NOM_COURT'].') avec SACoche [ '.SERVEUR_PROJET.' ] version '.VERSION_PROG.'.';
      $this->SetXY( 0 , -$this->distance_pied );
      $this->SetFont( 'Arial' , '' , 7 );
      $this->choisir_couleur_fond('gris_clair');
      $this->choisir_couleur_trait('gris_moyen');
      $this->Cell( $this->page_largeur , 3 , To::pdf($texte) , 'TB' /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , $this->fond , SERVEUR_PROJET);
    }
    elseif($this->officiel===TRUE)
    {
      if($this->filigrane)
      {
        $this->SetFont( 'Arial' , 'B' , 72 );
        $this->choisir_couleur_texte('gris_fonce');
        $this->TextWithRotation( $this->page_largeur/6 /*x*/ , $this->page_hauteur*5/6 /*y*/ , "TEST D'IMPRESSION" /*txt*/ , tanh($this->page_hauteur/$this->page_largeur)*180/M_PI /*txt_angle*/ , 0 /*font_angle*/ );
      }
      $this->SetFont( 'Arial' , '' , 4 );
      $this->choisir_couleur_texte('noir');
      $this->SetXY( 0 , -$this->distance_pied );
      $this->Cell( $this->page_largeur - $this->marge_droite , 3 , To::pdf('Suivi d\'Acquisition de Compétences') , 0 /*bordure*/ , 2 /*br*/ , 'R' /*alignement*/ , FALSE /*fond*/ , SERVEUR_PROJET);
      $this->SetXY( 0 , -$this->distance_pied + 1.5 );
      $this->Cell( $this->page_largeur - $this->marge_droite , 3 , To::pdf(SERVEUR_PROJET) , 0 /*bordure*/ , 0 /*br*/ , 'R' /*alignement*/ , FALSE /*fond*/ , SERVEUR_PROJET);
    }
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour remplacer le nombre de pages par la bonne valeur
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function reporter_page_nb()
  {
    $page_nb = $this->page - $this->page_numero_first + 1 ;
    for( $page_numero=$this->page_numero_first ; $page_numero<=$this->page ; $page_numero++ )
    {
      $this->pages[$page_numero] = ($page_numero<$this->page) ? gzcompress(str_replace( $this->page_nombre_alias , $page_nb , gzuncompress($this->pages[$page_numero]) )) : str_replace( $this->page_nombre_alias , $page_nb , $this->pages[$page_numero] ) ;
    }
    return $page_nb;
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour ajouter une page blanche
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function ajouter_page_blanche()
  {
    $this->AddPage($this->orientation , 'A4');
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthodes pour l'en-tête des bilans officiels
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // officiel_bloc_etablissement()
  // officiel_bloc_titres()
  // officiel_ligne_tag()
  // officiel_bloc_adresse_position_libre()
  // officiel_bloc_adresse_position_contrainte_et_pliures()
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function officiel_bloc_etablissement( $tab_etabl_coords , $tab_etabl_logo , $bloc_largeur )
  {
    $nb_etabl_coords = count($tab_etabl_coords);
    $memoX = $this->GetX();
    $memoY = $this->GetY();
    // logo
    if($tab_etabl_logo)
    {
      $img_objet = ($nb_etabl_coords) ? 'logo' : 'logo_seul' ;
      $hauteur_logo_autorisee = ($nb_etabl_coords) ? max( ($nb_etabl_coords) , 6 ) * 8*0.4 : 8 * 8*0.4 ;
      $largeur_logo = $this->afficher_image( $bloc_largeur , $hauteur_logo_autorisee , $tab_etabl_logo , $img_objet );
      $this->SetXY($memoX+$largeur_logo,$memoY);
    }
    else
    {
      $hauteur_logo_autorisee = 0;
      $largeur_logo = 0;
    }
    // texte
    $bloc_hauteur_texte = 0 ;
    if($nb_etabl_coords)
    {
      foreach($tab_etabl_coords as $key => $ligne_etabl)
      {
        $taille_police = ($key=='denomination') ? 11 : 8 ;
        $ligne_hauteur = $taille_police*0.4 ;
        $this->SetFont('Arial' , '' , $taille_police);
        $this->CellFit( $bloc_largeur-$largeur_logo , $ligne_hauteur , To::pdf($ligne_etabl) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
        $bloc_hauteur_texte += $ligne_hauteur ;
      }
    }
    $hauteur_bloc = max($bloc_hauteur_texte,$hauteur_logo_autorisee);
    $this->SetY($memoY+$hauteur_bloc);
    return $hauteur_bloc;
  }

  public function officiel_bloc_titres( $tab_bloc_titres , $bloc_largeur )
  {
    $taille_police = 10 ;
    $ligne_hauteur = $taille_police*0.4 ;
    $bloc_hauteur = 4*$ligne_hauteur ;
    $this->SetFont('Arial' , 'B' , $taille_police);
    $tab_bloc_titres[2] = $this->eleve_nom.' '.$this->eleve_prenom.' ('.$tab_bloc_titres[2].')';
    $this->choisir_couleur_fond('gris_clair');
    $DrawFill = ($this->fond) ? 'DF' : 'D' ;
    $this->Rect( $this->GetX() , $this->GetY() , $bloc_largeur , $bloc_hauteur , $DrawFill );
    $this->SetXY( $this->GetX() , $this->GetY() + 0.5*$ligne_hauteur );
    foreach($tab_bloc_titres as $ligne_titre)
    {
      $this->CellFit( $bloc_largeur , $ligne_hauteur , To::pdf($ligne_titre) , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
    }
    $this->SetXY( $this->GetX() , $this->GetY() + 0.5*$ligne_hauteur );
    return $bloc_hauteur;
  }

  public function officiel_ligne_tag( $eleve_genre , $date_naissance , $eleve_INE , $tag_date_heure_initiales , $ligne_largeur )
  {
    $element_largeur = $ligne_largeur / 3;
    // INE
    if($eleve_INE)
    {
      $taille_police = 8 ;
      $ligne_hauteur = $taille_police*0.4 ;
      $this->SetFont('Arial' , '' , $taille_police);
      $this->Cell( $element_largeur , $ligne_hauteur , To::pdf($eleve_INE) , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    }
    else
    {
      $this->SetX( $this->GetX()+$element_largeur );
    }
    // Date de naissance
    if($date_naissance)
    {
      if($eleve_genre=='M') { $ne_le = 'né le '; } else if($eleve_genre=='F') { $ne_le = 'née le '; } else { $ne_le = 'né(e) le '; }
      $taille_police = 8 ;
      $ligne_hauteur = $taille_police*0.4 ;
      $this->SetFont('Arial' , '' , $taille_police);
      $this->Cell( $element_largeur , $ligne_hauteur , To::pdf($ne_le.$date_naissance) , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
    }
    else
    {
      $this->SetX( $this->GetX()+$element_largeur );
    }
    // Tag date heure initiales
    $taille_police = 5 ;
    $ligne_hauteur = $taille_police*0.4 ;
    $this->SetFont('Arial' , '' , $taille_police);
    $this->Cell( $element_largeur , $ligne_hauteur , To::pdf($tag_date_heure_initiales) , 0 /*bordure*/ , 2 /*br*/ , 'R' /*alignement*/ , FALSE /*fond*/ );
  }

  public function officiel_bloc_adresse_position_libre( $tab_adresse , $bloc_largeur )
  {
    $taille_police = 9 ;
    $ligne_hauteur = $taille_police*0.4 ;
    $this->SetFont('Arial' , '' , $taille_police);
    foreach($tab_adresse as $ligne_adresse)
    {
      $this->CellFit( $bloc_largeur , $ligne_hauteur , To::pdf($ligne_adresse) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    }
    return count($tab_adresse)*$ligne_hauteur;
  }

  public function officiel_bloc_adresse_position_contrainte_et_pliures($tab_adresse)
  {
    // Placer les marques des pliures
    $longueur_tiret = 1; // <= 5
    $this->SetLineWidth(0.1);
    $enveloppe_hauteur = $this->SESSION['ENVELOPPE']['VERTICAL_HAUT'] + $this->SESSION['ENVELOPPE']['VERTICAL_MILIEU'] + $this->SESSION['ENVELOPPE']['VERTICAL_BAS'] ;
    $enveloppe_largeur = $this->SESSION['ENVELOPPE']['HORIZONTAL_GAUCHE'] + $this->SESSION['ENVELOPPE']['HORIZONTAL_MILIEU'] + $this->SESSION['ENVELOPPE']['HORIZONTAL_DROITE'] ;
    $jeu_minimum    = 2 ;
    $jeu_horizontal = $enveloppe_largeur - $this->page_largeur - $jeu_minimum ;
    $jeu_vertical   = $jeu_minimum ;
    $ligne2_y = $this->page_hauteur - $enveloppe_hauteur + $jeu_vertical ;
    $this->Line( $this->marge_gauche-$longueur_tiret , $ligne2_y , $this->marge_gauche , $ligne2_y );
    $this->Line( $this->page_largeur-$this->marge_droite , $ligne2_y , $this->page_largeur-$this->marge_droite+$longueur_tiret , $ligne2_y );
    $ligne1_y = $ligne2_y - $enveloppe_hauteur + $jeu_vertical ;
    $this->Line( $this->marge_gauche-$longueur_tiret , $ligne1_y , $this->marge_gauche , $ligne1_y );
    $this->Line( $this->page_largeur-$this->marge_droite , $ligne1_y , $this->page_largeur-$this->marge_droite+$longueur_tiret , $ligne1_y );
    $jeu_vertical -= 1 ; // Le pliage est manuel donc imparfait et il y a l'épaisseur du papier ;)
    // Déterminer et dessiner l'emplacement du bloc adresse
    $interieur_coin_hg_x = $this->SESSION['ENVELOPPE']['HORIZONTAL_GAUCHE'] ;
    $exterieur_coin_hg_x = $interieur_coin_hg_x - $jeu_horizontal ;
    $interieur_coin_bd_x = $this->page_largeur - $this->SESSION['ENVELOPPE']['HORIZONTAL_DROITE'] ;
    $exterieur_coin_bd_x = $interieur_coin_bd_x + $jeu_horizontal ;
    $interieur_coin_bd_y = $ligne1_y - $this->SESSION['ENVELOPPE']['VERTICAL_BAS'] ;
    $exterieur_coin_bd_y = $interieur_coin_bd_y + $jeu_vertical ;
    $exterieur_coin_hg_y = max( $interieur_coin_bd_y - $this->SESSION['ENVELOPPE']['VERTICAL_MILIEU'] , 5 ) ;
    $interieur_coin_hg_y = $exterieur_coin_hg_y + $jeu_vertical ;
    $exterieur_largeur = $exterieur_coin_bd_x - $exterieur_coin_hg_x ;
    $exterieur_hauteur = $exterieur_coin_bd_y - $exterieur_coin_hg_y ;
    $interieur_largeur = $interieur_coin_bd_x - $interieur_coin_hg_x ;
    $interieur_hauteur = $interieur_coin_bd_y - $interieur_coin_hg_y ;
    $this->choisir_couleur_trait('gris_clair');
    $this->Rect( $exterieur_coin_hg_x , $exterieur_coin_hg_y , $exterieur_largeur , $exterieur_hauteur , 'D' /* DrawFill */ );
    $this->choisir_couleur_trait('gris_moyen');
    $this->Rect( $interieur_coin_hg_x , $interieur_coin_hg_y , $interieur_largeur , $interieur_hauteur , 'D' /* DrawFill */ );
    $this->choisir_couleur_trait('noir');
    // Affiner la position du contenu de l'adresse
    $marge_suppl_x = $interieur_largeur*0.05;
    $marge_suppl_y = $interieur_hauteur*0.05;
    $interieur_largeur_reste = $interieur_largeur*0.8;
    $interieur_hauteur_reste = $interieur_hauteur*0.8;
    $lignes_adresse_nb = count($tab_adresse);
    $ligne_hauteur_reste = min( 4 , $interieur_hauteur_reste/$lignes_adresse_nb );
    $taille_police = $ligne_hauteur_reste*2.5 ;
    $marge_centrage_y = ( $interieur_hauteur_reste - $ligne_hauteur_reste*$lignes_adresse_nb ) / 2 ;
    $this->SetXY( $interieur_coin_hg_x+$marge_suppl_x , $interieur_coin_hg_y+$marge_suppl_y+$marge_centrage_y );
    $this->SetFont('Arial' , '' , $taille_police);
    foreach($tab_adresse as $ligne_adresse)
    {
      $this->CellFit( $interieur_largeur_reste , $ligne_hauteur_reste , To::pdf($ligne_adresse) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    }
    $bloc_hauteur = $exterieur_coin_bd_y - $this->marge_haut ;
    $bloc_gauche_largeur_restante = $exterieur_coin_hg_x - $this->marge_gauche - 2 ;
    return array($bloc_hauteur,$bloc_gauche_largeur_restante) ;
  }

  public function officiel_bloc_appreciation_intermediaire( $tab_saisie , $bloc_largeur , $ligne_hauteur , $bilan_type , $cadre_hauteur=0 )
  {
    // Récupération des données des appréciations
    if($bilan_type!='bulletin')
    {
      $nb_caracteres_maxi = $this->SESSION['OFFICIEL']['RELEVE_APPRECIATION_RUBRIQUE_LONGUEUR'];
      $nb_lignes_appreciation_potentielle_par_prof_hors_intitule = ($nb_caracteres_maxi<250) ? 1 : 2 ;
    }
    else
    {
      $nb_caracteres_maxi = $this->SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE_LONGUEUR'];
      $nb_lignes_appreciation_potentielle_par_prof_hors_intitule = $nb_caracteres_maxi / 100 / 2 ;
    }
    $nb_lignes_prevues = 0;
    $texte = '';
    $nb_saisies = count($tab_saisie);
    $tab_crlf = Clean::tab_crlf();
    foreach($tab_saisie as $prof_id => $tab)
    {
      extract($tab);  // $prof_info $appreciation $note
      if($nb_saisies==1)
      {
        $texte .= $appreciation;
      }
      else
      {
        $nom_auteur = '[ '.$prof_info.' ] '; // associer le nom de l'auteur avec l'appréciation si plusieurs appréciations pour une même rubrique
        $appreciation_sans_br = str_replace( $tab_crlf , ' ' , $appreciation , $nombre_br );
        $texte .= ($nombre_br<4-$nb_saisies) ? $nom_auteur.$appreciation."\n" : $nom_auteur.$appreciation_sans_br."\n" ;
      }
      $nb_lignes_prevues += $nb_lignes_appreciation_potentielle_par_prof_hors_intitule;
    }
    // Intitulé "Appréciations / Conseils :" + auteurs
    $hauteur_ligne_auteurs = $ligne_hauteur*0.8;
    $memoX = $this->GetX();
    $memoY = $this->GetY();
    $this->SetFont('Arial' , 'B' , $this->taille_police);
    $this->Write( $hauteur_ligne_auteurs , To::pdf('Appréciations / Conseils') );
    if($nb_saisies==1) // mettre le nom de l'auteur en tête si plusieurs appréciations pour une même rubrique
    {
      $this->SetFont('Arial' , '' , $this->taille_police);
      $this->Write( $hauteur_ligne_auteurs , To::pdf('   [ '.$prof_info.' ]') );
    }
    $this->SetXY( $memoX , $memoY+$hauteur_ligne_auteurs );
    // cadre appréciations : affichage
    $largeur_autorisee = $bloc_largeur;
    $hauteur_autorisee = ($bilan_type!='bulletin') ? $ligne_hauteur*$nb_lignes_prevues : $cadre_hauteur-$hauteur_ligne_auteurs ;
    $taille_police = $this->taille_police*1.2;
    $taille_interligne = $ligne_hauteur*0.8;
    $this->afficher_appreciation( $largeur_autorisee , $hauteur_autorisee , $taille_police , $taille_interligne , $texte );
  }

  public function officiel_bloc_appreciation_generale( $prof_id , $tab_infos , $tab_image_tampon_signature , $nb_lignes_appreciation_generale_avec_intitule , $bloc_largeur , $ligne_hauteur , $moyenne_generale_eleve , $moyenne_generale_classe )
  {
    $nb_lignes_appreciation_generale_hors_intitule = $nb_lignes_appreciation_generale_avec_intitule - 1 ;
    // Récupération des données de l'appréciation
    extract($tab_infos);  // $prof_info $appreciation $note
    // Intitulé "Appréciation générale"
    $memoX = $this->GetX();
    $memoY = $this->GetY();
    $this->SetFont('Arial' , 'B' , $this->taille_police*1.4);
    $this->Write( $ligne_hauteur , To::pdf('Appréciation générale / Conseils') );
    if($prof_info)
    {
      $this->SetFont('Arial' , '' , $this->taille_police);
      $this->Write( $ligne_hauteur , To::pdf('   [ '.$prof_info.' ]') );
    }
    // Moyenne générale éventuelle (élève & classe)
    $this->SetXY( $memoX , $memoY );
    $largeur = $this->page_largeur_moins_marges ;
    if($moyenne_generale_eleve!==NULL)
    {
      $largeur_note = 10;
      $texte = ($this->SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE']) ? 'Moyenne générale élève (classe) :' : 'Moyenne générale élève :' ;
      $this->SetFont('Arial' , '' , $this->taille_police);
      $largueur_texte = ($this->SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE']) ? $largeur-2*$largeur_note : $largeur-$largeur_note ;
      $this->Cell( $largueur_texte , $ligne_hauteur , To::pdf($texte) , 0 /*bordure*/ , 0 /*br*/ , 'R' /*alignement*/ , FALSE /*fond*/ );
      $moyenne_generale_eleve = ($moyenne_generale_eleve!==NULL) ? ( ($this->SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? number_format($moyenne_generale_eleve,1,',','') : round($moyenne_generale_eleve*5).'%' ) : '-' ;
      $this->SetFont('Arial' , 'B' , $this->taille_police*1.25);
      $this->Cell( $largeur_note , $ligne_hauteur , To::pdf($moyenne_generale_eleve) , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
      if($this->SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE'])
      {
        $moyenne_generale_classe = ($moyenne_generale_classe!==NULL) ? ( ($this->SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? number_format($moyenne_generale_classe,1,',','') : round($moyenne_generale_classe*5).'%' ) : '-' ;
        $this->SetFont('Arial' , '' , $this->taille_police*0.8);
        $this->Cell( $largeur_note , $ligne_hauteur , To::pdf('('.$moyenne_generale_classe.')') , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
      }
    }
    $this->SetXY( $memoX , $memoY+$ligne_hauteur );
    // préparation cadre appréciation
    $largeur_autorisee = $bloc_largeur;
    $hauteur_autorisee = $ligne_hauteur * $nb_lignes_appreciation_generale_hors_intitule;
    $memoX = $this->GetX();
    $memoY = $this->GetY();
    // signature
    $largeur_signature = ($tab_image_tampon_signature) ? $this->afficher_image( $largeur_autorisee , $hauteur_autorisee , $tab_image_tampon_signature , 'signature' /*img_objet*/ ) : min(50,$hauteur_autorisee) ;
    // contour cadre
    $this->SetXY($memoX,$memoY);
    $this->Cell( $largeur_autorisee , $hauteur_autorisee , '' , 1 /*bordure*/ , 2 /*br*/ , '' /*alignement*/ , FALSE /*fond*/ );
    // contenu appréciation
    $this->SetXY($memoX,$memoY);
    if($prof_id)
    {
      $taille_police = $this->taille_police*1.2;
      $taille_interligne = $ligne_hauteur*0.8;
      $this->afficher_appreciation( $largeur_autorisee-$largeur_signature , $hauteur_autorisee , $taille_police , $taille_interligne , $appreciation );
    }
    $this->SetXY($memoX,$memoY+$hauteur_autorisee);
  }

}
?>
