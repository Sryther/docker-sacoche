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

// Ces méthodes ne concernent que la mise en page d'un tableau de synthèse de la maîtrise du socle 2016

class PDF_socle2016_synthese extends PDF
{

  public function initialiser( $socle_synthese_format , $eleve_nb , $composante_nb , $socle_synthese_affichage )
  {
    $hauteur_entete = 10;
    $intitule_facteur  = ($socle_synthese_format=='eleve')      ? 6 : 8 ;
    $etiquette_facteur = ($socle_synthese_format=='composante') ? 6 : 5 ;
    $aff_socle_points_DNB = ($socle_synthese_affichage=='points') ? 1 : 0 ;
    $colonnes_nb = ($socle_synthese_format=='eleve')      ? $composante_nb+$aff_socle_points_DNB : $eleve_nb ;
    $lignes_nb   = ($socle_synthese_format=='composante') ? $composante_nb+$aff_socle_points_DNB : $eleve_nb ;
    $this->cases_largeur     = ($this->page_largeur_moins_marges - 2) / ( $colonnes_nb + $intitule_facteur ); // -2 pour une petite marge ; identité/composante
    $this->intitule_largeur  = $intitule_facteur * $this->cases_largeur;
    $this->taille_police     = $this->cases_largeur*1;
    $this->taille_police     = min($this->taille_police,10); // pas plus de 10
    $this->taille_police     = max($this->taille_police,5);  // pas moins de 5
    $this->cases_hauteur     = ( $this->page_hauteur_moins_marges - 2 - $hauteur_entete ) / ( $lignes_nb + $etiquette_facteur + $this->legende ); // -2 pour une petite marge - en-tête ; identité/item + légende
    $this->etiquette_hauteur = $etiquette_facteur * $this->cases_hauteur;
    $this->cases_hauteur     = min($this->cases_hauteur,10); // pas plus de 10
    $this->cases_hauteur     = max($this->cases_hauteur,3);  // pas moins de 3
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->AddPage($this->orientation , 'A4');
    $this->SetAutoPageBreak(TRUE);
  }

  public function entete( $titre , $groupe_nom , $objet , $socle_synthese_format )
  {
    $hauteur_entete = 10;
    $format = ($socle_synthese_format=='eleve') ? 'élèves' : 'composantes' ;
    // Intitulé
    $this->SetFont('Arial' , 'B' , 10);
    $this->SetXY($this->marge_gauche , $this->marge_haut);
    $this->Cell( $this->page_largeur , 4 , To::pdf($titre                                          ) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    $this->Cell( $this->page_largeur , 4 , To::pdf($groupe_nom.' - '.$objet.' ('.$format.' en lignes)') , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    // On se positionne sous l'en-tête
    $this->SetXY($this->marge_gauche , $this->marge_haut+$hauteur_entete);
    $this->SetFont('Arial' , '' , $this->taille_police);
  }

  public function ligne_tete_cellule_debut()
  {
    $this->Cell( $this->intitule_largeur , $this->cases_hauteur , '' , 0 , 0 , 'C' , FALSE /*fond*/ , '' );
    $this->choisir_couleur_fond('gris_clair');
  }

  public function ligne_tete_cellule_corps( $contenu , $is_bold = NULL )
  {
    if($is_bold)
    {
      $this->choisir_couleur_fond('gris_moyen');
      $this->SetFont('Arial' , 'B' , $this->taille_police);
    }
    $this->VertCellFit( $this->cases_largeur, $this->etiquette_hauteur, To::pdf($contenu), 1 /*border*/ , 0 /*br*/ , $this->fond );
    if($is_bold)
    {
      $this->SetFont('Arial' , '' , $this->taille_police);
    }
  }

  public function cellule_total_points( $texte )
  {
    $this->choisir_couleur_fond('gris_moyen');
    $this->SetFont('Arial' , 'B' , $this->taille_police);
    $this->CellFit( $this->cases_largeur , $this->cases_hauteur , To::pdf($texte) , 1 /*border*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ , '' );
    $this->SetFont('Arial' , '' , $this->taille_police);
  }

  public function ligne_retour( $id )
  {
    // $id vaut 0 pour la première ligne
    $hauteur = ($id) ? $this->cases_hauteur : $this->etiquette_hauteur ;
    $this->SetXY( $this->marge_gauche , $this->GetY() + $hauteur );
  }

  public function ligne_corps_cellule_debut( $contenu1 , $contenu2 = NULL , $is_bold = NULL )
  {
    $couleur = (!$is_bold) ? 'gris_clair' : 'gris_moyen' ;
    $this->choisir_couleur_fond($couleur);
    if(!$contenu2)
    {
      if($is_bold)
      {
        $this->SetFont('Arial' , 'B' , $this->taille_police);
      }
      $this->CellFit( $this->intitule_largeur , $this->cases_hauteur , To::pdf($contenu1) , 1 , 0 , 'L' , $this->fond , '' );
      if($is_bold)
      {
        $this->SetFont('Arial' , '' , $this->taille_police);
      }
    }
    else
    {
      $hauteur = $this->cases_hauteur / 2 ;
      $this->SetFont('Arial' , 'B' , $this->taille_police);
      $this->CellFit( $this->intitule_largeur , $hauteur , To::pdf($contenu1) , 0 , 2 , 'L' , $this->fond , '' );
      $this->SetFont('Arial' , '' , $this->taille_police);
      $this->CellFit( $this->intitule_largeur , $hauteur , To::pdf($contenu2) , 0 , 2 , 'L' , $this->fond , '' );
      $this->SetY( $this->GetY() - $this->cases_hauteur );
      $this->CellFit( $this->intitule_largeur , $this->cases_hauteur , ''     , 1 , 0 , 'L' , FALSE       , '' );
    }
  }

  public function legende( $socle_synthese_affichage )
  {
    $this->lignes_hauteur = $this->cases_hauteur;
    $ordonnee = $this->page_hauteur - $this->marge_bas - $this->lignes_hauteur*0.75;
    $info_points = ($socle_synthese_affichage=='points') ? '_points' : '' ;
    $this->afficher_legende( 'degre_maitrise'.$info_points /*type_legende*/ , $ordonnee /*ordonnée*/ );
  }

}
?>