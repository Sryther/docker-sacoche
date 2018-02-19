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

// Variable globale Highcharts
var graphique;
var ChartOptions;

// jQuery !
$(document).ready
(
  function()
  {

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Initialisation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var nb_caracteres_max = 2000;

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Options de base pour le graphique : sont complétées ensuite avec les données personnalisées
    // @see   http://www.highcharts.com/ --> http://api.highcharts.com/highcharts
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    ChartOptions = {
      chart: {
        renderTo: 'div_graphique_synthese',
        alignTicks: false,
        type: 'column',
        spacingTop: 10
       },
      colors: 
        BACKGROUND_COLORS
      ,
      title: {
        style: { color: '#333' } ,
        text: null // Pourrait être MAJ ensuite
      },
      xAxis: {
        labels: {
          style: { color: '#000' },
          autoRotationLimit: 0
        },
        categories: [] // MAJ ensuite
      },
      yAxis: [
        {
          labels: { enabled: false },
          reversedStacks: false,
          min: 0,
          max: 100,
          gridLineWidth: 0,
          title: { style: { color: '#333' } , text: 'Items acquis' }
        },
        {} // MAJ ensuite
      ],
      tooltip: {
        formatter: function() {
          return this.series.name +' : '+ (this.y);
        }
      },
      plotOptions: {
        column: {
          stacking: 'percent'
        }
      },
/*
// Demande http://sesaprof.sesamath.net/forum/viewtopic.php?id=2166
// Pour afficher les valeurs sans survol : @see http://jsfiddle.net/gh/get/jquery/2.2.4/highslide-software/highcharts.com/tree/master/samples/highcharts/demo/column-stacked/
// Problème des valeurs 0 affichées (on ne peut pas ne pas les envoyer sinon ça décale tout, et pas trouvé comment ne pas les afficher
// + Il faut ce choix d'affichage paramétrable (@see http://www.highcharts.com/demo/chart-update), c'est du boulot.
// Stoppé passé 30 minutes dessus.
      plotOptions: {
        column: {
          stacking: 'percent',
          dataLabels: {
            enabled: true
          }
        },
        series: {
          dataLabels: {
            enabled: true
          }
        }
      },
*/
      series: [] // MAJ ensuite
      ,
      credits: {
        enabled: false
      }
    };

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Afficher / Masquer la photo d'un élève (module bulletin)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function charger_photo_eleve()
    {
      $("#cadre_photo").html('<label id="ajax_photo" class="loader">En cours&hellip;</label>');
      $.ajax
      (
        {
          type : 'GET',
          url : 'ajax.php?page=calque_voir_photo',
          data : 'user_id='+memo_eleve,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_photo').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
          },
          success : function(responseJSON)
          {
            if(responseJSON['statut']==true)
            {
              $('#cadre_photo').html('<div>'+responseJSON['value']+'</div><div style="margin-top:-20px"><button id="masquer_photo" type="button" class="annuler">Fermer</button></div>');
            }
            else
            {
              $('#ajax_photo').attr('class','alerte').html(responseJSON['value']);
            }
          }
        }
      );
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic pour tout cocher ou tout décocher
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_accueil').on
    (
      'click',
      'q.cocher_tout , q.cocher_rien',
      function()
      {
        var id_mask = $(this).attr('id').replace('_deb1_','^=').replace('_deb2_','^=').replace('_fin1_','$=').replace('_fin2_','$=');
        var etat = ( $(this).attr('class').substring(7) == 'tout' ) ? true : false ;
        $('input['+id_mask+']').prop('checked',etat);
      }
    );

    $('#rubrique_check_all').click
    (
      function()
      {
        $('#zone_chx_rubriques input[type=checkbox]').prop('checked',true);
        return false;
      }
    );
    $('#rubrique_uncheck_all').click
    (
      function()
      {
        $('#zone_chx_rubriques input[type=checkbox]').prop('checked',false);
        return false;
      }
    );

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
    // Enregistrer les modifications de types et/ou d'accès
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_valider').click
    (
      function()
      {
        if(!$('#cadre_statut input[type=radio]:checked').length)
        {
          $('#ajax_msg_gestion').attr('class','erreur').html("Aucun statut coché !");
          return false;
        }
        var listing_id = new Array(); $("#table_accueil input[type=checkbox]:checked").each(function(){listing_id.push($(this).attr('id'));});
        if(!listing_id.length)
        {
          $('#ajax_msg_gestion').attr('class','erreur').html("Aucune case du tableau cochée !");
          return false;
        }
        $('#ajax_msg_gestion').attr('class','loader').html("Envoi&hellip;"); // volontairement court
        $('#listing_ids').val(listing_id);
        $('#csrf').val(CSRF);
        var form = document.getElementById('cadre_statut');
        form.action = './index.php?page=officiel&section=accueil_'+BILAN_TYPE;
        form.method = 'post';
        form.submit();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Initialisation de variables utiles accessibles depuis toute fonction
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var memo_objet         = '';
    var memo_section       = '';
    var memo_classe        = 0;
    var memo_groupe        = 0;
    var memo_periode       = 0;
    var memo_eleve         = 0;
    var memo_rubrique_nom  = 0;
    var memo_rubrique_type = '';
    var memo_rubrique_id   = 0;
    var memo_html          = '';
    var memo_long_max      = '';
    var memo_auto_next     = false;
    var memo_auto_prev     = false;
    var memo_eleve_first   = 0;
    var memo_eleve_last    = 0;
    var memo_classe_first  = 0;
    var memo_classe_last   = 0;
    var memo_config        = 0;

    var tab_classe_action_to_section = new Array();
    tab_classe_action_to_section['modifier']     = 'officiel_saisir';
    tab_classe_action_to_section['tamponner']    = 'officiel_saisir';
    tab_classe_action_to_section['detailler']    = 'officiel_examiner';
    tab_classe_action_to_section['voir']         = 'officiel_consulter';
    tab_classe_action_to_section['imprimer']     = 'officiel_imprimer';
    tab_classe_action_to_section['voir_archive'] = 'officiel_imprimer';

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur une image action
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_accueil td q').click
    (
      function()
      {
        memo_objet = $(this).attr('class');
        memo_section = tab_classe_action_to_section[memo_objet];
        if(typeof(memo_section)!='undefined')
        {
          var tab_ids = $(this).parent().attr('id').split('_');
          memo_classe  = tab_ids[1];
          memo_groupe  = tab_ids[2];
          memo_periode = tab_ids[3];
          memo_config  = tab_classe_config_ref[memo_classe];
          $('#f_objet').val(memo_objet);
          if( (memo_section=='officiel_saisir') || (memo_section=='officiel_consulter') )
          {
            // Masquer le tableau ; Afficher la zone action et charger son contenu
            $('#cadre_statut , #table_accueil').hide(0);
            $('#zone_action_eleve').html('<label class="loader">En cours&hellip;</label>').show(0);
            $.ajax
            (
              {
                type : 'POST',
                url : 'ajax.php?page='+PAGE,
                data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_action='+'initialiser'+'&f_bilan_type='+BILAN_TYPE+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_periode='+memo_periode+'&'+$('#form_hidden').serialize(),
                dataType : 'json',
                error : function(jqXHR, textStatus, errorThrown)
                {
                  var message = (jqXHR.status!=500) ? afficher_json_message_erreur(jqXHR,textStatus) : 'Erreur 500&hellip; Mémoire insuffisante ? Sélectionner moins d\'élèves à la fois ou demander à votre hébergeur d\'augmenter la valeur "memory_limit".' ;
                  $('#zone_action_eleve').html('<label class="alerte">'+message+' <button id="fermer_zone_action_eleve" type="button" class="retourner">Retour</button></label>');
                  return false;
                },
                success : function(responseJSON)
                {
                  initialiser_compteur();
                  if(responseJSON['statut']==false)
                  {
                    $('#zone_action_eleve').html('<label class="alerte">'+responseJSON['value']+'</label> <button id="fermer_zone_action_eleve" type="button" class="retourner">Retour</button>');
                  }
                  else
                  {
                    $('#zone_action_eleve').html(responseJSON['html']);
                    if( typeof(responseJSON['script']) !== 'undefined' )
                    {
                      // A priori on ne passe jamais là car ce n'est que pour les bulletins et on commence toujours par l'éppréciation sur la classe
                      eval( responseJSON['script'] );
                    }
                    memo_eleve       = $('#go_selection_eleve option:selected').val();
                    memo_eleve_first = $('#go_selection_eleve option:first').val();
                    memo_eleve_last  = $('#go_selection_eleve option:last').val();
                    masquer_element_navigation_choix_eleve();
                    if($('#voir_photo').length==0)
                    {
                      charger_photo_eleve();
                    }
                    $('#cadre_photo').show(0);
                  }
                }
              }
            );
          }
          else if(memo_section=='officiel_examiner')
          {
            // Masquer le tableau ; Afficher la zone de choix des rubriques
            $('#cadre_statut , #table_accueil').hide(0);
            $('#zone_action_classe h2').html('Recherche de saisies manquantes');
            if( tab_config[memo_config]["APP_RUBRIQUE_LONGUEUR"] && tab_config[memo_config]["APP_GENERALE_LONGUEUR"] )
            {
              $('#zone_chx_rubriques').find('input').prop('disabled',false);
            }
            else if( tab_config[memo_config]["APP_RUBRIQUE_LONGUEUR"] && !tab_config[memo_config]["APP_GENERALE_LONGUEUR"] )
            {
              $('#zone_chx_rubriques').find('input').prop('disabled',false);
              $('#rubrique_0').prop('disabled',true);
            }
            else if( !tab_config[memo_config]["APP_RUBRIQUE_LONGUEUR"] && tab_config[memo_config]["APP_GENERALE_LONGUEUR"] )
            {
              $('#zone_chx_rubriques').find('input').prop('disabled',true);
              $('#rubrique_0').prop('disabled',false);
            }
            else if( !tab_config[memo_config]["APP_RUBRIQUE_LONGUEUR"] && !tab_config[memo_config]["APP_GENERALE_LONGUEUR"] )
            {
              $('#zone_chx_rubriques').find('input').prop('disabled',true);
            }
            $('#zone_chx_rubriques').show(0);
          }
          else if(memo_section=='officiel_imprimer')
          {
            // Masquer le tableau ; Afficher la zone de choix des élèves, avec info si les bulletins sont déjà imprimés
            var titre = (memo_objet=='imprimer') ? 'Imprimer le bilan (PDF)' : 'Consulter un bilan imprimé (PDF)' ;
            configurer_form_choix_classe();
            $('#cadre_statut , #table_accueil').hide(0);
            $('#zone_action_classe h2').html(titre);
            $('#report_periode').html( $('#periode_'+memo_periode).text()+' :' );
            $('#zone_action_classe , #zone_'+memo_objet).show(0);
            charger_formulaire_imprimer();
          }
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire #zone_action_deport : envoyer un import csv (saisie déportée)
    // Upload d'un fichier (avec jquery.form.js)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire_saisie_deportee = $('#zone_action_deport');

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions_saisie_deportee =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
      clearForm : false,
      resetForm : false,
      target : "#msg_import",
      error : retour_form_erreur_saisie_deportee,
      success : retour_form_valide_saisie_deportee
    };

    // Vérifications précédant l'envoi du formulaire, déclenchées au choix d'un fichier
    $('#f_saisie_deportee').change
    (
      function()
      {
        var file = this.files[0];
        if( typeof(file) == 'undefined' )
        {
          $('#msg_import').removeAttr('class').html('');
          return false;
        }
        else
        {
          var fichier_nom = file.name;
          var fichier_ext = fichier_nom.split('.').pop().toLowerCase();
          if( '.csv.txt.'.indexOf('.'+fichier_ext+'.') == -1 )
          {
            $('#msg_import').attr('class','erreur').html('Le fichier "'+fichier_nom+'" n\'a pas l\'extension "csv" ou "txt".');
            return false;
          }
          else
          {
            $("#f_upload_bilan_type").val( BILAN_TYPE );
            $("#f_upload_classe"    ).val( memo_classe );
            $("#f_upload_groupe"    ).val( memo_groupe );
            $("#f_upload_periode"   ).val( memo_periode );
            $("#f_upload_objet"     ).val( $('#f_objet').val() );
            $("#f_upload_mode"      ).val( $('#f_mode').val() );
            $("#bouton_choisir_saisie_deportee").prop('disabled',true);
            $('#msg_import').attr('class','loader').html("En cours&hellip;");
            formulaire_saisie_deportee.submit();
          }
        }
      }
    );

    // Envoi du formulaire (avec jquery.form.js)
    formulaire_saisie_deportee.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions_saisie_deportee);
        return false;
      }
    );

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur_saisie_deportee(jqXHR, textStatus, errorThrown)
    {
      $('#f_saisie_deportee').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      $("#bouton_choisir_saisie_deportee").prop('disabled',false);
      $('#msg_import').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide_saisie_deportee(responseJSON)
    {
      $('#f_saisie_deportee').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      $("#bouton_choisir_saisie_deportee").prop('disabled',false);
      if(responseJSON['statut']==false)
      {
        $('#msg_import').attr('class','alerte').html(responseJSON['value']);
      }
      else
      {
        initialiser_compteur();
        $('#f_import_info').val(responseJSON['filename']);
        $('#msg_import').removeAttr('class').html('&nbsp;');
        $('#table_import_analyse').html(responseJSON['html']);
        $.fancybox( { 'href':'#zone_action_import' , onStart:function(){$('#zone_action_import').css("display","block");} , onClosed:function(){$('#zone_action_import').css("display","none");} , 'modal':true , 'minHeight':300 , 'centerOnScroll':true } );
        $("#bouton_choisir_saisie_deportee").prop('disabled',false);
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du clic sur le bouton pour confirmer le traitement d'un import csv (saisie déportée)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_action_import').on
    (
      'click',
      '#valider_importer',
      function()
      {
        $('#zone_action_import button').prop('disabled',true);
        $('#ajax_msg_importer').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_section='+'officiel_importer'+'&f_action='+'enregistrer_saisie_csv'+'&f_bilan_type='+BILAN_TYPE+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_periode='+memo_periode+'&f_import_info='+$('#f_import_info').val()+'&'+$('#form_hidden').serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg_importer').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              $('#zone_action_import button').prop('disabled',false);
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg_importer').attr('class','alerte').html(responseJSON['value']);
                $('#zone_action_import button').prop('disabled',false);
              }
              else
              {
                $('#table_import_analyse').html('');
                $('#ajax_msg_importer').attr('class','valide').html(responseJSON['value']);
                $('#fermer_zone_importer').prop('disabled',false);
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour fermer la zone action_eleve
    // Clic sur le bouton pour fermer la zone de choix des rubriques
    // Clic sur le bouton pour fermer la zone zone_action_classe
    // Clic sur le bouton pour fermer la zone zone_action_import
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_action_eleve').on
    (
      'click',
      '#fermer_zone_action_eleve',
      function()
      {
        $('#zone_action_eleve').html("").hide(0);
        $('#zone_action_deport').hide(0);
        $('#msg_import').removeAttr('class').html('&nbsp;');
        $('#cadre_photo').hide(0);
        $('#cadre_statut , #table_accueil').show(0);
        return false;
      }
    );

    $('#fermer_zone_chx_rubriques').click
    (
      function()
      {
        $('#zone_chx_rubriques').hide(0);
        $('#cadre_statut , #table_accueil').show(0);
        return false;
      }
    );

    $('#fermer_zone_action_classe').click
    (
      function()
      {
        $('#zone_resultat_classe').html("");
        var colspan = (memo_objet=='imprimer') ? 3 : 2 ;
        $('#zone_'+memo_objet+' table tbody').html('<tr><td class="nu" colspan="'+colspan+'"></td></tr>');
        $('#zone_action_classe , #zone_imprimer , #zone_voir_archive').hide(0);
        $('#ajax_msg_imprimer , #ajax_msg_voir_archive').removeAttr('class').html("");
        $('#cadre_statut , #table_accueil').show(0);
        return false;
      }
    );

    $('#fermer_zone_importer').click
    (
      function()
      {
        $.fancybox.close();
        $('#ajax_msg_importer').removeAttr('class').html('&nbsp;');
        $('#zone_action_import button').prop('disabled',false);
        return false;
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [officiel_saisir|officiel_consulter] Navigation d'un élève à un autre
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function charger_nouvel_eleve(eleve_id,reload)
    {
      if( (eleve_id==memo_eleve) && (!reload) )
      {
        return false;
      }
      memo_eleve = eleve_id;
      $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',true);
      $('#zone_resultat_eleve').html('<label class="loader">En cours&hellip;</label>');
      $('#msg_import').removeAttr('class').html('&nbsp;');
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_action='+'charger'+'&f_bilan_type='+BILAN_TYPE+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_periode='+memo_periode+'&f_user='+memo_eleve+'&'+$('#form_hidden').serialize(),
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#zone_resultat_eleve').html('<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+'</label>');
            $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
            return false;
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
            if(responseJSON['statut']==false)
            {
              $('#zone_resultat_eleve').html('<label class="alerte">'+responseJSON['value']+'</label>');
            }
            else
            {
              $('#go_selection_eleve option[value='+memo_eleve+']').prop('selected',true);
              masquer_element_navigation_choix_eleve();
              if($('#voir_photo').length==0)
              {
                charger_photo_eleve();
              }
              $('#zone_resultat_eleve').html(responseJSON['html']);
              if( typeof(responseJSON['script']) !== 'undefined' )
              {
                eval( responseJSON['script'] );
              }
              if(memo_auto_next || memo_auto_prev)
              {
                memo_auto_next = false;
                memo_auto_prev = false;
                $('#'+memo_rubrique_nom).find('button').click();
              }
            }
          }
        }
      );
    }

    function masquer_element_navigation_choix_eleve()
    {
      $('#form_choix_eleve button').css('visibility','visible');
      if(memo_eleve==memo_eleve_first)
      {
        $('#go_premier_eleve , #go_precedent_eleve').css('visibility','hidden');
      }
      if(memo_eleve==memo_eleve_last)
      {
        $('#go_dernier_eleve , #go_suivant_eleve').css('visibility','hidden');
      }
    }

    $('#zone_action_eleve').on
    (
      'click',
      '#go_premier_eleve',
      function()
      {
        var eleve_id = $('#go_selection_eleve option:first').val();
        charger_nouvel_eleve(eleve_id,false);
      }
    );

    $('#zone_action_eleve').on
    (
      'click',
      '#go_dernier_eleve',
      function()
      {
        var eleve_id = $('#go_selection_eleve option:last').val();
        charger_nouvel_eleve(eleve_id,false);
      }
    );

    $('#zone_action_eleve').on
    (
      'click',
      '#go_precedent_eleve',
      function()
      {
        if( $('#go_selection_eleve option:selected').prev().length )
        {
          var eleve_id = $('#go_selection_eleve option:selected').prev().val();
          charger_nouvel_eleve(eleve_id,false);
        }
      }
    );

    $('#zone_action_eleve').on
    (
      'click',
      '#go_suivant_eleve',
      function()
      {
        if( $('#go_selection_eleve option:selected').next().length )
        {
          var eleve_id = $('#go_selection_eleve option:selected').next().val();
          charger_nouvel_eleve(eleve_id,false);
        }
      }
    );

    $('#zone_action_eleve').on
    (
      'change',
      '#go_selection_eleve',
      function()
      {
        var eleve_id = $('#go_selection_eleve option:selected').val();
        charger_nouvel_eleve(eleve_id,false);
      }
    );

    $('#zone_action_eleve').on
    (
      'click',
      '#change_mode',
      function()
      {
        if($('#f_mode').val()=='texte')
        {
          $('#change_mode').attr('class',"texte").html('Interface détaillée');
          $('#f_mode').val('graphique');
        }
        else
        {
          $('#change_mode').attr('class',"stats").html('Interface graphique');
          $('#f_mode').val('texte');
        }
        var eleve_id = $('#go_selection_eleve option:selected').val();
        charger_nouvel_eleve(eleve_id,true);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [officiel_saisir] Clic sur le bouton pour afficher le formulaire "Saisie déportée"
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_action_eleve').on
    (
      'click',
      '#saisir_deport',
      function()
      {
        $('#msg_import').removeAttr('class').html("");
        $.fancybox( '<label class="loader">'+"En cours&hellip;"+'</label>' , {'centerOnScroll':true} );
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_section='+'officiel_importer'+'&f_action='+'generer_csv_vierge'+'&f_bilan_type='+BILAN_TYPE+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_periode='+memo_periode+'&'+$('#form_hidden').serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              var message = (jqXHR.status!=500) ? afficher_json_message_erreur(jqXHR,textStatus) : 'Erreur 500&hellip; Mémoire insuffisante ? Demander à votre hébergeur d\'augmenter la valeur "memory_limit".' ;
              $.fancybox( '<label class="alerte">'+message+'</label>' , {'centerOnScroll':true} );
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==false)
              {
                $.fancybox( '<label class="alerte">'+responseJSON['value']+'</label>' , {'centerOnScroll':true} );
              }
              else
              {
                $('#export_file_saisie_deportee').attr("href", './force_download.php?fichier='+responseJSON['value'] );
                $.fancybox( { 'href':'#zone_action_deport' , onStart:function(){$('#zone_action_deport').css("display","block");} , onClosed:function(){$('#zone_action_deport').css("display","none");} , 'minHeight':300 , 'centerOnScroll':true } );
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [officiel_saisir|officiel_consulter] Clic sur le bouton pour afficher les liens "archiver / imprimer des saisies"
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_action_eleve').on
    (
      'click',
      '#archiver_imprimer',
      function()
      {
        if(PROFIL_TYPE!='administrateur')
        {
          $('#imprimer_donnees_eleves_prof').prop('disabled',false);
        }
        else
        {
          $('#imprimer_donnees_eleves_prof').prop('disabled',true);
        }
        $('#ajax_msg_archiver_imprimer').removeAttr('class').html("");
        if(tab_config[memo_config]["APP_GENERALE_LONGUEUR"])
        {
          $('#imprimer_donnees_eleves_syntheses').parent().show(0);
        }
        else
        {
          $('#imprimer_donnees_eleves_syntheses').parent().hide(0);
        }
        if( (BILAN_TYPE=='bulletin') && tab_config[memo_config]["MOYENNE_SCORES"] )
        {
          $('#imprimer_donnees_eleves_positionnements').parent().show(0);
          $('#imprimer_donnees_eleves_recapitulatif').parent().show(0);
        }
        else
        {
          $('#imprimer_donnees_eleves_positionnements').parent().hide(0);
          $('#imprimer_donnees_eleves_recapitulatif').parent().hide(0);
        }
        $.fancybox( { 'href':'#zone_archiver_imprimer' , onStart:function(){$('#zone_archiver_imprimer').css("display","block");} , onClosed:function(){$('#zone_archiver_imprimer').css("display","none");} , 'minHeight':300 , 'centerOnScroll':true } );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [officiel_saisir|officiel_consulter] Clic sur un lien pour archiver / imprimer des saisies
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_archiver_imprimer button').click
    (
      function()
      {
        $('#zone_archiver_imprimer button').prop('disabled',true);
        $('#ajax_msg_archiver_imprimer').attr('class','loader').html("En cours&hellip;");
        var f_action = $(this).attr('id');
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+f_action+'&f_bilan_type='+BILAN_TYPE+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_periode='+memo_periode+'&'+$('#form_hidden').serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#zone_archiver_imprimer button').prop('disabled',false);
              $('#ajax_msg_archiver_imprimer').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('#zone_archiver_imprimer button').prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg_archiver_imprimer').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                $('#ajax_msg_archiver_imprimer').removeAttr('class').html(responseJSON['value']);
              }
            }
          }
        );
        return false;
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [officiel_consulter] Clic sur le bouton pour tester l'impression finale d'un bilan
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_action_eleve').on
    (
      'click',
      '#simuler_impression',
      function()
      {
        $('#f_parite').val(0);
        $('#f_listing_eleves').val(memo_eleve);
        $.fancybox( '<label class="loader">'+"En cours&hellip;"+'</label>' , {'centerOnScroll':true} );
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_action='+'imprimer'+'&f_etape='+"1"+'&f_bilan_type='+BILAN_TYPE+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_periode='+memo_periode+'&'+$('#form_hidden').serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              var message = (jqXHR.status!=500) ? afficher_json_message_erreur(jqXHR,textStatus) : 'Erreur 500&hellip; Mémoire insuffisante ? Demander à votre hébergeur d\'augmenter la valeur "memory_limit".' ;
              $.fancybox( '<label class="alerte">'+message+'</label>' , {'centerOnScroll':true} );
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==false)
              {
                $.fancybox( '<label class="alerte">'+responseJSON['value']+'</label>' , {'centerOnScroll':true} );
              }
              else
              {
                $.fancybox( '<h3>Test impression PDF finale</h3><p class="astuce">Ce fichier comprend l\'exemplaire archivé ainsi que le ou les exemplaires pour les responsables légaux.</p><div id="imprimer_liens"><ul class="puce"><li><a target="_blank" rel="noopener noreferrer" href="'+responseJSON['value']+'"><span class="file file_pdf">Récupérer le test d\'impression du bilan officiel demandé.</span></a></li></ul></div>' , {'centerOnScroll':true} );
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [officiel_saisir] Clic sur le bouton pour ajouter une appréciation (une note ne s'ajoute pas, mais elle peut se modifier ou se recalculer si NULL)
    // [officiel_saisir] Clic sur le bouton pour modifier une note ou une saisie d'appréciation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function afficher_textarea_appreciation_ou_input_moyenne(obj_lieu,champ_contenu)
    {
      // fabriquer le formulaire textarea ou input
      if(memo_rubrique_type=='appr')
      {
        memo_html = obj_lieu.closest('td').html();
        memo_long_max = (memo_rubrique_id) ? tab_config[memo_config]["APP_RUBRIQUE_LONGUEUR"] : tab_config[memo_config]["APP_GENERALE_LONGUEUR"] ;
        var nb_lignes = parseInt(memo_long_max/100,10);
        var formulaire_saisie = '<div class="ti"><b>Appréciation / Conseils pour progresser [ '+$('#go_selection_eleve option:selected').text()+' ] :</b></div>'
                              + '<div class="ti"><textarea id="f_appreciation" name="f_appreciation" rows="'+nb_lignes+'" cols="125"></textarea></div>'
                              + '<div class="ti"><label id="f_appreciation_reste"></label></div>'
                              + '<div class="ti"><button id="valider_appr_precedent" type="button" class="valider_prev">Précédent</button> <button id="valider_appr" type="button" class="valider">Valider</button> <button id="valider_appr_suivant" type="button" class="valider_next">Suivant</button> <button id="annuler_appr_precedent" type="button" class="annuler_prev">Précédent</button> <button id="annuler_appr" type="button" class="annuler">Annuler</button> <button id="annuler_appr_suivant" type="button" class="annuler_next">Suivant</button><label id="ajax_msg_appr">&nbsp;</label></div>';
      }
      if(memo_rubrique_type=='note')
      {
        memo_html = obj_lieu.closest('tr').html();
        var pourcent = (tab_config[memo_config]["CONVERSION_SUR_20"]) ? '' : '%' ;
        var texte    = (tab_config[memo_config]["CONVERSION_SUR_20"]) ? 'en note sur 20' : 'en pourcentage' ;
        var formulaire_saisie = '<div><b>Moyenne '+texte+' [ '+$('#go_selection_eleve option:selected').text()+' ] :</b> <input id="f_moyenne" name="f_moyenne" type="text" size="3" value="" />'+pourcent+'</div>'
                              + '<div><button id="valider_note_precedent" type="button" class="valider_prev">Précédent</button> <button id="valider_note" type="button" class="valider">Valider</button> <button id="valider_note_suivant" type="button" class="valider_next">Suivant</button> <button id="annuler_note_precedent" type="button" class="annuler_prev">Précédent</button> <button id="annuler_note" type="button" class="annuler">Annuler</button> <button id="annuler_note_suivant" type="button" class="annuler_next">Suivant</button><label id="ajax_msg_note">&nbsp;</label></div>';
      }
      // modif affichage
      $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',true);
      obj_lieu.closest('td').html(formulaire_saisie);
      if(memo_eleve==memo_eleve_first)
      {
        $('#valider_'+memo_rubrique_type+'_precedent , #annuler_'+memo_rubrique_type+'_precedent').css('visibility','hidden');
      }
      if(memo_eleve==memo_eleve_last)
      {
        $('#valider_'+memo_rubrique_type+'_suivant , #annuler_'+memo_rubrique_type+'_suivant').css('visibility','hidden');
      }
      // finalisation (remplissage et focus)
      if(memo_rubrique_type=='appr')
      {
        // report d'une appréciation préremplie
        var is_report = (memo_rubrique_id) ? tab_config[memo_config]["APP_RUBRIQUE_REPORT"] : tab_config[memo_config]["APP_GENERALE_REPORT"] ;
        if( !champ_contenu && is_report )
        {
          champ_contenu = (memo_rubrique_id) ? tab_config[memo_config]["APP_RUBRIQUE_MODELE"] : tab_config[memo_config]["APP_GENERALE_MODELE"] ;
        }
        $('#f_appreciation').focus().html(champ_contenu);
        afficher_textarea_reste( $('#f_appreciation') , memo_long_max );
        window.scrollBy(0,100); // Pour avoir à l'écran les bouton de validation et d'annulation situés en dessous du textarea
      }
      if(memo_rubrique_type=='note')
      {
        var valeur = (tab_config[memo_config]["CONVERSION_SUR_20"]) ? parseFloat(champ_contenu,10) : parseInt(champ_contenu.substr(0,champ_contenu.length-1),10) ;
        valeur = (isNaN(valeur)) ? '' : valeur ;
        $('#f_moyenne').focus().val(valeur);
      }
    }

    $('#zone_action_eleve').on
    (
      'click',
      'button.ajouter , button.modifier',
      function()
      {
        memo_rubrique_nom  = $(this).closest('tr').attr('id');
        var tab_ids = memo_rubrique_nom.split('_');
        memo_rubrique_type = tab_ids[0]; // note | appr
        memo_rubrique_id   = parseInt( tab_ids[1] , 10 );
        if($(this).attr('class')=='modifier')
        {
          var contenu = (memo_rubrique_type=='appr') ? $(this).parent().next().html() : $(this).closest('td').prev().html() ;
        }
        else
        {
          var contenu = '' ;
        }
        afficher_textarea_appreciation_ou_input_moyenne( $(this) , contenu );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [officiel_saisir] Indiquer le nombre de caractères restant autorisés dans le textarea
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_action_eleve').on
    (
      'keyup',
      '#f_appreciation',
      function()
      {
        afficher_textarea_reste($(this),memo_long_max);
      }
    );

    $('#section_corriger').on
    (
      'keyup',
      '#f_appreciation',
      function()
      {
        afficher_textarea_reste($(this),memo_long_max);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [officiel_saisir] Clic sur un bouton pour annuler une saisie de note ou d'appréciation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_action_eleve').on
    (
      'click',
      '#annuler_appr , #annuler_appr_suivant , #annuler_appr_precedent , #annuler_note , #annuler_note_suivant , #annuler_note_precedent',
      function()
      {
        if(memo_rubrique_type=='appr')
        {
          $(this).closest('td').html(memo_html);
        }
        else if(memo_rubrique_type=='note')
        {
          $(this).closest('tr').html(memo_html);
        }
        $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
        memo_auto_next = ($(this).attr('id')=='annuler_'+memo_rubrique_type+'_suivant')   ? true : false ;
        memo_auto_prev = ($(this).attr('id')=='annuler_'+memo_rubrique_type+'_precedent') ? true : false ;
        if(memo_auto_next) { $('#go_suivant_eleve').click(); }
        if(memo_auto_prev) { $('#go_precedent_eleve').click(); }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [officiel_saisir] Clic sur un bouton pour valider une saisie de note ou d'appréciation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_action_eleve').on
    (
      'click',
      '#valider_appr , #valider_appr_suivant , #valider_appr_precedent , #valider_note , #valider_note_suivant , #valider_note_precedent',
      function()
      {
        if(memo_rubrique_type=='appr')
        {
          if( !$.trim($('#f_appreciation').val()).length )
          {
            $('#ajax_msg_'+memo_rubrique_type).attr('class','erreur').html("Absence d'appréciation !");
            $('#f_appreciation').focus();
            return false;
          }
        }
        if(memo_rubrique_type=='note')
        {
          var note = parseFloat($('#f_moyenne').val(),10);
          if( isNaN(note) )
          {
            $('#ajax_msg_'+memo_rubrique_type).attr('class','erreur').html("Moyenne incorrecte !");
            $('#f_moyenne').focus();
            return false;
          }
          if( (note<0) || ((note>MOYENNE_MAXI)&&(tab_config[memo_config]["CONVERSION_SUR_20"])) || ((note>POURCENTAGE_MAXI)&&(!tab_config[memo_config]["CONVERSION_SUR_20"])) )
          {
            $('#ajax_msg_'+memo_rubrique_type).attr('class','erreur').html("Valeur incorrecte !");
            $('#f_moyenne').focus();
            return false;
          }
        }
        memo_auto_next = ($(this).attr('id')=='valider_'+memo_rubrique_type+'_suivant')   ? true : false ;
        memo_auto_prev = ($(this).attr('id')=='valider_'+memo_rubrique_type+'_precedent') ? true : false ;
        $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',true);
        $('#ajax_msg_'+memo_rubrique_type).attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_action='+'enregistrer_'+memo_rubrique_type+'&f_bilan_type='+BILAN_TYPE+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_periode='+memo_periode+'&f_user='+memo_eleve+'&f_rubrique='+memo_rubrique_id+'&'+$('#form_hidden').serialize()+'&'+$('#zone_resultat_eleve').serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg_'+memo_rubrique_type).attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg_'+memo_rubrique_type).attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                if(memo_rubrique_type=='appr')
                {
                  $('#ajax_msg_'+memo_rubrique_type).closest('td').html(responseJSON['value']);
                }
                else if(memo_rubrique_type=='note')
                {
                  $('#ajax_msg_'+memo_rubrique_type).closest('tr').html(responseJSON['value']);
                }
                if(memo_auto_next) { $('#go_suivant_eleve').click(); }
                if(memo_auto_prev) { $('#go_precedent_eleve').click(); }
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [officiel_saisir] Clic sur le bouton pour supprimer une saisie de note ou d'appréciation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_action_eleve').on
    (
      'click',
      'button.supprimer',
      function()
      {
        var obj_bouton = $(this);
        memo_rubrique_nom  = $(this).closest('tr').attr('id');
        var tab_ids = memo_rubrique_nom.split('_');
        memo_rubrique_type = tab_ids[0]; // note | appr
        memo_rubrique_id   = parseInt( tab_ids[1] , 10 );
        $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',true);
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_action='+'supprimer_'+memo_rubrique_type+'&f_bilan_type='+BILAN_TYPE+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_periode='+memo_periode+'&f_user='+memo_eleve+'&f_rubrique='+memo_rubrique_id+'&'+$('#form_hidden').serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+' Veuillez recommencer.'+'</label>' , {'centerOnScroll':true} );
              $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $.fancybox( '<label class="alerte">'+responseJSON['value']+'</label>' , {'centerOnScroll':true} );
              }
              else
              {
                if(memo_rubrique_type=='appr')
                {
                  obj_bouton.closest('td').html(responseJSON['value']);
                }
                else if(memo_rubrique_type=='note')
                {
                  obj_bouton.closest('tr').html(responseJSON['value']);
                }
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [officiel_saisir] Clic sur le bouton pour recalculer une note (soit effacée - NULL - soit figée car reportée manuellement)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_action_eleve').on
    (
      'click',
      'button.nettoyer',
      function()
      {
        var obj_bouton = $(this);
        memo_rubrique_nom  = $(this).closest('tr').attr('id');
        var tab_ids = memo_rubrique_nom.split('_');
        memo_rubrique_type = tab_ids[0]; // note | appr
        memo_rubrique_id   = parseInt( tab_ids[1] , 10 );
        $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',true);
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_action='+'recalculer_note'+'&f_bilan_type='+BILAN_TYPE+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_periode='+memo_periode+'&f_user='+memo_eleve+'&f_rubrique='+memo_rubrique_id+'&'+$('#form_hidden').serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+' Veuillez recommencer.'+'</label>' , {'centerOnScroll':true} );
              $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $.fancybox( '<label class="alerte">'+responseJSON['value']+'</label>' , {'centerOnScroll':true} );
              }
              else
              {
                obj_bouton.closest('tr').html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [officiel_examiner] Charger le contenu (résultat de l'examen pour une classe)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#lancer_recherche').click
    (
      function()
      {
        var listing_id = new Array(); $("#zone_chx_rubriques input[type=checkbox]:enabled:checked").each(function(){listing_id.push($(this).val());});
        if(!listing_id.length)
        {
          $('#ajax_msg_recherche').attr('class','erreur').html("Aucune rubrique cochée !");
          return false;
        }
        $('#f_listing_rubriques').val(listing_id);
        $('#zone_chx_rubriques button').prop('disabled',true);
        $('#ajax_msg_recherche').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_bilan_type='+BILAN_TYPE+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_periode='+memo_periode+'&'+$('#form_hidden').serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              var message = (jqXHR.status!=500) ? afficher_json_message_erreur(jqXHR,textStatus) : 'Erreur 500&hellip; Mémoire insuffisante ? Sélectionner moins d\'élèves à la fois ou demander à votre hébergeur d\'augmenter la valeur "memory_limit".' ;
              $('#ajax_msg_recherche').attr('class','alerte').html(message);
              $('#zone_chx_rubriques button').prop('disabled',false);
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('#zone_chx_rubriques button').prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg_recherche').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                configurer_form_choix_classe();
                masquer_element_navigation_choix_classe();
                $('#ajax_msg_recherche').removeAttr('class').html('');
                $('#report_periode').html( $('#periode_'+memo_periode).text()+' :' );
                $('#zone_resultat_classe').html(responseJSON['value']);
                $('#zone_chx_rubriques').hide(0);
                $('#zone_action_classe').show(0);
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [officiel_imprimer] Lancer l'impression pour une liste d'élèves
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function imprimer(etape)
    {
      $('#ajax_msg_imprimer').attr('class','loader').html("En cours&hellip; Étape "+etape+"/4.");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_action='+'imprimer'+'&f_etape='+etape+'&f_bilan_type='+BILAN_TYPE+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_periode='+memo_periode+'&'+$('#form_hidden').serialize(),
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            var message = (jqXHR.status!=500) ? afficher_json_message_erreur(jqXHR,textStatus) : ( (etape==1) ? 'Erreur 500&hellip; Mémoire insuffisante ? Sélectionner moins d\'élèves à la fois ou demander à votre hébergeur d\'augmenter la valeur "memory_limit".' : 'Erreur 500&hellip; Temps alloué insuffisant ? Sélectionner moins d\'élèves à la fois ou demander à votre hébergeur d\'augmenter la valeur "max_execution_time".' ) ;
            $('#ajax_msg_imprimer').attr('class','alerte').html(message);
            $('#form_choix_classe button , #form_choix_classe select , #valider_imprimer').prop('disabled',false);
            return false;
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==false)
            {
              $('#form_choix_classe button , #form_choix_classe select , #valider_imprimer').prop('disabled',false);
              $('#ajax_msg_imprimer').attr('class','alerte').html(responseJSON['value']);
            }
            else if(etape<4)
            {
              etape++;
              imprimer(etape);
            }
            else
            {
              $('#form_choix_classe button , #form_choix_classe select , #valider_imprimer').prop('disabled',false);
              tab_listing_id = $('#f_listing_eleves').val().split(',');
              for ( var key in tab_listing_id )
              {
                $('#id_'+tab_listing_id[key]).children('td:first').children('input').prop('checked',false);
                $('#id_'+tab_listing_id[key]).children('td:last').html('Oui, le '+TODAY_FR);
              }
              $('#ajax_msg_imprimer').removeAttr('class').html("");
              $.fancybox( '<h3>Bilans PDF imprimés</h3>'+'<p class="danger">Archivez ces documents : seul l\'exemplaire générique sans le bloc adresse est conservé par <em>SACoche</em> !</p>'+'<div id="imprimer_liens">'+responseJSON['value']+'</div>' , {'centerOnScroll':true} );
            }
          }
        }
      );
    }

    $('#valider_imprimer').click
    (
      function()
      {
        var listing_id = new Array(); $("#form_choix_eleves input[type=checkbox]:checked").each(function(){listing_id.push($(this).val());});
        if(!listing_id.length)
        {
          $('#ajax_msg_imprimer').attr('class','erreur').html("Aucun élève coché !");
          return false;
        }
        $('#f_listing_eleves').val(listing_id);
        var notification = ($("#f_envoi_notif_parent").is(':checked')) ? 1 : 0 ;
        $('#f_notification').val(notification);
        var parite = $('#check_parite').is(':checked') ? 1 : 0 ;
        $('#f_parite').val(parite);
        $('#form_choix_classe button , #form_choix_classe select , #valider_imprimer').prop('disabled',true);
        imprimer(1);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [officiel_imprimer] Charger la liste de choix des élèves, avec info si les bulletins sont déjà imprimés
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function charger_formulaire_imprimer()
    {
      var colspan = (memo_objet=='imprimer') ? 3 : 2 ;
      $('#zone_'+memo_objet+' table tbody').html('<tr><td class="nu" colspan="'+colspan+'"></td></tr>');
      $('#zone_voir_archive table tbody').html('<tr><td class="nu" colspan="2"></td></tr>');
      $('#form_choix_classe button , #form_choix_classe select , #valider_imprimer').prop('disabled',true);
      $('#ajax_msg_'+memo_objet).attr('class','loader').html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_action='+'initialiser'+'&f_bilan_type='+BILAN_TYPE+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_periode='+memo_periode+'&'+$('#form_hidden').serialize(),
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg_'+memo_objet).attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            $('#form_choix_classe button , #form_choix_classe select').prop('disabled',false);
            return false;
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==false)
            {
              $('#ajax_msg_'+memo_objet).attr('class','alerte').html(responseJSON['value']);
              $('#form_choix_classe button , #form_choix_classe select').prop('disabled',false);
            }
            else
            {
              masquer_element_navigation_choix_classe();
              $('#zone_'+memo_objet+' table tbody').html(responseJSON['value']);
              $('#ajax_msg_'+memo_objet).removeAttr('class').html("");
              $('#form_choix_classe button , #form_choix_classe select , #valider_imprimer').prop('disabled',false);
            }
          }
        }
      );
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [officiel_examiner|officiel_imprimer] Actualiser l'état enabled/disabled des options du formulaire de navigation dans les classes, masquer les boutons de navigation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function masquer_element_navigation_choix_classe()
    {
      $('#go_selection_classe option[value='+memo_classe+'_'+memo_groupe+']').prop('selected',true);
      $('#form_choix_classe button').css('visibility','visible');
      if( memo_classe+'_'+memo_groupe == memo_classe_first )
      {
        $('#go_precedent_classe').css('visibility','hidden');
      }
      if( memo_classe+'_'+memo_groupe == memo_classe_last )
      {
        $('#go_suivant_classe').css('visibility','hidden');
      }
    }

    // La recherche de la bonne option après appui sur "classe précédente" ou "classe suivante" n'est pas évident à cause des options désactivées.
    // D'où la mise en place de deux tableaux supplémentaires :
    var tab_id_option_to_numero = new Array();
    var tab_numero_to_id_option = new Array();

    function configurer_form_choix_classe()
    {
      var numero = 0;
      tab_id_option_to_numero = new Array();
      tab_numero_to_id_option = new Array();
      var indice = (memo_section=='officiel_examiner') ? 'examiner' : ( (memo_objet=='imprimer') ? 'imprimer' : 'voir_pdf' ) ;
      $('#go_selection_classe option').each
      (
        function()
        {
          var id_option = $(this).val();
          var etat = tab_disabled[indice][id_option+'_'+memo_periode];
          $(this).prop( 'disabled' , etat );
          if(etat==false)
          {
            numero++;
            tab_id_option_to_numero[id_option] = [numero];
            tab_numero_to_id_option[numero] = [id_option];
          }
        }
      );
      memo_classe_first = tab_numero_to_id_option[1];
      memo_classe_last = tab_numero_to_id_option[numero];
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [officiel_examiner|officiel_imprimer] Navigation d'une classe à une autre
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function charger_nouvelle_classe(classe_groupe_id)
    {
      if( classe_groupe_id == memo_classe+'_'+memo_groupe )
      {
        return false;
      }
      var tab_indices = classe_groupe_id.toString().split('_'); // Sans toString() on obtient "error: split is not a function"
      memo_classe = tab_indices[0];
      memo_groupe = tab_indices[1];
      if(memo_section=='officiel_imprimer')
      {
        charger_formulaire_imprimer();
      }
      else if(memo_section=='officiel_examiner')
      {
        $('#form_choix_classe button , #form_choix_classe select').prop('disabled',true);
        $('#zone_resultat_classe').html('<label class="loader">En cours&hellip;</label>');
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_page='+BILAN_TYPE+'&f_bilan_type='+BILAN_TYPE+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_periode='+memo_periode+'&'+$('#form_hidden').serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#zone_resultat_classe').html('<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+'</label>');
              $('#form_choix_classe button , #form_choix_classe select').prop('disabled',false);
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('#form_choix_classe button , #form_choix_classe select').prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $('#zone_resultat_classe').html('<label class="alerte">'+responseJSON['value']+'</label>');
              }
              else
              {
                masquer_element_navigation_choix_classe();
                $('#zone_resultat_classe').html(responseJSON['value']);
              }
            }
          }
        );
      }
    }

    $('#go_precedent_classe').click
    (
      function()
      {
        var id_option = $('#go_selection_classe option:selected').val();
        var numero = tab_id_option_to_numero[id_option];
        numero--;
        if( tab_numero_to_id_option[numero].length )
        {
          charger_nouvelle_classe( tab_numero_to_id_option[numero] );
        }
      }
    );

    $('#go_suivant_classe').click
    (
      function()
      {
        var id_option = $('#go_selection_classe option:selected').val();
        var numero = tab_id_option_to_numero[id_option];
        numero++;
        if( tab_numero_to_id_option[numero].length )
        {
          charger_nouvelle_classe( tab_numero_to_id_option[numero] );
        }
      }
    );

    $('#go_selection_classe').change
    (
      function()
      {
        var classe_groupe_id = $('#go_selection_classe option:selected').val();
        charger_nouvelle_classe(classe_groupe_id);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [officiel_saisir|officiel_consulter] Afficher le formulaire pour signaler ou corriger une faute
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_action_eleve').on
    (
      'click',
      'button.signaler , button.corriger',
      function()
      {
        var objet        = $(this).attr('class');
        var tab_ids      = $(this).closest('tr').attr('id').split('_');
        var prof_id      = parseInt( tab_ids[2] , 10 );
        memo_rubrique_id = parseInt( tab_ids[1] , 10 );
        $('#f_action').val(objet+'_faute');
        $('#zone_signaler_corriger h2').html(objet[0].toUpperCase() + objet.substring(1) + " une faute");
        var appreciation_contenu = $(this).parent().next().html();
        var message_contenu = $('h1').text()+' - '+$('#periode_'+memo_periode).text()+' - '+$('#groupe_'+memo_classe+'_'+memo_groupe).text()+"\n\n"+'Concernant '+$('#go_selection_eleve option:selected').text()+', ';
        $('#f_destinataire_id').val(prof_id);
        // Affichage supplémentaire si correction de l'appréciation
        if(objet=='corriger')
        {
          if( prof_id != USER_ID )
          {
            $('#section_signaler').show(0);
          }
          else
          {
            $('#section_signaler').hide(0);
          }
          memo_long_max = (memo_rubrique_id) ? tab_config[memo_config]["APP_RUBRIQUE_LONGUEUR"] : tab_config[memo_config]["APP_GENERALE_LONGUEUR"] ;
          var nb_lignes = parseInt(memo_long_max/100,10);
          message_contenu += 'je me suis permis de corriger l\'appréciation en remplaçant "ANCIENCONTENU" par "NOUVEAUCONTENU".';
          $('#section_corriger').html('<div><label for="f_appreciation" class="tab">Appréciation  :</label><textarea name="f_appreciation" id="f_appreciation" rows="'+nb_lignes+'" cols="100"></textarea></div>'+'<div><span class="tab"></span><label id="f_appreciation_reste"></label></div>').show(0);
          $('#f_appreciation').focus().html(unescapeHtml(appreciation_contenu));
          afficher_textarea_reste( $('#f_appreciation') , memo_long_max );
        }
        else if(objet=='signaler')
        {
          message_contenu += 'je pense qu\'il y a un souci dans l\'appréciation "'+appreciation_contenu+'" : SAISIREXPLICATION';
          $('#section_corriger').html("").hide(0);
          $('#section_signaler').show(0);
        }
        // Afficher la zone
        $.fancybox( { 'href':'#zone_signaler_corriger' , onStart:function(){$('#zone_signaler_corriger').css("display","block");} , onClosed:function(){$('#zone_signaler_corriger').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
        $('#f_message_contenu').focus().val(unescapeHtml(message_contenu));
        afficher_textarea_reste( $('#f_message_contenu') , nb_caracteres_max );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [officiel_saisir|officiel_consulter] Indiquer le nombre de caractères restant autorisés dans le textarea
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_message_contenu').keyup
    (
      function()
      {
        afficher_textarea_reste( $(this) , nb_caracteres_max );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [officiel_saisir|officiel_consulter] Clic sur le bouton pour fermer le cadre de rédaction d'un signalement d'une faute (annuler / retour)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#annuler_signaler_corriger').click
    (
      function()
      {
        $('#section_corriger').html("");
        $('#ajax_msg_signaler_corriger').removeAttr('class').html("");
        $.fancybox.close();
        return false;
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [officiel_saisir|officiel_consulter] Valider le formulaire pour signaler ou corriger une faute
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#valider_signaler_corriger').click
    (
      function()
      {
        $('#zone_signaler_corriger button').prop('disabled',true);
        $('#ajax_msg_signaler_corriger').attr('class','loader').html("En cours&hellip;");
        var action  = $('#f_action').val();
        var prof_id = $('#f_destinataire_id').val();
        // Signaler la faute (signalement simple, ou signalement d'une correction)
        if( prof_id != USER_ID )
        {
          $.ajax
          (
            {
              type : 'POST',
              url : 'ajax.php?page='+PAGE,
              data : 'csrf='+CSRF+'&'+$('#zone_signaler_corriger').serialize(),
              dataType : 'json',
              error : function(jqXHR, textStatus, errorThrown)
              {
                $('#ajax_msg_signaler_corriger').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
                $('#zone_signaler_corriger button').prop('disabled',false);
                return false;
              },
              success : function(responseJSON)
              {
                initialiser_compteur();
                $('#zone_signaler_corriger button').prop('disabled',false);
                if(responseJSON['statut']==false)
                {
                  $('#ajax_msg_signaler_corriger').attr('class','alerte').html(responseJSON['value']);
                  return false;
                }
                else if(action=='signaler_faute')
                {
                  $('#ajax_msg_signaler_corriger').removeAttr('class').html("");
                  $('#annuler_signaler_corriger').click();
                  return false;
                }
              }
            }
          );
        }
        // Corriger la faute
        if(action=='corriger_faute')
        {
          $.ajax
          (
            {
              type : 'POST',
              url : 'ajax.php?page='+PAGE,
              data : 'csrf='+CSRF+'&f_section='+'officiel_saisir'+'&f_bilan_type='+BILAN_TYPE+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_periode='+memo_periode+'&f_user='+memo_eleve+'&f_rubrique='+memo_rubrique_id+'&f_prof='+prof_id+'&'+$('#form_hidden').serialize()+'&'+$('#zone_signaler_corriger').serialize(),
              dataType : 'json',
              error : function(jqXHR, textStatus, errorThrown)
              {
                $('#ajax_msg_signaler_corriger').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
                $('#zone_signaler_corriger button').prop('disabled',false);
                return false;
              },
              success : function(responseJSON)
              {
                initialiser_compteur();
                $('#zone_signaler_corriger button').prop('disabled',false);
                if(responseJSON['statut']==false)
                {
                  $('#ajax_msg_signaler_corriger').attr('class','alerte').html(responseJSON['value']);
                  return false;
                }
                else
                {
                  $('#appr_'+memo_rubrique_id+'_'+prof_id).find('div.appreciation').html(responseJSON['value']);
                  $('#ajax_msg_signaler_corriger').removeAttr('class').html("");
                  $('#annuler_signaler_corriger').click();
                }
              }
            }
          );
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Voir / masquer tous les détails
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_action_eleve').on
    (
      'click',
      '#montrer_details',
      function()
      {
        $('#zone_action_eleve').find('a.toggle_plus').click();
        $(this).replaceWith('<a href="#" id="masquer_details">tout masquer</a>');
        return false;
      }
    );

    $('#zone_action_eleve').on
    (
      'click',
      '#masquer_details',
      function()
      {
        $('#zone_action_eleve').find('a.toggle_moins').click();
        $(this).replaceWith('<a href="#" id="montrer_details">tout montrer</a>');
        return false;
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Voir / masquer une photo
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#cadre_photo').on( 'click', '#voir_photo',    function() { charger_photo_eleve(); } );
    $('#cadre_photo').on( 'click', '#masquer_photo', function() { $('#cadre_photo').html('<button id="voir_photo" type="button" class="voir_photo">Photo</button>'); } );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Élement saisissable / déplaçable
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $( "#cadre_photo" ).draggable({cursor:"move"});
    $( "#cadre_statut" ).draggable({cursor:"move"});

  }
);
