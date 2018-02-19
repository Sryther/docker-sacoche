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

// Ces méthodes ne concernent que la mise en page d'une grille d'items d'un référentiel

class PDF_grille_referentiel extends PDF
{

  private function new_page( $hauteur_dispo_restante )
  {
    if( ($this->legende) && (!$this->legende_deja_affichee) )
    {
      // On n'est pas certain qu'il y ait la place pour la légende en dernière page, alors on la met dès que possible
      $test_place_legende = ($this->lignes_hauteur*$this->legende_nb_lignes*0.9 < $hauteur_dispo_restante) ;
      if( $test_place_legende )
      {
        $this->legende();
        $this->legende_deja_affichee = TRUE;
      }
    }
    $this->AddPage($this->orientation , 'A4');
  }

  public function initialiser( $cases_nb , $cases_largeur , $lignes_nb , $longueur_ref_max , $colonne_bilan , $colonne_vide , $aff_anciennete_notation , $aff_etat_acquisition , $pages_nb_methode )
  {
    // On calcule la hauteur de la ligne et la taille de la police pour tout faire rentrer sur une page si possible, un minimum de pages sinon
    $hauteur_dispo_par_page  = $this->page_hauteur_moins_marges ;
    $this->legende_nb_lignes = 1 + (int)$aff_anciennete_notation + (int)$aff_etat_acquisition ;
    $lignes_nb = 1 + 1 + 1 + $lignes_nb + ($this->legende*$this->legende_nb_lignes+0.25) ; // intitulé-structure + matière-niveau-élève + marge (1 & un peu plus car aussi avant domaines) + lignes (domaines+thèmes+items) + légende
    $hauteur_ligne_minimale = ($pages_nb_methode=='optimise') ? 3.5 : 6 ;
    $hauteur_ligne_maximale = ($pages_nb_methode=='optimise') ? 5   : 9 ;
    $nb_pages = 0;
    do
    {
      $nb_pages++;
      $hauteur_ligne_calcule = $nb_pages*$hauteur_dispo_par_page / $lignes_nb ;
    }
    while($hauteur_ligne_calcule < $hauteur_ligne_minimale);
    if($nb_pages>1)
    {
      $coef_retrait = ($pages_nb_methode=='optimise') ? 0.1 : 0.2 ;
      $hauteur_ligne_calcule -= $nb_pages*$coef_retrait; // Tenter de contrebalancer un peu le pb des thèmes non coupés
    }
    $this->lignes_hauteur = Math::floorTo( $hauteur_ligne_calcule , 0.1 ) ; // valeur approchée au dixième près par défaut
    $this->lignes_hauteur = min ( $this->lignes_hauteur , $hauteur_ligne_maximale ) ;
    $this->taille_police  = $this->lignes_hauteur * 1.6 ; // 5mm de hauteur par ligne donne une taille de 8
    $this->taille_police  = min ( $this->taille_police , 10 ) ;
    // La suite est classique
    $this->cases_nb          = $cases_nb;
    $this->cases_largeur     = $cases_largeur;
    $this->cases_hauteur     = $this->lignes_hauteur;
    $this->colonne_bilan_largeur = ($colonne_bilan=='non') ? 0 : $cases_largeur ;
    $this->colonne_vide_largeur  = $colonne_vide;
    $this->reference_largeur = ($longueur_ref_max) ? ceil($longueur_ref_max*1.7) : 0 ;
    $this->intitule_largeur  = $this->page_largeur_moins_marges - $this->reference_largeur - ($this->cases_nb * $this->cases_largeur) - $this->colonne_bilan_largeur - $this->colonne_vide_largeur ;
    $this->aff_codes_notation      = TRUE;
    $this->aff_anciennete_notation = $aff_anciennete_notation;
    $this->aff_etat_acquisition    = $aff_etat_acquisition;
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->SetAutoPageBreak(FALSE);
    $this->calculer_dimensions_images($this->cases_largeur,$this->cases_hauteur);
  }

  public function entete( $bilan_titre , $matiere_nom , $niveau_nom , $eleve_id , $eleve_nom , $eleve_prenom )
  {
    // On prend une nouvelle page PDF pour chaque élève
    $this->AddPage($this->orientation , 'A4');
    $this->SetXY( $this->marge_gauche , $this->marge_haut );
    $this->legende_deja_affichee = FALSE; // On n'est pas certain qu'il y ait la place pour la légende en dernière page, alors on la met dès que possible
    $largeur_demi_page = ( $this->page_largeur_moins_marges ) / 2;
    // intitulé-structure
    $this->SetFont('Arial' , 'B' , $this->taille_police*1.4);
    $this->Cell($largeur_demi_page , $this->lignes_hauteur , To::pdf($bilan_titre)                                 , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    $this->Cell($largeur_demi_page , $this->lignes_hauteur , To::pdf($this->SESSION['ETABLISSEMENT_DENOMINATION']) , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*fond*/ );
    // matière-niveau-élève
    $this->CellFit($largeur_demi_page , $this->lignes_hauteur , To::pdf($matiere_nom.' - Niveau '.$niveau_nom) , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    if($eleve_id)
    {
      $this->CellFit($largeur_demi_page , $this->lignes_hauteur , To::pdf($eleve_nom.' '.$eleve_prenom) , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*fond*/ );
    }
    else
    {
      $this->choisir_couleur_trait('gris_moyen');
      $this->SetLineWidth(0.1);
      $this->Line($this->page_largeur-$this->marge_droite-75 , $this->marge_haut+2*$this->lignes_hauteur , $this->page_largeur-$this->marge_droite , $this->marge_haut+2*$this->lignes_hauteur);
      $this->choisir_couleur_trait('noir');
    }
    $this->SetXY( $this->marge_gauche , $this->marge_haut + 2.5*$this->lignes_hauteur );
  }

  public function domaine( $domaine_nom , $domaine_nb_lignes )
  {
    $hauteur_requise = $this->cases_hauteur * $domaine_nb_lignes;
    $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
    if($hauteur_requise > $hauteur_restante)
    {
      // Prendre une nouvelle page après avoir éventuellement affiché la légende
      $this->new_page( $hauteur_restante );
    }
    $this->SetFont('Arial' , 'B' , $this->taille_police*1.25);
    $this->SetXY( $this->reference_largeur + $this->marge_gauche , $this->GetY()+1 );
    $this->CellFit( $this->intitule_largeur , $this->cases_hauteur , To::pdf($domaine_nom) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
  }

  public function theme( $theme_ref , $theme_nom , $theme_nb_lignes )
  {
    $hauteur_requise = $this->cases_hauteur * $theme_nb_lignes;
    $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
    if($hauteur_requise > $hauteur_restante)
    {
      // Prendre une nouvelle page après avoir éventuellement affiché la légende
      $this->new_page( $hauteur_restante );
    }
    $this->SetFont('Arial' , 'B' , $this->taille_police);
    $this->choisir_couleur_fond('gris_moyen');
    if($this->reference_largeur)
    {
      $this->CellFit( $this->reference_largeur , $this->cases_hauteur , To::pdf($theme_ref) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , $this->fond );
    }
    $this->CellFit( $this->intitule_largeur , $this->cases_hauteur , To::pdf($theme_nom) , 1 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , $this->fond );
    if($this->colonne_vide_largeur)
    {
      // Ajouter une case vide sur la hauteur du nombre d'items du thème
      $abscisse = $this->GetX();
      $ordonnee = $this->GetY();
      $this->SetXY( $this->page_largeur - $this->marge_droite - $this->colonne_vide_largeur , $ordonnee );
      $this->CellFit( $this->colonne_vide_largeur , $this->cases_hauteur * ($theme_nb_lignes-1) , '' , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
      $this->SetXY( $abscisse , $ordonnee );
    }
    $this->SetFont('Arial' , '' , $this->taille_police);
  }

  public function item( $item_ref , $item_texte , $colspan_nb )
  {
    $br = ($colspan_nb) ? 0 : 1 ;
    $this->choisir_couleur_fond('gris_clair');
    if($this->reference_largeur)
    {
      $this->CellFit( $this->reference_largeur , $this->cases_hauteur , To::pdf($item_ref) , 1 /*bordure*/ ,   0 /*br*/ , 'C' /*alignement*/ , $this->fond );
    }
    $this->CellFit( $this->intitule_largeur , $this->cases_hauteur , To::pdf($item_texte) , 1 /*bordure*/ , $br /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    $this->choisir_couleur_fond('blanc');
  }

  public function legende()
  {
    if(!$this->legende_deja_affichee)
    {
      $ordonnee = $this->page_hauteur - $this->marge_bas - $this->lignes_hauteur*$this->legende_nb_lignes*0.9 ;
      if($this->aff_codes_notation)      { $this->afficher_legende( 'codes_notation'      /*type_legende*/ , $ordonnee     /*ordonnée*/ ); }
      if($this->aff_anciennete_notation) { $this->afficher_legende( 'anciennete_notation' /*type_legende*/ , $this->GetY() /*ordonnée*/ ); }
      if($this->aff_etat_acquisition)    { $this->afficher_legende( 'score_bilan'         /*type_legende*/ , $this->GetY() /*ordonnée*/ ); }
    }
  }

}
?>