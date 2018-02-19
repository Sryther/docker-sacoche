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
// Afficher / Masquer le formulaire de jointure aux périodes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('select[name=f_periode]').change
    (
      function()
      {
        if( $(this).val() )
        {
          $(this).next().show();
        }
        else
        {
          $(this).next().hide();
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du formulaire form_chefetabl
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Alerter sur la nécessité de valider
    $("#f_chefetabl").change
    (
      function()
      {
        $('#ajax_msg_chefetabl').attr('class','alerte').html("Valider pour confirmer.");
      }
    );

    $('#bouton_valider_chefetabl').click
    (
      function()
      {
        var f_chefetabl = $('#f_chefetabl option:selected').val();
        if(f_chefetabl==0)
        {
          $('#ajax_msg_chefetabl').attr('class','erreur').html("Responsable manquant !");
          $('#f_chefetabl').focus();
          return false;
        }
        $("#bouton_valider_chefetabl").prop('disabled',true);
        $('#ajax_msg_chefetabl').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_chefetabl='+f_chefetabl,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $("#bouton_valider_chefetabl").prop('disabled',false);
              $('#ajax_msg_chefetabl').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $("#bouton_valider_chefetabl").prop('disabled',false);
              if(responseJSON['statut']==true)
              {
                $('#ajax_msg_chefetabl').attr('class','valide').html("Choix appliqué.");
                $('select[name=f_chef]').each( function(){$(this).find('option[value='+f_chefetabl+']').prop('selected',true);} );
              }
              else
              {
                $('#ajax_msg_chefetabl').attr('class','alerte').html(responseJSON['value']);
              }
              return false;
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Alerter sur la nécessité de valider
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').find('select').change
    (
      function()
      {
        var groupe_id = $(this).parent().data('id');
        $(this).nextAll('span').html('<button type="button" class="valider">Valider.</button><label class="alerte">Pensez à enregistrer !</label>');
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Soumission du formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on
    (
      'click',
      'button.valider',
      function()
      {
        var obj_bouton = $(this);
        var obj_label  = obj_bouton.next('label');
        var obj_span   = obj_bouton.parent();
        var obj_td     = obj_span.parent();
        var groupe_id  = obj_td.data('id');
        var f_periode  = $('#f_periode_' +groupe_id).val();
        var f_jointure = $('#f_jointure_'+groupe_id).val();
        var f_cycle    = $('#f_cycle_'   +groupe_id).val();
        var f_chef     = $('#f_chef_'    +groupe_id).val();
        if( (f_chef==0) && (f_periode || f_cycle) )
        {
          obj_label.attr('class','erreur').html("Responsable manquant !");
          $('#f_chef_'+groupe_id).focus();
          return false;
        }
        obj_bouton.prop('disabled',true);
        obj_label.attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_groupe='+groupe_id+'&f_periode='+f_periode+'&f_jointure='+f_jointure+'&f_cycle='+f_cycle+'&f_chef='+f_chef,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              obj_bouton.prop('disabled',false);
              obj_label.attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==false)
              {
                obj_bouton.prop('disabled',false);
                obj_label.attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                obj_bouton.remove();
                obj_label.attr('class','valide').html("Choix enregistrés !").fadeOut( 2000, function() { obj_label.remove(); } );
                var td_class = 'bj';
                var vignettes = '';
                if(f_periode)
                {
                  vignettes += '<a href="'+SERVEUR_LSU_PDF+'livret_'+f_periode+'.pdf" class="fancybox" rel="gallery_'+groupe_id+'" data-titre="'+$('#f_periode_'+groupe_id+' option:selected').text()+'"><span class="livret livret_'+f_periode+'"></span></a>';
                  td_class = 'bv';
                }
                if(f_cycle)
                {
                  vignettes += '<a href="'+SERVEUR_LSU_PDF+'livret_'+f_cycle+'.pdf" class="fancybox" rel="gallery_'+groupe_id+'" data-titre="'+$('#f_cycle_'+groupe_id+' option:selected').text()+'"><span class="livret livret_'+f_cycle+'"></span></a>';
                  td_class = 'bv';
                }
                obj_td.prev('td').attr('class',td_class);
                obj_td.next('td').html(vignettes);
              }
            }
          }
        );
      }
    );

  }
);
