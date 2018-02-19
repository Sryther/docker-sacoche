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

// Ces méthodes ne concernent que la mise en page d'un ensemble de commantaires aux évaluations

class PDF_evaluation_commentaires extends PDF
{

  public function initialiser()
  {
    $this->cases_hauteur     = 5 ;
    $this->reference_largeur = 60 ;
    $this->intitule_largeur  = $this->page_largeur_moins_marges - $this->reference_largeur ;
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->AddPage($this->orientation , 'A4');
    $this->SetAutoPageBreak(TRUE);
  }

  public function eleve_commentaires( $eleve_nom , $eleve_prenom , $nb_lignes , $tab_donnees )
  {
    // On prend une nouvelle page PDF si besoin
    $hauteur_requise = $this->cases_hauteur * $nb_lignes;
    $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
    if($hauteur_requise > $hauteur_restante)
    {
      $this->AddPage($this->orientation , 'A4');
    }
    // Nom de l'élève
    $this->SetFont('Arial' , 'B' , 10);
    $this->CellFit( $this->page_largeur_moins_marges , $this->cases_hauteur , To::pdf($eleve_nom.' '.$eleve_prenom) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ );
    $this->SetFont('Arial' , '' , 10);
    // On passe aux données
    foreach($tab_donnees as $tab_ligne)
    {
      $memo_y = $this->GetY();
      // 1ère case
      $this->CellFit( $this->reference_largeur , $this->cases_hauteur , To::pdf($tab_ligne['date'])  , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ );
      $this->CellFit( $this->reference_largeur , $this->cases_hauteur , To::pdf($tab_ligne['titre']) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ );
      $this->SetXY($this->marge_gauche , $memo_y);
      $this->CellFit( $this->reference_largeur , $tab_ligne['lignes']*$this->cases_hauteur , ''  , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ );
      // 2ème case
      $this->afficher_appreciation( $this->intitule_largeur , $tab_ligne['lignes']*$this->cases_hauteur , 10 /*taille_police*/ , 4 /*taille_interligne*/ , $tab_ligne['comm'] );
      $this->SetXY($this->marge_gauche + $this->reference_largeur , $memo_y);
      $this->CellFit( $this->intitule_largeur , $tab_ligne['lignes']*$this->cases_hauteur , ''  , 1 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ );
    }
    // Séparation
    $this->SetXY($this->marge_gauche , $this->GetY() + 2*$this->cases_hauteur);
  }

}
?>