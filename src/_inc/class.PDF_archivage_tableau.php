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

// Ces méthodes ne concernent que l'archivage de tableaux de données issues de bilans officiels

class PDF_archivage_tableau extends PDF
{

  private function appreciation_page_break($nb_lignes=3)
  {
    $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
    $hauteur_requise = $nb_lignes*$this->lignes_hauteur;
    if($hauteur_requise > $hauteur_restante)
    {
      $this->AddPage($this->orientation , 'A4');
    }
  }

  public function appreciation_intitule($intitule)
  {
    $this->taille_police = $this->lignes_hauteur * 2;
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->AddPage($this->orientation , 'A4');
    $this->SetAutoPageBreak(FALSE);
    $this->SetFont('Arial' , 'B' , $this->taille_police*1.2);
    $this->CellFit( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($intitule)  , 0 /*bordure*/ , 1 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
    $this->SetXY($this->marge_gauche , $this->GetY() + 0.5*$this->lignes_hauteur);
  }

  public function appreciation_initialiser_eleves_prof( $nb_eleves , $nb_lignes_supplémentaires , $with_moyenne )
  {
    $this->reference_largeur = 40; // valeur fixe
    $note_largeur          = ($with_moyenne) ? 10 : 0 ; // valeur fixe
    $nb_lignes_necessaires = 1.5 + (2*$nb_eleves)+$nb_lignes_supplémentaires ; // titre + appreciations (2 lignes mini / app)
    $this->cases_largeur   = $this->page_largeur_moins_marges - $this->reference_largeur - $note_largeur ;
    $this->lignes_hauteur  = ($this->page_hauteur_moins_marges) / $nb_lignes_necessaires;
    $this->lignes_hauteur  = min($this->lignes_hauteur,5);
    $this->choisir_couleur_fond('gris_clair');
  }

  public function appreciation_initialiser_eleves_collegues( $nb_eleves , $nb_lignes_rubriques )
  {
    $this->reference_largeur = 40; // valeur fixe
    $nb_lignes_necessaires  = 2 + 1.5*$nb_eleves + $nb_lignes_rubriques ; // titre + élèves et marges (0.5 ligne / eleve) + rubriques avec appréciations
    $this->cases_largeur    = $this->page_largeur_moins_marges - $this->reference_largeur ;
    $hauteur_dispo_par_page = $this->page_hauteur_moins_marges;
    $hauteur_ligne_minimale = 3.5;
    $hauteur_ligne_maximale = 5;
    $nb_pages = 0;
    do
    {
      $nb_pages++;
      $hauteur_ligne_calcule = $nb_pages*$hauteur_dispo_par_page / $nb_lignes_necessaires ;
    }
    while($hauteur_ligne_calcule < $hauteur_ligne_minimale);
    $this->lignes_hauteur = Math::floorTo( $hauteur_ligne_calcule , 0.1 ) ; // valeur approchée au dixième près par défaut
    $this->lignes_hauteur = min ( $this->lignes_hauteur , $hauteur_ligne_maximale ) ;
  }

  public function appreciation_initialiser_classe_collegues( $nb_eleves , $nb_rubriques , $nb_lignes_supplémentaires )
  {
    $this->reference_largeur = 40; // valeur fixe
    $nb_lignes_necessaires = 1.5 + (2.5*$nb_rubriques)+$nb_lignes_supplémentaires ; // titre + appreciations (2 lignes mini / rubrique + 0.5 de marge)
    $this->cases_largeur   = $this->page_largeur_moins_marges - $this->reference_largeur ;
    $this->lignes_hauteur  = ($this->page_hauteur_moins_marges) / $nb_lignes_necessaires;
    $this->lignes_hauteur  = min($this->lignes_hauteur,5);
  }

  public function appreciation_initialiser_eleves_syntheses( $nb_eleves , $nb_lignes_supplémentaires , $with_moyenne )
  {
    $this->reference_largeur = 40; // valeur fixe
    $note_largeur          = ($with_moyenne) ? 10 : 0 ; // valeur fixe
    $nb_lignes_necessaires = 1.5 + (2*$nb_eleves)+$nb_lignes_supplémentaires ; // titre + appreciations (2 lignes mini / app)
    $this->cases_largeur   = $this->page_largeur_moins_marges - $this->reference_largeur - $note_largeur ;
    $this->lignes_hauteur  = ($this->page_hauteur_moins_marges) / $nb_lignes_necessaires;
    $this->lignes_hauteur  = min($this->lignes_hauteur,5);
    $this->choisir_couleur_fond('gris_clair');
  }

  public function appreciation_rubrique_eleves_prof( $eleve_id , $eleve_nom , $eleve_prenom , $note , $appreciation , $with_moyenne , $objet_document )
  {
    $nb_lignes = max( 2 , ceil(mb_strlen($appreciation)/125) );
    $note_largeur = 10; // valeur fixe
    // cadre
    $memo_x = $this->GetX();
    $memo_y = $this->GetY();
    $fond = ($eleve_id) ? FALSE : TRUE ;
    $this->Cell( $this->page_largeur_moins_marges , $nb_lignes*$this->lignes_hauteur , '' , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , $fond );
    // nom-prénom
    $this->SetXY($memo_x , $memo_y+($nb_lignes-2+(int)$fond)*$this->lignes_hauteur/2);
    $this->SetFont('Arial' , '' , $this->taille_police);
    $this->CellFit( $this->reference_largeur , $this->lignes_hauteur , To::pdf($eleve_nom)    , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    $this->CellFit( $this->reference_largeur , $this->lignes_hauteur , To::pdf($eleve_prenom) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    // moyenne
    $this->SetXY($memo_x+$this->reference_largeur , $memo_y);
    if($with_moyenne)
    {
      if($objet_document=='bulletin')
      {
        $moyenne_eleve = ($note!==NULL) ? ( ($this->SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? number_format($note,1,',','') : ($note*5).'%' ) : '-' ;
      }
      else
      {
        $moyenne_eleve = $note;
      }
      $this->CellFit( $note_largeur , $nb_lignes*$this->lignes_hauteur , To::pdf($moyenne_eleve) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
    }
    else
    {
      $this->Line( $memo_x+$this->reference_largeur , $memo_y , $memo_x+$this->reference_largeur , $memo_y+$nb_lignes*$this->lignes_hauteur );
    }
    // appréciation
    $this->afficher_appreciation( $this->cases_largeur , $nb_lignes*$this->lignes_hauteur , $this->taille_police , $this->lignes_hauteur , $appreciation );
    $this->SetXY($memo_x , $memo_y+$nb_lignes*$this->lignes_hauteur);
  }

  public function appreciation_rubrique_eleves_collegues( $eleve_nom , $eleve_prenom , $rubrique_nom , $note , $appreciations , $with_moyenne , $objet_document )
  {
    $nb_lignes = max( 2 , ceil(mb_strlen($appreciations)/125) );
    // On prend une nouvelle page PDF si besoin
    $this->appreciation_page_break( $nb_lignes + ($eleve_nom && $eleve_prenom) );
    $this->choisir_couleur_fond('gris_moyen');
    // nom-prénom
    if($eleve_nom && $eleve_prenom)
    {
      $this->SetXY($this->marge_gauche , $this->GetY() + 0.5*$this->lignes_hauteur);
      $this->SetFont('Arial' , '' , $this->taille_police);
      $this->CellFit( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($eleve_nom.' '.$eleve_prenom) , 1 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , TRUE /*fond*/ );
    }
    // cadre
    $memo_x = $this->GetX();
    $memo_y = $this->GetY();
    $this->Cell( $this->page_largeur_moins_marges , $nb_lignes*$this->lignes_hauteur , '' , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    // rubrique + moyenne
    $this->SetXY($memo_x , $memo_y+($nb_lignes-2)*$this->lignes_hauteur/2);
    $this->SetFont('Arial' , '' , $this->taille_police);
    $this->CellFit( $this->reference_largeur , $this->lignes_hauteur , To::pdf($rubrique_nom) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    if($with_moyenne)
    {
      if($objet_document=='bulletin')
      {
        $moyenne_eleve = ($note!==NULL) ? ( ($this->SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? number_format($note,1,',','') : ($note*5).'%' ) : '-' ;
      }
      else
      {
        $moyenne_eleve = $note;
      }
      $this->CellFit( $this->reference_largeur , $this->lignes_hauteur , To::pdf($moyenne_eleve) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    }
    $this->Line( $memo_x+$this->reference_largeur , $memo_y , $memo_x+$this->reference_largeur , $memo_y+$nb_lignes*$this->lignes_hauteur );
    // appréciations
    $this->SetXY($memo_x+$this->reference_largeur , $memo_y);
    $this->afficher_appreciation( $this->cases_largeur , $nb_lignes*$this->lignes_hauteur , $this->taille_police , $this->lignes_hauteur , $appreciations );
    $this->SetXY($memo_x , $memo_y+$nb_lignes*$this->lignes_hauteur);
  }

  public function appreciation_rubrique_classe_collegues( $rubrique_nom , $note , $appreciations , $with_moyenne , $objet_document )
  {
    $nb_lignes = max( 2 , ceil(mb_strlen($appreciations)/125) );
    // marge
    $this->SetXY($this->marge_gauche , $this->GetY() + 0.5*$this->lignes_hauteur);
    // cadre
    $memo_x = $this->GetX();
    $memo_y = $this->GetY();
    $this->Cell( $this->page_largeur_moins_marges , $nb_lignes*$this->lignes_hauteur , '' , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    // rubrique + moyenne
    $this->SetXY($memo_x , $memo_y+($nb_lignes-2)*$this->lignes_hauteur/2);
    $this->SetFont('Arial' , '' , $this->taille_police);
    $this->CellFit( $this->reference_largeur , $this->lignes_hauteur , To::pdf($rubrique_nom)    , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    if($with_moyenne)
    {
      if($objet_document=='bulletin')
      {
        $moyenne_eleve = ($note!==NULL) ? ( ($this->SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? number_format($note,1,',','') : ($note*5).'%' ) : '-' ;
      }
      else
      {
        $moyenne_eleve = $note;
      }
      $this->CellFit( $this->reference_largeur , $this->lignes_hauteur , To::pdf($moyenne_eleve) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    }
    $this->Line( $memo_x+$this->reference_largeur , $memo_y , $memo_x+$this->reference_largeur , $memo_y+$nb_lignes*$this->lignes_hauteur );
    // appréciation
    $this->SetXY($memo_x+$this->reference_largeur , $memo_y);
    $this->afficher_appreciation( $this->cases_largeur , $nb_lignes*$this->lignes_hauteur , $this->taille_police , $this->lignes_hauteur , $appreciations );
    $this->SetXY($memo_x , $memo_y+$nb_lignes*$this->lignes_hauteur);
  }

  public function moyennes_initialiser( $eleve_nb , $rubrique_nb )
  {
    $reference_largeur_minimum = 50;
    $cases_hauteur_maximum     = 25;
    $this->cases_largeur     = 10; // valeur par défaut ; diminué si pas assez de place pour la référence de l'item
    $this->etiquette_hauteur = 50; // valeur fixe
    $this->reference_largeur = $this->page_largeur_moins_marges - ($rubrique_nb * $this->cases_largeur);
    if($this->reference_largeur < $reference_largeur_minimum)
    {
      $this->reference_largeur = $reference_largeur_minimum;
      $this->cases_largeur     = ($this->page_largeur_moins_marges - $this->reference_largeur) / $rubrique_nb;
    }
    $this->cases_hauteur     = ($this->page_hauteur_moins_marges - $this->etiquette_hauteur) / $eleve_nb;
    $this->cases_hauteur     = min($this->cases_hauteur,$cases_hauteur_maximum);
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->AddPage($this->orientation , 'A4');
    $this->SetAutoPageBreak(TRUE);
  }

  public function moyennes_intitule( $classe_nom , $periode_nom , $objet_document )
  {
    if($objet_document=='bulletin')
    {
      $ligne1 = 'Bulletin scolaire';
      $ligne2 = 'Tableau des positionnements';
    }
    if($objet_document=='livret')
    {
      $ligne1 = 'Livret scolaire';
      $ligne2 = 'Tableau des positionnements';
    }
    if($objet_document=='points_dnb')
    {
      $ligne1 = 'Livret scolaire';
      $ligne2 = 'Tableau des points pour le brevet';
    }
    if($objet_document=='affelnet')
    {
      $ligne1 = 'Moyenne Affelnet';
      $ligne2 = 'Tableau des points calculés par matière';
    }
    $hauteur_quart = $this->etiquette_hauteur / 4 ;
    $this->SetXY($this->marge_gauche , $this->marge_haut);
    $this->SetFont('Arial' , 'B' , $this->taille_police);
    $this->CellFit( $this->reference_largeur , $hauteur_quart , To::pdf($ligne1)      , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
    $this->CellFit( $this->reference_largeur , $hauteur_quart , To::pdf($ligne2)      , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
    $this->CellFit( $this->reference_largeur , $hauteur_quart , To::pdf($classe_nom)  , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
    $this->CellFit( $this->reference_largeur , $hauteur_quart , To::pdf($periode_nom) , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
    $this->SetXY($this->marge_gauche , $this->marge_haut);
    $this->SetFont('Arial' , '' , $this->taille_police);
    $this->Cell( $this->reference_largeur , $this->etiquette_hauteur , '' , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
  }

  public function moyennes_reference_rubrique( $rubrique_id , $rubrique_nom )
  {
    $couleur = ($rubrique_id) ? 'gris_clair' : 'gris_fonce' ;
    $this->choisir_couleur_fond($couleur);
    $this->VertCellFit( $this->cases_largeur, $this->etiquette_hauteur, To::pdf($rubrique_nom), 1 /*border*/ , 0 /*ln*/ , TRUE /*fill*/ );
  }

  public function moyennes_reference_eleve( $eleve_id , $eleve_nom_prenom )
  {
    $couleur = ($eleve_id) ? 'gris_clair' : 'gris_fonce' ;
    $this->choisir_couleur_fond($couleur);
    $this->CellFit( $this->reference_largeur , $this->cases_hauteur , To::pdf($eleve_nom_prenom) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , TRUE /*fond*/ );
  }

  public function moyennes_note( $eleve_id , $rubrique_id , $note , $objet_document )
  {
    $couleur = ($eleve_id && $rubrique_id ) ? 'blanc' : 'gris_fonce' ;
    $this->choisir_couleur_fond($couleur);
    if($objet_document=='bulletin')
    {
      $moyenne_eleve = ($note!==NULL) ? ( ($this->SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? number_format($note,1,',','') : ($note*5).'%' ) : '-' ;
    }
    else
    {
      $moyenne_eleve = $note;
    }
    $this->CellFit( $this->cases_largeur , $this->cases_hauteur , To::pdf($moyenne_eleve) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
  }

  public function recapitulatif_initialiser( $tab_etabl_coords , $tab_eleve , $classe_nom , $classe_effectif , $annee_affichee , $tag_date_heure_initiales , $nb_lignes , $objet_document )
  {
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->AddPage($this->orientation , 'A4');
    $this->SetAutoPageBreak(FALSE);
    $largeur_tiers = $this->page_largeur_moins_marges / 3 ;
    // Infos établissement
    $bloc_etabl_hauteur = $this->officiel_bloc_etablissement( $tab_etabl_coords , NULL /*tab_etabl_logo*/ , $largeur_tiers );
    // Infos classe / élève
    $this->SetXY( $this->marge_gauche + $largeur_tiers , $this->marge_haut );
    extract($tab_eleve);  // $eleve_nom $eleve_prenom
    $taille_police = 12 ;
    $ligne_hauteur = $taille_police*0.4 ;
    $this->SetFont('Arial' , 'B' , $taille_police);
    $this->CellFit( $largeur_tiers , $ligne_hauteur , To::pdf($classe_nom) , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
    $taille_police = 8 ;
    $ligne_hauteur = $taille_police*0.4 ;
    $this->SetFont('Arial' , '' , $taille_police);
    $this->CellFit( $largeur_tiers , $ligne_hauteur , To::pdf('('.$classe_effectif.' élèves)') , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
    $taille_police = 12 ;
    $ligne_hauteur = $taille_police*0.4 ;
    $this->SetFont('Arial' , 'B' , $taille_police);
    $this->CellFit( $largeur_tiers , $ligne_hauteur , To::pdf($eleve_nom.' '.$eleve_prenom) , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
    // Infos titre / année scolaire / horodatage
    $this->SetXY( $this->marge_gauche + 2*$largeur_tiers , $this->marge_haut );
    $taille_police = 12 ;
    $ligne_hauteur = $taille_police*0.4 ;
    $this->SetFont('Arial' , '' , $taille_police);
    $tab_titre = array(
      'bulletin' => 'Bulletins scolaires',
      'livret'   => 'Livret scolaire',
      'affelnet' => 'Points Affelnet',
    );
    $this->CellFit( $largeur_tiers , $ligne_hauteur , To::pdf($tab_titre[$objet_document]) , 0 /*bordure*/ , 2 /*br*/ , 'R' /*alignement*/ , FALSE /*fond*/ );
    $this->CellFit( $largeur_tiers , $ligne_hauteur , To::pdf('Récapitulatif annuel')      , 0 /*bordure*/ , 2 /*br*/ , 'R' /*alignement*/ , FALSE /*fond*/ );
    $this->CellFit( $largeur_tiers , $ligne_hauteur , To::pdf($annee_affichee)             , 0 /*bordure*/ , 2 /*br*/ , 'R' /*alignement*/ , FALSE /*fond*/ );
    $taille_police = 5 ;
    $ligne_hauteur = $taille_police*0.4 ;
    $this->SetFont('Arial' , '' , $taille_police);
    $this->Cell( $largeur_tiers , $ligne_hauteur+4 , To::pdf($tag_date_heure_initiales) , 0 /*bordure*/ , 2 /*br*/ , 'R' /*alignement*/ , FALSE /*fond*/ );
    // On passe au tableau
    $hauteur_entete = max( $bloc_etabl_hauteur , (12+8+12)*0.4 , (12+12+12+5)*0.4+4 ) + 2 ;
    $hauteur_restante = $this->page_hauteur_moins_marges - $hauteur_entete;
    $hauteur_ligne_calcule = $hauteur_restante / ($nb_lignes+2) ;
    $this->lignes_hauteur = min( 10 , Math::floorTo( $hauteur_ligne_calcule , 0.1 ) ) ; // valeur approchée au dixième près par défaut
    $this->taille_police  = $this->lignes_hauteur * 1.6 ; // 5mm de hauteur par ligne donne une taille de 8
    // Ligne d'en-tête ; d'abord le cadre puis le texte quand il est sur plusieurs lignes
    $this->choisir_couleur_fond('gris_clair');
    $memo_y = $this->marge_haut + $hauteur_entete + 2 ;
    $bloc_hauteur = 2*$this->lignes_hauteur;
    $taille_police_minimum = max(10,$this->taille_police);
    $this->SetFont('Arial' , '' , $taille_police_minimum );
    if($objet_document!='affelnet')
    {
      $this->reference_largeur = 30;
      $this->cases_largeur     = 15;
      $this->intitule_largeur  = $this->page_largeur_moins_marges - $this->reference_largeur - 2 * $this->cases_largeur ;
      // case 1
      $memo_x = $this->marge_gauche;
      $this->Rect( $memo_x , $memo_y , $this->reference_largeur , $bloc_hauteur , 'DF' /* DrawFill */ );
      $this->SetXY( $memo_x , $memo_y );
      $this->CellFit( $this->reference_largeur , $this->lignes_hauteur , To::pdf('Enseignements') , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
      $this->CellFit( $this->reference_largeur , $this->lignes_hauteur , To::pdf('Professeur(s)') , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
      // case 2
      $memo_x += $this->reference_largeur;
      $this->Rect( $memo_x , $memo_y , $this->cases_largeur , $bloc_hauteur , 'DF' /* DrawFill */ );
      $this->SetXY( $memo_x , $memo_y );
      $this->CellFit( $this->cases_largeur     , $this->lignes_hauteur , To::pdf('Moy. annuelle') , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
      $this->CellFit( $this->cases_largeur     , $this->lignes_hauteur , To::pdf('de l\'élève')   , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
      // case 3
      $memo_x += $this->cases_largeur;
      $this->Rect( $memo_x , $memo_y , $this->cases_largeur , $bloc_hauteur , 'DF' /* DrawFill */ );
      $this->SetXY( $memo_x , $memo_y );
      $this->CellFit( $this->cases_largeur     , $this->lignes_hauteur , To::pdf('Moy. annuelle') , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
      $this->CellFit( $this->cases_largeur     , $this->lignes_hauteur , To::pdf('de la classe')  , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
      // case 4
      $memo_x += $this->cases_largeur;
      $this->SetXY( $memo_x , $memo_y );
      $this->CellFit( $this->intitule_largeur  , $bloc_hauteur , To::pdf('Positionnements et appréciations par période') , 1 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , TRUE /*fond*/ );
    }
    else
    {
      $this->cases_largeur = $this->page_largeur_moins_marges / 4;
      // case 1
      $memo_x = $this->marge_gauche;
      $this->Rect( $memo_x , $memo_y , $this->cases_largeur , $bloc_hauteur , 'DF' /* DrawFill */ );
      $this->SetXY( $memo_x , $memo_y );
      $this->CellFit( $this->cases_largeur , $this->lignes_hauteur , To::pdf('Enseignements') , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
      $this->CellFit( $this->cases_largeur , $this->lignes_hauteur , To::pdf('Professeur(s)') , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
      // case 2
      $memo_x += $this->cases_largeur;
      $this->Rect( $memo_x , $memo_y , $this->cases_largeur , $bloc_hauteur , 'DF' /* DrawFill */ );
      $this->SetXY( $memo_x , $memo_y );
      $this->CellFit( $this->cases_largeur , $this->lignes_hauteur , To::pdf('Positionnements') , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
      $this->CellFit( $this->cases_largeur , $this->lignes_hauteur , To::pdf('livret scolaire') , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
      // case 3
      $memo_x += $this->cases_largeur;
      $this->Rect( $memo_x , $memo_y , $this->cases_largeur , $bloc_hauteur , 'DF' /* DrawFill */ );
      $this->SetXY( $memo_x , $memo_y );
      $this->CellFit( $this->cases_largeur , $this->lignes_hauteur , To::pdf('Points') , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
      $this->CellFit( $this->cases_largeur , $this->lignes_hauteur , To::pdf('Affelnet')  , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
      // case 4
      $memo_x += $this->cases_largeur;
      $this->Rect( $memo_x , $memo_y , $this->cases_largeur , $bloc_hauteur , 'DF' /* DrawFill */ );
      $this->SetXY( $memo_x , $memo_y );
      $this->CellFit( $this->cases_largeur , $this->lignes_hauteur , To::pdf('Moyenne') , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
      $this->CellFit( $this->cases_largeur , $this->lignes_hauteur , To::pdf('Affelnet')  , 0 /*bordure*/ , 1 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
    }
  }

  public function recapitulatif_rubrique( $nb_lignes , $rubrique_nom , $tab_profs , $moyenne_eleve , $moyenne_classe , $tab_appreciations )
  {
    $memo_y = $this->GetY() ;
    $bloc_hauteur = $nb_lignes*$this->lignes_hauteur;
    $taille_police_minimum = max(10,$this->taille_police);
    $taille_police_maximum = min(12,$this->taille_police);
    // case 1
    $memo_x = $this->marge_gauche;
    $this->Rect( $memo_x , $memo_y , $this->reference_largeur , $bloc_hauteur , 'D' /* DrawFill */ );
    $nb_lignes_case1 = ($tab_profs) ? 1 + count($tab_profs) : 1 ;
    $this->SetXY( $memo_x , $memo_y + ($nb_lignes-$nb_lignes_case1)*$this->lignes_hauteur/2 );
    $this->SetFont('Arial' , 'B' , $taille_police_minimum);
    $this->CellFit( $this->reference_largeur , $this->lignes_hauteur , To::pdf($rubrique_nom) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    $this->SetFont('Arial' , '' , $taille_police_minimum);
    if($tab_profs)
    {
      foreach($tab_profs as $prof_info)
      {
        $this->CellFit( $this->reference_largeur , $this->lignes_hauteur , To::pdf($prof_info) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
      }
    }
    // case 2
    $memo_x += $this->reference_largeur;
    $this->SetXY( $memo_x , $memo_y );
    $moyenne_eleve = ($moyenne_eleve!==NULL) ? number_format($moyenne_eleve,1,',','') : '-' ;
    $this->SetFont('Arial' , 'B' , $taille_police_minimum+1);
    $this->CellFit( $this->cases_largeur , $bloc_hauteur , To::pdf($moyenne_eleve) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
    // case 3
    $memo_x += $this->cases_largeur;
    $this->SetXY( $memo_x , $memo_y );
    $moyenne_classe = ($moyenne_classe!==NULL) ? number_format($moyenne_classe,1,',','') : '-' ;
    $this->SetFont('Arial' , '' , $taille_police_minimum-1);
    $this->CellFit( $this->cases_largeur , $bloc_hauteur , To::pdf($moyenne_classe) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
    // case 4
    $memo_x += $this->cases_largeur;
    $this->SetXY( $memo_x , $memo_y );
    $line_y = $memo_y;
    $this->SetFont('Arial' , '' , $taille_police_maximum);
    if($tab_appreciations)
    {
      $this->choisir_couleur_trait('gris_moyen');
      foreach($tab_appreciations as $appreciation)
      {
        $nb_lignes = ceil(mb_strlen($appreciation)/125);
        $this->afficher_appreciation( $this->intitule_largeur , $nb_lignes*$this->lignes_hauteur , $taille_police_maximum , $this->lignes_hauteur , $appreciation );
        $line_y = $line_y + $nb_lignes*$this->lignes_hauteur;
        $this->Line( $memo_x , $line_y , $memo_x+$this->intitule_largeur , $line_y );
      }
      $this->choisir_couleur_trait('noir');
    }
    $this->Rect( $memo_x , $memo_y , $this->intitule_largeur , $bloc_hauteur , 'D' /* DrawFill */ );
    // retour à la ligne
    $this->SetXY( $this->marge_gauche , $memo_y + $bloc_hauteur );
  }

  public function recapitulatif_rubrique_affelnet( $nb_lignes , $rubrique_nom , $tab_profs , $tab_notes , $tab_points , $moyenne_eleve )
  {
    $memo_y = $this->GetY() ;
    $bloc_hauteur = $nb_lignes*$this->lignes_hauteur;
    $taille_police_minimum = max(10,$this->taille_police);
    $taille_police_maximum = min(12,$this->taille_police);
    // case 1
    $memo_x = $this->marge_gauche;
    $this->Rect( $memo_x , $memo_y , $this->cases_largeur , $bloc_hauteur , 'D' /* DrawFill */ );
    $nb_lignes_case1 = ($tab_profs) ? 1 + count($tab_profs) : 1 ;
    $this->SetXY( $memo_x , $memo_y + ($nb_lignes-$nb_lignes_case1)*$this->lignes_hauteur/2 );
    $this->SetFont('Arial' , 'B' , $taille_police_minimum);
    $this->CellFit( $this->cases_largeur , $this->lignes_hauteur , To::pdf($rubrique_nom) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    $this->SetFont('Arial' , '' , $taille_police_minimum);
    if($tab_profs)
    {
      foreach($tab_profs as $prof_info)
      {
        $this->CellFit( $this->cases_largeur , $this->lignes_hauteur , To::pdf($prof_info) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
      }
    }
    // case 2
    $memo_x += $this->cases_largeur;
    $this->SetXY( $memo_x , $memo_y );
    $this->SetFont('Arial' , '' , $taille_police_maximum);
    if($tab_notes)
    {
      foreach($tab_notes as $note)
      {
        $this->CellFit( $this->cases_largeur , $this->lignes_hauteur , To::pdf($note) , 1 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
      }
    }
    $this->Rect( $memo_x , $memo_y , $this->cases_largeur , $bloc_hauteur , 'D' /* DrawFill */ );
    // case 3
    $memo_x += $this->cases_largeur;
    $this->SetXY( $memo_x , $memo_y );
    $this->SetFont('Arial' , '' , $taille_police_maximum);
    if($tab_points)
    {
      foreach($tab_notes as $jointure_periode => $note)
      {
        $texte_point = isset($tab_points[$jointure_periode]) ? $tab_points[$jointure_periode].' points' : '-' ;
        $this->CellFit( $this->cases_largeur , $this->lignes_hauteur , To::pdf($texte_point) , 1 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
      }
    }
    $this->Rect( $memo_x , $memo_y , $this->cases_largeur , $bloc_hauteur , 'D' /* DrawFill */ );
    // case 4
    $memo_x += $this->cases_largeur;
    $this->SetXY( $memo_x , $memo_y );
    $moyenne_eleve = ($moyenne_eleve!==NULL) ? number_format($moyenne_eleve,1,',','').' points' : '-' ;
    $this->SetFont('Arial' , 'B' , $taille_police_minimum+1);
    $this->CellFit( $this->cases_largeur , $bloc_hauteur , To::pdf($moyenne_eleve) , 1 /*bordure*/ , 1 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
  }

}
?>