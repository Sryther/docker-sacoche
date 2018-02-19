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
    // Appel en ajax pour initialiser/actualiser le select f_logo
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function chargement_select_logo()
    {
      $('#ajax_logo').attr('class','loader').html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=select_logo',
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_logo').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            return false;
          },
          success : function(responseJSON)
          {
            if(responseJSON['statut']==false)
            {
              $('#ajax_logo').attr('class','alerte').html(responseJSON['value']);
            }
            else
            {
              $('#ajax_logo').removeAttr('class').html('');
              $("#f_logo").html(responseJSON['value']);
            }
          }
        }
      );
    }
    chargement_select_logo();

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Appel en ajax pour initialiser/actualiser le ul listing_logos
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function chargement_ul_logo()
    {
      $('#ajax_listing').attr('class','loader').html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=listing_logos',
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_listing').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            return false;
          },
          success : function(responseJSON)
          {
            if(responseJSON['statut']==false)
            {
              $('#ajax_listing').attr('class','alerte').html(responseJSON['value']);
            }
            else
            {
              $('#ajax_listing').removeAttr('class').html('');
              $("#listing_logos").html(responseJSON['value']);
            }
          }
        }
      );
    }
    chargement_ul_logo();

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Appel en ajax pour supprimer un logo
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#listing_logos').on
    (
      'click',
      'q.supprimer',
      function()
      {
        memo_li = $(this).parent();
        logo = $(this).prev().attr('alt');
        $('#ajax_listing').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=delete_logo'+'&f_logo='+logo,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_listing').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              if(responseJSON['statut']==false)
              {
                $('#ajax_listing').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                $('#ajax_listing').removeAttr('class').html('');
                memo_li.remove();
                chargement_select_logo();
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire #form_logo
    // Upload d'un fichier (avec jquery.form.js)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire_logo = $('#form_logo');

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions_logo =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg_logo",
      error : retour_form_erreur_logo,
      success : retour_form_valide_logo
    };

    // Vérifications précédant l'envoi du formulaire, déclenchées au choix d'un fichier
    $('#f_import_logo').change
    (
      function()
      {
        var file = this.files[0];
        if( typeof(file) == 'undefined' )
        {
          $('#ajax_msg_logo').removeAttr('class').html('');
          return false;
        }
        else
        {
          var fichier_nom = file.name;
          var fichier_ext = fichier_nom.split('.').pop().toLowerCase();
          if( '.bmp.gif.jpg.jpeg.png.'.indexOf('.'+fichier_ext+'.') == -1 )
          {
            $('#ajax_msg_logo').attr('class','erreur').html('Le fichier "'+fichier_nom+'" n\'a pas une extension autorisée (bmp gif jpg jpeg png).');
            return false;
          }
          else
          {
            $("#bouton_choisir_logo").prop('disabled',true);
            $('#ajax_msg_logo').attr('class','loader').html("En cours&hellip;");
            formulaire_logo.submit();
          }
        }
      }
    );

    // Envoi du formulaire (avec jquery.form.js)
    formulaire_logo.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions_logo);
        return false;
      }
    );

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur_logo(jqXHR, textStatus, errorThrown)
    {
      $('#f_import_logo').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      $("#bouton_choisir_logo").prop('disabled',false);
      $('#ajax_msg_logo').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide_logo(responseJSON)
    {
      $('#f_import_logo').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      $("#bouton_choisir_logo").prop('disabled',false);
      if(responseJSON['statut']==false)
      {
        $('#ajax_msg_logo').attr('class','alerte').html(responseJSON['value']);
      }
      else
      {
        initialiser_compteur();
        $('#ajax_msg_logo').attr('class','valide').html('');
        chargement_select_logo();
        chargement_ul_logo();
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Gérer les focus et click pour les boutons radio
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_cnil_numero').focus
    (
      function()
      {
        if($('#f_cnil_oui').is(':checked')==false)
        {
          $('#f_cnil_oui').prop('checked',true);
          $("#cnil_dates").show();
          return false; // important, sinon pb de récursivité
        }
      }
    );

    $('#f_cnil_oui').click
    (
      function()
      {
        $('#f_cnil_numero').focus();
        $("#cnil_dates").show();
      }
    );

    $('#f_cnil_non').click
    (
      function()
      {
        $("#cnil_dates").hide();
        $("#f_cnil_numero").val('');
        $("#f_cnil_date_engagement").val('');
        $("#f_cnil_date_recepisse").val('');
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire principal
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $('#form_gestion');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          f_denomination         : { required:true , maxlength:60 },
          f_uai                  : { required:false , uai_format:true , uai_clef:true },
          f_adresse_site         : { required:false , url:true, maxlength:150 },
          f_logo                 : { required:false },
          f_cnil_etat            : { required:true },
          f_cnil_numero          : { required:function(){return $('#f_cnil_oui').is(':checked');} , digits:true },
          f_cnil_date_engagement : { required:function(){return $('#f_cnil_oui').is(':checked');} , dateITA:true },
          f_cnil_date_recepisse  : { required:function(){return $('#f_cnil_oui').is(':checked');} , dateITA:true },
          f_nom                  : { required:true , maxlength:NOM_LONGUEUR_MAX },
          f_prenom               : { required:true , maxlength:PRENOM_LONGUEUR_MAX },
          f_courriel             : { required:true , email:true , maxlength:COURRIEL_LONGUEUR_MAX }
        },
        messages :
        {
          f_denomination         : { required:"dénomination manquante" , maxlength:"60 caractères maximum" },
          f_uai                  : { uai_format:"n°UAI invalide" , uai_clef:"n°UAI invalide" },
          f_adresse_site         : { url:"adresse invalide (http:// manquant ?)", maxlength:"150 caractères maximum" },
          f_logo                 : { },
          f_cnil_etat            : { required:"indication CNIL manquante" },
          f_cnil_numero          : { required:"numéro CNIL manquant" , digits:"nombre entier requis" },
          f_cnil_date_engagement : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_cnil_date_recepisse  : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_nom                  : { required:"nom manquant" , maxlength:NOM_LONGUEUR_MAX+" caractères maximum" },
          f_prenom               : { required:"prénom manquant" , maxlength:PRENOM_LONGUEUR_MAX+" caractères maximum" },
          f_courriel             : { required:"courriel manquant" , email:"courriel invalide", maxlength:COURRIEL_LONGUEUR_MAX+" caractères maximum" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          if(element.attr("type")=="radio") {$('#f_cnil_numero').after(error);}
          else if(element.attr("size")==9){ element.next().after(error); }
          else { element.after(error); }
        }
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
        $(this).ajaxSubmit(ajaxOptions);
        return false;
      }
    );

    // Fonction précédant l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi(formData, jqForm, options)
    {
      $('#ajax_msg').removeAttr('class').html("");
      var readytogo = validation.form();
      if(readytogo)
      {
        $("button").prop('disabled',true);
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      $("button").prop('disabled',false);
      $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseJSON)
    {
      initialiser_compteur();
      $("button").prop('disabled',false);
      if(responseJSON['statut']==true)
      {
        $('#ajax_msg').attr('class','valide').html("Données enregistrées !");
      }
      else
      {
        $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
      }
    }

  }
);
