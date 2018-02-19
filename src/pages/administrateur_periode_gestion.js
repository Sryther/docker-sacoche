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
    $('#table_action').tablesorter({ headers:{3:{sorter:false}} });
    var tableau_tri = function(){ $('#table_action').trigger( 'sorton' , [ [[0,0],[1,0]] ] ); };
    var tableau_maj = function(){ $('#table_action').trigger( 'update' , [ true ] ); };
    tableau_tri();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Fonctions utilisées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function afficher_form_gestion( mode , id , ordre , nom , livret_texte )
    {
      $('#f_action').val(mode);
      $('#f_id'    ).val(id);
      $('#f_ordre' ).val(ordre);
      $('#f_nom'   ).val(nom);
      $('#f_livret').html(select_livret.replace('>'+livret_texte,' selected>'+livret_texte));
      // pour finir
      $('#form_gestion h2').html(mode[0].toUpperCase() + mode.substring(1) + " une période");
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
      $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
      if(mode!='supprimer') { $('#f_ordre').focus(); }
    }

    /**
     * Ajouter une période : mise en place du formulaire
     * @return void
     */
    var ajouter = function()
    {
      mode = $(this).attr('class');
      // Afficher le formulaire
      afficher_form_gestion( mode , '' /*id*/ , '' /*ordre*/ , '' /*nom*/ , '-' /*livret_texte*/ );
    };

    /**
     * Modifier une période : mise en place du formulaire
     * @return void
     */
    var modifier = function()
    {
      mode = $(this).attr('class');
      var objet_tr   = $(this).parent().parent();
      var objet_tds  = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var id           = objet_tr.attr('id').substring(3);
      var ordre        = objet_tds.eq(0).html();
      var nom          = objet_tds.eq(1).html();
      var livret_texte = objet_tds.eq(2).html();
      // Afficher le formulaire
      afficher_form_gestion( mode , id , ordre , unescapeHtml(nom) , livret_texte /* volontairement sans unescapeHtml() */ );
    };

    /**
     * Dupliquer une période : mise en place du formulaire
     * @return void
     */
    var dupliquer = function()
    {
      mode = $(this).attr('class');
      var objet_tds  = $(this).parent().parent().find('td');
      // Récupérer les informations de la ligne concernée
      var ordre        = objet_tds.eq(0).html();
      var nom          = objet_tds.eq(1).html();
      var livret_texte = objet_tds.eq(2).html();
      ordre++;
      // Afficher le formulaire
      afficher_form_gestion( mode , '' /*id*/ , ordre , unescapeHtml(nom) , livret_texte /* volontairement sans unescapeHtml() */ );
    };

    /**
     * Supprimer une période : mise en place du formulaire
     * @return void
     */
    var supprimer = function()
    {
      mode = $(this).attr('class');
      var objet_tr   = $(this).parent().parent();
      var objet_tds  = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var id         = objet_tr.attr('id').substring(3);
      var nom        = objet_tds.eq(1).html();
      // Afficher le formulaire
      afficher_form_gestion( mode , id , '' /*ordre*/ , unescapeHtml(nom) , '-' /*livret_texte*/ );
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
    $('#table_action').on( 'click' , 'q.dupliquer'     , dupliquer );
    $('#table_action').on( 'click' , 'q.supprimer'     , supprimer );

    $('#form_gestion').on( 'click' , '#bouton_annuler' , annuler );
    $('#form_gestion').on( 'click' , '#bouton_valider' , function(){formulaire.submit();} );
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
          f_ordre  : { required:true  , digits:true , range:[1,99] },
          f_nom    : { required:true  , maxlength:40 },
          f_livret : { required:false }
        },
        messages :
        {
          f_ordre  : { required:"ordre manquant" , digits:"nombre entier requis" , range:"nombre entre 1 et 99" },
          f_nom    : { required:"nom manquant" , maxlength:"40 caractères maximum" },
          f_livret : { }
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
      beforeSubmit : test_form_avant_envoi,
      error : retour_form_erreur,
      success : retour_form_valide
    };

    var prompt_etapes_confirmer_suppression = {
      etape_2: {
        title   : 'Demande de confirmation (2/3)',
        html    : "Les éventuels bilans officiels associés (bulletins, livrets...) seront supprimés !<br />Pour modifier les dates, il faut utiliser le menu [Périodes&nbsp;&amp;&nbsp;classes&nbsp;/&nbsp;groupes]&hellip;<br />Souhaitez-vous vraiment supprimer cette période ?",
        buttons : {
          "Non, c'est une erreur !" : false ,
          "Oui, je confirme !" : true
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            event.preventDefault();
            $('#prompt_indication').html( $('#f_nom').val() );
            $.prompt.goToState('etape_3');
            return false;
          }
          else {
            annuler();
          }
        }
      },
      etape_3: {
        title   : 'Demande de confirmation (3/3)',
        html    : "Attention : dernière demande de confirmation !!!<br />Êtes-vous bien certain de vouloir supprimer la période &laquo;&nbsp;"+'<span id="prompt_indication"></span>'+"&nbsp;&raquo; ?<br />Est-ce définitivement votre dernier mot ???",
        buttons : {
          "Oui, j'insiste !" : true ,
          "Non, surtout pas !" : false
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            formulaire.ajaxSubmit(ajaxOptions); // Pas de $(this) ici...
            return true;
          }
          else {
            annuler();
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
        else if(mode=='supprimer')
        {
          $.prompt(prompt_etapes_confirmer_suppression);
        }
        else
        {
          $(this).ajaxSubmit(ajaxOptions);
        }
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
        please_wait = true;
        $('#form_user button').prop('disabled',true);
        $('#ajax_msg_gestion').attr('class','loader').html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      please_wait = false;
      $('#form_user button').prop('disabled',false);
      $('#ajax_msg_gestion').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseJSON)
    {
      initialiser_compteur();
      please_wait = false;
      $('#form_user button').prop('disabled',false);
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
            $('#table_action tbody tr.vide').remove(); // En cas de tableau avec une ligne vide pour la conformité XHTML
          case 'dupliquer':
            $('#table_action tbody').append(responseJSON['value']);
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
