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

// Ces méthodes ne concernent que la mise en page d'un relevé (individuel ou collectif) de maîtrise du socle

class PDF_socle2016_releve extends PDF
{

  public function initialiser( $socle_individuel_format , $eleve_nb , $composante_nb , $eleve_nb_moyen , $composante_nb_moyen , $pages_nb_methode , $aff_socle_items_acquis , $aff_socle_position , $aff_socle_points_DNB )
  {
    $hauteur_entete = 10;
    $largeur = $this->page_largeur_moins_marges / ( 8 + (6*$aff_socle_items_acquis) + (4*$aff_socle_position) + (2*$aff_socle_points_DNB) );
    $this->intitule_largeur  = $largeur * 8;
    $this->synthese_largeur  = $largeur * 6;
    $this->cases_largeur     = $largeur;
    $this->SetAutoPageBreak(FALSE);
    $lignes_nb  = ($socle_individuel_format=='eleve') ? $composante_nb_moyen : $eleve_nb_moyen ;
    $parties_nb = ($socle_individuel_format=='eleve') ? $eleve_nb            : $composante_nb ;
    $lignes_nb_par_partie   = 1 + 1 + $lignes_nb + ($this->legende*2) + 1; // intitulé-structure + lignes + légende + interligne
    $hauteur_dispo_par_page = $this->page_hauteur_moins_marges ;
    if($pages_nb_methode=='optimise')
    {
      // Dans ce cas on met plusieurs parties par page si possible : on calcule maintenant combien et la hauteur de ligne à prendre
      $lignes_nb_total            = $parties_nb * $lignes_nb_par_partie ; // eleves|composantes * lignes_nb_par_partie
      $hauteur_ligne_moyenne      = 6;
      $lignes_nb_moyen_par_page   = $hauteur_dispo_par_page / $hauteur_ligne_moyenne ;
      $nb_page_moyen              = max( 1 , round( $lignes_nb_total / $lignes_nb_moyen_par_page ) ); // max 1 pour éviter une division par zéro
      $parties_nb_par_page         = ceil( $parties_nb / $nb_page_moyen ) ;
      // $nb_page_calcule = ceil( $parties_nb / $parties_nb_par_page ) ; // devenu inutile
      $lignes_nb_moyen_partie      = $lignes_nb_total / $parties_nb ;
      $lignes_nb_calcule_par_page = $parties_nb_par_page * $lignes_nb_moyen_partie ; // $lignes_nb/$nb_page_calcule ne va pas car une partie peut alors être considéré à cheval sur 2 pages
      $hauteur_ligne_calcule      = $hauteur_dispo_par_page / $lignes_nb_calcule_par_page ;
    }
    else
    {
      // Dans ce cas on met une seule partie par page
      $hauteur_ligne_calcule      = $hauteur_dispo_par_page / $lignes_nb_par_partie ;
    }
    $this->lignes_hauteur = Math::floorTo( $hauteur_ligne_calcule , 0.1 ) ; // valeur approchée au dixième près par défaut
    $this->lignes_hauteur = min ( $this->lignes_hauteur , 7.5 ) ;
    $this->cases_hauteur  = $this->lignes_hauteur ;
    // On s'occupe aussi maintenant de la taille de la police
    $this->taille_police  = $this->lignes_hauteur * 1.6 ; // 5mm de hauteur par ligne donne une taille de 8
    $this->taille_police  = min ( $this->taille_police , 10 ) ; // pas plus de 10
    // Pour forcer à prendre une nouvelle page au 1er élève
    $this->SetXY( 0 , 0 );
  }

  public function entete( $titre , $sous_titre , $nb_lignes , $pages_nb_methode )
  {
    if( ($this->GetY()==0) || ($pages_nb_methode=='augmente') )
    {
      $this->AddPage($this->orientation , 'A4');
    }
    else
    {
      $hauteur_requise  = $this->lignes_hauteur * ( 1 + 1 + $nb_lignes + ($this->legende*1.5) + 1); // avec interligne
      $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
      if($hauteur_requise > $hauteur_restante)
      {
        $this->AddPage($this->orientation , 'A4');
      }
      else
      {
        $this->SetXY( $this->marge_gauche , $this->GetY()+$this->lignes_hauteur );
      }
    }
    // Intitulé
    $this->SetFont('Arial' , 'B' , 10);
    // $this->SetXY( $this->marge_gauche , $this->marge_haut );
    $this->Cell( $this->page_largeur , 4 , To::pdf($titre     ) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    $this->Cell( $this->page_largeur , 4 , To::pdf($sous_titre) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    // On se positionne sous l'en-tête
    $this->SetXY( $this->marge_gauche , $this->GetY() + 2 );
    $this->SetFont('Arial' , '' , $this->taille_police);
  }

  public function ligne_debut( $contenu1 , $contenu2 = NULL )
  {
    $this->choisir_couleur_fond('gris_clair');
    if(!$contenu2)
    {
      $this->CellFit( $this->intitule_largeur , $this->cases_hauteur , To::pdf($contenu1) , 1 , 0 , 'L' , $this->fond , '' );
    }
    else
    {
      $hauteur = $this->cases_hauteur / 2 ;
      $this->SetFont('Arial' , 'B' , 0.8*$this->taille_police);
      $this->CellFit( $this->intitule_largeur , $hauteur , To::pdf($contenu1) , 0 , 2 , 'L' , $this->fond , '' );
      $this->SetFont('Arial' , '' , 0.8*$this->taille_police);
      $this->CellFit( $this->intitule_largeur , $hauteur , To::pdf($contenu2) , 0 , 2 , 'L' , $this->fond , '' );
      $this->SetFont('Arial' , '' , $this->taille_police);
      $this->SetXY( $this->marge_gauche , $this->GetY() - $this->cases_hauteur );
      $this->CellFit( $this->intitule_largeur , $this->cases_hauteur , ''     , 1 , 0 , 'L' , FALSE       , '' );
    }
  }

  public function cellule_nombre_points( $texte )
  {
    $this->SetFont('Arial' , 'B' , $this->taille_police);
    $this->CellFit( $this->cases_largeur * 2 , $this->cases_hauteur , To::pdf($texte) , 0 /*border*/ , 0 /*br*/ , 'L' , FALSE , '' );
    $this->SetFont('Arial' , '' , $this->taille_police);
  }

  public function ligne_retour()
  {
    $this->SetXY( $this->marge_gauche , $this->GetY() + $this->cases_hauteur );
  }

  public function legende( $aff_socle_items_acquis , $aff_socle_position , $aff_socle_points_DNB )
  {
    $ordonnee = $this->GetY() + $this->lignes_hauteur*0.5 ;
    if($aff_socle_items_acquis)
    {
      $this->afficher_legende( 'etat_acquisition' /*type_legende*/ , $ordonnee /*ordonnée*/ );
      $ordonnee = $this->GetY();
    }
    if($aff_socle_position)
    {
      $info_points = ($aff_socle_points_DNB) ? '_points' : '' ;
      $this->afficher_legende( 'degre_maitrise'.$info_points /*type_legende*/ , $ordonnee /*ordonnée*/ );
    }
  }

}
?>