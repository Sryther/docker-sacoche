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
    $('#table_notifications').tablesorter({ headers:{0:{sorter:'date_fr'},3:{sorter:false},4:{sorter:false}} });
    var tableau_tri = function(){ $('#table_notifications').trigger( 'sorton' , [ [[0,1]] ] ); };
    var tableau_maj = function(){ $('#table_notifications').trigger( 'update' , [ true ] ); };
    tableau_tri();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Voir le détail d'une notification
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('q.voir').click
    (
      function()
      {
        var objet_tr  = $(this).parent().parent();
        var objet_tds = objet_tr.find('td');
        var id        = objet_tr.attr('id').substring(3);
        var date_fr   = objet_tds.eq(0).html();
        var is_new    = objet_tr.hasClass('new');
        $('#report_date').html(date_fr);
        $('#textarea_notification').html( unescapeHtml(tab_notif_contenu[id]) );
        $('#ajax_save').removeAttr('class').html("");
        $.fancybox( { 'href':'#div_notification' , onStart:function(){$('#div_notification').css("display","block");} , onClosed:function(){$('#div_notification').css("display","none");} , 'minWidth':600 , 'centerOnScroll':true } );
        if(is_new)
        {
          // Une requête ajax discrète pour mémoriser qu'une notification a été consultée.
          $.ajax
          (
            {
              type : 'POST',
              url : 'ajax.php?page='+PAGE,
              data : 'csrf='+CSRF+'&f_action=memoriser_consultation'+'&f_id='+id,
              dataType : 'json',
              error : function(jqXHR, textStatus, errorThrown)
              {
                $('#ajax_save').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              },
              success : function(responseJSON)
              {
                initialiser_compteur();
                if(responseJSON['statut']==true)
                {
                  objet_tr.removeAttr('class');
                  objet_tds.eq(1).html('consultée');
                }
                else
                {
                  $('#ajax_save').attr('class','alerte').html(responseJSON['value']);
                }
              }
            }
          );
        }
      }
    );

  }
);
