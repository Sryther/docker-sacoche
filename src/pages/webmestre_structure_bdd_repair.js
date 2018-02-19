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
// Formulaire et traitement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_valider').click
    (
      function()
      {
        // grouper le select multiple
        if( $("#f_base input:checked").length==0 )
        {
          $('#ajax_msg').attr('class','erreur').html("Sélectionnez au moins un établissement !");
          return false;
        }
        else
        {
          // Grouper les checkbox dans un champ unique afin d'éviter tout problème avec une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque) ou "max input vars" généralement fixé à 1000.
          var f_listing_id = new Array(); $("#f_base input:checked").each(function(){f_listing_id.push($(this).val());});
        }
        // on envoie
        $('#bouton_valider').prop('disabled',true);
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_listing_id='+f_listing_id,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#bouton_valider').prop('disabled',false);
              $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==false)
              {
                $('#bouton_valider').prop('disabled',false);
                $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                var max = responseJSON['value'];
                $('#ajax_msg1').attr('class','loader').html('Structures à l\'étude : étape 1 sur ' + max + '...');
                $('#ajax_msg2').html('Ne pas interrompre la procédure avant la fin du traitement !');
                $('#ajax_num').html(1);
                $('#ajax_max').html(max);
                $('#ajax_info').show('fast');
                analyser_et_reparer();
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Etapes de calcul des statistiques
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function analyser_et_reparer()
    {
      var num = parseInt( $('#ajax_num').html() , 10 );
      var max = parseInt( $('#ajax_max').html() , 10 );
      // Appel en ajax
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&num='+num+'&max='+max,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg1').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
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
              num++;
              if(num > max)  // Utilisation de parseInt obligatoire sinon la comparaison des valeurs pose ici pb
              {
                var fichier = responseJSON['value'];
                $('#ajax_msg1').attr('class','valide').html('Calcul des statistiques terminé.');
                $('#ajax_msg2').html('');
                $('#ajax_info').hide('fast');
                $('#bouton_valider').prop('disabled',false);
                $('#ajax_msg').removeAttr('class').html("");
                $.fancybox( { 'href':fichier , 'type':'iframe' , 'width':'80%' , 'height':'80%' , 'centerOnScroll':true } );
              }
              else
              {
                $('#ajax_num').html(num);
                $('#ajax_msg1').attr('class','loader').html('Structures à l\'étude : étape ' + num + ' sur ' + max + '...');
                $('#ajax_msg2').html('Ne pas interrompre la procédure avant la fin du traitement !');
                analyser_et_reparer();
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
        $('#ajax_msg1').attr('class','loader').html('Structures à l\'étude : étape ' + num + ' sur ' + max + '...');
        $('#ajax_msg2').html('Ne pas interrompre la procédure avant la fin du traitement !');
        analyser_et_reparer();
      }
    );

  }
);
