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
    var profil = 'ADM';
    var memo_login = '';

    // tri du tableau (avec jquery.tablesorter.js).
    $('#table_action').tablesorter({ headers:{6:{sorter:false},8:{sorter:false}} });
    var tableau_tri = function(){ $('#table_action').trigger( 'sorton' , [ [[3,0],[4,0]] ] ); };
    var tableau_maj = function(){ $('#table_action').trigger( 'update' , [ true ] ); };
    tableau_tri();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le checkbox pour choisir ou non un login
// Clic sur le checkbox pour choisir ou non un mot de passe
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#box_login , #box_password').click
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

    function afficher_form_gestion( mode , id , id_ent , id_gepi , profil , genre , nom , prenom , login , courriel )
    {
      var tab_genre = { '' : 'I' , 'M.' : 'M' , 'Mme' : 'F' };
      var opt_genre = ( typeof(tab_genre[genre]) !== 'undefined' ) ? tab_genre[genre] : 'I' ;
      $('#f_action').val(mode);
      $('#f_id').val(id);
      $('#f_id_ent').val(id_ent);
      $('#f_id_gepi').val(id_gepi);
      $('#f_genre option[value='+opt_genre+']').prop('selected',true);
      $('#f_nom').val(nom);
      $('#f_prenom').val(prenom);
      $('#f_courriel').val(courriel);
      // login
      memo_login = login;
      var texte_box  = (mode=='modifier') ? "inchangé" : "automatique (modèle "+tab_login_modele[profil]+")" ;
      $('#f_login').val(login).parent().hide(0);
      $('#box_login').prop('checked',true).next().show(0).html(texte_box);
      // mot de passe
      var texte_box  = (mode=='modifier') ? "inchangé" : "aléatoire" ;
      $('#f_password').val('').parent().hide(0);
      $('#box_password').prop('checked',true).next().show(0).html(texte_box);
      // pour finir
      $('#form_gestion h2').html(mode[0].toUpperCase() + mode.substring(1) + " un utilisateur");
      if(mode!='supprimer')
      {
        $('#gestion_edit').show(0);
        $('#gestion_delete').hide(0);
      }
      else
      {
        $('#gestion_delete_identite').html(prenom+" "+nom);
        $('#gestion_edit').hide(0);
        $('#gestion_delete').show(0);
      }
      $('#ajax_msg_gestion').removeAttr('class').html("");
      $('#form_gestion label[generated=true]').removeAttr('class').html("");
      $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
      if(mode=='ajouter') { $('#f_nom').focus(); }
    }

    /**
     * Ajouter un administrateur : mise en place du formulaire
     * @return void
     */
    var ajouter = function()
    {
      mode = $(this).attr('class');
      // Afficher le formulaire
      afficher_form_gestion( mode , '' /*id*/ , '' /*id_ent*/ , '' /*id_gepi*/ , profil , '' /*genre*/ , '' /*nom*/ , '' /*prenom*/ , '' /*login*/ , '' /*couriel*/ );
    };

    /**
     * Modifier un administrateur : mise en place du formulaire
     * @return void
     */
    var modifier = function()
    {
      mode = $(this).attr('class');
      var objet_tr   = $(this).parent().parent();
      var objet_tds  = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var id         = objet_tr.attr('id').substring(3);
      var id_ent     = objet_tds.eq(0).html();
      var id_gepi    = objet_tds.eq(1).html();
      var genre      = objet_tds.eq(2).html();
      var nom        = objet_tds.eq(3).html();
      var prenom     = objet_tds.eq(4).html();
      var login      = objet_tds.eq(5).html();
      var courriel   = objet_tds.eq(7).html();
      // Retirer une éventuelle balise image présente dans login
      position_image = login.indexOf('<');
      if (position_image!=-1)
      {
        login = login.substring(0,position_image-1);
      }
      // Afficher le formulaire
      afficher_form_gestion( mode , id , unescapeHtml(id_ent) , unescapeHtml(id_gepi) , profil , unescapeHtml(genre) , unescapeHtml(nom) , unescapeHtml(prenom) , unescapeHtml(login) , unescapeHtml(courriel) );
    };

    /**
     * Retirer un administrateur : mise en place du formulaire
     * @return void
     */
    var supprimer = function()
    {
      mode = $(this).attr('class');
      var objet_tr   = $(this).parent().parent();
      var objet_tds  = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var id         = objet_tr.attr('id').substring(3);
      var nom        = objet_tds.eq(3).html();
      var prenom     = objet_tds.eq(4).html();
      // Afficher le formulaire
      afficher_form_gestion( mode , id , '' /*id_ent*/ , '' /*id_gepi*/ , profil , '' /*genre*/ , unescapeHtml(nom) , unescapeHtml(prenom) , '' /*login*/ , '' /*courriel*/ );
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
    $('#table_action').on( 'click' , 'q.supprimer'     , supprimer );

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
          f_id_ent   : { required:false , maxlength:ID_ENT_LONGUEUR_MAX },
          f_id_gepi  : { required:false , maxlength:ID_GEPI_LONGUEUR_MAX },
          f_genre    : { required:false },
          f_nom      : { required:true , maxlength:NOM_LONGUEUR_MAX },
          f_prenom   : { required:true , maxlength:PRENOM_LONGUEUR_MAX },
          f_login    : { required:function(){return !$('#box_login').is(':checked');} , maxlength:LOGIN_LONGUEUR_MAX },
          f_password : { required:function(){return !$('#box_password').is(':checked');} , minlength:function(){return tab_mdp_longueur_mini[profil];} , maxlength:PASSWORD_LONGUEUR_MAX },
          f_courriel : { required:false , email:true , maxlength:COURRIEL_LONGUEUR_MAX }
        },
        messages :
        {
          f_id_ent   : { maxlength:ID_ENT_LONGUEUR_MAX+" caractères maximum" },
          f_id_gepi  : { maxlength:ID_GEPI_LONGUEUR_MAX+" caractères maximum" },
          f_genre    : { },
          f_nom      : { required:"nom manquant"    , maxlength:NOM_LONGUEUR_MAX+" caractères maximum" },
          f_prenom   : { required:"prénom manquant" , maxlength:PRENOM_LONGUEUR_MAX+" caractères maximum" },
          f_login    : { required:"login manquant"  , maxlength:LOGIN_LONGUEUR_MAX+" caractères maximum" },
          f_password : { required:"mot de passe manquant" , minlength:function(){return tab_mdp_longueur_mini[profil]+" caractères minimum";} , maxlength:PASSWORD_LONGUEUR_MAX+" caractères maximum" },
          f_courriel : { email:"adresse invalide", maxlength:COURRIEL_LONGUEUR_MAX+" caractères maximum" }
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
