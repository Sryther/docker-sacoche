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
        type: 'line',
        spacingTop: 10
       },
      title: {
        text: null
      },
      xAxis: {
        labels: {
          style: { color: '#000' },
          autoRotationLimit: 0
        },
        categories: [] // MAJ ensuite
      },
      yAxis: {} // MAJ ensuite
      ,
      tooltip: {
        formatter: function() {
          return this.series.name +' : '+ (this.y);
        }
      },
      series: [] // MAJ ensuite
      ,
      credits: {
        enabled: false
      }
    };

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Afficher / Masquer la photo d'un élève
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
        form.action = './index.php?page=livret&section=edition';
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
    var memo_page_ref      = '';
    var memo_periode       = '';
    var memo_eleve         = 0;
    var memo_action        = ''; // pour distinguer ajouter / modifier
    var memo_conteneur     = [];
    var memo_saisie_id     = 0;
    var memo_objet_id      = '';
    var memo_rubrique_type = ''; // eval | socle | epi | ap | parcours | bilan | viesco | enscompl | attitude
    var memo_rubrique_id   = 0;
    var memo_saisie_objet  = ''; // position | appreciation | elements
    var memo_page_colonne  = ''; // objectif | position | moyenne | pourcentage | maitrise | reussite
    var memo_html          = '';
    var memo_div_assiduite = '';
    var memo_long_max      = 0;
    var memo_auto_next     = false;
    var memo_auto_prev     = false;
    var memo_eleve_first   = 0;
    var memo_eleve_last    = 0;
    var memo_classe_first  = 0;
    var memo_classe_last   = 0;
    var memo_config        = 0;

    var tab_classe_action_to_section = new Array();
    tab_classe_action_to_section['modifier']     = 'livret_saisir';
    tab_classe_action_to_section['tamponner']    = 'livret_saisir';
    tab_classe_action_to_section['detailler']    = 'livret_examiner';
    tab_classe_action_to_section['voir']         = 'livret_consulter';
    tab_classe_action_to_section['imprimer']     = 'livret_imprimer';
    tab_classe_action_to_section['voir_archive'] = 'livret_imprimer';

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
          memo_classe   = tab_ids[1];
          memo_groupe   = tab_ids[2];
          memo_page_ref = tab_ids[3];
          memo_periode  = tab_ids[4];
          memo_config   = tab_classe_config_ref[memo_classe];
          $('#f_objet').val(memo_objet);
          if( (memo_section=='livret_saisir') || (memo_section=='livret_consulter') )
          {
            // Masquer le tableau ; Afficher la zone action et charger son contenu
            $('#cadre_statut , #table_accueil').hide(0);
            $('#zone_action_eleve').html('<label class="loader">Initialisation en cours&hellip;</label>').show(0);
            $.ajax
            (
              {
                type : 'POST',
                url : 'ajax.php?page='+PAGE,
                data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_action='+'initialiser'+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_page_ref='+memo_page_ref+'&f_periode='+memo_periode+'&'+$('#form_hidden').serialize(),
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
                      // A priori on ne passe jamais là car ce n'est que pour les livrets et on commence toujours par l'éppréciation sur la classe
                      eval( responseJSON['script'] );
                    }
                    memo_eleve       =  parseInt( $('#go_selection_eleve option:selected').val() , 10 );
                    memo_eleve_first =  parseInt( $('#go_selection_eleve option:first'   ).val() , 10 );
                    memo_eleve_last  =  parseInt( $('#go_selection_eleve option:last'    ).val() , 10 );
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
          else if(memo_section=='livret_examiner')
          {
            // Masquer le tableau ; Afficher la zone de choix des rubriques
            $('#cadre_statut , #table_accueil').hide(0);
            $('#zone_action_classe h2').html('Recherche de saisies manquantes');
            $('#zone_chx_rubriques').show(0);
          }
          else if(memo_section=='livret_imprimer')
          {
            // Masquer le tableau ; Afficher la zone de choix des élèves, avec info si les livrets sont déjà imprimés
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
            $("#f_upload_classe"    ).val( memo_classe );
            $("#f_upload_groupe"    ).val( memo_groupe );
            $("#f_upload_page_ref"  ).val( memo_page_ref );
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
            data : 'csrf='+CSRF+'&f_section='+'livret_importer'+'&f_action='+'enregistrer_saisie_csv'+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_page_ref='+memo_page_ref+'&f_periode='+memo_periode+'&f_import_info='+$('#f_import_info').val()+'&'+$('#form_hidden').serialize(),
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
    // [livret_saisir|livret_consulter] Navigation d'un élève à un autre
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
          data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_action='+'charger'+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_page_ref='+memo_page_ref+'&f_periode='+memo_periode+'&f_user='+memo_eleve+'&'+$('#form_hidden').serialize(),
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
                $('#'+memo_objet_id).find('button').click();
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
        var eleve_id = parseInt( $('#go_selection_eleve option:first').val() , 10 );
        charger_nouvel_eleve(eleve_id,false);
      }
    );

    $('#zone_action_eleve').on
    (
      'click',
      '#go_dernier_eleve',
      function()
      {
        var eleve_id = parseInt( $('#go_selection_eleve option:last').val() , 10 );
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
          var eleve_id = parseInt( $('#go_selection_eleve option:selected').prev().val() , 10 );
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
          var eleve_id = parseInt( $('#go_selection_eleve option:selected').next().val() , 10 );
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
        var eleve_id = parseInt( $('#go_selection_eleve option:selected').val() , 10 );
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
        var eleve_id = parseInt( $('#go_selection_eleve option:selected').val() , 10 );
        charger_nouvel_eleve(eleve_id,true);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [livret_saisir] Clic sur le bouton pour afficher le formulaire "Saisie déportée"
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
            data : 'csrf='+CSRF+'&f_section='+'livret_importer'+'&f_action='+'generer_csv_vierge'+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_page_ref='+memo_page_ref+'&f_periode='+memo_periode+'&'+$('#form_hidden').serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              var message = (jqXHR.status!=500) ? afficher_json_message_erreur(jqXHR,textStatus) : 'Erreur 500&hellip; Mémoire insuffisante ? Demander à votre hébergeur d\'augmenter la valeur "memory_limit".' ;
              $.fancybox( '<label class="alerte">'+message+'</label>' , {'centerOnScroll':true , 'minWidth':500} );
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==false)
              {
                $.fancybox( '<label class="alerte">'+responseJSON['value']+'</label>' , {'centerOnScroll':true , 'minWidth':500} );
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
    // [livret_saisir|livret_consulter] Clic sur le bouton pour afficher les liens "archiver / imprimer des saisies"
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_action_eleve').on
    (
      'click',
      '#archiver_imprimer',
      function()
      {
        if(memo_periode=='cycle')
        {
          $('#zone_archiver_imprimer').find('li[data-periodicite="periode"]').hide();
          $('#zone_archiver_imprimer').find('li[data-periodicite="cycle"]').show();
          if(memo_page_ref=='cycle4')
          {
            $('#imprimer_donnees_eleves_socle_points_dnb').prop('disabled',false);
          }
          else
          {
            $('#imprimer_donnees_eleves_socle_points_dnb').prop('disabled',true);
          }
        }
        else
        {
          $('#zone_archiver_imprimer').find('li[data-periodicite="periode"]').show();
          $('#zone_archiver_imprimer').find('li[data-periodicite="cycle"]').hide();
          if(memo_page_ref=='3e')
          {
            $('#imprimer_donnees_eleves_affelnet').prop('disabled',false);
          }
          else
          {
            $('#imprimer_donnees_eleves_affelnet').prop('disabled',true);
          }
          if(PROFIL_TYPE!='administrateur')
          {
            $('#imprimer_donnees_eleves_prof').prop('disabled',false);
          }
          else
          {
            $('#imprimer_donnees_eleves_prof').prop('disabled',true);
          }
        }
        $('#ajax_msg_archiver_imprimer').removeAttr('class').html("");
        $.fancybox( { 'href':'#zone_archiver_imprimer' , onStart:function(){$('#zone_archiver_imprimer').css("display","block");} , onClosed:function(){$('#zone_archiver_imprimer').css("display","none");} , 'minHeight':300 , 'centerOnScroll':true } );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [livret_saisir|livret_consulter] Clic sur un lien pour archiver / imprimer des saisies
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_archiver_imprimer button').click
    (
      function()
      {
        // On ne désactive pas tous les boutons pour ne pas perdre l'état déjà désactivé de certains lors de la réactivation de tous...
        $('#ajax_msg_archiver_imprimer').attr('class','loader').html("En cours&hellip;");
        var f_action = $(this).attr('id');
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+f_action+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_page_ref='+memo_page_ref+'&f_periode='+memo_periode+'&'+$('#form_hidden').serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg_archiver_imprimer').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
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
    // [livret_consulter] Clic sur le bouton pour tester l'impression finale d'un bilan
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_action_eleve').on
    (
      'click',
      '#simuler_impression',
      function()
      {
        $('#f_listing_eleves').val(memo_eleve);
        $.fancybox( '<label class="loader">'+"En cours&hellip;"+'</label>' , {'centerOnScroll':true} );
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_action='+'imprimer'+'&f_etape='+"1"+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_page_ref='+memo_page_ref+'&f_periode='+memo_periode+'&'+$('#form_hidden').serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              var message = (jqXHR.status!=500) ? afficher_json_message_erreur(jqXHR,textStatus) : 'Erreur 500&hellip; Mémoire insuffisante ? Demander à votre hébergeur d\'augmenter la valeur "memory_limit".' ;
              $.fancybox( '<label class="alerte">'+message+'</label>' , {'centerOnScroll':true , 'minWidth':500} );
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==false)
              {
                $.fancybox( '<label class="alerte">'+responseJSON['value']+'</label>' , {'centerOnScroll':true , 'minWidth':500} );
              }
              else
              {
                $.fancybox( '<h3>Test impression PDF finale</h3><p class="astuce">Ce fichier comprend l\'exemplaire archivé ainsi que le ou les exemplaires pour les responsables légaux.</p><div id="imprimer_liens"><ul class="puce"><li><a target="_blank" rel="noopener noreferrer" href="'+responseJSON['value']+'"><span class="file file_pdf">Récupérer le test d\'impression du bilan demandé.</span></a></li></ul></div>' , {'centerOnScroll':true} );
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [livret_saisir] Clic sur le bouton pour ajouter une appréciation (un positionnement ou des éléments du programme ne s'ajoutent pas, mais peuvent se modifier ou se recalculer si NULL)
    // [livret_saisir] Clic sur le bouton pour modifier un positionnement ou une appréciation ou des éléments du programme
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_action_eleve').on
    (
      'click',
      'button.ajouter , button.modifier',
      function()
      {
        memo_action = $(this).attr('class'); // ajouter | modifier
        memo_conteneur = $(this).parent().parent();
        // Récupération des principaux identifiants
        memo_saisie_id  = $(this).parent().attr('data-id');
        memo_objet_id   = memo_conteneur.attr('id');
        var tab_ids     = memo_objet_id.split('_');
        memo_rubrique_type = tab_ids[0]; // eval | socle | epi | ap | parcours | bilan | viesco | enscompl | attitude
        memo_rubrique_id   = parseInt( tab_ids[1] , 10 );
        memo_saisie_objet  = tab_ids[2]; // position | appreciation | elements
        memo_page_colonne  = (memo_saisie_objet=='position') ? tab_ids[3] : '' ; // objectif | position | moyenne | pourcentage | maitrise | reussite
        // Contenu de la saisie existante
        if(memo_action=='ajouter')
        {
          var saisie_contenu = '' ;
        }
        else if(memo_saisie_objet=='elements')
        {
          var saisie_contenu = '';
          // http://www.w3schools.com/jsref/prop_node_nodetype.asp
          $(this).parent().prev().find('div').contents().filter( function(){return this.nodeType == 3;} ).each( function(){saisie_contenu+=$(this).text().trim()+"\n";} );
        }
        else if( (memo_saisie_objet=='position') && ( (memo_page_colonne=='objectif') || (memo_page_colonne=='position') || (memo_page_colonne=='maitrise') || (memo_page_colonne=='reussite') ) )
        {
          var saisie_contenu = $(this).parent().next().html();
        }
        else
        {
          var saisie_contenu = $(this).parent().prev().html();
        }
        if(memo_rubrique_type=='viesco')
        {
           memo_div_assiduite = ($('#div_assiduite').length) ? $('#div_assiduite').html() : '' ;
        }
        // Désactiver les autres boutons d'action
        $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',true);
        // 1/3 Fabriquer un formulaire de saisie textarea
        if( (memo_saisie_objet=='appreciation') || (memo_saisie_objet=='elements') )
        {
          memo_html = memo_conteneur.html();
          if( (memo_rubrique_type=='eval') || (memo_rubrique_type=='socle') )
          {
            var texte = (memo_saisie_objet=='appreciation') ? ( (memo_page_ref!='cycle1') ? 'Acquisitions / Conseils' : 'Points forts / Besoins' ) : 'Principaux éléments travaillés' ;
          }
          else if(memo_rubrique_type=='epi')
          {
            var texte = (memo_eleve) ? 'Implication de l&#8217;élève' : 'Projet réalisé' ; // ’ remplacé par &#8217; car ne s'affiche pas correctement sur le serveur de Bordeaux ; corresponce trouvée avec http://hapax.qc.ca/conversion.fr.html
          }
          else if(memo_rubrique_type=='ap')
          {
            var texte = (memo_eleve) ? 'Implication de l&#8217;élève' : 'Action réalisée' ; // ’ remplacé par &#8217; car ne s'affiche pas correctement sur le serveur de Bordeaux ; corresponce trouvée avec http://hapax.qc.ca/conversion.fr.html
          }
          else if(memo_rubrique_type=='parcours')
          {
            var texte = (memo_eleve) ? 'Implication de l&#8217;élève' : 'Projet mis en oeuvre' ; // ’ remplacé par &#8217; car ne s'affiche pas correctement sur le serveur de Bordeaux ; corresponce trouvée avec http://hapax.qc.ca/conversion.fr.html
          }
          else if(memo_rubrique_type=='bilan')
          {
            var texte = (memo_eleve) ? 'Synthèse / Conseils' : 'Synthèse' ;
          }
          else if(memo_rubrique_type=='attitude')
          {
            var texte = 'Observations';
          }
          else if(memo_rubrique_type=='viesco')
          {
            var texte = 'Vie scolaire';
          }
          if(memo_saisie_objet!='elements')
          {
            var lien_dgesco = '';
            var label_reste = '<label id="f_'+memo_saisie_objet+'_reste"></label>';
            memo_long_max = (memo_rubrique_id) ? tab_config[memo_config]["APP_RUBRIQUE_LONGUEUR"] : tab_config[memo_config]["APP_GENERALE_LONGUEUR"] ;
          }
          else
          {
            var lien_dgesco = (memo_eleve) ? '' : '<div><a id="voir_elements" href="#">Piocher parmi les propositions IGEN / DGESCO.</a></div>' ;
            var label_reste = '';
            memo_long_max = 1000;
          }
          var nb_lignes = parseInt(memo_long_max/100,10);
          var cols = ( (memo_rubrique_type=='eval') || (memo_rubrique_type=='socle') ) ? 50 : 125 ;
          var formulaire_saisie = '<div><b>'+texte+' [ '+$('#go_selection_eleve option:selected').text()+' ] :</b></div>'
                                + lien_dgesco
                                + '<div><textarea id="f_'+memo_saisie_objet+'" name="f_'+memo_saisie_objet+'" rows="'+nb_lignes+'" cols="'+cols+'"></textarea></div>'
                                + '<div>'+label_reste+'</div>'
                                + '<div><button id="valider_precedent" type="button" class="valider_prev">Précédent</button> <button id="valider" type="button" class="valider">Valider</button> <button id="valider_suivant" type="button" class="valider_next">Suivant</button></div>'
                                + '<div><button id="annuler_precedent" type="button" class="annuler_prev">Précédent</button> <button id="annuler" type="button" class="annuler">Annuler</button> <button id="annuler_suivant" type="button" class="annuler_next">Suivant</button></div>'
                                + '<div><label id="ajax_msg_'+memo_saisie_objet+'">&nbsp;</label></div>';
          memo_conteneur.html(formulaire_saisie);
        }
        // 2/3 Fabriquer un formulaire de saisie input[type=number]
        else if( (memo_page_colonne=='moyenne') || (memo_page_colonne=='pourcentage') )
        {
          memo_html = memo_conteneur.html();
          var max      = (memo_page_colonne=='moyenne') ? MOYENNE_MAXI : POURCENTAGE_MAXI ;
          var pourcent = (memo_page_colonne=='moyenne') ? ''  : '%' ;
          var step     = (memo_page_colonne=='moyenne') ? 0.1 : 0.5 ;
          var formulaire_saisie = '<div><b>Positionnement [ '+$('#go_selection_eleve option:selected').text()+' ] :</b> <input id="f_position" name="f_position" type="number" min="0" max="'+max+'" step="'+step+'" value="" />'+pourcent+'</div>'
                                + '<div><button id="valider_precedent" type="button" class="valider_prev">Précédent</button> <button id="valider" type="button" class="valider">Valider</button> <button id="valider_suivant" type="button" class="valider_next">Suivant</button></div>'
                                + '<div><button id="annuler_precedent" type="button" class="annuler_prev">Précédent</button> <button id="annuler" type="button" class="annuler">Annuler</button> <button id="annuler_suivant" type="button" class="annuler_next">Suivant</button></div>'
                                + '<div><label id="ajax_msg_'+memo_saisie_objet+'">&nbsp;</label></div>';
          memo_conteneur.html(formulaire_saisie);
        }
        // 3/3 Fabriquer un formulaire de saisie input[type=radio]
        else if( (memo_page_colonne=='objectif') || (memo_page_colonne=='position') || (memo_page_colonne=='reussite') )
        {
          memo_html = memo_conteneur.parent().html();
          var id_debut = memo_rubrique_type+'_'+memo_rubrique_id+'_'+memo_saisie_objet+'_';
          var i_fin = (memo_page_colonne!='reussite') ? 4 : 3 ;
          for( var i=1 ; i<=i_fin ; i++ )
          {
            $('#'+id_debut+i).html('<label for="f_position_'+i+'"><input id="f_position_'+i+'" name="f_position" type="radio" value="'+i+'" /></label>');
          }
          var formulaire_saisie = '<div><button id="valider_precedent" type="button" class="valider_prev" title="Valider & Précédent">&nbsp;</button> <button id="valider" type="button" class="valider" title="Valider">&nbsp;</button> <button id="valider_suivant" type="button" class="valider_next" title="Valider & Suivant">&nbsp;</button></div>'
                                + '<div><button id="annuler_precedent" type="button" class="annuler_prev" title="Annuler & Précédent">&nbsp;</button> <button id="annuler" type="button" class="annuler" title="Annuler">&nbsp;</button> <button id="annuler_suivant" type="button" class="annuler_next" title="Annuler & Suivant">&nbsp;</button></div>'
                                + '<div><label id="ajax_msg_'+memo_saisie_objet+'">&nbsp;</label></div>';
          memo_conteneur.html(formulaire_saisie);
        }
        else if(memo_page_colonne=='maitrise')
        {
          if(memo_rubrique_type=='enscompl')
          {
            var i_debut = 3;
          }
          else if(memo_rubrique_id==12) // langue étrangère avec positionnement dispensé possible
          {
            var i_debut = 0;
          }
          else
          {
            var i_debut = 1;
          }
          memo_html = memo_conteneur.parent().html();
          var id_debut = memo_rubrique_type+'_'+memo_rubrique_id+'_'+memo_saisie_objet+'_';
          var i_fin = 4;
          for( var i=i_debut ; i<=i_fin ; i++ )
          {
            $('#'+id_debut+i).html('<label for="f_position_'+i+'" style="padding:2em 3em;"><input id="f_position_'+i+'" name="f_position" type="radio" value="'+i+'" /></label>');
          }
          var formulaire_saisie = '<div><button id="valider_precedent" type="button" class="valider_prev" title="Valider & Précédent">&nbsp;</button> <button id="valider" type="button" class="valider" title="Valider">&nbsp;</button> <button id="valider_suivant" type="button" class="valider_next" title="Valider & Suivant">&nbsp;</button></div>'
                                + '<div><button id="annuler_precedent" type="button" class="annuler_prev" title="Annuler & Précédent">&nbsp;</button> <button id="annuler" type="button" class="annuler" title="Annuler">&nbsp;</button> <button id="annuler_suivant" type="button" class="annuler_next" title="Annuler & Suivant">&nbsp;</button></div>'
                                + '<div><label id="ajax_msg_'+memo_saisie_objet+'">&nbsp;</label></div>';
          memo_conteneur.html(formulaire_saisie);
        }
        // modif affichage
        if(memo_eleve==memo_eleve_first)
        {
          $('#valider_precedent , #annuler_precedent').css('visibility','hidden');
        }
        if(memo_eleve==memo_eleve_last)
        {
          $('#valider_suivant , #annuler_suivant').css('visibility','hidden');
        }
        // finalisation (remplissage et focus)
        if( (memo_saisie_objet=='appreciation') || (memo_saisie_objet=='elements') )
        {
          $('#f_'+memo_saisie_objet).focus().html(saisie_contenu);
          afficher_textarea_reste( $('#f_'+memo_saisie_objet) , memo_long_max );
          window.scrollBy(0,100); // Pour avoir à l'écran les bouton de validation et d'annulation situés en dessous du textarea
        }
        else if( (memo_page_colonne=='moyenne') || (memo_page_colonne=='pourcentage') )
        {
          // report d'un positionnement numérique
          var valeur = (memo_page_colonne=='moyenne') ? parseFloat(saisie_contenu,10) : parseInt(saisie_contenu.substr(0,saisie_contenu.length-1),10) ;
          valeur = (isNaN(valeur)) ? '' : valeur ;
          $('#f_'+memo_saisie_objet).focus().val(valeur);
        }
        else if( (memo_page_colonne=='objectif') || (memo_page_colonne=='position') || (memo_page_colonne=='maitrise') || (memo_page_colonne=='reussite') )
        {
          // report d'un positionnement sur une échelle
          if(saisie_contenu!=='')
          {
            $('#f_position_'+saisie_contenu).prop('checked',true).focus();
          }
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [livret_saisir] Indiquer le nombre de caractères restant autorisés dans le textarea
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_action_eleve').on
    (
      'keyup',
      '#f_appreciation, #f_elements',
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
    // [livret_saisir] Clic sur un bouton pour annuler une saisie de positionnement / appréciation / éléments du programme
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_action_eleve').on
    (
      'click',
      '#annuler , #annuler_suivant , #annuler_precedent , #annuler , #annuler_suivant , #annuler_precedent',
      function()
      {
        memo_auto_next = ($(this).attr('id')=='annuler_suivant')   ? true : false ;
        memo_auto_prev = ($(this).attr('id')=='annuler_precedent') ? true : false ;
        if( (memo_saisie_objet!='position') || (memo_page_colonne=='moyenne') || (memo_page_colonne=='pourcentage') )
        {
          memo_conteneur.html(memo_html);
        }
        else
        {
          memo_conteneur.parent().html(memo_html);
        }
        $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
        if(memo_auto_next) { $('#go_suivant_eleve').click(); }
        if(memo_auto_prev) { $('#go_precedent_eleve').click(); }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [livret_saisir] Clic sur un bouton pour valider une saisie de positionnement / appréciation / éléments du programme
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_action_eleve').on
    (
      'click',
      '#valider , #valider_suivant , #valider_precedent , #valider , #valider_suivant , #valider_precedent',
      function()
      {
        // appréciation préremplie
        if( (memo_saisie_objet=='appreciation') || (memo_saisie_objet=='elements') )
        {
          if( !$.trim($('#f_'+memo_saisie_objet).val()).length )
          {
            $('#ajax_msg_'+memo_saisie_objet).attr('class','erreur').html("Absence de saisie !");
            $('#f_'+memo_saisie_objet).focus();
            return false;
          }
        }
        // positionnement numérique
        else if( (memo_page_colonne=='moyenne') || (memo_page_colonne=='pourcentage') )
        {
          var position = parseFloat($('#f_position').val(),10);
          if( isNaN(position) )
          {
            $('#ajax_msg_'+memo_saisie_objet).attr('class','erreur').html("Saisie incorrecte !");
            $('#f_position').focus();
            return false;
          }
          if( (position<0) || ((position>MOYENNE_MAXI)&&(memo_page_colonne=='moyenne')) || ((position>POURCENTAGE_MAXI)&&(memo_page_colonne=='pourcentage')) )
          {
            $('#ajax_msg_'+memo_saisie_objet).attr('class','erreur').html("Valeur incorrecte !");
            $('#f_position').focus();
            return false;
          }
        }
        // positionnement sur une échelle
        else if( (memo_page_colonne=='objectif') || (memo_page_colonne=='position') || (memo_page_colonne=='maitrise') || (memo_page_colonne=='reussite') )
        {
          position = $("input[name=f_position]:checked").val();
          if(typeof(position)=='undefined')
          {
            $('#ajax_msg_'+memo_saisie_objet).attr('class','erreur').html("Absence de positionnement !");
            return false;
          }
        }
        memo_auto_next = ($(this).attr('id')=='valider_suivant')   ? true : false ;
        memo_auto_prev = ($(this).attr('id')=='valider_precedent') ? true : false ;
        $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',true);
        $('#ajax_msg_'+memo_saisie_objet).attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_action='+memo_action+'_saisie'+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_page_ref='+memo_page_ref+'&f_periode='+memo_periode+'&f_user='+memo_eleve+'&f_saisie_id='+memo_saisie_id+'&f_rubrique_type='+memo_rubrique_type+'&f_rubrique_id='+memo_rubrique_id+'&f_saisie_objet='+memo_saisie_objet+'&f_page_colonne='+memo_page_colonne+'&'+$('#form_hidden').serialize()+'&'+$('#zone_resultat_eleve').serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg_'+memo_saisie_objet).attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==false)
              {
                $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
                $('#ajax_msg_'+memo_saisie_objet).attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                if( (memo_saisie_objet!='position') || (memo_page_colonne=='moyenne') || (memo_page_colonne=='pourcentage') )
                {
                  if(memo_rubrique_type=='viesco')
                  {
                     responseJSON['value'] += '<div id="div_assiduite" class="notnow i">'+memo_div_assiduite+'</div>';
                  }
                  memo_conteneur.html(responseJSON['value']);
                }
                else
                {
                  memo_conteneur.parent().html(memo_html);
                  if(memo_rubrique_type=='enscompl')
                  {
                    var i_debut = 3;
                  }
                  else if(memo_rubrique_id==12) // langue étrangère avec positionnement dispensé possible
                  {
                    var i_debut = 0;
                  }
                  else
                  {
                    var i_debut = 1;
                  }
                  var i_fin = (memo_page_colonne!='reussite') ? 4 : 3 ;
                  for( var i=i_debut ; i<=i_fin ; i++ )
                  {
                    $('#'+memo_rubrique_type+'_'+memo_rubrique_id+'_position_'+i).html(responseJSON['td_'+i]);
                  }
                  $('#'+memo_rubrique_type+'_'+memo_rubrique_id+'_position_'+memo_page_colonne).html(responseJSON['td_'+memo_page_colonne]);
                }
                $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
                if(memo_auto_next) { $('#go_suivant_eleve').click(); }
                if(memo_auto_prev) { $('#go_precedent_eleve').click(); }
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [livret_saisir] Clic sur le bouton pour supprimer un positionnement ou une appréciation ou des éléments du programme ou un rattachement à une rubrique
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_action_eleve').on
    (
      'click',
      'button.supprimer',
      function()
      {
        memo_bouton = $(this);
        memo_action = memo_bouton.attr('class'); // supprimer
        memo_conteneur = memo_bouton.parent().parent();
        // Récupération des principaux identifiants
        memo_saisie_id  = memo_bouton.parent().attr('data-id');
        memo_objet_id   = memo_conteneur.attr('id');
        var tab_ids     = memo_objet_id.split('_');
        memo_rubrique_type = tab_ids[0]; // eval | socle | epi | ap | parcours | bilan | viesco | enscompl | attitude
        memo_rubrique_id   = parseInt( tab_ids[1] , 10 );
        memo_saisie_objet  = tab_ids[2]; // position | appreciation | elements | saisiejointure
        memo_page_colonne  = (memo_saisie_objet=='position') ? tab_ids[3] : '' ; // objectif | position | moyenne | pourcentage | maitrise | reussite
        // Contenu de la saisie existante
        if(memo_rubrique_type=='viesco')
        {
           memo_div_assiduite = ($('#div_assiduite').length) ? $('#div_assiduite').html() : '' ;
        }
        // Désactiver les autres boutons d'action
        $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',true);
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_action=supprimer_saisie'+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_page_ref='+memo_page_ref+'&f_periode='+memo_periode+'&f_user='+memo_eleve+'&f_saisie_id='+memo_saisie_id+'&f_rubrique_type='+memo_rubrique_type+'&f_rubrique_id='+memo_rubrique_id+'&f_saisie_objet='+memo_saisie_objet+'&f_page_colonne='+memo_page_colonne+'&'+$('#form_hidden').serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+' Veuillez recommencer.'+'</label>' , {'centerOnScroll':true , 'minWidth':500} );
              $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $.fancybox( '<label class="alerte">'+responseJSON['value']+'</label>' , {'centerOnScroll':true , 'minWidth':500} );
              }
              else
              {
                if(memo_saisie_objet=='saisiejointure')
                {
                  memo_bouton.remove();
                  $('#'+responseJSON['value']).remove();
                }
                else if( (memo_saisie_objet!='position') || (memo_page_colonne=='moyenne') || (memo_page_colonne=='pourcentage') )
                {
                  if(memo_rubrique_type=='viesco')
                  {
                     responseJSON['value'] += '<div id="div_assiduite" class="notnow i">'+memo_div_assiduite+'</div>';
                  }
                  memo_conteneur.html(responseJSON['value']);
                }
                else
                {
                  // memo_conteneur.parent().html(memo_html); // Pas besoin ici car il n'y a pas eu de remplacement du contenu
                  if(memo_rubrique_type=='enscompl')
                  {
                    var i_debut = 3;
                  }
                  else if(memo_rubrique_id==12) // langue étrangère avec positionnement dispensé possible
                  {
                    var i_debut = 0;
                  }
                  else
                  {
                    var i_debut = 1;
                  }
                  var i_fin = (memo_page_colonne!='reussite') ? 4 : 3 ;
                  for( var i=i_debut ; i<=i_fin ; i++ )
                  {
                    $('#'+memo_rubrique_type+'_'+memo_rubrique_id+'_position_'+i).html(responseJSON['td_'+i]);
                  }
                  $('#'+memo_rubrique_type+'_'+memo_rubrique_id+'_position_'+memo_page_colonne).html(responseJSON['td_'+memo_page_colonne]);
                }
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [livret_saisir] Clic sur le bouton pour recalculer un positionnement ou une appréciation ou des éléments du programme (soit effacé - NULL - soit figé car saisi manuellement)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_action_eleve').on
    (
      'click',
      'button.eclair',
      function()
      {
        memo_action = $(this).attr('class'); // eclair (!)
        memo_conteneur = $(this).parent().parent();
        // Récupération des principaux identifiants
        memo_saisie_id  = $(this).parent().attr('data-id');
        memo_objet_id   = memo_conteneur.attr('id');
        var tab_ids     = memo_objet_id.split('_');
        memo_rubrique_type = tab_ids[0]; // eval | socle | epi | ap | parcours | bilan | viesco | enscompl | attitude
        memo_rubrique_id   = parseInt( tab_ids[1] , 10 );
        memo_saisie_objet  = tab_ids[2]; // position | appreciation | elements
        memo_page_colonne  = (memo_saisie_objet=='position') ? tab_ids[3] : '' ; // objectif | position | moyenne | pourcentage | maitrise | reussite
        // Désactiver les autres boutons d'action
        $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',true);
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_action='+'recalculer_saisie'+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_page_ref='+memo_page_ref+'&f_periode='+memo_periode+'&f_user='+memo_eleve+'&f_saisie_id='+memo_saisie_id+'&f_rubrique_type='+memo_rubrique_type+'&f_rubrique_id='+memo_rubrique_id+'&f_saisie_objet='+memo_saisie_objet+'&f_page_colonne='+memo_page_colonne+'&'+$('#form_hidden').serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+' Veuillez recommencer.'+'</label>' , {'centerOnScroll':true , 'minWidth':500} );
              $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $.fancybox( '<label class="alerte">'+responseJSON['value']+'</label>' , {'centerOnScroll':true , 'minWidth':500} );
              }
              else
              {
                if( (memo_saisie_objet!='position') || (memo_page_colonne=='moyenne') || (memo_page_colonne=='pourcentage') )
                {
                  memo_conteneur.html(responseJSON['value']);
                }
                else
                {
                  // memo_conteneur.parent().html(memo_html); // Pas besoin ici car il n'y a pas eu de remplacement du contenu
                  if(memo_rubrique_type=='enscompl')
                  {
                    var i_debut = 3;
                  }
                  else if(memo_rubrique_id==12) // langue étrangère avec positionnement dispensé possible
                  {
                    var i_debut = 0;
                  }
                  else
                  {
                    var i_debut = 1;
                  }
                  var i_fin = (memo_page_colonne!='reussite') ? 4 : 3 ;
                  for( var i=i_debut ; i<=i_fin ; i++ )
                  {
                    $('#'+memo_rubrique_type+'_'+memo_rubrique_id+'_position_'+i).html(responseJSON['td_'+i]);
                  }
                  $('#'+memo_rubrique_type+'_'+memo_rubrique_id+'_position_'+memo_page_colonne).html(responseJSON['td_'+memo_page_colonne]);
                }
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [livret_examiner] Charger le contenu (résultat de l'examen pour une classe)
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
            data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_page_ref='+memo_page_ref+'&f_periode='+memo_periode+'&'+$('#form_hidden').serialize(),
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
    // [livret_imprimer] Lancer l'impression pour une liste d'élèves
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function imprimer(etape)
    {
      $('#ajax_msg_imprimer').attr('class','loader').html("En cours&hellip; Étape "+etape+"/4.");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_action='+'imprimer'+'&f_etape='+etape+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_page_ref='+memo_page_ref+'&f_periode='+memo_periode+'&'+$('#form_hidden').serialize(),
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
        $('#form_choix_classe button , #form_choix_classe select , #valider_imprimer').prop('disabled',true);
        imprimer(1);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [livret_imprimer] Charger la liste de choix des élèves, avec info si les livrets sont déjà imprimés
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
          data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_action='+'initialiser'+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_page_ref='+memo_page_ref+'&f_periode='+memo_periode+'&'+$('#form_hidden').serialize(),
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
    // [livret_examiner|livret_imprimer] Actualiser l'état enabled/disabled des options du formulaire de navigation dans les classes, masquer les boutons de navigation
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
      var indice = (memo_section=='livret_examiner') ? 'examiner' : ( (memo_objet=='imprimer') ? 'imprimer' : 'voir_pdf' ) ;
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
    // [livret_examiner|livret_imprimer] Navigation d'une classe à une autre
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
      memo_page_ref = tab_bilan_page_ref[classe_groupe_id+'_'+memo_periode];
      if(memo_section=='livret_imprimer')
      {
        charger_formulaire_imprimer();
      }
      else if(memo_section=='livret_examiner')
      {
        $('#form_choix_classe button , #form_choix_classe select').prop('disabled',true);
        $('#zone_resultat_classe').html('<label class="loader">En cours&hellip;</label>');
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_page_ref='+memo_page_ref+'&f_periode='+memo_periode+'&'+$('#form_hidden').serialize(),
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
    // [livret_saisir|livret_consulter] Afficher le formulaire pour signaler ou corriger une faute
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_action_eleve').on
    (
      'click',
      'button.signaler , button.corriger',
      function()
      {
        memo_action = $(this).attr('class'); // signaler | corriger
        memo_conteneur = $(this).parent().parent();
        // Récupération des principaux identifiants
        memo_saisie_id  = $(this).parent().attr('data-id');
        memo_objet_id   = memo_conteneur.attr('id');
        var tab_ids     = memo_objet_id.split('_');
        memo_rubrique_type = tab_ids[0]; // eval | socle | epi | ap | parcours | bilan | viesco | enscompl | attitude
        memo_rubrique_id   = parseInt( tab_ids[1] , 10 );
        memo_saisie_objet  = tab_ids[2]; // appreciation
        var prof_id        = parseInt( tab_ids[3] , 10 );
        // Préparation de l'affichage
        $('#f_action').val(memo_action+'_faute');
        $('#zone_signaler_corriger h2').html(memo_action[0].toUpperCase() + memo_action.substring(1) + " une faute");
        var appreciation_contenu = $(this).parent().prev().html();
        var message_contenu = 'Livret Scolaire - '+$('#periode_'+memo_periode).text()+' - '+$('#groupe_'+memo_classe+'_'+memo_groupe).text()+"\n\n"+'Concernant '+$('#go_selection_eleve option:selected').text()+', ';
        $('#f_destinataire_id').val(prof_id);
        // Affichage supplémentaire si correction de l'appréciation
        if(memo_action=='corriger')
        {
          if( prof_id != USER_ID )
          {
            $('#section_signaler').show(0);
          }
          else
          {
            $('#section_signaler').hide(0);
          }
          memo_long_max = (memo_rubrique_type!='bilan') ? tab_config[memo_config]["APP_RUBRIQUE_LONGUEUR"] : tab_config[memo_config]["APP_GENERALE_LONGUEUR"] ;
          var nb_lignes = parseInt(memo_long_max/100,10);
          message_contenu += 'je me suis permis de corriger l\'appréciation en remplaçant "ANCIENCONTENU" par "NOUVEAUCONTENU".';
          $('#section_corriger').html('<div><label for="f_appreciation" class="tab">Appréciation  :</label><textarea name="f_appreciation" id="f_appreciation" rows="'+nb_lignes+'" cols="100"></textarea></div>'+'<div><span class="tab"></span><label id="f_appreciation_reste"></label></div>').show(0);
          $('#f_appreciation').focus().html(unescapeHtml(appreciation_contenu));
          afficher_textarea_reste( $('#f_appreciation') , memo_long_max );
        }
        else if(memo_action=='signaler')
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
    // [livret_saisir|livret_consulter] Indiquer le nombre de caractères restant autorisés dans le textarea
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_message_contenu').keyup
    (
      function()
      {
        afficher_textarea_reste( $(this) , nb_caracteres_max );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [livret_saisir|livret_consulter] Clic sur le bouton pour fermer le cadre de rédaction d'un signalement d'une faute (annuler / retour)
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
    // [livret_saisir|livret_consulter] Valider le formulaire pour signaler ou corriger une faute
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
              data : 'csrf='+CSRF+'&f_section='+'livret_saisir'+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_page_ref='+memo_page_ref+'&f_periode='+memo_periode+'&f_user='+memo_eleve+'&f_saisie_id='+memo_saisie_id+'&f_rubrique_type='+memo_rubrique_type+'&f_rubrique_id='+memo_rubrique_id+'&f_saisie_objet='+memo_saisie_objet+'&f_prof='+prof_id+'&'+$('#form_hidden').serialize()+'&'+$('#zone_signaler_corriger').serialize(),
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
                  $('#'+memo_objet_id).find('div.appreciation').html(responseJSON['value']);
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
    // [livret_saisir|livret_consulter] Clic sur le bouton pour voir le détail des items évalués
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_action_eleve').on
    (
      'click',
      'a.voir_detail',
      function()
      {
        var id = $(this).attr('data-id');
        $.fancybox( { 'href':'#detail_'+id , onStart:function(){$('#detail_'+id).css("display","block");} , onClosed:function(){$('#detail_'+id).css("display","none");} , 'centerOnScroll':true , 'minWidth':600 } );
        return false;
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // [livret_saisir] Clic sur le lien pour voir piocher dans la liste IGEN / DGESCO
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var correspondance_domaine = {
      // Cycle 2
      '2x111' : '2x21', // Enseignement moral et civique
      '2x81'  : '2x22', // Éducation physique et sportive
      '2x91'  : '2x23', // Enseignements artistiques
      '2x61'  : '2x24', // Français
      '2x121' : '2x25', // Langues vivantes
      '2x71'  : '2x26', // Mathématiques
      '2x101' : '2x27', // Questionner le monde
      // Cycle 3 1D
      '3x191' : '3x31', // Enseignement moral et civique
      '3x151' : '3x32', // Éducation physique et sportive
      '3x161' : '3x34', // Enseignements artistiques
      '3x131' : '3x35', // Français
      '3x181' : '3x37', // Histoire-Géographie
      '3x201' : '3x38', // Langues vivantes
      '3x141' : '3x39', // Mathématiques
      '3x171' : '3x40', // Sciences et technologie
      // Cycle 3 2D
      '3x901'  : '3x30', // Arts plastiques
      '3x438'  : '3x31', // Enseignement moral et civique
      '3x414'  : '3x31', // Education civique
      '3x1001' : '3x32', // Éducation physique et sportive
      '3x813'  : '3x33', // Éducation musicale
      '3x9943' : '3x34', // Enseignements artistiques
      '3x207'  : '3x35', // Français
      '3x2757' : '3x36', // Histoire des Arts
      '3x437'  : '3x37', // Histoire-Géographie
      '3x406'  : '3x37', // Histoire et géographie
      '3x416'  : '3x37', // Histoire - géographie - instruction civique
      '3x421'  : '3x37', // Histoire - géographie - éducation civique
      '3x9944' : '3x38', // Langues vivantes
      '3x301'  : '3x38', // Allemand
      '3x302'  : '3x38', // Anglais
      '3x306'  : '3x38', // Espagnol
      '3x315'  : '3x38', // Allemand LV1
      '3x316'  : '3x38', // Anglais LV1
      '3x320'  : '3x38', // Espagnol LV1
      '3x327'  : '3x38', // Allemand LV2
      '3x328'  : '3x38', // Anglais LV2
      '3x332'  : '3x38', // Espagnol LV2
      '3x613'  : '3x39', // Mathématiques
      '3x9942' : '3x40', // Sciences et technologie
      // Cycle 4
      '4x901'  : '4x41', // Arts plastiques
      '4x438'  : '4x42', // Enseignement moral et civique
      '4x414'  : '4x42', // Education civique
      '4x0'    : '4x43', // Éducation aux médias et à l’information (EMI)
      '4x1001' : '4x44', // Éducation physique et sportive
      '4x813'  : '4x45', // Éducation musicale
      '4x207'  : '4x46', // Français
      '4x2757' : '4x47', // Histoire des Arts
      '4x437'  : '4x48', // Histoire-Géographie
      '4x406'  : '4x48', // Histoire et géographie
      '4x416'  : '4x48', // Histoire - géographie - instruction civique
      '4x421'  : '4x48', // Histoire - géographie - éducation civique
      '4x9944' : '4x49', // Langues vivantes
      '4x301'  : '4x49', // Allemand
      '4x302'  : '4x49', // Anglais
      '4x306'  : '4x49', // Espagnol
      '4x315'  : '4x49', // Allemand LV1
      '4x316'  : '4x49', // Anglais LV1
      '4x320'  : '4x49', // Espagnol LV1
      '4x327'  : '4x49', // Allemand LV2
      '4x328'  : '4x49', // Anglais LV2
      '4x332'  : '4x49', // Espagnol LV2
      '4x613'  : '4x50', // Mathématiques
      '4x623'  : '4x51', // Physique-Chimie
      '4x629'  : '4x52', // Sciences de la vie et de la Terre
      '4x708'  : '4x53'  // Technologie
    };

    $('#zone_action_eleve').on
    (
      'click',
      '#voir_elements',
      function()
      {
        var $zone_elements = $('#zone_elements');
        // Replier tout sauf le plus haut niveau la 1e fois ; ensuite on laisse aussi volontairement ouvert ce qui a pu l'être précédemment
        $zone_elements.find('ul').css("display","none");
        $zone_elements.find('ul.ul_n1').css("display","block");
        $zone_elements.find('li.li_n3').css("display","block");
        // ouvrir le cycle
        var cycle_id = $('#cycle_id').val();
        $('#el'+cycle_id).find('ul.ul_m1').css("display","block");
        // ouvrir le domaine / la matière
        var clef = cycle_id+'x'+memo_rubrique_id;
        if(typeof(correspondance_domaine[clef])!='undefined')
        {
          var domaine_id = correspondance_domaine[clef];
          $('#el'+domaine_id).find('ul.ul_m2').css("display","block");
          // ouvrir le niveau
          var niveau_id = (cycle_id!='3') ? cycle_id+'0' : ( (memo_page_ref=='6') ? '34' : '33' ) ;
          $('#el'+domaine_id+'x'+niveau_id).find('ul.ul_n2').css("display","block");
        }
        $.fancybox( { 'href':'#zone_elements' , onStart:function(){$('#zone_elements').css("display","block");} , onClosed:function(){$('#zone_elements').css("display","none");} , 'centerOnScroll':true , 'minWidth':800 , 'minHeight':800 } );
        return false;
      }
    );

    $('#zone_elements').on
    (
      'click',
      'q.ajouter',
      function()
      {
        var ligne = $(this).parent().text().trim();
        $('#f_'+memo_saisie_objet).focus().html( $('#f_'+memo_saisie_objet).val() + ligne + "\n" );
        $(this).parent().css("display","none");
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

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Récupéré après le chargement de la page car un peu lourd (> 100 ko)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    if(PROFIL_TYPE=='professeur')
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_load_arborescence',
          data : 'f_objet=elements_dgesco',
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#arborescence label').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            return false;
          },
          success : function(responseJSON)
          {
            $('#arborescence').replaceWith(responseJSON['value']);
          }
        }
      );
    }

  }
);
