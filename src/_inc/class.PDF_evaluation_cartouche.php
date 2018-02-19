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

// Ces méthodes ne concernent que la mise en page d'un cartouche

class PDF_evaluation_cartouche extends PDF
{

  public function initialiser( $detail , $longueur_ref_max , $item_nb , $cases_nb )
  {
    $colonne_codes = ($cases_nb==1) ? 0 : 15 ;
    $this->cases_largeur     = ($detail=='minimal') ? ($this->page_largeur_moins_marges - $colonne_codes) / $item_nb : 10 ;
    $this->cases_hauteur     = 5 ;
    $this->cases_nb          = $cases_nb ;
    $this->reference_largeur = ($longueur_ref_max) ? ceil($longueur_ref_max*1.7) : 0 ;
    $this->intitule_largeur  = ($detail=='minimal') ? 0 : $this->page_largeur_moins_marges - $this->reference_largeur - ($this->cases_largeur*$this->cases_nb) ;
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->AddPage($this->orientation , 'A4');
    $this->SetAutoPageBreak(FALSE);
    $largeur = ( ($detail=='complet') || ($cases_nb==1) ) ? $this->cases_largeur : 15 ;
    $this->calculer_dimensions_images( $largeur , $this->cases_hauteur );
  }

  public function entete( $texte_entete , $lignes_nb , $detail , $cases_nb )
  {
    // On prend une nouvelle page PDF si besoin
    $hauteur_requise = $this->cases_hauteur * $lignes_nb;
    $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
    if($hauteur_requise > $hauteur_restante)
    {
      $this->AddPage($this->orientation , 'A4');
    }
    $this->SetFont('Arial' , '' , 10);
    $this->choisir_couleur_fond('gris_clair');
    // Intitulé
    if($cases_nb==1)
    {
      // Avec une case à remplir
      $this->SetX($this->marge_gauche);
      $this->CellFit( $this->page_largeur_moins_marges , $this->cases_hauteur , To::pdf($texte_entete) , 1 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , $this->fond );
    }
    else
    {
      // Avec ( $this->SESSION['NOMBRE_CODES_NOTATION'] + 1 ) cases dont une à cocher
      $tab_codes = array_fill_keys($this->SESSION['NOTE_ACTIF'],TRUE) + array('X'=>FALSE);
      if($detail=='minimal')
      {
        // ... dans le cas d'un cartouche minimal
        $this->SetX( $this->marge_gauche + 15 );
        $this->CellFit( $this->page_largeur_moins_marges - 15 , $this->cases_hauteur , To::pdf($texte_entete) , 1 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , $this->fond );
        $memo_x = $this->GetX();
        $memo_y = $this->GetY();
        $this->SetXY( $this->marge_gauche , $memo_y + $this->cases_hauteur );
        foreach($tab_codes as $note_code => $is_note )
        {
          if($is_note)
          {
            $this->afficher_note_lomer( $note_code , 1 /*border*/ , 2 /*br*/ );
          }
          else
          {
            $this->CellFit( 15 , $this->cases_hauteur , To::pdf('Autre') , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
          }
        }
        $this->SetXY($memo_x , $memo_y);
      }
      else
      {
        // ... dans le cas d'un cartouche complet
        $this->SetX($this->marge_gauche);
        $this->CellFit( $this->reference_largeur + $this->intitule_largeur , $this->cases_hauteur , To::pdf($texte_entete) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , $this->fond );
        foreach($tab_codes as $note_code => $is_note )
        {
          if($is_note)
          {
            $this->afficher_note_lomer( $note_code , 1 /*border*/ , 0 /*br*/ );
          }
          else
          {
            $this->CellFit( $this->cases_largeur , $this->cases_hauteur , To::pdf('Autre') , 1 /*bordure*/ , 1 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
          }
        }
      }
    }
    $this->choisir_couleur_fond('gris_moyen');
    $this->SetFont('Arial' , '' , 8);
  }

  public function minimal_competence( $item_ref , $note , $cases_nb )
  {
    $memo_x = $this->GetX();
    $memo_y = $this->GetY();
    if($this->cases_largeur>30)
    {
      $this->CellFit( $this->cases_largeur , $this->cases_hauteur , To::pdf($item_ref) , 1 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
    }
    else
    {
      list($ref_matiere,$ref_suite) = explode('.',$item_ref,2);
      $this->SetFont('Arial' , '' , 7);
      $this->CellFit( $this->cases_largeur , $this->cases_hauteur/2 , To::pdf($ref_matiere) , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
      $this->CellFit( $this->cases_largeur , $this->cases_hauteur/2 , To::pdf($ref_suite)   , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
      $this->SetFont('Arial' , '' , 8);
      $this->SetXY($memo_x , $memo_y);
      $this->Cell( $this->cases_largeur , $this->cases_hauteur , '' , 1 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
    }
    if($cases_nb==1)
    {
      // Avec une case à remplir
      $this->afficher_note_lomer( $note , 1 /*border*/ , 0 /*br*/ );
    }
    else
    {
      // Avec ( $this->SESSION['NOMBRE_CODES_NOTATION'] + 1 ) cases dont une à cocher
      $tab_codes = array_fill_keys($this->SESSION['NOTE_ACTIF'],TRUE) + array('X'=>FALSE);
      foreach($tab_codes as $note_code => $is_note )
      {
        if($is_note)
        {
          $coche = ($note_code==$note) ? $note : '' ;
          $fill  = ($note_code==$note) ? TRUE  : FALSE ;
        }
        else
        {
          $coche = ( $note && !isset($tab_codes[$note]) ) ? 'X'  : '' ;
          $fill  = ( $note && !isset($tab_codes[$note]) ) ? TRUE : FALSE ;
        }
        $this->CellFit( $this->cases_largeur , $this->cases_hauteur , To::pdf($coche) , 1 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , $fill /*fond*/ );
      }
    }
    $this->SetXY( $memo_x + $this->cases_largeur , $memo_y );
  }

  public function complet_competence( $item_ref , $item_intitule , $note , $cases_nb )
  {
    if($this->reference_largeur)
    {
      $memo_x = $this->GetX();
      $memo_y = $this->GetY();
      list($ref_matiere,$ref_suite) = explode('.',$item_ref,2);
      $this->SetFont('Arial' , '' , 7);
      $this->CellFit( $this->reference_largeur , $this->cases_hauteur/2 , To::pdf($ref_matiere) , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
      $this->CellFit( $this->reference_largeur , $this->cases_hauteur/2 , To::pdf($ref_suite)   , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
      $this->SetFont('Arial' , '' , 8);
      $this->SetXY($memo_x , $memo_y);
      $this->Cell( $this->reference_largeur , $this->cases_hauteur , ''                         , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
    }
    $this->CellFit( $this->intitule_largeur  , $this->cases_hauteur , To::pdf($item_intitule) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    if($cases_nb==1)
    {
      // Avec une case à remplir
      $this->afficher_note_lomer( $note , 1 /*border*/ , 1 /*br*/ );
    }
    else
    {
      // Avec ( $this->SESSION['NOMBRE_CODES_NOTATION'] + 1 ) cases dont une à cocher
      $tab_codes = array_fill_keys($this->SESSION['NOTE_ACTIF'],TRUE) + array('X'=>FALSE);
      foreach($tab_codes as $note_code => $is_note )
      {
        if($is_note)
        {
          $coche = ($note_code==$note) ? 'X'  : '' ;
          $fill  = ($note_code==$note) ? TRUE : FALSE ;
          $br = 0;
        }
        else
        {
          $coche = ( $note && !isset($tab_codes[$note]) ) ? $note : '' ;
          $fill  = ( $note && !isset($tab_codes[$note]) ) ? TRUE  : FALSE ;
          $br = 1;
        }
        $this->CellFit( $this->cases_largeur , $this->cases_hauteur , To::pdf($coche) , 1 /*bordure*/ , $br /*br*/ , 'C' /*alignement*/ , $fill /*fond*/ );
      }
    }
  }

  public function commentaire_interligne( $decalage_nb_lignes , $commentaire , $commentaire_nb_lignes , $lignes_vide_nb )
  {
    if($decalage_nb_lignes)
    {
      $this->SetXY($this->marge_gauche , $this->GetY() + $decalage_nb_lignes*$this->cases_hauteur);
    }
    if($commentaire)
    {
      // cadre
      $memo_x = $this->GetX();
      $memo_y = $this->GetY();
      $this->choisir_couleur_fond('gris_clair');
      $this->Cell( $this->page_largeur_moins_marges , $commentaire_nb_lignes*$this->cases_hauteur , '' , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , $this->fond );
      $this->SetXY($memo_x , $memo_y);
      $this->SetFont('Arial' , '' , 9);
      $this->afficher_appreciation( $this->page_largeur_moins_marges , $commentaire_nb_lignes*$this->cases_hauteur , 8 /*taille_police*/ , 4 /*taille_interligne*/ , $commentaire );
    }
    // positionnement, dont marge
    $this->SetXY($this->marge_gauche , $this->GetY() + (2+$lignes_vide_nb)*$this->cases_hauteur);
    // trait de séparation
    $this->choisir_couleur_trait('gris_moyen');
    $this->SetLineWidth(0.1);
    $this->SetDash(1,2); // active le pointillé
    $this->Line( 4 , $this->GetY()-$this->cases_hauteur , $this->page_largeur - 4 , $this->GetY()-$this->cases_hauteur );
    $this->SetDash(); //enlève le pointillé
    $this->choisir_couleur_trait('noir');
  }

}
?>