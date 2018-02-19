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

    $('select').change
    (
      function()
      {
        $('#ajax_msg').removeAttr('class').html("");
      }
    );

    var choix = '';
    var requis = '';

    $('#f_type').change
    (
      function()
      {
        choix = $(this).val();
        if( (choix=='listing_eleves') || (choix=='devoirs_commentaires') || (choix.substring(0,6)=='infos_') || (choix=='socle2016_gepi') ) {requis='groupe';  $('#div_groupe' ).slideDown();} else {$('#div_groupe' ).slideUp();}
        if( (choix=='listing_matiere') || (choix=='item_matiere_usage') || (choix=='arbre_matiere') )                                       {requis='matiere'; $('#div_matiere').slideDown();} else {$('#div_matiere').slideUp();}
        if( (choix=='jointure_socle2016_matiere') || (choix=='socle2016_gepi') )                                                            {requis='cycle';   $('#div_cycle'  ).slideDown();} else {$('#div_cycle'  ).slideUp();}
        if(choix=='socle2016_gepi')                                                                                                         {                  $('#div_sconet' ).slideDown();} else {$('#div_sconet' ).slideUp();}
        if(choix=='devoirs_commentaires')                                                                                                   {                  $('#div_periode').slideDown();} else {$('#div_periode').slideUp();}
        if(choix=='')                                                                                                                       {requis='';        $('#p_submit'   ).hide(0);    } else {$('#p_submit'   ).show(0);  }
        $('#bilan').html("");
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

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Changement de groupe
    // -> desactiver les périodes prédéfinies en cas de groupe de besoin (prof uniquement)
    // -> choisir automatiquement la meilleure période si un changement manuel de période n'a jamais été effectué
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function selectionner_periode_adaptee()
    {
      if(choix=='devoirs_commentaires')
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
      }
    );

    // Rechercher automatiquement la meilleure période au chargement de la page (uniquement pour un élève, seul cas où la classe est préselectionnée)
    selectionner_periode_adaptee();

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Soumettre le formulaire principal
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $("#form_export");

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          f_type       : { required:true },
          f_groupe     : { required:function(){return (requis=='groupe') || (choix=='socle2016_gepi');} },
          f_matiere    : { required:function(){return requis=='matiere';} },
          f_cycle      : { required:function(){return requis=='cycle';} },
          f_periode    : { required:function(){return choix=='devoirs_commentaires';} },
          f_date_debut : { required:function(){return choix=='devoirs_commentaires' && $("#f_periode").val()==0;} , dateITA:true },
          f_date_fin   : { required:function(){return choix=='devoirs_commentaires' && $("#f_periode").val()==0;} , dateITA:true }
        },
        messages :
        {
          f_type       : { required:"type manquant" },
          f_groupe     : { required:"regroupement manquant" },
          f_matiere    : { required:"matière manquante" },
          f_cycle      : { required:"cycle manquant" },
          f_periode    : { required:"période manquante" },
          f_date_debut : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_date_fin   : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element){
          if(element.attr("type")=="text") {element.next().after(error);}
          else {element.after(error);}
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
        // récupération du type et du nom du groupe
        var groupe_val = $("#f_groupe option:selected").val();
        if(groupe_val)
        {
          // Pour un directeur ou un administrateur, groupe_val est de la forme d3 / n2 / c51 / g44
          if(isNaN(parseInt(groupe_val,10)))
          {
            groupe_type = groupe_val.substring(0,1);
            groupe_id   = groupe_val.substring(1);
          }
          // Pour un professeur, groupe_val est un entier, et il faut récupérer la 1ère lettre du label parent
          else
          {
            groupe_type = $("#f_groupe option:selected").parent().attr('label').substring(0,1).toLowerCase();
            groupe_id   = groupe_val;
          }
          groupe_nom = $("#f_groupe option:selected").text();
          $('#f_groupe_type').val( groupe_type );
          $('#f_groupe_nom').val( groupe_nom );
          $('#f_groupe_id').val( groupe_id );
        }
        // récupération du nom de la matière
        var matiere_val = $("#f_matiere").val();
        if(matiere_val)
        {
          var nom = $("#f_matiere option:selected").text();
          $('#f_matiere_nom').val( nom );
        }
        // récupération du nom du cycle
        var cycle_val = $("#f_cycle").val();
        if(cycle_val)
        {
          var nom = $("#f_cycle option:selected").text();
          $('#f_cycle_nom').val( nom );
        }
        // récupération du nom de la période
        var periode_val = $("#f_periode").val();
        if(periode_val)
        {
          var nom = $("#f_periode option:selected").text();
          $('#f_periode_nom').val( nom );
        }
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
        $("#bouton_exporter").prop('disabled',true);
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        $('#bilan').html('');
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      $("#bouton_exporter").prop('disabled',false);
      $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseJSON)
    {
      initialiser_compteur();
      $("#bouton_exporter").prop('disabled',false);
      if(responseJSON['statut']==false)
      {
        $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
      }
      else
      {
        $('#ajax_msg').removeAttr('class').html('');
        $.fancybox( responseJSON['value'] , {'centerOnScroll':true} );
      }
    }

  }
);
