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

    var listing_id = new Array();
    var f_action = '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Demande d'export des bases => soumission du formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_exporter').click
    (
      function()
      {
        if( $("#f_base input:checked").length==0 )
        {
          $('#ajax_msg_export').attr('class','erreur').html("structure(s) manquante(s)");
          return false;
        }
        // Grouper les checkbox dans un champ unique afin d'éviter tout problème avec une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque) ou "max input vars" généralement fixé à 1000.
        var bases = new Array(); $("#f_base input:checked").each(function(){bases.push($(this).val());});
        $("button").prop('disabled',true);
        $('#ajax_msg_export').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=exporter'+'&f_listing_id='+bases,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $("button").prop('disabled',false);
              $('#ajax_msg_export').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $("button").prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg_export').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                var max = responseJSON['value'];
                $('#ajax_msg_export').attr('class','loader').html('Export en cours : étape 1 sur ' + max + '...');
                $('#puce_info_export').html('<li>Ne pas interrompre la procédure avant la fin du traitement !</li>');
                $('#ajax_export_num').html(1);
                $('#ajax_export_max').html(max);
                $('#div_info_export').show('fast');
                $('#zone_actions_export').hide('fast');
                exporter();
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Demande d'export des bases => étapes du traitement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function exporter()
    {
      var num = parseInt( $('#ajax_export_num').html() , 10 );
      var max = parseInt( $('#ajax_export_max').html() , 10 );
      // Appel en ajax
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=exporter'+'&num='+num+'&max='+max,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg_export').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            $('#puce_info_export').html('<li><a id="a_reprise_export" href="#">Reprendre la procédure à l\'étape ' + num + ' sur ' + max + '.</a></li>');
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==false)
            {
              $('#ajax_msg_export').attr('class','alerte').html(responseJSON['value']);
              $('#puce_info_export').html('<li><a id="a_reprise_export" href="#">Reprendre la procédure à l\'étape ' + num + ' sur ' + max + '.</a></li>');
            }
            else
            {
              num++;
              if(num > max)  // Utilisation de parseInt obligatoire sinon la comparaison des valeurs pose ici pb
              {
                var fichier_csv = responseJSON['csv'];
                var fichier_zip = responseJSON['zip'];
                $('#ajax_msg_export').attr('class','valide').html('Export terminé.');
                var li1 = '<li><a target="_blank" rel="noopener noreferrer" href="'+fichier_csv+'">Récupérer le listing des bases exportées au format <em>CSV</em>.</a></li>';
                var li2 = '<li><a target="_blank" rel="noopener noreferrer" href="'+fichier_zip+'">Récupérer le fichier des bases sauvegardées au format <em>ZIP</em>.</a></li>';
                $('#puce_info_export').html(li1+li2);
                $('#zone_actions_export').show('fast');
                $("button").prop('disabled',false);
              }
              else
              {
                $('#ajax_export_num').html(num);
                $('#ajax_msg_export').attr('class','loader').html('Export en cours : étape ' + num + ' sur ' + max + '...');
                $('#puce_info_export').html('<li>Ne pas interrompre la procédure avant la fin du traitement !</li>');
                exporter();
              }
            }
          }
        }
      );
    }

    $('#puce_info_export').on
    (
      'click',
      '#a_reprise_export',
      function()
      {
        num = $('#ajax_export_num').html();
        max = $('#ajax_export_max').html();
        $('#ajax_msg_export').attr('class','loader').html('Export en cours : étape ' + num + ' sur ' + max + '...');
        $('#puce_info_export').html('<li>Ne pas interrompre la procédure avant la fin du traitement !</li>');
        exporter();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Initialisation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    if( $('#f_base input:checked').length )
    {
      $('#bouton_exporter').click();
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire #form_importer
    // Upload d'un fichier (avec jquery.form.js)
    // Réagir au clic sur un bouton pour uploader un fichier csv ou zip à importer
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire_import = $('#form_importer');

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
        else
        {
          var fichier_nom = file.name;
          var fichier_ext = fichier_nom.split('.').pop().toLowerCase();
          if ( (f_action=='importer_csv') && ('.csv.txt.'.indexOf('.'+fichier_ext+'.')==-1) )
          {
            $('#ajax_msg_'+f_action).attr('class','erreur').html('Le fichier "'+fichier_nom+'" n\'a pas une extension "csv" ou "txt".');
            return false;
          }
          else if ( (f_action=='importer_zip') && (fichier_ext!='zip') )
          {
            $('#ajax_msg_'+f_action).attr('class','erreur').html('Le fichier "'+fichier_nom+'" n\'a pas une extension "zip".');
            return false;
          }
          else
          {
            $('#form_importer button').prop('disabled',true);
            $('#ajax_msg_'+f_action).attr('class','loader').html("En cours&hellip;");
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
      $('#form_importer button').prop('disabled',false);
      $('#ajax_msg_'+f_action).attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide_import(responseJSON)
    {
      $('#f_import').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      $('#form_importer button').prop('disabled',false);
      if(responseJSON['statut']==false)
      {
        $('#ajax_msg_'+f_action).attr('class','alerte').html(responseJSON['value']);
        if(f_action=='importer_csv')
        {
          $('#div_zip , #div_import , #div_info_import , #structures').hide('fast');
        }
        else
        {
          $('#div_import , #div_info_import , #structures').hide('fast');
        }
      }
      else
      {
        initialiser_compteur();
        if(f_action=='importer_csv')
        {
          $('#ajax_msg_'+f_action).attr('class','valide').html("Fichier bien reçu ; "+responseJSON['value']+".");
          $('#div_import , #div_info_import , #structures').hide('fast');
          $('#ajax_msg_zip').removeAttr('class').html('&nbsp;');
          $('#div_zip').show('fast');
        }
        else
        {
          $('#ajax_msg_'+f_action).attr('class','valide').html("Fichier bien reçu ; sauvegarde(s) extraite(s).");
          $('#div_info_import , #structures').hide('fast');
          $('#div_import').show('fast');
          $('#ajax_import_num').html(1);
          $('#ajax_import_max').html(responseJSON['value']);
        }
      }
    }

    $('button.fichier_import').click
    (
      function()
      {
        f_action = $(this).attr('id');
        $('#f_upload_action').val(f_action); // importer_csv | importer_zip
        $('#f_import').click();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Demande d'import des bases => soumission du formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_importer').click
    (
      function()
      {
        $("button").prop('disabled',true);
        var num = $('#ajax_import_num').html();
        var max = $('#ajax_import_max').html();
        $('#ajax_msg_import').attr('class','loader').html('Import en cours : étape ' + num + ' sur ' + max + '...');
        $('#puce_info_import').html('<li>Ne pas interrompre la procédure avant la fin du traitement !</li>');
        $('#div_info_import').show('fast');
        $('#structures').hide('fast').children('#table_action').children('tbody').html('');
        importer();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Demande d'import des bases => étapes du traitement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function importer()
    {
      var num = parseInt( $('#ajax_import_num').html() , 10 );
      var max = parseInt( $('#ajax_import_max').html() , 10 );
      // Appel en ajax
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=importer'+'&num='+num+'&max='+max,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg_import').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            $('#puce_info_import').html('<li><a id="a_reprise_import" href="#">Reprendre la procédure à l\'étape ' + num + ' sur ' + max + '.</a></li>');
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==false)
            {
              $('#ajax_msg_import').attr('class','alerte').html(responseJSON['value']);
              $('#puce_info_import').html('<li><a id="a_reprise_import" href="#">Reprendre la procédure à l\'étape ' + num + ' sur ' + max + '.</a></li>');
            }
            else
            {
              num++;
              if(num > max)  // Utilisation de parseInt obligatoire sinon la comparaison des valeurs pose ici pb
              {
                $('#ajax_msg_import').attr('class','valide').html('');
                $('#puce_info_import').html('<li>Import terminé !</li>');
                $('#ajax_msg_importer_csv , #ajax_msg_importer_zip , #ajax_msg_import').removeAttr('class').html('');
                $('#div_zip , #div_import').hide('fast');
                $('#structures').show('fast');
                $("button").prop('disabled',false);
              }
              else
              {
                $('#table_action tbody').append(responseJSON['value']);
                $('#ajax_import_num').html(num);
                $('#ajax_msg_import').attr('class','loader').html('Import en cours : étape ' + num + ' sur ' + max + '...');
                $('#puce_info_import').html('<li>Ne pas interrompre la procédure avant la fin du traitement !</li>');
                importer();
              }
            }
          }
        }
      );
    }

    $('#puce_info_import').on
    (
      'click',
      '#a_reprise_import',
      function()
      {
        var num = $('#ajax_import_num').html();
        var max = $('#ajax_import_max').html();
        $('#ajax_msg_import').attr('class','loader').html('Import en cours : étape ' + num + ' sur ' + max + '...');
        $('#puce_info_import').html('<li>Ne pas interrompre la procédure avant la fin du traitement !</li>');
        importer();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Tout cocher ou tout décocher
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on
    (
      'click',
      'q.cocher_tout , q.cocher_rien',
      function()
      {
        var etat = ( $(this).attr('class').substring(7) == 'tout' ) ? true : false ;
        $('#table_action td.nu input[type=checkbox]').prop('checked',etat);
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur un bouton pour effectuer une action sur les structures sélectionnées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var prompt_etapes_supprimer_selectionnees = {
      etape_1: {
        title   : 'Demande de confirmation (1/2)',
        html    : "Souhaitez-vous vraiment supprimer les bases des structures sélectionnées ?",
        buttons : {
          "Non, c'est une erreur !" : false ,
          "Oui, je confirme !" : true
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            event.preventDefault();
            $.prompt.goToState('etape_2');
            return false;
          }
        }
      },
      etape_2: {
        title   : 'Demande de confirmation (2/2)',
        html    : "Êtes-vous bien certain de vouloir supprimer ces bases ?<br /><b>Est-ce définitivement votre dernier mot ???</b>",
        buttons : {
          "Oui, j'insiste !" : true ,
          "Non, surtout pas !" : false
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            supprimer_structures_selectionnees(listing_id);
            return true;
          }
        }
      }
    };

    var supprimer_structures_selectionnees = function(listing_id)
    {
      $("button").prop('disabled',true);
      $('#ajax_supprimer_export').attr('class','loader').html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=supprimer'+'&f_listing_id='+listing_id,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_supprimer_export').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            $("button").prop('disabled',false);
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==false)
            {
              $('#ajax_supprimer_export').attr('class','alerte').html(responseJSON['value']);
            }
            else
            {
              $("#f_base input:checked").each
              (
                function()
                {
                  $(this).parent().remove();
                }
              );
              $('#ajax_supprimer_export').attr('class','valide').html('Demande réalisée !');
              $("button").prop('disabled',false);
            }
          }
        }
      );
    };

    $('#zone_actions_export button').click
    (
      function()
      {
        // Grouper les checkbox dans un champ unique afin d'éviter tout problème avec une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque) ou "max input vars" généralement fixé à 1000.
        listing_id = [];
        $("#f_base input:checked").each(function(){listing_id.push($(this).val());});
        if(!listing_id.length)
        {
          $('#ajax_supprimer_export').attr('class','erreur').html("Aucune structure sélectionnée !");
          return false;
        }
        $('#ajax_supprimer_export').removeAttr('class').html('&nbsp;');
        var id = $(this).attr('id');
        if(id=='bouton_supprimer_export')
        {
          $.prompt(prompt_etapes_supprimer_selectionnees);
        }
        else
        {
          $('#listing_ids').val(listing_id);
          var tab = new Array;
          tab['bouton_newsletter_export'] = "webmestre_newsletter";
          tab['bouton_stats_export']      = "webmestre_statistiques";
          // tab['bouton_transfert_export']  = "webmestre_structure_transfert";
          var page = tab[id];
          var form = document.getElementById('structures');
          form.action = './index.php?page='+page;
          form.method = 'post';
          // form.target = '_blank';
          form.submit();
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur un bouton pour effectuer une action sur les structures cochées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var prompt_etapes_supprimer_cochees = {
      etape_1: {
        title   : 'Demande de confirmation (1/2)',
        html    : "Souhaitez-vous vraiment supprimer les bases des structures cochées ?<br />Toutes les données associées seront perdues !",
        buttons : {
          "Non, c'est une erreur !" : false ,
          "Oui, je confirme !" : true
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            event.preventDefault();
            $.prompt.goToState('etape_2');
            return false;
          }
        }
      },
      etape_2: {
        title   : 'Demande de confirmation (2/2)',
        html    : "Êtes-vous bien certain de vouloir supprimer ces bases ?<br />Est-ce définitivement votre dernier mot ???",
        buttons : {
          "Oui, j'insiste !" : true ,
          "Non, surtout pas !" : false
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            supprimer_structures_cochees(listing_id);
            return true;
          }
        }
      }
    };

    var supprimer_structures_cochees = function(listing_id)
    {
      $("button").prop('disabled',true);
      $('#ajax_supprimer_import').attr('class','loader').html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=supprimer'+'&f_listing_id='+listing_id,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_supprimer_import').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            $("button").prop('disabled',false);
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==false)
            {
              $('#ajax_supprimer_import').attr('class','alerte').html(responseJSON['value']);
            }
            else
            {
              $("#table_action input[type=checkbox]:checked").each
              (
                function()
                {
                  $(this).parent().parent().remove();
                }
              );
              $('#ajax_supprimer_import').attr('class','valide').html('Demande réalisée !');
              $("button").prop('disabled',false);
            }
          }
        }
      );
    };

    $('#zone_actions_import button').click
    (
      function()
      {
        // Grouper les checkbox dans un champ unique afin d'éviter tout problème avec une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque) ou "max input vars" généralement fixé à 1000.
        listing_id = [];
        $("#table_action input[type=checkbox]:checked").each(function(){listing_id.push($(this).val());});
        if(!listing_id.length)
        {
          $('#ajax_supprimer_import').attr('class','erreur').html("Aucune structure cochée !");
          return false;
        }
        $('#ajax_supprimer_import').removeAttr('class').html('&nbsp;');
        var id = $(this).attr('id');
        if(id=='bouton_supprimer_import')
        {
          $.prompt(prompt_etapes_supprimer_cochees);
        }
        else
        {
          $('#listing_ids').val(listing_id);
          var tab = new Array;
          tab['bouton_newsletter_import'] = "webmestre_newsletter";
          tab['bouton_stats_import']      = "webmestre_statistiques";
          // tab['bouton_transfert_import']  = "webmestre_structure_transfert";
          var page = tab[id];
          var form = document.getElementById('structures');
          form.action = './index.php?page='+page;
          form.method = 'post';
          // form.target = '_blank';
          form.submit();
        }
      }
    );

  }
);
