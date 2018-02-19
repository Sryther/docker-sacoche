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
    // Enlever le message ajax au changement d'un élément de formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_select').on
    (
      'change',
      'select, input',
      function()
      {
        $('#ajax_msg').removeAttr('class').html("");
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Réagir au changement dans le premier formulaire (choix principal)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#f_choix_principal").change
    (
      function()
      {
        // Masquer tout
        $('fieldset[id^=fieldset]').hide(0);
        $('#ajax_msg').removeAttr('class').html("");
        $('#ajax_retour').html("");
        // Puis afficher ce qu'il faut
        var objet = $(this).val();
        if(objet=='new_loginmdp')
        {
          maj_eleve_birth();
          maj_f_user();
          $('#fieldset_'+objet).show();
        }
        else if(objet.substring(0,7)=='import_')
        {
          $('#fieldset_'+objet).show();
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Réagir au changement dans le choix d'un profil ou d'un groupe
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#f_profil , #f_groupe").change
    (
      function()
      {
        $('#ajax_msg').removeAttr('class').html("");
        $('#ajax_retour').html("");
        maj_eleve_birth();
        maj_f_user();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Mettre à jour la liste des utilisateurs concernés
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_eleve_birth()
    {
      if($('#f_profil option:selected').val()=='eleves')
      {
        $('#eleve_birth').show();
      }
      else
      {
        $('#eleve_birth').hide();
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Mettre à jour la liste des utilisateurs concernés
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_f_user()
    {
      $('#fieldset_new_loginmdp button').prop('disabled',true);
      $('#div_users').hide();
      // On récupère le profil
      var profil = $('#f_profil option:selected').val();
      // On récupère le regroupement
      var groupe_val = $("#f_groupe option:selected").val();
      if( !profil || !groupe_val )
      {
        return false
      }
      groupe_type = groupe_val.substring(0,1);
      groupe_id   = groupe_val.substring(1);
      $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
      $('#bilan tbody').html('');
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_maj_select_'+profil,
          data : 'f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type+'&f_statut=1'+'&f_multiple=1'+'&f_selection=1'+'&f_nom=f_user',
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==true)
            {
              $('#ajax_msg').attr('class','valide').html("Affichage actualisé !");
              $('#f_user').html(responseJSON['value']);
              $('#div_users').show();
              $('#fieldset_new_loginmdp button').prop('disabled',false);
            }
            else
            {
              $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
            }
          }
        }
      );
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Réagir au clic sur un bouton pour demander un export csv de la base (user_ent -> user_export)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#user_export').click
    (
      function()
      {
        $('#form_select button').prop('disabled',true);
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+'user_export',
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#form_select button').prop('disabled',false);
              $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==true)
              {
                $('#form_select button').prop('disabled',false);
                $('#ajax_msg').attr('class','valide').html("Demande réalisée !");
                $('#ajax_retour').html(responseJSON['value']);
              }
              else
              {
                $('#form_select button').prop('disabled',false);
                $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Soumission du formulaire - choix 1 et 2
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#generer_login , #generer_mdp , #forcer_mdp_birth').click
    (
      function()
      {
        var f_action = $(this).attr('id');
        var profil = $('#f_profil option:selected').val();
        if( !profil )
        {
          $('#ajax_msg').attr('class','erreur').html("Sélectionnez déjà un profil utilisateur !");
          return false;
        }
        if( !$("#f_user input:checked").length )
        {
          $('#ajax_msg').attr('class','erreur').html("Sélectionnez au moins un utilisateur !");
          return false;
        }
        $('#form_select button').prop('disabled',true);
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        // Grouper les checkbox dans un champ unique afin d'éviter tout problème avec une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque) ou "max input vars" généralement fixé à 1000.
        var tab_user = new Array();
        $("#f_user input:checked").each
        (
          function()
          {
            tab_user.push($(this).val());
          }
        );
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+f_action+'&f_profil='+profil+'&f_user='+tab_user,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#form_select button').prop('disabled',false);
              $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('#form_select button').prop('disabled',false);
              if(responseJSON['statut']==true)
              {
                $('#ajax_msg').attr('class','valide').html('Demande réalisée.');
                $('#ajax_retour').html(responseJSON['value']);
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

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire #form_select
    // Upload d'un fichier (avec jquery.form.js)
    // - import csv afin de forcer les logins ou/et mdp élèves (user_ent -> user_import)
    // - envoyer un csv issu de l'ENT
    // - envoyer un csv issu de Gepi
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire_import = $('#form_select');

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions_import =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg",
      error : retour_form_erreur_import,
      success : retour_form_valide_import
    };

    // Vérifications précédant l'envoi du formulaire, déclenchées au choix d'un fichier
    $('#f_import').change
    (
      function()
      {
        var file = this.files[0];
        if( typeof(file) == 'undefined' )
        {
          $('#ajax_msg').removeAttr('class').html('');
          return false;
        }
        else
        {
          var fichier_nom = file.name;
          var fichier_ext = fichier_nom.split('.').pop().toLowerCase();
          if( '.csv.txt.'.indexOf('.'+fichier_ext+'.') == -1 )
          {
            $('#ajax_msg').attr('class','erreur').html('Le fichier "'+fichier_nom+'" n\'a pas une extension "csv" ou "txt".');
            return false;
          }
          else
          {
            $('#form_select button').prop('disabled',true);
            $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
            $('#ajax_retour').html("");
            formulaire_import.submit();
          }
        }
      }
    );

    // Envoi du formulaire (avec jquery.form.js)
    formulaire_import.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions_import);
        return false;
      }
    );

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur_import(jqXHR, textStatus, errorThrown)
    {
      $('#f_import').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      $('#form_select button').prop('disabled',false);
      $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide_import(responseJSON)
    {
      $('#f_import').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      $('#form_select button').prop('disabled',false);
      if(responseJSON['statut']==false)
      {
        $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
      }
      else
      {
        initialiser_compteur();
        $('#form_select button').prop('disabled',false);
        $('#ajax_msg').attr('class','valide').html("Demande réalisée !");
        $('#ajax_retour').html(responseJSON['value']);
      }
    }

    $('button.fichier_import').click
    (
      function()
      {
        var objet = $(this).attr('id'); // import_loginmdp | import_ent | import_gepi_profs | import_gepi_parents | import_gepi_eleves
        $('#f_action').val(objet);
        $('#f_import').click();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Réagir au clic sur un bouton afin de demander la duplication d'un champ
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('button[name=dupliquer]').click
    (
      function()
      {
        var f_action = $(this).attr('id');
        $('#ajax_retour').html('&nbsp;');
        $('#form_select button').prop('disabled',true);
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+f_action,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#form_select button').prop('disabled',false);
              $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('#form_select button').prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                $('#ajax_msg').attr('class','valide').html("Demande réalisée !");
                if(responseJSON['value']) // pour les appels de webservices qui retournent un bilan
                {
                  $('#ajax_retour').html(responseJSON['value']);
                }
              }
            }
          }
        );
      }
    );

  }
);
