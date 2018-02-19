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

// Ces méthodes ne concernent que la mise en page d'un tableau de saisie d'évaluation ou de répartition quantitative / nominative

class PDF_evaluation_tableau extends PDF
{

  private function saisie_reference_devoir( $groupe_nom , $date_fr , $description )
  {
    $hauteur_tiers = $this->etiquette_hauteur / 3 ;
    $this->SetXY( $this->marge_gauche , $this->marge_haut );
    $this->SetFont('Arial' , 'B' , $this->taille_police);
    $this->CellFit( $this->reference_largeur , $hauteur_tiers , To::pdf($groupe_nom)  , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
    $this->CellFit( $this->reference_largeur , $hauteur_tiers , To::pdf($date_fr)     , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
    $this->CellFit( $this->reference_largeur , $hauteur_tiers , To::pdf($description) , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
    $this->SetXY( $this->marge_gauche , $this->marge_haut );
    $this->SetFont('Arial' , '' , $this->taille_police);
    $this->Cell( $this->reference_largeur , $this->etiquette_hauteur , '' , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
  }

  private function saisie_reference_eleve($texte)
  {
    $this->choisir_couleur_fond('gris_clair');
    $this->VertCellFit( $this->cases_largeur, $this->etiquette_hauteur, To::pdf($texte), 1 /*border*/ , 0 /*ln*/ , $this->fond );
  }

  public function saisie_initialiser( $eleve_nb , $item_nb )
  {
    $reference_largeur_minimum = 50;
    $cases_hauteur_maximum     = 25;
    $this->cases_largeur     = 10; // valeur par défaut ; diminuée si pas assez de place pour la référence de l'item
    $this->etiquette_hauteur = 40; // valeur fixe
    $this->reference_largeur = $this->page_largeur_moins_marges - ($eleve_nb * $this->cases_largeur);
    if($this->reference_largeur < $reference_largeur_minimum)
    {
      $this->reference_largeur = $reference_largeur_minimum;
      $this->cases_largeur     = ($this->page_largeur_moins_marges - $this->reference_largeur) / $eleve_nb;
    }
    $this->cases_hauteur     = ($this->page_hauteur_moins_marges - $this->etiquette_hauteur) / $item_nb;
    $this->cases_hauteur     = min( $this->cases_hauteur , $cases_hauteur_maximum );
    $this->SetMargins( $this->marge_gauche , $this->marge_haut , $this->marge_droite );
    $this->AddPage($this->orientation , 'A4');
    $this->SetAutoPageBreak(TRUE);
    $this->calculer_dimensions_images( $this->cases_largeur , $this->cases_hauteur );
  }

  public function saisie_entete( $groupe_nom , $date_fr , $description , $DB_TAB_USER )
  {
    $this->saisie_reference_devoir( $groupe_nom , $date_fr , $description );
    foreach($DB_TAB_USER as $DB_ROW)
    {
      $this->saisie_reference_eleve($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']);
    }
    $this->SetXY( $this->marge_gauche , $this->marge_haut+$this->etiquette_hauteur );
  }

  // public function saisie_reference_item( $item_intro , $item_nom )
  // {
    // $memo_x = $this->GetX();
    // $memo_y = $this->GetY();
    // $this->choisir_couleur_fond('gris_clair');
    // $this->Cell( $this->reference_largeur , $this->cases_hauteur , '' , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , $this->fond );
    // $this->SetXY( $memo_x , $memo_y+1 );
    // $this->SetFont('Arial' , 'B' , $this->taille_police);
    // $this->CellFit( $this->reference_largeur , $this->cases_hauteur/3 , To::pdf($item_intro) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    // $this->SetFont('Arial' , '' , $this->taille_police);
    // $this->CellFit( $this->reference_largeur , $this->cases_hauteur*2/3 , To::pdf($item_nom) , 0 /*bordure*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    // $this->SetXY( $memo_x+$this->reference_largeur , $memo_y );
  // }

  public function saisie_reference_item( $item_intro , $item_nom , $fusion_lignes )
  {
    $memo_x = $this->GetX();
    $memo_y = $this->GetY();
    $this->choisir_couleur_fond('gris_clair');
    $this->Cell( $this->reference_largeur , $this->cases_hauteur , '' , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , $this->fond );
    $this->SetXY($memo_x , $memo_y);
    $separateur = ($fusion_lignes) ? ' ' : "\r\n" ;
    $this->afficher_appreciation( $this->reference_largeur , $this->cases_hauteur , $this->taille_police /*taille_police*/ , $this->taille_police/2 /*taille_interligne*/ , $item_intro.$separateur.$item_nom );
    $this->SetXY($memo_x + $this->reference_largeur , $memo_y);
    // $this->CellFit( $this->reference_largeur , $this->cases_hauteur , To::pdf($item_intro.' '.$item_nom) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , $this->fond );
  }

  public function saisie_cases_eleves( $DB_TAB_COMP , $DB_TAB_USER , $eleve_nb , $tab_scores , $cart_detail , $with_ref , $with_coef , $with_socle )
  {
    $fusion_lignes = ( ($cart_detail=='minimal') || (!$with_ref) || ($this->cases_hauteur<15) ) ? TRUE : FALSE ;
    foreach($DB_TAB_COMP as $DB_ROW_COMP)
    {
      $item_ref    = (!$with_ref  ) ? '' : $DB_ROW_COMP['matiere_ref'].'.'.$DB_ROW_COMP['item_ref'];
      $texte_s2016 = (!$with_socle) ? '' : ( ($DB_ROW_COMP['s2016_nb'])  ? ' [S]' : ' [–]' );
      $texte_coef  = (!$with_coef ) ? '' : ' ['.$DB_ROW_COMP['item_coef'].']' ;
      $item_nom    = ($cart_detail=='minimal') ? '' : $DB_ROW_COMP['item_nom'];
      $this->saisie_reference_item( $item_ref.$texte_s2016.$texte_coef , $item_nom , $fusion_lignes );
      if($tab_scores)
      {
        foreach($DB_TAB_USER as $DB_ROW_USER)
        {
          $this->afficher_note_lomer( $tab_scores[$DB_ROW_COMP['item_id']][$DB_ROW_USER['user_id']] , $border=1 , $br=0 );
        }
      }
      else
      {
        for($i=0 ; $i<$eleve_nb ; $i++)
        {
          $this->Cell( $this->cases_largeur , $this->cases_hauteur , '' , 1 , 0 , 'C' , FALSE /*fond*/ , '' );
        }
      }
      $this->SetXY( $this->marge_gauche , $this->GetY()+$this->cases_hauteur );
    }
  }

  public function repartition_quantitative_initialiser($item_nb)
  {
    $cases_hauteur_maximum   = 20;
    $cases_nombre = ($this->SESSION['NOMBRE_CODES_NOTATION']+1);
    $this->cases_largeur     = 30 - ($cases_nombre * 2.5) ;
    $this->reference_largeur = $this->page_largeur_moins_marges - ( $cases_nombre * $this->cases_largeur);
    $this->etiquette_hauteur = 10; // valeur fixe
    $this->cases_hauteur     = ($this->page_hauteur_moins_marges - $this->etiquette_hauteur) / $item_nb;
    $this->cases_hauteur     = min( $this->cases_hauteur , $cases_hauteur_maximum );
    $this->SetMargins( $this->marge_gauche , $this->marge_haut , $this->marge_droite );
    $this->AddPage($this->orientation , 'A4');
    $this->SetAutoPageBreak(TRUE);
    $this->calculer_dimensions_images( $this->cases_largeur , $this->etiquette_hauteur );
  }

  public function repartition_quantitative_entete( $groupe_nom , $date_fr , $description , $tab_init_quantitatif )
  {
    $this->saisie_reference_devoir($groupe_nom,$date_fr,$description);
    $this->SetXY( $this->marge_gauche+$this->reference_largeur , $this->marge_haut );
    foreach($tab_init_quantitatif as $note=>$vide)
    {
      if($note!='X')
      {
        $this->afficher_note_lomer( $note , 1 /*border*/ , 0 /*br*/ );
      }
      else
      {
        $this->CellFit( $this->cases_largeur , $this->etiquette_hauteur , To::pdf('Autre') , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
      }
    }
    $this->SetXY( $this->marge_gauche , $this->marge_haut+$this->etiquette_hauteur );
  }

  public function repartition_quantitative_cases_eleves( $tab_repartition_quantitatif_item , $eleve_nb )
  {
    foreach($tab_repartition_quantitatif_item as $code=>$note_nb)
    {
      $coefficient = $note_nb/$eleve_nb ;
      // Tracer un rectangle coloré d'aire et d'intensité de niveau de gris proportionnels
      $teinte_gris = 255-128*$coefficient ;
      $this->SetFillColor($teinte_gris,$teinte_gris,$teinte_gris);
      $memo_X = $this->GetX();
      $memo_Y = $this->GetY();
      $rect_largeur = $this->cases_largeur * sqrt( $coefficient ) ;
      $rect_hauteur = $this->cases_hauteur * sqrt( $coefficient ) ;
      $pos_X = $memo_X + ($this->cases_largeur - $rect_largeur) / 2 ;
      $pos_Y = $memo_Y + ($this->cases_hauteur - $rect_hauteur) / 2 ;
      $this->SetXY($pos_X , $pos_Y);
      $this->Cell( $rect_largeur , $rect_hauteur , '' , 0 , 0 , 'C' , TRUE /*fond*/ , '' );
      // Écrire le %
      $this->SetXY( $memo_X , $memo_Y );
      $this->SetFont('Arial' , '' , $this->taille_police*(1+$coefficient));
      $this->Cell( $this->cases_largeur , $this->cases_hauteur , To::pdf(round(100*$coefficient).'%') , 1 , 0 , 'C' , FALSE , '' );
    }
    $this->SetXY( $this->marge_gauche , $this->GetY()+$this->cases_hauteur );
  }

  public function repartition_nominative_initialiser($lignes_nb)
  {
    $cases_nombre = ($this->SESSION['NOMBRE_CODES_NOTATION']+1);
    $this->cases_largeur     = 60 - ($cases_nombre * 5) ;
    $this->reference_largeur = $this->page_largeur_moins_marges - ( $cases_nombre * $this->cases_largeur);
    $this->etiquette_hauteur = 10; // valeur fixe
    $lignes_hauteur_maximum  = 5;
    $this->lignes_hauteur    = ($this->page_hauteur_moins_marges - $this->etiquette_hauteur) / $lignes_nb;
    $this->lignes_hauteur    = min($this->lignes_hauteur,$lignes_hauteur_maximum);
    $this->lignes_hauteur    = max($this->cases_hauteur,3.5); // pas moins de 3,5
    $this->SetMargins( $this->marge_gauche , $this->marge_haut , $this->marge_droite );
    $this->AddPage($this->orientation , 'A4');
    $this->SetAutoPageBreak(FALSE);
    $this->calculer_dimensions_images( $this->cases_largeur , $this->etiquette_hauteur );
  }

  public function repartition_nominative_entete( $groupe_nom , $date_fr , $description , $tab_init_quantitatif , $tab_repartition_quantitatif )
  {
    // on calcule la hauteur de la case
    $this->cases_hauteur = $this->lignes_hauteur * max( 4 , max($tab_repartition_quantitatif) );
    // On prend une nouvelle page PDF si besoin et y remettre la ligne d'en-tête si y a pas assez de place
    if($this->GetY()>$this->marge_haut)
    {
      // on regarde s'il y a la place
      $hauteur_requise = $this->cases_hauteur;
      $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
      if($hauteur_requise > $hauteur_restante)
      {
        $this->AddPage($this->orientation , 'A4');
      }
    }
    // 1ère ligne : référence des codes
    if($this->GetY()==$this->marge_haut)
    {
      $this->saisie_reference_devoir( $groupe_nom , $date_fr , $description );
      $this->SetXY( $this->marge_gauche+$this->reference_largeur , $this->marge_haut );
      foreach($tab_init_quantitatif as $note=>$vide)
      {
        if($note!='X')
        {
          $this->afficher_note_lomer( $note , 1 /*border*/ , 0 /*br*/ );
        }
        else
        {
          $this->CellFit( $this->cases_largeur , $this->etiquette_hauteur , To::pdf('Autre') , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
        }
      }
      $this->SetXY( $this->marge_gauche , $this->marge_haut+$this->etiquette_hauteur );
    }
  }

  public function repartition_nominative_cases_eleves( $tab_repartition_nominatif_item )
  {
    foreach($tab_repartition_nominatif_item as $code=>$tab_eleves)
    {
      // Ecrire les noms ; plus court avec MultiCell() mais pb des retours à la ligne pour les noms trop longs
      $memo_X = $this->GetX();
      $memo_Y = $this->GetY();
      foreach($tab_eleves as $key => $eleve_texte)
      {
        $this->CellFit( $this->cases_largeur , $this->lignes_hauteur , To::pdf($eleve_texte) , 0 , 2 , 'L' , FALSE /*fond*/ , '' );
      }
      // Ajouter la bordure
      $this->SetXY($memo_X , $memo_Y);
      $this->Cell( $this->cases_largeur , $this->cases_hauteur , '' , 1 , 0 , 'C' , FALSE /*fond*/ , '' );
    }
    $this->SetXY( $this->marge_gauche , $this->GetY()+$this->cases_hauteur );
  }

}
?>