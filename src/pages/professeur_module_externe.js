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

    $(".fancybox").fancybox({'minWidth':400,'centerOnScroll':true});

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire form_module
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Alerter sur la nécessité de valider
    $("#form_module input").change
    (
      function()
      {
        $('#ajax_msg_module_url').attr('class','alerte').html("Enregistrer pour confirmer.");
      }
    );

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire_module_url = $('#form_module');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation_module_url = formulaire_module_url.validate
    (
      {
        rules :
        {
          f_module_url : { required:false , maxlength:255 , URL:true }
        },
        messages :
        {
          f_module_url : { maxlength:"255 caractères maximum" , URL:"url invalide (http:// manquant ?)" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element) { element.after(error); }
        // success: function(label) {label.text("ok").attr('class','valide');} Pas pour des champs soumis à vérification PHP
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions_module_url =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg_module_url",
      beforeSubmit : test_form_avant_envoi_module_url,
      error : retour_form_erreur_module_url,
      success : retour_form_valide_module_url
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire_module_url.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions_module_url);
        return false;
      }
    );

    // Fonction précédant l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi_module_url(formData, jqForm, options)
    {
      $('#ajax_msg_module_url').removeAttr('class').html("");
      var readytogo = validation_module_url.form();
      if(readytogo)
      {
        $("#bouton_valider_module_url").prop('disabled',true);
        $('#ajax_msg_module_url').attr('class','loader').html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur_module_url(jqXHR, textStatus, errorThrown)
    {
      $("#bouton_valider_module_url").prop('disabled',false);
      $('#ajax_msg_module_url').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide_module_url(responseJSON)
    {
      initialiser_compteur();
      $("#bouton_valider_module_url").prop('disabled',false);
      if(responseJSON['statut']==true)
      {
        $('#ajax_msg_module_url').attr('class','valide').html("Valeur enregistrée !");
      }
      else
      {
        $('#ajax_msg_module_url').attr('class','alerte').html(responseJSON['value']);
      }
    }

  }
);
