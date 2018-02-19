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

    var mode = false;

    var memo_nombre = 1;

    // tri du tableau (avec jquery.tablesorter.js).
    $('#table_action').tablesorter({ headers:{3:{sorter:false}} });
    var tableau_tri = function(){ $('#table_action').trigger( 'sorton' , [ [[0,0],[1,0]] ] ); };
    var tableau_maj = function(){ $('#table_action').trigger( 'update' , [ true ] ); };
    tableau_tri();

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Mettre à jour la liste des professeurs associés à une classe
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_f_prof( groupe_id , select_numero , prof_id , force_select_prof )
    {
      $('#f_prof_'+select_numero).html('<option></option>');
      $('#ajax_msg_gestion').removeAttr('class').html("");
      if( groupe_id )
      {
        $('#bouton_valider').prop('disabled',true);
        $('#ajax_msg_gestion').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page=_maj_select_livret',
            data : 'f_select=profs_classe'+'&f_groupe_id='+groupe_id,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg_gestion').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('#bouton_valider').prop('disabled',false);
              if(responseJSON['statut']==true)
              {
                $('#ajax_msg_gestion').removeAttr('class').html("");
                eval( responseJSON['script'] );
                var select_prof_copie = select_prof;
                var options = '<option value="">&nbsp;</option>';
                var nb_meilleure_suggestion = tab_meilleure_suggestion.length;
                if(nb_meilleure_suggestion)
                {
                  options += '<optgroup label="Meilleures suggestions">';
                  for(i in tab_meilleure_suggestion)
                  {
                    id = tab_meilleure_suggestion[i];
                    new_option = '<option value="'+id+'">'+tab_prof[id]+'</option>';
                    options += new_option;
                    select_prof_copie = select_prof_copie.replace(new_option,'');
                    select_prof_copie = select_prof_copie.replace('<option value="'+id+'">'+tab_prof[id]+'</option>','');
                  }
                  options += '</optgroup>';
                }
                var nb_profs = tab_prof.length;
                if( nb_profs > nb_meilleure_suggestion )
                {
                  options += '<optgroup label="Personnels restants">';
                  options += select_prof_copie;
                  options += '</optgroup>';
                }
                if( prof_id && force_select_prof )
                {
                  options = options.replace('value="'+prof_id+'"','value="'+prof_id+'" selected');
                }
                else if ( nb_meilleure_suggestion == 1 )
                {
                  prof_id = tab_meilleure_suggestion[0];
                  options = options.replace('value="'+prof_id+'"','value="'+prof_id+'" selected');
                }
                $('#f_prof_'+select_numero).html(options);
              }
              else
              {
                $('#ajax_msg_gestion').attr('class','alerte').html(responseJSON['value']);
              }
            }
          }
        );
      }
    }

    $("#f_groupe").change
    (
      function()
      {
        var groupe_id = $(this).val();
        for( var select_numero=1 ; select_numero<=memo_nombre ; select_numero++ )
        {
          var prof_id = $('#f_prof_'+select_numero+' option:selected').val();
          maj_f_prof( groupe_id , select_numero , prof_id , true /*force_select_prof*/ );
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Mettre à jour la liste des classes associées à un moment
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_f_groupe( page_ref , groupe_id , tab_join , force_select_prof )
    {
      memo_nombre = Math.max( tab_join.length , 1 );
      $('#f_nombre option[value="'+memo_nombre+'"]').prop('selected',true);
      $('#f_groupe').html('<option></option>');
      for( var i=1 ; i<=15 ; i++ )
      {
        $('#f_prof_'+i).html('<option></option>');
        if(i<=memo_nombre)
        {
          $('#join_'+i).show(0);
        }
        else
        {
          $('#join_'+i).hide(0);
        }
      }
      $('#ajax_msg_gestion').removeAttr('class').html("");
      if( page_ref )
      {
        $('#bouton_valider').prop('disabled',true);
        $('#ajax_msg_gestion').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page=_maj_select_livret',
            data : 'f_select=groupes'+'&f_page_ref='+page_ref+'&f_groupe_id='+groupe_id+'&only_groupes_id='+only_groupes_id,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg_gestion').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('#bouton_valider').prop('disabled',false);
              if(responseJSON['statut']==true)
              {
                $('#ajax_msg_gestion').removeAttr('class').html("");
                $('#f_groupe').html(responseJSON['value']);
                groupe_id = $('#f_groupe option:selected').val(); // ce peut être le groupe_id transmis ou la valeur de l'option unique du select
                if(groupe_id)
                {
                  if(tab_join.length)
                  {
                    var select_numero = 0;
                    for(var i in tab_join)
                    {
                      select_numero++;
                      var tab_id = tab_join[i].split('_');
                      maj_f_prof( groupe_id , select_numero , tab_join[i] /*prof_id*/ , force_select_prof );
                    }
                  }
                }
              }
              else
              {
                $('#ajax_msg_gestion').attr('class','alerte').html(responseJSON['value']);
              }
            }
          }
        );
      }
    }

    $("#f_page").change
    (
      function()
      {
        var page_ref  = $(this).val();
        var groupe_id = $('#f_groupe option:selected').val();
        var tab_join  = new Array();
        for( var i=1 ; i<=memo_nombre ; i++ )
        {
          var prof_id = $('#f_prof_'+i+' option:selected').val();
          tab_join[i-1] = prof_id;
        }
        maj_f_groupe( page_ref , groupe_id , tab_join , false /*force_select_prof*/ );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Mettre à jour le nombre de profs
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#f_nombre").change
    (
      function()
      {
        var nombre = $(this).val();
        var page_ref    = $('#f_page option:selected').val();
        var groupe_id   = $('#f_groupe option:selected').val();
        var tab_join  = new Array();
        for( var i=1 ; i<=nombre ; i++ )
        {
          var prof_id = $('#f_prof_'+i+' option:selected').val();
          tab_join[i-1] = prof_id;
        }
        maj_f_groupe( page_ref , groupe_id , tab_join , true /*force_select_prof*/ );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Fonctions utilisées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function afficher_form_gestion( mode , id , usage , page_ref , groupe_id , join_ids , delete_txt )
    {
      $('#f_action').val(mode);
      $('#f_id').val(id);
      $('#f_usage').val(usage);
      if( (mode=='ajouter') || (mode=='dupliquer') || !usage )
        {$('#alerte_used').hide();}
      else                 
        {$('#alerte_used').show();}
      // Ci-dessous, les guillemets autour des valeurs transmises évitent une erreur en cas de valeur vide.
      $('#f_page option[value="'+page_ref+'"]').prop('selected',true);
      maj_f_groupe( page_ref , groupe_id , join_ids.toString().split(' ') , true /*force_select_prof*/ );
      // pour finir
      $('#gestion_titre_action').html( mode[0].toUpperCase() + mode.substring(1) );
      if(mode!='supprimer')
      {
        $('#gestion_edit').show(0);
        $('#gestion_delete').hide(0);
      }
      else
      {
        $('#gestion_delete_identite').html( delete_txt );
        $('#gestion_edit').hide(0);
        $('#gestion_delete').show(0);
      }
      $('#ajax_msg_gestion').removeAttr('class').html("");
      $('#form_gestion label[generated=true]').removeAttr('class').html("");
      $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
    }

    /**
     * Ajouter un parcours : mise en place du formulaire
     * @return void
     */
    var ajouter = function()
    {
      mode = $(this).attr('class');
      // Afficher le formulaire
      afficher_form_gestion( mode , 0 /*id*/ , 0 /*usage*/ , '' /*page_ref*/ , 0 /*groupe_id*/ , '' /*join_ids*/ , '' /*delete_txt*/ );
    };

    /**
     * Modifier / Dupliquer / Supprimer un parcours : mise en place du formulaire
     * @return void
     */
    var modifier_dupliquer_supprimer = function()
    {
      mode = $(this).attr('class');
      var objet_tr    = $(this).parent().parent();
      var objet_tds   = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var id         = (mode!='dupliquer') ? objet_tr.attr('id').substring(3) : '' ;
      var usage      = objet_tr.data('used');
      var page_ref   = objet_tds.eq(0).data('id');
      var groupe_id  = objet_tds.eq(1).data('id');
      var join_ids   = objet_tds.eq(2).data('id');
      var delete_txt = (mode!='supprimer') ? '' : objet_tds.eq(1).html() + ' || ' + objet_tds.eq(2).html() ;
      // Afficher le formulaire
      afficher_form_gestion( mode , id , usage , page_ref , groupe_id , join_ids , unescapeHtml(delete_txt) );
    };

    /**
     * Annuler une action
     * @return void
     */
    var annuler = function()
    {
      $.fancybox.close();
      mode = false;
    };

    /**
     * Intercepter la touche entrée ou escape pour valider ou annuler les modifications
     * @return void
     */
    function intercepter(e)
    {
      if(mode)
      {
        if(e.which==13)  // touche entrée
        {
          $('#bouton_valider').click();
        }
        else if(e.which==27)  // touche escape
        {
          $('#bouton_annuler').click();
        }
      }
    }

    var prompt_etapes = {
      etape_1: {
        title   : 'Demande de confirmation',
        html    : "Attention : les saisies associées à ce dispositif seront perdues !<br />Souhaitez-vous vraiment supprimer ce parcours ?",
        buttons : {
          "Non, c'est une erreur !" : false ,
          "Oui, je confirme !" : true
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            formulaire.submit();
          }
          else {
            $('#bouton_annuler').click();
          }
        }
      }
    };

    var soumettre_formulaire = function()
    {
      // On demande confirmation pour la suppression d'un dispositif utilisé
      if( ($('#f_action').val()=='supprimer') && ($('#f_usage').val()>0) )
      {
        $.prompt(prompt_etapes);
      }
      else
      {
        formulaire.submit();
      }
      return false;
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Appel des fonctions en fonction des événements
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on( 'click' , 'q.ajouter'       , ajouter );
    $('#table_action').on( 'click' , 'q.modifier'      , modifier_dupliquer_supprimer );
    $('#table_action').on( 'click' , 'q.dupliquer'     , modifier_dupliquer_supprimer );
    $('#table_action').on( 'click' , 'q.supprimer'     , modifier_dupliquer_supprimer );

    $('#form_gestion').on( 'click' , '#bouton_annuler' , annuler );
    $('#form_gestion').on( 'click' , '#bouton_valider' , soumettre_formulaire );
    $('#form_gestion').on( 'keyup' , 'input'           , function(e){intercepter(e);} );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $('#form_gestion');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          f_page    : { required:true },
          f_groupe  : { required:true },
          f_prof_1  : { required:true },
          f_prof_2  : { required:function(){return memo_nombre>=2;} },
          f_prof_3  : { required:function(){return memo_nombre>=3;} },
          f_prof_4  : { required:function(){return memo_nombre>=4;} },
          f_prof_5  : { required:function(){return memo_nombre>=5;} },
          f_prof_6  : { required:function(){return memo_nombre>=6;} },
          f_prof_7  : { required:function(){return memo_nombre>=7;} },
          f_prof_8  : { required:function(){return memo_nombre>=8;} },
          f_prof_9  : { required:function(){return memo_nombre>=9;} },
          f_prof_10 : { required:function(){return memo_nombre>=10;} },
          f_prof_11 : { required:function(){return memo_nombre>=11;} },
          f_prof_12 : { required:function(){return memo_nombre>=12;} },
          f_prof_13 : { required:function(){return memo_nombre>=13;} },
          f_prof_14 : { required:function(){return memo_nombre>=14;} },
          f_prof_15 : { required:function(){return memo_nombre>=15;} }
        },
        messages :
        {
          f_page    : { required:"moment manquant" },
          f_groupe  : { required:"classe manquante" },
          f_prof_1  : { required:"professeur manquant" },
          f_prof_2  : { required:"professeur manquant" },
          f_prof_3  : { required:"professeur manquant" },
          f_prof_4  : { required:"professeur manquant" },
          f_prof_5  : { required:"professeur manquant" },
          f_prof_6  : { required:"professeur manquant" },
          f_prof_7  : { required:"professeur manquant" },
          f_prof_8  : { required:"professeur manquant" },
          f_prof_9  : { required:"professeur manquant" },
          f_prof_10 : { required:"professeur manquant" },
          f_prof_11 : { required:"professeur manquant" },
          f_prof_12 : { required:"professeur manquant" },
          f_prof_13 : { required:"professeur manquant" },
          f_prof_14 : { required:"professeur manquant" },
          f_prof_15 : { required:"professeur manquant" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element) { element.after(error); }
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
        if (!please_wait)
        {
          $(this).ajaxSubmit(ajaxOptions);
          return false;
        }
        else
        {
          return false;
        }
      }
    );

    // Fonction précédant l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi(formData, jqForm, options)
    {
      $('#ajax_msg_gestion').removeAttr('class').html("");
      var readytogo = validation.form();
      if(readytogo)
      {
        please_wait = true;
        $('#form_gestion button').prop('disabled',true);
        $('#ajax_msg_gestion').attr('class','loader').html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      please_wait = false;
      $('#form_gestion button').prop('disabled',false);
      $('#ajax_msg_gestion').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseJSON)
    {
      initialiser_compteur();
      please_wait = false;
      $('#form_gestion button').prop('disabled',false);
      if(responseJSON['statut']==false)
      {
        $('#ajax_msg_gestion').attr('class','alerte').html(responseJSON['value']);
      }
      else
      {
        $('#ajax_msg_gestion').attr('class','valide').html("Demande réalisée !");
        action = $('#f_action').val();
        switch (action)
        {
          case 'ajouter':
          case 'modifier':
          case 'dupliquer':
            var page_moment = $('#f_page option:selected').text();
            var groupe_nom  = $('#f_groupe option:selected').text();
            var tab_prof_nom = new Array();
            for( var i=1 ; i<=memo_nombre ; i++ )
            {
              var prof_nom = $('#f_prof_'+i+' option:selected').text();
              tab_prof_nom[i-1] = prof_nom;
            }
            var prof_nom = tab_prof_nom.join('<br />');
            responseJSON['value'] = responseJSON['value']
                                    .replace('{{PAGE_MOMENT}}','<i>'+tab_page_ordre[page_moment]+'</i>'+page_moment)
                                    .replace('{{GROUPE_NOM}}',groupe_nom)
                                    .replace('{{PROF_NOM}}',prof_nom);
            if(action=='modifier')
            {
              $('#id_'+$('#f_id').val()).addClass("new").html(responseJSON['value']);
            }
            else
            {
              if(action=='ajouter')
              {
                $('#table_action tbody tr.vide').remove(); // En cas de tableau avec une ligne vide pour la conformité XHTML
              }
              $('#table_action tbody').append(responseJSON['value']);
            }
            break;
          case 'supprimer':
            $('#id_'+$('#f_id').val()).remove();
            break;
        }
        tableau_maj();
        $.fancybox.close();
        mode = false;
      }
    }

  }
);
