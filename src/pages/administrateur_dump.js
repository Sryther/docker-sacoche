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
    // Appel en ajax pour lancer une sauvegarde
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function sauvegarder(etape)
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=sauvegarder'+'&etape='+etape,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $("button").prop('disabled',false);
            $('#ajax_msg_sauvegarde').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            return false;
          },
          success : function(responseJSON)
          {
            if(responseJSON['statut']==false)
            {
              $("button").prop('disabled',false);
              $('#ajax_msg_sauvegarde').attr('class','alerte').html(responseJSON['value']);
            }
            else
            {
              $('#ajax_info').html('<li><label class="'+responseJSON['class']+'">'+responseJSON['texte']+'</label></li>');
              initialiser_compteur();
              if(responseJSON['class']=='loader')
              {
                etape++;
                sauvegarder(etape);
              }
              else
              {
                $("button").prop('disabled',false);
                $('#ajax_msg_sauvegarde').removeAttr('class').html('');
                $('#ajax_info').append('<li><a target="_blank" rel="noopener noreferrer" href="'+responseJSON['href']+'"><span class="file file_zip">Récupérer le fichier de sauvegarde au format ZIP.</span></a></li>'+'<li><label class="alerte">Pour des raisons de sécurité et de confidentialité, ce fichier sera effacé du serveur dans 1h.</label></li>');
              }
            }
          }
        }
      );
    }

    $('#bouton_sauvegarde').click
    (
      function()
      {
        $("button").prop('disabled',true);
        $('#ajax_msg_sauvegarde').attr('class','loader').html("En cours&hellip;");
        $('#ajax_msg_restauration').removeAttr('class').html('');
        $('#ajax_info').html('');
        initialiser_compteur();
        sauvegarder(1);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire form_restauration
    // Upload d'un fichier (avec jquery.form.js)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire_restauration = $('#form_restauration');

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions_restauration =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg_restauration",
      error : retour_form_erreur_restauration,
      success : retour_form_valide_restauration
    };

    // Vérifications précédant l'envoi du formulaire, déclenchées au choix d'un fichier
    $('#f_restauration').change
    (
      function()
      {
        $("#ajax_info").html('');
        $('#ajax_msg_sauvegarde').removeAttr('class').html('');
        $('#ajax_msg_restauration').removeAttr('class').html('');
        var file = this.files[0];
        if( typeof(file) == 'undefined' )
        {
          $('#ajax_msg_restauration').removeAttr('class').html('');
          return false;
        }
        else
        {
          var fichier_nom = file.name;
          var fichier_ext = fichier_nom.split('.').pop().toLowerCase();
          if( fichier_ext != 'zip' )
          {
            $('#ajax_msg_restauration').attr('class','erreur').html('Le fichier "'+fichier_nom+'" n\'a pas l\'extension zip.');
            return false;
          }
          else
          {
            $("button").prop('disabled',true);
            $('#ajax_msg_restauration').attr('class','loader').html("En cours&hellip;");
            formulaire_restauration.submit();
          }
        }
      }
    );

    // Envoi du formulaire (avec jquery.form.js)
    formulaire_restauration.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions_restauration);
        return false;
      }
    );

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur_restauration(jqXHR, textStatus, errorThrown)
    {
      $('#f_restauration').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      $("button").prop('disabled',false);
      $('#ajax_msg_restauration').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide_restauration(responseJSON)
    {
      $('#f_restauration').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      $("button").prop('disabled',false);
      if(responseJSON['statut']==false)
      {
        $('#ajax_msg_restauration').attr('class','alerte').html(responseJSON['value']);
      }
      else
      {
        $('#ajax_msg_restauration').attr('class','valide').html('Contenu du fichier récupéré avec succès.');
        $.prompt(
          "Souhaitez-vous vraiment restaurer la base contenue dans ce fichier&nbsp;?<br />==&gt; "+responseJSON['value']+"<br />Toute action effectuée depuis le moment de cette sauvegarde sera à refaire&nbsp;!!!<br />En particulier les saisies d'évaluations et les modifications de référentiels seront perdues&hellip;",
          {
            title   : 'Demande de confirmation',
            buttons : {
              "Non, c'est une erreur !" : false ,
              "Oui, je confirme !" : true
            },
            submit  : function(event, value, message, formVals) {
              if(value)
              {
                $('#ajax_msg_restauration').attr('class','loader').html('Demande traitée...');
                initialiser_compteur();
                restaurer(1);
              }
              else
              {
                $("button").prop('disabled',false);
                $('#ajax_msg_restauration').attr('class','alerte').html('Restauration annulée.');
              }
            }
          }
        );
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Appel en ajax pour lancer une restauration
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function restaurer(etape)
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=restaurer'+'&etape='+etape,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $("button").prop('disabled',false);
            $('#ajax_msg_restauration').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            return false;
          },
          success : function(responseJSON)
          {
            if(responseJSON['statut']==false)
            {
              $("button").prop('disabled',false);
              $('#ajax_msg_restauration').attr('class','alerte').html(responseJSON['value']);
            }
            else
            {
              $('#ajax_info').html('<li><label class="'+responseJSON['class']+'">'+responseJSON['texte']+'</label></li>');
              initialiser_compteur();
              if(responseJSON['class']=='loader')
              {
                etape++;
                restaurer(etape);
              }
              else
              {
                $("button").prop('disabled',false);
                $('#ajax_msg_restauration').removeAttr('class').html('');
                $('#ajax_info').append('<li><label class="alerte">Veuillez maintenant vous déconnecter / reconnecter pour mettre la session en conformité avec la base restaurée.</label></li>');
              }
            }
          }
        }
      );
    }

  }
);
