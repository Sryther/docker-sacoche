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
 
// Extension de classe qui étend PDF

// Ces méthodes ne concernent que la mise en page d'un trombinoscope

class PDF_trombinoscope extends PDF
{

  public function initialiser($regroupement)
  {
    $this->taille_police = 10;
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->AddPage($this->orientation , 'A4');
    $this->SetAutoPageBreak(TRUE);
    // Titre
    $this->SetFont('Arial' , 'B' , $this->taille_police*1.2);
    $this->CellFit( $this->page_largeur_moins_marges , 7 , To::pdf($regroupement)  , 0 /*bordure*/ , 1 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    // Avertissement
    $this->SetFont('Arial' , '' , $this->taille_police*0.9);
    $this->SetX( $this->GetX() + 5 );
    $message = 'Le trombinoscope est privé (accessible aux seuls personnels habilités) et réservé à un usage pédagogique interne (il ne doit pas être transmis à un tiers), ceci quel que soit son support (numérique ou imprimé). Pour davantage d\'informations relatives au respect de la vie privée et au droit à l\'image, consulter la documentation correspondante (intégrée à l\'application et disponible sur internet).';
    $this->MultiCell( $this->page_largeur_moins_marges - 2*5 , 4 , To::pdf($message)  , 1 /*bordure*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    // Next...
    $this->SetY( $this->GetY() + 2 );
    $this->SetFont('Arial' , '' , $this->taille_police);
    $this->photo_hauteur_maxi   = PHOTO_DIMENSION_MAXI*$this->coef_conv_pixel_to_mm;
    $this->cadre_photo_hauteur  = $this->photo_hauteur_maxi + 0.5 + 8 ; // 0.5 marge + 2x4mm pour lignes nom et prénom
  }

  public function vignette($tab_vignette)
  {
    $espacement_x = 2;
    $espacement_y = 2;
    // On récupère les infos
    extract($tab_vignette); // $user_nom $user_prenom $img_width $img_height $img_src $img_title
    $img_width  *= $this->coef_conv_pixel_to_mm;
    $img_height *= $this->coef_conv_pixel_to_mm;
    // retour à la ligne si manque de place
    if( $this->GetX() + $img_width + $this->marge_droite > $this->page_largeur )
    {
      $this->SetXY( $this->marge_gauche , $this->GetY() + $this->cadre_photo_hauteur + $espacement_y );
      // saut de page si manque de place
      if( $this->GetY() + $this->cadre_photo_hauteur + $this->marge_bas > $this->page_hauteur )
      {
        $this->AddPage($this->orientation , 'A4');
      }
    }
    // image
    $memo_x = $this->GetX();
    $memo_y = $this->GetY();
    if($img_src)
    {
      $this->MemImage(base64_decode($img_src),$memo_x,$memo_y,$img_width,$img_height,'JPEG');
    }
    else
    {
      $this->Image('./_img/trombinoscope_vide.png',$memo_x,$memo_y,$img_width,$img_height,'PNG');
    }
    // nom & prénom
    $this->SetXY( $memo_x , $memo_y + $this->photo_hauteur_maxi + 0.5 );
    $this->CellFit( $img_width , 4 , To::pdf($user_nom)    , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    $this->CellFit( $img_width , 4 , To::pdf($user_prenom) , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    // positionnement pour la photo suivante
    $this->SetXY( $memo_x + $img_width + $espacement_x , $memo_y );
  }

}
?>