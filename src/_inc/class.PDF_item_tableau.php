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

// Ces méthodes ne concernent que la mise en page d'un tableau de synthèse d'items (grille d'items ou relevé d'items)

class PDF_item_tableau extends PDF
{

  public function initialiser( $eleve_nb , $item_nb , $tableau_synthese_format )
  {
    $hauteur_entete = 10;
    $intitule_facteur  = ($tableau_synthese_format=='eleve') ? 4 : 3 ;
    $etiquette_facteur = ($tableau_synthese_format=='item')  ? 4 : 3 ;
    $colonnes_nb = ($tableau_synthese_format=='eleve') ? $item_nb : $eleve_nb ;
    $lignes_nb   = ($tableau_synthese_format=='item')  ? $item_nb : $eleve_nb ;
    $this->cases_largeur     = ($this->page_largeur_moins_marges - 2) / ($colonnes_nb+2+$intitule_facteur); // -2 pour une petite marge ; 2 colonnes ajoutées + identité/item
    $this->intitule_largeur  = $intitule_facteur  * $this->cases_largeur;
    $this->taille_police     = $this->cases_largeur*0.8;
    $this->taille_police     = min($this->taille_police,10); // pas plus de 10
    $this->taille_police     = max($this->taille_police,5);  // pas moins de 5
    $this->cases_hauteur     = ( $this->page_hauteur_moins_marges - 2 - $hauteur_entete ) / ( $lignes_nb + 2 + $etiquette_facteur + $this->legende ); // -2 pour une petite marge - en-tête ; 2 lignes ajoutées + identité/item + légende
    $this->etiquette_hauteur = $etiquette_facteur * $this->cases_hauteur;
    $this->cases_hauteur     = min($this->cases_hauteur,10); // pas plus de 10
    $this->cases_hauteur     = max($this->cases_hauteur,3);  // pas moins de 3
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->AddPage($this->orientation , 'A4');
    $this->SetAutoPageBreak(TRUE);
  }

  public function entete( $titre_nom , $matiere_et_groupe , $texte_periode )
  {
    $hauteur_entete = 10;
    // Intitulé
    $this->SetFont('Arial' , 'B' , 10);
    $this->SetXY($this->marge_gauche , $this->marge_haut);
    $this->Cell( $this->page_largeur-$this->marge_droite-55 , 4 , To::pdf('Bilan '.$titre_nom) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    $this->Cell( $this->page_largeur-$this->marge_droite-55 , 4 , To::pdf($matiere_et_groupe)  , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    // Synthèse
    $this->SetXY($this->page_largeur-$this->marge_droite-50 , $this->marge_haut);
    $this->Cell( 20 , 4 , To::pdf('SYNTHESE') , 0 /*bordure*/ , 1 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
    // Période
    $this->SetFont('Arial' , '' , 8);
    $this->Cell( $this->page_largeur-$this->marge_gauche-$this->marge_droite , 4 , To::pdf($texte_periode) , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*fond*/ );
    // On se positionne sous l'en-tête
    $this->SetXY($this->marge_gauche , $this->marge_haut+$hauteur_entete);
    $this->SetFont('Arial' , '' , $this->taille_police);
  }

  public function ligne_tete_cellule_debut()
  {
    $this->Cell( $this->intitule_largeur , $this->cases_hauteur , '' , 0 , 0 , 'C' , FALSE /*fond*/ , '' );
    $this->choisir_couleur_fond('gris_clair');
  }

  public function ligne_tete_cellule_corps( $contenu )
  {
      $this->VertCellFit( $this->cases_largeur, $this->etiquette_hauteur, To::pdf($contenu), 1 /*border*/ , 0 /*br*/ , $this->fond );
  }

  public function ligne_tete_cellules_fin()
  {
    $this->SetX( $this->GetX()+2 );
    $this->choisir_couleur_fond('gris_moyen');
    $this->VertCell( $this->cases_largeur , $this->etiquette_hauteur , '[ * ]'  , 1 /*border*/ , 0 /*br*/ , $this->fond );
    $this->VertCell( $this->cases_largeur , $this->etiquette_hauteur , '[ ** ]' , 1 /*border*/ , 1 /*br*/ , $this->fond );
  }

  public function ligne_corps_cellule_debut( $contenu )
  {
    $this->choisir_couleur_fond('gris_clair');
    $this->CellFit( $this->intitule_largeur , $this->cases_hauteur , To::pdf($contenu) , 1 , 0 , 'L' , $this->fond , '' );
  }

  public function ligne_corps_cellules_fin( $moyenne_pourcent , $moyenne_nombre , $last_ligne , $last_colonne )
  {
    // $last_ligne = TRUE si on veut afficher les deux dernières lignes
    // $last_colonne = TRUE si on veut afficher les deux dernières colonnes
    // si $last_ligne = $last_colonne = TRUE alors ce sont les deux dernières cases en diagonale

    // sauter 2mm pour la dernière colonne ; pour la ligne cela a déjà été fait avec l'étiquette de ligne
    if($last_colonne)
    {
      $this->SetX( $this->GetX()+2 );
    }
    // pour la dernière ligne, mais pas pour les 2 dernières cases, mémoriser l'ordonnée pour s'y repositionner à la fin
    elseif($last_ligne)
    {
      $memo_y = $this->GetY();
    }

    // aller vers le bas ou vers la droite après la 1ère case 
    $direction_after_case1 = ($last_ligne) ? 2 : 0;
    // aller à la ligne ou vers la droite après la 2ème case 
    $direction_after_case2 = ($last_colonne) ? 1 : 0;

    // première case
    if($moyenne_pourcent===FALSE)
    {
      $this->choisir_couleur_fond('blanc');
      $this->Cell( $this->cases_largeur , $this->cases_hauteur , '-' , 1 /*bordure*/ , $direction_after_case1 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
    }
    else
    {
      $this->choisir_couleur_fond('A'.OutilBilan::determiner_etat_acquisition($moyenne_pourcent).$this->couleur);
      $score_affiche = $this->afficher_score ? $moyenne_pourcent.'%' : '' ;
      $this->Cell( $this->cases_largeur , $this->cases_hauteur , $score_affiche , 1 /*bordure*/ , $direction_after_case1 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
    }

    // pour les 2 cases en diagonales, une case invisible permet de se positionner correctement
    if($last_colonne && $last_ligne)
    {
      $this->Cell( $this->cases_largeur , $this->cases_hauteur , '' , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
    }

    // deuxième case
    if($moyenne_pourcent===FALSE)
    {
      $this->Cell( $this->cases_largeur , $this->cases_hauteur , '-' , 1 /*bordure*/ , $direction_after_case2 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
    }
    else
    {
      $this->choisir_couleur_fond('A'.OutilBilan::determiner_etat_acquisition($moyenne_nombre).$this->couleur);
      $score_affiche = $this->afficher_score ? $moyenne_nombre.'%' : '' ;
      $this->Cell( $this->cases_largeur , $this->cases_hauteur , $score_affiche , 1 /*bordure*/ , $direction_after_case2 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
    }

    // pour la dernière ligne, mais pas pour les 2 dernières cases, se repositionner à la bonne ordonnée
    if($last_ligne && !$last_colonne)
    {
      $memo_x = $this->GetX();
      $this->SetXY($memo_x , $memo_y);
    }
  }

  public function lignes_pied_cellules_debut( $info_ponderation )
  {
    $memo_y = $this->GetY()+2;
    $this->SetY( $memo_y );
    $this->choisir_couleur_fond('gris_moyen');
    $this->CellFit( $this->intitule_largeur , $this->cases_hauteur , To::pdf('moy. scores '.$info_ponderation.' [*]') , 1 , 2 , 'C' , $this->fond , '' );
    $this->CellFit( $this->intitule_largeur , $this->cases_hauteur , To::pdf('% items acquis [**]'                  ) , 1 , 0 , 'C' , $this->fond , '' );
    $memo_x = $this->GetX();
    $this->SetXY($memo_x,$memo_y);
  }

  public function legende()
  {
    $this->lignes_hauteur = $this->cases_hauteur;
    $ordonnee = $this->page_hauteur - $this->marge_bas - $this->lignes_hauteur*0.75;
    $this->afficher_legende( 'score_bilan' /*type_legende*/ , $ordonnee /*ordonnée*/ );
  }

}
?>