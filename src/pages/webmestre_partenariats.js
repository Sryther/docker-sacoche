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
    $('#table_action').tablesorter({ headers:{5:{sorter:false},6:{sorter:false}} });
    var tableau_tri = function(){ $('#table_action').trigger( 'sorton' , [ [[1,0]] ] ); };
    var tableau_maj = function(){ $('#table_action').trigger( 'update' , [ true ] ); };
    tableau_tri();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Fonctions utilisées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function afficher_form_gestion( mode , id , denomination , nom , prenom , courriel , connecteurs )
    {
      $('#f_action').val(mode);
      $('#f_id').val(id);
      $('#f_denomination').val(denomination);
      $('#f_nom').val(nom);
      $('#f_prenom').val(prenom);
      $('#f_courriel').val(courriel);
      $('#f_connecteurs').val(connecteurs);
      // pour finir
      $('#form_gestion h2').html(mode[0].toUpperCase() + mode.substring(1) + " un partenaire conventionné");
      if(mode=='supprimer')
      {
        $('#gestion_delete_identite').html(denomination);
        $('#gestion_edit').hide(0);
        $('#gestion_delete').show(0);
        $('#gestion_generer_mdp').hide(0);
      }
      else if(mode=='initialiser_mdp')
      {
        $('#gestion_initialiser_mdp_identite').html(denomination);
        $('#gestion_edit').hide(0);
        $('#gestion_delete').hide(0);
        $('#gestion_generer_mdp').show(0);
      }
      else
      {
        $('#gestion_edit').show(0);
        $('#gestion_delete').hide(0);
        $('#gestion_generer_mdp').hide(0);
      }
      $('#ajax_msg_gestion').removeAttr('class').html("");
      $('#form_gestion label[generated=true]').removeAttr('class').html("");
      $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':800 , 'centerOnScroll':true } );
      if(mode=='ajouter') { $('#f_denomination').focus(); }
    }

    /**
     * Ajouter un partenaire conventionné : mise en place du formulaire
     * @return void
     */
    var ajouter = function()
    {
      mode = $(this).attr('class');
      // Afficher le formulaire
      afficher_form_gestion( mode , '' /*id*/ , '' /*denomination*/ , '' /*nom*/ , '' /*prenom*/ , '' /*courriel*/ , '' /*connecteurs*/ );
    };

    /**
     * Modifier un partenaire conventionné (éventuellement que le mdp) : mise en place du formulaire
     * @return void
     */
    var modifier = function()
    {
      mode = $(this).attr('class');
      var objet_tds = $(this).parent().parent().find('td');
      // Récupérer les informations de la ligne concernée
      var id           = objet_tds.eq(0).html();
      var denomination = objet_tds.eq(1).html();
      var nom          = objet_tds.eq(2).html();
      var prenom       = objet_tds.eq(3).html();
      var courriel     = objet_tds.eq(4).html();
      var connecteurs  = objet_tds.eq(5).html();
      // Afficher le formulaire
      afficher_form_gestion( mode , id , unescapeHtml(denomination) , unescapeHtml(nom) , unescapeHtml(prenom) , unescapeHtml(courriel) , unescapeHtml(connecteurs) );
    };

    /**
     * Retirer un partenaire conventionné : mise en place du formulaire
     * @return void
     */
    var supprimer = function()
    {
      mode = $(this).attr('class');
      var objet_tds = $(this).parent().parent().find('td');
      // Récupérer les informations de la ligne concernée
      var id           = objet_tds.eq(0).html();
      var denomination = objet_tds.eq(1).html();
      // Afficher le formulaire
      afficher_form_gestion( mode , id , unescapeHtml(denomination) , '' /*nom*/ , '' /*prenom*/ , '' /*courriel*/ , '' /*connecteurs*/ );
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
    $('#table_action').on( 'click' , 'q.initialiser_mdp' , modifier );
    $('#table_action').on( 'click' , 'q.supprimer'       , supprimer );

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
          f_denomination : { required:true , maxlength:100 },
          f_nom          : { required:true , maxlength:NOM_LONGUEUR_MAX },
          f_prenom       : { required:true , maxlength:PRENOM_LONGUEUR_MAX },
          f_courriel     : { required:true , email:true , maxlength:COURRIEL_LONGUEUR_MAX },
          f_connecteurs  : { required:true , maxlength:255 }
        },
        messages :
        {
          f_denomination : { required:"dénomination manquante" , maxlength:"100 caractères maximum" },
          f_nom          : { required:"nom manquant"    , maxlength:NOM_LONGUEUR_MAX+" caractères maximum" },
          f_prenom       : { required:"prénom manquant" , maxlength:PRENOM_LONGUEUR_MAX+" caractères maximum" },
          f_courriel     : { required:"courriel manquant" , email:"courriel invalide", maxlength:COURRIEL_LONGUEUR_MAX+" caractères maximum" },
          f_connecteurs  : { required:"liste des connecteurs manquante" , maxlength:"255 caractères maximum" }
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
      if(mode!='initialiser_mdp')
      {
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
      else
      {
        if(responseJSON['statut']==false)
        {
          $('#ajax_msg_gestion').attr('class','alerte').html(responseJSON['value']);
        }
        else
        {
          $.fancybox( '<p>'+responseJSON['value']+'</p>' , {'centerOnScroll':true} );
        }
      }
    }

  }
);
