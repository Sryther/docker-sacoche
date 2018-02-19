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
    // Alerter sur la nécessité de valider
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#form_debug input").change
    (
      function()
      {
        $('#ajax_debug').attr('class','alerte').html("Enregistrer pour confirmer.");
      }
    );

    $("#form_phpCAS input").change
    (
      function()
      {
        $('#ajax_phpCAS').attr('class','alerte').html("Enregistrer pour confirmer.");
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Modifier les paramètres de debug
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_debug').click
    (
      function()
      {
        $('#bouton_debug').prop('disabled',true);
        $('#ajax_debug').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=modifier_debug'+'&'+$("form").serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#bouton_debug').prop('disabled',false);
              $('#ajax_debug').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              $('#bouton_debug').prop('disabled',false);
              if(responseJSON['statut']==true)
              {
                $('#ajax_debug').attr('class','valide').html('Choix enregistrés.');
                initialiser_compteur();
              }
              else
              {
                $('#ajax_debug').attr('class','alerte').html(responseJSON['value']);
              }
              return false;
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Modifier les paramètres des logs phpCAS
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_phpCAS').click
    (
      function()
      {
        $('#bouton_phpCAS').prop('disabled',true);
        $('#ajax_phpCAS').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=modifier_phpCAS'+'&'+$('#form_phpCAS').serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#bouton_phpCAS').prop('disabled',false);
              $('#ajax_phpCAS').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              $('#bouton_phpCAS').prop('disabled',false);
              if(responseJSON['statut']==true)
              {
                initialiser_compteur();
                $('#ajax_phpCAS').attr('class','valide').html('Choix enregistrés.');
              }
              else
              {
                $('#ajax_phpCAS').attr('class','alerte').html(responseJSON['value']);
              }
              return false;
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Intercepter la touche entrée pour éviter une soumission d'un formulaire sans contrôle
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_phpCAS').submit
    (
      function()
      {
        $("#bouton_phpCAS").click();
        return false;
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Voir | Effacer un fichier de logs de phpCAS
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#fichiers_logs q').click
    (
      function()
      {
        var f_action  = $(this).attr('class');
        var f_fichier = $(this).parent().attr('id');
        $.fancybox( '<label class="loader">'+'En cours&hellip;'+'</label>' , {'centerOnScroll':true} );
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+f_action+'&f_fichier='+f_fichier,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+'</label>' , {'centerOnScroll':true} );
              return false;
            },
            success : function(responseJSON)
            {
              if(responseJSON['statut']==false)
              {
                $.fancybox( '<label class="alerte">'+responseJSON['value']+'</label>' , {'centerOnScroll':true} );
              }
              else if(f_action=='supprimer')
              {
                initialiser_compteur();
                $('#'+f_fichier).remove();
                $.fancybox.close();
              }
              else if(f_action=='voir')
              {
                initialiser_compteur();
                // Mis dans le div bilan et pas balancé directement dans le fancybox sinon la mise en forme des liens nécessite un peu plus de largeur que le fancybox ne recalcule pas (et $.fancybox.update(); ne change rien).
                // Malgré tout, pour Chrome par exemple, la largeur est mal calculée et provoque des retours à la ligne, d'où le minWidth ajouté.
                $('#bilan').html(responseJSON['value']);
                $.fancybox( { 'href':'#bilan' , onClosed:function(){$('#bilan').html("");} , 'centerOnScroll':true , 'minWidth':300 } );
              }
              return false;
            }
          }
        );
      }
    );

  }
);
