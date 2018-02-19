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

    /**
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Pour les vignettes du livret
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     */

    // On utilise "data-titre" au lieu de "title" d'une part parce qu'on n'en a pas besoin dans l'infobulle et d'autre part parce que sinon à cause de l'infobulle fancybox ne récupère pas le titre de la vignette cliquée.
    $(".fancybox").fancybox({
      type : 'iframe',
      beforeLoad: function() {
        this.title = $(this.element).attr('data-titre');
      }
    });

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Charger les liaisons des items vers le socle commun
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_liaisons_cycle( cycle_id , cycle_nom )
    {
      $('#socle_liaisons').html('<label class="loader">'+"En cours&hellip;"+'</label>');
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=export_fichier',
          data : 'csrf='+CSRF+'&f_type='+"jointure_socle2016_matiere"+'&f_cycle='+cycle_id+'&f_cycle_nom='+encodeURIComponent(cycle_nom),
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#socle_liaisons').html('<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+'</label>');
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==true)
            {
              $('#socle_liaisons').html(responseJSON['value']);
            }
            else
            {
              $('#socle_liaisons').html('<label class="alerte">'+responseJSON['value']+'</label>');
            }
          }
        }
      );
    }

    if( typeof(cycle_id) !== 'undefined' )
    {
      maj_liaisons_cycle( cycle_id , cycle_nom );
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Choix général du type de liaison aux référentiels
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var prompt_etapes_confirmer_type_liaison_deux_etapes = {
      etape_1: {
        title   : 'Demande de confirmation (1/2)',
        html    : "Changer le type de liaison supprime toutes les associations antérieures !<br />Souhaitez-vous vraiment changer le type de liaison aux référentiels ?",
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
          else
          {
            $('#rubrique_join option[value='+rubrique_join+']').prop('selected',true);
          }
        }
      },
      etape_2: {
        title   : 'Demande de confirmation (2/2)',
        html    : "Attention : dernière demande de confirmation !!!<br />Les liaisons des référentiels aux rubriques du livret seront toutes supprimées !<br />Est-ce définitivement votre dernier mot ???",
        buttons : {
          "Oui, j'insiste !" : true ,
          "Non, surtout pas !" : false
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            envoyer_type_liaison_confirmee();
            return true;
          }
          else
          {
            $('#rubrique_join option[value='+rubrique_join+']').prop('selected',true);
          }
        }
      }
    };

    $('#rubrique_join').change
    (
      function()
      {
        $.prompt(prompt_etapes_confirmer_type_liaison_deux_etapes);
        return false;
      }
    );

    function envoyer_type_liaison_confirmee()
    {
      rubrique_join = $('#rubrique_join option:selected').val();
      $.fancybox( '<label class="loader">En cours&hellip;</label>' , {'centerOnScroll':true} );
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=choisir_type_liaison'+'&f_rubrique_type='+rubrique_type+'&f_rubrique_join='+rubrique_join,
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
              return false;
            }
            else
            {
              document.location.reload();
            }
          }
        }
      );
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Ajouter des liaisons
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('q.ajouter').click
    (
      function()
      {
        if( $("#form_select input:checked").length == 0 )
        {
          $.fancybox( '<label class="erreur">Cochez un ou plusieurs éléments à ajouter !</label>' , {'centerOnScroll':true} );
          return false;
        }
        var obj_rubrique = $(this).parent().next();
        var rubrique_id = obj_rubrique.attr('id').substring(11); // 'f_rubrique_' + id
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=ajouter'+'&f_rubrique_type='+rubrique_type+'&f_rubrique_join='+rubrique_join+'&f_rubrique='+rubrique_id+'&'+$("#form_select").serialize(),
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
                return false;
              }
              else
              {
                // Retirer l'éventuel signalement d'absence d'élément associé
                obj_rubrique.children('span').remove();
                // Déplacer les éléments restants
                $("#f_elements_rest input:checked").each(function(){
                  var element_id    = $(this).val();
                  var obj_label     = $(this).parent();
                  var element_txt   = obj_label.text();
                  var element_ordre = obj_label.attr('data-ordre');
                  $('#f_elements_used').append(obj_label);
                  obj_rubrique.append('<div id="f_liaison_'+element_id+'_'+rubrique_id+'" data-ordre='+element_ordre+'><q class="annuler" title="Retirer cette association"></q> '+element_txt+'</div>');
                });
                // Copier les éléments déjà reliés
                $("#f_elements_used input:checked").each(function(){
                  var element_id    = $(this).val();
                  var obj_label     = $(this).parent();
                  var element_txt   = obj_label.text();
                  var element_ordre = obj_label.attr('data-ordre');
                  if( ! $('#f_liaison_'+element_id+'_'+rubrique_id).length )
                  {
                    obj_rubrique.append('<div id="f_liaison_'+element_id+'_'+rubrique_id+'" data-ordre='+element_ordre+'><q class="annuler" title="Retirer cette association"></q> '+element_txt+'</div>');
                  }
                });
                $("#form_select input").prop('checked',false).parent().removeAttr('class');
                // Trier les éléments déjà reliés
                tinysort( '#f_elements_used>label' , {attr:'data-ordre'} );
                // Trier les éléments de la rubrique
                tinysort( '#f_rubrique_'+rubrique_id+'>div' , {attr:'data-ordre'} );
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Retirer une liaison
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_rubrique').on
    (
      'click',
      'q.annuler',
      function()
      {
        var obj_liaison   = $(this).parent();
        var obj_rubrique  = obj_liaison.parent();
        var tab_id        = obj_liaison.attr('id').split('_'); // 'f_liaison_' + element_id + '_' + rubrique_id
        var element_id    = tab_id[2];
        var rubrique_id   = tab_id[3];
        var element_txt   = obj_liaison.text();
        var element_ordre = obj_liaison.attr('data-ordre');
        obj_liaison.hide();
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=retirer'+'&f_rubrique_type='+rubrique_type+'&f_rubrique_join='+rubrique_join+'&f_rubrique='+rubrique_id+'&f_element='+element_id,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              obj_liaison.show();
              $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+'</label>' , {'centerOnScroll':true} );
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==false)
              {
                obj_liaison.show();
                $.fancybox( '<label class="alerte">'+responseJSON['value']+'</label>' , {'centerOnScroll':true} );
              }
              else
              {
                // Retirer la liaison + insérer l'éventuel signalement d'absence de liaison
                obj_liaison.remove();
                if( !obj_rubrique.children().length )
                {
                  obj_rubrique.append('<span class="astuce">Aucun élément de <em>SACoche</em> associé.</span>');
                }
                // Déplacer l'élément si besoin + tri des éléments à relier
                if( ! $('div[id^=f_liaison_'+element_id+']').length )
                {
                  var obj_label = $('#f_element_'+element_id).parent();
                  $('#f_elements_rest').append(obj_label);
                  tinysort( '#f_elements_rest>label' , {attr:'data-ordre'} );
                }
              }
            }
          }
        );
      }
    );

  }
);
