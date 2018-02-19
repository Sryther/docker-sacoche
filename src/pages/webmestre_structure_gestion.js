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

    var mode        = false;
    var please_wait = false;
    var listing_id  = new Array();

    // tri du tableau (avec jquery.tablesorter.js).
    $('#table_action').tablesorter({ headers:{0:{sorter:false},1:{sorter:false},6:{sorter:'date_fr'},7:{sorter:false}} });
    var tableau_tri = function(){ $('#table_action').trigger( 'sorton' , [ [[3,0],[4,0]] ] ); };
    var tableau_maj = function(){ $('#table_action').trigger( 'update' , [ true ] ); };
    tableau_tri();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Recharger la page en restreignant l'affichage en fonction des choix préalables
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_prechoix select').change
    (
      function()
      {
        if($('#f_geo_id option:selected').val())
        {
          $('#table_action, #structures').hide(0);
          $('#form_prechoix').submit();
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Fonctions utilisées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function afficher_form_gestion( mode , base_id , geo , localisation , denomination , uai , contact_nom , contact_prenom , contact_courriel , date_fr , acces , check )
    {
      $('#f_action').val(mode);
      $('#f_acces').val(acces);
      $('#f_check').val(check);
      $('#f_base_id').val(base_id);
      $('#f_geo').html( options_geo.replace('>'+geo,' selected>'+geo) );
      $('#f_localisation').val(localisation);
      $('#f_denomination').val(denomination);
      $('#f_uai').val(uai);
      $('#f_contact_nom').val(contact_nom);
      $('#f_contact_prenom').val(contact_prenom);
      $('#f_contact_courriel').val(contact_courriel);
      $('#f_date_fr').val(date_fr);
      // pour finir
      $('#form_gestion h2').html(mode[0].toUpperCase() + mode.substring(1) + " un établissement");
      if(mode=='ajouter')
      {
        $('#p_ajout , #span_envoi').show(0);
      }
      else
      {
        $('#p_ajout , #span_envoi').hide(0);
      }
      if(mode!='supprimer')
      {
        $('#gestion_edit').show(0);
        $('#gestion_delete').hide(0);
      }
      else
      {
        $('#gestion_delete_identite').html(denomination+' ['+uai+']');
        $('#gestion_edit').hide(0);
        $('#gestion_delete').show(0);
      }
      $('#ajax_msg_gestion').removeAttr('class').html("");
      $('#form_gestion label[generated=true]').removeAttr('class').html("");
      $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':800 , 'centerOnScroll':true } );
    }

    /**
     * Ajouter un établissement : mise en place du formulaire
     * @return void
     */
    var ajouter = function()
    {
      mode = $(this).attr('class');
      var geo = escapeHtml($('#f_geo_id option[value='+geo_defaut+']').text());
      // Afficher le formulaire
      afficher_form_gestion( mode , '' /*base_id*/ , geo , '' /*localisation*/ , '' /*denomination*/ , '' /*uai*/ , '' /*contact_nom*/ , '' /*contact_prenom*/ , '' /*contact_courriel*/ , input_date /*date_fr*/ , 'bloquer' /*acces*/ , '' /*check*/ );
    };

    /**
     * Modifier un établissement : mise en place du formulaire
     * @return void
     */
    var modifier = function()
    {
      mode = $(this).attr('class');
      var objet_tds  = $(this).parent().parent().find('td');
      // Récupérer les informations de la ligne concernée
      var acces            = objet_tds.eq(0).children('a').children('img').attr('class');
      var check            = Number(objet_tds.eq(1).children('input').is(':checked'));
      var base_id          = objet_tds.eq(2).html();
      var lieu             = objet_tds.eq(3).html();
      var structure        = objet_tds.eq(4).html();
      var contact_nom      = objet_tds.eq(5).children('span').eq(0).html();
      var contact_prenom   = objet_tds.eq(5).children('span').eq(1).html();
      var contact_courriel = objet_tds.eq(5).children('div').html();
      var date_fr          = objet_tds.eq(6).html();
      // retirer le champ caché pour le tri, séparer zone géographique et localisation
      var reg = new RegExp('<br ?/?>',"g");  // Le navigateur semble transformer <br /> en <br> ...
      var tab_infos        = lieu.substring(geo_ordre_longueur+7).split(reg); // <i>...</i>
      var geo              = tab_infos[0];
      var localisation     = tab_infos[1];
      // séparer denomination et UAI
      var reg = new RegExp('<br ?/?>',"g");  // Le navigateur semble transformer <br /> en <br> ...
      var tab_infos        = structure.split(reg);
      var denomination     = tab_infos[0];
      var uai              = tab_infos[1];
      // Afficher le formulaire
      afficher_form_gestion( mode , base_id , geo , unescapeHtml(localisation) , unescapeHtml(denomination) , unescapeHtml(uai) , unescapeHtml(contact_nom) , unescapeHtml(contact_prenom) , unescapeHtml(contact_courriel) , date_fr , acces , check );
    };

    /**
     * Supprimer un établissement : mise en place du formulaire
     * @return void
     */
    var supprimer = function()
    {
      mode = $(this).attr('class');
      var objet_tds     = $(this).parent().parent().find('td');
      // Récupérer les informations de la ligne concernée
      var base_id          = objet_tds.eq(2).html();
      var structure        = objet_tds.eq(4).html();
      // séparer denomination et UAI
      var reg = new RegExp('<br ?/?>',"g");  // Le navigateur semble transformer <br /> en <br> ...
      var tab_infos        = structure.split(reg);
      var denomination     = tab_infos[0];
      var uai              = tab_infos[1];
      // Afficher le formulaire
      afficher_form_gestion( mode , base_id , '' /*geo*/ , '' /*localisation*/ , unescapeHtml(denomination) , unescapeHtml(uai) , '' /*contact_nom*/ , '' /*contact_prenom*/ , '' /*contact_courriel*/ , '' /*date_fr*/ , '' /*acces*/ , '' /*check*/ );
    };

    /**
     * Générer un nouveau mdp admin : mise en place du formulaire
     * @return void
     */
    var initialiser_mdp = function()
    {
      mode = $(this).attr('class');
      var objet_tds     = $(this).parent().parent().find('td');
      // Récupérer les informations de la ligne concernée
      var base_id          = objet_tds.eq(2).html();
      var structure        = objet_tds.eq(4).html();
      // séparer denomination et UAI
      var reg = new RegExp('<br ?/?>',"g");  // Le navigateur semble transformer <br /> en <br> ...
      var tab_infos        = structure.split(reg);
      var denomination     = tab_infos[0];
      var uai              = tab_infos[1];
      // Mettre les infos de côté
      $('#generer_base_id').val(base_id);
      // Afficher la zone associée après avoir chargé son contenu
      $('#titre_generer_mdp').html(denomination+' ['+uai+']');
      $('#ajax_msg_generer_mdp').removeAttr('class').html('&nbsp;');
      $.fancybox( '<label class="loader">'+'En cours&hellip;'+'</label>' , {'centerOnScroll':true} );
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=lister_admin'+'&f_base_id='+base_id,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+'</label>' , {'centerOnScroll':true} );
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
              $('#f_admin_id').html('<option value="">&nbsp;</option>'+responseJSON['value']);
              $.fancybox( { 'href':'#zone_generer_mdp' , onStart:function(){$('#zone_generer_mdp').css("display","block");} , onClosed:function(){$('#zone_generer_mdp').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
            }
          }
        }
      );
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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Appel des fonctions en fonction des événements
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on( 'click' , 'q.ajouter'         , ajouter );
    $('#table_action').on( 'click' , 'q.modifier'        , modifier );
    $('#table_action').on( 'click' , 'q.supprimer'       , supprimer );
    $('#table_action').on( 'click' , 'q.initialiser_mdp' , initialiser_mdp );

    $('#form_gestion').on( 'click' , '#bouton_annuler' , annuler );
    $('#form_gestion').on( 'click' , '#bouton_valider' , function(){formulaire.submit();} );
    $('#form_gestion').on( 'keyup' , 'input,select'    , function(e){intercepter(e);} );

    $('#zone_generer_mdp').on( 'click' , '#fermer_zone_generer_mdp' , annuler );

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
// Clic sur un bouton pour bloquer ou débloquer une structure
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on
    (
      'click',
      'img[class=bloquer] , img[class=debloquer]',
      function()
      {
        var objet   = $(this);
        var action  = $(this).attr('class');
        var base_id = $(this).parent().parent().next().next().html();
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+action+'&f_base_id='+base_id,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+'</label>' , {'centerOnScroll':true} );
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
                objet.parent().html(responseJSON['value']);
              }
              return false;
            }
          }
        );
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
      $('#ajax_supprimer').attr('class','loader').html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=supprimer'+'&f_listing_id='+listing_id,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_supprimer').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            $("button").prop('disabled',false);
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==false)
            {
              $('#ajax_supprimer').attr('class','alerte').html(responseJSON['value']);
            }
            else
            {
              $("input[type=checkbox]:checked").each
              (
                function()
                {
                  $(this).parent().parent().remove();
                }
              );
              $('#ajax_supprimer').attr('class','valide').html('Demande réalisée !');
              $("button").prop('disabled',false);
            }
          }
        }
      );
    };

    $('#zone_actions button').click
    (
      function()
      {
        // Grouper les checkbox dans un champ unique afin d'éviter tout problème avec une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque) ou "max input vars" généralement fixé à 1000.
        listing_id = [];
        $("#table_action input[type=checkbox]:checked").each(function(){listing_id.push($(this).val());});
        if(!listing_id.length)
        {
          $('#ajax_supprimer').attr('class','erreur').html("Aucune structure cochée !");
          return false;
        }
        $('#ajax_supprimer').removeAttr('class').html('&nbsp;');
        var id = $(this).attr('id');
        if(id=='bouton_supprimer')
        {
          $.prompt(prompt_etapes_supprimer_cochees);
        }
        else
        {
          $('#listing_ids').val(listing_id);
          var tab = new Array;
          tab['bouton_newsletter'] = "webmestre_newsletter";
          tab['bouton_stats']      = "webmestre_statistiques";
          tab['bouton_transfert']  = "webmestre_structure_transfert";
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
    // Validation de la demande de génération d'un mot de passe administrateur
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#valider_generer_mdp').click
    (
      function()
      {
        if(!$('#f_admin_id option:selected').val())
        {
          $('#ajax_msg_generer_mdp').attr('class','erreur').html("Sélectionner un administrateur !");
          return false;
        }
        $('#zone_generer_mdp button').prop('disabled',true);
        $('#ajax_msg_generer_mdp').attr('class','loader').html("En cours&hellip;");
        $('#zone_imprimer_retour').html("");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=initialiser_mdp'+'&'+$("#zone_generer_mdp").serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#zone_generer_mdp button').prop('disabled',false);
              $('#ajax_msg_generer_mdp').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('#zone_generer_mdp button').prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg_generer_mdp').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                $.fancybox( '<p>'+responseJSON['value']+'</p>' , {'centerOnScroll':true} );
              }
            }
          }
        );
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
          f_base_id          : { required:false , digits:true },
          f_geo              : { required:true },
          f_localisation     : { required:true , maxlength:50 },
          f_denomination     : { required:true , maxlength:50 },
          f_uai              : { required:false , uai_format:true , uai_clef:true },
          f_contact_nom      : { required:true , maxlength:NOM_LONGUEUR_MAX },
          f_contact_prenom   : { required:true , maxlength:PRENOM_LONGUEUR_MAX },
          f_contact_courriel : { required:true , email:true , maxlength:COURRIEL_LONGUEUR_MAX },
          f_courriel_envoi   : { required:false },
          f_courriel_copie   : { required:false }
        },
        messages :
        {
          f_base_id          : { digits:"nombre entier requis" },
          f_geo              : { required:"zone manquante" },
          f_localisation     : { required:"localisation manquante" , maxlength:"50 caractères maximum" },
          f_denomination     : { required:"dénomination manquante" , maxlength:"50 caractères maximum" },
          f_uai              : { uai_format:"n°UAI invalide" , uai_clef:"n°UAI invalide" },
          f_contact_nom      : { required:"nom manquant" , maxlength:NOM_LONGUEUR_MAX+" caractères maximum" },
          f_contact_prenom   : { required:"prénom manquant" , maxlength:PRENOM_LONGUEUR_MAX+" caractères maximum" },
          f_contact_courriel : { required:"courriel manquant" , email:"courriel invalide", maxlength:COURRIEL_LONGUEUR_MAX+" caractères maximum" },
          f_courriel_envoi   : { },
          f_courriel_copie   : { }
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
      target : "#ajax_msg_gestion",
      beforeSerialize : action_form_avant_serialize,
      beforeSubmit : test_form_avant_envoi,
      error : retour_form_erreur,
      success : retour_form_valide
    };

    var prompt_etapes_confirmer_inscription = {
      etape_1: {
        title   : 'Demande de confirmation',
        html    : "Le mot de passe du premier administrateur, non récupérable ultérieurement, ne sera pas transmis !<br />Souhaitez-vous vraiment ne pas vouloir envoyer le courriel d'inscription ?",
        buttons : {
          "Non, c'est une erreur !" : false ,
          "Oui, je confirme !" : true
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            formulaire.ajaxSubmit(ajaxOptions); // Pas de $(this) ici...
          }
        }
      }
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire.submit
    (
      function()
      {
        if (please_wait)
        {
          return false;
        }
        else if( (mode=='ajouter') && ($('#f_courriel_envoi').is(':checked')==false) )
        {
          $.prompt(prompt_etapes_confirmer_inscription);
        }
        else
        {
          $(this).ajaxSubmit(ajaxOptions);
        }
        return false;
      }
    );

    // Fonction précédent le traitement du formulaire (avec jquery.form.js)
    function action_form_avant_serialize(jqForm, options)
    {
      // Décocher les checkbox sans rapport avec ce formulaire
      $('input[name=f_ids]:checked').each
      (
        function()
        {
          $(this).prop('checked',false);
        }
      );
    }

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
        $('#ajax_msg_gestion').attr('class','valide').html('Demande réalisée !');
        switch (mode)
        {
          case 'ajouter':
            $('#table_action tbody').prepend(responseJSON['value']);
            break;
          case 'modifier':
            $('#id_'+$('#f_base_id').val()).addClass("new").html(responseJSON['value']);
            break;
          case 'supprimer':
            $('#id_'+$('#f_base_id').val()).remove();
            break;
        }
        tableau_maj;
        $.fancybox.close();
        mode = false;
      }
    }

  }
);
