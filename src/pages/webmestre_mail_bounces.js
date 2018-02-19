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
    // Traitement du 1er formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire_1 = $('#form_gestion');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation_1 = formulaire_1.validate
    (
      {
        rules :
        {
          f_bounce : { required:false , email:true , maxlength:COURRIEL_LONGUEUR_MAX }
        },
        messages :
        {
          f_bounce : { email:"adresse invalide", maxlength:COURRIEL_LONGUEUR_MAX+" caractères maximum" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          $('#ajax_msg_1').html(error);
        }
        // success: function(label) {label.text("ok").attr('class','valide');} Pas pour des champs soumis à vérification PHP
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions_1 =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg_1",
      beforeSubmit : test_form_avant_envoi_1,
      error : retour_form_erreur_1,
      success : retour_form_valide_1
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire_1.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions_1);
        return false;
      }
    );

    // Fonction précédant l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi_1(formData, jqForm, options)
    {
      $('#ajax_msg_1').removeAttr('class').html("");
      var readytogo = validation_1.form();
      if(readytogo)
      {
        $("button").prop('disabled',true);
        $('#ajax_msg_1').attr('class','loader').html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur_1(jqXHR, textStatus, errorThrown)
    {
      $("button").prop('disabled',false);
      $('#ajax_msg_1').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide_1(responseJSON)
    {
      initialiser_compteur();
      $("button").prop('disabled',false);
      if(responseJSON['statut']==true)
      {
        $('#ajax_msg_1').attr('class','valide').html("Choix enregistré !");
      }
      else
      {
        $('#ajax_msg_1').attr('class','alerte').html(responseJSON['value']);
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du 2ème formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire_2 = $('#form_test');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation_2 = formulaire_2.validate
    (
      {
        rules :
        {
          f_courriel : { required:true , email:true , maxlength:COURRIEL_LONGUEUR_MAX }
        },
        messages :
        {
          f_courriel : { required:"adresse manquante" , email:"adresse invalide", maxlength:COURRIEL_LONGUEUR_MAX+" caractères maximum" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          $('#ajax_msg_2').html(error);
        }
        // success: function(label) {label.text("ok").attr('class','valide');} Pas pour des champs soumis à vérification PHP
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions_2 =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg_2",
      beforeSubmit : test_form_avant_envoi_2,
      error : retour_form_erreur_2,
      success : retour_form_valide_2
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire_2.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions_2);
        return false;
      }
    );

    // Fonction précédant l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi_2(formData, jqForm, options)
    {
      $('#ajax_msg_2').removeAttr('class').html("");
      var readytogo = validation_2.form();
      if(readytogo)
      {
        $("button").prop('disabled',true);
        $('#ajax_msg_2').attr('class','loader').html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur_2(jqXHR, textStatus, errorThrown)
    {
      $("button").prop('disabled',false);
      $('#ajax_msg_2').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide_2(responseJSON)
    {
      initialiser_compteur();
      $("button").prop('disabled',false);
      if(responseJSON['statut']==true)
      {
        $('#ajax_msg_2').attr('class','valide').html("Deux courriels envoyés !");
      }
      else
      {
        $('#ajax_msg_2').attr('class','alerte').html(responseJSON['value']);
      }
    }

  }
);
