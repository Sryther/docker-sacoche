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
    // Appel en ajax pour lancer un nettoyage
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_numeroter , #bouton_nettoyer , #bouton_purger , #bouton_supprimer , #bouton_effacer').click
    (
      function()
      {
        memo_action = $(this).attr('id').substring(7); // "nettoyer" ou "purger" ou "supprimer" ou "effacer"
        if(memo_action=='purger')
        {
          var options = (nb_devoirs_annee_scolaire_precedente) ? prompt_etapes_confirmer_purge_une_etape : prompt_etapes_confirmer_purge_deux_etapes ;
          $.prompt(options);
          return false;
        }
        else if(memo_action=='supprimer')
        {
          $.prompt(prompt_etapes_confirmer_suppression);
          return false;
        }
        else
        {
          envoyer_action_confirmee();
        }
      }
    );

    var prompt_etapes_confirmer_purge_une_etape = {
      etape_1: {
        title   : 'Demande de confirmation',
        html    : "Attention : les scores déjà saisis ne seront plus modifiables !<br />Attention : les saisies des bilans officiels seront effacées !<br />Souhaitez-vous vraiment lancer l'initialisation annuelle des données ?",
        buttons : {
          "Non, c'est une erreur !" : false ,
          "Oui, je confirme !" : true
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            envoyer_action_confirmee();
          }
        }
      }
    };

    var prompt_etapes_confirmer_purge_deux_etapes = {
      etape_1: {
        title   : 'Demande de confirmation (1/2)',
        html    : "Votre base ne comporte pas de devoirs des années scolaires précédentes.<br />Une telle initialisation ne semble donc pas justifiée dans votre situation&hellip;<br />Souhaitez-vous vraiment lancer l'initialisation annuelle des données ?",
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
        html    : "Attention : dernière demande de confirmation !!!<br />Les scores déjà saisis ne seront plus modifiables !<br />Les saisies des bilans officiels seront effacées !<br />Est-ce définitivement votre dernier mot ???",
        buttons : {
          "Oui, j'insiste !" : true ,
          "Non, surtout pas !" : false
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            nb_devoirs_annee_scolaire_precedente = 0;
            envoyer_action_confirmee();
            return true;
          }
        }
      }
    };

    var prompt_etapes_confirmer_suppression = {
      etape_1: {
        title   : 'Demande de confirmation (1/2)',
        html    : "Toutes les notes des élèves seront effacées !<br />Souhaitez-vous vraiment supprimer les scores et les validations ?",
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
        html    : "Attention : dernière demande de confirmation !!!<br />Voulez-vous vraiment tout effacer et repartir de zéro ?<br />Est-ce définitivement votre dernier mot ???",
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
      $("button").prop('disabled',true);
      $("label").removeAttr('class').html('');
      $('#ajax_msg_'+memo_action).addClass('loader').html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action='+memo_action,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $("button").prop('disabled',false);
            $('#ajax_msg_'+memo_action).attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            return false;
          },
          success : function(responseJSON)
          {
            $("button").prop('disabled',false);
            if(responseJSON['statut']==false)
            {
              $('#ajax_msg_'+memo_action).attr('class','alerte').html(responseJSON['value']);
            }
            else
            {
              $('#ajax_msg_'+memo_action).removeAttr('class').html('');
              $.fancybox( '<ul class="puce">'+responseJSON['value']+'</ul>' , {'centerOnScroll':true} );
              initialiser_compteur();
            }
          }
        }
      );
    }

  }
);
