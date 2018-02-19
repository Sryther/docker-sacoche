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

    var nb_caracteres_max = 250;

    // tri du tableau (avec jquery.tablesorter.js).
    $('#table_action').tablesorter({ headers:{3:{sorter:false},4:{sorter:false}} });
    var tableau_tri = function(){ $('#table_action').trigger( 'sorton' , [ [[0,0],[1,0],[2,0]] ] ); };
    var tableau_maj = function(){ $('#table_action').trigger( 'update' , [ true ] ); };
    tableau_tri();

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Alerter au changement d'un élément de formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_select').on
    (
      'change',
      'select, input',
      function()
      {
        $('#ajax_msg').attr('class','alerte').html("Pensez à valider vos modifications !");
      }
    );


    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Indiquer le nombre de caractères restant autorisés dans le textarea
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#input_commentaire, #f_commentaire').keyup
    (
      function()
      {
        afficher_textarea_reste( $(this) , nb_caracteres_max );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Charger le select f_eleve en ajax
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_eleve(groupe_id,groupe_type)
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_maj_select_eleves',
          data : 'f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type+'&f_statut=1'+'&f_multiple=1'+'&f_selection=0',
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==true)
            {
              $('#ajax_msg').attr('class','valide').html("Affichage actualisé !");
              $('#f_eleve').html(responseJSON['value']);
            }
            else
            {
              $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
            }
          }
        }
      );
    }
    function changer_groupe()
    {
      $("#f_eleve").html('');
      var groupe_val = $("#select_groupe").val();
      if(groupe_val)
      {
        groupe_type = $("#select_groupe option:selected").parent().attr('label');
        groupe_id = groupe_val;
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        maj_eleve(groupe_id,groupe_type);
      }
      else
      {
        $('#ajax_msg').removeAttr('class').html("");
      }
    }
    $("#select_groupe").change
    (
      function()
      {
        changer_groupe();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Afficher / masquer la zone du commentaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#f_modaccomp").change
    (
      function()
      {
        if($(this).val()=='PPRE')
        {
          $('#p_commentaire').show(0).children('textarea').focus();
          afficher_textarea_reste( $('#input_commentaire') , nb_caracteres_max );
        }
        else
        {
          $('#p_commentaire').hide(0);
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Soumission du premier formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_associer').click
    (
      function()
      {
        var f_modaccomp   = $('#f_modaccomp option:selected').val();
        var f_commentaire = $('#input_commentaire').val();
        if(!$("#f_eleve input:checked").length)
        {
          $('#ajax_msg').attr('class','erreur').html("Sélectionnez au moins un élève !");
          return false;
        }
        else if(!f_modaccomp)
        {
          $('#ajax_msg').attr('class','erreur').html("Sélectionnez la modalité d'accompagnement !");
          return false;
        }
        else if( (f_modaccomp=='PPRE') && !f_commentaire )
        {
          $('#ajax_msg').attr('class','erreur').html("Indiquez en commentaire un descriptif du PPRE !");
          return false;
        }
        $('#form_select button').prop('disabled',true);
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        // Grouper les checkbox dans un champ unique afin d'éviter tout problème avec une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque) ou "max input vars" généralement fixé à 1000.
        var tab_eleve = new Array();
        $("#f_eleve input:checked").each
        (
          function()
          {
            tab_eleve.push($(this).val());
          }
        );
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=associer'+'&f_modaccomp='+f_modaccomp+'&f_commentaire='+encodeURIComponent(f_commentaire)+'&f_eleve='+tab_eleve+'&only_groupes_id='+only_groupes_id,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#form_select button').prop('disabled',false);
              $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('#form_select button').prop('disabled',false);
              if(responseJSON['statut']==true)
              {
                $('#ajax_msg').attr('class','valide').html("Demande réalisée !");
                $('#table_action tbody').html(responseJSON['html']);
                if( typeof(responseJSON['script']) !== 'undefined' )
                {
                  eval( responseJSON['script'] );
                }
                tableau_maj();
              }
              else
              {
                $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Intercepter la touche entrée car dans le cas d'un seul champ input de type text la soumission est sinon incontrôlée
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_select').submit
    (
      function()
      {
        $('#bouton_associer').click();
        return false;
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Initialisation : charger le tableau
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    if( typeof(only_groupes_id) !== 'undefined' )
    {
      $('#ajax_msg').addClass('loader').html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=initialiser'+'&only_groupes_id='+only_groupes_id,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            return false;
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==true)
            {
              $('#ajax_msg').removeAttr('class').html("");
              $('#table_action tbody').prepend(responseJSON['html']);
              if( typeof(responseJSON['script']) !== 'undefined' )
              {
                eval( responseJSON['script'] );
              }
              tableau_maj();
            }
            else
            {
              $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
            }
          }
        }
      );
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Suppression d'un dispositif
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on
    (
      'click',
      'q.supprimer',
      function()
      {
        var objet_tr    = $(this).parent().parent();
        var tab_id      = objet_tr.attr('id').split('_'); // id_..._...
        var f_eleve     = tab_id[1];
        var f_modaccomp = tab_id[2];
        objet_tr.hide();
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=retirer'+'&f_modaccomp='+f_modaccomp+'&f_eleve='+f_eleve,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              objet_tr.show();
              $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+'</label>' , {'centerOnScroll':true} );
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==true)
              {
                objet_tr.remove();
              }
              else
              {
                objet_tr.show();
                $.fancybox( '<label class="alerte">'+responseJSON['value']+'</label>' , {'centerOnScroll':true} );
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Modifier un commentaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on
    (
      'click',
      'q.modifier',
      function()
      {
        var objet_tr    = $(this).parent().parent();
        var objet_tds   = objet_tr.find('td');
        var classe_nom  = objet_tds.eq(0).html();
        var eleve_nom   = objet_tds.eq(1).html();
        var tab_id      = objet_tr.attr('id').split('_'); // id_..._...
        var eleve_id    = tab_id[1];
        var commentaire = tab_commentaire[eleve_id];
        $('#b_classe').html(classe_nom);
        $('#b_eleve').html(eleve_nom);
        $('#ppre_eleve').val(eleve_id);
        // pour finir
        $('#ajax_msg_gestion').removeAttr('class').html("");
        $('#form_gestion label[generated=true]').removeAttr('class').html("");
        $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
        $('#f_commentaire').focus().val(unescapeHtml(commentaire));
        afficher_textarea_reste( $('#f_commentaire') , nb_caracteres_max );
      }
    );

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
      if(e.which==13)  // touche entrée
      {
        $('#bouton_valider').click();
      }
      else if(e.which==27)  // touche escape
      {
        $('#bouton_annuler').click();
      }
    }

    // Appel des fonctions en fonction des événements

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
          f_commentaire : { required:true , maxlength:nb_caracteres_max }
        },
        messages :
        {
          f_commentaire : { required:"commentaire manquant" , maxlength:nb_caracteres_max+" caractères maximum" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          $('#ajax_msg_gestion').html(error);
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
        $( '#id_'+$('#ppre_eleve').val()+'_PPRE' ).find('td').eq(3).html( responseJSON['html'] );
        eval( responseJSON['script'] );
        tableau_maj();
        $.fancybox.close();
      }
    }

  }
);
