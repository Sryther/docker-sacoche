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

    // Indiquer le nombre de caractères restant autorisés dans le textarea
    $('#f_message').keyup
    (
      function()
      {
        afficher_textarea_reste( $(this) , 999 );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Appel en ajax pour supprimer un logo
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_delete_logo').click
    (
      function()
      {
        $("button").prop('disabled',true);
        $('#ajax_msg_logo').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=delete_logo',
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $("button").prop('disabled',false);
              $('#ajax_msg_logo').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              $("button").prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg_logo').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                initialiser_compteur();
                $('#ajax_msg_logo').removeAttr('class').html('');
                $('#image_logo').attr('src',responseJSON['value']);
                $('#ajax_msg').attr('class','alerte').html("Pensez à valider vos modifications !");
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
    $('#f_logo').change
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
            $("button").prop('disabled',true);
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
      $('#f_logo').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      $("button").prop('disabled',false);
      $('#ajax_msg_logo').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide_logo(responseJSON)
    {
      $('#f_logo').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      $("button").prop('disabled',false);
      if(responseJSON['statut']==false)
      {
        $('#ajax_msg_logo').attr('class','alerte').html(responseJSON['value']);
      }
      else
      {
        initialiser_compteur();
        $('#ajax_msg_logo').removeAttr('class').html('&nbsp;');
        $('#image_logo').attr('src',responseJSON['value']);
        $('#ajax_msg').attr('class','alerte').html("Pensez à valider vos modifications !");
      }
    }

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
          f_upload_logo : { required:false },
          f_adresse_web : { required:false , url:true, maxlength:150 },
          f_message     : { required:false }
        },
        messages :
        {
          f_upload_logo : { },
          f_adresse_web : { url:"adresse invalide (http:// manquant ?)", maxlength:"150 caractères maximum" },
          f_message     : {  }
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
        $('#resultat').html(responseJSON['value']);
      }
      else
      {
        $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
      }
    }

  }
);
