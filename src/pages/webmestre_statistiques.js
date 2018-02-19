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

    var listing_id = new Array();

    // tri du tableau (avec jquery.tablesorter.js).
    $('#table_action').tablesorter({ headers:{0:{sorter:false}} });
    var tableau_tri = function(){ $('#table_action').trigger( 'sorton' , [ [[1,0]] ] ); };
    var tableau_maj = function(){ $('#table_action').trigger( 'update' , [ true ] ); };
    tableau_tri();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Formulaire et traitement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_valider').click
    (
      function()
      {
        // grouper le select multiple
        if( $("#f_base input:checked").length==0 )
        {
          $('#ajax_msg').attr('class','erreur').html("Sélectionnez au moins un établissement !");
          return false;
        }
        else
        {
          // Grouper les checkbox dans un champ unique afin d'éviter tout problème avec une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque) ou "max input vars" généralement fixé à 1000.
          var f_listing_id = new Array(); $("#f_base input:checked").each(function(){f_listing_id.push($(this).val());});
        }
        // on envoie
        $('#bouton_valider').prop('disabled',true);
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=calculer'+'&f_listing_id='+f_listing_id,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#bouton_valider').prop('disabled',false);
              $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==false)
              {
                $('#bouton_valider').prop('disabled',false);
                $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                var max = responseJSON['value'];
                $('#ajax_msg1').attr('class','loader').html('Structures à l\'étude : étape 1 sur ' + max + '...');
                $('#ajax_msg2').html('Ne pas interrompre la procédure avant la fin du traitement !');
                $('#ajax_num').html(1);
                $('#ajax_max').html(max);
                $('#ajax_info').show('fast');
                $('#structures').hide('fast');
                $('#table_action tbody').html('');
                $('#table_action tfoot').html('');
                calculer();
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Etapes de calcul des statistiques
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function calculer()
    {
      var num = parseInt( $('#ajax_num').html() , 10 );
      var max = parseInt( $('#ajax_max').html() , 10 );
      // Appel en ajax
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=calculer'+'&num='+num+'&max='+max,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg1').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            $('#ajax_msg2').html('<a id="a_reprise" href="#">Reprendre la procédure à l\'étape ' + num + ' sur ' + max + '.</a>');
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==false)
            {
              $('#ajax_msg1').attr('class','alerte').html(responseJSON['value']);
              $('#ajax_msg2').html('<a id="a_reprise" href="#">Reprendre la procédure à l\'étape ' + num + ' sur ' + max + '.</a>');
            }
            else
            {
              var ligne = responseJSON['value'];
              num++;
              if(num > max)  // Utilisation de parseInt obligatoire sinon la comparaison des valeurs pose ici pb
              {
                $('#table_action tfoot').append(ligne);
                $('#ajax_msg1').attr('class','valide').html('Calcul des statistiques terminé.');
                $('#ajax_msg2').html('');
                tableau_maj();
                $('#structures').show('fast');
                $('#ajax_info').hide('fast');
                $('#bouton_valider').prop('disabled',false);
                $('#ajax_msg').removeAttr('class').html("");
              }
              else
              {
                $('#table_action tbody').append(ligne);
                $('#ajax_num').html(num);
                $('#ajax_msg1').attr('class','loader').html('Structures à l\'étude : étape ' + num + ' sur ' + max + '...');
                $('#ajax_msg2').html('Ne pas interrompre la procédure avant la fin du traitement !');
                calculer();
              }
            }
          }
        }
      );
    }

    $('#ajax_msg2').on
    (
      'click',
      '#a_reprise',
      function()
      {
        num = $('#ajax_num').html();
        max = $('#ajax_max').html();
        $('#ajax_msg1').attr('class','loader').html('Structures à l\'étude : étape ' + num + ' sur ' + max + '...');
        $('#ajax_msg2').html('Ne pas interrompre la procédure avant la fin du traitement !');
        calculer();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Initialisation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    if( $('#f_base input:checked').length )
    {
      $('#bouton_valider').click();
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Tout cocher ou tout décocher
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on
    (
      'click',
      'q.cocher_tout , q.cocher_rien',
      function()
      {
        var etat = ( $(this).attr('class').substring(7) == 'tout' ) ? true : false ;
        $('#table_action td.nu input[type=checkbox]').prop('checked',etat);
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur un bouton pour effectuer une action sur les structures cochées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var prompt_etapes_supprimer_cochees = {
      etape_1: {
        title   : 'Demande de confirmation (1/2)',
        html    : "Souhaitez-vous vraiment supprimer les bases des structures cochées ?<br />Toutes les données associées seront perdues !",
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
        html    : "Êtes-vous bien certain de vouloir supprimer ces bases ?<br />Est-ce définitivement votre dernier mot ???",
        buttons : {
          "Oui, j'insiste !" : true ,
          "Non, surtout pas !" : false
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            supprimer_structures_cochees(listing_id);
            return true;
          }
        }
      }
    };

    var supprimer_structures_cochees = function(listing_id)
    {
      $("button").prop('disabled',true);
      $('#ajax_supprimer').attr('class','loader').html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=supprimer'+'&f_listing_id='+listing_id,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_supprimer').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            $("button").prop('disabled',false);
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==false)
            {
              $('#ajax_supprimer').attr('class','alerte').html(responseJSON['value']);
            }
            else
            {
              $("#table_action input[type=checkbox]:checked").each
              (
                function()
                {
                  $('#f_base option[value='+$(this).val()+']').parent().remove();
                  $(this).parent().parent().remove();
                }
              );
              $('#ajax_supprimer').attr('class','valide').html('Demande réalisée !');
              $("button").prop('disabled',false);
            }
          }
        }
      );
    };

    $('#zone_actions button').click
    (
      function()
      {
        // Grouper les checkbox dans un champ unique afin d'éviter tout problème avec une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque) ou "max input vars" généralement fixé à 1000.
        listing_id = [];
        $("#table_action input[type=checkbox]:checked").each(function(){listing_id.push($(this).val());});
        if(!listing_id.length)
        {
          $('#ajax_supprimer').attr('class','erreur').html("Aucune structure cochée !");
          return false;
        }
        $('#ajax_supprimer').removeAttr('class').html('&nbsp;');
        var id = $(this).attr('id');
        if(id=='bouton_supprimer')
        {
          $.prompt(prompt_etapes_supprimer_cochees);
        }
        else
        {
          $('#listing_ids').val(listing_id);
          var tab = new Array;
          tab['bouton_newsletter'] = "webmestre_newsletter";
          // tab['bouton_stats']      = "webmestre_statistiques";
          tab['bouton_transfert']  = "webmestre_structure_transfert";
          var page = tab[id];
          var form = document.getElementById('structures');
          form.action = './index.php?page='+page;
          form.method = 'post';
          // form.target = '_blank';
          form.submit();
        }
      }
    );

  }
);
