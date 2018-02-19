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

    var nb_caracteres_max = 2000;

    // tri du tableau (avec jquery.tablesorter.js).
    $('#table_action').tablesorter({ headers:{0:{sorter:false},7:{sorter:'date_fr'},10:{sorter:false},11:{sorter:false}} });
    var tableau_tri = function(){ $('#table_action').trigger( 'sorton' , [ [[9,0],[1,0],[3,1],[2,0]] ] ); };
    var tableau_maj = function(){ $('#table_action').trigger( 'update' , [ true ] ); };
    tableau_tri();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le checkbox pour choisir ou non une date visible différente de la date du devoir
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_visible()
    {
      // Emploi de css() au lieu de show() hide() car sinon conflits constatés avec $("#step_creer").show() et $("#step_creer").hide() vers ligne 360.
      if($('#box_date').is(':checked'))
      {
        $('#box_date').next().css('display','inline-block').next().css('display','none');
      }
      else
      {
        $('#box_date').next().css('display','none').next().css('display','inline-block').val(input_visible).focus();
      }
    }

    function maj_autoeval()
    {
      // Emploi de css() au lieu de show() hide() car sinon conflits constatés avec $("#step_creer").show() et $("#step_creer").hide() vers ligne 360.
      if($('#box_autoeval').is(':checked'))
      {
        $('#f_date_autoeval').val('00/00/0000');
        $('#box_autoeval').next().css('display','inline-block').next().css('display','none');
      }
      else
      {
        $('#box_autoeval').next().css('display','none').next().css('display','inline-block');
        $('#f_date_autoeval').val(input_autoeval);
      }
    }

    function maj_dates()
    {
      if( $("#f_quoi option:selected").val() == 'completer')
      {
        var tab_infos = $('#f_devoir option:selected').text().split(' || ');
      }
      else
      {
        var tab_infos = new Array();
        tab_infos[0] = '';
        tab_infos[1] = input_visible;
      }
      if(tab_infos.length>1)
      {
        $('#f_date').val(tab_infos[0]);
        $('#f_date_visible').val(tab_infos[1]);
        if( $("#f_quoi option:selected").val() == 'completer')
        {
          $('#f_description').val(tab_infos[2]);
        }
        // Simuler un clic sur #box_date pour un appel de maj_visible() deconne (dans maj_visible() le test .is(':checked') ne renvoie pas ce qui est attendu) :
        /*
        if( ( (tab_infos[0]==tab_infos[1])&&(!$('#box_date').is(':checked')) ) || ( (tab_infos[0]!=tab_infos[1])&&($('#box_date').is(':checked')) ) )
        {
          $('#box_date').click();
        }
        */
        // Alors j'ai réécrit ici une partie de maj_visible() :
        // Emploi de css() au lieu de show() hide() car sinon conflits constatés avec $("#step_creer").show() et $("#step_creer").hide() vers ligne 360.
        if(tab_infos[0]==tab_infos[1])
        {
          $('#box_date').prop('checked',true).next().css('display','inline-block').next().css('display','none');
        }
        else
        {
          $('#box_date').prop('checked',false).next().css('display','none').next().css('display','inline-block');
        }
      }
    }

    $('#box_date').click
    (
      function()
      {
        maj_visible();
      }
    );

    $('#box_autoeval').click
    (
      function()
      {
        maj_autoeval();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du premier formulaire pour afficher le tableau avec la liste des demandes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Afficher / masquer des options du formulaire

    $('#f_periode').change
    (
      function()
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
    );

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire0 = $('#form_prechoix');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation0 = formulaire0.validate
    (
      {
        rules :
        {
          f_matiere : { required:false },
          f_groupe  : { required:false },
          f_prof    : { required:false }
        },
        messages :
        {
          f_matiere : { },
          f_groupe  : { },
          f_prof    : { }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          if(element.is("select")) {element.after(error);}
          else if(element.attr("type")=="text") {element.next().after(error);}
        }
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions0 =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg_prechoix",
      beforeSubmit : test_form_avant_envoi0,
      error : retour_form_erreur0,
      success : retour_form_valide0
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire0.submit
    (
      function()
      {
        $('#table_action tbody').html('');
        $('#tr_sans').html('<td class="nu"></td>');
        $("#zone_actions").hide(0);
        $('#ajax_msg_gestion').removeAttr('class').html("");
        // Mémoriser le nom de la matière + le type de groupe + le nom du groupe
        $('#f_matiere_nom').val( $("#f_matiere option:selected").text() );
        $("#f_groupe_id").val(   $("#f_groupe option:selected").val() );
        $("#f_groupe_nom").val(  $("#f_groupe option:selected").text() );
        $("#f_groupe_type").val( $("#f_groupe option:selected").parent().attr('label') );
        $("#f2_groupe_id").val( $("#f_groupe_id").val() );
        $("#f2_groupe_type").val( $("#f_groupe_type").val() );
        $(this).ajaxSubmit(ajaxOptions0);
        return false;
      }
    );

    // Fonction précédant l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi0(formData, jqForm, options)
    {
      $('#ajax_msg_prechoix').removeAttr('class').html("");
      var readytogo = validation0.form();
      if(readytogo)
      {
        $('#ajax_msg_prechoix').attr('class','loader').html("En cours&hellip;");
        $('#form_gestion').hide();
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur0(jqXHR, textStatus, errorThrown)
    {
      $('#ajax_msg_prechoix').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide0(responseJSON)
    {
      initialiser_compteur();
      if(responseJSON['statut']==false)
      {
        $('#ajax_msg_prechoix').attr('class','alerte').html(responseJSON['value']);
      }
      else
      {
        $('#ajax_msg_prechoix').attr('class','valide').html("Demande réalisée !");
        $('#export_fichier').attr('href',responseJSON['file']);
        $('#zone_messages').html(responseJSON['msg']);
        $('#table_action tbody').html(responseJSON['tr']);
        $('#tr_sans').html(responseJSON['td']);
        tableau_maj();
        var etat_disabled = ($("#f_groupe_id").val()>0) ? false : true ;
        $('#form_gestion').show();
        $("#f_qui option[value=groupe]").text($("#f_groupe_nom").val()).prop('disabled',etat_disabled);
        if(etat_disabled) { $("#f_qui option[value=select]").prop('selected',true); }
        maj_evaluation();
        if( $("#f_prof option:selected").val() != 0 )
        {
          $("#zone_actions").show(0);
        }
        else
        {
          $("#zone_actions").hide(0);
        }
      }
    }

    // Soumettre au chargement pour initialiser l'affichage, et au changement d'un select initial

    formulaire0.submit();

    $('#f_matiere , #f_groupe , #f_prof').change
    (
      function()
      {
        formulaire0.submit();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic pour demander le recalcul d'un score
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on
    (
      'click',
      'q.actualiser',
      function()
      {
        var obj_q      = $(this);
        var obj_td     = $(this).parent();
        var ids        = obj_td.parent().children('td:first').children('input').val();
        var score      = $(this).prev('i').html();
        var debut_date = $(this).attr('data-debut_date');
        score = (typeof(score)!=='undefined') ? parseInt(score,10) : -1 ;
        obj_q.removeAttr('class');
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+'actualiser_score'+'&ids='+ids+'&score='+score+'&f_debut_date='+debut_date,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+' Veuillez recommencer.'+'</label>' , {'centerOnScroll':true} );
              obj_q.addClass("actualiser");
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==true)
              {
                obj_td.replaceWith(responseJSON['value']);
              }
              else
              {
                $.fancybox( '<label class="alerte">'+responseJSON['value']+'</label>' , {'centerOnScroll':true} );
                obj_q.addClass("actualiser");
              }
            }
          }
        );
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic pour voir les messages des élèves
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#voir_messages').click
    (
      function()
      {
        $.fancybox( { 'href':'#zone_messages' , onStart:function(){$('#zone_messages').css("display","block");} , onClosed:function(){$('#zone_messages').css("display","none");} , 'centerOnScroll':true } );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Charger le select f_devoir en ajax
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_evaluation()
    {
      $("#f_devoir").html('<option value="">&nbsp;</option>');
      $('#ajax_maj1').attr('class','loader').html("En cours&hellip;");
      eval_type = $('#f_qui option:selected').val();
      groupe_id = $("#f_groupe_id").val();
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_maj_select_eval',
          data : 'f_objet=demande_eval_prof'+'&f_eval_type='+eval_type+'&f_groupe_id='+groupe_id,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_maj1').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==true)
            {
              $('#ajax_maj1').removeAttr('class').html("");
              $('#f_devoir').html(responseJSON['value']).show();
              maj_dates();
            }
            else
            {
              $('#ajax_maj1').attr('class','alerte').html(responseJSON['value']);
            }
          }
        }
      );
    }

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
// Éléments dynamiques du formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Récupérer les noms des items des checkbox cochés pour la description de l'évaluation
    $('#table_action').on
    (
      'click',
      'input[type=checkbox]',
      function()
      {
        // Récupérer les checkbox cochés
        var listing_refs = '';
        $('#table_action input[type=checkbox]:checked').each
        (
          function()
          {
            item = $(this).parent().next().next().text();
            ref  = ' ' + item.substring( item.indexOf('.')+1 , item.length-1 );
            if(listing_refs.indexOf(ref)==-1)
            {
              listing_refs += ref;
            }
          }
        );
        if(listing_refs.length)
        {
          $("#f_description").val('Demande'+listing_refs);
        }
      }
    );

    // Afficher / masquer les éléments suivants du formulaire suivant le choix du select "f_quoi"
    // Si "f_quoi" vaut "completer" alors charger le select "f_devoir" en ajax
    $('#f_quoi').change
    (
      function()
      {
        $('#bilan').hide();
        quoi = $("#f_quoi option:selected").val();
        if(quoi=='completer')                        {maj_evaluation();}
        if( (quoi=='creer') || (quoi=='completer') ) {$("#step_qui").show(0);}       else {$("#step_qui").hide(0);}
        if(quoi=='saisir')                           {$("#step_saisir").show(0);}    else {$("#step_saisir").hide(0);}
        if(quoi=='creer')                            {$("#step_creer").show(0);}     else {$("#step_creer").hide(0);}
        if(quoi=='completer')                        {$("#step_completer").show(0);} else {$("#step_completer").hide(0);}
        if( (quoi=='creer') || (quoi=='completer') ) {$("#step_suite").show(0);}     else {$("#step_suite").hide(0);}
        if( (quoi!='') && (quoi!='saisir') )         {$("#step_message").show(0);}   else {$("#step_message").hide(0);}
        if(quoi!='')                                 {$("#step_valider").show(0);}
      }
    );

    // Charger le select "f_devoir" en ajax si "f_qui" change et que "f_quoi" est à "completer"
    $('#f_qui').change
    (
      function()
      {
        $('#bilan').hide();
        if( $("#f_quoi option:selected").val() == 'completer')
        {
          maj_evaluation();
        }
      }
    );

    $('#f_quoi , #f_devoir').change
    (
      function()
      {
        maj_dates();
      }
    );

    // Indiquer le nombre de caractères restant autorisés dans le textarea
    $('#f_message').keyup
    (
      function()
      {
        afficher_textarea_reste( $(this) , nb_caracteres_max );
      }
    );

    // Choisir les professeurs associés à une évaluation

    $('#step_creer').on
    (
      'click',
      'q.choisir_prof',
      function()
      {
        selectionner_profs_option( $('#f_prof_liste').val() );
        // Afficher la zone
        $.fancybox( { 'href':'#zone_profs' , onStart:function(){$('#zone_profs').css("display","block");} , onClosed:function(){$('#zone_profs').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
        $(document).tooltip("destroy");infobulle(); // Sinon, bug avec l'infobulle contenu dans le fancybox qui ne disparait pas au clic...
      }
    );

    $('input[name=prof_check_all]').click
    (
      function()
      {
        var valeur = $(this).val();
        $('#zone_profs').find('select').find('option[value='+valeur+']').prop('selected',true);
        $('.prof_liste').find('span.select_img').attr('class','select_img droit_'+valeur);
      }
    );

    $('#zone_profs').on
    (
      'change',
      'select',
      function()
      {
        var val_option = $(this).find('option:selected').val();
        $(this).next('span').attr('class','select_img droit_'+val_option);
      }
    );

    $('#annuler_profs').click
    (
      function()
      {
        $.fancybox.close();
      }
    );

    $('#valider_profs').click
    (
      function()
      {
        var liste = '';
        var nombre = 0;
        $('#zone_profs').find('select').each
        (
          function()
          {
            var val_option = $(this).find('option:selected').val();
            if( (val_option!='x') && (val_option!='z') )
            {
              var tab_val = $(this).attr('id').split('_');
              var id_prof = tab_val[1];
              liste += val_option+id_prof+'_';
              nombre++;
            }
          }
        );
        liste  = (!nombre) ? '' : liste.substring(0,liste.length-1) ;
        nombre = (!nombre) ? 'non' : (nombre+1)+' profs' ;
        $('#f_prof_liste').val(liste);
        $('#f_prof_nombre').val(nombre);
        $('#annuler_profs').click();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du formulaire principal
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    // On sépare en 2 parties pour traiter les évaluations à la volée à part.
    $('#bouton_valider').click
    (
      function()
      {
        if($("#f_quoi").val()!='saisir')
        {
          formulaire.submit();
        }
        else
        {
          valider_envoi_saisie();
        }
      }
    );

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $('#form_gestion');

    // Ajout d'une méthode pour valider les dates de la forme jj/mm/aaaa (trouvé dans le zip du plugin, corrige en plus un bug avec Safari)
    // méthode dateITA déjà ajoutée

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          f_ids           : { required:true },
          f_quoi          : { required:true },
          f_qui           : { required:function(){quoi=$("#f_quoi").val(); return ((quoi=='creer')||(quoi=='completer'));} },
          f_date          : { required:function(){return $("#f_quoi").val()=='creer';} , dateITA:true },
          f_date_visible  : { required:function(){return (($("#f_quoi").val()=='creer')&&(!$('#box_date').is(':checked')));} , dateITA:true },
          f_date_autoeval : { required:function(){return (($("#f_quoi").val()=='creer')&&(!$('#box_autoeval').is(':checked')));} , dateITA:true },
          f_description   : { required:function(){quoi=$("#f_quoi").val(); return ((quoi=='creer')||(quoi=='completer'));} , maxlength:60 },
          f_prof_liste    : { required:false },
          f_devoir        : { required:function(){return $("#f_quoi").val()=='completer';} },
          f_suite         : { required:function(){quoi=$("#f_quoi").val(); return ((quoi=='creer')||(quoi=='completer'));} },
          f_message       : { required:false }
        },
        messages :
        {
          f_ids           : { required:"cocher au moins une demande" },
          f_quoi          : { required:"action manquante" },
          f_qui           : { required:"groupe manquant" },
          f_date          : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_date_visible  : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_date_autoeval : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_description   : { required:"nom manquant" , maxlength:"60 caractères maximum" },
          f_prof_liste    : { },
          f_devoir        : { required:"évaluation manquante" },
          f_suite         : { required:"suite manquante" },
          f_message       : { }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          if(element.is("select")) {element.after(error);}
          else if(element.attr("id")=='f_description') {element.after(error);}
          else if(element.attr("type")=="text") {element.next().after(error);}
          else if(element.attr("type")=="checkbox") {$('#ajax_msg_gestion').after(error);}
        }
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
      target : "#ajax_msg_gestion",
      beforeSerialize : action_form_avant_serialize,
      beforeSubmit : test_form_avant_envoi,
      error : retour_form_erreur,
      success : retour_form_valide
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire.submit
    (
      function()
      {
        // grouper les checkbox multiples => normalement pas besoin si name de la forme nom[], mais ça pose pb à jquery.validate.js d'avoir un id avec []
        // alors j'ai copié le tableau dans un champ hidden...
        var f_ids = new Array(); $("input[name=f_ids]:checked").each(function(){f_ids.push($(this).val());});
        $('#ids').val(f_ids);
        $(this).ajaxSubmit(ajaxOptions);
        return false;
      }
    );

    // Fonction précédent le traitement du formulaire (avec jquery.form.js)
    function action_form_avant_serialize(jqForm, options)
    {
      if($('#box_date').is(':checked'))
      {
        // Obligé rajouter le test à ce niveau car si la date a été changé depuis le calendrier, l'événement change() n'a pas été déclenché (et dans test_form_avant_envoi() c'est trop tard).
        $('#f_date_visible').val($('#f_date').val());
      }
    }

    // Fonction précédant l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi(formData, jqForm, options)
    {
      $('#ajax_msg_gestion').removeAttr('class').html("");
      var readytogo = validation.form();
      if(readytogo)
      {
        $('button').prop('disabled',true);
        $('#ajax_msg_gestion').attr('class','loader').html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      $('button').prop('disabled',false);
      $('#ajax_msg_gestion').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseJSON)
    {
      initialiser_compteur();
      $('button').prop('disabled',false);
      if(responseJSON['statut']==false)
      {
        $('#ajax_msg_gestion').attr('class','alerte').html(responseJSON['value']);
      }
      else
      {
        var qui   = $("#f_qui"  ).val();
        var quoi  = $("#f_quoi" ).val();
        var suite = $("#f_suite").val();
        if( ((quoi=='creer')&&(suite=='changer')) || ((quoi=='completer')&&(suite=='changer')) || (quoi=='changer_prof') )
        {
          // Changer le statut des demandes cochées
          $('#table_action input[type=checkbox]:checked').each
          (
            function()
            {
              this.checked = false;
              $(this).parent().parent().removeAttr('class').find('td').eq(9).html('évaluation en préparation');
            }
          );
          tableau_maj(); // sinon, un clic ultérieur pour retrier par statut ne fonctionne pas
        }
        else if(quoi=='changer_eleve')
        {
          // Changer le statut des demandes cochées
          $('#table_action input[type=checkbox]:checked').each
          (
            function()
            {
              this.checked = false;
              $(this).parent().parent().attr('class',"new").find('td').eq(9).html('demande non traitée');
            }
          );
          tableau_maj(); // sinon, un clic ultérieur pour retrier par statut ne fonctionne pas
        }
        else if( ((quoi=='creer')&&(suite=='retirer')) || ((quoi=='completer')&&(suite=='retirer')) || (quoi=='retirer') )
        {
          // Retirer les demandes cochées
          $('#table_action input[type=checkbox]:checked').each
          (
            function()
            {
              $(this).parent().parent().remove();
            }
          );
        }
        // lien vers le devoir
        if( (quoi=='creer') || (quoi=='completer') )
        {
          var section = (qui=='select') ? 'selection' : 'groupe' ;
          $('#bilan_lien').attr('href','./index.php?page=evaluation&section=gestion_'+section+'&devoir_id='+responseJSON['devoir_id']+'&groupe_type='+responseJSON['groupe_type']+'&groupe_id='+responseJSON['groupe_id']);
          $('#bilan').show();
        }
        $('#ajax_msg_gestion').attr('class','valide').html("Demande réalisée !");
      }
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Enregistrement d'une évaluation à la volée
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function valider_envoi_saisie()
    {
      var tab_ids = new Array(); $("input[name=f_ids]:checked").each(function(){tab_ids.push($(this).val());});
      var valeur = $('#step_saisir input[name=f_note]:checked').val();
      var devoir_id = $("#f_saisir_devoir").val();
      var groupe_id = $("#f_saisir_groupe").val();
      $('#ids').val(tab_ids);
      if(!tab_ids.length)
      {
        $('#ajax_msg_gestion').attr('class','erreur').html("Cocher au moins une demande !");
        return false;
      }
      else if(typeof(valeur)=='undefined')	// normalement impossible, sauf si par exemple on triche avec la barre d'outils Web Developer...
      {
        $('#ajax_msg_gestion').attr('class','erreur').html("Choisir une note !");
        return false;
      }
      else
      {
        $('button').prop('disabled',true);
        $('#ajax_msg_gestion').attr('class','loader').html("En cours&hellip;");
        enregistrer_saisie( tab_ids , valeur , devoir_id , groupe_id );
      }
    }

    function enregistrer_saisie( tab_ids , valeur , devoir_id , groupe_id )
    {
      var ids = tab_ids[0];
      tab_ids.shift();
      var tab = ids.split('x');
      var user_id = tab[1];
      var item_id = tab[2];
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=evaluation_ponctuelle',
          data : 'csrf='+CSRF+'&f_action=enregistrer_note'+'&f_item='+item_id+'&f_eleve='+user_id+'&f_note='+valeur+'&f_devoir='+devoir_id+'&f_groupe='+groupe_id+'&box_autodescription=1',
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('button').prop('disabled',false);
            $('#ajax_msg_gestion').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            return false;
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==true)
            {
              // On enregistre la note pour la demande suivante
              if(tab_ids.length)
              {
                enregistrer_saisie( tab_ids , valeur , responseJSON['devoir_id'] , responseJSON['groupe_id'] );
              }
              // ... ou on passe à la suppression des demandes
              else
              {
                supprimer_demandes( responseJSON['devoir_id'] , responseJSON['groupe_id'] );
              }
            }
            else
            {
              $('button').prop('disabled',false);
              $('#ajax_msg_gestion').attr('class','alerte').html(responseJSON['value']);
            }
          }
        }
      );
    }

    function supprimer_demandes( devoir_id , groupe_id )
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=retirer'+'&devoir_saisie='+devoir_id+'&'+'ids='+$('#ids').val(),
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('button').prop('disabled',false);
            $('#ajax_msg_gestion').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            $('button').prop('disabled',false);
            if(responseJSON['statut']==false)
            {
              $('#ajax_msg_gestion').attr('class','alerte').html(responseJSON['value']);
            }
            else
            {
              $('#table_action input[type=checkbox]:checked').each
              (
                function()
                {
                  $(this).parent().parent().remove();
                }
              );
              $('#ajax_msg_gestion').attr('class','valide').html("Demande réalisée !");
              $('#bilan_lien').attr('href','./index.php?page=evaluation&section=gestion_selection&devoir_id='+devoir_id+'&groupe_type='+'E'+'&groupe_id='+groupe_id);
              $('#bilan').show();
            }
          }
        }
      );
    }

  }
);
