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

    var objet        = $('#f_objet option:selected').val();
    var prof_id      = 0;
    var matiere_id   = 0;
    var groupe_id    = 0;
    var groupe_type  = $("#f_groupe option:selected").parent().attr('label'); // Il faut indiquer une valeur initiale au moins pour le profil élève
    var eleves_ordre = '';

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Enlever le message ajax et le résultat précédent au changement d'un élément de formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_select').on
    (
      'change',
      'select, input',
      function()
      {
        $('#ajax_msg').removeAttr('class').html("");
        $('#bilan').html("");
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Afficher / masquer des éléments du formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#f_objet").change
    (
      function()
      {
        // on masque
        $('#choix_matiere , #choix_multimatiere , #choix_selection , #choix_professeur , #choix_evaluation').hide();
        // on affiche
        objet = $('#f_objet option:selected').val();
        if(objet)
        {
          $('#choix_'+objet).show();
        }
        if(objet=='evaluation')
        {
          $('#zone_periodes').addClass("hide");
          charger_evaluations_prof();
        }
        else if( $('#f_groupe option:selected').val() )
        {
          $('#zone_periodes').removeAttr('class');
        }
        if(objet=='multimatiere')
        {
          $('#div_not_multimatiere_1 , #div_not_multimatiere_2').hide();
          if(!$('#f_type_individuel').is(':checked'))
          {
            $('#f_type_individuel').click();
          }
        }
        else
        {
          $('#div_not_multimatiere_1 , #div_not_multimatiere_2').show();
        }
      }
    );

    function visibility_option_with_coef()
    {
      if( ($('#f_type_synthese').is(':checked')) || ($('#f_type_bulletin').is(':checked')) || ( ($('#f_type_individuel').is(':checked')) && ($('#f_moyenne_scores').is(':checked')) ) )
      {
        $("#option_with_coef").attr("class","show");
      }
      else
      {
        $("#option_with_coef").attr("class","hide");
      }
    }

    $('#f_type_individuel').click
    (
      function()
      {
        $("#options_individuel").toggle();
        visibility_option_with_coef();
      }
    );

    $('#f_type_synthese').click
    (
      function()
      {
        $("#options_synthese").toggle();
        visibility_option_with_coef();
      }
    );

    $('#f_type_bulletin').click
    (
      function()
      {
        visibility_option_with_coef();
        if($(this).is(':checked'))
        {
          $("#f_individuel_format option[value=eleve]").prop('selected',true);
        }
      }
    );

    $('#f_individuel_format').change
    (
      function()
      {
        if($(this).val()=='item')
        {
          $("#f_type_bulletin").prop('checked',false);
        }
      }
    );

    $('#f_etat_acquisition').click
    (
      function()
      {
        $("#span_etat_acquisition").toggle();
      }
    );

    $('#f_moyenne_scores , #f_pourcentage_acquis').click
    (
      function()
      {
        if( ($('#f_moyenne_scores').is(':checked')) || ($('#f_pourcentage_acquis').is(':checked')) )
        {
          $('label[for=f_conversion_sur_20]').show();
        }
        else
        {
          $('label[for=f_conversion_sur_20]').hide();
        }
        visibility_option_with_coef();
      }
    );

    var autoperiode = true; // Tant qu'on ne modifie pas manuellement le choix des périodes, modification automatique du formulaire

    function view_dates_perso()
    {
      var periode_val = $("#f_periode").val();
      if(periode_val!=0)
      {
        $("#dates_perso").attr("class","hide");
      }
      else
      {
        $("#dates_perso").attr("class","show");
      }
    }

    $('#f_periode').change
    (
      function()
      {
        view_dates_perso();
        autoperiode = false;
      }
    );

    $('#f_cases_auto').click
    (
      function()
      {
        $("#span_cases_auto").toggle();
        $("#span_cases_manuel").toggle();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Charger tous les profs d'une classe (approximativement) ou n'affiche que le prof connecté
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var action_prof = 'ajouter';

    function afficher_prof_connecte()
    {
      $('#f_prof').html('<option value="'+user_id+'">'+user_texte+'</option>');
      $('#modifier_prof').attr('class','form_ajouter');
      action_prof = 'ajouter';
    }

    function charger_profs_groupe()
    {
      $('button').prop('disabled',true);
      prof_id     = $("#f_prof   option:selected").val();
      groupe_id   = $("#f_groupe option:selected").val();
      groupe_type = $("#f_groupe option:selected").parent().attr('label');
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_maj_select_profs_groupe',
          data : 'f_prof='+prof_id+'&f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('button').prop('disabled',false);
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            $('button').prop('disabled',false);
            if(responseJSON['statut']==true)
            {
              $('#f_prof').html(responseJSON['value']);
              $('#modifier_prof').attr('class','form_retirer');
              action_prof = 'retirer';
            }
          }
        }
      );
    }

    $("#modifier_prof").click
    (
      function()
      {
        if(action_prof=='retirer')
        {
          afficher_prof_connecte();
        }
        else if(action_prof=='ajouter')
        {
          charger_profs_groupe();
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Charger tous les évaluations d'un profs, sur un regroupement ou non
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function charger_evaluations_prof()
    {
      // Pour un professeur uniquement
      if(PROFIL_TYPE=='professeur')
      {
        $("#f_evaluation").html('');
        groupe_id = $("#f_groupe option:selected").val();
        if(groupe_id)
        {
          $('#zone_evals').removeAttr('class');
          $('#ajax_maj_evals').attr('class','loader').html("En cours&hellip;");
          $.ajax
          (
            {
              type : 'POST',
              url : 'ajax.php?page=_maj_select_eval',
              data : 'f_objet=releve_items'+'&f_groupe_id='+groupe_id,
              dataType : 'json',
              error : function(jqXHR, textStatus, errorThrown)
              {
                $('#ajax_maj_evals').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              },
              success : function(responseJSON)
              {
                initialiser_compteur();
                if(responseJSON['statut']==true)
                {
                  $('#ajax_maj_evals').removeAttr('class').html("");
                  $('#f_evaluation').html(responseJSON['value']);
                }
                else
                {
                  $('#ajax_maj_evals').attr('class','alerte').html(responseJSON['value']);
                }
              }
            }
          );
        }
        else
        {
          $('#zone_evals').addClass("hide");
        }
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Changement de groupe
    // -> desactiver les périodes prédéfinies en cas de groupe de besoin (prof uniquement)
    // -> choisir automatiquement la meilleure période si un changement manuel de période n'a jamais été effectué
    // -> afficher le formulaire de périodes s'il est masqué
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function selectionner_periode_adaptee()
    {
      var id_groupe = $('#f_groupe option:selected').val();
      if(typeof(tab_groupe_periode[id_groupe])!='undefined')
      {
        for(var id_periode in tab_groupe_periode[id_groupe]) // Parcourir un tableau associatif...
        {
          var tab_split = tab_groupe_periode[id_groupe][id_periode].split('_');
          if( (date_mysql>=tab_split[0]) && (date_mysql<=tab_split[1]) )
          {
            $("#f_periode option[value="+id_periode+"]").prop('selected',true);
            view_dates_perso();
            break;
          }
        }
      }
    }

    $('#f_groupe').change
    (
      function()
      {
        groupe_type = $("#f_groupe option:selected").parent().attr('label');
        $("#f_periode option").each
        (
          function()
          {
            periode_id = $(this).val();
            // La période personnalisée est tout le temps accessible
            if(periode_id!=0)
            {
              // classe ou groupe classique -> toutes périodes accessibles
              if(groupe_type!='Besoins')
              {
                $(this).prop('disabled',false);
              }
              // groupe de besoin -> desactiver les périodes prédéfinies
              else
              {
                $(this).prop('disabled',true);
              }
            }
          }
        );
        // Sélectionner si besoin la période personnalisée
        if(groupe_type=='Besoins')
        {
          $("#f_periode option[value=0]").prop('selected',true);
          $("#dates_perso").attr("class","show");
        }
        // Modification automatique du formulaire : périodes
        if(autoperiode)
        {
          if( (typeof(groupe_type)!='undefined') && (groupe_type!='Besoins') )
          {
            // Rechercher automatiquement la meilleure période
            selectionner_periode_adaptee();
          }
        }
        // Afficher la zone de choix des périodes, des enseignants, des évaluations
        if(typeof(groupe_type)!='undefined')
        {
          $('#zone_profs').removeAttr('class');
          if(objet!='evaluation')
          {
            $('#zone_periodes').removeAttr('class');
          }
          else
          {
            charger_evaluations_prof();
          }
        }
        else
        {
          $('#zone_periodes , #zone_profs , #zone_evals').addClass("hide");
        }
        // Rechercher automatiquement la liste des profs
        if( (typeof(groupe_type)!='undefined') && (groupe_type!='Besoins') )
        {
          if( (user_profil!='professeur') || (action_prof=='retirer') )
          {
            charger_profs_groupe();
          }
        }
        else
        {
          afficher_prof_connecte();
        }
      }
    );

    // Rechercher automatiquement la meilleure période au chargement de la page (uniquement pour un élève, seul cas où la classe est préselectionnée)
    selectionner_periode_adaptee();

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Charger les selects f_eleve (pour le professeur et le directeur et les parents de plusieurs enfants) et f_matiere (pour le directeur) en ajax
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_matiere(groupe_id,matiere_id) // Uniquement pour un directeur
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_maj_select_matieres',
          data : 'f_groupe='+groupe_id+'&f_matiere='+matiere_id+'&f_multiple=0',
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_maj').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==true)
            {
              $('#f_matiere').html(responseJSON['value']).show();
              maj_eleve(groupe_id,groupe_type,eleves_ordre);
            }
            else
            {
              $('#ajax_maj').attr('class','alerte').html(responseJSON['value']);
            }
          }
        }
      );
    }

    function maj_eleve(groupe_id,groupe_type,eleves_ordre)
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_maj_select_eleves',
          data : 'f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type+'&f_eleves_ordre='+eleves_ordre+'&f_statut=1'+'&f_multiple='+is_multiple+'&f_selection=1',
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_maj').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(groupe_type=='Classes')
            {
              $("#bloc_ordre").hide();
            }
            else
            {
              $("#bloc_ordre").show();
            }
            if(responseJSON['statut']==true)
            {
              $('#ajax_maj').removeAttr('class').html("");
              $('#f_eleve').html(responseJSON['value']).parent().show();
              // Demande d'affichage du relevé pour un parent avec plusieurs enfants
              if(auto_select_eleve_id)
              {
                $('#f_eleve option[value='+auto_select_eleve_id+']').prop('selected',true);
                $('#bouton_valider').click();
                auto_voir_releve = false;
                auto_select_eleve_id = false;
              }
              else if( !is_multiple && ($('#f_eleve option').length==2) )
              {
                // Cas d'un seul élève retourné dans le regroupement (en particulier pour un parent de plusieurs enfants)
                $('#f_eleve option').eq(1).prop('selected',true);
              }
            }
            else
            {
              $('#ajax_maj').attr('class','alerte').html(responseJSON['value']);
            }
          }
        }
      );
    }

    $("#f_groupe").change
    (
      function charger_eleves()
      {
        // Pour un directeur, on met à jour f_matiere (on mémorise avant matiere_id) puis f_eleve
        // Pour un professeur ou un parent de plusieurs enfants, on met à jour f_eleve uniquement
        // Pour un élève ou un parent d'un seul enfant cette fonction n'est pas appelée puisque son groupe (masqué) ne peut être changé
        if(PROFIL_TYPE=='directeur')
        {
          matiere_id = $("#f_matiere").val();
          $("#f_matiere").html('<option value="">&nbsp;</option>').hide();
        }
        $("#f_eleve").html('<option value="">&nbsp;</option>').parent().hide();
        groupe_id = $("#f_groupe option:selected").val();
        if(groupe_id)
        {
          groupe_type  = $("#f_groupe option:selected").parent().attr('label');
          eleves_ordre = $("#f_eleves_ordre option:selected").val();
          $('#ajax_maj').attr('class','loader').html("En cours&hellip;");
          if(PROFIL_TYPE=='directeur')
          {
            maj_matiere(groupe_id,matiere_id);
          }
          else if( (PROFIL_TYPE=='professeur') || (PROFIL_TYPE=='parent') )
          {
            maj_eleve(groupe_id,groupe_type,eleves_ordre);
          }
        }
        else
        {
          $("#bloc_ordre").hide();
          $('#ajax_maj').removeAttr('class').html("");
        }
      }
    );

    $("#f_eleves_ordre").change
    (
      function()
      {
        groupe_id    = $("#f_groupe option:selected").val();
        groupe_type  = $("#f_groupe option:selected").parent().attr('label');
        eleves_ordre = $("#f_eleves_ordre option:selected").val();
        $("#f_eleve").html('<option value="">&nbsp;</option>').parent().hide();
        $('#ajax_maj').attr('class','loader').html("En cours&hellip;");
        maj_eleve(groupe_id,groupe_type,eleves_ordre);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Charger toutes les matières ou seulement les matières affectées (pour un prof)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var action_matiere = 'ajouter';
    $("#modifier_matiere").click
    (
      function()
      {
        $('button').prop('disabled',true);
        matiere_id = $("#f_matiere option:selected").val();
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page=_maj_select_matieres_prof',
            data : 'f_matiere='+matiere_id+'&f_action='+action_matiere+'&f_multiple=0',
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('button').prop('disabled',false);
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('button').prop('disabled',false);
              if(responseJSON['statut']==true)
              {
                action_matiere = (action_matiere=='ajouter') ? 'retirer' : 'ajouter' ;
                $('#modifier_matiere').attr('class',"form_"+action_matiere);
                $('#f_matiere').html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Choisir les items : mise en place du formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var choisir_compet = function()
    {
      $('#f_selection_items option:first').prop('selected',true);
      cocher_matieres_items( $('#f_compet_liste').val() );
      $.fancybox( {
        'href'           : '#zone_matieres_items' ,
        onStart          : function(){$('#zone_matieres_items').css("display","block");} ,
        onClosed         : function(){$('#zone_matieres_items').css("display","none");} ,
        'modal'          : true ,
        'centerOnScroll' : true
      } );
    };

    $('q.choisir_compet').click( choisir_compet );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour fermer le cadre des items choisis (annuler / retour)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#annuler_compet').click
    (
      function()
      {
        $.fancybox.close();
        return false;
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour valider le choix des items
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#valider_compet').click
    (
      function()
      {
        var liste = '';
        var nombre = 0;
        $("#zone_matieres_items input[type=checkbox]:checked").each
        (
          function()
          {
            liste += $(this).val()+'_';
            nombre++;
          }
        );
        var compet_liste  = liste.substring(0,liste.length-1);
        var compet_nombre = (nombre==0) ? 'aucun' : ( (nombre>1) ? nombre+' items' : nombre+' item' ) ;
        $('#f_compet_liste').val(compet_liste);
        $('#f_compet_nombre').val(compet_nombre);
        $('#annuler_compet').click();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Demande pour sélectionner d'une liste d'items mémorisés
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_selection_items').change
    (
      function()
      {
        cocher_matieres_items( $("#f_selection_items").val() );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour mémoriser un choix d'items
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_enregistrer_items').click
    (
      function()
      {
        memoriser_selection_matieres_items( $("#f_liste_items_nom").val() );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Soumettre le formulaire principal
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $("#form_select");

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          f_objet              : { required:true },
          f_matiere            : { required:function(){return objet=='matiere';} },
          f_compet_liste       : { required:function(){return objet=='selection';} },
          f_prof               : { required:function(){return objet=='professeur';} },
          'f_type[]'           : { required:function(){return objet!='multimatiere';} },
          'f_evaluation[]'     : { required:function(){return objet=='evaluation';} },
          f_individuel_format  : { required:true },
          f_etat_acquisition   : { required:false },
          f_moyenne_scores     : { required:false },
          f_pourcentage_acquis : { required:false },
          f_conversion_sur_20  : { required:false },
          f_synthese_format    : { required:true },
          f_tri_etat_mode      : { required:true },
          f_repeter_entete     : { required:false },
          f_groupe             : { required:true },
          'f_eleve[]'          : { required:true },
          f_eleves_ordre       : { required:true },
          f_periode            : { required:true },
          f_date_debut         : { required:function(){return $("#f_periode").val()==0;} , dateITA:true },
          f_date_fin           : { required:function(){return $("#f_periode").val()==0;} , dateITA:true },
          f_retroactif         : { required:true },
          f_only_etat          : { required:true },
          f_only_socle         : { required:false },
          f_reference          : { required:false },
          f_coef               : { required:false },
          f_socle              : { required:false },
          f_comm               : { required:false },
          f_lien               : { required:false },
          f_domaine            : { required:false },
          f_theme              : { required:false },
          f_orientation        : { required:true },
          f_couleur            : { required:true },
          f_fond               : { required:true },
          f_legende            : { required:true },
          f_marge_min          : { required:true },
          f_pages_nb           : { required:true },
          f_cases_auto         : { required:false },
          f_cases_nb           : { required:true },
          f_cases_larg         : { required:true },
          f_highlight_id       : { required:false }
        },
        messages :
        {
          f_objet              : { required:"objet manquant" },
          f_matiere            : { required:"matière manquante" },
          f_compet_liste       : { required:"item(s) manquant(s)" },
          f_prof               : { required:"enseignant manquant" },
          'f_type[]'           : { required:"type(s) manquant(s)" },
          'f_evaluation[]'     : { required:"évaluation(s) manquante(s)" },
          f_individuel_format  : { required:"choix manquant" },
          f_etat_acquisition   : { },
          f_moyenne_scores     : { },
          f_pourcentage_acquis : { },
          f_conversion_sur_20  : { },
          f_synthese_format    : { required:"choix manquant" },
          f_tri_etat_mode      : { required:"choix manquant" },
          f_repeter_entete     : { },
          f_groupe             : { required:"groupe manquant" },
          'f_eleve[]'          : { required:"élève(s) manquant(s)" },
          f_eleves_ordre       : { required:"ordre manquant" },
          f_periode            : { required:"période manquante" },
          f_date_debut         : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_date_fin           : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_retroactif         : { required:"choix manquant" },
          f_only_etat          : { required:"choix manquant" },
          f_only_socle         : { },
          f_reference          : { },
          f_coef               : { },
          f_socle              : { },
          f_comm               : { },
          f_lien               : { },
          f_domaine            : { },
          f_theme              : { },
          f_orientation        : { required:"orientation manquante" },
          f_couleur            : { required:"couleur manquante" },
          f_fond               : { required:"fond manquant" },
          f_legende            : { required:"légende manquante" },
          f_marge_min          : { required:"marge mini manquante" },
          f_pages_nb           : { required:"choix manquant" },
          f_cases_auto         : { },
          f_cases_nb           : { required:"nombre manquant" },
          f_cases_larg         : { required:"largeur manquante" },
          f_highlight_id       : { }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          if(element.attr("id")=='f_matiere') { element.next().after(error); }
          else if(element.is("select")) {element.after(error);}
          else if(element.attr("type")=="text") {element.next().after(error);}
          else if(element.attr("type")=="radio") {element.parent().next().next().after(error);}
          else if(element.attr("type")=="checkbox") {
            if(element.parent().parent().hasClass('select_multiple')) {element.parent().parent().next().after(error);}
            else {element.parent().next().next().after(error);}
          }
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
        // récupération d'éléments
        $('#f_matiere_nom').val( $("#f_matiere option:selected").text() );
        $('#f_groupe_nom' ).val( $("#f_groupe  option:selected").text() );
        $('#f_prof_texte' ).val( $("#f_prof    option:selected").text() );
        $('#f_groupe_type').val( groupe_type );
        $(this).ajaxSubmit(ajaxOptions);
        return false;
      }
    );

    // Fonction précédant l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi(formData, jqForm, options)
    {
      $('#ajax_msg').removeAttr('class').html("");
      if( ($('#f_type_individuel').is(':checked')) && (!$('#f_etat_acquisition').is(':checked')) && ($('#f_cases_nb option:selected').val()==0) )
      {
        $('#ajax_msg').attr('class','erreur').html("Choisir au moins une indication à faire figurer sur le relevé individuel !");
        return false;
      }
      var readytogo = validation.form();
      if(readytogo)
      {
        $('#bouton_valider').prop('disabled',true);
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        $('#bilan').html('');
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      $('#bouton_valider').prop('disabled',false);
      var message = (jqXHR.status!=500) ? afficher_json_message_erreur(jqXHR,textStatus) : 'Erreur 500&hellip; Mémoire insuffisante ? Sélectionner moins d\'élèves à la fois ou demander à votre hébergeur d\'augmenter la valeur "memory_limit".' ;
      $('#ajax_msg').attr('class','alerte').html(message);
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseJSON)
    {
      initialiser_compteur();
      $('#bouton_valider').prop('disabled',false);
      if(responseJSON['statut']==false)
      {
        $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
      }
      else if(responseJSON['direct']==true)
      {
        $('#ajax_msg').attr('class','valide').html("Résultat ci-dessous.");
        $('#bilan').html(responseJSON['bilan']);
      }
      else if(responseJSON['direct']==false)
      {
        $('#ajax_msg').removeAttr('class').html('');
        // Mis dans le div bilan et pas balancé directement dans le fancybox sinon la mise en forme des liens nécessite un peu plus de largeur que le fancybox ne recalcule pas (et $.fancybox.update(); ne change rien).
        // Malgré tout, pour Chrome par exemple, la largeur est mal calculée et provoque des retours à la ligne, d'où le minWidth ajouté.
        $('#bilan').html('<p class="noprint">Afin de préserver l\'environnement, n\'imprimer que si nécessaire !</p>'+responseJSON['bilan']);
        $.fancybox( { 'href':'#bilan' , onClosed:function(){$('#bilan').html("");} , 'centerOnScroll':true , 'minHeight':400 , 'minWidth':600 } );
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Forcer le report de notes vers un bulletin SACoche
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bilan').on
    (
      'click',
      '#bouton_report',
      function()
      {
        $('#form_report_bulletin button, #form_report_bulletin select').prop('disabled',true);
        $('#ajax_msg_report').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=reporter_notes'+'&f_periode_eleves='+$('#f_periode_eleves').val()+'&f_eleves_moyennes='+$('#f_eleves_moyennes').val()+'&f_rubrique='+$('#f_rubrique').val(),
            // data : $('#form_report_bulletin').serialize(), le select f_rubrique n'est curieusement pas envoyé...
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg_report').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              $('#form_report_bulletin button, #form_report_bulletin select').prop('disabled',false);
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('#form_report_bulletin button, #form_report_bulletin select').prop('disabled',false);
              if(responseJSON['statut']==true)
              {
                $('#ajax_msg_report').attr('class','valide').html(responseJSON['value']);
              }
              else
              {
                $('#ajax_msg_report').attr('class','alerte').html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Initialisation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Afficher un relevé au chargement
    if(auto_voir_releve)
    {
      // Parent avec plusieurs enfants : d'abord charger la liste des enfants de la classe
      if(auto_select_eleve_id)
      {
        groupe_id = $("#f_groupe option:selected").val();
        groupe_type = 'Classes';
        maj_eleve(groupe_id,groupe_type,eleves_ordre);
      }
      // Parent avec un seul enfant ou élève : on peut lancer le formulaire de suite
      else
      {
        $('#bouton_valider').click();
        auto_voir_releve = false;
      }
    }
    else if(PROFIL_TYPE=='eleve')
    {
      $('#bouton_valider').click();
    }

    // Récupéré après le chargement de la page car potentiellement lourd pour les directeurs et les PP (bloque l'affichage plusieurs secondes)
    if( (PROFIL_TYPE=='professeur') || (PROFIL_TYPE=='directeur') )
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_load_arborescence',
          data : 'f_objet=referentiels'+'&f_item_comm=0'+'&f_all_if_pp=1',
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
