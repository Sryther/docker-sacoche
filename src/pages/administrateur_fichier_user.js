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

    var f_action = '';
    var f_mode   = '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Réagir au changement dans le premier formulaire (choix principal)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#f_choix_principal").change
    (
      function()
      {
        $('#ajax_msg').removeAttr('class').html('&nbsp;');
        // Masquer tout
        $('#span_mode').hide(0);
        $('#form_choix').find('fieldset').hide(0);
        // Puis afficher ce qu'il faut
        f_action = $(this).val();
        $('#f_action').val(f_action);
        if(f_action!='')
        {
               if(f_action.indexOf('eleves')     !=-1) { $('#f_mode_'+check_eleve     ).prop('checked',true); }
          else if(f_action.indexOf('parents')    !=-1) { $('#f_mode_'+check_parent    ).prop('checked',true); }
          else if(f_action.indexOf('professeurs')!=-1) { $('#f_mode_'+check_professeur).prop('checked',true); }
          if( (f_action.indexOf('commun')==-1) && (f_action.indexOf('nomenclature')==-1) ) { $('#span_mode').show(0); }
          $('#fieldset_'+f_action).show(0);
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Initialisation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    if(auto_select_categorie)
    {
      $('#f_choix_principal option[value='+auto_select_categorie+']').prop('selected',true).trigger('change'); // trigger() sinon l'événement ci-dessus ne se déclenche pas (@see https://forum.jquery.com/topic/should-chk-prop-checked-true-trigger-change-event)
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le lien pour revenir au formulaire principal
// ////////////////////////////////////////////////////////////////////////////////////////////////////
    $('#form_bilan').on
    (
      'click',
      '#bouton_annuler',
      function()
      {
        $('#form_choix').show();
        $('#form_bilan').html('<hr /><label id="ajax_msg">&nbsp;</label>');
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Tout cocher ou tout décocher
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_bilan').on
    (
      'click',
      'q.cocher_tout , q.cocher_rien',
      function()
      {
        var etat = ( $(this).attr('class').substring(7) == 'tout' ) ? true : false ;
        $(this).parent().parent().parent().find('input[type=checkbox]').prop('checked',etat);
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// depart -> step10     Réagir au clic sur un bouton pour envoyer un import (quel qu'il soit)
// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du formulaire form_choix
// Upload d'un fichier (avec jquery.form.js)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire_import = $('#form_choix');

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
          if( (f_action.indexOf('siecle')!=-1) && ('.xml.zip.'.indexOf('.'+fichier_ext+'.')==-1) )
          {
            $('#f_import').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom, après avoir changé la catégorie, alors l'événement change() ne se déclenche pas
            $('#ajax_msg').attr('class','erreur').html('Le fichier "'+fichier_nom+'" n\'a pas une extension "xml" ou "zip".');
            return false;
          }
          else if ( (f_action.indexOf('onde')!=-1) && ('.csv.txt.'.indexOf('.'+fichier_ext+'.')==-1) )
          {
            $('#f_import').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom, après avoir changé la catégorie, alors l'événement change() ne se déclenche pas
            $('#ajax_msg').attr('class','erreur').html('Le fichier "'+fichier_nom+'" n\'a pas une extension "csv" ou "txt".');
            return false;
          }
          else if ( (f_action.indexOf('factos')!=-1) && ('.csv.txt.'.indexOf('.'+fichier_ext+'.')==-1) )
          {
            $('#f_import').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom, après avoir changé la catégorie, alors l'événement change() ne se déclenche pas
            $('#ajax_msg').attr('class','erreur').html('Le fichier "'+fichier_nom+'" n\'a pas une extension "csv" ou "txt".');
            return false;
          }
          else if ( (f_action.indexOf('tableur')!=-1) && ('.csv.txt.'.indexOf('.'+fichier_ext+'.')==-1) )
          {
            $('#f_import').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom, après avoir changé la catégorie, alors l'événement change() ne se déclenche pas
            $('#ajax_msg').attr('class','erreur').html('Le fichier "'+fichier_nom+'" n\'a pas une extension "csv" ou "txt".');
            return false;
          }
          else
          {
            $('#form_choix button').prop('disabled',true);
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
      $('#form_choix button').prop('disabled',false);
      $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide_import(responseJSON)
    {
      $('#f_import').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      $('#form_choix button').prop('disabled',false);
      if(responseJSON['statut']==false)
      {
        $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
      }
      else
      {
        initialiser_compteur();
        var texte1 = $('#f_choix_principal option:selected').parent('optgroup').attr('label');
        var texte2 = $('#f_choix_principal option:selected').text();
        $('#form_choix').hide();
        $('#form_bilan').html('<p><input name="report_objet" readonly size="80" value="'+texte1.substring(0,texte1.indexOf('(')-1)+' &rarr; '+texte2.substring(0,texte2.indexOf('(')-1)+'" class="b" /> <button id="bouton_annuler" class="retourner">Annuler / Retour</button></p>'+responseJSON['value']);
        $("#step1").addClass("on");
      }
    }

    $('button.fichier_import').click
    (
      function()
      {
        f_mode = $('input[name=f_mode]:checked').val();
        $('#f_import').click();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// step10 -> step20                              Passer à l'extraction des données
// step20 -> step31                              Passer à l'analyse des données des classes
// step32 -> step41                              Passer à l'analyse des données des groupes
// step20 | step32 | step42 -> step51            Passer à l'analyse des données des utilisateurs
// step52 | step53 -> step61                     Passer aux ajouts d'affectations éventuelles (Sconet uniquement)
// step52 | step53 -> step71                     Passer aux adresses des parents
// step72 -> step81                              Passer aux liens de responsabilité des parents
// step52 | step53 | step62 | step82 -> step90   Nettoyage des fichiers temporaires
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_bilan').on
    (
      'click',
      '#passer_etape_suivante',
      function()
      {
        var hash = extract_hash( $(this).attr('href') );
        var li_step = hash.substring(4,5); // 'step' + numero
        var f_step  = hash.substring(4); // 'step' + numero
        $("#step li").removeAttr('class');
        $('#form_bilan fieldset table').remove();
        $("#step"+li_step).addClass("on");
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_step='+f_step+'&f_action='+f_action+'&f_mode='+f_mode,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                $('#ajax_msg').removeAttr('class').html('&nbsp;');
                $('#form_bilan fieldset').html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// step31 -> step32     Envoyer les actions sur les classes
// step41 -> step42     Envoyer les actions sur les groupes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_bilan').on
    (
      'click',
      '#envoyer_infos_regroupements',
      function()
      {
        nb_pb = 0;
        $("#form_bilan input:checked").each
        (
          function()
          {
            var infos = $(this).attr('id');
            var mode = infos.substring(0,3);
            if( mode == 'add' )
            {
              var ref = infos.substring(4);
              if( (!$('#'+'add_niv_'+ref).val()) || (!$('#'+'add_nom_'+ref).val()) )
              {
                nb_pb++;
              }
            }
          }
        );
        if(nb_pb)
        {
          var s = (nb_pb>1) ? 's' : '';
          $('#ajax_msg').attr('class','erreur').html(nb_pb+' ligne'+s+' de formulaire à compléter.');
          return false;
        }
        else
        {
          var f_step = $(this).attr('href').substring(5);
          // Grouper les données des groupes dans un champ unique par groupe afin d'éviter tout problème avec une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque) ou "max input vars" généralement fixé à 1000.
          // En effet, un lycée peut avoir plus de 300 groupes, et avec 4 champs par groupe on dépasse la limitation usuelle de 1000 champs...
          var f_del = new Array();
          var f_add = new Array();
          var sep = encodeURIComponent(']¤[');
          $("#form_bilan input:checked").each
          (
            function()
            {
              var infos = $(this).attr('id');
              var mode = infos.substring(0,3);
              var id   = infos.substring(4); // add_ | del_
              if( mode == 'del' )
              {
                f_del.push(id);
              }
              else if( mode == 'add' )
              {
                var ref = $('#add_ref_'+id).val();
                var niv = $('#add_niv_'+id).val();
                var nom = $('#add_nom_'+id).val();
                f_add.push(id+sep+niv+sep+encodeURIComponent(ref)+sep+encodeURIComponent(nom));
              }
            }
          );
          $('#form_bilan fieldset table').hide(0);
          $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
          $.ajax
          (
            {
              type : 'POST',
              url : 'ajax.php?page='+PAGE,
              data : 'csrf='+CSRF+'&f_step='+f_step+'&f_action='+f_action+'&f_mode='+f_mode+'&f_del='+f_del+'&f_add='+f_add,
              dataType : 'json',
              error : function(jqXHR, textStatus, errorThrown)
              {
                $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
                return false;
              },
              success : function(responseJSON)
              {
                initialiser_compteur();
                if(responseJSON['statut']==false)
                {
                  $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
                }
                else
                {
                  $('#ajax_msg').removeAttr('class').html('&nbsp;');
                  $('#form_bilan fieldset').html(responseJSON['value']);
                }
              }
            }
          );
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// step51 -> step52     Envoyer les actions sur les utilisateurs
// step61 -> step62     Envoyer les actions sur les ajouts d'affectations éventuelles
// step71 -> step72     Envoyer les actions sur les ajouts d'affectations éventuelles
// step81 -> step82     Envoyer les modifications éventuelles sur les liens de responsabilité des parents
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_bilan').on
    (
      'click',
      '#envoyer_infos_utilisateurs',
      function()
      {
        var f_step = $(this).attr('href').substring(5);
        // Grouper les checkbox dans un champ unique afin d'éviter tout problème avec une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque) ou "max input vars" généralement fixé à 1000.
        var f_check = new Array();
        $("#form_bilan input:checked").each
        (
          function()
          {
            f_check.push($(this).attr('id'));
          }
        );
        $('#form_bilan fieldset table').hide(0);
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_step='+f_step+'&f_action='+f_action+'&f_mode='+f_mode+'&f_check='+f_check,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                $('#ajax_msg').removeAttr('class').html('&nbsp;');
                $('#form_bilan fieldset').html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// step52 -> step53     Récupérer les identifiants des nouveaux utilisateurs
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_bilan').on
    (
      'click',
      'a.step53',
      function()
      {
        $('#form_bilan fieldset table').remove();
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_step=53'+'&f_action='+f_action+'&f_mode='+f_mode+'&'+$("#form_bilan").serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                $('#ajax_msg').removeAttr('class').html('&nbsp;');
                $('#form_bilan fieldset').html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// step90 -> step0
// Retour au départ
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_bilan').on
    (
      'click',
      '#retourner_depart',
      function()
      {
        $('#bouton_annuler').click();
      }
    );

  }
);
