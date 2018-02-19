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
    // Clic sur une image-lien afin d'afficher ou de masquer un élément de la page d'accueil
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('a[href^="#toggle_"]').click
    (
      function()
      {
        var type  = extract_hash( $(this).attr('href') ).substring(7); // 'toggle_' + type
        var symb1 = $(this).attr('class').substring(7); // 'toggle_' + état
        var symb2 = ( symb1=='moins' ) ? 'plus' : 'moins' ;
        $('#'+type+'_'+symb1).hide(0);
        $('#'+type+'_'+symb2).show(0);
        // Au passage, une requête ajax discrète pour mémoriser cette préférence
        var etat = ( symb1=='moins' ) ? 0 : 1 ;
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_type='+type+'&f_etat='+etat,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+' Choix non mémorisé.'+'</label>' , {'centerOnScroll':true} );
            },
            success : function(responseJSON)
            {
              if(responseJSON['statut']==false)
              {
                $.fancybox( '<label class="alerte">'+responseJSON['value']+'<br />Choix non mémorisé.'+'</label>' , {'centerOnScroll':true} );
              }
            }
          }
        );
        return false;
      }
    );

  }
);
