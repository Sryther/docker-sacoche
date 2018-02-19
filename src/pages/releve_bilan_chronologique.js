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

    var objet        = $('#f_objet option:selected').val();
    var matiere_id   = 0;
    var groupe_id    = 0;
    var groupe_type  = $("#f_groupe option:selected").parent().attr('label'); // Il faut indiquer une valeur initiale au moins pour le profil élève
    var eleves_ordre = '';

    $("#f_eleve").hide();

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Afficher / masquer des éléments du formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#f_objet").change
    (
      function()
      {
        // on masque
        $('#zone_matieres , #zone_matiere ,#zone_synthese , #zone_selection').hide();
        // on récupère les infos
        objet = $('#f_objet option:selected').val();
        var tab_infos = objet.split('_');
        var zone_principale = tab_infos[0];
        var zone_secondaire = tab_infos[1];
        if(zone_principale)
        {
          $('#zone_'+zone_principale).show();
        }
        if(zone_secondaire=='synthese')
        {
          $('#zone_'+zone_secondaire).show();
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Thème personnalisé pour le graphique
    // @see   http://www.highcharts.com/ --> http://api.highcharts.com/highcharts
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    Highcharts.theme = {
       colors: ['#0563C7', '#ED642E', '#009B00', '#050112', '#CE63CB', '#FB0A0A', '#24CBE5', '#066C0D', '#8313BD', '#626262', '#D6D900', '#07CE2F', '#0004BD', '#583216', '#BD1363'],
       chart: {
          plotBorderWidth: 1
       },
       xAxis: {
          gridLineWidth: 1,
          lineColor: '#000',
          tickColor: '#000',
          labels: {
             style: {
                color: '#000',
                font: '11px Trebuchet MS, Verdana, sans-serif'
             }
          },
          title: {
             style: {
                color: '#333',
                fontWeight: 'bold',
                fontSize: '12px',
                fontFamily: 'Trebuchet MS, Verdana, sans-serif'

             }
          }
       },
       yAxis: {
          minorTickInterval: 'auto',
          lineColor: '#000',
          lineWidth: 1,
          tickWidth: 1,
          tickColor: '#000',
          labels: {
             style: {
                color: '#000',
                font: '11px Trebuchet MS, Verdana, sans-serif'
             }
          },
          title: {
             style: {
                color: '#333',
                fontWeight: 'bold',
                fontSize: '12px',
                fontFamily: 'Trebuchet MS, Verdana, sans-serif'
             }
          }
       },
       legend: {
          itemStyle: {
             font: '9pt Trebuchet MS, Verdana, sans-serif',
             color: '#139'

          },
          itemHoverStyle: {
             color: '#D41'
          },
          itemHiddenStyle: {
             color: 'gray'
          }
       },
       labels: {
          style: {
             color: '#99b'
          }
       }
    };

    // Apply the theme
    var highchartsOptions = Highcharts.setOptions(Highcharts.theme);

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Options de base pour le graphique : sont complétées ensuite avec les données personnalisées
    // @see   http://www.highcharts.com/ --> http://api.highcharts.com/highcharts
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    ChartOptions = {
      chart: {
        renderTo: 'div_graphique_chronologique',
        type: 'spline',
        spacingTop: 5
       },
      title: {
        style: { color: '#333' } ,
        text: null
      },
      legend: {
        layout: 'horizontal',
        align: 'center',
        verticalAlign: 'bottom',
        x: 0
      },
      xAxis: {
        type: 'datetime',
        labels: {
          formatter: function() {
            return Highcharts.dateFormat('%m/%Y', this.value);
          }
        }
      },
      yAxis: {
        labels: { enabled: false },
        min: 0,
        max: 100,
        title: { style: { color: '#333' } , text: '???' } // MAJ ensuite
      },
      tooltip: {
        formatter: function() {
          return this.series.name + '<br/>' + '<b>' + (this.y) + '</b>' + '<br/>' + Highcharts.dateFormat('%d/%m/%Y', this.x) + '<br/>' + this.key; // this.key récupère la valeur "name"...
        }
      },
      plotOptions: {
        series: {
          marker: {
            enabled: true
          }
        }
      },
      series: [] // MAJ ensuite
      ,
      credits: {
        enabled: false
      }
    };

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Enlever le message ajax et le résultat précédent au changement d'un select
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('select').change
    (
      function()
      {
        $('#ajax_msg').removeAttr('class').html("");
      }
    );

    $('#f_indicateur_MS , #f_indicateur_PA').click
    (
      function()
      {
        if( ($('#f_indicateur_MS').is(':checked')) || ($('#f_indicateur_PA').is(':checked')) )
        {
          $('label[for=f_conversion_sur_20]').show();
        }
        else
        {
          $('label[for=f_conversion_sur_20]').hide();
        }
      }
    );

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
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Changement de groupe
// -> desactiver les périodes prédéfinies en cas de groupe de besoin (prof uniquement)
// -> afficher le formulaire de périodes s'il est masqué
// ////////////////////////////////////////////////////////////////////////////////////////////////////

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
        // Afficher la zone de choix des périodes
        if(typeof(groupe_type)!='undefined')
        {
          $('#zone_periodes').removeAttr('class');
        }
        else
        {
          $('#zone_periodes').addClass("hide");
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Charger les selects f_eleve (pour le professeur et le directeur et les parents de plusieurs enfants)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_eleve(groupe_id,groupe_type,eleves_ordre)
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_maj_select_eleves',
          data : 'f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type+'&f_eleves_ordre='+eleves_ordre+'&f_statut=1'+'&f_multiple=0',
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
              $('#f_eleve').html(responseJSON['value']).show();
              if($('#f_eleve option').length==2)
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
      function()
      {
        // Pour un directeur, un professeur ou un parent de plusieurs enfants, on met à jour f_eleve
        // Pour un élève ou un parent d'un seul enfant cette fonction n'est pas appelée puisque son groupe (masqué) ne peut être changé
        $("#f_eleve").html('<option value="">&nbsp;</option>').hide();
        groupe_id = $("#f_groupe option:selected").val();
        if(groupe_id)
        {
          eleves_ordre = $("#f_eleves_ordre option:selected").val();
          groupe_type  = $("#f_groupe option:selected").parent().attr('label');
          $('#ajax_maj').attr('class','loader').html("En cours&hellip;");
          if(PROFIL_TYPE=='directeur')
          {
            maj_eleve(groupe_id,groupe_type,eleves_ordre);
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
        $("#f_eleve").html('<option value="">&nbsp;</option>').hide();
        $('#ajax_maj').attr('class','loader').html("En cours&hellip;");
        maj_eleve(groupe_id,groupe_type,eleves_ordre);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Charger toutes les matières ou seulement les matières affectées (pour un prof)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var modifier_action_matiere = 'ajouter';
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
            data : 'f_matiere='+matiere_id+'&f_action='+modifier_action_matiere+'&f_multiple=0',
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
                modifier_action_matiere = (modifier_action_matiere=='ajouter') ? 'retirer' : 'ajouter' ;
                $('#modifier_matiere').attr('class',"form_"+modifier_action_matiere);
                $('#f_matiere').html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

    var modifier_action_matieres = 'ajouter';
    $("#modifier_matieres").click
    (
      function()
      {
        $('button').prop('disabled',true);
        matiere_id = $("#f_matieres input:checked").val();
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page=_maj_select_matieres_prof',
            data : 'f_matiere='+matiere_id+'&f_action='+modifier_action_matieres+'&f_multiple=1',
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
                modifier_action_matieres = (modifier_action_matieres=='ajouter') ? 'retirer' : 'ajouter' ;
                $('#modifier_matieres').attr('class',"form_"+modifier_action_matieres);
                $('#f_matieres').html(responseJSON['value']);
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
          'f_matieres[]'       : { required:function(){return objet=='matieres';} },
          f_matiere            : { required:function(){return objet.substring(0,8)=='matiere_';} },
          f_mode_synthese      : { required:function(){return objet=='matiere_synthese';} },
          f_fusion_niveaux     : { required:false },
          f_compet_liste       : { required:function(){return objet=='selection';} },
          f_indicateur         : { required:true },
          f_conversion_sur_20  : { required:false },
          f_groupe             : { required:true },
          f_groupe             : { required:true },
          f_eleve              : { required:true },
          f_eleves_ordre       : { required:true },
          f_periode            : { required:true },
          f_date_debut         : { required:function(){return $("#f_periode").val()==0;} , dateITA:true },
          f_date_fin           : { required:function(){return $("#f_periode").val()==0;} , dateITA:true },
          f_retroactif         : { required:true },
          f_only_socle         : { required:false },
          f_echelle            : { required:true }
        },
        messages :
        {
          f_objet              : { required:"objet manquant" },
          'f_matieres[]'       : { required:"matière(s) manquante(s)" },
          f_matiere            : { required:"matière manquante" },
          f_mode_synthese      : { required:"choix manquant" },
          f_fusion_niveaux     : { },
          f_compet_liste       : { required:"item(s) manquant(s)" },
          f_indicateur         : { required:"choix manquant" },
          f_conversion_sur_20  : { },
          f_groupe             : { required:"groupe manquant" },
          f_groupe             : { required:"groupe manquant" },
          f_eleve              : { required:"élève manquant" },
          f_eleves_ordre       : { required:"ordre manquant" },
          f_periode            : { required:"période manquante" },
          f_date_debut         : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_date_fin           : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_retroactif         : { required:"choix manquant" },
          f_only_socle         : { },
          f_echelle            : { required:"échelle manquante" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          if(element.is("select")) {
            if(element.attr("id")=="f_matiere") {element.next().after(error);}
            else {element.after(error);}
          }
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
        $('#f_groupe_type').val( groupe_type );
        $(this).ajaxSubmit(ajaxOptions);
        return false;
      }
    );

    // Fonction précédant l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi(formData, jqForm, options)
    {
      if(bilan_affiche)
      {
        return true;
      }
      else
      {
        $('#ajax_msg').removeAttr('class').html("");
        var readytogo = validation.form();
        if(readytogo)
        {
          $('#form_select button').prop('disabled',true);
          $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        }
        return readytogo;
      }
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      var message = (jqXHR.status!=500) ? afficher_json_message_erreur(jqXHR,textStatus) : 'Erreur 500&hellip; Mémoire insuffisante ? Sélectionner une période plus restreinte ou demander à votre hébergeur d\'augmenter la valeur "memory_limit".' ;
      if(bilan_affiche)
      {
        $('#bilan button , #bilan select').prop('disabled',false);
        $('#div_graphique_chronologique').html('<label class="alerte">'+message+'</label>');
      }
      else
      {
        $('#form_select button').prop('disabled',false);
        $('#ajax_msg').attr('class','alerte').html(message);
      }
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseJSON)
    {
      initialiser_compteur();
      if(bilan_affiche)
      {
        $('#bilan button , #bilan select').prop('disabled',false);
      }
      else
      {
        $('#form_select button').prop('disabled',false);
      }
      if(responseJSON['statut']==false)
      {
        if(bilan_affiche)
        {
          $('#div_graphique_chronologique').html('<label class="alerte">'+responseJSON['value']+'</label>');
        }
        else
        {
          $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
        }
      }
      else
      {
        var titre  = responseJSON['titre'];
        var script = responseJSON['script'];
        if(bilan_affiche)
        {
          $('#go_selection_eleve option[value='+memo_eleve+']').prop('selected',true);
          masquer_element_navigation_choix_eleve();
          eval(script);
        }
        else
        {
          if( (PROFIL_TYPE=='professeur') || (PROFIL_TYPE=='directeur') )
          {
            memo_eleve = $("#f_eleve option:selected").val();
            $('#go_selection_eleve').html( $('#f_eleve').html().substring(26) );
            $("#go_selection_eleve option[value="+memo_eleve+"]").prop('selected',true);
            memo_eleve_first = $('#go_selection_eleve option:first').val();
            memo_eleve_last  = $('#go_selection_eleve option:last').val();
            masquer_element_navigation_choix_eleve();
          }
          $('#ajax_msg').removeAttr('class').html('');
          $('#form_select , #zone_preliminaire').hide();
          $('#report_titre').html(titre);
          $('#bilan , #div_graphique_chronologique').show();
          eval(script);
          bilan_affiche = true;
        }
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Navigation d'un élève à un autre
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var bilan_affiche    = false;
    var memo_eleve       = 0;
    var memo_eleve_first = 0;
    var memo_eleve_last  = 0;

    function charger_nouvel_eleve(eleve_id)
    {
      $("#f_eleve option[value="+eleve_id+"]").prop('selected',true);
      memo_eleve = eleve_id;
      $('#bilan button , #bilan select').prop('disabled',true);
      $('#div_graphique_chronologique').html('<label class="loader">En cours&hellip;</label>');
      formulaire.submit();
    }

    function masquer_element_navigation_choix_eleve()
    {
      $('#bilan button').css('visibility','visible');
      if(memo_eleve==memo_eleve_first)
      {
        $('#go_premier_eleve , #go_precedent_eleve').css('visibility','hidden');
      }
      if(memo_eleve==memo_eleve_last)
      {
        $('#go_dernier_eleve , #go_suivant_eleve').css('visibility','hidden');
      }
    }

    $('#go_premier_eleve').click
    (
      function()
      {
        var eleve_id = $('#go_selection_eleve option:first').val();
        charger_nouvel_eleve(eleve_id);
      }
    );

    $('#go_dernier_eleve').click
    (
      function()
      {
        var eleve_id = $('#go_selection_eleve option:last').val();
        charger_nouvel_eleve(eleve_id);
      }
    );

    $('#go_precedent_eleve').click
    (
      function()
      {
        if( $('#go_selection_eleve option:selected').prev().length )
        {
          var eleve_id = $('#go_selection_eleve option:selected').prev().val();
          charger_nouvel_eleve(eleve_id);
        }
      }
    );

    $('#go_suivant_eleve').click
    (
      function()
      {
        if( $('#go_selection_eleve option:selected').next().length )
        {
          var eleve_id = $('#go_selection_eleve option:selected').next().val();
          charger_nouvel_eleve(eleve_id);
        }
      }
    );

    $('#go_selection_eleve').change
    (
      function()
      {
        var eleve_id = $('#go_selection_eleve option:selected').val();
        charger_nouvel_eleve(eleve_id);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour fermer la zone bilan
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#fermer_zone_bilan').click
    (
      function()
      {
        $('#bilan , #div_graphique_chronologique').hide();
        $('#form_select , #zone_preliminaire').show();
        bilan_affiche = false;
        return false;
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Initialisation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

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
