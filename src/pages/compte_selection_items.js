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

    // tri du tableau (avec jquery.tablesorter.js).
    $('#table_action').tablesorter({ headers:{1:{sorter:false},2:{sorter:false},3:{sorter:false}} });
    var tableau_tri = function(){ $('#table_action').trigger( 'sorton' , [ [[0,0]] ] ); };
    var tableau_maj = function(){ $('#table_action').trigger( 'update' , [ true ] ); };
    tableau_tri();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Fonctions utilisées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function afficher_form_gestion( mode , selection_id , nom , compet_nombre , compet_liste , prof_nombre , prof_liste , proprio_id )
    {
      // Éviter, en cas de duplication d'un regroupement dont on n'est pas le propriétaire, de se retrouver avec des complications
      // (droit du propriétaire d'origine ? regroupements en exemplaires multiples pour les autres ?)
      if( (mode=='dupliquer') && (user_id!=proprio_id) )
      {
        prof_nombre = 'non';
        prof_liste = '';
      }
      // Choix des collègues à masquer en cas de modification d'un regroupement dont on n'est pas le propriétaire
      // (ingérable sinon : on apparait comme propriétaire, le vrai propriétaire n'apparait pas comme tel...)
      if( (mode=='modifier') && (user_id!=proprio_id) )
      {
        $('#choisir_prof').hide(0);
        $('#choisir_prof_non').show(0);
      }
      else
      {
        $('#choisir_prof').show(0);
        $('#choisir_prof_non').hide(0);
      }
      $('#f_action').val(mode);
      $('#f_id').val(selection_id);
      $('#f_nom').val(nom);
      $('#f_compet_nombre').val(compet_nombre);
      $('#f_compet_liste').val(compet_liste);
      $('#f_prof_nombre').val(prof_nombre);
      $('#f_prof_liste').val(prof_liste);
      // pour finir
      $('#form_gestion h2').html(mode[0].toUpperCase() + mode.substring(1) + " un regroupements d'items");
      if(mode!='supprimer')
      {
        $('#gestion_edit').show(0);
        $('#gestion_delete').hide(0);
      }
      else
      {
        $('#gestion_delete_identite').html(nom);
        $('#gestion_edit').hide(0);
        $('#gestion_delete').show(0);
      }
      $('#ajax_msg_gestion').removeAttr('class').html("");
      $('#form_gestion label[generated=true]').removeAttr('class').html("");
      $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':700 , 'centerOnScroll':true } );
      if( (mode=='ajouter') || (mode=='dupliquer') ) { $('#f_nom').focus(); }
    }

    /**
     * Ajouter une sélection d'items : mise en place du formulaire
     * @return void
     */
    var ajouter = function()
    {
      mode = $(this).attr('class');
      // Afficher le formulaire
      afficher_form_gestion( mode , '' /*selection_id*/ , '' /*nom*/ , 'aucun' /*compet_nombre*/ , '' /*compet_liste*/ , 'non' /*prof_nombre*/ , '' /*prof_liste*/ , user_id /*proprio_id*/ );
    };

    /**
     * Modifier | Dupliquer une sélection d'items : mise en place du formulaire
     * @return void
     */
    var modifier_dupliquer = function()
    {
      mode = $(this).attr('class');
      var objet_tr      = $(this).parent().parent();
      var objet_tds     = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var selection_id  = objet_tr.attr('id').substring(3); // "id_" + ref
      var nom           = objet_tds.eq(0).html();
      var compet_nombre = objet_tds.eq(1).html();
      var prof_nombre   = objet_tds.eq(2).text().trim();
      var proprio_id    = objet_tds.eq(2).attr('id').substring(8); // "proprio_" + ref
      // liste des profs et des items
      var prof_liste    = tab_profs[selection_id];
      var compet_liste  = tab_items[selection_id];
      // Afficher le formulaire
      afficher_form_gestion( mode , selection_id , unescapeHtml(nom) , compet_nombre , compet_liste , prof_nombre , prof_liste , proprio_id );
    };

    /**
     * Supprimer une sélection d'items : mise en place du formulaire
     * @return void
     */
    var supprimer = function()
    {
      mode = $(this).attr('class');
      var objet_tr      = $(this).parent().parent();
      var objet_tds     = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var selection_id  = objet_tr.attr('id').substring(3);
      var nom           = objet_tds.eq(0).html();
      // Afficher le formulaire
      afficher_form_gestion( mode , selection_id , unescapeHtml(nom) , '' /*compet_nombre*/ , '' /*compet_liste*/ , '' /*prof_nombre*/ , '' /*prof_liste*/ , user_id /*proprio_id*/ );
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

    /**
     * Choisir les items associés à une sélection : mise en place du formulaire
     * @return void
     */
    var choisir_compet = function()
    {
      // Ne pas changer ici la valeur de "mode" (qui est à "ajouter" ou "modifier" ou "dupliquer").
      cocher_matieres_items( $('#f_compet_liste').val() );
      // Afficher la zone
      $.fancybox( { 'href':'#zone_matieres_items' , onStart:function(){$('#zone_matieres_items').css("display","block");} , onClosed:function(){$('#zone_matieres_items').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
      $(document).tooltip("destroy");infobulle(); // Sinon, bug avec l'infobulle contenu dans le fancybox qui ne disparait pas au clic...
    };

    /**
     * Choisir les professeurs associés à une sélection : mise en place du formulaire
     * @return void
     */
    var choisir_prof = function()
    {
      selectionner_profs_option( $('#f_prof_liste').val() );
      // Afficher la zone
      $.fancybox( { 'href':'#zone_profs' , onStart:function(){$('#zone_profs').css("display","block");} , onClosed:function(){$('#zone_profs').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
      $(document).tooltip("destroy");infobulle(); // Sinon, bug avec l'infobulle contenu dans le fancybox qui ne disparait pas au clic...
    };

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Appel des fonctions en fonction des événements
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on( 'click' , 'q.ajouter'        , ajouter );
    $('#table_action').on( 'click' , 'q.modifier'       , modifier_dupliquer );
    $('#table_action').on( 'click' , 'q.dupliquer'      , modifier_dupliquer );
    $('#table_action').on( 'click' , 'q.supprimer'      , supprimer );

    $('#form_gestion').on( 'click' , '#bouton_annuler'  , annuler );
    $('#form_gestion').on( 'click' , '#bouton_valider'  , function(){formulaire.submit();} );
    $('#form_gestion').on( 'keyup' , 'input,select'     , function(e){intercepter(e);} );
    $('#form_gestion').on( 'click' , 'q.choisir_compet' , choisir_compet );
    $('#form_gestion').on( 'click' , 'q.choisir_prof'   , choisir_prof );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Indiquer au survol une liste de profs associés à une évaluation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on
    (
      'mouseover',
      'img.bulle_profs',
      function()
      {
        var obj_image  = $(this);
        var selection_id = obj_image.parent().parent().attr('id').substring(3); // "id_" + ref
        var proprio_id   = obj_image.parent().attr('id').substring(8); // "proprio_" + ref
        var prof_liste   = tab_profs[selection_id];
        var tab_texte    = new Array();;
        if(prof_liste.length)
        {
          prof_liste += '_z'+proprio_id;
          var tab_val = prof_liste.split('_');
          for(i in tab_val)
          {
            var val_option = tab_val[i].substring(0,1);
            var id_prof    = tab_val[i].substring(1);
            var id_select  = 'p'+'_'+id_prof;
            if($('#'+id_select).length)
            {
              tab_texte[i] = $('#'+id_select).next().next().text();
            }
            else
            {
              tab_texte[i] = 'collègue n°'+id_prof+'... ?';
            }
          }
          tab_texte.sort();
        }
        obj_image.attr( 'title' , tab_texte.join('<br />') );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Modification du select par lot pour tous les profs
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('input[name=prof_check_all]').click
    (
      function()
      {
        var valeur = $(this).val();
        $('#zone_profs').find('select').find('option[value='+valeur+']').prop('selected',true);
        $('.prof_liste').find('span.select_img').attr('class','select_img droit_'+valeur);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Modification du select pour choisir un droit à un prof
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

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

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour fermer le cadre des items associés à une sélection (annuler / retour)
    // Clic sur le bouton pour fermer le cadre des professeurs associés à une sélection (annuler / retour)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#annuler_compet , #annuler_profs').click
    (
      function()
      {
        $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':700 , 'centerOnScroll':true } );
        return false;
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour valider le choix des items associés à une sélection
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
    // Clic sur le bouton pour valider le choix des profs associés à une sélection
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

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
        nombre = (!nombre) ? 'non' : (nombre+1)+' collègues' ;
        $('#f_prof_liste').val(liste);
        $('#f_prof_nombre').val(nombre);
        $('#annuler_profs').click();
      }
    );

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
          f_nom          : { required:true , maxlength:60 },
          f_compet_liste : { required:true },
          f_prof_liste   : { required:false }
        },
        messages :
        {
          f_nom          : { required:"nom manquant" , maxlength:"60 caractères maximum" },
          f_compet_liste : { required:"item(s) manquant(s)" },
          f_prof_liste   : { }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          element.after(error);
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
        switch (mode)
        {
          case 'ajouter':
            $('#table_action tbody tr.vide').remove(); // En cas de tableau avec une ligne vide pour la conformité XHTML
          case 'dupliquer':
            $('#table_action tbody').prepend(responseJSON['html']);
            eval( responseJSON['script'] );
            break;
          case 'modifier':
            $('#id_'+$('#f_id').val()).addClass("new").html(responseJSON['html']);
            eval( responseJSON['script'] );
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
          data : 'f_objet=referentiels'+'&f_item_comm=0'+'&f_all_if_pp=0',
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
