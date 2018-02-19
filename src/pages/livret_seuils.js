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
    // Alerter sur la nécessité de valider
    // Afficher / masquer le tableau des échelles
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("input").change
    (
      function()
      {
        var tab_infos = $(this).attr('id').split('_');
        var objet     = tab_infos[0];
        var page_ref  = tab_infos[1];
        var colonne   = tab_infos[2];
        $('#ajax_msg_'+page_ref).attr('class','alerte').html("Enregistrer pour confirmer.");
        if( objet=='choix' )
        {
          $('#table_'+page_ref+'_position').hide(0);
          $('#table_'+page_ref+'_objectif').hide(0);
          if( ( $(this).attr('value') == 'position' ) || ( $(this).attr('value') == 'objectif' ) )
          {
            $('#table_'+page_ref+'_'+colonne).show(0);
          }
          else
          {
          }
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Revenir aux seuils par défaut
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("button.retourner").click
    (
     function()
     {
       var obj_tr = $(this).parent().parent();
       obj_tr.find('input[type=number]').each
       (
          function()
          {
            $(this).val( $(this).attr('data-defaut') );
          }
        );
        var tab_infos = obj_tr.parent().parent().attr('id').split('_');
        var page_ref  = tab_infos[1];
        $('#ajax_msg_'+page_ref).attr('class','alerte').html("Enregistrer pour confirmer.");
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Enregistrer une configuration
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("button.parametre").click
    (
      function()
      {
        var obj_form  = $(this).parent().parent();
        var page_ref  = obj_form.attr('id').substring(5); // 'form_' + id
        var obj_label = $('#ajax_msg_'+page_ref);
        // Vérifications
        var table = false;
        if( !$('#choix_'+page_ref+'_moyenne').length )
        {
          table = 'table_'+page_ref+'_maitrise';
        }
        else if( $('#choix_'+page_ref+'_position').prop('checked') )
        {
          table = 'table_'+page_ref+'_position';
        }
        else if( $('#choix_'+page_ref+'_objectif').prop('checked') )
        {
          table = 'table_'+page_ref+'_objectif';
        }
        if( table )
        {
          var substr_length = 6 + page_ref.length + 4; // 'seuil_' + ref + '_ii_' + 'min' | 'max'
          var tab_valeur    = new Array();
          var val_memo      = -1 ;
          var obj_input     = $('#'+table).find('input[type=number]');
          var nb_valeurs    = obj_input.length;
          var nb_verifs     = 0;
          var num_valeur    = 0;
          obj_input.each
          (
            function()
            {
              var saisie = $(this).val();
              var valeur = parseInt(saisie,10);
              var id     = $(this).attr('id');
              var seuil  = id.substring(substr_length);
              num_valeur = tab_valeur.push(id);
              if( isNaN(saisie) || ( parseFloat(saisie) != valeur ) )
              {
                obj_label.attr('class','erreur').html("Nombre entier requis.");
                $(this).focus();
                return false;
              }
              if( ( num_valeur == 1 ) && ( valeur != 0 ) )
              {
                obj_label.attr('class','erreur').html("Valeur 0 requise.");
                $(this).focus();
                return false;
              }
              if( ( num_valeur == nb_valeurs ) && ( valeur != 100 ) )
              {
                obj_label.attr('class','erreur').html("Valeur 100 requise.");
                $(this).focus();
                return false;
              }
              if( valeur < 0 )
              {
                obj_label.attr('class','erreur').html("Nombre positif requis.")
                $(this).focus();
                return false;
              }
              if( valeur > 100 )
              {
                obj_label.attr('class','erreur').html("Nombre inférieur à 100 requis.");
                $(this).focus();
                return false;
              }
              if( ( seuil == 'min' ) && ( valeur != val_memo + 1 ) )
              {
                obj_label.attr('class','erreur').html("Seuils consécutifs requis.");
                $(this).focus();
                return false;
              }
              if( ( seuil == 'max' ) && ( valeur <= val_memo ) )
              {
                obj_label.attr('class','erreur').html("Valeurs croissantes requises.");
                $(this).focus();
                return false;
              }
              val_memo = valeur;
              nb_verifs++;
            }
          );
          if( nb_verifs < nb_valeurs )
          {
            return false;
          }
        }
        obj_label.attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=enregistrer_choix'+'&f_page_ref='+page_ref+'&'+obj_form.serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              obj_label.attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==false)
              {
                obj_label.attr('class','alerte').html(responseJSON['value']);
                return false;
              }
              else
              {
                obj_label.attr('class','valide').html("Configuration enregistrée !");
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Modifier une légende
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#cadre_bas').on
    (
      'click' ,
      'q.modifier' ,
      function()
      {
        var obj_q = $(this);
        var obj_b = obj_q.prev();
        var ids   = obj_b.attr('id');
        var legende = obj_b.html();
        obj_b.replaceWith('<input id="'+ids+'" name="'+ids+'" type="text" value="" size="15" maxlength="40" /><q class="valider"></q>');
        obj_q.remove();
        $('#'+ids).val(unescapeHtml(legende)).focus();
      }
    );

    $('#cadre_bas').on
    (
      'click' ,
      'q.valider' ,
      function()
      {
        var obj_q     = $(this);
        var obj_input = obj_q.prev();
        var ids     = obj_input.attr('id');
        var legende = obj_input.val();
        if(legende)
        {
          var tab_infos = ids.split('_');
          var colonne_id = tab_infos[1];
          $.fancybox( '<label class="loader">'+"En cours&hellip;"+'</label>' , {'centerOnScroll':true} );
          $.ajax
          (
            {
              type : 'POST',
              url : 'ajax.php?page='+PAGE,
              data : 'csrf='+CSRF+'&f_action=memoriser_legende'+'&f_colonne_id='+colonne_id+'&f_colonne_legende='+encodeURIComponent(legende),
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
                  obj_input.replaceWith('<b id="'+ids+'"></b><q class="modifier" title="Modifier la légende."></q>');
                  obj_q.remove();
                  $('b[id$='+colonne_id+'_legende]').html(unescapeHtml(legende));
                  $('input[id$='+colonne_id+'_legende]').val(unescapeHtml(legende));
                  $.fancybox.close();
                }
              }
            }
          );
        }
      }
    );

    $('#cadre_bas').on(
      'keyup' ,
      'input' ,
      function(e)
      {
        if(e.which==13)  // touche entrée
        {
          $(this).next().click();
        }
      }
    );

  }
);
