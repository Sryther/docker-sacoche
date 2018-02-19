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

    var user_id    = 0;
    var user_texte = 'Tampon de l\'établissement';
    var partie     = '';

    // Réagir au changement du select
    $('#f_user').change
    (
      function()
      {
        $('#ajax_msg_upload_signature').removeAttr('class').html('&nbsp;');
        user_id    = $('#f_user option:selected').val();
        user_texte = $('#f_user option:selected').text();
        $('#f_upload_user_id'   ).val(user_id   );
        $('#f_upload_user_texte').val(user_texte);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire form_mise_en_page
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var form_partie = {
      'f_coordonnees_adresse'   : 'coordonnees',
      'f_coordonnees_telephone' : 'coordonnees',
      'f_coordonnees_fax'       : 'coordonnees',
      'f_coordonnees_courriel'  : 'coordonnees',
      'f_coordonnees_url'       : 'coordonnees',
      'f_coordonnees_logo'      : 'coordonnees',
      'f_infos_responsables' : 'responsables',
      'f_nombre_exemplaires' : 'responsables',
      'f_horizontal_gauche' : 'positionnement',
      'f_horizontal_milieu' : 'positionnement',
      'f_horizontal_droite' : 'positionnement',
      'f_vertical_haut'     : 'positionnement',
      'f_vertical_milieu'   : 'positionnement',
      'f_vertical_bas'      : 'positionnement',
      'f_marge_gauche'      : 'positionnement',
      'f_marge_droite'      : 'positionnement',
      'f_marge_haut'        : 'positionnement',
      'f_marge_bas'         : 'positionnement',
      'f_tampon_signature' : 'signature'
    };
    // Alerter sur la nécessité de valider
    $("#form_mise_en_page input , #form_mise_en_page select").change
    (
      function()
      {
        $('#ajax_msg_'+form_partie[$(this).attr('id')]).attr('class','alerte').html("Enregistrer pour confirmer.");
      }
    );

    // Afficher / masquer p_enveloppe
    $("#f_infos_responsables").change
    (
      function()
      {
        if( $('#f_infos_responsables option:selected').val() == 'oui_force' )
        {
          $("#p_enveloppe").show();
        }
        else
        {
          $("#p_enveloppe").hide();
        }
      }
    );

    $('button.parametre').click
    (
      function()
      {
        partie = $(this).attr('id').substr(15); // bouton_valider_...
        if( (partie=='positionnement') && ( $('#f_infos_responsables option:selected').val() == 'oui_force' ) )
        {
          // Vérifier les dimensions de l'enveloppe
          var enveloppe_largeur = parseInt($('#f_horizontal_gauche').val(),10) + parseInt($('#f_horizontal_milieu').val(),10) + parseInt($('#f_horizontal_droite').val(),10) ;
          var enveloppe_hauteur = parseInt($('#f_vertical_haut'    ).val(),10) + parseInt($('#f_vertical_milieu'  ).val(),10) + parseInt($('#f_vertical_bas'     ).val(),10) ;
          if( (enveloppe_largeur<215) || (enveloppe_largeur>235) )
          {
            $('#ajax_msg_'+partie).attr('class','erreur').html("Dimensions incorrectes : la longueur de l'enveloppe doit être comprise entre 21,5cm et 23,5cm.");
            return false;
          }
          if( (enveloppe_hauteur<105) || (enveloppe_hauteur>125) )
          {
            $('#ajax_msg_'+partie).attr('class','erreur').html("Dimensions incorrectes : la hauteur de l'enveloppe doit être comprise entre 10,5cm et 12,5cm.");
            return false;
          }
        }
        $("button.parametre").prop('disabled',true);
        $('#ajax_msg_'+partie).attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+partie+'&'+$('#form_mise_en_page').serialize(),
            responseType: 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $("button.parametre").prop('disabled',false);
              $('#ajax_msg_'+partie).attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $("button.parametre").prop('disabled',false);
              if(responseJSON['statut']==true)
              {
                $('#ajax_msg_'+partie).attr('class','valide').html("Données enregistrées !");
              }
              else
              {
                $('#ajax_msg_'+partie).attr('class','alerte').html(responseJSON['value']);
              }
              return false;
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire #form_tampon
    // Upload d'un fichier (avec jquery.form.js)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire_signature = $('#form_tampon');

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions_signature =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg_upload_signature",
      error : retour_form_erreur_signature,
      success : retour_form_valide_signature
    };

    // Vérifications précédant l'envoi du formulaire, déclenchées au choix d'un fichier
    $('#f_signature').change
    (
      function()
      {
        var file = this.files[0];
        if( typeof(file) == 'undefined' )
        {
          $('#ajax_msg_upload_signature').removeAttr('class').html('');
          return false;
        }
        else
        {
          var fichier_nom = file.name;
          var fichier_ext = fichier_nom.split('.').pop().toLowerCase();
          if( '.gif.jpg.jpeg.png.'.indexOf('.'+fichier_ext+'.') == -1 )
          {
            $('#ajax_msg_upload_signature').attr('class','erreur').html('Le fichier "'+fichier_nom+'" n\'a pas une extension autorisée (gif jpg jpeg png).');
            return false;
          }
          else
          {
            $("#bouton_choisir_signature").prop('disabled',true);
            $('#ajax_msg_upload_signature').attr('class','loader').html("En cours&hellip;");
            formulaire_signature.submit();
          }
        }
      }
    );

    // Envoi du formulaire (avec jquery.form.js)
    formulaire_signature.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions_signature);
        return false;
      }
    );

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur_signature(jqXHR, textStatus, errorThrown)
    {
      $('#f_signature').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      $("#bouton_choisir_signature").prop('disabled',false);
      $('#ajax_msg_upload_signature').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide_signature(responseJSON)
    {
      $('#f_signature').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      $("#bouton_choisir_signature").prop('disabled',false);
      if(responseJSON['statut']==false)
      {
        $('#ajax_msg_upload_signature').attr('class','alerte').html(responseJSON['value']);
      }
      else
      {
        initialiser_compteur();
        $('#ajax_msg_upload_signature').attr('class','valide').html('Image ajoutée');
        if($('#sgn_'+user_id).length)
        {
          $('#sgn_'+user_id).replaceWith(responseJSON['value']);
        }
        else
        {
          $('#listing_signatures').prepend(responseJSON['value']);
        }
        $('#sgn_none').remove();
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Appel en ajax pour supprimer le tampon de l'établissement | une signature
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#listing_signatures').on
    (
      'click',
      'q.supprimer',
      function()
      {
        var sgn_id = $(this).parent().attr('id').substr(4);
        $('#ajax_msg_upload_signature').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=delete_signature'+'&f_user_id='+sgn_id,
            responseType: 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg_upload_signature').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg_upload_signature').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                $('#ajax_msg_upload_signature').removeAttr('class').html('');
                $('#sgn_'+sgn_id).remove();
              }
            }
          }
        );
      }
    );

  }
);
