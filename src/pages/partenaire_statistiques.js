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

    // tri du tableau (avec jquery.tablesorter.js).
    $('#table_action').tablesorter({ headers:{} });
    var tableau_tri = function(){ $('#table_action').trigger( 'sorton' , [ [[0,0],[1,0]] ] ); };
    var tableau_maj = function(){ $('#table_action').trigger( 'update' , [ true ] ); };
    tableau_tri();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Lancement de la récupération des stats
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#ajax_msg1').attr('class','loader').html("Connexion au serveur&hellip;");
    $.ajax
    (
      {
        type : 'POST',
        url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
        dataType : 'json',
        error : function(msg,string)
        {
          $('#ajax_msg1').attr('class','alerte').html("Échec de la connexion ! Veuillez recommencer.");
        },
        success : function(responseJSON)
        {
          if(responseJSON['statut']==false)
          {
            $('#ajax_msg1').attr('class','alerte').html(responseJSON['value']);
          }
          else
          {
            var max = responseJSON['value'];
            $('#ajax_msg1').attr('class','loader').html('Récolte des informations en cours : étape 1 sur ' + max + '...');
            $('#ajax_msg2').html('Ne pas interrompre la procédure avant la fin du traitement !');
            $('#ajax_num').html(1);
            $('#ajax_max').html(max);
            $('#ajax_info').show('fast');
            $('#table_action tbody').html('');
            $('#table_action tfoot').html('');
            rechercher();
          }
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Etapes de récupération des données d'un établissement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function rechercher()
    {
      var num = parseInt( $('#ajax_num').html() , 10 );
      var max = parseInt( $('#ajax_max').html() , 10 );
      // Appel en ajax
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
          data : 'num=' + num + '&max=' + max,
          dataType : 'json',
          error : function(msg,string)
          {
            $('#ajax_msg1').attr('class','alerte').html('Échec lors de la connexion au serveur !');
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
                $('#ajax_msg').removeAttr('class').html("");
              }
              else
              {
                if(ligne)
                {
                  $('#table_action tbody').append(ligne);
                }
                $('#ajax_num').html(num);
                $('#ajax_msg1').attr('class','loader').html('Récolte des informations en cours : étape ' + num + ' sur ' + max + '...');
                $('#ajax_msg2').html('Ne pas interrompre la procédure avant la fin du traitement !');
                rechercher();
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
        $('#ajax_msg1').attr('class','loader').html('Récolte des informations en cours : étape ' + num + ' sur ' + max + '...');
        $('#ajax_msg2').html('Ne pas interrompre la procédure avant la fin du traitement !');
        rechercher();
      }
    );

  }
);
