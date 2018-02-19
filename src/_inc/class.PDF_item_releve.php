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

class PDF_item_releve extends PDF
{

  // initialiser()         --> c'est là que les calculs se font pour une sortie "matiere" ou "selection" ou "professeur"
  // entete_format_eleve() --> c'est là que les calculs se font pour une sortie "multimatiere"

  private function premiere_page()
  {
    $this->AddPage($this->orientation , 'A4');
    $this->page_numero_first = $this->page;
    $this->choisir_couleur_texte('gris_fonce');
    $this->SetFont('Arial' , '' , 7);
    $this->Cell( $this->page_largeur_moins_marges , 4 /*ligne_hauteur*/ , To::pdf('Page 1/'.$this->page_nombre_alias) , 0 /*bordure*/ , 1 /*br*/ , $this->page_nombre_alignement , FALSE /*fond*/ );
    $this->choisir_couleur_texte('noir');
    $this->SetXY($this->marge_gauche,$this->marge_haut);
  }

  private function rappel_eleve_page()
  {
    $this->AddPage($this->orientation , 'A4');
    $page_numero = $this->page - $this->page_numero_first + 1 ;
    $this->choisir_couleur_texte('gris_fonce');
    $this->SetFont('Arial' , '' , 7);
    $this->Cell( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($this->doc_titre.' - '.$this->eleve_nom.' '.$this->eleve_prenom.' - Page '.$page_numero.'/'.$this->page_nombre_alias) , 0 /*bordure*/ , 1 /*br*/ , $this->page_nombre_alignement , FALSE /*fond*/ );
    $this->choisir_couleur_texte('noir');
  }

  public function initialiser( $releve_modele , $releve_individuel_format , $aff_etat_acquisition , $aff_anciennete_notation , $longueur_ref_max , $cases_nb , $cases_largeur , $lignes_nb , $eleves_ou_items_nb , $pages_nb_methode )
  {
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->SetAutoPageBreak(FALSE);
    $this->releve_modele           = $releve_modele;
    $this->releve_format           = $releve_individuel_format;
    $this->cases_nb                = $cases_nb;
    $this->cases_largeur           = $cases_largeur;
    $this->colonne_bilan_largeur   = ($aff_etat_acquisition) ? $this->cases_largeur : 0 ;
    $this->reference_largeur       = ($releve_individuel_format=='eleve') ? ( ($longueur_ref_max) ? ceil($longueur_ref_max*1.7) : 0 ) : 0 ;
    $this->synthese_largeur        = $this->page_largeur_moins_marges - $this->reference_largeur;
    $this->intitule_largeur        = $this->synthese_largeur - ( $this->cases_nb * $this->cases_largeur ) - $this->colonne_bilan_largeur;
    $this->legende_deja_affichee   = FALSE; // Si multimatières, on n'est pas certain qu'il y ait la place pour la légende en dernière page, alors on la met dès que possible
    $this->legende_nb_lignes       = 1 + (int)$aff_anciennete_notation + (int)$aff_etat_acquisition ;
    $this->aff_codes_notation      = TRUE;
    $this->aff_anciennete_notation = $aff_anciennete_notation;
    $this->aff_etat_acquisition    = $aff_etat_acquisition;
    if($this->releve_format=='item')
    {
      $items_nb = $eleves_ou_items_nb;
      // Dans ce cas on met plusieurs items par page : on calcule maintenant combien et la hauteur de ligne à prendre
      $hauteur_dispo_par_page   = $this->page_hauteur_moins_marges ;
      $lignes_nb_tous_items     = ( 1 + 1 + 2 ) + ($lignes_nb*1.1) + ($this->legende*$this->legende_nb_lignes) ; // [ intitulé-matiere-structure + classe-date + marge ] + [ lignes dont résumés et intitulé item ; x1.1 ] + légende
      $hauteur_ligne_moyenne    = 5;
      $lignes_nb_moyen_par_page = $hauteur_dispo_par_page / $hauteur_ligne_moyenne ;
      $nb_page_moyen            = max( 1 , round( $lignes_nb_tous_items / $lignes_nb_moyen_par_page ) ); // max 1 pour éviter une division par zéro
      $items_nb_par_page        = ceil( $items_nb / $nb_page_moyen ) ;
      if($pages_nb_methode=='augmente')
      {
        $items_nb_par_page = ($items_nb_par_page>1) ? $items_nb_par_page-1 : 0.5 ;
      }
      // $nb_page_calcule = ceil( $items_nb / $items_nb_par_page ) ; // devenu inutile
      $lignes_nb_moyen_item       = $lignes_nb_tous_items / $items_nb ;
      $lignes_nb_calcule_par_page = $items_nb_par_page * $lignes_nb_moyen_item ; // $lignes_nb/$nb_page_calcule ne va pas car un item peut alors être considéré à cheval sur 2 pages
      $hauteur_ligne_calcule      = $hauteur_dispo_par_page / $lignes_nb_calcule_par_page ;
      $this->lignes_hauteur = Math::floorTo( $hauteur_ligne_calcule , 0.1 ) ; // valeur approchée au dixième près par défaut
      $this->lignes_hauteur = min ( $this->lignes_hauteur , 7.5 ) ;
      // On s'occupe aussi maintenant de la taille de la police
      $this->taille_police  = $this->lignes_hauteur * 1.6 ; // 5mm de hauteur par ligne donne une taille de 8
      $this->taille_police  = min ( $this->taille_police , 10 ) ;
      // Pour forcer à prendre une nouvelle page au 1er élève
      $this->SetXY(0,$this->page_hauteur);
    }
    else if( ($this->releve_modele!='multimatiere') )
    {
      $eleves_nb = $eleves_ou_items_nb;
      // Dans ce cas on met plusieurs élèves par page : on calcule maintenant combien et la hauteur de ligne à prendre
      $hauteur_dispo_par_page   = $this->page_hauteur_moins_marges ;
      $lignes_nb_tous_eleves    = $eleves_nb * ( 1 + 1 + ($this->legende*$this->legende_nb_lignes) + 2 ) + $lignes_nb ; // eleves * [ intitulé-matiere-structure + classe-élève-date + légendes + marge ] + lignes dont résumés
      $hauteur_ligne_moyenne    = 5;
      $lignes_nb_moyen_par_page = $hauteur_dispo_par_page / $hauteur_ligne_moyenne ;
      $nb_page_moyen            = max( 1 , round( $lignes_nb_tous_eleves / $lignes_nb_moyen_par_page ) ); // max 1 pour éviter une division par zéro
      $eleves_nb_par_page       = ceil( $eleves_nb / $nb_page_moyen ) ;
      if($pages_nb_methode=='augmente')
      {
        $eleves_nb_par_page = ($eleves_nb_par_page>1) ? $eleves_nb_par_page-1 : 0.5 ;
      }
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
      $this->SetXY(0,$this->page_hauteur);
    }
    // Hauteur d'une case
    $this->cases_hauteur = $this->lignes_hauteur;
    $this->calculer_dimensions_images($this->cases_largeur,$this->cases_hauteur);
  }

  public function entete_format_eleve( $pages_nb_methode , $tab_infos_entete , $eleve_nom , $eleve_prenom , $eleve_INE , $eleve_nb_lignes )
  {
    $this->eleve_nom    = $eleve_nom;
    $this->eleve_prenom = $eleve_prenom;
    if( ($this->releve_modele!='multimatiere') )
    {
      // La hauteur de ligne a déjà été calculée ; mais il reste à déterminer si on saute une page ou non en fonction de la place restante (et sinon => interligne)
      $hauteur_dispo_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas ;
      $lignes_nb = 1 + 1 + $eleve_nb_lignes + ($this->legende*$this->legende_nb_lignes) + 2 ; // intitulé-matiere-structure + classe-élève-date + lignes dont résumés + légendes + marge
      if($this->lignes_hauteur*$lignes_nb > $hauteur_dispo_restante)
      {
        $this->AddPage($this->orientation , 'A4');
      }
      else
      {
        // Interligne
        $this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*2);
      }
      extract($tab_infos_entete); // $bilan_titre , $texte_periode , $groupe_nom
    }
    elseif($this->releve_modele=='multimatiere')
    {
      // On prend une nouvelle page PDF
      $this->premiere_page();
      $this->legende_deja_affichee = FALSE; // Si multimatières, on n'est pas certain qu'il y ait la place pour la légende en dernière page, alors on la met dès que possible
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
        extract($tab_infos_entete); // $bilan_titre , $texte_periode , $groupe_nom
        $this->doc_titre = 'Bilan '.$bilan_titre.' - '.$texte_periode;
        $hauteur_entete = 2*4 ; // HG L1 intitulé L2 période ; HD L1 structure L2 élève classe
      }
      // On calcule la hauteur de la ligne et la taille de la police pour tout faire rentrer sur une page si possible (personnalisée par élève), un minimum de pages sinon
      $hauteur_dispo_par_page = $this->page_hauteur_moins_marges ;
      $lignes_nb = ( $hauteur_entete / 3 ) + $eleve_nb_lignes + ($this->legende*$this->legende_nb_lignes) ; // en-tête + matières(marge+intitulé) & lignes dont résumés + légendes
      $hauteur_ligne_minimale = ($this->officiel) ? 3.5 : 3 ;
      $hauteur_ligne_maximale = $hauteur_ligne_minimale + 2;
      $nb_pages = 0;
      do
      {
        $nb_pages++;
        $lignes_nb += 1; // report infos élève
        $hauteur_ligne_calcule = $nb_pages*$hauteur_dispo_par_page / $lignes_nb ;
      }
      while($hauteur_ligne_calcule < $hauteur_ligne_minimale);
      if($pages_nb_methode=='augmente')
      {
        $nb_pages++;
        $hauteur_ligne_calcule = $nb_pages*$hauteur_dispo_par_page / $lignes_nb ;
      }
      $this->lignes_hauteur = Math::floorTo( $hauteur_ligne_calcule , 0.1 ) ; // valeur approchée au dixième près par défaut
      $this->lignes_hauteur = min ( $this->lignes_hauteur , $hauteur_ligne_maximale ) ;
      $this->taille_police  = $this->lignes_hauteur * 1.6 ; // 5mm de hauteur par ligne donne une taille de 8
      $this->taille_police  = min ( $this->taille_police , 10 ) ;
      // Hauteur d'une case
      $this->cases_hauteur = $this->lignes_hauteur;
      $this->calculer_dimensions_images($this->cases_largeur,$this->cases_hauteur);
    }
    if(!$this->officiel)
    {
      // Intitulé (dont éventuellement matière) / structure
      $largeur_demi_page = ( $this->page_largeur_moins_marges ) / 2;
      $this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
      $this->CellFit($largeur_demi_page , $this->lignes_hauteur , To::pdf('Bilan '.$bilan_titre)                        , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
      $this->CellFit($largeur_demi_page , $this->lignes_hauteur , To::pdf($this->SESSION['ETABLISSEMENT_DENOMINATION']) , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*fond*/ );
      // Période / Classe - élève
      $this->SetFont('Arial' , '' , $this->taille_police);
      $this->CellFit($largeur_demi_page , $this->taille_police*0.8 , To::pdf($texte_periode) , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
      $this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
      $this->CellFit($largeur_demi_page , $this->lignes_hauteur , To::pdf($this->eleve_nom.' '.$this->eleve_prenom.' ('.$groupe_nom.')') , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*fond*/ );
      if( ($this->releve_modele!='multimatiere') )
      {
        $this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*0.5);
      }
    }
    else
    {
      $this->SetXY($this->marge_gauche,$this->marge_haut+$hauteur_entete);
    }
  }

  public function entete_format_item( $bilan_titre , $texte_periode , $groupe_nom )
  {
    // On prend une nouvelle page PDF
    $this->AddPage($this->orientation , 'A4');
    $this->choisir_couleur_texte('noir');
    $this->SetXY($this->marge_gauche,$this->marge_haut);
    // Intitulé (dont éventuellement matière) / structure
    $largeur_demi_page = ( $this->page_largeur_moins_marges ) / 2;
    $this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
    $this->CellFit($largeur_demi_page , $this->lignes_hauteur , To::pdf('Bilan '.$bilan_titre)                        , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    $this->CellFit($largeur_demi_page , $this->lignes_hauteur , To::pdf($this->SESSION['ETABLISSEMENT_DENOMINATION']) , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*fond*/ );
    // Période / Classe
    $this->SetFont('Arial' , '' , $this->taille_police);
    $this->CellFit($largeur_demi_page , $this->taille_police*0.8 , To::pdf($texte_periode) , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    $this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
    $this->CellFit($largeur_demi_page , $this->lignes_hauteur , To::pdf($groupe_nom) , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*fond*/ );
    $this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*0.5);
  }

  public function transdisciplinaire_ligne_matiere( $matiere_nom , $matiere_lignes_nb , $cases_nb=NULL , $cases_largeur=NULL )
  {
    // Pendant des années le nombre de colonnes était fixé au départ pour tout le document, il est devenu automatique après
    if( !is_null($cases_nb) && !is_null($cases_largeur) )
    {
      $modif = FALSE;
      if($cases_nb!==$this->cases_nb)
      {
        $this->cases_nb                = $cases_nb;
        $modif = TRUE;
      }
      if($cases_largeur!==$this->cases_largeur)
      {
        $this->cases_largeur           = $cases_largeur;
        $this->colonne_bilan_largeur   = ($this->aff_etat_acquisition) ? $this->cases_largeur : 0 ;
        $this->calculer_dimensions_images($this->cases_largeur,$this->cases_hauteur);
        $modif = TRUE;
      }
      if($modif)
      {
        $this->intitule_largeur        = $this->synthese_largeur - ( $this->cases_nb * $this->cases_largeur ) - $this->colonne_bilan_largeur;
      }
    }
    // La hauteur de ligne a déjà été calculée ; mais il reste à déterminer si on saute une page ou non en fonction de la place restante (et sinon => interligne)
    $matiere_lignes_nb = 1.5 + $matiere_lignes_nb ; // matière(marge+intitulé) + lignes dont résumés (on ne compte pas la légende)
    $hauteur_dispo_restante          = $this->page_hauteur - $this->GetY() - $this->marge_bas ;
    $test_manque_place_page_courante = ($this->lignes_hauteur*$matiere_lignes_nb > $hauteur_dispo_restante);
    $test_pas_deja_en_haut_de_page   = ($this->GetY() > $this->marge_haut+$this->lignes_hauteur*8); // pour éviter un saut de page si déjà en haut (à cause d'une liste à rallonge dans une matière)
    $test_place_sur_page_entiere     = ($this->lignes_hauteur*$matiere_lignes_nb < $this->page_hauteur_moins_marges); // pas la peine de sauter une page si de toute façon ça ne rentre pas sur une page
    $test_nouvelle_page = $test_manque_place_page_courante && $test_pas_deja_en_haut_de_page && $test_place_sur_page_entiere ;
    if( $test_nouvelle_page )
    {
      if( ($this->legende) && (!$this->legende_deja_affichee) )
      {
         // Si multimatières, on n'est pas certain qu'il y ait la place pour la légende en dernière page, alors on la met dès que possible
        $test_place_legende = ($this->lignes_hauteur*$this->legende_nb_lignes*0.9 < $hauteur_dispo_restante) ;
        if( $test_place_legende )
        {
          $this->legende();
          $this->legende_deja_affichee = TRUE;
        }
      }
    }
    else
    {
      // Interligne
      $this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*0.5);
    }
    // Intitulé matière + éventuellement rappel élève
    if( $test_nouvelle_page )
    {
      $this->rappel_eleve_page();
    }
    $this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
    $this->Cell($this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($matiere_nom) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
  }

  public function format_item_ligne_item( $item_texte , $lignes_nb , $cases_nb=NULL , $cases_largeur=NULL )
  {
    // Pendant des années le nombre de colonnes était fixé au départ pour tout le document, il est devenu automatique après
    if( !is_null($cases_nb) && !is_null($cases_largeur) )
    {
      $modif = FALSE;
      if($cases_nb!==$this->cases_nb)
      {
        $this->cases_nb                = $cases_nb;
        $modif = TRUE;
      }
      if($cases_largeur!==$this->cases_largeur)
      {
        $this->cases_largeur           = $cases_largeur;
        $this->colonne_bilan_largeur   = ($this->aff_etat_acquisition) ? $this->cases_largeur : 0 ;
        $this->calculer_dimensions_images($this->cases_largeur,$this->cases_hauteur);
        $modif = TRUE;
      }
      if($modif)
      {
        $this->intitule_largeur        = $this->synthese_largeur - ( $this->cases_nb * $this->cases_largeur ) - $this->colonne_bilan_largeur;
      }
    }
    // La hauteur de ligne a déjà été calculée ; mais il reste à déterminer si on saute une page ou non en fonction de la place restante (et sinon => interligne)
    $hauteur_dispo_restante          = $this->page_hauteur - $this->GetY() - $this->marge_bas ;
    $test_manque_place_page_courante = ($this->lignes_hauteur*$lignes_nb > $hauteur_dispo_restante);
    $test_pas_deja_en_haut_de_page   = ($this->GetY() > $this->marge_haut+$this->lignes_hauteur*8); // pour éviter un saut de page si déjà en haut (à cause d'une liste à rallonge pour un item)
    $test_place_sur_page_entiere     = ($this->lignes_hauteur*$lignes_nb < $this->page_hauteur_moins_marges); // pas la peine de sauter une page si de toute façon ça ne rentre pas sur une page
    $test_nouvelle_page = $test_manque_place_page_courante && $test_pas_deja_en_haut_de_page && $test_place_sur_page_entiere ;
    if( $test_nouvelle_page )
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
    }
    else
    {
      // Interligne
      $this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*0.5);
    }
    if( $test_nouvelle_page ) /*************************************************************************************************/
    {
      $this->AddPage($this->orientation , 'A4');
      $this->SetXY( $this->marge_gauche , $this->GetY() + 2 );
    }
    // Texte item
    $this->SetFont('Arial' , 'B' , $this->taille_police*1.25);
    $this->choisir_couleur_fond('gris_clair');
    $this->CellFit($this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($item_texte) , 1 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , $this->fond );
    $this->choisir_couleur_fond('blanc');
  }

  public function appreciation_rubrique($tab_saisie)
  {
    $this->SetXY( $this->marge_gauche + $this->reference_largeur , $this->GetY() );
    $this->officiel_bloc_appreciation_intermediaire( $tab_saisie , $this->synthese_largeur , $this->lignes_hauteur , 'releve' );
  }

  public function appreciation_generale( $prof_id , $tab_infos , $tab_image_tampon_signature , $nb_lignes_appreciation_generale_avec_intitule , $nb_lignes_assiduite_et_pp_et_message_et_legende )
  {
    $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
    $hauteur_requise = $this->lignes_hauteur * ( $nb_lignes_appreciation_generale_avec_intitule + $nb_lignes_assiduite_et_pp_et_message_et_legende ) ;
    if($hauteur_requise > $hauteur_restante)
    {
      // Prendre une nouvelle page si ça ne rentre pas, avec recopie de l'identité de l'élève
      $this->rappel_eleve_page();
      $this->SetXY( $this->marge_gauche+$this->reference_largeur , $this->GetY() + 2 );
    }
    else
    {
      // Interligne
      $this->SetXY($this->marge_gauche+$this->reference_largeur , $this->GetY() + $this->lignes_hauteur*0.5);
    }
    $this->officiel_bloc_appreciation_generale( $prof_id , $tab_infos , $tab_image_tampon_signature , $nb_lignes_appreciation_generale_avec_intitule , $this->synthese_largeur , $this->cases_hauteur , NULL /*moyenne_generale_eleve*/ , NULL /*moyenne_generale_classe*/ );
  }

  public function debut_ligne_item( $item_ref , $item_texte )
  {
    $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
    $hauteur_requise = $this->lignes_hauteur;
    if($hauteur_requise > $hauteur_restante)
    {
      // Prendre une nouvelle page si ça ne rentre pas, avec recopie de l'identité de l'élève (il y a des bilans avec tellement d'items qu'il faut aussi mettre le test ici...)
      $this->rappel_eleve_page();
      $this->SetXY( $this->marge_gauche , $this->GetY() + 2 );
    }
    if($this->reference_largeur)
    {
      list($ref_matiere,$ref_suite) = explode('.',$item_ref,2);
      $this->choisir_couleur_fond('gris_clair');
      $this->SetFont('Arial' , '' , $this->taille_police*0.8);
      $this->CellFit( $this->reference_largeur , $this->cases_hauteur , To::pdf($ref_suite) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , $this->fond );
    }
    $this->SetFont('Arial' , '' , $this->taille_police);
    $this->CellFit( $this->intitule_largeur , $this->cases_hauteur , To::pdf($item_texte) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
  }

  public function debut_ligne_eleve( $eleve_texte )
  {
    $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
    $hauteur_requise = $this->lignes_hauteur;
    if($hauteur_requise > $hauteur_restante) /*************************************************************************************************/
    {
      // Prendre une nouvelle page si ça ne rentre pas (il y a des bilans avec tellement d'élèves qu'il faut aussi mettre le test ici...)
      $this->AddPage($this->orientation , 'A4');
      $this->SetXY( $this->marge_gauche , $this->GetY() + 2 );
    }
    $this->choisir_couleur_fond('blanc');
    $this->SetFont('Arial' , '' , $this->taille_police);
    $this->CellFit( $this->intitule_largeur , $this->cases_hauteur , To::pdf($eleve_texte) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
  }

  public function passage_ligne_suivante()
  {
    $this->SetXY( $this->marge_gauche , $this->GetY() + $this->cases_hauteur );
  }

  public function ligne_synthese($bilan_texte)
  {
    $this->SetFont('Arial' , '' , $this->taille_police);
    $this->choisir_couleur_fond('gris_moyen');
    if($this->reference_largeur) // Parce que sinon ça ne plait pas à Cell().
    {
      $this->Cell( $this->reference_largeur , $this->cases_hauteur , ''                    , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
    }
    $this->Cell( $this->synthese_largeur  , $this->cases_hauteur , To::pdf($bilan_texte) , 1 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , $this->fond );
  }

  public function legende()
  {
    if(!$this->legende_deja_affichee)
    {
      // Légende : à la suite si 'matiere' ou 'selection' ou 'professeur' , en bas de page si 'multimatiere',
      $ordonnee = ( ($this->releve_modele!='multimatiere') ) ? $this->GetY() + $this->lignes_hauteur*0.2 : $this->page_hauteur - $this->marge_bas - $this->lignes_hauteur*$this->legende_nb_lignes*0.9 ;
      if($this->aff_codes_notation)      { $this->afficher_legende( 'codes_notation'      /*type_legende*/ , $ordonnee     /*ordonnée*/ ); } /*toujours TRUE*/
      if($this->aff_anciennete_notation) { $this->afficher_legende( 'anciennete_notation' /*type_legende*/ , $this->GetY() /*ordonnée*/ ); }
      if($this->aff_etat_acquisition)    { $this->afficher_legende( 'score_bilan'         /*type_legende*/ , $this->GetY() /*ordonnée*/ ); }
    }
  }

}
?>