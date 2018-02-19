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

// Ces méthodes ne concernent que l'archivage de moyennes et éléments d'appréciations issus d'un relevé d'items

class PDF_item_bulletin extends PDF
{

  public function initialiser_et_entete( $titre_nom , $eleve_nb , $matiere_et_groupe , $texte_periode , $info_ponderation_complete )
  {
    // initialisation
    $this->cases_largeur  = floor( $this->page_largeur_moins_marges / 3 );
    $this->cases_hauteur  = floor( $this->page_hauteur_moins_marges / ($eleve_nb+2+3) ); // 2 pour les lignes en-tête et pied de tableau ; 3 pour les titres
    $this->lignes_hauteur = $this->cases_hauteur*0.8;
    $this->taille_police  = $this->cases_hauteur*1.2;
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->AddPage($this->orientation , 'A4');
    $this->SetAutoPageBreak(FALSE);
    // en-tête
    $this->SetFont('Arial' , 'B' , $this->taille_police*1.2);
    $titre = 'Bilan '.$titre_nom.' - Moyenne sur 20 / Élément d\'appréciation';
    $this->CellFit( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($titre)             , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    $this->CellFit( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($matiere_et_groupe) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    $this->CellFit( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($texte_periode)     , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    $this->SetFont('Arial' , '' , $this->taille_police);
    // première ligne
    $this->choisir_couleur_fond('gris_moyen');
    $this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur);
    $this->CellFit( $this->cases_largeur , $this->cases_hauteur , To::pdf('Élève')                                                                      , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
    $this->CellFit( $this->cases_largeur , $this->cases_hauteur , To::pdf('Moyenne '.$info_ponderation_complete.' sur 20 (des scores d\'acquisitions)') , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
    $this->CellFit( $this->cases_largeur , $this->cases_hauteur , To::pdf('Élément d\'appréciation (pourcentage d\'items acquis)')                      , 1 /*bordure*/ , 1 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
  }

  public function ligne_eleve( $eleve_nom_prenom , $note , $appreciation_PA )
  {
    $this->choisir_couleur_fond('gris_clair');
    $this->CellFit( $this->cases_largeur , $this->cases_hauteur , To::pdf($eleve_nom_prenom) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , TRUE  /*fond*/ );
    $this->CellFit( $this->cases_largeur , $this->cases_hauteur , To::pdf($note)             , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
    $this->CellFit( $this->cases_largeur , $this->cases_hauteur , To::pdf($appreciation_PA)  , 1 /*bordure*/ , 1 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
  }

  public function derniere_ligne( $info_ponderation_complete , $moyenne_affichee , $moyenne_pourcentage_acquis )
  {
    $this->choisir_couleur_fond('gris_moyen');
    $this->CellFit( $this->cases_largeur , $this->cases_hauteur , To::pdf('Moyenne '.$info_ponderation_complete.' sur 20') , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
    $this->CellFit( $this->cases_largeur , $this->cases_hauteur , To::pdf($moyenne_affichee)                               , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
    $this->CellFit( $this->cases_largeur , $this->cases_hauteur , To::pdf($moyenne_pourcentage_acquis.'% d\'items acquis') , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
  }

}
?>