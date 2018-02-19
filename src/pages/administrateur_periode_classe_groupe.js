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

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Initialisation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var memo_action = false;

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Alerter au changement d'un élément de formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_select').on
    (
      'change',
      'select, input',
      function()
      {
        $('#ajax_msg').attr('class','alerte').html("Pensez à valider vos modifications !");
      }
    );

    // Réagir au clic sur une image pour reporter des dates

    $('#bilan').on
    (
      'click',
      'q.date_ajouter',
      function()
      {
        texte = $(this).parent().html();
        $("#f_date_debut").val( texte.substring(0,10) );
        $("#f_date_fin").val(  texte.substring(13,23) );
        return false;
      }
    );

    // Réagir au clic sur un bouton (soumission du formulaire)

    $('#ajouter , #retirer').click
    (
      function()
      {
        memo_action = $(this).attr('id');
        if( $("#select_periodes input:checked").length==0 || $("#select_classes_groupes input:checked").length==0 )
        {
          $('#ajax_msg').attr('class','erreur').html("Sélectionnez dans les deux listes !");
          return false;
        }
        if(memo_action=='ajouter')
        {
          if( !test_dateITA( $("#f_date_debut").val() ) )
          {
            $('#ajax_msg').attr('class','erreur').html("Date de début au format JJ/MM/AAAA incorrecte !");
            return false;
          }
          if( !test_dateITA( $("#f_date_fin").val() ) )
          {
            $('#ajax_msg').attr('class','erreur').html("Date de fin au format JJ/MM/AAAA incorrecte !");
            return false;
          }
        }
        if(memo_action=='retirer')
        {
          $.prompt(prompt_etapes_confirmer_suppression);
          return false;
        }
        envoyer_action_confirmee();
      }
    );

    var prompt_etapes_confirmer_suppression = {
      etape_1: {
        title   : 'Demande de confirmation (1/2)',
        html    : "Les bilans officiels associés seront perdus !<br />Pour seulement modifier les dates, il faut utiliser l'autre bouton, au dessus.<br />Souhaitez-vous vraiment supprimer ces associations période(s) / classe(s) groupe(s) ?",
        buttons : {
          "Non, c'est une erreur !" : false ,
          "Oui, je confirme !" : true
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            event.preventDefault();
            $.prompt.goToState('etape_2');
            return false;
          }
        }
      },
      etape_2: {
        title   : 'Demande de confirmation (2/2)',
        html    : "Attention : dernière demande de confirmation !!!<br />Les éventuels bilans officiels associés (bulletins, livrets...) seront supprimés !<br />Avez-bien coché ce que vous souhaitiez, en connaissance de cause ?<br />Est-ce définitivement votre dernier mot ???",
        buttons : {
          "Oui, j'insiste !" : true ,
          "Non, surtout pas !" : false
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            envoyer_action_confirmee();
            return true;
          }
        }
      }
    };

    function envoyer_action_confirmee()
    {
      $('button').prop('disabled',true);
      $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action='+memo_action+'&'+$("#form_select").serialize(),
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('button').prop('disabled',false);
            $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            return false;
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            $('button').prop('disabled',false);
            if(responseJSON['statut']==false)
            {
              $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
            }
            else
            {
              $('#ajax_msg').attr('class','valide').html("Demande réalisée !");
              $('#bilan').html(responseJSON['value']);
            }
          }
        }
      );
    }

    // Initialisation : charger au chargement l'affichage du bilan

    $('#ajax_msg').addClass('loader').html("En cours&hellip;");
    $.ajax
    (
      {
        type : 'POST',
        url : 'ajax.php?page='+PAGE,
        data : 'csrf='+CSRF+'&f_action=initialiser',
        dataType : 'json',
        error : function(jqXHR, textStatus, errorThrown)
        {
          $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
          return false;
        },
        success : function(responseJSON)
        {
          initialiser_compteur();
          if(responseJSON['statut']==false)
          {
            $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
          }
          else
          {
            $('#ajax_msg').removeAttr('class').html("");
            $('#bilan').html(responseJSON['value']);
          }
        }
      }
    );

  }
);
