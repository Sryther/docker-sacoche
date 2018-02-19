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
    var tab_classe = new Array(); $("#f_classe input").each(function(){tab_classe.push($(this).val());});
    var tab_prof   = new Array(); $("#f_prof   input").each(function(){tab_prof.push($(this).val());});
    // On compare par rapport au tableau js pour savoir ce qui a changé
    for ( var key_classe in tab_classe )
    {
      for ( var key_prof in tab_prof )
      {
        var classe_id = tab_classe[key_classe];
        var prof_id   = tab_prof[key_prof];
        if(typeof(tab_join[classe_id][prof_id])=='undefined')
        {
          tab_join[classe_id][prof_id] = 0;
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
        if( !$("#f_prof input:checked").length || !$("#f_classe input:checked").length )
        {
          $('#ajax_msg').attr('class','erreur').html("Sélectionnez dans les deux listes !");
          return false;
        }
        // On récupère les id des profs et des classes concernés
        var select_classe = new Array(); $("#f_classe input:checked").each(function(){select_classe.push($(this).val());});
        var select_prof   = new Array(); $("#f_prof   input:checked").each(function(){select_prof.push($(this).val());});
        // On compare par rapport au tableau js pour savoir ce qui a changé
        var tab_modifs = new Array();
        for ( var key_classe in select_classe )
        {
          for ( var key_prof in select_prof )
          {
            var classe_id = select_classe[key_classe];
            var prof_id   = select_prof[key_prof];
            if( ( (tab_join[classe_id][prof_id]==0) && (action=='ajouter') ) || ( (tab_join[classe_id][prof_id]>0) && (action=='retirer') ) )
            {
              tab_modifs.push(classe_id+'_'+prof_id);
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
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                $('#ajax_msg').attr('class','valide').html("Demande réalisée !");
                maj_tableaux(action,tab_modifs);
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
      var total_classe = new Array();
      var total_prof   = new Array();
      // lignes et javascript
      for ( var key in tab_modifs )
      {
        var id_modifs = tab_modifs[key].split('_');
        var classe_id = id_modifs[0];
        var prof_id   = id_modifs[1];
        id_modifs[0];
        if(action=='ajouter')
        {
          var prof_nom   = $('#f_prof_'+prof_id    ).parent().text();
          var classe_nom = $('#f_classe_'+classe_id).parent().text();
          $('#cpb_'+classe_id).append('<div id="cp_'+classe_id+'_'+prof_id+'" class="off"><input type="checkbox" id="'+classe_id+'cp'+prof_id+'" value="" /> <label for="'+classe_id+'cp'+prof_id+'">'+prof_nom+'</label></div>');
          $('#pcb_'+prof_id  ).append('<div id="pc_'+prof_id+'_'+classe_id+'" class="off"><input type="checkbox" id="'+prof_id+'pc'+classe_id+'" value="" /> <label for="'+prof_id+'pc'+classe_id+'">'+classe_nom+'</label></div>');
          tab_join[classe_id][prof_id] = 1;
        }
        else if(action=='retirer')
        {
          $('#cp_'+classe_id+'_'+prof_id).remove();
          $('#pc_'+prof_id+'_'+classe_id).remove();
          tab_join[classe_id][prof_id] = 0;
        }
        total_classe[classe_id] = true;
        total_prof[prof_id]     = true;
      }
      // totaux
      for ( var classe_id in total_classe )
      {
        var nb_profs = $('#cpb_'+classe_id+' div').length;
        var s_profs = (nb_profs>1) ? 's' : '' ;
        $('#cpf_'+classe_id).html(nb_profs+' professeur'+s_profs);
      }
      for ( var prof_id in total_prof )
      {
        var nb_classes = $('#pcb_'+prof_id+' div').length;
        var s_classes = (nb_classes>1) ? 's' : '' ;
        $('#pcf_'+prof_id).html(nb_classes+' classe'+s_classes);
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Ajouter / Retirer une affectation en tant que professeur principal
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('table.affectation').on
    (
      'click',
      'input[type=checkbox]',
      function()
      {
        var obj_bouton = $(this);
        var action     = (obj_bouton.is(':checked')) ? 'ajouter_pp' : 'retirer_pp' ;
        var tab_id     = obj_bouton.parent().attr('id').split('_');
        var prof_id    = (tab_id[0]=='pc') ? tab_id[1] : tab_id[2] ;
        var classe_id  = (tab_id[0]=='cp') ? tab_id[1] : tab_id[2] ;
        var check_old  = (action=='ajouter_pp') ? false : true ;
        var check_new  = (action=='ajouter_pp') ? true : false ;
        var class_old  = (action=='ajouter_pp') ? 'off' : 'on' ;
        var class_new  = (action=='ajouter_pp') ? 'on' : 'off' ;
        var js_val     = (action=='ajouter_pp') ? 2 : 1 ;
        obj_bouton.prop('disabled',true).parent().attr('class','load');
        $.ajax
        (
          {
            type : 'POST',
            url  : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+action+'&prof_id='+prof_id+'&classe_id='+classe_id,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              obj_bouton.prop('disabled',false).prop('checked',check_old).parent().attr('class',class_old);
              $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+' Veuillez recommencer.'+'</label>' , {'centerOnScroll':true} );
              return false;
            },
            success : function(responseJSON)
            {
              if(responseJSON['statut']==false)
              {
                $.fancybox( '<label class="alerte">'+responseJSON['value']+'</label>' , {'centerOnScroll':true} );
                obj_bouton.prop('disabled',false).prop('checked',check_old).parent().attr('class',class_old);
              }
              else
              {
                obj_bouton.prop('disabled',false).parent().attr('class',class_new);
                // MAJ tableaux bilans et javascript
                var id_autre = (tab_id[0]=='cp') ? prof_id+'pc'+classe_id : classe_id+'cp'+prof_id ;
                $('#'+id_autre).prop('checked',check_new).parent().attr('class',class_new);
                tab_join[classe_id][prof_id] = js_val;
              }
            }
          }
        );
      }
    );

  }
);
