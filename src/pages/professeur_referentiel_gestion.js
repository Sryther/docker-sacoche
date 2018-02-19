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

    var mode        = false;
    var ids         = false;
    var tab_ids     = new Array();
    var id_mat_niv  = false;

    // tri du tableau (avec jquery.tablesorter.js).
    $('#table_action').tablesorter({ headers:{5:{sorter:'date_fr'},7:{sorter:false}} });
    var tableau_tri = function(){ $('#table_action').trigger( 'sorton' , [ [[6,1]] ] ); };
    var tableau_maj = function(){ $('#table_action').trigger( 'update' , [ true ] ); };
    tableau_tri();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Changement de méthode -> desactiver les limites autorisées suivant les cas
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Tableaux utilisés pour savoir quelles options desactiver
    var tableau_limites_autorisees = new Array();
    tableau_limites_autorisees['geometrique']  = '.1.2.3.4.5.';
    tableau_limites_autorisees['arithmetique'] = '.1.2.3.4.5.6.7.8.9.';
    tableau_limites_autorisees['classique']    = '.1.2.3.4.5.6.7.8.9.10.15.20.30.40.50.0.';
    tableau_limites_autorisees['bestof1']      = '.1.2.3.4.5.6.7.8.9.10.15.20.30.40.50.0.';
    tableau_limites_autorisees['bestof2']      =   '.2.3.4.5.6.7.8.9.10.15.20.30.40.50.0.';
    tableau_limites_autorisees['bestof3']      =     '.3.4.5.6.7.8.9.10.15.20.30.40.50.0.';
    tableau_limites_autorisees['frequencemin'] = '.1.2.3.4.5.6.7.8.9.10.15.20.30.40.50.0.';
    tableau_limites_autorisees['frequencemax'] = '.1.2.3.4.5.6.7.8.9.10.15.20.30.40.50.0.';
    // La fonction qui s'en occupe
    var actualiser_select_limite = function()
    {
      // Déterminer s'il faut modifier l'option sélectionnée
      var limite_valeur            = $('#f_limite option:selected').val();
      var findme                   = '.'+limite_valeur+'.';
      var methode_valeur           = $('#f_methode option:selected').val();
      var chaine_autorisee         = tableau_limites_autorisees[methode_valeur];
      var modifier_limite_selected = (chaine_autorisee.indexOf(findme)==-1) ? true : false ; // 1|3 Si true alors il faudra changer le selected actuel qui ne sera plus dans les nouveaux choix.
      if(modifier_limite_selected)
      {
        modifier_limite_selected = chaine_autorisee.substr(chaine_autorisee.length-2,1) ; // 2|3 On prendra alors la valeur maximale dans les nouveaux choix.
      }
      $("#f_limite option").each
      (
        function()
        {
          limite_valeur = $(this).val();
          findme = '.'+limite_valeur+'.';
          if(chaine_autorisee.indexOf(findme)==-1)
          {
            $(this).prop('disabled',true);
          }
          else
          {
            $(this).prop('disabled',false);
          }
          if(limite_valeur===modifier_limite_selected) // === pour éviter un (false==0) qui sélectionne la 1ère option...
          {
            $(this).prop('selected',true); // 3|3 C'est ici que le selected se fait.
          }
        }
      );
    };
    // Appel de la fonction à chaque changement de méthode
    $(document).on( 'change', '#f_methode', actualiser_select_limite );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Changement de partage -> afficher / masquer la ligne d'information
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var actualiser_partage_information = function()
    {
      if( $('#f_partage option:selected').val()=='oui' )
      {
        $('#ligne_information').show(0);
      }
      else
      {
        $('#ligne_information').hide(0);
      }
    };
    // Appel de la fonction à chaque changement de méthode
    $(document).on( 'change', '#f_partage', actualiser_partage_information );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Changement du nombre de demandes autorisées pour une matière -> soumission
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('select[name=f_eleve_demandes]').change
    (
      function()
      {
        var element = $(this);
        var nb_demandes = $(this).val();
        var matiere_id = $(this).closest('table').attr('id').substring(4);
        element.parent().find('label').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=modifier_nombre_demandes'+'&f_matiere_id='+matiere_id+'&f_nb_demandes='+nb_demandes,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              element.parent().find('label').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==false)
              {
                element.parent().find('label').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                element.parent().find('label').attr('class','valide').html("Valeur enregistrée.");
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur l'image pour Voir un référentiel de son établissement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#div_tableaux').on
    (
      'click',
      'q.voir',
      function()
      {
        ids = $(this).parent().attr('id');
        $.fancybox( '<label class="loader">'+'En cours&hellip;'+'</label>' , {'centerOnScroll':true} );
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=voir_referentiel_etablissement'+'&f_ids='+ids,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+'</label>' , {'centerOnScroll':true} );
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==false)
              {
                $.fancybox( '<label class="alerte">'+responseJSON['value']+'</label>' , {'centerOnScroll':true} );
              }
              else
              {
                $.fancybox( responseJSON['value'].replace('<ul class="ul_m2">','<q class="imprimer_arbre" title="Imprimer le référentiel." />'+'<ul class="ul_m2">') , {'centerOnScroll':true} );
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur l'image pour Modifier le partage d'un référentiel
// Clic sur l'image pour Mettre à jour sur le serveur de partage la dernière version d'un référentiel
// Clic sur l'image pour Modifier le mode de calcul d'un référentiel
// Clic sur l'image pour Supprimer un référentiel
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $(document).on
    (
      'click',
      'q.partager , q.envoyer , q.calculer , q.supprimer',
      function()
      {
        mode       = $(this).attr('class');
        ids        = $(this).parent().attr('id');
        tab_ids    = ids.split('_');
        id_mat_niv = tab_ids[1]+'_'+tab_ids[2];
        var partage     = tab_partage_etat[     id_mat_niv];
        var methode     = tab_calcul_methode[   id_mat_niv];
        var limite      = tab_calcul_limite[    id_mat_niv];
        var retroactif  = tab_calcul_retroactif[id_mat_niv];
        var information = tab_information[      id_mat_niv];
        var matiere_nom = $(this).parent().parent().parent().parent().prev('h2').html();
        var niveau_nom  = $(this).parent().prev().prev().prev().html();
        $('#referentiel_infos').html( matiere_nom + '&nbsp;||&nbsp;' + niveau_nom );
        $('#f_matiere_nom').val(matiere_nom);
        $('#f_niveau_nom').val(niveau_nom);
        $('#f_action').val(mode);
        $('#f_ids').val(ids);
        if( ( tab_ids[1] <= ID_MATIERE_PARTAGEE_MAX ) && ( tab_ids[2] <= ID_NIVEAU_PARTAGE_MAX ) )
        {
          $('#f_partage option[value=oui] , #f_partage option[value=bof] , #f_partage option[value=non]').prop('disabled',false);
          $('#f_partage option[value=hs]').prop('disabled',true);
        }
        else
        {
          $('#f_partage option[value=oui] , #f_partage option[value=bof] , #f_partage option[value=non]').prop('disabled',true);
          $('#f_partage option[value=hs]').prop('disabled',false);
        }
        $('#f_partage    option[value='+partage   +']').prop('selected',true);
        $('#f_methode    option[value='+methode   +']').prop('selected',true);
        $('#f_limite     option[value='+limite    +']').prop('selected',true);
        $('#f_retroactif option[value='+retroactif+']').prop('selected',true);
        $('#f_information').val(information);
        actualiser_select_limite();
        $('#ajax_msg_gestion').removeAttr('class').html("");
        switch (mode)
        {
          case 'partager':
            $('#form_gestion h2').html("Modifier le partage d'un référentiel");
            $('#gestion_partager' ).show(0);
            $('#gestion_calculer' ).hide(0);
            $('#gestion_supprimer').hide(0);
            $('#ligne_partage'    ).show(0);
            actualiser_partage_information();
            break;
          case 'envoyer':
            $('#form_gestion h2').html("Mettre à jour sur le serveur de partage la dernière version d'un référentiel");
            $('#gestion_partager' ).show(0);
            $('#gestion_calculer' ).hide(0);
            $('#gestion_supprimer').hide(0);
            $('#ligne_partage'    ).hide(0);
            actualiser_partage_information();
            break;
          case 'calculer':
            $('#form_gestion h2').html("Modifier le mode de calcul d'un référentiel");
            $('#gestion_partager' ).hide(0);
            $('#gestion_calculer' ).show(0);
            $('#gestion_supprimer').hide(0);
            break;
          case 'supprimer':
            $('#form_gestion h2').html("Supprimer un référentiel");
            $('#gestion_partager' ).hide(0);
            $('#gestion_calculer' ).hide(0);
            $('#gestion_supprimer').show(0);
            break;
        }
        $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':780 , 'centerOnScroll':true } );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Valider la modification du partage d'un référentiel
// Valider la mise à jour sur le serveur de partage de la dernière version d'un référentiel
// Valider la modification du mode de calcul d'un référentiel
// Valider la suppression d'un référentiel
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_gestion').on
    (
      'click' ,
      '#bouton_valider' ,
      function()
      {
        if(mode=='supprimer')
        {
          $.prompt(prompt_etapes);
        }
        else
        {
          envoyer_action_confirmee();
        }
      }
    );

    var prompt_etapes = {
      etape_2: {
        title   : 'Demande de confirmation (2/3)',
        html    : "Tous les items correspondants seront supprimés !<br />Les résultats des élèves qui en dépendent seront perdus !<br />Souhaitez-vous vraiment supprimer ce référentiel ?",
        buttons : {
          "Non, c'est une erreur !" : false ,
          "Oui, je confirme !" : true
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            event.preventDefault();
            $('#referentiel_infos_prompt').html($('#referentiel_infos').html());
            $.prompt.goToState('etape_3');
            return false;
          }
          else {
            $('#bouton_annuler').click();
          }
        }
      },
      etape_3: {
        title   : 'Demande de confirmation (3/3)',
        html    : "Attention : dernière demande de confirmation !!!<br />Êtes-vous bien certain de vouloir supprimer le référentiel &laquo;&nbsp;"+'<span id="referentiel_infos_prompt"></span>'+"&nbsp;&raquo; ?<br />Est-ce définitivement votre dernier mot ???",
        buttons : {
          "Oui, j'insiste !" : true ,
          "Non, surtout pas !" : false
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            envoyer_action_confirmee();
            return true;
          }
          else {
            $('#bouton_annuler').click();
          }
        }
      }
    };

    function envoyer_action_confirmee()
    {
      $('#ajax_msg_gestion').attr('class','loader').html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&'+$('#form_gestion').serialize(),
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg_gestion').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            return false;
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            var action = $('#f_action').val();
            if(action=='partager')
            {
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg_gestion').attr('class','alerte').html(responseJSON['value']);
                return false;
              }
              else
              {
                $('#ajax_msg_gestion').attr('class','valide').html("Demande réalisée !");
                var partage     = $('#f_partage option:selected').val();
                var information = $('#f_information').val();
                tab_partage_etat[id_mat_niv] = partage;
                tab_information[ id_mat_niv] = information;
                $('#'+ids).prev().prev().html(responseJSON['value']);
                if(partage=='oui')
                {
                  $('#'+ids).children('q.envoyer_non').attr('class','envoyer').attr('title','Mettre à jour sur le serveur de partage la dernière version de ce référentiel.');
                }
                else
                {
                  $('#'+ids).children('q.envoyer').attr('class','envoyer_non').attr('title','Un référentiel non partagé ne peut pas être transmis à la collectivité.');
                }
                $.fancybox.close();
              }
            }
            if(action=='envoyer')
            {
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg_gestion').attr('class','alerte').html(responseJSON['value']);
                return false;
              }
              else
              {
                $('#ajax_msg_gestion').attr('class','valide').html("Demande réalisée !");
                var information = $('#f_information').val();
                tab_information[ id_mat_niv] = information;
                $('#'+ids).prev().prev().html(responseJSON['value']);
                $.fancybox.close();
              }
            }
            if(action=='calculer')
            {
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg_gestion').attr('class','alerte').html(responseJSON['value']);
                return false;
              }
              else
              {
                $('#ajax_msg_gestion').attr('class','valide').html("Demande réalisée !");
                tab_calcul_methode[   id_mat_niv] = $('#f_methode option:selected'   ).val();
                tab_calcul_limite[    id_mat_niv] = $('#f_limite option:selected'    ).val();
                tab_calcul_retroactif[id_mat_niv] = $('#f_retroactif option:selected').val();
                $('#'+ids).prev().html(responseJSON['value']);
                $.fancybox.close();
              }
            }
            if(action=='supprimer')
            {
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg_gestion').attr('class','alerte').html(responseJSON['value']);
                return false;
              }
              else
              {
                $('#ajax_msg_gestion').attr('class','valide').html("Demande réalisée !");
                $('#'+ids).parent().remove();
                if( $('#mat_'+tab_ids[1]+' tbody tr').length == 1 )
                {
                  $('#mat_'+tab_ids[1]+' tbody').prepend('<tr class="absent"><td class="r hc">---</td><td class="r hc">---</td><td class="r hc">---</td><td class="nu"></td></tr>');
                }
                $.fancybox.close();
              }
            }
          }
        }
      );
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur l'image pour Ajouter un référentiel => affichage de choisir_referentiel même dans le cas d'une matière spécifique à l'établissement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $(document).on
    (
      'click',
      'q.ajouter',
      function()
      {
        ids = $(this).parent().attr('id');
        tab_ids = ids.split('_');
        var matiere_id    = tab_ids[1];
        var matiere_nom = $('#h2_'+matiere_id).html();
        $('#matiere_id').val(matiere_id);
        $('#choisir_referentiel h2 span').html(matiere_nom);
        $("#f_niveau_create option").each
        (
          function()
          {
            var matiere_valeur = $(this).val();
            if( matiere_valeur )
            {
              if( $('#ids_'+matiere_id+'_'+matiere_valeur).length )
              {
                $(this).prop('disabled',true);
              }
              else
              {
                $(this).prop('disabled',false);
              }
            }
          }
        );
        $("#f_niveau_create option:first").prop('selected',true);
        $('#div_tableaux').hide();
        $('#choisir_importer').parent().hide();
        $('#ajax_msg_choisir').removeAttr('class').html("");
        $('#choisir_referentiel').show();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour Annuler le choix d'un référentiel
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#choisir_annuler').click
    (
      function()
      {
        $('#choisir_referentiel').hide();
        $('#ajax_msg_choisir').removeAttr('class').html("");
        $('#div_tableaux').show();
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Charger le formulaire listant les structures ayant partagées un référentiel (appel au serveur communautaire)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var charger_formulaire_structures = function()
    {
      $('#rechercher').prop('disabled',true);
      $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=afficher_structures_partage',
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus)+' <a href="#" id="charger_formulaire_structures">Veuillez essayer de nouveau.</a>');
            return false;
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==false)
            {
              $('#ajax_msg').attr('class','alerte').html(responseJSON['value']+' <a href="#" id="charger_formulaire_structures">Veuillez essayer de nouveau.</a>');
            }
            else
            {
              $('#ajax_msg').removeAttr('class').html('&nbsp;');
              $('#f_structure').html(responseJSON['value']);
              $('#rechercher').prop('disabled',false);
            }
          }
        }
      );
    };

    // Charger au clic sur le lien obtenu si échec
    $('#ajax_msg').on( 'click', '#charger_formulaire_structures', charger_formulaire_structures );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour Afficher le formulaire de recherche sur le serveur communautaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#choisir_rechercher').click
    (
      function()
      {
        // Récup des infos
        var matiere_id = $('#matiere_id').val();
        var niveau_id  = parseInt( $('#f_niveau_create option:selected').val() ,10); // parseInt() évite ensuite une erreur si aucun niveau n'est sélectionné
        // MAJ et affichage du formulaire
        $('#ajax_msg_choisir').removeAttr('class').html('');
        if( $('#f_structure option').length == 1 )
        {
          charger_formulaire_structures();
        }
        $('#f_matiere option[value='+matiere_id+']').prop('selected',true);
        $('#f_niveau option[value='+niveau_id+']').prop('selected',true);
        $('#choisir_referentiel_communautaire ul').html('<li></li>');
        $('#lister_referentiel_communautaire').hide("fast");
        $('#form_instance').hide();
        $('#form_communautaire').show();
        initialiser_compteur();
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Charger le select f_matiere en ajax
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_matiere(matiere_famille_id)
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_maj_select_matieres_famille',
          data : 'f_famille_matiere='+matiere_famille_id,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_maj_matiere').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==true)
            {
              $('#f_matiere').html(responseJSON['value']);
            }
            else
            {
              $('#ajax_maj_matiere').attr('class','alerte').html(responseJSON['value']);
            }
          }
        }
      );
    }

    $("#f_famille_matiere").change
    (
      function()
      {
        matiere_famille_id = $("#f_famille_matiere").val();
        if(matiere_famille_id)
        {
          maj_matiere(matiere_famille_id);
        }
        else
        {
          $('#f_matiere').html('<option value="0">Toutes les matières</option>');
          $('#ajax_maj_matiere').removeAttr('class').html("");
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Charger le select f_niveau en ajax
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_niveau(niveau_famille_id)
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_maj_select_niveaux_famille',
          data : 'f_famille_niveau='+niveau_famille_id,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_maj_niveau').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==true)
            {
              $('#f_niveau').html(responseJSON['value']);
            }
            else
            {
              $('#ajax_maj_niveau').attr('class','alerte').html(responseJSON['value']);
            }
          }
        }
      );
    }

    $("#f_famille_niveau").change
    (
      function()
      {
        niveau_famille_id = $("#f_famille_niveau").val();
        if(niveau_famille_id)
        {
          maj_niveau(niveau_famille_id);
        }
        else
        {
          $('#f_niveau').html('<option value="0">Tous les niveaux</option>');
          $('#ajax_maj_niveau').removeAttr('class').html("");
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Réagir au changement dans un select
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#choisir_referentiel_communautaire select').change
    (
      function()
      {
        $('#ajax_msg').removeAttr('class').html("");
        $('#choisir_referentiel_communautaire ul').html('<li></li>');
        $('#lister_referentiel_communautaire').hide("fast");
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le checkbox pour choisir ou non une date de maj
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#box_date').click
    (
      function()
      {
        if($(this).is(':checked'))
        {
          $(this).next().show(0).next().hide(0);
        }
        else
        {
          $(this).next().hide(0).next().show(0).children('input').focus();
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour chercher des référentiels partagés sur d'autres niveaux ou matières
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#rechercher').click
    (
      function()
      {
        var is_date_libre = $('#box_date').is(':checked');
        var matiere_id    = $('#f_matiere').val();
        var niveau_id     = $('#f_niveau').val();
        var structure_id  = $('#f_structure').val();
        var maj_date      = is_date_libre ? '01/01/1970' : $('#f_maj_date').val() ;
        if( !test_dateITA( maj_date ) )
        {
          $('#ajax_msg').attr('class','erreur').html("Date incorrecte !");
          return false;
        }
        if( (matiere_id==0) && (niveau_id==0) && (structure_id==0) && (is_date_libre) )
        {
          $('#ajax_msg').attr('class','erreur').html("Il faut préciser au moins un critère parmi matière / niveau / date / structure !");
          return false;
        }
        $('#rechercher').prop('disabled',true);
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=lister_referentiels_communautaires'+'&f_matiere_id='+matiere_id+'&f_niveau_id='+niveau_id+'&f_structure_id='+structure_id+'&f_maj_date='+encodeURIComponent(maj_date),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#rechercher').prop('disabled',false);
              $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              $('#rechercher').prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                initialiser_compteur();
                $('#ajax_msg').removeAttr('class').html("");
                var reg = new RegExp('</q>',"g"); // Si on ne prend pas une expression régulière alors replace() ne remplace que la 1e occurence
                responseJSON['value'] = responseJSON['value'].replace(reg,'</q><q class="valider" title="Sélectionner ce référentiel.<br />(choix à confirmer de retour à la page principale)"></q>'); // Ajouter les paniers
                $('#table_action tbody').html(responseJSON['value']);
                tableau_maj();
                infobulle();
                $('#lister_referentiel_communautaire').show("fast");
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur l'image pour Voir le détail d'un référentiel partagé
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#choisir_referentiel_communautaire').on
    (
      'click',
      'q.voir',
      function()
      {
        var referentiel_id = $(this).parent().attr('id').substr(3);
        var objet_tds      = $(this).parent().parent().find('td');
        var description    = objet_tds.eq(0).html() + ' || ' + objet_tds.eq(1).html() + ' || ' + objet_tds.eq(2).html() + ' || ' + objet_tds.eq(3).html();
        $.fancybox( '<label class="loader">'+'En cours&hellip;'+'</label>' , {'centerOnScroll':true} );
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=voir_referentiel_communautaire'+'&f_referentiel_id='+referentiel_id,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+'</label>' , {'centerOnScroll':true} );
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==false)
              {
                $.fancybox( '<label class="alerte">'+responseJSON['value']+'</label>' , {'centerOnScroll':true} );
              }
              else
              {
                $.fancybox( '<p class="noprint">Afin de préserver l\'environnement, n\'imprimer que si nécessaire !</p>'+'<ul class="ul_m1"><li class="li_m1"><b>'+description+'</b><q class="imprimer_arbre" title="Imprimer le référentiel."></q>'+responseJSON['value']+'</li></ul>' , {'centerOnScroll':true} );
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur une image pour choisir un référentiel donné
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#choisir_referentiel_communautaire').on
    (
      'click',
      'q.valider',
      function()
      {
        var referentiel_id = $(this).parent().attr('id').substr(3);
        var objet_tds      = $(this).parent().parent().find('td');
        var description    = objet_tds.eq(0).html() + ' || ' + objet_tds.eq(1).html() + ' || ' + objet_tds.eq(2).html() + ' || ' + objet_tds.eq(3).html();
        $('#reporter').html(description).parent('#choisir_importer').val('id_'+referentiel_id).parent().show();
        initialiser_compteur();
        $('#rechercher_annuler').click();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour Annuler la recherche sur le serveur communautaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#rechercher_annuler').click
    (
      function()
      {
        $('#form_instance').show();
        $('#form_communautaire').hide();
        $('#lister_referentiel_communautaire').hide();
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur un bouton pour Valider le choix d'un referentiel (vierge ou issu du serveur communautaire)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#choisir_initialiser , #choisir_importer').click
    (
      function()
      {
        var matiere_id  = $('#matiere_id').val();
        var matiere_nom = $('#h2_'+matiere_id).html();
        var niveau_id   = $('#f_niveau_create option:selected').val();
        var niveau_nom  = $('#f_niveau_create option:selected').text();
        if(!niveau_id)
        {
          $('#ajax_msg_choisir').attr('class','erreur').html('Choisir un niveau !');
          return false;
        }
        var partageable = ( ( matiere_id <= ID_MATIERE_PARTAGEE_MAX ) && ( niveau_id <= ID_NIVEAU_PARTAGE_MAX ) ) ? true : false ;
        $('#ajax_msg_choisir').removeAttr('class').html('');
        var referentiel_id = $(this).val().substring(3);
        $('button').prop('disabled',true);
        $('#ajax_msg_choisir').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=ajouter_referentiel_etablissement'+'&f_ids=ids_'+matiere_id+'_'+niveau_id+'&f_referentiel_id='+referentiel_id+'&f_matiere_nom='+encodeURIComponent(matiere_nom)+'&f_niveau_nom='+encodeURIComponent(niveau_nom),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('button').prop('disabled',false);
              $('#ajax_msg_choisir').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('button').prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg_choisir').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                // niveau
                var td_niveau = '<td>'+$('#f_niveau_create option:selected').text()+'</td>';
                // partage
                if(!partageable)
                {
                  var td_partage = '<td class="hc"><img title="Référentiel dont le partage est sans objet (matière ou niveau spécifique)." src="./_img/etat/partage_non.gif" /></td>';
                  tab_partage_etat[matiere_id+'_'+niveau_id] = 'hs';
                }
                else if(referentiel_id!='0')
                {
                  var td_partage = '<td class="hc"><img title="Référentiel dont le partage est sans intérêt (pas novateur)." src="./_img/etat/partage_non.gif" /></td>';
                  tab_partage_etat[matiere_id+'_'+niveau_id] = 'bof';
                }
                else
                {
                  var td_partage = '<td class="hc"><img title="Référentiel non partagé avec la communauté." src="./_img/etat/partage_non.gif" /></td>';
                  tab_partage_etat[matiere_id+'_'+niveau_id] = 'non';
                }
                // méthode de calcul
                var td_calcul = '<td>'+calcul_texte+'</td>';
                tab_calcul_methode[matiere_id+'_'+niveau_id] = calcul_methode;
                tab_calcul_limite[matiere_id+'_'+niveau_id]  = calcul_limite;
                // actions
                var q_partager = (partageable) ? '<q class="partager" title="Modifier le partage de ce référentiel."></q>' : '<q class="partager_non" title="Le référentiel d\'une matière ou d\'un niveau spécifique à l\'établissement ne peut être partagé."></q>' ;
                var td_actions = '<td id="ids_'+matiere_id+'_'+niveau_id+'" class="nu"><q class="voir" title="Voir le détail de ce référentiel."></q>'+q_partager+'<q class="envoyer_non" title="Un référentiel non partagé ne peut pas être transmis à la collectivité."></q><q class="calculer" title="Modifier le mode de calcul associé à ce référentiel."></q><q class="supprimer" title="Supprimer ce référentiel."></q></td>';
                // ajout de la ligne
                $('#mat_'+matiere_id).children('tbody').prepend('<tr class="new">'+td_niveau+td_partage+td_calcul+td_actions+'</tr>');
                $('#mat_'+matiere_id).children('tbody').children('tr.absent').remove();
                $('#choisir_annuler').click();
                var label_message  = (referentiel_id) ? "Référentiel importé avec succès !" : "Référentiel vierge ajouté." ;
                var astuce_message = (referentiel_id) ? "Pour éditer ce nouveau référentiel," : "Pour remplir ce nouveau référentiel," ;
                $.fancybox( '<label class="valide">'+label_message+'</label><p class="astuce">'+astuce_message+' utiliser la page "<a href="./index.php?page=professeur_referentiel&amp;section=edition">modifier le contenu des référentiels</a>".</p>' , {'centerOnScroll':true} );
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur l'image pour Annuler la suppression ou la modification du partage ou la modification du mode de calcul d'un référentiel
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_gestion').on
    (
      'click',
      '#bouton_annuler',
      function()
      {
        $.fancybox.close();
      }
    );

  }
);
