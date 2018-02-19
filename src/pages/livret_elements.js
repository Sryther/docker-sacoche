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
    // Obliger l'affichage de l'ascenseur vertical car son apparition / disparition en fonction des affichages peut modifier la position des blocs des sous-menus
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#force_scroll').css('height',screen.height);

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Afficher / masquer une zone de formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#sousmenu a').click
    (
      function()
      {
        var hash = extract_hash( $(this).attr('href') );
        // sous-menu
        $('#sousmenu a').removeAttr('class');
        $(this).addClass("actif");
        // zone de formulaire
        $('#form_synthese fieldset').addClass("hide");
        $('#'+hash).removeAttr('class');
        // pas de focus sur la zone
        return false;
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Afficher / masquer des thèmes ou des domaines
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_synthese input').click
    (
      function()
      {
        var ids = $(this).attr('name').substr(2);
        var option_valeur = $(this).val();
        $('#domaine_'+ids).addClass("hide");
        $('#theme_'  +ids).addClass("hide");
        $('#item_'   +ids).addClass("hide");
        $('#'+option_valeur+'_'+ids).removeAttr('class');
        $('#bouton_'+ids).prop('disabled',false);
        $('#label_'+ids).attr('class','alerte').html("Modification non enregistrée !");
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Enregistrer une modification
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_synthese button').click
    (
      function()
      {
        var ids = $(this).attr('id').substr(7);
        if( $('input[name=f_'+ids+']').is(':checked')!=true )  // normalement impossible, sauf si par exemple on triche avec la barre d'outils Web Developer...
        {
          $('#label_'+ids).attr('class','erreur').html("Cocher une option !");
          return false;
        }
        var f_methode = $('input[name=f_'+ids+']:checked').val();
        var tab_infos = ids.split('_');
        var f_matiere = tab_infos[0];
        var f_niveau  = tab_infos[1];
        $('#bouton_'+ids).prop('disabled',true);
        $('#label_'+ids).attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_methode='+f_methode+'&f_matiere='+f_matiere+'&f_niveau='+f_niveau,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#bouton_'+ids).prop('disabled',false);
              $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('#bouton_'+ids).prop('disabled',false);
              if(responseJSON['statut']==true)
              {
                $('#label_'+ids).attr('class','valide').html("Modification enregistrée !");
              }
              else
              {
                $('#label_'+ids).attr('class','alerte').html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

  }
);
