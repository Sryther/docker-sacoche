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
    $('#table_action').tablesorter({ headers:{0:{sorter:'date_fr'},6:{sorter:false},7:{sorter:false},8:{sorter:false}} });
    var tableau_tri = function(){ $('#table_action').trigger( 'sorton' , [ [[1,0],[2,0]] ] ); };
    var tableau_maj = function(){ $('#table_action').trigger( 'update' , [ true ] ); };
    tableau_tri();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Fonctions utilisées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Supprimer une demande : mise en place du formulaire
     * @return void
     */
    var supprimer = function()
    {
      mode = $(this).attr('class');
      var objet_tr  = $(this).parent().parent();
      var objet_tds = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var tab_ids   = objet_tr.attr('id').split('_');
      var matiere   = objet_tds.eq(1).html();
      var item      = objet_tds.eq(3).text();
      // Reporter les données
      $('#f_action').val(mode);
      $('#f_demande_id').val(tab_ids[1]);
      $('#f_item_id'   ).val(tab_ids[2]);
      $('#f_matiere_id').val(tab_ids[3]);
      $('#f_prof_id'   ).val(tab_ids[4]);
      $('#gestion_delete_identite').html(matiere+' | '+item);
      // pour finir
      $('#ajax_msg_gestion').removeAttr('class').html("");
      $('#form_gestion label[generated=true]').removeAttr('class').html("");
      $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Appel des fonctions en fonction des événements
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('q.supprimer').click( supprimer );
    $('#bouton_annuler').click( annuler );
    $('#bouton_valider').click( function(){formulaire.submit();} );


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
        },
        messages :
        {
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element) { $('#ajax_msg_gestion').after(error); }
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
        $('#ids_'+$('#f_demande_id').val()+'_'+$('#f_item_id').val()+'_'+$('#f_matiere_id').val()+'_'+$('#f_prof_id').val()).remove();
        $.fancybox.close();
        mode = false;
      }
    }

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
        var obj_td     = obj_q.parent();
        // Récupérer les informations de la ligne concernée
        var tab_ids    = obj_td.parent().attr('id').split('_');
        var demande_id = tab_ids[1];
        var item_id    = tab_ids[2];
        var score      = $(this).prev('i').html();
        var debut_date = $(this).attr('data-debut_date');
        score = (typeof(score)!=='undefined') ? parseInt(score,10) : -1 ;
        obj_q.removeAttr('class');
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+'actualiser_score'+'&f_demande_id='+demande_id+'&f_item_id='+item_id+'&score='+score+'&f_debut_date='+debut_date,
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

  }
);
