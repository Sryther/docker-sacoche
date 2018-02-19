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

// jQuery !
$(document).ready
(
  function()
  {

    /**
     * Ce fichier est associé à releve_html.php (affichage d'un relevé HTML enregistré temporairement).
     * Ces relevés peuvent en effet comporter des éléments dynamiques : cases à cocher et formulaire à soumettre 
     * afin de créer une évaluation ou constituer un groupe de besoin.
     */

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Tableaux à trier
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    if(typeof tri === 'function')
    {
      tri();
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Initialisation
// 
// Pour "Synthèse de maîtrise du socle" il y a un formulaire simplifié
//  (et pour "Recherche ciblée" c'est affiché directement sans passer par releve_html.php)
// Une bonne partie de ce code ne concerne donc que "Grille d'items d'un référentiel" et "Relevé d'items [...]"
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    if($('#span_submit').length)
    {
      $('#form_synthese input[type=checkbox]').css('display','none'); // bcp plus rapide que hide() qd il y a bcp d'éléments
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Voir / masquer tous les détails
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('div.astuce').on
    (
      'click',
      '#montrer_details',
      function()
      {
        $('body').find('a.toggle_plus').click();
        $(this).replaceWith('<a href="#" id="masquer_details">tout masquer</a>');
        return false;
      }
    );

    $('div.astuce').on
    (
      'click',
      '#masquer_details',
      function()
      {
        $('body').find('a.toggle_moins').click();
        $(this).replaceWith('<a href="#" id="montrer_details">tout montrer</a>');
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher / masquer checkbox au changement d'un choix d'action
// 
// .css('display','...') utilisé en remplacement de hide() et show() car plus rapide quand il y a bcp d'éléments.
// Ici, utiliser les fonctions de jQuery ralentissaient Firefox jusqu'à obtenir un boîte d'avertissement.
// 
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_action').change
    (
      function()
      {
        $('#form_synthese input[type=checkbox]').css('display','none');
        $('#check_msg').removeAttr('class').html('');
        var action = $('#f_action option:selected').val();
        if(action=='evaluer_items_perso')
        {
          $('#form_synthese input[name=id_req\\[\\]]').css('display',display_mode);
          $('#span_submit').show(0);
        }
        else if(action=='evaluer_items_commun')
        {
          $('#form_synthese input[name=id_user\\[\\]]').css('display',display_mode);
          $('#form_synthese input[name=id_item\\[\\]]').css('display',display_mode);
          $('#span_submit').show(0);
          
        }
        else if(action=='constituer_groupe_besoin')
        {
          $('#form_synthese input[name=id_user\\[\\]]').css('display',display_mode);
          $('#span_submit').show(0);
        }
        else
        {
          $('#span_submit').hide(0);
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Valider "Grille d'items d'un référentiel" et "Relevé d'items [...]"
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_submit').click
    (
      function()
      {
        var objet = $('#f_action option:selected').val();
        var action = false;
        // evaluer_items_perso
        if(objet=='evaluer_items_perso')
        {
          if( !$('#form_synthese input[name=id_req\\[\\]]:checked').length )
          {
            $('#check_msg').attr('class','alerte').html('Aucune case cochée !');
            return false;
          }
          else
          {
            $('#form_synthese input[name=id_user\\[\\]]').remove();
            $('#form_synthese input[name=id_item\\[\\]]').remove();
            action = 'evaluation_gestion';
          }
        }
        // evaluer_items_commun
        else if(objet=='evaluer_items_commun')
        {
          if( !$('#form_synthese input[name=id_user\\[\\]]:checked').length )
          {
            $('#check_msg').attr('class','alerte').html('Aucun élève coché !');
            return false;
          }
          else if( !$('#form_synthese input[name=id_item\\[\\]]:checked').length )
          {
            $('#check_msg').attr('class','alerte').html('Aucun item coché !');
            return false;
          }
          else
          {
            $('#form_synthese input[name=id_req\\[\\]]').remove();
            action = 'evaluation_gestion';
          }
        }
        // constituer_groupe_besoin
        else if(objet=='constituer_groupe_besoin')
        {
          if( !$('#form_synthese input[name=id_user\\[\\]]:checked').length )
          {
            $('#check_msg').attr('class','alerte').html('Aucun élève coché !');
            return false;
          }
          else
          {
            $('#form_synthese input[name=id_req\\[\\]]').remove();
            $('#form_synthese input[name=id_item\\[\\]]').remove();
            action = 'professeur_groupe_besoin';
          }
        }
        // si ok
        if(action)
        {
          $('#check_msg').removeAttr('class').html('');
          $('#form_synthese').attr( 'action' , './index.php?page='+action );
          $('#form_synthese').submit();
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Valider "Synthèse de maîtrise du socle" (formulaire simplifié)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_synthese button.ajouter').click
    (
      function()
      {
        if( $('#form_synthese input[name=id_user\\[\\]]:checked').length )
        {
          $('#check_msg').removeAttr('class').html('');
          $('#form_synthese').attr( 'action' , './index.php?page='+$(this).attr('name') );
          $('#form_synthese').submit();
        }
        else
        {
          $('#check_msg').attr('class','alerte').html('Aucun élève coché !');
          return false;
        }
      }
    );

  }
);

