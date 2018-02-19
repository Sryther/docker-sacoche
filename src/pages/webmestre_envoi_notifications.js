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
    // Afficher / masquer des éléments du formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_change_remove , #f_change_replace').click
    (
      function()
      {
        if($('#f_change_replace').is(':checked'))
        {
          $('#span_replace').show();
          $('#f_courriel_new').focus();
        }
        else
        {
          $('#span_replace').hide();
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du premier formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire_choix_envoi = $('#form_choix_envoi');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation_choix_envoi = formulaire_choix_envoi.validate
    (
      {
        rules :
        {
          f_send : { required:true }
        },
        messages :
        {
          f_send : { required:"choix manquant" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          $('#ajax_msg_choix_envoi').html(error);
        }
        // success: function(label) {label.text("ok").attr('class','valide');} Pas pour des champs soumis à vérification PHP
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions_choix_envoi =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg_choix_envoi",
      beforeSubmit : test_form_avant_envoi_choix_envoi,
      error : retour_form_erreur_choix_envoi,
      success : retour_form_valide_choix_envoi
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire_choix_envoi.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions_choix_envoi);
        return false;
      }
    );

    // Fonction précédant l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi_choix_envoi(formData, jqForm, options)
    {
      $('#ajax_msg_choix_envoi').removeAttr('class').html("");
      var readytogo = validation_choix_envoi.form();
      if(readytogo)
      {
        $("button").prop('disabled',true);
        $('#ajax_msg_choix_envoi').attr('class','loader').html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur_choix_envoi(jqXHR, textStatus, errorThrown)
    {
      $("button").prop('disabled',false);
      $('#ajax_msg_choix_envoi').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide_choix_envoi(responseJSON)
    {
      initialiser_compteur();
      $("button").prop('disabled',false);
      if(responseJSON['statut']==true)
      {
        $('#ajax_msg_choix_envoi').attr('class','valide').html("Choix enregistré !");
      }
      else
      {
        $('#ajax_msg_choix_envoi').attr('class','alerte').html(responseJSON['value']);
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du second formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire_modif = $('#form_modif_mail');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation_modif = formulaire_modif.validate
    (
      {
        rules :
        {
          f_base_id      : { required:true , digits:true },
          f_courriel_old : { required:true , email:true , maxlength:COURRIEL_LONGUEUR_MAX },
          f_change       : { required:true },
          f_courriel_new : { required:function(){return $('#f_change_replace').is(':checked');} , email:true , maxlength:COURRIEL_LONGUEUR_MAX }
        },
        messages :
        {
          f_base_id      : { required:"id structure manquant" , digits:"id structure en chiffres" },
          f_courriel_old : { required:"courriel manquant" , email:"courriel invalide" , maxlength:COURRIEL_LONGUEUR_MAX+" caractères maximum" },
          f_change       : { required:"action manquante" },
          f_courriel_new : { required:"courriel manquant" , email:"courriel invalide" , maxlength:COURRIEL_LONGUEUR_MAX+" caractères maximum" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          if(element.attr("type")=="radio") {element.parent().next().next().after(error);}
          else { element.after(error); }
        }
        // success: function(label) {label.text("ok").attr('class','valide');} Pas pour des champs soumis à vérification PHP
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions_modif =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg_modif",
      beforeSubmit : test_form_avant_envoi_modif,
      error : retour_form_erreur_modif,
      success : retour_form_valide_modif
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire_modif.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions_modif);
        return false;
      }
    );

    // Fonction précédant l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi_modif(formData, jqForm, options)
    {
      $('#ajax_msg_modif').removeAttr('class').html("");
      var readytogo = validation_modif.form();
      if(readytogo)
      {
        $("button").prop('disabled',true);
        $('#ajax_msg_modif').attr('class','loader').html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur_modif(jqXHR, textStatus, errorThrown)
    {
      $("button").prop('disabled',false);
      $('#ajax_msg_modif').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide_modif(responseJSON)
    {
      initialiser_compteur();
      $("button").prop('disabled',false);
      if(responseJSON['statut']==true)
      {
        $('#ajax_msg_modif').attr('class','valide').html(responseJSON['value']);
      }
      else
      {
        $('#ajax_msg_modif').attr('class','alerte').html(responseJSON['value']);
      }
    }

  }
);
