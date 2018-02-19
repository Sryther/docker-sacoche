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

    var mode       = false;
    var profil     = 'ELV';
    var memo_login = '';
    var listing_id = new Array();
    var f_action   = '';

    // tri du tableau (avec jquery.tablesorter.js).
    $('#table_action').tablesorter({ headers:{0:{sorter:false},9:{sorter:'date_fr'},11:{sorter:false},14:{sorter:'date_fr'},15:{sorter:false}} });
    var tableau_tri = function(){ $('#table_action').trigger( 'sorton' , [ [[7,0],[8,0]] ] ); };
    var tableau_maj = function(){ $('#table_action').trigger( 'update' , [ true ] ); };
    tableau_tri();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Recharger la page en restreignant l'affichage en fonction des choix préalables
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_groupes').change
    (
      function()
      {
        if($('#f_groupes option:selected').val())
        {
          $('#form_prechoix').submit();
        }
      }
    );

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
// Clic sur le checkbox pour choisir ou non un login
// Clic sur le checkbox pour choisir ou non un mot de passe
// Clic sur le checkbox pour choisir ou non une date de naissance
// Clic sur le checkbox pour choisir ou non une date de sortie
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#box_login , #box_password , #box_birth_date , #box_sortie_date').click
    (
      function()
      {
        if($(this).is(':checked'))
        {
          $(this).next().show(0).next().hide(0);
        }
        else
        {
          $(this).next().hide(0).next().show(0).children('input').focus();
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Fonctions utilisées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function afficher_form_gestion( mode , id , id_ent , id_gepi , sconet_id , sconet_num , reference , profil , genre , nom , prenom , birth_date_fr , login , courriel , uai_origine , sortie_date_fr , check )
    {
      var tab_genre = { '' : 'I' , 'Masculin' : 'M' , 'Féminin' : 'F' };
      var opt_genre = ( typeof(tab_genre[genre]) !== 'undefined' ) ? tab_genre[genre] : 'I' ;
      $('#f_action').val(mode);
      $('#f_check').val(check);
      $('#f_id').val(id);
      $('#f_id_ent').val(id_ent);
      $('#f_id_gepi').val(id_gepi);
      $('#f_sconet_id').val(sconet_id);
      $('#f_sconet_num').val(sconet_num);
      $('#f_reference').val(reference);
      $('#f_genre option[value='+opt_genre+']').prop('selected',true);
      $('#f_nom').val(nom);
      $('#f_prenom').val(prenom);
      $('#f_courriel').val(courriel);
      $('#f_uai_origine').val(uai_origine);
      // login
      memo_login = login;
      var texte_box  = (mode=='modifier') ? "inchangé" : "automatique (modèle "+tab_login_modele[profil]+")" ;
      $('#f_login').val(login).parent().hide(0);
      $('#box_login').prop('checked',true).next().show(0).html(texte_box);
      // mot de passe
      var texte_box  = (mode=='modifier') ? "inchangé" : "aléatoire" ;
      $('#f_password').val('').parent().hide(0);
      $('#box_password').prop('checked',true).next().show(0).html(texte_box);
      // date de naissance
      if(birth_date_fr=='-')
      {
        $('#box_birth_date').prop('checked',true).next().show(0);
        $('#f_birth_date').val('').parent().hide(0);
      }
      else
      {
        $('#box_birth_date').prop('checked',false).next().hide(0);
        $('#f_birth_date').val(birth_date_fr).parent().show(0);
      }
      // date de sortie
      if(sortie_date_fr=='-')
      {
        $('#box_sortie_date').prop('checked',true).next().show(0);
        $('#f_sortie_date').val(input_date).parent().hide(0);
      }
      else
      {
        $('#box_sortie_date').prop('checked',false).next().hide(0);
        $('#f_sortie_date').val(sortie_date_fr).parent().show(0);
      }
      // pour finir
      $('#form_gestion h2').html(mode[0].toUpperCase() + mode.substring(1) + " un utilisateur");
      $('#ajax_msg_gestion').removeAttr('class').html("");
      $('#form_gestion label[generated=true]').removeAttr('class').html("");
      $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
      if(mode=='ajouter') { $('#f_nom').focus(); }
    }

    /**
     * Ajouter un élève : mise en place du formulaire
     * @return void
     */
    var ajouter = function()
    {
      mode = $(this).attr('class');
      // Insérer l'information du groupe affiché
      $('#f_groupe').val($('#f_groupes option:selected').val());
      // Afficher le formulaire
      afficher_form_gestion( mode , '' /*id*/ , '' /*id_ent*/ , '' /*id_gepi*/ , '' /*sconet_id*/ , '' /*sconet_num*/ , '' /*reference*/ , profil , '' /*genre*/ , '' /*nom*/ , '' /*prenom*/ , '-' /*birth_date_fr*/ , '' /*login*/ , '' /*couriel*/ , '' /*uai_origine*/ , '-' /*sortie_date_fr*/ , '' /*check*/ );
    };

    /**
     * Modifier un élève : mise en place du formulaire
     * @return void
     */
    var modifier = function()
    {
      mode = $(this).attr('class');
      var objet_tr   = $(this).parent().parent();
      var objet_tds  = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var id             = objet_tr.attr('id').substring(3);
      var check          = Number(objet_tds.eq(0).children('input').is(':checked'));
      var id_ent         = objet_tds.eq( 1).html();
      var id_gepi        = objet_tds.eq( 2).html();
      var sconet_id      = objet_tds.eq( 3).html();
      var sconet_num     = objet_tds.eq( 4).html();
      var reference      = objet_tds.eq( 5).html();
      var genre          = objet_tds.eq( 6).html();
      var nom            = objet_tds.eq( 7).html();
      var prenom         = objet_tds.eq( 8).html();
      var birth_date_fr  = objet_tds.eq( 9).html();
      var login          = objet_tds.eq(10).html();
      var courriel       = objet_tds.eq(12).html();
      var uai_origine    = objet_tds.eq(13).html();
      var sortie_date_fr = objet_tds.eq(14).html();
      // Retirer une éventuelle balise image présente dans login
      position_image = login.indexOf('<');
      if (position_image!=-1)
      {
        login = login.substring(0,position_image-1);
      }
      // Afficher le formulaire
      afficher_form_gestion( mode , id , unescapeHtml(id_ent) , unescapeHtml(id_gepi) , sconet_id , sconet_num , unescapeHtml(reference) , profil , unescapeHtml(genre) , unescapeHtml(nom) , unescapeHtml(prenom) , birth_date_fr , unescapeHtml(login) , unescapeHtml(courriel) , unescapeHtml(uai_origine) , sortie_date_fr , check );
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

    $('#table_action').on( 'click' , 'q.ajouter'       , ajouter );
    $('#table_action').on( 'click' , 'q.modifier'      , modifier );

    $('#form_gestion').on( 'click' , '#bouton_annuler' , annuler );
    $('#form_gestion').on( 'click' , '#bouton_valider' , function(){formulaire.submit();} );
    $('#form_gestion').on( 'keyup' , 'input,select'    , function(e){intercepter(e);} );

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
          f_id_ent      : { required:false , maxlength:ID_ENT_LONGUEUR_MAX },
          f_id_gepi     : { required:false , maxlength:ID_GEPI_LONGUEUR_MAX },
          f_sconet_id   : { required:false , digits:true , max:16777215 },
          f_sconet_num  : { required:false , digits:true , max:65535 },
          f_reference   : { required:false , maxlength:11 },
          f_genre       : { required:false },
          f_nom         : { required:true , maxlength:NOM_LONGUEUR_MAX },
          f_prenom      : { required:true , maxlength:PRENOM_LONGUEUR_MAX },
          f_login       : { required:function(){return !$('#box_login').is(':checked');} , maxlength:LOGIN_LONGUEUR_MAX },
          f_birth_date  : { required:function(){return !$('#box_birth_date').is(':checked');} , dateITA:true },
          f_password    : { required:function(){return !$('#box_password').is(':checked');} , minlength:function(){return tab_mdp_longueur_mini[profil];} , maxlength:PASSWORD_LONGUEUR_MAX },
          f_courriel    : { required:false , email:true , maxlength:COURRIEL_LONGUEUR_MAX },
          f_uai_origine : { required:false , uai_format:true , uai_clef:true },
          f_sortie_date : { required:function(){return !$('#box_sortie_date').is(':checked');} , dateITA:true }
        },
        messages :
        {
          f_id_ent      : { maxlength:ID_ENT_LONGUEUR_MAX+" caractères maximum" },
          f_id_gepi     : { maxlength:ID_GEPI_LONGUEUR_MAX+" caractères maximum" },
          f_sconet_id   : { digits:"Id Sconet : nombre entier inférieur à 2^24" },
          f_sconet_num  : { digits:"N° Sconet : nombre entier inférieur à 2^16" },
          f_reference   : { maxlength:"référence de 11 caractères maximum" },
          f_genre       : { },
          f_nom         : { required:"nom manquant"    , maxlength:NOM_LONGUEUR_MAX+" caractères maximum" },
          f_prenom      : { required:"prénom manquant" , maxlength:PRENOM_LONGUEUR_MAX+" caractères maximum" },
          f_login       : { required:"login manquant"  , maxlength:LOGIN_LONGUEUR_MAX+" caractères maximum" },
          f_birth_date  : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_password    : { required:"mot de passe manquant" , minlength:function(){return tab_mdp_longueur_mini[profil]+" caractères minimum";} , maxlength:PASSWORD_LONGUEUR_MAX+" caractères maximum" },
          f_courriel    : { email:"adresse invalide", maxlength:COURRIEL_LONGUEUR_MAX+" caractères maximum" },
          f_uai_origine : { uai_format:"n°UAI invalide" , uai_clef:"n°UAI invalide" },
          f_sortie_date : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          if((element.attr("id")=='f_birth_date')||(element.attr("id")=='f_sortie_date')) { element.next().after(error); }
          else {element.after(error);}
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

    // Fonction précédent le traitement du formulaire (avec jquery.form.js)
    function action_form_avant_serialize(jqForm, options)
    {
      if($('#box_login').is(':checked'))
      {
        $('#f_login').val(memo_login); // Pas de risque d'enregistrement d'un mauvais login, mais d'un retour trompeur à afficher si login modifié puis case recochée.
      }
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
        $('#ajax_msg_gestion').attr('class','valide').html("Demande réalisée !");
        switch (mode)
        {
          case 'ajouter':
            $('#table_action tbody tr.vide').remove(); // En cas de tableau avec une ligne vide pour la conformité XHTML
            $('#table_action tbody').prepend(responseJSON['value']);
            break;
          case 'modifier':
            $('#id_'+$('#f_id').val()).addClass("new").html(responseJSON['value']);
            break;
        }
        tableau_maj();
        $.fancybox.close();
        mode = false;
      }
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur un bouton pour effectuer une action sur les utilisateurs cochés
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var prompt_etapes = {
      etape_1: {
        title   : 'Demande de confirmation',
        html    : "Attention : les informations associées aux comptes seront perdues !<br />Souhaitez-vous vraiment supprimer les comptes sélectionnés ?",
        buttons : {
          "Non, c'est une erreur !" : false ,
          "Oui, je confirme !" : true
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            envoyer_action_confirmee(f_action,listing_id);
          }
        }
      }
    };

    $('#zone_actions button').click
    (
      function()
      {
        // Grouper les checkbox dans un champ unique afin d'éviter tout problème avec une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque) ou "max input vars" généralement fixé à 1000.
        listing_id = [];
        $("input[name=f_ids]:checked").each(function(){listing_id.push($(this).val());});
        if(!listing_id.length)
        {
          $('#ajax_msg_actions').attr('class','erreur').html("Aucun utilisateur coché !");
          return false;
        }
        // On demande confirmation pour la suppression
        f_action = $(this).attr('id');
        if(f_action=='supprimer')
        {
          $('#ajax_msg_actions').removeAttr('class').html("");
          $.prompt(prompt_etapes);
        }
        else
        {
          envoyer_action_confirmee(f_action,listing_id);
        }
        return false;
      }
    );

    function envoyer_action_confirmee(f_action,listing_id)
    {
      $('#ajax_msg_actions').attr('class','loader').html("En cours&hellip;");
      $('#zone_actions button').prop('disabled',true);
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action='+f_action+'&f_listing_id='+listing_id,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg_actions').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            $('#zone_actions button').prop('disabled',false);
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            $('#zone_actions button').prop('disabled',false);
            if(responseJSON['statut']==false)
            {
              $('#ajax_msg_actions').attr('class','alerte').html(responseJSON['value']);
            }
            else
            {
              $('#ajax_msg_actions').attr('class','valide').html("Demande réalisée.");
              var tab_ids = responseJSON['value'].split(',');
              for ( i=0 ; i<tab_ids.length ; i++ )
              {
                switch (f_action)
                {
                  case 'retirer':
                    $('#id_'+tab_ids[i]).children("td:last").prev().html(input_date);
                    break;
                  case 'reintegrer':
                    $('#id_'+tab_ids[i]).children("td:last").prev().html('-');
                    break;
                  case 'supprimer':
                    $('#id_'+tab_ids[i]).remove();
                    break;
                }
              }
              tableau_maj();
            }
          }
        }
      );
    }

  }
);
