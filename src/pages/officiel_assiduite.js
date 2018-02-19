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

    var id_periode_import = $('#f_periode_import option:selected').val();
    var f_action = '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Réagir au changement de période ou d'origine du fichier
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function afficher_upload()
    {
      // Masquer tout
      $('#form_fichier').children('ul').hide(0);
        // Puis afficher ce qu'il faut
      if( id_periode_import && f_action )
      {
        $('#ajax_msg_'+f_action).removeAttr('class').html('&nbsp;');
        $('#puce_'+f_action).show(0);
      }
    }

    $('#f_periode_import').change
    (
      function()
      {
        id_periode_import = $('#f_periode_import option:selected').val();
        afficher_upload();
      }
    );

    $("#f_choix_principal").change
    (
      function()
      {
        f_action = $(this).val();
        afficher_upload();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Changement de classe
// -> choisir automatiquement la meilleure période si un changement manuel de période n'a jamais été effectué
// -> afficher le formulaire de périodes s'il est masqué
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var autoperiode = true; // Tant qu'on ne modifie pas manuellement le choix des périodes, modification automatique du formulaire

    $('#f_periode').change
    (
      function()
      {
        autoperiode = false;
      }
    );

    function selectionner_periode_adaptee(id_groupe)
    {
      if(typeof(tab_groupe_periode[id_groupe])!='undefined')
      {
        for(var id_periode in tab_groupe_periode[id_groupe]) // Parcourir un tableau associatif...
        {
          var tab_split = tab_groupe_periode[id_groupe][id_periode].split('_');
          if( (date_mysql>=tab_split[0]) && (date_mysql<=tab_split[1]) )
          {
            $("#f_periode option[value="+id_periode+"]").prop('selected',true);
            break;
          }
        }
      }
    }

    $('#f_groupe').change
    (
      function()
      {
        var id_groupe = $('#f_groupe option:selected').val();
        // Modification automatique du formulaire : périodes
        if(autoperiode && id_groupe)
        {
          // Rechercher automatiquement la meilleure période
          selectionner_periode_adaptee(id_groupe);
        }
        // Afficher la zone de choix des périodes
        if(id_groupe)
        {
          $('#f_periode').removeAttr('class');
        }
        else
        {
          $('#f_periode').addClass("hide");
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire #form_fichier
    // Upload d'un fichier (avec jquery.form.js)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Indéfini si pas de droit d'accès à cette fonctionnalité.
    if( $('#form_fichier').length )
    {

      // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
      // À définir avant la déclaration de ajaxOptions_import sinon Firefox plante mystétieusement... juste parce que cette partie est dans une boucle if{} !
      function retour_form_erreur_import(jqXHR, textStatus, errorThrown)
      {
        $('#f_import').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
        $('#form_fichier button').prop('disabled',false);
        $('#ajax_msg_'+f_action).attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
      }

      // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
      // À définir avant la déclaration de ajaxOptions_import sinon Firefox plante mystérieusement... juste parce que cette partie est dans une boucle if{} !
      function retour_form_valide_import(responseJSON)
      {
        $('#f_import').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
        $('#form_fichier button').prop('disabled',false);
        if(responseJSON['statut']==false)
        {
          $('#ajax_msg_'+f_action).attr('class','alerte').html(responseJSON['value']);
        }
        else
        {
          initialiser_compteur();
          $('#zone_confirmer').children('div').hide(0);
          if( (f_action=='import_sconet') || (f_action=='import_siecle') )
          {
            $('#sconet_date_export').html(responseJSON['date_export']);
            $('#sconet_libelle'    ).html(responseJSON['libelle']);
            $('#sconet_date_debut' ).html(responseJSON['date_debut']);
            $('#sconet_date_fin'   ).html(responseJSON['date_fin']);
          }
          else if(f_action=='import_gepi')
          {
            $('#gepi_eleves_nb').html(responseJSON['eleves_nb']);
          }
          else if(f_action=='import_pronote')
          {
            $('#pronote_objet_1'   ).html(responseJSON['objet']+'s');
            $('#pronote_objet_2'   ).html('0 '+responseJSON['objet']);
            $('#pronote_eleves_nb' ).html(responseJSON['eleves_nb']);
            $('#pronote_date_debut').html(responseJSON['date_debut']);
            $('#pronote_date_fin'  ).html(responseJSON['date_fin']);
          }
          else if(f_action=='import_moliere')
          {
            $('#moliere_eleves_nb').html(responseJSON['eleves_nb']);
          }
          else if(f_action=='import_entlibre')
          {
            $('#entlibre_eleves_nb').html(responseJSON['eleves_nb']);
          }
          else if(f_action=='import_entelyco')
          {
            $('#entelyco_eleves_nb' ).html(responseJSON['eleves_nb']);
            $('#entelyco_date_debut').html(responseJSON['date_debut']);
            $('#entelyco_date_fin'  ).html(responseJSON['date_fin']);
          }
          $('#periode_import').html($('#f_periode_import option:selected').text());
          $('#ajax_msg_'+f_action).removeAttr('class').html('');
          $('#ajax_msg_confirm').removeAttr('class').html('');
          $('#comfirm_'+f_action).show(0);
          $.fancybox( { 'href':'#zone_confirmer' , onStart:function(){$('#zone_confirmer').css("display","block");} , onClosed:function(){$('#zone_confirmer').css("display","none");} , 'modal':true , 'minWidth':700 , 'centerOnScroll':true } );
        }
      }

      // Le formulaire qui va être analysé et traité en AJAX
      var formulaire_import = $('#form_fichier');

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
            $('#ajax_msg_'+f_action).removeAttr('class').html('');
            return false;
          }
          else if (!id_periode_import)
          {
            $('#ajax_msg_'+f_action).attr('class','erreur').html("Choisir d'abord la période concernée.");
            return false;
          }
          else
          {
            var fichier_nom = file.name;
            var fichier_ext = fichier_nom.split('.').pop().toLowerCase();
            if ( (f_action=='import_sconet') && ('.xml.zip.'.indexOf('.'+fichier_ext+'.')==-1) )
            {
              $('#ajax_msg_'+f_action).attr('class','erreur').html('Le fichier "'+fichier_nom+'" n\'a pas une extension "xml" ou "zip".');
              return false;
            }
            else if ( (f_action=='import_siecle') && ('.xml.zip.'.indexOf('.'+fichier_ext+'.')==-1) )
            {
              $('#ajax_msg_'+f_action).attr('class','erreur').html('Le fichier "'+fichier_nom+'" n\'a pas une extension "xml" ou "zip".');
              return false;
            }
            else if ( (f_action=='import_gepi') && ('.csv.txt.'.indexOf('.'+fichier_ext+'.')==-1) )
            {
              $('#ajax_msg_'+f_action).attr('class','erreur').html('Le fichier "'+fichier_nom+'" n\'a pas une extension "csv" ou "txt".');
              return false;
            }
            else if ( (f_action=='import_pronote') && ('.xml.zip.'.indexOf('.'+fichier_ext+'.')==-1) )
            {
              $('#ajax_msg_'+f_action).attr('class','erreur').html('Le fichier "'+fichier_nom+'" n\'a pas une extension "xml" ou "zip".');
              return false;
            }
            else if ( (f_action=='import_moliere') && ('.csv.txt.'.indexOf('.'+fichier_ext+'.')==-1) )
            {
              $('#ajax_msg_'+f_action).attr('class','erreur').html('Le fichier "'+fichier_nom+'" n\'a pas une extension "csv" ou "txt".');
              return false;
            }
            else
            {
              $('#form_fichier button').prop('disabled',true);
              $('#ajax_msg_'+f_action).attr('class','loader').html("En cours&hellip;");
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

      $('button.fichier_import').click
      (
        function()
        {
          $('#f_upload_action').val(f_action); // import_sconet | import_siecle | import_gepi | import_pronote | import_moliere
          $('#f_upload_periode').val(id_periode_import);
          $('#f_import').click();
        }
      );

    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Confirmation du traitement du fichier
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#confirmer_import').click
    (
      function()
      {
        $('#zone_confirmer button').prop('disabled',true);
        $('#ajax_msg_confirm').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+'traitement_'+f_action+'&f_periode='+id_periode_import,
            responseType: 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#zone_confirmer button').prop('disabled',false);
              $('#ajax_msg_confirm').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              $('#zone_confirmer button').prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg_confirm').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                $('#zone_saisir h2').html('Résultat du traitement');
                $('#titre_saisir').html('');
                $('#table_saisir tbody').html(responseJSON['value']);
                $('#zone_saisir form').hide(0);
                $.fancybox( { 'href':'#zone_saisir' , onStart:function(){$('#zone_saisir').css("display","block");} , onClosed:function(){$('#zone_saisir').css("display","none");} , 'minWidth':600 , 'centerOnScroll':true } );
                initialiser_compteur();
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Afficher le formulaire de saisie manuel
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $("#form_manuel");

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          f_groupe        : { required:true },
          f_periode       : { required:true }
        },
        messages :
        {
          f_groupe        : { required:"classe manquante" },
          f_periode       : { required:"période manquante" }
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
      responseType: 'json',
      clearForm : false,
      resetForm : false,
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
      $('#ajax_msg_manuel').removeAttr('class').html("");
      var readytogo = validation.form();
      if(readytogo)
      {
        $('button').prop('disabled',true);
        $('#ajax_msg_manuel').attr('class','loader').html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      $('button').prop('disabled',false);
      $('#ajax_msg_manuel').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseJSON)
    {
      $('button').prop('disabled',false);
      if(responseJSON['statut']==false)
      {
        $('#ajax_msg_manuel').attr('class','alerte').html(responseJSON['value']);
      }
      else
      {
        $('#ajax_msg_manuel').removeAttr('class').html('');
        $('#zone_saisir h2').html('Saisie des absences et retards');
        $('#titre_saisir').html($('#f_periode option:selected').text()+' | '+$('#f_groupe option:selected').text());
        $('#table_saisir tbody').html(responseJSON['value']);
        $('#ajax_msg_saisir').removeAttr('class').html('&nbsp;');
        $('#zone_saisir form').show(0);
        $.fancybox( { 'href':'#zone_saisir' , onStart:function(){$('#zone_saisir').css("display","block");} , onClosed:function(){$('#zone_saisir').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
        initialiser_compteur();
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Modification d'une saisie : alerter besoin d'enregistrer
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_saisir').on
    (
      'change',
      'input[type=text]',
      function()
      {
        $('#ajax_msg_saisir').attr('class','alerte').html('Penser à enregistrer les modifications !');
        $('#fermer_zone_saisir').attr('class',"annuler").html('Annuler / Retour');
        return false;
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Intercepter la touche entrée
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_saisir').on
    (
      'keyup',
      'input[type=text]',
      function(e)
      {
        if(e.which==13)  // touche entrée
        {
          $('#Enregistrer_saisies').click();
        }
        return false;
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour envoyer les saisies
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#Enregistrer_saisies').click
    (
      function()
      {
        $('#zone_saisir button').prop('disabled',true);
        // Récupérer les infos
        var tab_infos = new Array();
        $("#table_saisir tbody tr").each
        (
          function()
          {
            var user_id = $(this).attr('id').substring(3);
            tab_infos.push( user_id + '.' + $('#td1_'+user_id).val() + '.' + $('#td2_'+user_id).val() + '.' + $('#td3_'+user_id).val() + '.' + $('#td4_'+user_id).val() );
          }
        );
        $('#ajax_msg_saisir').attr('class','loader').html("En cours&hellip;");
        // Les envoyer en ajax
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=enregistrer_saisies'+'&f_periode='+$('#f_periode option:selected').val()+'&f_data='+tab_infos.join('_'),
            responseType: 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#zone_saisir button').prop('disabled',false);
              $('#ajax_msg_saisir').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('#zone_saisir button').prop('disabled',false);
              if(responseJSON['statut']==true)
              {
                $('#ajax_msg_saisir').attr('class','valide').html("Saisies enregistrées !");
                $('#fermer_zone_saisir').attr('class',"retourner").html('Retour');
              }
              else
              {
                $('#ajax_msg_saisir').attr('class','alerte').html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur un bouton pour fermer un cadre
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_confirmer').on( 'click' , '#fermer_zone_confirmer' , function(){ $.fancybox.close(); return false; } );
    $('#zone_saisir'   ).on( 'click' , '#fermer_zone_saisir'    , function(){ $.fancybox.close(); return false; } );

  }
);
