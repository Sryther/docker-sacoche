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

    // tri du tableau (avec jquery.tablesorter.js).
    $('table#bilan').tablesorter();
    var tableau_tri = function(){ $('table#bilan').trigger( 'sorton' , [ [[1,1]] ] ); };
    var tableau_maj = function(){ $('table#bilan').trigger( 'update' , [ true ] ); };
    tableau_tri();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Réagir au clic sur un bouton radio ou un changement de select
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_affichage()
    {
      // On récupère le profil
      var profil = $("input[type=radio]:checked").val();
      if(typeof(profil)=='undefined')
      {
        $('#ajax_msg').removeAttr('class').html("");
        $('#div_bilan').addClass("hide");
        return false
      }
      // On récupère le regroupement
      var groupe_val = $("#f_groupe option:selected").val();
      if(!groupe_val)
      {
        $('#ajax_msg').removeAttr('class').html("");
        $('#div_bilan').addClass("hide");
        return false
      }
      // Pour un directeur ou un administrateur, groupe_val est de la forme d3 / n2 / c51 / g44
      if(isNaN(parseInt(groupe_val,10)))
      {
        groupe_type = groupe_val.substring(0,1);
        groupe_id   = groupe_val.substring(1);
      }
      // Pour un professeur, groupe_val est un entier, et il faut récupérer la 1ère lettre du label parent
      else
      {
        groupe_type = $("#f_groupe option:selected").parent().attr('label').substring(0,1).toLowerCase();
        groupe_id   = groupe_val;
      }
      $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
      $('#bilan tbody').html('');
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_profil='+profil+'&f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==false)
            {
              $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
              $('#div_bilan').addClass("hide");
            }
            else
            {
              $('#ajax_msg').attr('class','valide').html("Demande réalisée !");
              $('#bilan tbody').html(responseJSON['value']);
              tableau_maj();
              $('#div_bilan').removeAttr('class');
            }
          }
        }
      );
    }

    $("#f_groupe , input[type=radio]").change
    (
      function()
      {
        maj_affichage();
      }
    );

  }
);
