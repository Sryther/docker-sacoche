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

    var taux_opacite = 0.3;
    var is_modif = false;

    $("input[name^=menu_]:not(:checked)"    ).each( function(){$(this).parent().parent().css('opacity',taux_opacite);} );
    $("input[name^=sousmenu_]:not(:checked)").each( function(){$(this).parent().parent().css('opacity',taux_opacite);} );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Réagir au clic sur un bouton checkbox
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

   // Actualiser l'opacité d'un menu et de ses sous-menus
    $("input[name^=menu_]").change
    (
      function()
      {
        if( $(this).is(':checked') )
        {
          $(this).parent().parent().css('opacity',1);
          $(this).parent().next().find('li').css('opacity',1);
          $(this).parent().next().find('input[name^=sousmenu_]').prop('checked',true);
        }
        else
        {
          $(this).parent().parent().css('opacity',taux_opacite);
          $(this).parent().next().find('li').css('opacity',taux_opacite);
          $(this).parent().next().find('input[name^=sousmenu_]').prop('checked',false);
          $(this).parent().next().find('input[name^=favori_]'  ).prop('checked',false);
        }
      }
    );

   // Actualiser l'opacité d'un sous-menu
   $("input[name^=sousmenu_]").change
    (
      function()
      {
        if( $(this).is(':checked') )
        {
          if( $(this).parent().parent().parent().parent().css('opacity')==1 )
          {
            $(this).parent().parent().css('opacity',1);
          }
          // Ne pas activer un sous-menu si le menu ne l'est pas
          else
          {
            $(this).prop('checked',false);
          }
        }
        else
        {
          $(this).parent().parent().css('opacity',taux_opacite);
          $(this).next().prop('checked',false);
        }
      }
    );

    // Ne pas activer un favori si le sous-menu n'est pas visible
    $("input[name^=favori_]").change
    (
      function()
      {
        if( $(this).is(':checked') )
        {
          if( !$(this).prev().is(':checked') )
          {
            $(this).prop('checked',false);
          }
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Alerter sur la nécessité de valider
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#form_menu input").change
    (
      function()
      {
        is_modif = true;
        $('#ajax_msg').attr('class','alerte').html("Enregistrer pour confirmer.");
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Soumission du formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_valider').click
    (
      function()
      {
        if(!is_modif)
        {
          $('#ajax_msg').attr('class','erreur').html("Aucune modification effectuée !");
          return false;
        }
        else if( $("input[name^=favori_]:checked").length > 5 )
        {
          $('#ajax_msg').attr('class','erreur').html("Choisissez 5 raccourcis maximum !");
          return false;
        }
        else
        {
          $('#bouton_valider').prop('disabled',true);
          $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
          $.ajax
          (
            {
              type : 'POST',
              url : 'ajax.php?page='+PAGE,
              data : 'csrf='+CSRF+'&'+$("#form_menu").serialize(),
              dataType : 'json',
              error : function(jqXHR, textStatus, errorThrown)
              {
                $('#bouton_valider').prop('disabled',false);
                $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
                return false;
              },
              success : function(responseJSON)
              {
                is_modif = false;
                initialiser_compteur();
                $('#bouton_valider').prop('disabled',false);
                $('#ajax_msg').attr('class','valide').html("Choix enregistrés !");
              }
            }
          );
        }
      }
    );

  }
);
