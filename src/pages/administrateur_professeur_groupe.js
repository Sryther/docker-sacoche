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

    // Il est plus simple d'initialiser à 0 les valeurs manquantes que de tenter d'ajouter et surtout de supprimer des éléments par la suite
    var tab_groupe = new Array(); $("#f_groupe input").each(function(){tab_groupe.push($(this).val());});
    var tab_prof   = new Array(); $("#f_prof   input").each(function(){tab_prof.push($(this).val());});
    // On compare par rapport au tableau js pour savoir ce qui a changé
    for ( var key_groupe in tab_groupe )
    {
      for ( var key_prof in tab_prof )
      {
        var groupe_id = tab_groupe[key_groupe];
        var prof_id   = tab_prof[key_prof];
        if(typeof(tab_join[groupe_id][prof_id])=='undefined')
        {
          tab_join[groupe_id][prof_id] = 0;
        }
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Alerter au changement d'un élément de formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_select').on
    (
      'change',
      'select, input',
      function()
      {
        $('#ajax_msg').attr('class','alerte').html("Pensez à valider vos choix !");
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Réagir au clic sur un bouton (soumission du formulaire)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#ajouter , #retirer').click
    (
      function()
      {
        var action = $(this).attr('id');
        if( !$("#f_prof input:checked").length || !$("#f_groupe input:checked").length )
        {
          $('#ajax_msg').attr('class','erreur').html("Sélectionnez dans les deux listes !");
          return false;
        }
        // On récupère les id des profs et des groupes concernés
        var select_groupe = new Array(); $("#f_groupe input:checked").each(function(){select_groupe.push($(this).val());});
        var select_prof   = new Array(); $("#f_prof   input:checked").each(function(){select_prof.push($(this).val());});
        // On compare par rapport au tableau js pour savoir ce qui a changé
        var tab_modifs = new Array();
        for ( var key_groupe in select_groupe )
        {
          for ( var key_prof in select_prof )
          {
            var groupe_id = select_groupe[key_groupe];
            var prof_id   = select_prof[key_prof];
            if( ( (tab_join[groupe_id][prof_id]==0) && (action=='ajouter') ) || ( (tab_join[groupe_id][prof_id]>0) && (action=='retirer') ) )
            {
              tab_modifs.push(groupe_id+'_'+prof_id);
            }
          }
        }
        if(!tab_modifs.length)
        {
          $('#ajax_msg').attr('class','erreur').html("Aucune nouveauté détectée !");
          return false;
        }
        // On envoie les changements
        $('#form_select button').prop('disabled',true);
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+action+'&tab_modifs='+tab_modifs,
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
                maj_tableaux(action,tab_modifs);
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
    // Mettre à jour les tableaux bilans et le javascript
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_tableaux(action,tab_modifs)
    {
      var total_groupe = new Array();
      var total_prof   = new Array();
      // lignes et javascript
      for ( var key in tab_modifs )
      {
        var id_modifs = tab_modifs[key].split('_');
        var groupe_id = id_modifs[0];
        var prof_id   = id_modifs[1];
        id_modifs[0];
        if(action=='ajouter')
        {
          var prof_nom   = $('#f_prof_'+prof_id    ).parent().text();
          var groupe_nom = $('#f_groupe_'+groupe_id).parent().text();
          $('#gpb_'+groupe_id).append('<div id="gp_'+groupe_id+'_'+prof_id+'">'+prof_nom+'</div>');
          $('#pgb_'+prof_id  ).append('<div id="pg_'+prof_id+'_'+groupe_id+'">'+groupe_nom+'</div>');
          tab_join[groupe_id][prof_id] = 1;
        }
        else if(action=='retirer')
        {
          $('#gp_'+groupe_id+'_'+prof_id).remove();
          $('#pg_'+prof_id+'_'+groupe_id).remove();
          tab_join[groupe_id][prof_id] = 0;
        }
        total_groupe[groupe_id] = true;
        total_prof[prof_id]     = true;
      }
      // totaux
      for ( var groupe_id in total_groupe )
      {
        var nb_profs = $('#gpb_'+groupe_id+' div').length;
        var s_profs = (nb_profs>1) ? 's' : '' ;
        $('#gpf_'+groupe_id).html(nb_profs+' professeur'+s_profs);
      }
      for ( var prof_id in total_prof )
      {
        var nb_groupes = $('#pgb_'+prof_id+' div').length;
        var s_groupes = (nb_groupes>1) ? 's' : '' ;
        $('#pgf_'+prof_id).html(nb_groupes+' groupe'+s_groupes);
      }
    }

  }
);
