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

// Ces méthodes ne concernent que la mise en page d'un bilan d'items

class PDF_item_synthese extends PDF
{

  // initialiser()   --> c'est là que les calculs se font pour une sortie "matiere"
  // entete()        --> c'est là que les calculs se font pour une sortie "multimatiere"

  private function premiere_page()
  {
    $this->AddPage($this->orientation , 'A4');
    $this->page_numero_first = $this->page;
    $this->choisir_couleur_texte('gris_fonce');
    $this->SetFont('Arial' , 'B' , 7);
    $this->Cell( $this->page_largeur_moins_marges , 4 /*ligne_hauteur*/ , To::pdf('Page 1/'.$this->page_nombre_alias) , 0 /*bordure*/ , 1 /*br*/ , $this->page_nombre_alignement , FALSE /*fond*/ );
    $this->choisir_couleur_texte('noir');
    $this->SetXY( $this->marge_gauche , $this->marge_haut );
  }

  private function rappel_eleve_page()
  {
    $this->AddPage($this->orientation , 'A4');
    $page_numero = $this->page - $this->page_numero_first + 1 ;
    $this->SetFont('Arial' , 'B' , $this->taille_police);
    $this->choisir_couleur_texte('gris_fonce');
    $this->Cell( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($this->doc_titre.' - '.$this->eleve_nom.' '.$this->eleve_prenom.' - Page '.$page_numero.'/'.$this->page_nombre_alias) , 0 /*bordure*/ , 1 /*br*/ , $this->page_nombre_alignement , FALSE /*fond*/ );
    $this->choisir_couleur_texte('noir');
  }

  public function initialiser( $synthese_modele , $nb_lignes_total , $eleves_nb )
  {
    $this->synthese_modele = $synthese_modele;
    $this->SetMargins( $this->marge_gauche , $this->marge_haut , $this->marge_droite );
    $this->SetAutoPageBreak(FALSE);
    if($this->synthese_modele=='matiere')
    {
      // Dans ce cas on met plusieurs élèves par page : on calcule maintenant combien et la hauteur de ligne à prendre
      $hauteur_dispo_par_page     = $this->page_hauteur_moins_marges ;
      $lignes_nb_tous_eleves      = $eleves_nb * ( 1 + 1 + ($this->legende*1.5) ) + $nb_lignes_total ; // eleves * [ intitulé-structure + classe-élève-date + légende ] + toutes_synthèses
      $hauteur_ligne_moyenne      = 6;
      $lignes_nb_moyen_par_page   = $hauteur_dispo_par_page / $hauteur_ligne_moyenne ;
      $nb_page_moyen              = max( 1 , round( $lignes_nb_tous_eleves / $lignes_nb_moyen_par_page ) ); // max 1 pour éviter une division par zéro
      $eleves_nb_par_page         = ceil( $eleves_nb / $nb_page_moyen ) ;
      // $nb_page_calcule = ceil( $eleves_nb / $eleves_nb_par_page ) ; // devenu inutile
      $lignes_nb_moyen_eleve      = $lignes_nb_tous_eleves / $eleves_nb ;
      $lignes_nb_calcule_par_page = $eleves_nb_par_page * $lignes_nb_moyen_eleve ; // $lignes_nb/$nb_page_calcule ne va pas car un élève peut alors être considéré à cheval sur 2 pages
      $hauteur_ligne_calcule      = $hauteur_dispo_par_page / $lignes_nb_calcule_par_page ;
      $this->lignes_hauteur = Math::floorTo( $hauteur_ligne_calcule , 0.1 ) ; // valeur approchée au dixième près par défaut
      $this->lignes_hauteur = min ( $this->lignes_hauteur , 7.5 ) ;
      // On s'occupe aussi maintenant de la taille de la police
      $this->taille_police  = $this->lignes_hauteur * 1.6 ; // 5mm de hauteur par ligne donne une taille de 8
      $this->taille_police  = min ( $this->taille_police , 10 ) ;
      // Pour forcer à prendre une nouvelle page au 1er élève
      $this->SetXY( 0 , $this->page_hauteur );
    }
  }

  public function entete( $tab_infos_entete , $eleve_nom , $eleve_prenom , $eleve_INE , $eleve_nb_lignes)
  {
    $this->eleve_nom    = $eleve_nom;
    $this->eleve_prenom = $eleve_prenom;
    if($this->synthese_modele=='matiere')
    {
      // La hauteur de ligne a déjà été calculée ; mais il reste à déterminer si on saute une page ou non en fonction de la place restante (et sinon => interligne)
      $hauteur_dispo_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas ;
      $lignes_nb = 1 + 1 + ($this->legende*1.5) + $eleve_nb_lignes ; // intitulé-structure + classe-élève-date + légende + synthèses
      if( $this->lignes_hauteur*$lignes_nb > $hauteur_dispo_restante )
      {
        $this->AddPage($this->orientation , 'A4');
      }
      else
      {
        // Interligne
        $this->SetXY( $this->marge_gauche , $this->GetY() + $this->lignes_hauteur*1.5 );
      }
    }
    elseif($this->synthese_modele=='multimatiere')
    {
      // On prend une nouvelle page PDF
      $this->premiere_page();
      if($this->officiel)
      {
        // Ecrire l'en-tête (qui ne dépend pas de la taille de la police calculée ensuite) et récupérer la place requise par cet en-tête.
        extract($tab_infos_entete); // $tab_etabl_coords , $tab_etabl_logo , $etabl_coords_bloc_hauteur , $tab_bloc_titres , $tab_adresse , $tag_date_heure_initiales , $eleve_genre , $date_naissance
        $this->doc_titre = $tab_bloc_titres[0].' - '.$tab_bloc_titres[1];
        // Bloc adresse en positionnement contraint
        if( (is_array($tab_adresse)) && ($this->SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='oui_force') )
        {
          list( $bloc_droite_hauteur , $bloc_gauche_largeur_restante ) = $this->officiel_bloc_adresse_position_contrainte_et_pliures($tab_adresse);
          $this->SetXY( $this->marge_gauche , $this->marge_haut );
        }
        // Bloc établissement
        $bloc_etabl_largeur = (isset($bloc_gauche_largeur_restante)) ? $bloc_gauche_largeur_restante : $this->page_largeur_moins_marges / 2 ;
        $bloc_etabl_hauteur = $this->officiel_bloc_etablissement( $tab_etabl_coords , $tab_etabl_logo , $bloc_etabl_largeur );
        // Bloc titres
        if( (is_array($tab_adresse)) && ($this->SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='oui_force') )
        {
          // En dessous du bloc établissement
          $bloc_titre_largeur = $bloc_etabl_largeur ;
          $this->SetXY( $this->marge_gauche , $this->GetY() + 2 );
          $bloc_titre_hauteur = $this->officiel_bloc_titres( $tab_bloc_titres , $bloc_titre_largeur );
          $bloc_gauche_hauteur = $bloc_etabl_hauteur + 2 + $bloc_titre_hauteur + 2 ;
        }
        else
        {
          // En haut à droite, modulo la place pour le texte indiquant le nombre de pages
          $bloc_titre_largeur = $this->page_largeur_moins_marges / 2;
          $this->SetXY( $this->page_largeur-$this->marge_droite-$bloc_titre_largeur , $this->marge_haut+4 );
          $bloc_titre_hauteur = $this->officiel_bloc_titres( $tab_bloc_titres , $bloc_titre_largeur) + 6;
          $bloc_gauche_hauteur = $bloc_etabl_hauteur ;
          $bloc_droite_hauteur = $bloc_titre_hauteur ; // temporaire, au cas où il n'y aurait pas d'adresse à ajouter
        }
        // Date de naissance + Tag date heure initiales (sous le bloc titres dans toutes les situations)
        $this->officiel_ligne_tag( $eleve_genre , $date_naissance , $eleve_INE , $tag_date_heure_initiales , $bloc_titre_largeur );
        // Bloc adresse en positionnement libre
        if( (is_array($tab_adresse)) && ($this->SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='oui_libre') )
        {
          $bloc_adresse_largeur = $bloc_titre_largeur - 10; // Pour avoir un petit décalage par rapport au bloc titre
          $this->SetXY( $this->page_largeur-$this->marge_droite-$bloc_adresse_largeur , $this->marge_haut+$bloc_titre_hauteur+4 );
          $bloc_adresse_hauteur = $this->officiel_bloc_adresse_position_libre($tab_adresse,$bloc_adresse_largeur);
          $bloc_droite_hauteur = $bloc_titre_hauteur + $bloc_adresse_hauteur ;
        }
        $hauteur_entete = max($bloc_gauche_hauteur,$bloc_droite_hauteur);
      }
      else
      {
        $hauteur_entete = 2*4 ; // HG L1 intitulé L2 période ; HD L1 structure L2 élève classe
      }
      // On calcule la hauteur de la ligne et la taille de la police pour tout faire rentrer sur une page si possible (personnalisée par élève), un minimum de pages sinon
      $hauteur_dispo_par_page = $this->page_hauteur_moins_marges ;
      $lignes_nb = ( $hauteur_entete / 4 ) + $eleve_nb_lignes + ($this->legende*1.5) ; // en-tête + synthèses + légendes
      $hauteur_ligne_minimale = ($this->officiel) ? 4 : 3.5 ;
      $hauteur_ligne_maximale = $hauteur_ligne_minimale + 2;
      $nb_pages = 0;
      do
      {
        $nb_pages++;
        $lignes_rabe = ($nb_pages-1)*10; // Prendre un peu de marge pour tenir compte des sauts de page laissant du blanc en bas de page si une rubrique ne rentre pas.
        $hauteur_ligne_calcule = $nb_pages*$hauteur_dispo_par_page / ($lignes_nb+$lignes_rabe) ;
      }
      while($hauteur_ligne_calcule < $hauteur_ligne_minimale);
      $this->lignes_hauteur = Math::floorTo( $hauteur_ligne_calcule , 0.1 ) ; // valeur approchée au dixième près par défaut
      $this->lignes_hauteur = min ( $this->lignes_hauteur , $hauteur_ligne_maximale ) ;
      $this->taille_police  = $this->lignes_hauteur * 1.6 ; // 5mm de hauteur par ligne donne une taille de 8
      $this->taille_police  = min ( $this->taille_police , 10 ) ;
    }
    if(!$this->officiel)
    {
      extract($tab_infos_entete); // $bilan_titre , $texte_periode , $texte_precision , $groupe_nom
      $this->doc_titre = 'Synthèse '.$bilan_titre.' - '.$texte_periode;
      // Intitulé (dont éventuellement matière) / structure
      $largeur_demi_page = ( $this->page_largeur_moins_marges ) / 2;
      $this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
      $this->Cell( $largeur_demi_page , $this->lignes_hauteur , To::pdf('Synthèse '.$bilan_titre)                     , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
      $this->Cell( $largeur_demi_page , $this->lignes_hauteur , To::pdf($this->SESSION['ETABLISSEMENT_DENOMINATION']) , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*fond*/ );
      // Période / Classe - élève
      $memo_y = $this->GetY();
      $this->SetFont('Arial' , '' , $this->taille_police);
      $this->SetXY( $this->GetX() , $this->GetY() - $this->lignes_hauteur*0.2 );
      $this->Cell( $largeur_demi_page , $this->taille_police*0.8 , To::pdf($texte_periode)   , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
      $this->SetXY( $this->GetX() , $this->GetY() - $this->lignes_hauteur*0.6 );
      $this->Cell( $largeur_demi_page , $this->taille_police*0.8 , To::pdf($texte_precision) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
      $this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
      $this->SetXY( $this->marge_gauche + $largeur_demi_page , $memo_y - $this->lignes_hauteur*0.1 );
      $this->Cell( $largeur_demi_page , $this->lignes_hauteur , To::pdf($this->eleve_nom.' '.$this->eleve_prenom.' ('.$groupe_nom.')') , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*fond*/ );
      if($this->synthese_modele=='matiere')
      {
        $this->SetXY( $this->marge_gauche , $this->GetY() + $this->lignes_hauteur*0.5 );
      }
    }
    else
    {
      $this->SetXY( $this->marge_gauche , $this->marge_haut + $hauteur_entete );
    }
  }

  public function ligne_matiere( $matiere_nom , $lignes_nb , $tab_infos_matiere , $total , $moyenne_eleve , $moyenne_classe , $avec_texte_nombre , $avec_texte_code )
  {
    if($this->synthese_modele=='multimatiere')
    {
      // La hauteur de ligne a déjà été calculée ; mais il reste à déterminer si on saute une page ou non en fonction de la place restante (et sinon => interligne)
      $hauteur_dispo_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas ;
      $test_nouvelle_page = ($this->lignes_hauteur*$lignes_nb > $hauteur_dispo_restante) && ($this->GetY() > $this->lignes_hauteur*5) ; // 2e condition pour éviter un saut de page si déjà en haut (à cause d'une liste à rallonge dans une matière)
      if( $test_nouvelle_page )
      {
        $this->rappel_eleve_page();
      }
      else
      {
        // Interligne
        $nb_lignes_vides = ($this->officiel) ? 1 : 1.5 ;
        $this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*$nb_lignes_vides);
      }
    }
    if(!$this->officiel)
    {
      // Intitulé matière
      $this->SetFont('Arial' , 'B' , $this->taille_police*1.25);
      $couleur_fond = ($this->couleur=='oui') ? 'gris_moyen' : 'blanc' ; // Forcer un fonc blanc en cas d'impression en niveau de gris sinon c'est très confus
      $this->choisir_couleur_fond($couleur_fond);
      $this->CellFit( $this->page_largeur_moins_marges - 80 , $this->lignes_hauteur*1.5 , To::pdf($matiere_nom) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , $this->fond );
      // Proportions acquis matière
      $this->SetFont('Arial' , 'B' , $this->taille_police);
      $this->afficher_proportion_acquis( 80 , $this->lignes_hauteur*1.5 , $tab_infos_matiere , $total , $avec_texte_nombre , $avec_texte_code );
      // Interligne
      $this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*1.5);
    }
    else
    {
      $memo_y = $this->GetY();
      $demi_largeur = ( $this->page_largeur_moins_marges ) / 2 ;
      // Intitulé matière
      $this->SetFont('Arial' , 'B' , $this->taille_police*1.25);
      $couleur_fond = ($this->couleur=='oui') ? 'gris_moyen' : 'blanc' ; // Forcer un fonc blanc en cas d'impression en niveau de gris sinon c'est très confus
      $this->choisir_couleur_fond($couleur_fond);
      $this->CellFit( $demi_largeur , $this->lignes_hauteur*2 , To::pdf($matiere_nom) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , $this->fond );
      // Moyenne élève (éventuelle) et moyenne classe (éventuelle)
      if($this->SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'])
      {
        $nb_lignes_hauteur = 2 - $this->SESSION['OFFICIEL']['BULLETIN_BARRE_ACQUISITIONS'] ;
        $largeur_note = 10;
        $this->Rect( $this->GetX() , $this->GetY() , $demi_largeur , $this->lignes_hauteur*$nb_lignes_hauteur , 'D' /* DrawFill */ );
        $texte = ($this->SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE']) ? 'Moyenne élève (classe) :' : 'Moyenne élève :' ;
        $this->SetFont('Arial' , '' , $this->taille_police);
        $largueur_texte = ($this->SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE']) ? $demi_largeur-2*$largeur_note : $demi_largeur-$largeur_note ;
        $this->Cell( $largueur_texte , $this->lignes_hauteur*$nb_lignes_hauteur , To::pdf($texte) , 0 /*bordure*/ , 0 /*br*/ , 'R' /*alignement*/ , FALSE /*fond*/ );
        $moyenne_eleve = ($moyenne_eleve!==NULL) ? ( ($this->SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? number_format($moyenne_eleve,1,',','') : ($moyenne_eleve*5).'%' ) : '-' ;
        $this->SetFont('Arial' , 'B' , $this->taille_police*1.25);
        $this->Cell( $largeur_note , $this->lignes_hauteur*$nb_lignes_hauteur , To::pdf($moyenne_eleve) , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
        if($this->SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE'])
        {
          $moyenne_classe = ($moyenne_classe!==NULL) ? ( ($this->SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? number_format($moyenne_classe,1,',','') : round($moyenne_classe*5).'%' ) : '-' ;
          $this->SetFont('Arial' , '' , $this->taille_police*0.8);
          $this->Cell( $largeur_note , $this->lignes_hauteur*$nb_lignes_hauteur , To::pdf('('.$moyenne_classe.')') , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
        }
        $this->SetXY($this->marge_gauche + $demi_largeur , $this->GetY() + $this->lignes_hauteur*$nb_lignes_hauteur );
      }
      // Proportions acquis matière
      if($this->SESSION['OFFICIEL']['BULLETIN_BARRE_ACQUISITIONS'])
      {
        $nb_lignes_hauteur = 2 - $this->SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'] ;
        $this->SetFont('Arial' , '' , $this->taille_police);
        $this->afficher_proportion_acquis( $demi_largeur , $this->lignes_hauteur*$nb_lignes_hauteur , $tab_infos_matiere , $total , $avec_texte_nombre , $avec_texte_code );
      }
      // Positionnement
      $this->SetXY( $this->marge_gauche , $memo_y + $this->lignes_hauteur*2 );
    }
  }

  public function ligne_synthese( $synthese_nom , $tab_infos_synthese , $total , $hauteur_ligne_synthese , $avec_texte_nombre , $avec_texte_code )
  {
    $hauteur_ligne = $this->lignes_hauteur * $hauteur_ligne_synthese ;
    $largeur_diagramme = ($this->officiel) ? 20 : 40 ;
    $this->SetFont('Arial' , '' , $this->taille_police*0.8);
    $this->afficher_proportion_acquis( $largeur_diagramme , $hauteur_ligne , $tab_infos_synthese , $total,$avec_texte_nombre , $avec_texte_code );
    $intitule_synthese_largeur = ( ($this->officiel) && ($this->SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE_LONGUEUR']) ) ? ( $this->page_largeur_moins_marges ) / 2 - $largeur_diagramme : $this->page_largeur_moins_marges - $largeur_diagramme ;
    // Intitulé synthèse
    $this->SetFont('Arial' , '' , $this->taille_police);
    $couleur_fond = ($this->couleur=='oui') ? 'gris_clair' : 'blanc' ; // Forcer un fonc blanc en cas d'impression en niveau de gris sinon c'est très confus
    $this->choisir_couleur_fond($couleur_fond);
    $this->CellFit( $intitule_synthese_largeur , $hauteur_ligne , To::pdf($synthese_nom) , 1 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , $this->fond );
  }

  public function appreciation_rubrique( $tab_saisie , $nb_lignes_hauteur )
  {
    $cadre_hauteur = $nb_lignes_hauteur * $this->lignes_hauteur ;
    $demi_largeur = ( $this->page_largeur_moins_marges ) / 2 ;
    $this->SetXY( $this->marge_gauche + $demi_largeur , $this->GetY() - $cadre_hauteur );
    $this->Rect( $this->GetX() , $this->GetY() , $demi_largeur , $cadre_hauteur , 'D' /* DrawFill */ );
    if($tab_saisie!==NULL)
    {
      unset($tab_saisie[0]); // la note
      $memo_y = $this->GetY();
      $this->officiel_bloc_appreciation_intermediaire( $tab_saisie , $demi_largeur , $this->lignes_hauteur , 'bulletin' , $cadre_hauteur );
      $this->SetXY( $this->marge_gauche , $memo_y + $cadre_hauteur );
    }
    else
    {
      $this->SetXY( $this->marge_gauche , $this->GetY() + $cadre_hauteur );
    }
  }

  public function appreciation_generale( $prof_id , $tab_infos , $tab_image_tampon_signature , $nb_lignes_appreciation_generale_avec_intitule , $nb_lignes_assiduite_et_pp_et_message_et_legende , $moyenne_generale_eleve , $moyenne_generale_classe )
  {
    $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
    $hauteur_requise = $this->lignes_hauteur * ( $nb_lignes_appreciation_generale_avec_intitule + $nb_lignes_assiduite_et_pp_et_message_et_legende ) ;
    if($hauteur_requise > $hauteur_restante)
    {
      // Prendre une nouvelle page si ça ne rentre pas, avec recopie de l'identité de l'élève
      $this->rappel_eleve_page();
      $this->SetXY( $this->marge_gauche , $this->GetY() + 2 );
    }
    else
    {
      // Interligne
      $this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*0.5);
    }
    $this->officiel_bloc_appreciation_generale( $prof_id , $tab_infos , $tab_image_tampon_signature , $nb_lignes_appreciation_generale_avec_intitule , $this->page_largeur_moins_marges , $this->lignes_hauteur , $moyenne_generale_eleve , $moyenne_generale_classe );
  }

  public function legende()
  {
    // Légende : en bas de page si 'multimatiere', à la suite si 'matiere'
    $ordonnee = ($this->synthese_modele=='multimatiere') ?  $this->page_hauteur - $this->marge_bas - $this->lignes_hauteur*0.5 : $this->GetY() + $this->lignes_hauteur*0.5 ;
    $this->afficher_legende( 'etat_acquisition' /*type_legende*/ , $ordonnee );
  }

}
?>