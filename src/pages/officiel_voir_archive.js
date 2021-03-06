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

    // Modification de style pour un document consulté
    $('#statistiques').on
    (
      'click',
      'a',
      function()
      {
        $.fancybox( { 'href':$(this).attr('href') , 'type':'iframe' , 'width':'100%' , 'height':'100%' } );
        $(this).parent().removeAttr('class').parent().removeAttr('class');
        return false;
      }
    );

    // Afficher une archive au chargement
    if(auto_voir_archive_id)
    {
      if( $('#archive_'+auto_voir_archive_id).length )
      {
        $('#archive_'+auto_voir_archive_id).click();
        // Cela n'ouvre pas le document, et le forcer avec window.open() se heurte au blocage des popups, du coup je suis passé par une fenêtre modale pour tout le monde.
      }
      auto_voir_archive_id = false;
    }

  }
);
