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

    if(!($('#f_courriel').val()))
    {
      $('#f_courriel').focus();
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Alerter sur la nécessité de valider
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#f_courriel").change
    (
      function()
      {
        $('#ajax_msg_courriel').attr('class','alerte').html("Enregistrer pour confirmer.");
      }
    );

    $("#table_abonnements input[type=radio]").change
    (
      function()
      {
        $('#ajax_msg_abonnements').attr('class','alerte').html("Enregistrer pour confirmer.");
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du premier formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $('#form_courriel');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          f_courriel : { required:false , email:true , maxlength:COURRIEL_LONGUEUR_MAX }
        },
        messages :
        {
          f_courriel : { email:"adresse invalide", maxlength:COURRIEL_LONGUEUR_MAX+" caractères maximum" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element) { element.after(error); }
        // success: function(label) {label.text("ok").attr('class','valide');} Pas pour des champs soumis à vérification PHP
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg_courriel",
      beforeSubmit : test_form_avant_envoi,
      error : retour_form_erreur,
      success : retour_form_valide
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions);
        return false;
      }
    );

    // Fonction précédant l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi(formData, jqForm, options)
    {
      $('#ajax_msg_courriel').removeAttr('class').html("");
      var readytogo = validation.form();
      if(readytogo)
      {
        $('#bouton_valider').prop('disabled',true);
        $('#ajax_msg_courriel').attr('class','loader').html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      $('#bouton_valider').prop('disabled',false);
      $('#ajax_msg_courriel').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseJSON)
    {
      initialiser_compteur();
      $('#bouton_valider').prop('disabled',false);
      if(responseJSON['statut']==true)
      {
        $('#info_adresse').html(responseJSON['info_adresse']);
        $('#info_abonnement_mail').html(responseJSON['info_abonnement_mail']);
        $('#ajax_msg_courriel').attr('class','valide').html("Choix enregistré !");
      }
      else
      {
        $('#ajax_msg_courriel').attr('class','alerte').html(responseJSON['value']);
      }
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du deuxième formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_abonner').click
    (
      function()
      {
        $('#ajax_msg_abonnements').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=enregistrer_abonnements'+'&'+$('#form_abonnements').serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg_abonnements').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==true)
              {
                $('#ajax_msg_abonnements').attr('class','valide').html("Choix enregistrés !");
              }
              else
              {
                $('#ajax_msg_abonnements').attr('class','alerte').html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

  }
);
