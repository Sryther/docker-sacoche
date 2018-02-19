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

    $('#f_proxy_used').click
    (
      function()
      {
        $("#div_proxy_used").toggle();
      }
    );

    $('#f_proxy_auth_used').click
    (
      function()
      {
        $("#div_proxy_auth_used").toggle();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Masquer les message si formulaire modifié
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('input,select').change
    (
      function()
      {
        $('#ajax_msg_enregistrer').attr('class','alerte').html('Pensez à enregistrer vos modifications !');
        $('#ajax_msg_tester').removeAttr('class').html('&nbsp;');
        $('#retour_test').html('&nbsp;');
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Tester les réglages actuellement enregistrés
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_tester').click
    (
      function()
      {
        $('#retour_test').html('&nbsp;');
        $('#ajax_msg_tester').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=tester',
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg_tester').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg_tester').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                $('#ajax_msg_tester').removeAttr('class').html('&nbsp;');
                $('#retour_test').html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Soumettre le formulaire principal => Enregistrer des nouveaux réglages
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $("#form_proxy");

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          f_proxy_used        : { required:false },
          f_proxy_name        : { required:function(){return $('#f_proxy_used').is(':checked');} },
          f_proxy_port        : { required:function(){return $('#f_proxy_used').is(':checked');} , digits:true },
          f_proxy_type        : { required:function(){return $('#f_proxy_used').is(':checked');} },
          f_proxy_auth_used   : { required:false },
          f_proxy_auth_method : { required:function(){return $('#f_proxy_auth_used').is(':checked');} },
          f_proxy_auth_user   : { required:function(){return $('#f_proxy_auth_used').is(':checked');} },
          f_proxy_auth_pass   : { required:function(){return $('#f_proxy_auth_used').is(':checked');} }
        },
        messages :
        {
          f_proxy_used        : { },
          f_proxy_name        : { required:"nom manquant" },
          f_proxy_port        : { required:"numéro manquant" , digits:"nombre entier requis" },
          f_proxy_type        : { required:"type manquant" },
          f_proxy_auth_used   : { },
          f_proxy_auth_method : { required:"méthode manquante" },
          f_proxy_auth_user   : { required:"utilisateur manquant" },
          f_proxy_auth_pass   : { required:"mot de passe manquant" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element){element.after(error);}
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
      target : "#ajax_msg_enregistrer",
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
      $('#retour_test').html('&nbsp;');
      $('#ajax_msg_enregistrer').removeAttr('class').html("");
      var readytogo = validation.form();
      if(readytogo)
      {
        $('button').prop('disabled',true);
        $('#ajax_msg_enregistrer').attr('class','loader').html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      $('button').prop('disabled',false);
      $('#ajax_msg_enregistrer').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseJSON)
    {
      initialiser_compteur();
      $('button').prop('disabled',false);
      if(responseJSON['statut']==false)
      {
        $('#ajax_msg_enregistrer').attr('class','alerte').html(responseJSON['value']);
      }
      else
      {
        $('#ajax_msg_enregistrer').attr('class','valide').html("Demande réalisée !");
      }
    }

  }
);
