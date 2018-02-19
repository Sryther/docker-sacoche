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

    // ********************
    // * Départ | Étape n -> Étape 1
    // ********************

    $(document).on
    (
      'click',
      'a.step1',
      function()
      {
        $("#step li").removeAttr('class');
        $("#step1").addClass("on");
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_step=1',
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                $('#zone_consignes').html(responseJSON['value']);
                $('#form_info_heberg').html('');
                $('#form_param_mysql').html('');
              }
            }
          }
        );
      }
    );

    // ********************
    // * Étape 1|n -> Étape 2
    // ********************

    $(document).on
    (
      'click',
      'a.step2',
      function()
      {
        $("#step li").removeAttr('class');
        $("#step2").addClass("on");
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_step=2',
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                $('#zone_consignes').html(responseJSON['value']);
                $('#form_info_heberg').html('');
                $('#form_param_mysql').html('');
              }
            }
          }
        );
      }
    );

    // ********************
    // * Étape 2|n -> Étape 3
    // ********************

    $(document).on
    (
      'click',
      'a.step3',
      function()
      {
        $("#step li").removeAttr('class');
        $("#step3").addClass("on");
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_step=3',
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                $('#zone_consignes').html(responseJSON['value']);
                $('#form_info_heberg').html('');
                $('#form_param_mysql').html('');
                $('#f_installation').focus();
              }
            }
          }
        );
      }
    );

    // ********************
    // * Étape 3 -> Étape 4
    // ********************

    $(document).on
    (
      'click',
      'a.step4',
      function()
      {
        var f_installation = $(this).attr('id');
        $("#step li").removeAttr('class');
        $("#step4").addClass("on");
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_step=4'+'&f_installation='+f_installation,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                $('#zone_consignes').html('');
                $('#form_info_heberg').html(responseJSON['value']);
                $('#form_param_mysql').html('');
                $('#f_denomination').focus();
              }
            }
          }
        );
      }
    );

    // ********************
    // * Étape 4 -> Étape 41
    // ********************

    // Analyse de la robustesse du mot de passe
    $(document).on
    (
      'keyup',
      '#f_password1',
      function()
      {
        analyse_mdp( $(this).val() );
      }
    );

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire_info_heberg = $('#form_info_heberg');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation_info_heberg = formulaire_info_heberg.validate
    (
      {
        rules :
        {
          f_installation : { required:true },
          f_denomination : { required:true , maxlength:60 },
          f_uai :          { required:false , uai_format:true , uai_clef:true },
          f_adresse_site : { required:false , url:true, maxlength:150 },
          f_nom :          { required:true , maxlength:NOM_LONGUEUR_MAX },
          f_prenom :       { required:true , maxlength:PRENOM_LONGUEUR_MAX },
          f_courriel :     { required:true , email:true , maxlength:COURRIEL_LONGUEUR_MAX },
          f_password1 :    { required:true , minlength:6 , maxlength:PASSWORD_LONGUEUR_MAX },
          f_password2 :    { required:true , minlength:6 , maxlength:PASSWORD_LONGUEUR_MAX , equalTo: "#f_password1" }
        },
        messages :
        {
          f_installation : { required:"type manquant" },
          f_denomination : { required:"dénomination manquante" , maxlength:"60 caractères maximum" },
          f_uai :          { uai_format:"n°UAI invalide" , uai_clef:"n°UAI invalide" },
          f_adresse_site : { url:"adresse invalide (http:// manquant ?)", maxlength:"150 caractères maximum" },
          f_nom :          { required:"nom manquant" , maxlength:NOM_LONGUEUR_MAX+" caractères maximum" },
          f_prenom :       { required:"prénom manquant" , maxlength:PRENOM_LONGUEUR_MAX+" caractères maximum" },
          f_courriel :     { required:"courriel manquant" , email:"courriel invalide", maxlength:COURRIEL_LONGUEUR_MAX+" caractères maximum" },
          f_password1 :    { required:"mot de passe manquant" , minlength:"6 caractères minimum" , maxlength:PASSWORD_LONGUEUR_MAX+" caractères maximum" },
          f_password2 :    { required:"mot de passe manquant" , minlength:"6 caractères minimum" , maxlength:PASSWORD_LONGUEUR_MAX+" caractères maximum" , equalTo:"mots de passe différents" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element) { element.after(error); }
        // success: function(label) {label.text("ok").attr('class','valide');} Pas pour des champs soumis à vérification PHP
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions_info_heberg =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg",
      beforeSubmit : test_form_info_heberg_avant_envoi,
      error : retour_form_info_heberg_erreur,
      success : retour_form_info_heberg_valide
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire_info_heberg.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions_info_heberg);
        return false;
      }
    );

    // Fonction précédant l'envoi du formulaire (avec jquery.form.js)
    function test_form_info_heberg_avant_envoi(formData, jqForm, options)
    {
      $('#ajax_msg').removeAttr('class').html("");
      var readytogo = validation_info_heberg.form();
      if(readytogo)
      {
        $('button').prop('disabled',true);
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_info_heberg_erreur(jqXHR, textStatus, errorThrown)
    {
      $('button').prop('disabled',false);
      $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_info_heberg_valide(responseJSON)
    {
      $('button').prop('disabled',false);
      if(responseJSON['statut']==false)
      {
        $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
      }
      else
      {
        $('#zone_consignes').html(responseJSON['value']);
        $('#form_info_heberg').html('');
        $('#form_param_mysql').html('');
      }
    }

    // ********************
    // * Étape 4|41|n -> Étape 5
    // ********************

    $(document).on
    (
      'click',
      'a.step5',
      function()
      {
        $("#step li").removeAttr('class');
        $("#step5").addClass("on");
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_step=5',
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              $('#zone_consignes').html(responseJSON['value']);
              $('#form_info_heberg').html('');
              $('#form_param_mysql').html();
            }
          }
        );
      }
    );

    // ********************
    // * Étape 5 -> Étape 6
    // ********************

    $(document).on
    (
      'click',
      'a.step6',
      function()
      {
        $("#step li").removeAttr('class');
        $("#step6").addClass("on");
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_step=6',
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              $('#zone_consignes').html('');
              $('#form_info_heberg').html('');
              $('#form_param_mysql').html(responseJSON['value']);
              $('#f_host').focus();
            }
          }
        );
      }
    );

    // ********************
    // * Étape 6|61 -> Étape 61|62
    // ********************

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire_param_mysql = $('#form_param_mysql');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation_param_mysql = formulaire_param_mysql.validate
    (
      {
        rules :
        {
          f_host : { required:true },
          f_port : { required:true , digits:true },
          f_name : { required:true },
          f_user : { required:true },
          f_pass : { required:false }
        },
        messages :
        {
          f_host : { required:"champ obligatoire" },
          f_port : { required:"champ obligatoire" , digits:"nombre entier requis" },
          f_name : { required:"champ obligatoire" },
          f_user : { required:"champ obligatoire" },
          f_pass : { }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element) { element.after(error); }
        // success: function(label) {label.text("ok").attr('class','valide');} Pas pour des champs soumis à vérification PHP
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions_param_mysql =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg",
      beforeSubmit : test_form_param_mysql_avant_envoi,
      error : retour_form_param_mysql_erreur,
      success : retour_form_param_mysql_valide
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire_param_mysql.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions_param_mysql);
        return false;
      }
    );

    // Fonction précédant l'envoi du formulaire (avec jquery.form.js)
    function test_form_param_mysql_avant_envoi(formData, jqForm, options)
    {
      $('#ajax_msg').removeAttr('class').html("");
      var readytogo = validation_param_mysql.form();
      if(readytogo)
      {
        $('button').prop('disabled',true);
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_param_mysql_erreur(jqXHR, textStatus, errorThrown)
    {
      $('button').prop('disabled',false);
      $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_param_mysql_valide(responseJSON)
    {
      $('button').prop('disabled',false);
      if(responseJSON=='') // En cas de port incorrect, le test de la connexion peut durer longtemps, et on récupère une chaine vide à la place de l'erreur, qui devrait être "Une tentative de connexion a échoué car le parti connecté n’a pas répondu convenablement au-delà d’une certaine durée ou une connexion établie a échoué car l’hôte de connexion n’a pas répondu."
      {
        $('#ajax_msg').attr('class','alerte').html('Impossible de se connecter à  MySQL ["La tentative de connexion a échoué : MySQL n\'a pas répondu (port probablement incorrect)."] !');
      }
      else if(responseJSON['statut']==false)
      {
        $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
      }
      else if(responseJSON['value'].substring(0,10)=='<fieldset>')
      {
        // choix de la base (mono-structure)
        $('#zone_consignes').html('');
        $('#form_info_heberg').html('');
        $('#form_param_mysql').html(responseJSON['value']);
        $('#f_name').focus();
      }
      else
      {
        // paramètres mysql et base ok
        $('#zone_consignes').html(responseJSON['value']);
        $('#form_info_heberg').html('');
        $('#form_param_mysql').html('');
      }
    }

    // ********************
    // * Étape 6|61|62|n -> Étape 7
    // ********************

    $(document).on
    (
      'click',
      'a.step7',
      function()
      {
        $("#step li").removeAttr('class');
        $("#step7").addClass("on");
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_step=7',
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              $('#zone_consignes').html(responseJSON['value']);
              $('#form_info_heberg').html('');
              $('#form_param_mysql').html('');
            }
          }
        );
      }
    );

  }
);
