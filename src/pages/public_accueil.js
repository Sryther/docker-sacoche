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

    function curseur()
    {
      if( ($("#f_profil").val()!='structure') || GET_LOGIN )
      {
        $('#f_password').focus();
      }
      else if($("#f_login").length)
      {
        $('#f_login').focus();
      }
    }

    // Appel en ajax pour initialiser le formulaire au chargement
    function chargement()
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=initialiser'+'&f_base='+$("#f_base").val()+'&f_profil='+$("#f_profil").val()+'&get_mode='+encodeURIComponent(GET_MODE)+'&get_login='+encodeURIComponent(GET_LOGIN),
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            return false;
          },
          success : function(responseJSON)
          {
            if(responseJSON['statut']==true)
            {
              $("#fieldset_auth").html(responseJSON['value']);
              curseur();
            }
            else
            {
              $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
            }
          }
        }
      );
    }
    chargement();

    // Choix dans le formulaire des structures => Afficher le formulaire de la structure
    $('#fieldset_auth').on
    (
      'click',
      '#f_choisir',
      function()
      {
        $('button').prop('disabled',true);
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=charger'+'&f_base='+$('#f_base option:selected').val()+'&f_profil='+$("#f_profil").val(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('button').prop('disabled',false);
              $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              $('button').prop('disabled',false);
              if(responseJSON['statut']==true)
              {
                $("#fieldset_auth").html(responseJSON['value']);
                curseur();
              }
              else
              {
                $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

    // Clic sur le lien pour changer de structure
    $('#fieldset_auth').on
    (
      'click',
      '#f_changer',
      function()
      {
        $('#f_changer').hide();
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=choisir'+'&f_base='+$('#f_base').val()+'&f_profil='+$("#f_profil").val(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#f_changer').show();
              $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              $('#f_changer').show();
              if(responseJSON['statut']==true)
              {
                $("#fieldset_auth").html(responseJSON['value']);
                curseur();
              }
              else
              {
                $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

    // Clic sur le lien [ Identifiants perdus ! ]
    $('#fieldset_auth').on
    (
      'click',
      '#lien_lost',
      function()
      {
        var base = $('#f_base').length ? $('#f_base').val() : 0 ;
        var ancre = extract_hash( $(this).attr('href') );
        document.location.href = './index.php?page=public_identifiants_perdus'+'&base='+base+'&profil='+ancre;
        return false;
      }
    );

    // Clic sur le lien [ Contacter un administrateur ? ]
    $('#fieldset_auth').on
    (
      'click',
      '#contact_admin',
      function()
      {
        var base = $('#f_base').length ? $('#f_base').val() : 0 ;
        document.location.href = './index.php?page=public_contact_admin'+'&base='+base;
        return false;
      }
    );

    // Afficher / masquer le formulaire d'identifiants SACoche si formulaire ENT possible
    $('#fieldset_auth').on
    (
      'change',
      'input[type=radio]',
      function()
      {
        if($('#f_mode_normal').is(':checked'))
        {
          $("#fieldset_normal, #lien_lost").show();
          $('#f_login').focus();
        }
        else
        {
          $("#fieldset_normal, #lien_lost").hide();
        }
      }
    );

    // Appel en ajax pour tester le numéro de la dernière version (et le comparer avec l'actuelle).
    function tester_version()
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=tester_version',
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_version').addClass('alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            return false;
          },
          success : function(responseJSON)
          {
            $('#ajax_version').addClass(responseJSON['class']).html(responseJSON['texte']).after(responseJSON['after']);
          }
        }
      );
    }
    tester_version();

    // Intercepter la touche entrée
    $('#form_lost').on
    (
      'keyup',
      'input',
      function(e)
      {
        if(e.which==13)  // touche entrée
        {
          $('#submit_lost').click();
          return false;
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $('#form_auth');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          f_base       : { required:true },
          f_partenaire : { required:true },
          f_login      : { required:true , maxlength:LOGIN_LONGUEUR_MAX },
          f_password   : { required:true , maxlength:PASSWORD_LONGUEUR_MAX }
        },
        messages :
        {
          f_base       : { required:"établissement manquant" },
          f_partenaire : { required:"partenariat manquant" },
          f_login      : { required:"nom d'utilisateur manquant" , maxlength:LOGIN_LONGUEUR_MAX+" caractères maximum" },
          f_password   : { required:"mot de passe manquant" , maxlength:PASSWORD_LONGUEUR_MAX+" caractères maximum" }
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
      target : "#ajax_msg",
      beforeSubmit : test_form_avant_envoi,
      error : retour_form_erreur,
      success : retour_form_valide
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire.submit
    (
      function()
      {
        if( ($('#fieldset_normal').length) && !($('#f_mode_normal').is(':checked')) )
        {
          document.location.href = './index.php?sso='+$('#f_base').val();
          return false;
        }
        else
        {
          $(this).ajaxSubmit(ajaxOptions);
          return false;
        }
      }
    );

    // Fonction précédant l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi(formData, jqForm, options)
    {
      $('#ajax_msg').removeAttr('class').html("");
      var readytogo = validation.form();
      if(readytogo)
      {
        $('button').prop('disabled',true);
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      $('button').prop('disabled',false);
      $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseJSON)
    {
      $('button').prop('disabled',false);
      if(responseJSON['statut']==true)
      {
        $('#ajax_msg').attr('class','valide').html("Identification réussie !");
        document.location.href = responseJSON['value'];
      }
      else
      {
        $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
      }
    }

  }
);
