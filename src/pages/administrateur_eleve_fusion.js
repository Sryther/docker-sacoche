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
    // Intercepter la soumission du formulaire de recherche
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_recherche').on(
      'keyup' ,
      'input' ,
      function(e)
      {
        if(e.which==13)  // touche entrée
        {
          var statut = $(this).attr('id').substring(4); // nom_
          $('#bouton_chercher_'+statut).click();
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Activation ou pas du bouton de sélection
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function change_etat_bouton_selection()
    {
      if( $('#id_actuel option:selected').val() && $('#id_ancien option:selected').val() )
      {
        $('#bouton_selectionner').prop('disabled',false);
      }
      else
      {
        $('#bouton_selectionner').prop('disabled',true);
      }
    }


    $('#id_actuel , #id_ancien').change
    (
      function()
      {
        change_etat_bouton_selection();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire de recherche
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_chercher_actuel , #bouton_chercher_ancien').click
    (
      function()
      {
        $('#bouton_selectionner').prop('disabled',true);
        $('#ajax_msg_selection').removeAttr('class').html("");
        var statut = $(this).attr('id').substring(16); // bouton_chercher_
        var nom    = $('#nom_'+statut).val();
        if( !nom )
        {
          $('#ajax_msg_'+statut).attr('class','erreur').html("Entrer un nom !");
          return false;
        }
        $('#bouton_chercher_'+statut).prop('disabled',true);
        $('#ajax_msg_'+statut).attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+'chercher'+'&f_statut='+statut+'&f_nom='+encodeURIComponent(nom),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg_'+statut).attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              $('#bouton_chercher_'+statut).prop('disabled',false);
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('#bouton_chercher_'+statut).prop('disabled',false);
              if(responseJSON['statut']==true)
              {
                $('#ajax_msg_'+statut).attr('class','valide').html("");
                $('#id_'+statut).html(responseJSON['value']).show(0);
                change_etat_bouton_selection();
              }
              else
              {
                $('#ajax_msg_'+statut).attr('class','alerte').html(responseJSON['value']);
                $('#id_'+statut).hide(0);
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire de sélection
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_selectionner').click
    (
      function()
      {
        var id_actuel = $('#id_actuel option:selected').val();
        var id_ancien = $('#id_ancien option:selected').val();
        if( !id_actuel || !id_ancien )
        {
          $('#ajax_msg_selection').attr('class','erreur').html("Sélectionner 2 élèves !");
          return false;
        }
        $('#bouton_selectionner').prop('disabled',true);
        $('#ajax_msg_selection').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+'fusionner'+'&f_id_actuel='+id_actuel+'&f_id_ancien='+id_ancien,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg_selection').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              $('#bouton_selectionner').prop('disabled',false);
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==true)
              {
                $('#ajax_msg_selection').attr('class','valide').html("Comptes fusionnés.");
                $('#nom_actuel').val("");
                $('#nom_ancien').val("");
                $('#id_actuel').html('<option value=""></option>').hide(0);;
                $('#id_ancien').html('<option value=""></option>').hide(0);;
                $('#ajax_msg_actuel').removeAttr('class').html("");
                $('#ajax_msg_ancien').removeAttr('class').html("");
                $('#bouton_selectionner').prop('disabled',true);
              }
              else
              {
                $('#ajax_msg_selection').attr('class','alerte').html(responseJSON['value']);
                $('#bouton_selectionner').prop('disabled',false);
              }
            }
          }
        );
      }
    );

  }
);
