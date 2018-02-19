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
    // Droits du système de fichiers - Choix UMASK
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_umask').click
    (
      function()
      {
        $('button').prop('disabled',true);
        $('#ajax_umask').attr('class','loader').html("En cours&hellip;");
        var umask = $('#select_umask option:selected').val();
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=choix_umask'+'&f_umask='+umask,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('button').prop('disabled',false);
              $('#ajax_umask').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              $('button').prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $('#ajax_umask').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                var tab_chmod = new Array();
                tab_chmod['000'] = '777 / 666';
                tab_chmod['002'] = '775 / 664';
                tab_chmod['022'] = '755 / 644';
                tab_chmod['026'] = '751 / 640';
                $(info_chmod).html(tab_chmod[umask]);
                $('#ajax_umask').attr('class','valide').html('Choix enregistré !');
                initialiser_compteur();
              }
              return false;
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Droits du système de fichiers - Appliquer CHMOD
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_chmod').click
    (
      function()
      {
        $('button').prop('disabled',true);
        $('#ajax_chmod').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=appliquer_chmod',
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('button').prop('disabled',false);
              $('#ajax_chmod').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              $('button').prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $('#ajax_chmod').attr('class','alerte').html(responseJSON['value']);
                return false;
              }
              else
              {
                $('#ajax_chmod').attr('class','valide').html('Procédure terminée !');
                $.fancybox( { 'href':responseJSON['value'] , 'type':'iframe' , 'width':'80%' , 'height':'80%' , 'centerOnScroll':true } );
                initialiser_compteur();
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Vérification des droits en écriture
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_droit').click
    (
      function()
      {
        $('button').prop('disabled',true);
        $('#ajax_droit').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=verif_droits',
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('button').prop('disabled',false);
              $('#ajax_droit').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              $('button').prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $('#ajax_droit').attr('class','alerte').html(responseJSON['value']);
                return false;
              }
              else
              {
                $('#ajax_droit').attr('class','valide').html('Vérification terminée !');
                $.fancybox( { 'href':responseJSON['value'] , 'type':'iframe' , 'width':'80%' , 'height':'80%' , 'centerOnScroll':true } );
                initialiser_compteur();
              }
            }
          }
        );
      }
    );

  }
);
