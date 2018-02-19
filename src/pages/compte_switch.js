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

    var mode = false;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Basculer vers un autre compte
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on
    (
      'click',
      'a',
      function()
      {
        var user_id = $(this).attr('href').substring(4); // "#id_" + ref
        $.fancybox( '<label class="loader">'+'En cours&hellip;'+'</label>' , { 'minWidth':400 , 'centerOnScroll':true } );
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=basculer&f_user_id='+user_id,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+'</label>' , { 'minWidth':400 , 'centerOnScroll':true } );
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==true)
              {
                $.fancybox( '<label class="valide">'+"Bascule réussie ; redirection en cours&hellip;"+'</label>' , { 'minWidth':400 , 'centerOnScroll':true } );
                document.location.href = './index.php?page=compte_accueil';
              }
              else
              {
                $.fancybox( '<label class="alerte">'+responseJSON['value']+'</label>' , { 'minWidth':400 , 'centerOnScroll':true } );
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Fonctions utilisées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function afficher_form_gestion( mode , user_id , message_info )
    {
      $('#f_action').val(mode);
      $('#f_user_id').val(user_id);
      // pour finir
      $('#form_gestion h2').html(mode[0].toUpperCase() + mode.substring(1) + " une liaison");
      if(mode=='ajouter')
      {
        $('#gestion_ajouter').show(0);
        $('#gestion_supprimer').hide(0);
      }
      else
      {
        $('#gestion_delete_liaison').html(message_info);
        $('#gestion_ajouter').hide(0);
        $('#gestion_supprimer').show(0);
      }
      $('#ajax_msg_gestion').removeAttr('class').html("");
      $('#form_gestion label[generated=true]').removeAttr('class').html("");
      $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':700 , 'centerOnScroll':true } );
      if(mode=='ajouter') { $('#f_login').focus(); }
    }

    /**
     * Ajouter une liaison : mise en place du formulaire
     * @return void
     */
    var ajouter = function()
    {
      mode = $(this).attr('class');
      // Afficher le formulaire
      afficher_form_gestion( mode , '' /*user_id*/ , 'aucun' /*message_info*/ );
    };

    /**
     * Supprimer une liaison : mise en place du formulaire
     * @return void
     */
    var supprimer = function()
    {
      mode = $(this).attr('class');
      var objet_tr     = $(this).parent().parent();
      var objet_tds    = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var user_id      = objet_tr.attr('id').substring(3);
      var message_info = objet_tds.eq(0).text()+" ("+objet_tds.eq(1).text()+")";
      // Afficher le formulaire
      afficher_form_gestion( mode , user_id , message_info );
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
    $('#table_action').on( 'click' , 'q.supprimer'     , supprimer );

    $('#form_gestion').on( 'click' , '#bouton_annuler' , annuler );
    $('#form_gestion').on( 'click' , '#bouton_valider' , function(){formulaire.submit();} );
    $('#form_gestion').on( 'keyup' , 'input'           , function(e){intercepter(e);} );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Basculer vers un autre compte
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du deuxième formulaire : Ajouter une liaison | Retirer une liaison
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $('#form_gestion');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          f_login    : { required:function(){return mode=='ajouter';} , maxlength:LOGIN_LONGUEUR_MAX },
          f_password : { required:function(){return mode=='ajouter';} , maxlength:PASSWORD_LONGUEUR_MAX }
        },
        messages :
        {
          f_login    : { required:"nom d'utilisateur manquant" , maxlength:LOGIN_LONGUEUR_MAX+" caractères maximum" },
          f_password : { required:"mot de passe manquant"      , maxlength:PASSWORD_LONGUEUR_MAX+" caractères maximum" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element) { element.after(error); }
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
      target : "#ajax_ajouter",
      beforeSubmit : test_form_avant_envoi,
      error : retour_form_erreur,
      success : retour_form_valide
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions);
        return false;
      }
    );

    // Fonction précédant l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi(formData, jqForm, options)
    {
      $('#ajax_msg_gestion').removeAttr('class').html("");
      var readytogo = validation.form();
      if(readytogo)
      {
        $('#form_gestion button').prop('disabled',true);
        $('#ajax_msg_gestion').attr('class','loader').html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      $('#form_gestion button').prop('disabled',false);
      $('#ajax_msg_gestion').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseJSON)
    {
      initialiser_compteur();
      $('#form_gestion button').prop('disabled',false);
      if(responseJSON['statut']==true)
      {
        var msg = (mode=='ajouter') ? 'Liaison réussie' : 'Retrait réussi' ;
        $('#ajax_msg_gestion').attr('class','valide').html(msg+" ; actualisation en cours&hellip;");
        document.location.reload();
      }
      else
      {
        $('#ajax_msg_gestion').attr('class','alerte').html(responseJSON['value']);
      }
    }

  }
);
